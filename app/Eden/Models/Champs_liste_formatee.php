<?php

namespace App\Eden\Models;

use Illuminate\Database\Eloquent\Model;

class Champs_liste_formatee extends Model {

    protected $connection = "mysql";

    protected $table = 'eden_champs_listes_formatees';
    
    protected $guarded = [];
    public $timestamps = false;
}
