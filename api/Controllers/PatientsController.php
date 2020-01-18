<?php 
namespace Controllers;
use Models\PatientsModel;
use Models\UsersModel;
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
					"status" => $body['status'],
					"drtb" => $body['drtb'],
					"address" => ucwords(strtolower($body['address'])),
					"remarks" => $body['remarks'],
					"username" => $body['username'],
					"password" => $password,
					"token" => $token
				));
				$this->response['status'] = true;
				$this->response['message'] = "Successfully Created";
			}
		}else{
			$checkUser = PatientsModel::select("username")->where('username',$body['username'])->where('id', '!=',$body['id'])->first();
			if($checkUser){
				$this->response['message'] = "Username already used!";
				$this->response['status'] = "false";
			}else{
				$patientCreate = PatientsModel::where('id',$body['id'])
					->update(array(
					"firstname" => ucwords(strtolower($body['firstname'])),
					"middlename" => ucwords(strtolower($body['middlename'])),
					"lastname" => ucwords(strtolower($body['lastname'])),
					"dateofbirth" => $body['dateofbirth'],
					"consultationdate" => $body['consultationdate'],
					"gender" => $body['gender'],
					"mobilenumber" => $body['mobilenumber'],
					"status" => $body['status'],
					"drtb" => $body['drtb'],
					"address" => $body['address'],
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

		$limit = 20;
		$offset = ($_GET['page'] - 1) * $limit;
		$patientList = PatientsModel::select("id","patient_id","firstname","middlename","lastname","username","dateofbirth","consultationdate","doctor_id","gender","mobilenumber","status","drtb","category","address","remarks");

		$patientCount = PatientsModel::selectRaw("count(id) as count");

		if(!empty($_GET['search'])){
			$patientList = $patientList
				->where('patient_id', 'LIKE', "%".$_GET['search']."%" )
				->orWhere('firstname', 'LIKE', "%".$_GET['search']."%" )
				->orWhere('middlename', 'LIKE', "%".$_GET['search']."%" )
				->orWhere('lastname', 'LIKE', "%".$_GET['search']."%" )
				->orWhere('status', 'LIKE', "%".$_GET['search']."%" )
				->orWhere('address', 'LIKE', "%".$_GET['search']."%" )
				->orWhere('remarks', 'LIKE', "%".$_GET['search']."%" );
			$patientCount = $patientCount
				->where('patient_id', 'LIKE', "%".$_GET['search']."%" )
				->orWhere('firstname', 'LIKE', "%".$_GET['search']."%" )
				->orWhere('middlename', 'LIKE', "%".$_GET['search']."%" )
				->orWhere('lastname', 'LIKE', "%".$_GET['search']."%" )
				->orWhere('status', 'LIKE', "%".$_GET['search']."%" )
				->orWhere('address', 'LIKE', "%".$_GET['search']."%" )
				->orWhere('remarks', 'LIKE', "%".$_GET['search']."%" );
			
		}else{
			$patientList = $patientList
			->offset($offset)
			->limit($limit);
		}
		$patientList = $patientList
			->limit($limit)
			->get();
		$patientCount= $patientCount->first();

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
}