<?php
namespace Controllers;
use \Firebase\JWT\JWT;
use Models\UsersModel;
use Models\PatientsModel;

class Utils
{
	public function getUserFromBearerToken($req, $container){
		$Authorization = $req->getHeader('Authorization');
		$token = str_replace("Bearer ", "", $Authorization[0]);
		$decoded = JWT::decode($token, $container['settings']["jwt"], array('HS256'));
		$user = UsersModel::where("token", $decoded)->first();
		return $user;
	}

	public function getPatientFromBearerToken($req, $container){
		$Authorization = $req->getHeader('Authorization');
		$token = str_replace("Bearer ", "", $Authorization[0]);
		$decoded = JWT::decode($token, $container['settings']["jwt"], array('HS256'));
		$user = PatientsModel::where("token", $decoded)->first();
		return $user;
	}
}