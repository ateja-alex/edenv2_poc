<?php

namespace App\Eden\Models\Elements;
use DB;

class Utilisateur extends Element {
	
	public $timestamps = false;

	//---------------------------------------------------------- Relations
	public function taches() {

		return $this->hasMany(Tache::class, 'affectation', 'id')->orderBy('date_de_debut');
	}

	public function recupere_utilisateurs_pour_feuilles_de_temps() {
		
		return modele('utilisateur')->select(DB::raw("concat(prenom,' ',nom) as nom, id as id"))->orderBy('super_admin')->orderBy('prenom')->get()->pluck('nom', 'id')->toArray();
    }

	public function liste_utilisateurs_visibles($requete = null) {

        $requete = $requete === null ? modele('utilisateur') : $requete;

        if(super_admin())
            return $requete->orderBy('nom')->get();
        else
            return $requete->zero_ou_null('super_admin')->orderBy('nom')->get();
    }
}