<?php

namespace App\Eden\Managements\Accueil;

use App\Eden\Managements\Accueil_management ;
use App\Eden\Managements\Fiches\Fiche_client_management_eden ;

/**
 * Gestion des fiches projets
 */
class Accueil_commercial_management extends Accueil_management {
	
	/**
     * 
     * Prépare les données pour la page d'accueil
     * 
     * @attention si vous utilisez cette méthode dans les classes filles, il faut faire appelle à parent::prepare_donnees_pour_accueil($donnees)
     * 
     */
    public function prepare_donnees_pour_accueil($donnees = array()) {
        
        $donnees['taches'] = $this->taches() ;
                                                
        return $donnees;
    }


    /**
     *
     * Retourne les taches liées au client
     *
     * @return collection
     *
     */
    public function taches() {

        $taches = modele('tache')
                    ->where('affectation', id_utilisateur)
                    ->leftJoin('client', 'client_id', 'client.id')
                    ->selectRaw(
                            'tache.*,
                            CONCAT(client.nom, \' \', client.prenom) as client,
                            client.chaine_affichage as chaine_affichage_client,
                            DATEDIFF(tache.date_de_debut, NOW()) as retard, 
                            DATEDIFF(tache.date_de_debut, NOW() + INTERVAL 7 DAY ) as sept_jours')
                    ->orderBy('date_de_fin')
                    ->get();

        if($taches === null)
            return collect(array());

        return $taches;
    }


    public function taches_actualiser() {
        return json_encode($this->taches()) ;
    }
	
}