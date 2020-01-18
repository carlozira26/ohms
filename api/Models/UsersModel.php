<?php

namespace Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Users Model
 */
class UsersModel extends Model
{
    // The table must be protected
    protected $table = "users";

    // Columns that are insertables and must be protected
    protected $fillable = array(
    	"id", "firstname", "middlename", "lastname", "birthdate", "gender", "contact_number", "licensenumber",  "specialization", "clinic_name", "clinic_address", "email", "password", "token", "usertype"
    );

    // Table should have updated_at and created_at columns when this is set to true
    public $timestamps = false;

}
?>
