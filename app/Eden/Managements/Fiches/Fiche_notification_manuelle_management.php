<?php

namespace App\Eden\Managements\Fiches;

use App\Eden\Managements\Fiche_management;


/**
 * Gestion des fiches lots
 */
class Fiche_notification_manuelle_management extends Fiche_management {

    /**
     * @return Array
     *
     * Permet de récupérer les options des fils arianes
     *
     */
    public function options_fil_ariane($donnees){

        $options_fil_ariane = parent::options_fil_ariane($donnees);

        $options_fil_ariane[] = [
            'id' => 'boutons_generation',
            'ordre' => 0
        ];

        return $options_fil_ariane;
    }
}
