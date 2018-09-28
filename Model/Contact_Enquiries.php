<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Contact_Enquiries extends Model
{
    protected $table = "contact_enquiries";
    public $timestamps = true;
    public $primaryKey  = 'id';
}
