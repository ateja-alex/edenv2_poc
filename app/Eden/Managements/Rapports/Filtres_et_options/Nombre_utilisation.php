<?php

namespace App\Eden\Managements\Rapports\Filtres_et_options;

use App\Eden\Managements\Rapports\Rapports_management;

/**
 *
 * Gestion des exports excel
 *
 */
class Nombre_utilisation {

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

        if(!empty(request()->get('nb_utilisation'))) {
			
            list($min, $max) = request()->get('nb_utilisation');
            $valeurs = [
                'min' => $min,
                'max' => $max
            ];
        }
        else {

            if(isset($parametres['nb_utilisation'])) {
				$valeurs = $parametres['nb_utilisation'];
			}
        }

        $parametres = array('nb_utilisation' => $valeurs);


        Rapports_management::enregistre_parametres($rapport->id_rapport, $parametres, true);

        $rapport->option('nombre_utilisation', array('nombre_utilisation' => $valeurs));

        return $valeurs;
        

    }
}