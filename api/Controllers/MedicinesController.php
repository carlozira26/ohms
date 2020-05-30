<?php 
namespace Controllers;
use Models\MedicinesModel;
use Models\TimeIntakeModel;
use Models\PatientMedicinesModel;
use Models\IntakeLogsModel;
use Models\PatientIntakeModel;
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
		$medicineList = MedicinesModel::selectRaw("id,concat(brandname,':',genericname) as medicinename")
			->where('is_active','Y')
			->get();
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
		$data = $request->getQueryParams();
		if($data['status'] == 'New'){
			$medicineList = MedicinesModel::selectRaw("id,concat(brandname,':',genericname) as medicinename");

			if($data['category'] == 'Cat I'){
				$medicineList = $medicineList->selectRaw('cat1 as dosage');
			}else if($data['category'] == 'Cat II'){
				$medicineList = $medicineList->selectRaw('cat2 as dosage');
			}else{
				$medicineList = $medicineList->selectRaw('mdr as dosage');
			}
			$medicineList = $medicineList->where('is_primary','Y')->get();
		}else{
			$medicineList = MedicinesModel::selectRaw("medicines.id as id,concat(brandname,':',genericname) as medicinename,dosage,pieces")
				->join('patient_medicine','medicines.id','=','patient_medicine.medicineid')
				->where('uid',$data['id'])
				->get();
		}
		if($medicineList){
			$this->response['data'] = $medicineList;
			$this->response['status'] = true;
		}

		return $this->container->response->withJson($this->response);
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
		$body = $req->getQueryParams();

		$Utils = new Utils();
		$user = $Utils->getPatientFromBearerToken($req, $this->container);
		$id = (isset($body['id'])) ? $body['id'] : $user['id'];

		$date = date('Y-m-d');
		$val = IntakeLogsModel::select('intake_value')
			->where('patient_id',$id)
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
		
		$medicinename = $body['medicinename'];
		$medicine_index = (isset($body['medicineindex'])) ? $body['medicineindex'] : "";
		$reason = (isset($body['reason'])) ? $body['reason'] : "";
		$Utils = new Utils();
		$user = $Utils->getPatientFromBearerToken($req, $this->container);

		$id = (isset($body['id'])) ? $body['id'] : $user['id'];
		$date = date('Y-m-d');
		if($body['type']=='create'){
			IntakeLogsModel::create(array(
				'patient_id' => $id,
				'intake_value' => $newVal,
				'date_intake' => $date
			));
		}else{
			IntakeLogsModel::where('patient_id',$id)
				->where('date_intake',$date)
				->update(array(
				'intake_value' => $newVal,
			));
			$check = PatientIntakeModel::where('patient_id', $id)
				->where('medicine_index', $medicine_index)
				->where('medicine', $medicinename)
				->whereRaw('cast(created_at as date) = "'.$date.'"')
				->first();
			if($check){
				PatientIntakeModel::where('patient_id', $id)
				->where('medicine_index', $medicine_index)
				->where('medicine', $medicinename)
				->whereRaw('cast(created_at as date)="'.$date.'"')
				->update(array(
					'reason' => $reason
				));
			}else{
				PatientIntakeModel::create(array(
					'patient_id' => $id,
					'medicine_index' => $medicine_index,
					'medicine' => $medicinename,
					'reason' => $reason
				));
			}
		}
		$this->response['status'] = true;
		return $this->container->response->withJson($this->response);
	}
	public function medicineInstructions($req, $res, $args){
		$medicineName = $_GET['medicine'];
		$instruction = MedicinesModel::select("instructions")->whereRaw('concat(brandname,":",genericname) = "'.$medicineName.'"')->first();
		
		return $this->container->response->withJson($instruction['instructions']);
	}
	public function checkReason($req, $res, $args){
		$data = $req->getQueryParams();
		$date = date('Y-m-d');
		$logs = PatientIntakeModel::select('medicine_index','reason')
			->where('patient_id', $data['patientid'])
			->whereRaw('cast(created_at as date)="'.$date.'"')
			->orderBy('medicine_index','asc')
			->get();
		$this->response['data'] = $logs;
		$this->response['status'] = true;
		return $this->container->response->withJson($this->response);
	}
}