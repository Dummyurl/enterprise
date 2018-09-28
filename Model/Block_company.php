<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Block_company extends Model
{
    protected $table = "block_company_list";
    public $timestamps = true;
    public $primaryKey  = 'block_id';
}
