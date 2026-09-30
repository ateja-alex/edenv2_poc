<?php

namespace App\Eden\Models;

use Illuminate\Database\Eloquent\Model;

class Liste_libre_autresvues extends Model {

    protected $connection = 'mysql';

    protected $table = 'eden_listes_libres_autresvues';
	
    public $timestamps = false;
}
