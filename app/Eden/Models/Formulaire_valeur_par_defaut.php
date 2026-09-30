<?php

namespace App\Eden\Models;

use Illuminate\Database\Eloquent\Model;

class Formulaire_valeur_par_defaut extends Table_avec_gestion_profil
{

    protected $table = "eden_formulaireslibres_valeur_par_defaut";
    
    public $timestamps = false;
}
