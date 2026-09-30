<?php

namespace App\Eden\Models;

use Illuminate\Database\Eloquent\Model;

class Article_fournisseur extends Model {

    protected $connection = 'mysql';

    protected $table = 'article_fournisseur';
	
    public $timestamps = false;

    public function fournisseur(){
        return $this->hasOne(Fournisseur::class, 'id', 'fournisseur_id');
    }

}
