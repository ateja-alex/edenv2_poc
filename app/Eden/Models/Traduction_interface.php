<?php

namespace App\Eden\Models;

use Illuminate\Database\Eloquent\Model;

class Traduction_interface extends Model {

    protected $connection = "mysql";

    protected $table = 'eden_traductions';
	
    protected $primaryKey = 'id';
	
    public $timestamps = false;

}
