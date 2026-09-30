<?php

namespace App\Eden\Managements\Rapports\Filtres_et_options;

use App\Eden\Managements\Rapports\Rapports_management;

/**
 *
 * Gestion des exports excel
 *
 */
class Valeur_fourchette {

    public static function applique($rapport) { 

        // on récupère les paramètres
        if(isset($rapport->parametres)) {
			
			$parametres = $rapport->parametres;
		}
		else {
			
			$parametres = Rapports_management::recupere_parametres($rapport->id_rapport);
		}

        $valeurs = [
            'min' => 0,
            'max' => 100000
        ];

        if(!empty(request()->get('valeur_fourchette'))) {
			
            list($min, $max) = request()->get('valeur_fourchette');
            $valeurs = [
                'min' => $min,
                'max' => $max
            ];
        }
        else {

            if(isset($parametres['valeur_fourchette'])) {
				$valeurs = $parametres['valeur_fourchette'];
			}
        }

        $parametres = array('valeur_fourchette' => $valeurs);


        Rapports_management::enregistre_parametres($rapport->id_rapport, $parametres, true);

        $rapport->option('valeur_fourchette', array('valeur_fourchette' => $valeurs));

        return $valeurs;
        

    }
}