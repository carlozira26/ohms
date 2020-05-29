<?php

namespace Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Barangay Model
 */
class BarangayModel extends Model{
    // The table must be protected
    protected $table = "barangay";

    // Columns that are insertables and must be protected
    protected $fillable = array(
    	"id", "barangay"
    );

    // Table should have updated_at and created_at columns when this is set to true
    public $timestamps = false;

}
?>
