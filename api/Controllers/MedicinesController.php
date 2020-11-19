<?php 
namespace Controllers;
use Models\MedicinesModel;
use Models\TimeIntakeModel;
use Models\PatientMedicinesModel;
use Models\IntakeLogsModel;
use Models\PatientIntakeModel;
use Models\PatientsModel;
use Models\PatientLogsModel;
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
		$Utils = new Utils();
		$user = $Utils->getUserFromBearerToken($request, $this->container);
		
		$MedicineList = json_decode($body['patientMedicineList'],true);
		$patientID = $body['id'];
		$category = $body['category'];
		$status = $body['status'];
		$medList = array();
		$date = date('Y-m-d');

		$patientSchedule = implode(',',$this->getWeeklySchedule($date,$category));
		foreach($MedicineList as $newMedicine){
			$selectID = PatientMedicinesModel::where('uid',$patientID)
				->where('medicineid',$newMedicine['medicineID'])
				->where('is_active','Y')
				->first();
			if($selectID){
				array_push($medList, $selectID['id']);
				PatientMedicinesModel::where('uid',$patientID)
				->where('id', $selectID['id'])
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

		if($status == "New"){
			$sched = PatientsModel::where('id',$patientID)
				->update(array('medicine_schedule'=>$patientSchedule, "datestart" => $date, 
				"dateend" => $this->getEndDate($date,$category)));
			PatientLogsModel::create(array(
				'uid' => $patientID,
				'status' => "Ongoing",
				"updated_by" => $user['lastname'].", ".$user['firstname']
			));
		}

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
			$medicineList = MedicinesModel::selectRaw("medicines.id as id, concat(brandname,':',genericname) as medicinename, dosage, pieces")
				->join('patient_medicine','medicines.id','=','patient_medicine.medicineid')
				->where('uid',$data['id'])
				->where('patient_medicine.is_active','Y')
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
		$date = (isset($body['date'])) ?  date('Y-m-d',strtotime($body['date'])) : date('Y-m-d');
		$Utils = new Utils();
		$user = $Utils->getUserFromBearerToken($req, $this->container);

		$patientIntake = new PatientIntakeModel;
		$patientIntake->status = $body['status'];
		$patientIntake->reason = $body['reason'];
		$patientIntake->patient_id = $body['patientid'];
		$patientIntake->date = $date;
		$patientIntake->distributor = $user['lastname'].", ".$user['firstname'];
		$patientIntake->save();
		
		if($body['status']=="Declined"){
			$patient = PatientsModel::select('mobilenumber')->where('id',$body['patientid'])->first();
			$contactNumber = str_replace('+63','0',$patient['mobilenumber']);
			$chatController = new ChatController($this->container);
			$message = $chatController->messageSend($contactNumber, 'Reminder: Please go to the clinic to take your medicine.');
		}

		$this->response['status'] = $body;
		return $this->container->response->withJson($this->response);
	}

	public function getSchedule($req, $res, $args){
		$body = $req->getQueryParams();
		$category = $body['category'];
		$date = $body['date'];
		$arr = $this->getDateSchedule($category, $date);
		
		return $this->container->response->withJson($arr);
	}

	public function getChartData($req, $res, $args){
		$body = $req->getQueryParams();
		$id = $body['id'];
		$category = $body['category'];
		$date = $body['date'];
		$xaxis = array();
		$arr = array();
		$series = array();
		$logs = PatientIntakeModel::select('status','date')
			->where('patient_id', $id)
			->where('is_active','Y')
			->orderBy('date','asc')
			->get();
		
		if($category == "MDR"){
			$arr = $this->MDR($date);
		}else if($category == "Cat II"){
			$week = date('l', strtotime($date));
			if($week == "Monday" || $week == "Tuesday" || $week == "Thursday"){
				$arr = $this->CatII($date, date('Y-m-d', strtotime($date ."+34 weeks")), 3);
			}else if($week == "Wednesday" || $week == "Friday"){
				$arr = $this->CatII($date, date('Y-m-d', strtotime($date ."+34 weeks")), 6);
			}
		}else if($category == "Cat I"){
			$week = date('l', strtotime($date));
			if($week == "Friday"){
				$arr = $this->CatI($date, date('Y-m-d', strtotime($date . "+26 weeks")), 4);
			}else{
				$arr = $this->CatI($date, date('Y-m-d', strtotime($date . "+26 weeks")), 2);
			}
		}
		$weeknumber = 1;
		$newlist = array();
		$weekPercent = $this->getPercentage($category);
		$adder = 0;
		foreach($arr as $dates){
			foreach($logs as $log){
				if($dates == $log['date']){
					$week = date('W',strtotime($dates));
					if($log['status']=="Done"){
						$newlist[$week] = $weekPercent;
					}else{
						$newlist[$week] = 0.0;
					}
				}
			}
		}

		foreach($newlist as $data){
			$xaxis[] = "Week ".$weeknumber;
			$adder += $data;
			$series[] = array(
				"name" => "Week ".$weeknumber,
				"data" => number_format($adder,2)
			);
			$weeknumber++;
		}
		$this->response['status'] = (count($xaxis)>0)?true:false;
		$this->response['xaxis'] = $xaxis;
		$this->response['series'] = $series;
		
		return $this->container->response->withJson($this->response);
	}
	private function getPercentage($category){
		if($category=='Cat I'){
			$ret = 100/26;
		}else if($category=='Cat II'){
			$ret = 100/34;
		}else{
			$ret = 100/52;
		}
		return $ret;
	}
	private function getDateSchedule($category, $date){
		$arr = array();
		if($category == "MDR"){
			$arr = $this->MDR($date);
		}else if($category == "Cat II"){
			$week = date('l', strtotime($date));
			if($week == "Monday" || $week == "Tuesday" || $week == "Thursday"){
				$arr = $this->CatII($date, date('Y-m-d', strtotime($date ."+34 weeks")), 3);
			}else if($week == "Wednesday" || $week == "Friday"){
				$arr = $this->CatII($date, date('Y-m-d', strtotime($date ."+34 weeks")), 6);
			}
		}else if($category == "Cat I"){
			$week = date('l', strtotime($date));
			if($week == "Friday"){
				$arr = $this->CatI($date, date('Y-m-d', strtotime($date . "+26 weeks")), 4);
			}else{
				$arr = $this->CatI($date, date('Y-m-d', strtotime($date . "+26 weeks")), 2);
			}
		}
		return $arr;
	}
	private function MDR($date){
		$nxtYear = date("Y-m-d",strtotime($date.'+365 days'));
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

		for($x=0;$x<102;$x++){
			$datelist[] = $dates[$x];
		}
		return $datelist;
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
		for($x=0;$x<52;$x++){
			$datelist[] = $dates[$x];
		}
		return $datelist;
	}
	private function getEndDate($date,$category){
		if($category == 'MDR'){
			$enddate = date('Y-m-d',strtotime($date."+52 weeks"));
		}else if($category == "Cat II"){
			$enddate = date('Y-m-d',strtotime($date."+34 weeks"));
		}else if($category == "Cat I"){
			$enddate = date('Y-m-d',strtotime($date."+26 weeks"));
		}
		return $enddate;
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