<?php

namespace App\Eden\Managements;

/**
 * Gestion des paramètres utilisateur de l'ERP
 */
class Parametres_erp_management {

    /**
     * 
     * Retourne une liste de paramètres
     * 
     * @return $array
     * 
     */
    public static function liste($liste) {
		
		$parametres = array();
		
		foreach($liste as $id_entite => $parametres_par_entite) {
			
			if(!isset($parametres[$id_entite]))
				$parametres[$id_entite] = array();
				
			foreach($parametres_par_entite as $nom) {
				
				if($id_entite == 0) {
					
					$parametres[$id_entite][$nom] = parametre($nom);
				}
				else {
					
					$parametres[$id_entite][$nom] = parametre_entite($id_entite, $nom);
				}
			}
		}

		return $parametres;
    }

    /**
     * 
     * Retourne une liste de paramètres qui ne sont pas liés à une entité
     * 
     * @return $array
     * 
     */
    public static function liste_sans_entite($liste) {
		
		$parametres = array();
		
		foreach($liste as $nom) {
				
			$parametres[$nom] = parametre($nom);
		}

		return $parametres;
    }

    
}
