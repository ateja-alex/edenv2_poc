<?php

namespace App\Eden\Models;

use Illuminate\Database\Eloquent\Model;

class Rapport_favoris extends Model {

    protected $connection = "mysql";

    protected $table = 'eden_rapports_favoris';
	
    protected $primaryKey = 'id';
	
    public $timestamps = false;
}
