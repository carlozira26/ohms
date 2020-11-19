<?php

namespace Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * BroadcastMessage Model
 */
class BroadcastMessageModel extends Model{
    // The table must be protected
    protected $table = "broadcast_message";

    // Columns that are insertables and must be protected
    protected $fillable = array(
    	"id", "receiver", "message", "sender"
    );

    // Table should have updated_at and created_at columns when this is set to true
    public $timestamps = false;

}
?>
