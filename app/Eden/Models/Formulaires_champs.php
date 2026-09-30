<?php

namespace App\Eden\Models;

use Illuminate\Database\Eloquent\Model;

class Formulaires_champs extends Table_avec_gestion_profil
{

    protected $table = "eden_formulaireslibres_champs";
    
    public $timestamps = false;
}
