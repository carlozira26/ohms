<?php 
namespace Controllers;
use Models\MedicinesModel;
use Models\TimeIntakeModel;
use Models\PatientMedicinesModel;
use Models\IntakeLogsModel;
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
		if($type == "new"){
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
		$medList = array();
		$date = date('Y-m-d');

		foreach($body as $newMedicine){
			$selectID = PatientMedicinesModel::where('uid',$patientID)
				->where('medicineid',$newMedicine['medicineID'])
				->where('is_active','Y')
				->first();
			if($selectID){
				array_push($medList, $selectID['id']);
				PatientMedicinesModel::where('uid',$patientID)
				->where('medicineid', $selectID['id'])
				->update(array(
					'medicineid' => $newMedicine['medicineID'],
					'dosage' => $newMedicine['medicineDosage'],
					'pieces' => $newMedicine['medicinePieces']
				));
			}else{
				$newMedID = PatientMedicinesModel::create(array(
					'uid' => $patientID,
					'medicineid' => $newMedicine['medicineID'],
					'dosage' => $newMedicine['medicineDosage'],
					'pieces' => $newMedicine['medicinePieces'],
					'date_added' => $date
				));
				array_push($medList, $newMedID->id);
			}
		}
		$newMed = PatientMedicinesModel::where('uid',$patientID)
			->where('is_active','Y')
			->whereNotIn('id',$medList)
			->update(array('is_active'=>'N'));

		$this->response["message"] = "Successfully Added!";
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
			$schedule = array();
			$datelist = array();
			foreach($MedicineList as $Medicine){
				$timeIntake = TimeIntakeModel::select('intakedays','intaketime')
					->where('is_active','Y')
					->where('uid', $Medicine['id'])
					->get();

				$dates = $this->processMedicineTimeSchedule($Medicine['pieces'],$timeIntake[0]['intaketime'],$timeIntake[0]['intakedays'],$Medicine['date_added']);
				$intaketime = explode(',',$timeIntake[0]['intaketime']);
				foreach($dates as $date){
					if(!in_array($date,$datelist)){
						array_push($datelist,$date);
					}
					foreach($intaketime as $time){
						$schedule[$date][$time][] = $Medicine['medicine'];
					}
				}
			}
			ksort($schedule);
			// $resort = array();
			// foreach($schedule as $key=>$value){
			// 	$resort[] = array(
			// 		"date" => $key,
			// 		"time" => $value
			// 	);
			// }
			$this->response['data'] = $schedule;
			$this->response['dates'] = $datelist;
			$this->response['status'] = true;
		return $this->container->response->withJson($this->response);
	}
	public function processMedicineTimeSchedule($pieces,$timelist,$days,$date_added){
		$timelist = explode(',',$timelist);
		$totaldays = $pieces / count($timelist);
		$totaldays = (!is_int($totaldays)) ? round($totaldays) : $totaldays;
		
		return $this->getDates($days, $date_added, $totaldays);
	}
	private function getDates($weekdays,$startdate,$totaldays){
		if($weekdays == "Daily"){
			$date = date('Y-m-d', strtotime($startdate));
			$thisMonth = date("m",strtotime($date));
			$arr = array();
			$zerostart = 0;
			while($zerostart < $totaldays){
				array_push($arr, $date);
				$date = date('Y-m-d',strtotime($date." +1 days"));
				$zerostart++;
			}
		}else{
			$explweek = explode(",",$weekdays);
			$averagedays = round($totaldays / count($explweek));
			$arr = array();
			$zerostart = 0;
			foreach($explweek as $day){
				$date = date('Y-m-d', strtotime($startdate.' first '.$day.' of this month'));
				$zerostart = 0;
				while($zerostart < $averagedays){
					if(date("Y-m-d", strtotime($date)) > $startdate){
						array_push($arr, date("Y-m-d", strtotime($date)));
						$zerostart++;						
					}
					$date = date('Y-m-d', strtotime($date ." next ".$day));
				}
			}
			$arr = (count($arr) > $totaldays) ? array_slice($arr,0,$totaldays) : $arr;
			sort($arr);
		}
		return $arr;
	}
	public function getMedicineVal($req, $res, $args){
		$Utils = new Utils();
		$user = $Utils->getPatientFromBearerToken($req, $this->container);
		$date = date('Y-m-d');
		$val = IntakeLogsModel::select('intake_value')
			->where('patient_id',$user['id'])
			->where('date_intake',$date)
			->first();
		if($val){
			$this->response['data'] = $val;
			$this->response['status'] = true;
		}
		return $this->container->response->withJson($this->response);
	}
	public function newMedicineVal($req, $res, $args){
		$body = $req->getParsedBody();
		$newVal = implode(",",json_decode($body['newVal']));
		$Utils = new Utils();
		$user = $Utils->getPatientFromBearerToken($req, $this->container);
		$date = date('Y-m-d');
		if($body['type']=='create'){
			IntakeLogsModel::create(array(
				'patient_id' => $user['id'],
				'intake_value' => $newVal,
				'date_intake' => $date
			));
		}else{
			IntakeLogsModel::where('patient_id',$user['id'])
				->where('date_intake',$date)
				->update(array(
				'intake_value' => $newVal,
			));
		}
		$this->response['status'] = true;
		return $this->container->response->withJson($this->response);
	}
}