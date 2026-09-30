<?php

namespace App\Eden\Managements\Rapports\Filtres_et_options;

use App\Eden\Managements\Rapports\Rapports_management;

/**
 *
 * Gestion des exports excel
 *
 */
class Recherche {

    public static function applique($rapport) {

        // on récupère les paramètres
        if(isset($rapport->parametres)) {

            $parametres = $rapport->parametres;
        }
        else {

            $parametres = Rapports_management::recupere_parametres($rapport->id_rapport);
        }

        $valeur = '';

        if((request()->has('recherche_' . $rapport->id_rapport))) {

            $valeur = request()->get('recherche_' . $rapport->id_rapport);
        }
        else {

            if(isset($parametres['recherche'])) {

                $valeur = $parametres['recherche'];
            }
        }

        // on va enregistrer l'entité choisie
        $parametres = array('recherche' => $valeur);

        Rapports_management::enregistre_parametres($rapport->id_rapport, $parametres, true);

        $rapport->option('recherche', array('valeur' => $valeur));

        return $valeur;
    }
}