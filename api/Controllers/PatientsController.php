<?php 
namespace Controllers;
use Models\PatientsModel;
use Models\PatientMedicinesModel;
use Models\UsersModel;
use Models\PatientLogsModel;
use Models\DiagnosticLogsModel;
use Models\PatientIntakeModel;
use Models\BarangayModel;
use Models\MedicinesModel;
use Models\BroadcastMessageModel;
use \Firebase\JWT\JWT;

class PatientsController{
	protected $container;

	private $response = array(
		"status" => false,
		"data" => array(),
		"message" => ""
	);
	
	function __construct($container){
		$this->container = $container;
	}

	public function PatientProfile($req, $res, $args){
		$Utils = new Utils();
		$user = $Utils->getPatientFromBearerToken($req, $this->container);
		$this->response['data'] = $user;
		$this->response['status'] = true;

		return $this->container->response->withJson($this->response);
	}
	public function patientAppList($req, $res, $args){
		$data = $req->getQueryParams();
		$date = (!empty($data['date'])) ? date('Y-m-d',strtotime($data['date'])) : date('Y-m-d');
		$ldate = date('l',strtotime($date));
		$search = $data['search'];

		$patientList = PatientsModel::where('status','Ongoing');
		if(isset($search) && $search != ""){
			$search = addslashes($search);
			$patientList = $patientList
				->whereRaw('(patient_id = "'.$search.'" or firstname = "'.$search.'" or lastname = "'.$search.'")');
		}
		$patientList = $patientList->where('datestart', '<=', $date);
		$patientList = $patientList->where('dateend', '>=', $date);
		$patientList = $patientList->get();
		$newList = array();
		foreach($patientList as $patient){
			if($patient['category'] == "MDR"){
				$stat = $this->MDR($date,$patient['datestart']);
				$patient['a'] = $stat;
			}else if($patient['category'] == "Cat II"){
				$week = date('l', strtotime($patient['datestart']));
				if($week == "Monday" || $week == "Tuesday" || $week == "Thursday"){
					$stat = $this->CatII($date, $patient['datestart'], date('Y-m-d', strtotime($patient['datestart'] ."+34 weeks")), 3);
				}else if($week == "Wednesday" || $week == "Friday"){
					$stat = $this->CatII($date, $patient['datestart'], date('Y-m-d', strtotime($patient['datestart'] ."+34 weeks")), 6);
				}
					$patient['a'] = $stat;
			}else if($patient['category'] == "Cat I"){
				$week = date('l', strtotime($patient['datestart']));
				if($week == "Friday"){
					$stat = $this->CatI($date, $patient['datestart'], date('Y-m-d', strtotime($patient['datestart'] . "+26 weeks")), 4);
				}else{
					$stat = $this->CatI($date, $patient['datestart'], date('Y-m-d', strtotime($patient['datestart'] . "+26 weeks")), 2);
				}
				$patient['a'] = $stat;
			}
			// print_r($patient['id']." - ".$patient['category']." - ".$stat."<br>");
			if($stat == "true"){
				$newList[] = $patient;
			}
		}
		if($patientList){
			$this->response['data'] = $newList;
			$this->response['status'] = true;
		}

		return $this->container->response->withJson($this->response);
	}
	public function PatientProfileSubmit($req, $res, $args){
		$body = $req->getParsedBody();
		$patient = PatientsModel::where('id',$body['id'])->update(array(
			'firstname' => $body['firstname'],
			'middlename' => $body['middlename'],
			'lastname' => $body['lastname'],
			'dateofbirth' => $body['dateofbirth'],
			'mobilenumber' => $body['mobilenumber'],
			'address' => $body['address'],
			'street' => $body['street'], 
			'barangay' => $body['barangay'],
			'city' => $body['city']
		));

		if($patient){
			$this->response['status'] = true;
		}

		return $this->container->response->withJson($this->response);
	}
	public function MessagePatients($req, $res, $args){
		$body = $req->getParsedBody();
		$receivers = explode(" ",$body['receiver']);
		$message = $body['message'];

		$Utils = new Utils();
		$user = $Utils->getUserFromBearerToken($req, $this->container);
		$chatController = new ChatController($this->container);
		
		$patient = PatientsModel::select('mobilenumber')->where('status',$receivers[0])->get();
		$savemessage = BroadcastMessageModel::create(array(
			'receiver' => $body['receiver'],
			'message' => $message,
			'sender' => $user['lastname'].", ".$user['firstname']
		));
		if($savemessage){
			$y = 0;
			foreach($patient as $p){
				$y++;
				if($y<4){
					$contactNumber = str_replace('+63','0',$p['mobilenumber']);
					$messageSubmit = $chatController->messageSend($contactNumber, $message);
				}
			}
			$this->response['status'] = true;
		}
		
		return $this->container->response->withJson($this->response);

	}
	public function countPatientForMessage($req, $res, $args){
		$param = $req->getQueryParams();
		$status = explode(" ",$param['status']);
		
		$patientCount = PatientsModel::selectRaw('count(id) as count')
			->where('status',$status[0])
			->first();
		if($patientCount){
			$this->response['status'] = true;
			$this->response['data'] = $patientCount['count'];
		}
		return $this->container->response->withJson($this->response);
	}
	public function countPatients($req, $res, $args){
		$param = $req->getQueryParams();
		$d = explode(" ~ ",$param['date']);
		$datefrom = (!empty($param['date'])) ? date("Y-m-d", strtotime($d[0])) : date("Y-m-d", strtotime(date('Y-m-d') . "-1 year"));
		$dateto = (!empty($param['date'])) ? date("Y-m-d", strtotime($d[1])) : date("Y-m-d");
		$status = $param['status'];
		$category = $param['category'];

		$patients = PatientsModel::select('status')->whereBetween('consultationdate', [$datefrom, $dateto]);
		if($status != 'All'){
			$patients = $patients->where('status',$status);
		}
		if($category != 'All'){
			$patients = $patients->where('category',$category);
		}

		$patients = $patients->get();
		$arrstatus = array(0,0,0,0);

		foreach($patients as $patient){
			if($patient['status'] == 'New'){
				$arrstatus[0] = $arrstatus[0]+1;
			}else if($patient['status'] == 'Ongoing'){
				$arrstatus[1] = $arrstatus[1]+1;
			}else if($patient['status'] == 'Success'){
				$arrstatus[2] = $arrstatus[2]+1;
			}else{
				$arrstatus[3] = $arrstatus[3]+1;
			}
		}
		$this->response['date_range'] = date("M d, Y",strtotime($datefrom))." to ".date("M d, Y",strtotime($dateto));
		if($patients){
			$this->response['data'] = $arrstatus;
			$this->response['status'] = true;
		}

		return $this->container->response->withJson($this->response);
	}

	public function addEditPatient($request, $response, $args){
		$body = $request->getParsedBody();

		$Utils = new Utils();
		$user = $Utils->getUserFromBearerToken($request, $this->container);
		
		$generateChars = $this->container['generateRandomChars'];
		$token = $generateChars(22);

		$passwordConverter = $this->container['passwordConverter'];
		$password = $passwordConverter($body['password']);
		
		if($body['type'] == "add"){
			$checkUser = PatientsModel::select("username")->where('username',$body['username'])->first();

			if($checkUser){
				$this->response['message'] = "Username already used!";
				$this->response['status'] = "false";
			}else{
				$lastID = PatientsModel::select('patient_id')->latest()->first();
				if($lastID){
					$patientid = substr($lastID['patient_id'],2) + 1;
					$patientid = date("y").str_pad($patientid,4,"0",STR_PAD_LEFT);
				}else{
					$patientid = date("y").str_pad("1",4,"0",STR_PAD_LEFT);
				}
				$patientCreate = PatientsModel::create(array(
					"patient_id" => $patientid,
					"firstname" => ucwords(strtolower($body['firstname'])),
					"middlename" => ucwords(strtolower($body['middlename'])),
					"lastname" => (isset($body['lastname'])) ? ucwords(strtolower($body['lastname'])) : '',
					"dateofbirth" => $body['dateofbirth'],
					"doctor_id" => $body['doctorid'],
					"consultationdate" => $body['consultationdate'],
					"gender" => $body['gender'],
					"mobilenumber" => $body['mobilenumber'],
					"drtb" => $body['drtb'],
					"category" => $body['category'],
					"address" => ucwords(strtolower($body['address'])),
					"street" => ucwords(strtolower($body['street'])),
					"barangay" => ucwords(strtolower($body['barangay'])),
					"city" => ucwords(strtolower($body['city'])),
					"remarks" => (!empty($body['remarks']) || $body['remarks'] != 'undefined' ||  $body['remarks'] != 'null') ? $body['remarks'] : "",
					"username" => $body['username'],
					"password" => $password,
					"token" => $token));
				$id = $patientCreate->id;
				$patientLogs = PatientLogsModel::create(array(
					"uid" => $id,
					"status" => "New",
					"updated_by" => $user['lastname'].", ".$user['firstname']
				));
				$this->response['data'] = $patientid;
				$this->response['status'] = true;
				$this->response['message'] = "Successfully Created";
			}
		}else{
			$checkUser = PatientsModel::select("username")->where('username',$body['username'])->where('id', '!=',$body['id'])->first();
			if($checkUser){
				$this->response['message'] = "Username already used!";
				$this->response['status'] = "false";
			}else{
				$patientEdit = PatientsModel::where('id',$body['id'])
					->update(array(
					"firstname" => ucwords(strtolower($body['firstname'])),
					"middlename" => ucwords(strtolower($body['middlename'])),
					"lastname" => ucwords(strtolower($body['lastname'])),
					"dateofbirth" => $body['dateofbirth'],
					"consultationdate" => $body['consultationdate'],
					"gender" => $body['gender'],
					"mobilenumber" => $body['mobilenumber'],
					"drtb" => $body['drtb'],
					"category" => $body['category'],
					"address" => $body['address'],
					"street" => $body['street'],
					"barangay" => $body['barangay'],
					"city" => $body['city'],
					"remarks" => (!empty($body['remarks']) || $body['remarks'] != 'undefined' ||  $body['remarks'] != 'null') ? $body['remarks'] : "",
					"username" => $body['username'],
					"password" => $password
				));
				$this->response['status'] = true;
				$this->response['message'] = "Successfully Edited!";
			}
		}
		return $this->container->response->withJson($this->response);
	}
	public function patientList($requests, $response, $args) {
		$body = $requests->getQueryParams();
		$Utils = new Utils();
		$user = $Utils->getUserFromBearerToken($requests, $this->container);
		
		$sort = $_GET['sort'];
		$status = $_GET['status'];
		$limit = 20;
		$offset = ($_GET['page'] - 1) * $limit;
		$patientList = PatientsModel::select("id","patient_id","firstname","middlename","lastname","username","dateofbirth","consultationdate","doctor_id","gender","mobilenumber","status","drtb","category","address","street","barangay","city","remarks","datestart","dateend");

		$patientCount = PatientsModel::selectRaw("count(id) as count");
		if(!empty($status)){
			$patientList = $patientList
			->where('status',$status);
			$patientCount = $patientCount
			->where('status',$status);
		}

		if(!empty($sort)){
			$patientList = $patientList
			->orderBy('lastname', $sort);
		}
		if($user['usertype'] == 2){
			$patientList = $patientList
				->where('doctor_id',$user['id']);
			$patientCount = $patientCount
				->where('doctor_id',$user['id']);
		}
		if(!empty($_GET['search'])){
			$patientList = $patientList
				->whereRaw('(patient_id LIKE "%'.$_GET['search'].'%" or
				firstname LIKE "%'.$_GET['search'].'%" or
				middlename LIKE "%'.$_GET['search'].'%" or
				lastname LIKE "%'.$_GET['search'].'%")');
			$patientCount = $patientCount
				->whereRaw('(patient_id LIKE "%'.$_GET['search'].'%" or
				firstname LIKE "%'.$_GET['search'].'%" or
				middlename LIKE "%'.$_GET['search'].'%" or
				lastname LIKE "%'.$_GET['search'].'%")');
			
		}else{
			if(empty($status)){
				$patientList = $patientList
				->whereRaw("status in ('New', 'Ongoing')")
				->offset($offset)
				->limit($limit);
				$patientCount = $patientCount
				->whereRaw("status in ('New', 'Ongoing')");
			}
		}

		$patientList = $patientList
			->orderBy('patient_id','desc')
			->limit($limit)
			->get();
		$patientCount = $patientCount->first();

		$this->response['count'] = $patientCount;
		$this->response['data'] = $patientList;
		$this->response['status'] = true;
		return $this->container->response->withJson($this->response);
	}
	public function getLastID($req, $res, $args){
		$lastID = PatientsModel::select('patient_id')->latest()->first();
		if($lastID){
			$this->response['data'] = $lastID;
			$this->response['status'] = true;
		}
		return $this->container->response->withJson($this->response);
	}
	public function DoctorAssign($req, $res, $args){
		$body = $req->getParsedBody();
		PatientsModel::where('id',$body['patientID'])->update(array('doctor_id' => $body['doctorid']));

		$this->response['message'] = "Successfully Assigned!";
		$this->response['status'] = true;
		
		return $this->container->response->withJson($this->response);
	}
	public function fetchDoctor($req, $res, $args){
		$doctorid = PatientsModel::select('doctor_id')
			->where('id', $args['id'])
			->first();
		$this->response['data'] = $doctorid;
		$this->response['status'] = true;
		return $this->container->response->withJson($this->response);
	}
	public function countIntake($req, $res, $args){
		$param = $req->getQueryParams();
		$id = $param['id'];
		$category = $param['category'];
		$intake = PatientIntakeModel::selectRaw('count(id) as count')->where('patient_id',$id)->first();
		switch ($category) {
			case "Cat I" : 
				$stat = ($intake['count']>='52') ? true : false;
			break;
			case "Cat II" :
				$stat = ($intake['count']>='102') ? true : false;
			break;
			case "MDR" :
				$stat = ($intake['count']>='360') ? true : false;
			break;
			default :
				$stat = false;
		}
		$this->response['status'] = $stat;
		$this->response['intake'] = $category;
		return $this->container->response->withJson($this->response);
	}
	public function changePatientStatus($req, $res, $args){
		$Utils = new Utils();
		$user = $Utils->getUserFromBearerToken($req, $this->container);
		
		$body = $req->getParsedBody();
		$id = $args['id'];
		$reason = (isset($body['reason'])) ? $body['reason'] : "";

		$patient = PatientsModel::select("patient_id")->where('id',$id)->first();
		$file = null;
		if(isset($_FILES['imageFile'])){
			$file = $_FILES['imageFile'];
			$imgUploadResponse = $this->uploadImage($file, "Other" ,$patient['patient_id']);
		}

		$imgpath = null;
		if(isset($imgUploadResponse['imageLocation'])){
			$imgpath = $imgUploadResponse['imageLocation'];
			$dag = new DiagnosticLogsModel;
			$dag->patient_id = $id;
			$dag->diagnostic_type = 'Other';
			$dag->examination_date = date('Y-m-d');
			$dag->remarks = $reason;
			$dag->image_location = $imgpath;
			$dag->save();
		}
		PatientsModel::where('id',$id)->update(array('status' => $body['status']));
		PatientLogsModel::create(array(
			'uid' => $id,
			'status' => $body['status'],
			'reason' => $reason,
			'updated_by' => $user['lastname'].", ".$user['firstname']
		));
		if($body['status']=='New'){
			PatientMedicinesModel::where('uid',$id)->update(array('is_active' => 'N'));
			PatientIntakeModel::where('patient_id',$id)->update(array('is_active' => 'N'));
		}
		$this->response['message'] = "Successfully Changed!";
		$this->response['status'] = true;
		return $this->container->response->withJson($this->response);
	}
	public function fetchDiagnosticResults($req, $res, $args){
		$data = $req->getQueryParams();
		$limit = 8;
		$offset = ($data['page'] - 1) * $limit;
		$patientDiagnostic = DiagnosticLogsModel::where('patient_id', $data['id'])
			->where('is_active','Y')
			->orderBy('created_at', 'desc')
			->offset($offset)
			->limit($limit);
		$diagnosticCount = DiagnosticLogsModel::selectRaw("count(id) as count")
			->where('patient_id',$data['id'])
			->where('is_active','Y')
			->first();
		$patientDiagnostic = $patientDiagnostic->get();
		$this->response['count'] = $diagnosticCount;
		$this->response['data'] = $patientDiagnostic;
		$this->response['status'] = true;
		return $this->container->response->withJson($this->response);
	}
	public function uploadResult($req, $res, $args){
		$body = $req->getParsedBody();
		$patient = PatientsModel::select("patient_id")->where('id',$body['patientid'])->first();
		$diagnostic = explode(" ",$body['diagnostictype']);
		
		$file = null;
		if(isset($_FILES['imageFile'])){
			$file = $_FILES['imageFile'];
			$imgUploadResponse = $this->uploadImage($file, $diagnostic[0] ,$patient['patient_id']);
		}

		$imgpath = null;
		if(isset($imgUploadResponse['imageLocation'])){
			$imgpath = $imgUploadResponse['imageLocation'];
		}
		DiagnosticLogsModel::create(array(
			'patient_id' => $body['patientid'],
			'diagnostic_type' => $body['diagnostic'],
			'examination_date' => $body['examinationdate'],
			'remarks' => $body['remarks'],
			'image_location' => $imgpath	
		));
		$this->response['message'] = "Diagnostic result has been uploaded!";
		$this->response['status'] = true;
		return $this->container->response->withJson($this->response);
	}

	public function uploadImage($file, $diagnostictype, $patientid){
		$uploadOk = 1;
		$date = date("Y-m-d");
		if(!isset($file['name'])){
			$error['status']="error";
			$error['msg']="Encountered an error while uploading the image.";
			echo json_encode($error);
		}else{
			$uploadDirectory = $this->container["settings"]["uploadDirectory"];
			$baseUrl = $this->container["settings"]["baseUrl"];
			if($file['error']==0){
				$fileType = explode("/",$file['type']);
				if(move_uploaded_file($file['tmp_name'], $uploadDirectory."/".$diagnostictype."/".$patientid."(".$date.").".$fileType[1])==true){
					$error['status']="success";
					$error['msg']="The image has been uploaded.";
					$error['imageLocation'] = $baseUrl."/uploads/".$diagnostictype."/".$patientid."(".$date.").".$fileType[1];
				}else{
					$error['status']="error";
					$error['msg']="Sorry, there was an error uploading the image.";
				}
			}
			return $error;
		}
	}
	public function checkLaboratory($req, $res, $args){
		$id = $args['id'];
		$res = DiagnosticLogsModel::where('patient_id',$id)
			->where('is_active','Y')
			->first();
		
		if($res){
			$this->response['status'] = true;
		}

		return $this->container->response->withJson($this->response);
	}
	public function fetchPatientFile($req, $res, $args){
		$examType = array("Sputum Examination","CXR Examination","TST Examination", "Other Examination Result");
		$examList = 
			array( 
				array(
					"name" => "Sputum Examination",
					"children" => array()
				),
				array(
					"name" => "CXR Examination",
					"children" => array()
				),
				array(
					"name" => "TST Examination",
					"children" => array()
				),
				array(
					"name" => "Other Examination Result",
					"children" => array()
				)
			);
		$Utils = new Utils();
		$user = $Utils->getPatientFromBearerToken($req, $this->container);
		$patientFile = DiagnosticLogsModel::select('diagnostic_type','image_location','remarks')
			->where('patient_id',$user['id'])
			->where('is_active','Y')
			->get();
		$count = 0;
		foreach($examType as $type){
			foreach($patientFile as $file){
				$filedate = explode("(", $file['image_location']);
				if(!in_array($file['diagnostic_type'], $examType)){
					$forCheck = array();
					foreach($examList[3]['children'] as $list){
						array_push($forCheck,$list['name']);
					}
					if(!in_array($file['diagnostic_type']."(".$filedate[1], $forCheck)){
						array_push($examList[3]['children'], array ("name" => $file['diagnostic_type']."(".$filedate[1], "file" => "image", "location" => $file['image_location'], "remarks" => $file['remarks']));
					}

				}else if($type == $file['diagnostic_type']){
					array_push($examList[$count]['children'], array ("name" => $file['diagnostic_type']."(".$filedate[1] ,"file" => "image", "location" => $file['image_location'], "remarks" => $file['remarks']));
				}
			}
			$count++;
		}
		return $this->container->response->withJson($examList);
	}

	private function getAge($birthdate){
		$today = date_create(date('Y-m-d'));
		$bdate = date_create($birthdate);
		$datediff = date_diff($bdate, $today);
		return $datediff->y;
	}

	public function fetchIntakeLogs($req, $res, $args){
		$params = $req->getQueryParams();
		$id = $params['id'];
		$d = explode(" ~ ",$params['date']); 
		$datefrom = ($params['date'] == '') ? date('Y-m-d', strtotime($d[0])) : '';
		$dateto = ($params['date'] != '') ? date('Y-m-d', strtotime($d[1])) : '';
		
		$log = PatientIntakeModel::selectRaw('status,date,created_at,reason,distributor')
			->where('patient_id',$id);

		if(!empty($datefrom) && !empty($dateto)){
			$log = $log->whereRaw('`date` between "'.$datefrom.'" and "'.$dateto.'"');
		}
		$log = $log->orderBy('created_at','desc')->get();

		if(count($log) > 0){
			$this->response['data'] = $log;
			$this->response['status'] = true;
		}
		return $this->container->response->withJson($this->response);
	}

	public function fetchOutcomes($req, $res, $args){
		$param = $req->getQueryParams();
		$d = explode(" ~ ",$param['date']); 
		$today = ($param['date'] == '') ? date('Y-m-d') : date('Y-m-d', strtotime($d[1]));
		$datefrom = ($param['date'] == '') ? date('Y-m-d', strtotime($today . "-11 months")) : date('Y-m-d', strtotime($d[0]));
		$status = $param['status'];
		$category = $param['category'];

		$d1 = date_create($today);
		$d2 = date_create($datefrom);
		$ddiff = date_diff($d1,$d2); 

		$patientlist = array();
		$monthlist = array();
		$exp = explode('-',$today);
		for($x=0; $x <= $ddiff->format('%m'); $x++){
			$aaa = date('M',strtotime($exp[0]."-".$exp[1]." -$x months"));
			$d1->modify("-1 month");
			if(!in_array($d1->format('M'), $monthlist)){
				array_unshift($monthlist, $aaa);	
			}
			if(date('M',strtotime($datefrom)) == date('M',strtotime($today))){
				break;
			}
		}
		$arraylist = array();
		for($x=0; $x<count($monthlist);$x++){
			array_push($arraylist,0);
		}
		$patientlistqry = PatientsModel::select('patient_id','firstname','middlename','lastname','consultationdate','status','category')
			->whereRaw('consultationdate between "'.$datefrom.'" and "'.$today.'"');
		if($status!='All'){
			$patientlistqry = $patientlistqry->where('status', $status);
		}
		if($category!='All'){
			$patientlistqry = $patientlistqry->where('category',$category);
		}
		$patientlist = $patientlistqry->orderBy('firstname','asc')->get();
		$arrstat = array();
		foreach($monthlist as $month){
			$arrstat[$month] = 0;		
		}
		foreach($patientlist as $patient){
			$mo = date('M',strtotime($patient['consultationdate']));
			$arrstat[$mo] += 1;
		}

		$this->response['data'] = $arrstat;
		$this->response['months'] = $monthlist;

		return $this->container->response->withJson($this->response);
	}
	public function fetchPatientList($req, $res, $args){
		$params = $req->getQueryParams();
		$d = explode(" ~ ",$params['date']);
		$datefrom = (!empty($params['date'])) ? date("Y-m-d", strtotime($d[0])) : date("Y-m-d", strtotime(date('Y-m-d') . "-1 year"));
		$dateto = (!empty($params['date'])) ? date("Y-m-d", strtotime($d[1])) : date("Y-m-d");
		$category = $params['category'];
		$status = $params['status'];
		$patients = PatientsModel::select('patient_id','firstname','middlename','lastname','consultationdate','status','category')
			->whereRaw('consultationdate between "'.$datefrom.'" and "'.$dateto.'"');

		if($category!='All'){
			$patients = $patients->where('patients.category',$category);
		}
		if($status!='All'){
			$patients = $patients->where('patients.status',$status);
		}
			
		$patients = $patients->orderBy('firstname','desc')->get();
		$this->response['data'] = $patients;
		return $this->container->response->withJson($this->response);
	}
	public function FetchBarangayList($req, $res, $args){
		$barangayList = BarangayModel::select('barangay')->get();
		$this->response['data'] = $barangayList;
		$this->response['status'] = true;
		return $this->container->response->withJson($this->response);
	}
	public function PatientAppMedicine($req, $res, $args){
		$body = $req->getQueryParams();
		$Utils = new Utils();
		$user = $Utils->getPatientFromBearerToken($req, $this->container);
		$id = (isset($body['id'])) ? $body['id'] : $user['id'];

		$date = (isset($body['date']))? date('Y-m-d',strtotime($body['date'])) : date('Y-m-d');
		$medicine = MedicinesModel::select('medicines.id','brandname','genericname')
			->join('patient_medicine','medicines.id','=','patient_medicine.medicineid')
			->where('uid',$id)
			->where('patient_medicine.is_active','Y');
		$medicine = $medicine->get();
		$intakelogs = PatientIntakeModel::select('date', 'status', 'reason', 'distributor')
			->where('patient_id',$id)
			->where('date', $date)
			->orderBy('id','desc')
			->first();

		$medicineinfo['list'] = $medicine;
		$medicineinfo['status'] = ($intakelogs['status']!=null) ? $intakelogs['status'] : 'None';
		$this->response['data'] = $medicineinfo;
		$this->response['status'] = true;
		
		return $this->container->response->withJson($this->response);
	}

	public function checkPatient($req, $res, $args){
		$data = $req->getQueryParams();
		$stat = PatientsModel::select('status')->where('id', $data['id'])->first();
		$diagnostic = DiagnosticLogsModel::select('id')
			->where('patient_id', $data['id'])
			->where('is_active','Y')
			->first();
		$this->response['data']["prerequisite"] = "diagnostic";
		$this->response['patient'] = $stat['status'];
		if($diagnostic){
			$patientMedicine = PatientMedicinesModel::select('id')
			->where('uid', $data['id'])
			->where('is_active','Y')
			->first();
			$this->response['data']["prerequisite"] = "medicine";
			if($patientMedicine){
				$this->response['data'] = "";
				$this->response['status'] = true;
			}
		}
		return $this->container->response->withJson($this->response);
	}
	public function logStatus($req, $res, $args){
		$body = $req->getQueryParams();
		$logs = PatientLogsModel::select('status','reason','updated_by','created_at')
				->where('uid',$body['id'])
				->orderBy('id','desc')
				->first();
			if($logs){
				$this->response['data'] = $logs;
				$this->response['status'] = true;
			}
		return $this->container->response->withJson($this->response);
	}
	private function MDR($datetoday, $datestart){
		$nxtYear = date("Y-m-d",strtotime($datestart.'+1 year'));
		$str = strtotime($nxtYear) - strtotime($datestart);
		$diff = floor($str/3600/24);
		$status = "false";
		for($x = 0; $x < $diff; $x++){
			$day = date('Y-m-d', strtotime($datestart . "+$x days"));
			if($datetoday == $day){
				$status = "true";
				break;
			}
		}
		return $status;
	}

	private function CatII($datetoday, $dateFromString, $dateToString, $loop){
		$y = 0;
		$datetoday = '2020-07-17';
		$status = "false";
		for($x=0;$x<$loop;$x++){
			$day = date('Y-m-d',strtotime($dateFromString." +$y days"));
			if($day == $datetoday){
				$status = "true";
				break;
			}
			$date = date('Y-m-d',strtotime($dateFromString." +$y days"));
			$y = $y+2;
		}
		$y = 0;
		if($status == "false"){
			if ("Monday" != date('l',strtotime($date))) {
				$date = date('Y-m-d',strtotime($date . "next monday"));
			}
			for($x=0;$x<3;$x++){
				$incrementdate = date('Y-m-d',strtotime($date." +$y days"));
				$dateFrom = new \DateTime(date('Y-m-d',strtotime($incrementdate)));
				$dateTo = new \DateTime($dateToString);
				
				while ($dateFrom <= $dateTo) {
					if($incrementdate == $datetoday){
						$status = "true";
						break;
					}
					$dateFrom->modify('+1 week');
				}
				$y = $y+2;
			}
		}
		return $status;
	}
	
	private function CatI($datetoday, $dateFromString, $dateToString,$loop){
		$y = 0;
		$datetoday = '2020-07-17';
		$month = date('m',strtotime($dateFromString));
		$status = "false";
		$date = "";
		for($x=0;$x<$loop;$x++){
			$day = date('Y-m-d',strtotime($dateFromString." +$y days"));
			if($day == $datetoday){
				$status = "true";
				break;
			}
			$date = date('Y-m-d',strtotime($dateFromString." +$y days"));
			$y = $y+2;
		}
		$y = 0;
		if($status == "false"){
			if ("Monday" != date('l',strtotime($date))) {
				$date = date('Y-m-d',strtotime($date . "next monday"));
			}
			for($x=0;$x<2;$x++){
				$incrementdate = date('Y-m-d',strtotime($date." +$y days"));
				$dateFrom = new \DateTime(date('Y-m-d',strtotime($incrementdate)));
				$dateTo = new \DateTime($dateToString);
				
				while ($dateFrom <= $dateTo) {
					if($incrementdate == $datetoday){
						$status = "true";
						break;
					}
					$dateFrom->modify('+1 week');
				}
				$y = $y+2;
			}
		}
		return $status;
	}
}