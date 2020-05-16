<?php

namespace Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Patients Model
 */
class PatientsModel extends Model{
    // The table must be protected
    protected $table = "patients";

    // Columns that are insertables and must be protected
    protected $fillable = array(
    	"id", "patient_id", "firstname", "middlename", "lastname", "dateofbirth", "gender", "mobilenumber", "consultationdate", "doctor_id", "status", "drtb", "licensenumber","specialization", "category", "address", "street", "barangay", "city", "remarks", "username","password", "token",
    );

    // Table should have updated_at and created_at columns when this is set to true
    public $timestamps = false;

}
?>
