<?php

namespace App\Eden\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model {

    protected $connection = "mysql";

    protected $table = 'paiement';
	
    protected $primaryKey = 'id';
	
    public $timestamps = false;

}