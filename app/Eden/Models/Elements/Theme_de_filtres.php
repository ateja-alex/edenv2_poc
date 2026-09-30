<?php

namespace App\Eden\Models\Elements;

class Theme_de_filtres extends Element {

	protected $table = 'theme_de_filtres';
	protected $primaryKey = 'id';
	
    public function getFiltresAssociesTxtAttribute() {
		
		return $this->filtres()->count();
	}
	
	public function filtres() {
	
        // return $this->belongsToMany('App\Eden\Models\Elements\Filtre', 'filtre_theme_de_filtres', 'theme_de_filtres_id', 'filtre_id');
        return $this->belongsToMany('App\Eden\Models\Elements\Filtre', 'filtre_theme_de_filtres', 'theme_de_filtres_id', 'id');
    }

    public function familles() {

        return $this->belongsToMany('App\Eden\Models\Elements\Famille', 'famille_theme_de_filtres', 'theme_de_filtres_id', 'famille_id');
    }
	
	public function tous_les_filtres() {
		
		$filtres_disponibles = array();
		
		$themes_de_filtres = modele('theme_de_filtres')->get();
		
		
		foreach($themes_de_filtres as $theme_de_filtres) {
			
			// on va chercher les filtres disponibles
			$filtres_dispo = modele('theme_de_filtres')->find($theme_de_filtres->id)->filtres;
			
			if(!empty($filtres_dispo))
				$filtres_dispo = $filtres_dispo->pluck('filtre', 'id');
			
			$filtres_disponibles[] = array(
				
				'theme_de_filtres' => $theme_de_filtres,
				'filtres_dispo' => $filtres_dispo,
			);
		}
		
		if($filtres_disponibles === null)
			return collect(array());
		
		return collect($filtres_disponibles);
	}

}
