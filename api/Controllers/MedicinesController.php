<?php 
namespace Controllers;
use Models\MedicinesModel;
use Models\TimeIntakeModel;
use Models\PatientMedicinesModel;
use Models\IntakeLogsModel;
use Models\PatientIntakeModel;
use Models\PatientsModel;
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
		$type = $args['type'];
		if($type == "new"){
			$medicineSave = MedicinesModel::create(array(
				"brandname" => $body['brandName'],
				"genericname" => $body['genericName'],
				"manufacturer" => $body['manufacturer'],
				"expiration" => $body['expiration'],
				"description" => $body['description'],
				"is_primary" => ($body['is_primary']) ? "Y" : "N",
			));
			$this->response["message"] = "Successfully added!";
			$this->response["status"] = true;
		}else{
			$medicineSave = MedicinesModel::where('id',$body['id'])
				->update(array(
				"brandname" => $body['brandName'],
				"genericname" => $body['genericName'],
				"manufacturer" => $body['manufacturer'],
				"expiration" => $body['expiration'],
				"description" => $body['description'],
				"is_primary" => ($body['is_primary']=="true") ? "Y" : "N",
			));
			$this->response["message"] = "Successfully edited!";
			$this->response["status"] = true;
		}

		return $this->container->response->withJson($this->response);
	}
	public function MedicineList($request, $response, $args){
		$body = $request->getQueryParams();
		$limit = 6;
		$offset = ($_GET['page'] - 1) * $limit;
		
		$medicineList = MedicinesModel::where('is_active','Y');
		$medicineCount = MedicinesModel::selectRaw("count(id) as count")->where('is_active','Y');
		if(!empty($_GET['search'])){
			$medicineList = $medicineList
				->where('brandname', 'LIKE', "%".$_GET['search']."%" )
				->orWhere('genericname', 'LIKE', "%".$_GET['search']."%" )
				->orWhere('manufacturer', 'LIKE', "%".$_GET['search']."%" );
			$medicineCount = $medicineCount
				->where('brandname', 'LIKE', "%".$_GET['search']."%" )
				->orWhere('genericname', 'LIKE', "%".$_GET['search']."%" )
				->orWhere('manufacturer', 'LIKE', "%".$_GET['search']."%" );
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
		$MedicineList = json_decode($body['patientMedicineList'],true);
		$patientID = $body['id'];
		$category = $body['category'];
		$medList = array();
		$date = date('2020-06-05');

		$patientSchedule = implode(',',$this->getWeeklySchedule($date,$category));
		foreach($MedicineList as $newMedicine){
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
				PatientsModel::where('id',$patientID)
					->update(array("status" => "Ongoing"));

				array_push($medList, $newMedID->id);
			}
		}
		$newMed = PatientMedicinesModel::where('uid',$patientID)
			->where('is_active','Y')
			->whereNotIn('id',$medList)
			->update(array('is_active'=>'N'));
		$sched = PatientsModel::where('id',$patientID)->update(array('medicine_schedule'=>$patientSchedule));
		$this->response["message"] = "Successfully Added!";
		$this->response['status'] = true;
		return $this->container->response->withJson($this->response);
	}
	public function getPatientMedicine($request, $response, $args){
		$data = $request->getQueryParams();
		if($data['status'] == 'New'){
			$medicineList = MedicinesModel::selectRaw("id,concat(brandname,':',genericname) as medicinename");
			$medicineList = $medicineList->where('is_primary','Y')->where('is_active','Y')->get();
		}else{
			$medicineList = MedicinesModel::selectRaw("medicines.id as id, concat(brandname,':',genericname) as medicinename")
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
	public function newMedicineVal($req, $res, $args){
		$body = $req->getParsedBody();
		$date = (isset($body['date'])) ?  $body['date'] : date('Y-m-d');
		$patientIntake = new PatientIntakeModel;
		$patientIntake->status = $body['status'];
		$patientIntake->medicineid = $body['medicineid'];
		$patientIntake->reason = $body['reason'];
		$patientIntake->patient_id = $body['patientid'];
		$patientIntake->date = $date;
		$patientIntake->save();

		$this->response['status'] = true;
		return $this->container->response->withJson($body);
	}

	public function getSchedule($req, $res, $args){
		$body = $req->getQueryParams();
		$category = $body['category'];
		$date = $body['date'];
		$arr = array();
		if($category == "MDR"){
			$arr = $this->MDR($date);
		}else if($category == "Cat II"){
			$week = date('l', strtotime($date));
			if($week == "Monday" || $week == "Tuesday" || $week == "Thursday"){
				$arr = $this->CatII($date, date('Y-m-d', strtotime($date ."+8 months")), 3);
			}else if($week == "Wednesday" || $week == "Friday"){
				$arr = $this->CatII($date, date('Y-m-d', strtotime($date ."+8 months")), 6);
			}
		}else if($category == "Cat I"){
			$week = date('l', strtotime($date));
			$arr = $this->CatI($date, date('Y-m-d', strtotime($date . "+2 months")), 2);
			if($week == "Friday"){
				$arr = $this->CatI($date, date('Y-m-d', strtotime($date . "+2 months")), 4);
			}
		}
		return $this->container->response->withJson($arr);
	}

	private function MDR($date){
		$nxtYear = date("Y-m-d",strtotime($date.'+1 year'));
		$str = strtotime($nxtYear) - strtotime($date);
		$diff = floor($str/3600/24);

		for($x = 0; $x < $diff; $x++){
			$arr[] = date('Y-m-d', strtotime($date . "+$x days"));
		}
		
		return $arr;
	}

	private function CatII($dateFromString, $dateToString, $loop){
		$dates = [];
		$y = 0;
		for($x=0;$x<$loop;$x++){
			$dates[] = date('Y-m-d',strtotime($dateFromString." +$y days"));
			$date = date('Y-m-d',strtotime($dateFromString." +$y days"));
			$y = $y+2;
		}
		if("Monday" != date('l',strtotime($dateFromString)) && "Tuesday" != date('l',strtotime($dateFromString))){
			array_pop($dates);
		}
		if ("Monday" != date('l',strtotime($date))) {
			$date = date('Y-m-d',strtotime($date . "next monday"));
		}
		$y = 0;
		for($x=0;$x<3;$x++){
			$incrementdate = date('Y-m-d',strtotime($date." +$y days"));
			$dateFrom = new \DateTime(date('Y-m-d',strtotime($incrementdate)));
			$dateTo = new \DateTime($dateToString);
			
			while ($dateFrom <= $dateTo) {
				$dates[] = $dateFrom->format('Y-m-d');
				$dateFrom->modify('+1 week');
			}
			sort($dates);
			$y = $y+2;
		}
		return $dates;
	}
	
	private function CatI($dateFromString, $dateToString,$loop){
		$dates = [];
		$y = 0;
		for($x=0;$x<$loop;$x++){
			$dates[] = date('Y-m-d',strtotime($dateFromString." +$y days"));
			$date = date('Y-m-d',strtotime($dateFromString." +$y days"));
			$y = $y+2;
		}
		if ("Monday" != date('l',strtotime($date))) {
			$date = date('Y-m-d',strtotime($date . "next monday"));
		}
		$y = 0;
		for($x=0;$x<2;$x++){
			$incrementdate = date('Y-m-d',strtotime($date." +$y days"));
			$dateFrom = new \DateTime(date('Y-m-d',strtotime($incrementdate)));
			$dateTo = new \DateTime($dateToString);
			
			while ($dateFrom <= $dateTo) {
				$dates[] = $dateFrom->format('Y-m-d');
				$dateFrom->modify('+1 week');
			}
			sort($dates);
			$y = $y+2;
		}
		return $dates;
	}

	private function getWeeklySchedule($date,$category){
		if($category == "MDR"){
			$arrdays = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
		}else{
			$wday = date('l',strtotime($date));
			$y=0;
			$loop = ($category == "Cat I")? 2 : 3;
			for($x=0;$x<$loop;$x++){
				$arrdays[] = date('l',strtotime($date."+$y days"));
				$y = $y+2;
			}
			if($arrdays[$loop-1] == "Monday"){
				array_pop($arrdays);
			}
		}
		
		return $arrdays;
	}
}