<?php

namespace App\Eden\Managements\Elements;

class Requete_sql_cron_management extends Element_management {
	
	/**
	 *
	 * On empeche la gestion d'un non super admin
	 *
	 */
    public function enregistre($modifications = array(), $modele = false) {

        if(!editeur()){

            return traduction('messages.php.requete_sql_cron.droit_non_disponible');
        }
		return parent::enregistre($modifications,$modele);
	}

    public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();

        $liste_options[] = 'lancer';

        return $liste_options;
    }
}