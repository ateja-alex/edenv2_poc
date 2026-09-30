<?php

namespace App\Eden\Managements\Rapports\Rapports;

use App\Eden\Managements\Calcul_gescom_management;

use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_liste_management;

use Illuminate\Http\Request;

/**
* Gestion des rapports
*/
class Activites_par_utilisateur_management extends Rapports_management {

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
	 * On calcule le CA par mois
	 * 
	 */	
    public function genere($ajax = false) {
		
		// on va chercher les utilisateurs et les temps saisis
		$activites = modele('activite_projet')->whereIn('statut', array(0,1))->orderBy('utilisateur_id')->orderBy('projet_id')->get();
		
		$dernier_utilisateur = false;
		$dernier_projet = false;
		
		foreach($activites as $activite) {
			
			if($activite->utilisateur_id !== $dernier_utilisateur) {
				
				if(empty($activite->utilisateur_id)) {
					
					$ligne = array('<b>Non affectées</b>');
					
					$this->rapport->sous_titre($ligne);
				}
				else {
					
					$utilisateur = modele('utilisateur', $activite->utilisateur_id);
					
					$ligne = array('<b>'.$utilisateur->prenom.' '.$utilisateur->nom.'</b>');
					
					$this->rapport->sous_titre($ligne);
					
				}
				
				$dernier_utilisateur = $activite->utilisateur_id;
				$dernier_projet = false;
			}
			
			if($activite->projet_id !== $dernier_projet) {
				
				$projet = management('projet', $activite->projet_id);
				
				$ligne = array($projet->affiche_lien());
				
				$this->rapport->sous_titre($ligne);
				
				$dernier_projet = $activite->projet_id;
			}
			
			$ligne = array($activite->activite);
			$this->rapport->ligne($ligne);
			
			
		}
		
		
		
		return $this->rapport->genere($ajax);
    }
	
	
	
	
	
}