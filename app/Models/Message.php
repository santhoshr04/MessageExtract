<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['group_name', 'sender', 'timestamp', 'message', 'media_path', 'language'];


}
