<?php

namespace App\Eden\Managements\Rapports\Rapports;


use App\Eden\Managements\Calcul_gescom_management;
use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_liste_management;

use Illuminate\Http\Request;

class Mes_taches_management extends Rapports_management {

    /**
     *
     * On initialise les données du rapport
     *
     */
    function __construct() {
        $this->rapport = new Rapport_liste_management();
    }
    
	/**
	 * 
	 * On calcule la balance agée
	 * 
	 */	
    public function genere($ajax = false) {
		
    if(!empty(moi())){

      $taches = modele('tache')
                      ->where('affectation', moi()->id)
                      ->leftJoin('client', 'client_id', 'client.id')
                      ->selectRaw(
                              'tache.*,
                              CONCAT(client.nom, \' \', client.prenom) as client,
                              DATEDIFF(tache.date_de_debut, NOW()) as retard, 
                              DATEDIFF(tache.date_de_debut, NOW() + INTERVAL 7 DAY ) as sept_jours')
                      ->orderBy('date_de_debut')
                      ->get();

    }

    else if(!empty(moi_extranet())){

      $taches = modele('tache')
                      ->where('client.id', moi_extranet()->contact_selectionne->client_id)
                      ->leftJoin('client', 'client_id', 'client.id')
                      ->selectRaw(
                              'tache.*,
                              CONCAT(client.nom, \' \', client.prenom) as client,
                              DATEDIFF(tache.date_de_debut, NOW()) as retard, 
                              DATEDIFF(tache.date_de_debut, NOW() + INTERVAL 7 DAY ) as sept_jours')
                      ->orderBy('date_de_debut')
                      ->get();

    }

        if($taches === null)
            $taches = collect(array());
		
		// sinon cela génère une erreur
		$this->rapport->titres(array());
		$this->rapport->ligne(array());
		
		$this->rapport->parametres_pour_vue['taches'] = $taches;
		
		$this->rapport->taches = $taches;
        $this->rapport->rapport_libre->type_rapport = 'tableau';

		return $this->rapport->genere($ajax);
    }
	
	
}