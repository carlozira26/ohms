<?php

namespace Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Medicines Model
 */
class MedicinesModel extends Model{
    // The table must be protected
    protected $table = "medicines";

    // Columns that are insertables and must be protected
    protected $fillable = array(
    	"id", "brandname", "genericname","manufacturer","expiration", "description", "is_primary", "is_active"
    );

    // Table should have updated_at and created_at columns when this is set to true
    public $timestamps = false;

}
?>
