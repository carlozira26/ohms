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

		$url = 'https://www.itexmo.com/php_api/api.php';
		$itexmo = array('1' => $number, '2' => $message, '3' => 'TR-CHRIS383442_LBFHR');
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
		$datetoday = '2020-02-12';
		$patientList = PatientsModel::selectRaw("id, firstname, mobilenumber")->where('status','Ongoing')->get();
		$message = "";
		
		foreach($patientList as $patient){
			$MedicineList = PatientMedicinesModel::selectRaw('medicines.id, concat(brandname,":",genericname) medicine,pieces,date_added')
				->join('medicines','patient_medicine.medicineid','=','medicines.id')
				->where('patient_medicine.uid', $patient['id'])
				->where('patient_medicine.is_active','Y')
				->get();
				$schedule = array();
				$message = "Hi ".$patient['firstname'].", </br>";
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
					$message .= "</br>".date("H:i A", strtotime($key))."</br>";
					foreach($m as $medicine){
						$message .= $medicine."</br>";
					}
				}
				$this->messageSend($patient['mobilenumber'], $message);
			}
		}
	}
}