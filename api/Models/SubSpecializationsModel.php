<?php

namespace Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Specializations Model
 */
class SubSpecializationsModel extends Model{
    // The table must be protected
    protected $table = "sub_specializations";

    // Columns that are insertables and must be protected
    protected $fillable = array(
    	"id", "uid", "sub_type"
    );

    // Table should have updated_at and created_at columns when this is set to true
    public $timestamps = false;

}
?>
