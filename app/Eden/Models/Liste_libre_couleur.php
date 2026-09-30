<?php

namespace App\Eden\Models;

use Illuminate\Database\Eloquent\Model;

class Liste_libre_couleur extends Model {

    protected $connection = 'mysql';

    protected $table = 'eden_listes_libres_couleur';
	
    public $timestamps = false;
}
