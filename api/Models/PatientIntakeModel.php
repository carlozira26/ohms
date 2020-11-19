<?php

namespace Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Medicines Model
 */
class PatientIntakeModel extends Model{
    // The table must be protected
    protected $table = "patient_intake";

    // Columns that are insertables and must be protected
    protected $fillable = array(
    	"id", "status", "reason", "date", "created_at", "patient_id", "distributor", "is_active"
    );

    // Table should have updated_at and created_at columns when this is set to true
    public $timestamps = false;

}
?>
