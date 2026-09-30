<?php

namespace App\Eden\Managements\Accueil;

use App\Eden\Managements\Accueil_management ;

/**
 * Gestion des fiches projets
 */
class Accueil_standard_management extends Accueil_management {
	
	/**
     * 
     * Prépare les données pour la page d'accueil
     * 
     * @attention si vous utilisez cette méthode dans les classes filles, il faut faire appelle à parent::prepare_donnees_pour_accueil($donnees)
     * 
     */
    public function prepare_donnees_pour_accueil($donnees = array()) {
        
        // on va chercher les données de base
        $donnees = parent::prepare_donnees_pour_accueil($donnees);

        $rappels = modele('rappel')->orderBy('deadline')->whereIn('statut', array(0,1))->where('deadline', '<=', date('Y-m-d', strtotime("now +14 days")))->get();
        
        foreach($rappels as $rappel) {
            
            $rappel->deadline = formate_date('d/m/Y', $rappel->deadline);
        }
        
        $donnees['rappels'] = $rappels ;

        $donnees['informations_pour_tous'] = modele('information_pour_tous')
                                                ->orderBy('cree_le', 'DESC')
                                                ->where('cree_le', '>=', date('Y-m-d', strtotime("now -30 days")))->get();
                                                
        return $donnees;
    }

    
    
    /**
     * 
     * Retourne la liste des rappels
     * 
     */
    public function liste_rappels() {
        
        $rappels = modele('rappel')->orderBy('deadline')->whereIn('statut', array(0,1))->where('deadline', '<=', date('Y-m-d', strtotime("now +14 days")))->get();
        
        foreach($rappels as $rappel) {
            
            $rappel->deadline = formate_date('d/m/Y', $rappel->deadline);
        }
        
        return response()->json($rappels);
    }
    
    /**
     * 
     * Retourne la liste des rappels
     * 
     */
    public function liste_informations_pour_tous() {
        
        $informations_pour_tous = modele('information_pour_tous')->orderBy('cree_le', 'DESC')->where('cree_le', '>=', date('Y-m-d', strtotime("now -30 days")))->get();
        
        return response()->json($informations_pour_tous);
    }
	
}