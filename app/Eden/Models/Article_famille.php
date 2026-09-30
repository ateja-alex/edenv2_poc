<?php

namespace App\Eden\Models;

use Illuminate\Database\Eloquent\Model;

class Article_famille extends Model {

    protected $connection = 'mysql';

    protected $table = 'article_famille';
	
    public $timestamps = false;
}
