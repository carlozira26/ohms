<?php 
namespace Controllers;
use Models\MedicinesModel;
use Models\TimeIntakeModel;
use Models\PatientMedicinesModel;
use \Firebase\JWT\JWT;

class MedicinesController{
	protected $container;

	private $response = array(
		"status" => false,
		"data" => array(),
		"message" => ""
	);
	
	function __construct($container){
		$this->container = $container;
	}

	public function addEditMedicine($request, $response, $args){
		$body = $request->getParsedBody();
		$timeIntakeList = json_decode($body["timeIntake"],true);
		$type = $args['type'];

		if($type == "add"){
			$medicineSave = MedicinesModel::create(array(
				"brandname" => $body['brandName'],
				"genericname" => $body['genericName'],
				"instructions" => $body['instructions']
			));
			$id = $medicineSave->id;
			$this->response["message"] = "Successfully added!";
			$this->response["status"] = true;
		}else{
			$oldDayTime = TimeIntakeModel::where('uid',$body['id'])
				->update(array("is_active" => "N"));

			$medicineSave = MedicinesModel::where('id',$body['id'])
				->update(array(
				"brandname" => $body['brandName'],
				"genericname" => $body['genericName'],
				"instructions" => $body['instructions']
			));
			$id = $body['id'];
			$this->response["message"] = "Successfully edited!";
			$this->response["status"] = true;
		}

		for($x=0; $x<count($timeIntakeList); $x++ ){
			$dayListImplode = implode(",",$timeIntakeList[$x]["dayList"]);
			$timeListImplode = implode(",",$timeIntakeList[$x]["timeList"]);
			
			TimeIntakeModel::create(array(
				"uid" => $id,
				"intakedays" => $dayListImplode,
				"intaketime" => $timeListImplode
			));
		}
		return $this->container->response->withJson($this->response);
	}
	public function MedicineList($request, $response, $args){
		$body = $request->getQueryParams();
		$limit = 6;
		$offset = ($_GET['page'] - 1) * $limit;
		
		$medicineList = MedicinesModel::select("id", "brandname", "genericname", "instructions", "is_active")->where('is_active','Y');
		$medicineCount = MedicinesModel::selectRaw("count(id) as count")->where('is_active','Y');
		if(!empty($_GET['search'])){
			$medicineList = $medicineList
				->where('brandname', 'LIKE', "%".$_GET['search']."%" )
				->orWhere('genericname', 'LIKE', "%".$_GET['search']."%" )
				->orWhere('instructions', 'LIKE', "%".$_GET['search']."%" );
			$medicineCount = $medicineCount
				->where('brandname', 'LIKE', "%".$_GET['search']."%" )
				->orWhere('genericname', 'LIKE', "%".$_GET['search']."%" )
				->orWhere('instructions', 'LIKE', "%".$_GET['search']."%" );
		}else{
			$medicineList = $medicineList
				->offset($offset)
				->limit($limit);
		}
		$medicineList = $medicineList
			->orderBy('brandname',"asc")
			->get();
		$medicineCount = $medicineCount->first();	
		$this->response['data'] = $medicineList;
		$this->response['count'] = $medicineCount;
		$this->response['status'] = true;
		return $this->container->response->withJson($this->response);
	}
	public function MedicineDetails($request, $response, $args){
		$id = $args['id'];
		$timeIntake = TimeIntakeModel::where('is_active','Y')->where('uid',$id)->get();
		$this->response['data'] = $timeIntake;
		$this->response['status'] = true;
		return $this->container->response->withJson($this->response);
	}
	public function MedicineDelete($request, $response, $args){
		$id = $args['id'];
		MedicinesModel::where('id',$id)
			->update(array('is_active' => 'N'));
		$this->response['status'] = true;
		return $this->container->response->withJson($this->response);
	}

	public function getMedicineList($request, $response, $args) {
		$medicineList = MedicinesModel::selectRaw("id,concat(brandname,':',genericname) as medicinename,instructions")->where('is_active','Y')->get();
		$this->response['data'] = $medicineList;
		$this->response['status'] = true;
		return $this->container->response->withJson($this->response);
	}

	public function submitPatientMedicine($request, $response, $args){
		$body = $request->getParsedBody();
		$body = json_decode($body['patientMedicineList'],true);
		$patientID = $args['id'];

		PatientMedicinesModel::where('uid',$patientID)->update(array('is_active'=>'N'));
		foreach($body as $medicine){
			PatientMedicinesModel::create(array(
				'uid' => $patientID,
				'medicineid' => $medicine['medicineID'],
				'dosage' => $medicine['medicineDosage'],
				'pieces' => $medicine['medicinePieces'],
				'date_added' => date('Y-m-d')
			));
		}
		$this->response['status'] = true;
		return $this->container->response->withJson($this->response);
	}
	public function getPatientMedicine($request, $response, $args){
		$patientID = $args['id'];
		$patientMedicine = PatientMedicinesModel::selectRaw("patient_medicine.medicineid,concat(brandname,':',genericname)as medicinename,dosage,pieces,instructions")
			->join('medicines','medicines.id','=','patient_medicine.medicineid')
			->where('uid',$patientID)
			->where('patient_medicine.is_active','Y')
			->get();
		return $this->container->response->withJson($patientMedicine);
	}
	public function getPatientMedicineSchedule($request, $response, $args){
		$patientid = $args['id'];
		$MedicineList = PatientMedicinesModel::selectRaw('medicines.id, concat(brandname,":",genericname) medicine,pieces,date_added')
			->join('medicines','patient_medicine.medicineid','=','medicines.id')
			->where('patient_medicine.uid', $patientid)
			->where('patient_medicine.is_active','Y')
			->get();

			$datacount = 0; 
			foreach($MedicineList as $Medicine){
				$timeIntake = TimeIntakeModel::select('intakedays','intaketime')
					->where('is_active','Y')
					->where('uid',$Medicine['id'])
					->get();
				$arrtime = array();
				$arrdate = array();
				foreach($timeIntake as $time){
					array_push($arrtime,$time['intaketime']); 
					array_push($arrdate,$time['intakedays']); 
				}
				$MedicineList[$datacount]['timeintake'] = $arrtime;
				$MedicineList[$datacount]['dateintake'] = $arrdate;
				$datacount++;
			}
			$datacount = 0;
			foreach($MedicineList as $medicine){
				$medList = $medicine['dateintake'];
				$datearr = array();
				$medicineDateArr = array();
				foreach($medList as $weeks){
					$weekname = explode(',',$weeks);
					foreach($weekname as $week){
						foreach($this->getDates($week,$medicine['date_added']) as $dates){
							array_push($datearr,$dates);
							array_push($medicineDateArr,$dates);
						}
					}
					sort($medicineDateArr);
				}
				$perMedicineSchedule[$datacount]['medicinedatelist'] = $medicineDateArr;
				$datacount++;
				sort($datearr);
			}
			foreach($perMedicineSchedule as $key=>$med){
				$MedicineList[$key]['datelist'] = $med;
			}
			$this->response['data'] = $MedicineList;
			$this->response['dates'] = $datearr;
		return $this->container->response->withJson($this->response);
	}

	private function getDates($weekname,$startdate){
		$date = date('Y-m-d', strtotime('first '.$weekname.' of this month'));
		$thisMonth = date("m", strtotime($date));
		$arr = array();
		while (date("m", strtotime($date)) === $thisMonth){
			if($startdate <= date("Y-m-d", strtotime($date))){
				array_push($arr, date("Y-m-d", strtotime($date)));
			}
			$date = date('Y-m-d', strtotime($date ." next ".$weekname));
		}
		return $arr;
	}
}