<?php

namespace App\Eden\Models\Elements;

class Filtre extends Element {
	
	protected $table = 'filtre';
	protected $primaryKey = 'id';

    public function themes_de_filtres() {
	
        return $this->belongsToMany('App\Eden\Models\Elements\Theme_de_filtres', 'filtre_theme_de_filtres', 'filtre_id', 'theme_de_filtres_id');
    }

    public function articles() {
		
	    return $this->belongsToMany('App\Eden\Models\Elements\Article', 'article_filtre', 'filtre_id', 'article_id');
    }

}
