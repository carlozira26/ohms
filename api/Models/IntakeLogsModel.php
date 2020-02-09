<?php

namespace Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Medicines Model
 */
class IntakeLogsModel extends Model{
    // The table must be protected
    protected $table = "intake_logs";

    // Columns that are insertables and must be protected
    protected $fillable = array(
    	"id", "patient_id", "intake_value", "date_intake"
    );

    // Table should have updated_at and created_at columns when this is set to true
    public $timestamps = false;

}
?>
