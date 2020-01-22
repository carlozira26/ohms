<?php 
namespace Controllers;
use Models\UsersModel;
use Models\PatientsModel;
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
	
	public function UserAuth($request, $response, $args){
		$body = $request->getParsedBody();
		$usertype = $body['usertype'];
		if($usertype == 'doctor'){
			$user = $this->authenticateEmailPassword($body['email'], $body['password']);
			$role = $user->usertype;
		}else{
			$user = $this->authenticateUsernamePassword($body['username'], $body['password']);
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
			$this->response['data'] = array(
				'id' => $user->id,
				'fullname' => ucfirst(strtolower($fullname)),
				'role' => $role,
			);
		}
		return $this->container->response->withJson($this->response);
	}
	public function UserAccount($request, $response, $args){
		$id = $args['id'];
		$userDetails = UsersModel::select('firstname','middlename','lastname','birthdate','gender','contact_number','licensenumber','specialization','clinic_name','clinic_address','email')->where('id',$id)->first();
		
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
		$password = (!empty($body['password'])) ? $passwordConverter($body['password']) : $user['password'];
		
		UsersModel::where('id',$id)
			->update(array(
				'firstname' => $body['firstname'],
				'middlename' => $body['middlename'],
				'lastname' => $body['lastname'],
				'birthdate' => $body['birthdate'],
				'gender' => $body['gender'],
				'licensenumber' => $body['licensenumber'],
				'specialization' => $body['specialization'],
				'contact_number' => $body['contact_number'],
				'clinic_name' => $body['clinic_name'],
				'clinic_address' => $body['clinic_address'],
				'email' => $body['email'],
				'password' => $password,
			));
		return $this->container->response->withJson($password);
	}
	public function DoctorsList($req, $res, $args){
		$list = UsersModel::selectRaw('id,concat("Dr. ",firstname," ",lastname) as doctor')
			->where('usertype',2)->get();

		return $this->container->response->withJson($list);
	}
}