<?php

namespace Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Medicines Model
 */
class MessagesModel extends Model{
    // The table must be protected
    protected $table = "messages";

    // Columns that are insertables and must be protected
    protected $fillable = array(
    	"id", "user_type", "message_from", "message_to", "message", "is_seen", "created_at"
    );

    // Table should have updated_at and created_at columns when this is set to true
    public $timestamps = false;

}
?>
