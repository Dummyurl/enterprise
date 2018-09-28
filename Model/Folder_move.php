<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Folder_move extends Model
{
    protected $table = "folder_move";
    public $timestamps = false;
    public $primaryKey  = 'move_id';
}
