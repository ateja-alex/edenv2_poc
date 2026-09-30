<?php

namespace App\Eden\Models;

use Illuminate\Database\Eloquent\Model;

class Liste_libre_parametre_requete extends Model {

    protected $connection = 'mysql';

    protected $table = 'eden_listes_libres_parametre_requete';

    public $timestamps = false;
}
