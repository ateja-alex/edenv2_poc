<?php

namespace App\Eden\Models\Elements;

use Illuminate\Database\Eloquent\Model;


class Article_fournisseur extends Element {
	
	protected $table = 'article_fournisseur';
	
    protected $primaryKey = 'id';
	
    public $timestamps = false;
	
	public function fournisseur() {
		
	    return $this->belongsTo('App\Eden\Models\Elements\Fournisseur');
    }
	
	
}
