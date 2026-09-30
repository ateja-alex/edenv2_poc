<?php

namespace App\Eden\Models;

use Illuminate\Database\Eloquent\Model;

class Liste_libre_filtre extends Table_avec_gestion_profil {

    protected $connection = 'mysql';

    protected $table = 'eden_listes_libres_filtres';
	
    public $timestamps = false;
}
