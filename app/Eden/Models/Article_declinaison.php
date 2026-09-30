<?php

namespace App\Eden\Models;

use Illuminate\Database\Eloquent\Model;

class Article_declinaison extends Model {

    protected $connection = 'mysql';

    protected $table = 'article_declinaison';
	
    public $timestamps = false;
}
