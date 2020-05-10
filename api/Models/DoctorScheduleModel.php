<?php

namespace Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Medicines Model
 */
class DoctorScheduleModel extends Model{
    // The table must be protected
    protected $table = "doctor_schedule";

    // Columns that are insertables and must be protected
    protected $fillable = array(
    	"id", "uid", "schedule"
    );

    // Table should have updated_at and created_at columns when this is set to true
    public $timestamps = false;

}
?>
