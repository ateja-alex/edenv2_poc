<?php

namespace App\Eden\Models\Elements;

class Famille extends Element {
	
	protected $table = 'famille';
    protected $primaryKey = 'id';
    protected $with = ['enfants'];

    public function theme_de_filtres() {

        return $this->belongsToMany('App\Eden\Models\Elements\Theme_de_filtres', 'famille_theme_de_filtres', 'famille_id', 'theme_de_filtres_id');
    }

    public function enfants() {

        return $this->hasMany(Famille::class, 'parent_id');
    }

}
