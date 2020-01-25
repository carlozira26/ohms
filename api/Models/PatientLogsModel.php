<?php

namespace Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Patient Medicines Model
 */
class PatientLogsModel extends Model{
    // The table must be protected
    protected $table = "patient_logs";

    // Columns that are insertables and must be protected
    protected $fillable = array(
    	"id", "uid", "status", "reason", "updated_by", "created_at"
    );

    // Table should have updated_at and created_at columns when this is set to true
    public $timestamps = false;

}
?>
