<?php

namespace App\Eden\Models;

use Illuminate\Database\Eloquent\Model;

class Article_filtre extends Model {

    protected $connection = 'mysql';

    protected $table = 'article_filtre';
	
    public $timestamps = false;
}
