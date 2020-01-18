<?php

namespace Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Patient Medicines Model
 */
class PatientMedicinesModel extends Model{
    // The table must be protected
    protected $table = "patient_medicine";

    // Columns that are insertables and must be protected
    protected $fillable = array(
    	"id", "uid", "medicineid", "dosage", "pieces", "is_active"
    );

    // Table should have updated_at and created_at columns when this is set to true
    public $timestamps = false;

}
?>
