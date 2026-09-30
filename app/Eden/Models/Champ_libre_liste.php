<?php

namespace App\Eden\Models;

use Illuminate\Database\Eloquent\Model;

class Champ_libre_liste extends Model {

    protected $connection = "mysql";

    protected $table = 'eden_champslibres_listes';
	
    protected $primaryKey = 'id_valeur';
	
    public $timestamps = false;
	

}
