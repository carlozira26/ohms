<?php 
namespace Controllers;
use Models\UsersModel;
use Models\PatientsModel;
use Models\MessagesModel;
use Models\TimeIntakeModel;
use Models\PatientMedicinesModel;
use Controllers\Utils;
use \Firebase\JWT\JWT;

class ChatController{
	protected $container;

	private $response = array(
		"status" => false,
		"data" => array(),
		"message" => ""
	);
	
	function __construct($container){
		$this->container = $container;
	}

	public function getDoctorChat($req, $res, $args){
		$body = $req->getParsedBody();
		$page = $body['page'];
		
		$Utils = new Utils();
		$user = $Utils->getUserFromBearerToken($req, $this->container);
		
		$limit = 20;
		$offset = ($page - 1) * $limit;
		
		$patientid = $args['patientid'];
		$message = MessagesModel::select('user_type','message','is_seen')
			->whereIn('message_from',array($user['id'],$patientid))
			->whereIn('message_to',array($user['id'],$patientid))
			->offset($offset)
			->limit($limit)
			->orderBy('created_at','desc')
			->get();
		// $messageCount = MessagesModel::selectRaw('count(id) as count')
		// 	->whereIn('message_from',array($user['id'],$patientid))
		// 	->whereIn('message_to',array($user['id'],$patientid))
		// 	->first();

		$this->response['data'] = $message;
		// $this->response['count'] = $messageCount;
		$this->response['status'] = true;
		return $this->container->response->withJson($this->response);
	}
	private function messageSend($number, $message){
		echo ($message);
		$url = 'https://www.itexmo.com/php_api/api.php';
		$itexmo = array('1' => $number, '2' => substr($message, 0, 100), '3' => 'TR-CHRIS383442_LBFHR');
		$param = array(
		    'http' => array(
		        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
		        'method'  => 'POST',
		        'content' => http_build_query($itexmo),
		    ),
		);
		$context  = stream_context_create($param);
		return file_get_contents($url, false, $context);
	}

	public function patientReminderCron($req, $res, $args){
		$medicinesController = new MedicinesController($this->container);
		$datetoday = '2020-02-20';
		$patientList = PatientsModel::selectRaw("id, firstname, mobilenumber")->where('status','Ongoing')->get();
		$message = "";
		
		foreach($patientList as $patient){
			$MedicineList = PatientMedicinesModel::selectRaw('medicines.id, concat(brandname,":",genericname) medicine,pieces,date_added')
				->join('medicines','patient_medicine.medicineid','=','medicines.id')
				->where('patient_medicine.uid', $patient['id'])
				->where('patient_medicine.is_active','Y')
				->get();
				$schedule = array();
				$message = "Hi ".$patient['firstname'].", \n";
			foreach($MedicineList as $Medicine){
				$timeIntake = TimeIntakeModel::select('intakedays','intaketime')
					->where('is_active','Y')
					->where('uid', $Medicine['id'])
					->get();

				$dates = $medicinesController->processMedicineTimeSchedule($Medicine['pieces'],$timeIntake[0]['intaketime'],$timeIntake[0]['intakedays'],$Medicine['date_added']);
				$intaketime = explode(',',$timeIntake[0]['intaketime']);
				foreach($dates as $date){
					if($date == $datetoday){
						$msgcheck = true;
						foreach($intaketime as $time){
							$schedule[$time][] = $Medicine['medicine'];
						}
					}
				}
				ksort($schedule);
			}
			if(count($schedule) > 0){
				foreach($schedule as $key=>$m){
					$message .= "\n".date("H:i A", strtotime($key))."\n";
					foreach($m as $medicine){
						$message .= $medicine."\n";
					}
				}
				$this->messageSend($patient['mobilenumber'], $message);
			}
		}
	}
	public function getMessageList($req, $res, $args){
		$usertype = $_GET['usertype'];
		$Utils = new Utils();
		$user = ($usertype == 'patient') ? $Utils->getPatientFromBearerToken($req, $this->container) : $Utils->getUserFromBearerToken($req, $this->container);

		$messages = MessagesModel::selectRaw('messages.doctor_id,concat(users.firstname," ",users.lastname) as doctor_name, messages.patient_id, concat(patients.firstname, " ",patients.lastname) as patient_name, message')
			->join('users','messages.doctor_id','users.id')
			->join('patients','messages.patient_id','patients.id');
		$unseenmessages = MessagesModel::selectRaw('messages.doctor_id, messages.patient_id, count(messages.id) as count');

		if($usertype == 'patient'){
			$messages = $messages
				->where('messages.patient_id',$user['id'])
				->groupBy('messages.doctor_id')
				->orderBy('messages.created_at','desc');
			$unseenmessages = $unseenmessages
				->where('messages.patient_id',$user['id'])
				->where('patient_seen','N');
		}else{
			$messages = $messages
				->where('messages.doctor_id',$user['id'])
				->groupBy('messages.patient_id')
				->orderBy('messages.created_at','desc');
			$unseenmessages = $unseenmessages
				->where('messages.doctor_id',$user['id'])
				->where('doctor_seen','N');
		}
		$sql = $messages->toSql();
		$messages = $messages->get();
		$unseenmessages = $unseenmessages->get();
		foreach($messages as $message){
			$message['unseen'] = 0;
			foreach($unseenmessages as $unseen){
				if($message['doctor_id'] == $unseen['doctor_id'] && $message['patient_id'] == $unseen['patient_id']){
					$message['unseen'] = $unseen['count'];
				}
			}
		}
		$this->response['status'] = true;
		$this->response['data'] = $messages;

		return $this->container->response->withJson($this->response);
	}
	public function getMessages($req, $res, $args){
		$receiverid = $args['receiverid'];
		$body = $req->getParsedBody();
		$userType = $body['userType'];

		$Utils = new Utils();

		$user = ($userType == 'patient') ? $Utils->getPatientFromBearerToken($req, $this->container) : $Utils->getUserFromBearerToken($req, $this->container);

		$message = MessagesModel::select('message_from','message');
		if($userType == 'patient'){
			$message = $message
				->where('patient_id', $user['id'])
				->where('doctor_id',$receiverid);
			MessagesModel::where('patient_id', $user['id'])
				->where('doctor_id',$receiverid)
				->update(array('patient_seen' => 'Y'));	
		}else{
			$message = $message
				->where('patient_id', $receiverid)
				->where('doctor_id', $user['id']);
			MessagesModel::where('doctor_id', $user['id'])
				->where('patient_id',$receiverid)
				->update(array('doctor_seen' => 'Y'));
		}
		$message = $message->get();
		$this->response['status'] = true;
		$this->response['data'] = $message;
		return $this->container->response->withJson($this->response);
	}
	public function submitMessage($req, $res, $args){
		$body = $req->getParsedBody();
		$send = ($body['usertype'] == 'patient') ? 
		array(
			'patient_id' => $body['senderid'],
			'doctor_id' => $body['receiverid'],
			'message' => $body['message'],
			'message_from' => $body['usertype'],
			'patient_seen' => 'Y'

		) : array(
			'patient_id' => $body['receiverid'],
			'doctor_id' => $body['senderid'],
			'message' => $body['message'],
			'message_from' => $body['usertype'],
			'doctor_seen' => 'Y'
		);

		MessagesModel::create($send);
		$this->response['status'] = true;
		return $this->container->response->withJson($this->response);
	}
}