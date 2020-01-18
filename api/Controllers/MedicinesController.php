<?php 
namespace Controllers;
use Models\MedicinesModel;
use Models\TimeIntakeModel;
use Models\PatientMedicinesModel;
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
		$timeIntakeList = json_decode($body["timeIntake"],true);
		$type = $args['type'];

		if($type == "add"){
			$medicineSave = MedicinesModel::create(array(
				"brandname" => $body['brandName'],
				"genericname" => $body['genericName'],
				"instructions" => $body['instructions']
			));
			$id = $medicineSave->id;
			$this->response["message"] = "Successfully added!";
			$this->response["status"] = true;
		}else{
			$oldDayTime = TimeIntakeModel::where('uid',$body['id'])
				->update(array("is_active" => "N"));

			$medicineSave = MedicinesModel::where('id',$body['id'])
				->update(array(
				"brandname" => $body['brandName'],
				"genericname" => $body['genericName'],
				"instructions" => $body['instructions']
			));
			$id = $body['id'];
			$this->response["message"] = "Successfully edited!";
			$this->response["status"] = true;
		}

		for($x=0; $x<count($timeIntakeList); $x++ ){
			$dayListImplode = implode(",",$timeIntakeList[$x]["dayList"]);
			$timeListImplode = implode(",",$timeIntakeList[$x]["timeList"]);
			
			TimeIntakeModel::create(array(
				"uid" => $id,
				"intakedays" => $dayListImplode,
				"intaketime" => $timeListImplode
			));
		}
		return $this->container->response->withJson($this->response);
	}
	public function MedicineList($request, $response, $args){
		$body = $request->getQueryParams();
		$limit = 6;
		$offset = ($_GET['page'] - 1) * $limit;
		
		$medicineList = MedicinesModel::select("id", "brandname", "genericname", "instructions", "is_active")->where('is_active','Y');
		$medicineCount = MedicinesModel::selectRaw("count(id) as count")->where('is_active','Y');
		if(!empty($_GET['search'])){
			$medicineList = $medicineList
				->where('brandname', 'LIKE', "%".$_GET['search']."%" )
				->orWhere('genericname', 'LIKE', "%".$_GET['search']."%" )
				->orWhere('instructions', 'LIKE', "%".$_GET['search']."%" );
			$medicineCount = $medicineCount
				->where('brandname', 'LIKE', "%".$_GET['search']."%" )
				->orWhere('genericname', 'LIKE', "%".$_GET['search']."%" )
				->orWhere('instructions', 'LIKE', "%".$_GET['search']."%" );
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
		$medicineList = MedicinesModel::selectRaw("id,concat(brandname,':',genericname) as medicinename,instructions")->where('is_active','Y')->get();
		$this->response['data'] = $medicineList;
		$this->response['status'] = true;
		return $this->container->response->withJson($this->response);
	}

	public function submitPatientMedicine($request, $response, $args){
		$body = $request->getParsedBody();
		$body = json_decode($body['patientMedicineList'],true);
		$patientID = $args['id'];

		PatientMedicinesModel::where('uid',$patientID)->update(array('is_active'=>'N'));
		foreach($body as $medicine){
			PatientMedicinesModel::create(array(
				'uid' => $patientID,
				'medicineid' => $medicine['medicineID'],
				'dosage' => $medicine['medicineDosage'],
				'pieces' => $medicine['medicinePieces'],
			));
		}
		$this->response['status'] = true;
		return $this->container->response->withJson($this->response);
	}
	public function getPatientMedicine($request, $response, $args){
		$patientID = $args['id'];
		$patientMedicine = PatientMedicinesModel::selectRaw("patient_medicine.medicineid,concat(brandname,':',genericname)as medicinename,dosage,pieces,instructions")
			->join('medicines','medicines.id','=','patient_medicine.medicineid')
			->where('uid',$patientID)
			->where('patient_medicine.is_active','Y')
			->get();
		return $this->container->response->withJson($patientMedicine);
	}
}