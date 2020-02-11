<?php 
namespace Controllers;
use Models\UsersModel;
use Models\PatientsModel;
use Models\MessagesModel;
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
	public function messageSend($req, $res, $args){
		$body = $req->getParsedBody(); 
		if (!isset($body['text'])) {
			return $this->response["message"] = "No message provided";
		}
		 
		$client = new NexmoClient(new NexmoClientCredentialsBasic(API_KEY, API_SECRET));
		$text = new NexmoMessageText($args['number'], NEXMO_FROM_NUMBER, $body['text']);
		$client->message()->send($text);
		
		$this->response["message"] = "Sending an SMS to " . $args['number'];
		return $this->container->response->withJson($this->response);
	}
}