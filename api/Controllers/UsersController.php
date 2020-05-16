<?php 
namespace Controllers;
use Models\UsersModel;
use Models\PatientsModel;
use Models\SpecializationsModel;
use Models\SubSpecializationsModel;
use Models\DoctorScheduleModel;
use \Firebase\JWT\JWT;

class UsersController{
	protected $container;

	private $response = array(
		"status" => false,
		"data" => array(),
		"message" => ""
	);
	
	function __construct($container){
		$this->container = $container;
	}

	private function authenticateEmailPassword($email="", $password=""){
		$passwordConverter = $this->container['passwordConverter'];
		$password = $passwordConverter($password);
		return UsersModel::where('email',$email)
		->where('password',$password)
		->where('is_active','Y')
		->first();
	}

	private function authenticateUsernamePassword($username="", $password=""){
		$passwordConverter = $this->container['passwordConverter'];
		$password = $passwordConverter($password);
		return PatientsModel::where('username',$username)
		->where('password',$password)
		->first();
	}

	private function authenticatePassword($password=""){
		$passwordConverter = $this->container['passwordConverter'];
		$password = $passwordConverter($password);
		return UsersModel::where('password',$password)
		->first();
	}
	
	public  function SpecializationList($req, $res, $args){
		$list = SpecializationsModel::orderBy('type','asc')->get();
		$sublist = SubSpecializationsModel::select('uid','sub_type')->orderBy('sub_type','asc')->get();
		$this->response['data']['specializations'] = $list;
		$this->response['data']['subspecializations'] = $sublist;
		return $this->container->response->withJson($this->response);
	}

	public function UserAuth($request, $response, $args){
		$body = $request->getParsedBody();
		$username = $body['username'];
		$usertype = "";
		if(strpos($username,'@') && strpos($username,'.com')){
			$user = $this->authenticateEmailPassword($username, $body['password']);
			$role = $user->usertype;
		}else{
			$user = $this->authenticateUsernamePassword($username, $body['password']);
			$role = "none";
		}
		
		$this->response['status'] = (!isset($user)) ? false : true;
		$this->response['message'] = (!isset($user)) ? "Invalid Credential" : "";
		
		if(isset($user)){
			$fullname = $user->firstname." ".$user->lastname;
			$this->response['token'] = JWT::encode(
				$user->token,
				$this->container["settings"]["jwt"]
			);
			if($usertype == 'doctor'){
				$this->response['data'] = array(
					'id' => $user->id,
					'fullname' => ucwords(strtolower($fullname)),
					'role' => $role,
				);
			}else{
				$this->response['data'] = array(
					'id' => $user->id,
					'fullname' => ucwords(strtolower($fullname)),
					'role' => $role,
					'status' => $user->status
				);
			}
		}
		return $this->container->response->withJson($this->response);
	}
	public function UserAccount($request, $response, $args){
		$id = $args['id'];
		$userDetails = UsersModel::select('firstname','middlename','lastname','birthdate','gender','contact_number','licensenumber','specialization','subspecialization','clinic_name','clinic_address','email','image_path')->where('id',$id)->first();
		
		$this->response['data'] = $userDetails;
		$this->response['status'] = true;
		
		return $this->container->response->withJson($this->response);
	}

	public function ConfirmPassword($req, $res, $args){
		$id = $args['id'];
		$body = $req->getParsedBody();
		$user = $this->authenticatePassword($body['password']);

		if(isset($user)){
			$this->response['status'] = true;
			$this->response['message'] = "Confirmed";
		}
		return $this->container->response->withJson($this->response);
	}

	public function ChangeInformation($req, $res, $args){
		$id = $args['id'];
		$body = $req->getParsedBody();
		
		$user = UsersModel::select('email','password')->where('id',$id)->first();
		$passwordConverter = $this->container['passwordConverter'];
		if(!empty($body['password'])){
			$password = $passwordConverter($body['password']);
			UsersModel::where('id',$id)
			->update(array(
				'email' => $body['email'],
				'password' => $password,
			));	
		}else{
			UsersModel::where('id',$id)
				->update(array(
					'firstname' => $body['firstname'],
					'middlename' => $body['middlename'],
					'lastname' => $body['lastname'],
					'birthdate' => $body['birthdate'],
					'gender' => $body['gender'],
					'licensenumber' => $body['licensenumber'],
					'specialization' => $body['specialization'],
					'subspecialization' => $body['subspecialization'],
					'contact_number' => $body['contact_number'],
					'clinic_name' => $body['clinic_name'],
					'clinic_address' => $body['clinic_address'],
			));
			if(isset($_FILES['imageFile'])){
				$file = $_FILES['imageFile'];
				$imgUploadResponse = $this->uploadImage($file,$id);
				UsersModel::where("id",$id)->update(array(
				"image_path" => $imgUploadResponse['imageLocation']
				));
			}
		}
		$this->response['status'] = true;
		return $this->container->response->withJson($this->response);
	}
	public function uploadImage($file,$id){
		if(!isset($file['name'])){
			$error['status']="error";
			$error['msg']="Encountered an error while uploading image";
			return json_encode($error);
		}else{
			$uploadDirectory = $this->container["settings"]["uploadDirectory"];
			$baseUrl = $this->container["settings"]["baseUrl"];
			if($file['error']==0){
				$fileType = explode("/",$file['type']);
				if(move_uploaded_file($file['tmp_name'], $uploadDirectory."/Doctors/".$id.".".$fileType[1])==true){
					$error['status']="success";
					$error['msg']="The image has been uploaded.";
					$error['imageLocation'] = $baseUrl."/uploads/Doctors/".$id.".".$fileType[1];
				}else{
					$error['status']="error";
					$error['msg']="Sorry, there was an error uploading the image.";
				}
			}
			return $error;
		}
	}
	public function DoctorsList($req, $res, $args){
		$Utils = new Utils();
		$user = $Utils->getUserFromBearerToken($req, $this->container);

		$list = UsersModel::selectRaw('id,concat("Dr. ",firstname," ",lastname) as doctor')
			->where('is_active','Y')
			->where('usertype',2)
			->where('id','!=',$user['id'])
			->get();
		return $this->container->response->withJson($list);
	}

	public function DoctorsListAccount($req, $res, $args){
		$body = $req->getQueryParams();

		$limit = 20;
		$offset = ($_GET['page'] - 1) * $limit;
		$doctorList = UsersModel::selectRaw('id, firstname, middlename, lastname,birthdate, gender, contact_number, licensenumber, specialization, clinic_name, clinic_address, email, is_active')
			->where('usertype',2);

		$doctorCount = UsersModel::selectRaw("count(id) as count");

		if(!empty($_GET['search'])){
			$doctorList = $doctorList
				->whereRaw('(firstname LIKE "%'.$_GET['search'].'%" or
				middlename LIKE "%'.$_GET['search'].'%" or
				lastname LIKE "%'.$_GET['search'].'%" or
				gender LIKE "%'.$_GET['search'].'%" or
				specialization LIKE "%'.$_GET['search'].'%" or
				clinic_name LIKE "%'.$_GET['search'].'%" or
				clinic_address LIKE "%'.$_GET['search'].'%" or
				email LIKE "%'.$_GET['search'].'%")');
			$doctorCount = $doctorCount
				->whereRaw('(firstname LIKE "%'.$_GET['search'].'%" or
				middlename LIKE "%'.$_GET['search'].'%" or
				lastname LIKE "%'.$_GET['search'].'%" or
				gender LIKE "%'.$_GET['search'].'%" or
				specialization LIKE "%'.$_GET['search'].'%" or
				clinic_name LIKE "%'.$_GET['search'].'%" or
				clinic_address LIKE "%'.$_GET['search'].'%" or
				email LIKE "%'.$_GET['search'].'%")');
		}else{
			$doctorList = $doctorList
			->where('is_active','Y')
			->offset($offset)
			->limit($limit);
		}
		$doctorList = $doctorList->get();
		$doctorCount = $doctorCount->first();

		$this->response['count'] = $doctorCount;
		$this->response['data'] = $doctorList;
		$this->response['status'] = true;
		return $this->container->response->withJson($this->response);
	}
	public function CreateAccount($req, $res, $args){
		$body = $req->getParsedBody();
		$passwordConvert = $this->container['passwordConverter'];
		$generateChars = $this->container['generateRandomChars'];
		$token = $generateChars(22);

		$firstname = $body['firstname'];
		$middlename = $body['middlename'];
		$lastname = $body['lastname'];
		$birthdate = $body['birthdate'];
		$license = ($body['license']!="undefined" && isset($body['license'])) ? $body['license'] : "";
		$specialization = ($body['specialization']!="undefined" && isset($body['specialization'])) ? $body['specialization'] : "";
		$gender = $body['gender'];
		$email = $body['email'];
		$contactnumber = $body['contactnumber'];
		$clinicname = ($body['clinicname']!="undefined" && isset($body['clinicname'])) ? $body['clinicname'] : "";
		$clinicaddress = ($body['clinicaddress']!="undefined" && isset($body['clinicaddress'])) ? $body['clinicaddress'] : "";
		$password = $body['password'];
		$isadmin = ($body['isadmin']=="true") ? 1 : 2;
		$form = array(
			'firstname' => $firstname,
			'middlename' => $middlename,
			'lastname' => $lastname,
			'birthdate' => $birthdate,
			'gender' => $gender,
			'contact_number' => $contactnumber,
			'licensenumber' => $license,
			'specialization' => $specialization,
			'clinic_name' => $clinicname,
			'clinic_address' => $clinicaddress,
			'email' => $email,
			'password' => $passwordConvert($password),
			'usertype' => '2',
			'token' => $token,
			'usertype' => $isadmin
		);
		$user = UsersModel::select("email")
			->where('email',$email)
			->first();
		if($user){
			$this->response['message'] = "Username already used!";
			$this->response['status'] = false;
		}else{
			UsersModel::create($form);
			$this->response['status'] = true;
			$this->response['message'] = "Successfully Created";
		}
		
		return $this->container->response->withJson($this->response);
	}
	public function AccountActivate($req, $res, $args){
		$body = $req->getParsedBody();
		$id = $args['id'];
		if($body['type'] == "deactivate"){
			UsersModel::where('id',$id)
				->update(array('is_active'=>'N'));
			$this->response["message"] = "Account has been deactivated!";
		}else{
			UsersModel::where('id',$id)
				->update(array('is_active'=>'Y'));
			$this->response["message"] = "Account has been reactivated!";
		}
			$this->response["status"] = true;
		return $this->container->response->withJson($this->response);
	}
	public function DoctorSchedule($req, $res, $args){
		$body = $req->getParsedBody();
		$Utils = new Utils();
		$user = $Utils->getUserFromBearerToken($req, $this->container);
		$schedule = DoctorScheduleModel::updateOrCreate(
			['uid' => $user['id']],
			['schedule' => $body['doctorSchedule']]
		);

		if($schedule){
			$this->response['status'] = true;
		}
		return $this->container->response->withJson($this->response);
	}
	public function FetchDoctorSchedule($req, $res, $args){
		if($args['id']==''){
			$Utils = new Utils();
			$user = $Utils->getPatientFromBearerToken($req, $this->container);
		}
		$id = ($args['id']=='') ? $user['id'] : $args['id'];

		$schedule = DoctorScheduleModel::select('schedule')->where('uid', $id)->first();
		if($schedule){
			$this->response['data'] = $schedule;
			$this->response['status'] = true;
		}

		return $this->container->response->withJson($this->response);
	}
	public function DoctorsProfile($req, $res, $args){
		$Utils = new Utils();
		$user = $Utils->getPatientFromBearerToken($req, $this->container);

		$doctor = PatientsModel::select('doctor_id')->where('id',$user['id'])->first();
		$profile = UsersModel::select('firstname','lastname','subspecialization','specialization','clinic_name','clinic_address','contact_number','image_path','schedule','licensenumber')
			->join('doctor_schedule','users.id','=','doctor_schedule.uid')
			->where('users.id',$doctor['doctor_id'])
			->first();
			$licenseEncrypter = $this->container['changeLicense'];

			$profile['licensenumber'] = $licenseEncrypter($profile['licensenumber']);
			if($profile){
				$this->response['data'] = $profile;
				$this->response['status'] = true;
			}
		return $this->container->response->withJson($this->response);
	}
}