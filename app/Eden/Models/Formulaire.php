<?php

namespace App\Eden\Models;

use Illuminate\Database\Eloquent\Model;

class Formulaire extends Model {

    protected $connection = 'mysql';

    protected $table = 'eden_formulaireslibres';
	
    public $timestamps = false;
}
