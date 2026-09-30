<?php

namespace App\Eden\Models\Elements;

class Fournisseur extends Element {
	
	protected $table = 'fournisseur';
	protected $primaryKey = 'id';

	public function articles() {
		
	    return $this->hasMany('App\Eden\Models\Elements\Article');
    }

	public function contacts() {
		
	    return $this->hasMany('App\Eden\Models\Elements\Contact');
    }
	
	public function filtres() {
		
	    return $this->belongsToMany('App\Eden\Models\Elements\Filtre', 'filtre_fournisseur', 'fournisseur_id', 'filtre_id');
    }

}
