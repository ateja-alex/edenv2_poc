<?php

namespace App\Eden\Models\Elements;

use Illuminate\Database\Eloquent\Model;

class Blog_article extends Element {
	
	protected $table = 'blog_article';
	
    protected $primaryKey = 'id';
	
    public $timestamps = false;
	
	public function categorie() {
		
	    return $this->belongsTo('App\Eden\Models\Elements\Blog_categorie', 'id', 'categorie_id');
    }
	
	
}