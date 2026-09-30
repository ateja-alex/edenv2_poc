<?php

namespace App\Eden\Managements\Fiches;

use App\Eden\Managements\Fiche_management;

use App\Eden\Models\Element_piece_jointe;
use DB;

/**
 * Gestion des fiches fournisseurs
 */
class Fiche_ticket_client_management extends Fiche_management {

    /**
     * @return Array
     *
     * Permet de récupérer les options des fils arianes
     *
     */
    public function options_fil_ariane($donnees){

        $options_fil_ariane = parent::options_fil_ariane($donnees);

        $options_fil_ariane[] = [
            'id' => 'planifier_intervention',
            'ordre' => -1
        ];

        $options_fil_ariane[] = [
            'id' => 'blackliste',
            'ordre' => 0
        ];

        return $options_fil_ariane;
    }
}
