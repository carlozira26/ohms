<?php

namespace Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Time Intake Model
 */
class TimeIntakeModel extends Model{
    // The table must be protected
    protected $table = "time_intake";

    // Columns that are insertables and must be protected
    protected $fillable = array(
    	"id", "uid", "intakedays", "intaketime"
    );

    // Table should have updated_at and created_at columns when this is set to true
    public $timestamps = false;

}
?>
