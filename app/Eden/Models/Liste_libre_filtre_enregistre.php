<?php

namespace App\Eden\Models;

use Illuminate\Database\Eloquent\Model;

class Liste_libre_filtre_enregistre extends Model {

    protected $connection = 'mysql';

    protected $table = 'eden_listes_libres_filtres_enregistres';
	
    public $timestamps = false;
}
