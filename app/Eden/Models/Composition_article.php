<?php

namespace App\Eden\Models;

use Illuminate\Database\Eloquent\Model;

class Composition_article extends Model {

    protected $connection = 'mysql';

    protected $table = 'composition_article';
	
    public $timestamps = false;
}
