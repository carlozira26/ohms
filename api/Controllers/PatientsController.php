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
		$ldate = date('l',strtotime(date('Y-m-d')));
		$search = $data['search'];
		$patientList = PatientsModel::where('status','Ongoing')
			->where('medicine_schedule','like','%'.$ldate.'%');
		if(isset($search) && $search != ""){
			$patientList = $patientList->where('patient_id',$search);
			$patientList = $patientList->orWhere('firstname',$search);
			$patientList = $patientList->orWhere('lastname',$search);
		}
		$patientList = $patientList->get();
		if($patientList){
			$this->response['data'] = $patientList;
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
	public function countPatients($req, $res, $args){
		$d = explode(" ~ ",$_GET['date']);
		$datefrom = (!empty($_GET['date'])) ? date("Y-m-d", strtotime($d[0])) : date("Y-m-d", strtotime(date('Y-m-d') . "-1 year"));
		$dateto = (!empty($_GET['date'])) ? date("Y-m-d", strtotime($d[1])) : date("Y-m-d");

		$patients = PatientsModel::select('status')->whereBetween('consultationdate', [$datefrom, $dateto])->get();
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
				$patientCreate = PatientsModel::create(array(
					"patient_id" => $body['patientid'],
					"firstname" => ucwords(strtolower($body['firstname'])),
					"middlename" => ucwords(strtolower($body['middlename'])),
					"lastname" => ucwords(strtolower($body['lastname'])),
					"dateofbirth" => $body['dateofbirth'],
					"consultationdate" => $body['consultationdate'],
					"gender" => $body['gender'],
					"mobilenumber" => $body['mobilenumber'],
					"drtb" => $body['drtb'],
					"address" => ucwords(strtolower($body['address'])),
					"street" => ucwords(strtolower($body['street'])),
					"barangay" => ucwords(strtolower($body['barangay'])),
					"city" => ucwords(strtolower($body['city'])),
					"remarks" => (!empty($body['remarks']) && $body['remarks'] != 'undefined') ? $body['remarks'] : NULL,
					"username" => $body['username'],
					"password" => $password,
					"token" => $token));
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
					"remarks" => $body['remarks'],
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
		$patientList = PatientsModel::select("id","patient_id","firstname","middlename","lastname","username","dateofbirth","consultationdate","doctor_id","gender","mobilenumber","status","drtb","category","address","street","barangay","city","remarks");

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
		return $this->container->response->withJson($lastID);
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
	public function changePatientStatus($req, $res, $args){
		$Utils = new Utils();
		$user = $Utils->getUserFromBearerToken($req, $this->container);
		
		$body = $req->getParsedBody();
		$id = $args['id'];

		$reason = (isset($body['reason'])) ? $body['reason'] : ""; 
		PatientsModel::where('id',$id)->update(array('status' => $body['status']));
		PatientMedicinesModel::where('uid',$id)->update(array('is_active' => 'N'));
		PatientLogsModel::create(array(
			'uid' => $id,
			'status' => $body['status'],
			'reason' => $reason,
			'updated_by' => $user['lastname'].", ".$user['firstname']
		));
		$this->response['message'] = "Successfully Changed!";
		$this->response['status'] = true;
		return $this->container->response->withJson($this->response);
	}
	public function fetchDiagnosticResults($req, $res, $args){
		$data = $req->getQueryParams();
		$limit = 8;
		$offset = ($data['page'] - 1) * $limit;
		$patientDiagnostic = DiagnosticLogsModel::where( 'patient_id', $data['id'] )
			->orderBy('created_at', 'desc')
			->offset($offset)
			->limit($limit)
			->get();
		$diagnosticCount = DiagnosticLogsModel::selectRaw("count(id) as count")->where( 'patient_id',$data['id'] )->first();
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
		$res = DiagnosticLogsModel::where('patient_id',$id)->first();
		
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
		$patientFile = DiagnosticLogsModel::select('diagnostic_type','image_location','remarks')->where('patient_id',$user['id'])->get();
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

	public function fetchInfected($req, $res, $args){
		$body = $req->getQueryParams();
		$dates = (!empty($body['date'])) ? explode(" ~ ",$body['date']) : array(date('Y-01-01'), date('Y-12-31'));
		$datefrom = date('Y-m-d', strtotime($dates[0]));
		$dateto = date('Y-m-d', strtotime($dates[1]));
		
		$patients = PatientsModel::whereBetween('consultationdate',[$datefrom,$dateto])->get();
		$agelist = array(0,0,0,0,0);
		
		foreach($patients as $patient){
			if($this->getAge($patient['dateofbirth']) <= 17){
				$agelist[0] += 1;
			}else if ($this->getAge($patient['dateofbirth']) > 17 && $this->getAge($patient['dateofbirth']) <= 25){
				$agelist[1] += 1;
			}else if ($this->getAge($patient['dateofbirth']) > 25 && $this->getAge($patient['dateofbirth']) <=35){
				$agelist[2] += 1;
			}else if ($this->getAge($patient['dateofbirth']) > 35 && $this->getAge($patient['dateofbirth']) <= 40){
				$agelist[3] += 1;
			}else{
				$agelist[4] += 1;
			}
		}
		return $this->container->response->withJson($agelist);
	}

	private function getAge($birthdate){
		$today = date_create(date('Y-m-d'));
		$bdate = date_create($birthdate);
		$datediff = date_diff($bdate, $today);
		return $datediff->y;
	}

	public function fetchIntakeLogs($req, $res, $args){
		$id = $_GET['id'];
		$log = PatientIntakeModel::selectRaw('status,date,created_at,concat(brandname,":",genericname) as medicine,reason')
			->join('medicines','medicines.id','=','patient_intake.medicineid')
			->where('patient_id',$id)
			->orderBy('created_at','desc')
			->get();

		if(count($log) > 0){
			$this->response['data'] = $log;
			$this->response['status'] = true;
		}
		return $this->container->response->withJson($this->response);
	}

	public function fetchOutcomes($req, $res, $args){
		$d = explode(" ~ ",$_GET['date']); 
		$today = ($_GET['date'] == '') ? date('Y-m-d') : date('Y-m-d', strtotime($d[1]));
		$datefrom = ($_GET['date'] == '') ? date('Y-m-d', strtotime($today . "-11 months")) : date('Y-m-d', strtotime($d[0]));
		
		$d1 = date_create($today);
		$d2 = date_create($datefrom);
		$ddiff = date_diff($d1,$d2); 

		$patientlist = array();
		$monthlist = array();
		for($x=0; $x < $ddiff->format('%m') +1 ; $x++){
			array_unshift($monthlist,date('M',strtotime($today." -".$x." month")));
		}
		$arraylist = array();
		for($x=0; $x<count($monthlist);$x++){
			array_push($arraylist,0);
		}
		$arrstat = array(
			'New' => $arraylist,
			'Ongoing' => $arraylist,
			'Success' => $arraylist,
			'Discontinuation' => $arraylist
		);

		$patientlistqry = PatientLogsModel::selectRaw("uid,status,cast(created_at as date) as date")
			->whereRaw('cast(created_at as date) between "'.$datefrom.'" and "'.$today.'"')
			->orderBy('created_at');
		$patientlistqryget = $patientlistqry->get();
		$patientlistqrysql = $patientlistqry->toSql();

		$this->response['sql'] = $patientlistqrysql;
		foreach($patientlistqryget as $patients){
			$patientlist[$patients['uid']]['status'][] = $patients['status'];
			$patientlist[$patients['uid']]['dates'][] = $patients['date'];
		}

		foreach($patientlist as $key=>$patient){
			if(in_array('Discontinuation',$patient['status'])){
				$date = $patient['dates'][array_search('Discontinuation',$patient['status'])];
				$mo = array_search( date('M', strtotime($date)),$monthlist);
				
				$arrstat['Discontinuation'][$mo] = $arrstat['Discontinuation'][$mo] + 1;
			}if(in_array('Success',$patient['status'])){
				$date = $patient['dates'][array_search('Success',$patient['status'])];
				$mo = array_search( date('M', strtotime($date)),$monthlist);
				
				$arrstat['Success'][$mo] = $arrstat['Success'][$mo] + 1;
			}if(in_array('Ongoing',$patient['status'])){
				$checks = in_array('Success',$patient['status']);
				$checkd = in_array('Discontinuation',$patient['status']);
				if($checks){
					$successm = date('n', strtotime($patient['dates'][array_search('Success',$patient['status'])]));
					$successmo = date_create($patient['dates'][array_search('Success',$patient['status'])]);
					$ongoingmo = date_create($patient['dates'][array_search('Ongoing',$patient['status'])]);
					$diff = date_diff($successmo,$ongoingmo)->format('%m');
					for($x=$successm-1; $x > ($successm - $diff) ; $x--){
						$oldMonth =  date('Y-m-d', strtotime($patient['dates'][array_search('Success',$patient['status'])] . "-$x month"));
						$mo = array_search( date('M', strtotime($oldMonth)), $monthlist);
					}
				}
				if($checkd){
					$discontm = date('n', strtotime($patient['dates'][array_search('Discontinuation',$patient['status'])]));
					$discontmo = date_create($patient['dates'][array_search('Discontinuation',$patient['status'])]);
					$ongoingmo = date_create($patient['dates'][array_search('Ongoing',$patient['status'])]);
					$diff = date_diff($successmo,$ongoingmo)->format('%m');
						
					for($x=$successm-1; $x > ($successm - $diff) ; $x--){
						$oldMonth =  date('Y-m-d', strtotime($patient['dates'][array_search('Discontinuation',$patient['status'])] . "-$x month"));
						$mo = array_search( date('M', strtotime($oldMonth)), $monthlist);
						$arr['Ongoing'][$mo] = $arr['Ongoing'][$mo] + 1;
					}
				}
				$date = $patient['dates'][array_search('Ongoing',$patient['status'])];
				$mo = array_search( date('M', strtotime($date)),$monthlist);
				
				$arrstat['Ongoing'][$mo] = $arrstat['Ongoing'][$mo] + 1;
			}if(in_array('New',$patient['status'])){
				$date = $patient['dates'][array_search('New',$patient['status'])];
				$mo = array_search( date('M', strtotime($date)),$monthlist);
				$arrstat['New'][$mo] = $arrstat['New'][$mo] + 1;		
			}
		}
		$this->response['data'] = $arrstat;
		$this->response['months'] = $monthlist;

		return $this->container->response->withJson($this->response);
	}
	public function fetchPatientList($req, $res, $args){
		$d = explode(" ~ ",$_GET['date']);
		$datefrom = (!empty($_GET['date'])) ? date("Y-m-d", strtotime($d[0])) : date("Y-m-d", strtotime(date('Y-m-d') . "-1 year"));
		$dateto = (!empty($_GET['date'])) ? date("Y-m-d", strtotime($d[1])) : date("Y-m-d");

		$patients = PatientsModel::select('patient_id','firstname','middlename','lastname','patient_logs.created_at','patient_logs.status')
			->join('patient_logs','patient_logs.uid','patients.id')
			->whereRaw('cast(patient_logs.created_at as date) between "'.$datefrom.'" and "'.$dateto.'"')
			->orderBy('created_at','desc')->get();
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

		$date = (isset($body['date']))? $body['date'] : date('Y-m-d');
		$medicine = MedicinesModel::select('medicines.id','brandname','genericname')
			->join('patient_medicine','medicines.id','=','patient_medicine.medicineid')
			->where('uid',$id)
			->where('patient_medicine.is_active','Y')
			->get();

		$intakelogs = PatientIntakeModel::select('medicineid','status','reason')
			->where('patient_id',$id)
			->where('date', $date)
			->get();

		foreach($medicine as $meds){
			$is_taken = false;
			foreach($intakelogs as $logs){
				if($logs['medicineid'] == $meds['id']){
					$is_taken = true;
					$meds['status'] = $logs['status'];
				}
			}
			$meds['is_taken'] = $is_taken;
		}
		$this->response['data'] = $medicine;
		$this->response['status'] = true;
		
		return $this->container->response->withJson($this->response);
	}
}