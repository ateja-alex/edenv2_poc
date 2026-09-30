<?php

namespace App\Eden\Models;

use Illuminate\Database\Eloquent\Model;

class Rapport_libre extends Table_avec_gestion_profil {

    protected $connection = "mysql";

    protected $table = 'eden_rapports';
	
    protected $primaryKey = 'id';
	
    public $timestamps = false;
}
