<?php

namespace App\Eden\Managements\Listes;

use App\Eden\Managements\Listes_management;

class Liste_fiche_fournisseur_paiement_management extends Listes_management {

    /**
     *
     * Fonction qui permet d'effectuer un traitement particulier sur les calculs
     *
     */
    protected function traitement_calculs_specifiques() {

        return array(
            'valeurs_changantes' => array(
                'montant' => '* -1',
            )
        );
    }

}
