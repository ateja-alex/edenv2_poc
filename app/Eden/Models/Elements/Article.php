<?php

namespace App\Eden\Models\Elements;

use Illuminate\Database\Eloquent\Model;

use App\Eden\Models\Element_image;

class Article extends Element {
	
	protected $table = 'article';
	
    protected $primaryKey = 'id';
	
    public $timestamps = false;
	
	// protected $appends = ['image_principale'];
	
	public function filtres() {
		
	    return $this->belongsToMany('App\Eden\Models\Elements\Filtre', 'article_filtre', 'article_id', 'filtre_id');
    }
	
	public function famille() {
		
	    return $this->belongsTo('App\Eden\Models\Elements\Famille');
    }
	
	public function fournisseur() {
		
	    return $this->belongsTo('App\Eden\Models\Elements\Fournisseur');
    }
	
	public function getImagePrincipaleAttribute() {
		
		// on regarde si on a le cache sur la page
		$images_principales_des_articles = cache_temporaire('images_principales_des_articles');
		
		if(empty($images_principales_des_articles)) {
			
			$images_principales_des_articles = Element_image::where('type_element', 'article')->orderBy('ordre')->groupBy('element_id')->pluck('chemin', 'element_id');
			
			cache_temporaire('images_principales_des_articles', $images_principales_des_articles);
		}
		if(isset($images_principales_des_articles[$this->id]))
			$image = $images_principales_des_articles[$this->id];
		else
			$image = null;
		
		if($image !== null)
			return asset('storage/'.$image);
		else
			return asset('eden/images/article_sans_image.png');
		
		// return 'test';
	}
}
