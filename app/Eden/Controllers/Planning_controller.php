<?php

namespace App\Eden\Controllers;

use App\Http\Controllers\Controller;
use App\Eden\Controllers\Planification_controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class Planning_controller extends Planification_controller {

    public function __construct() {

        $this->contexte = 'planning';
    }

	/**
	 *
	 * Page d'accueil du planning
	 *
	 */
	public function index() {

		return view('eden::planning.index');
	}

    /**
	 *
	 *  Initilisiation des données nécessaires à l'affichage du planning
	 *
	 */
	public function initialisation(Request $requete) {

        $parametres = $requete->all();

        $parametres['initialisation'] = true;

        $donnees  = service('planning')->recuperer_donnees($parametres);

        $donnees['utilisateurs'] = $donnees['utilisateurs'];
        $donnees['equipes'] = modele('equipe')->get()->keyBy('id');

		return response()->json($donnees);
	}

     /**
	 *
	 * Actualisation des données du planning
	 *
	 */
	public function actualisation(Request $requete) {

        $parametres = $requete->all();

        $donnees  = service('planning')->recuperer_donnees($parametres);

		return response()->json($donnees);
	}
}
