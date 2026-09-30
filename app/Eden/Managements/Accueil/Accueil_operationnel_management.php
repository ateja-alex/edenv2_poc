<?php

namespace App\Eden\Managements\Accueil;

use App\Eden\Managements\Accueil_management ;
use App\Eden\Managements\Fiches\Fiche_client_management_eden ;

/**
 * Gestion des fiches projets
 */
class Accueil_operationnel_management extends Accueil_management {
	
	/**
     * 
     * Prépare les données pour la page d'accueil
     * 
     * @attention si vous utilisez cette méthode dans les classes filles, il faut faire appelle à parent::prepare_donnees_pour_accueil($donnees)
     * 
     */
    public function prepare_donnees_pour_accueil($donnees = array()) {
        
        $donnees['rapport_commande_vente'] = rapport('suivi_commande_vente_kanban')->genere();
        $donnees['rapport_bl_vente'] = rapport('suivi_bl_vente_kanban')->genere();
        $donnees['rapport_facture_vente'] = rapport('suivi_facture_vente_kanban')->genere();
                                                
        return $donnees;
    }


   
	
}