<?php

namespace App\Eden\Models\Elements;

use Illuminate\Database\Eloquent\Model;

class Blog_categorie extends Element {
	
	protected $table = 'blog_categorie';
	
    protected $primaryKey = 'id';
	
    public $timestamps = false;
	
	public function articles() {
		
	    return $this->hasMany('App\Eden\Models\Elements\Blog_article', 'categorie_id', 'id');
    }
	
	
}
