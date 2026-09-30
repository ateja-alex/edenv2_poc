<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Parametrage\Champ_libre_management;
use App\Eden\Managements\Script_management;

class S20220531_saisie_documents_afficher_prix implements Script {

    public function execute() {

        $nom_nouvelle_fonctionnalite = 'saisie_documents_afficher_prix';

        $correspondance = array(
            'bl_vente' => 'saisie_documents_afficher_prix_sur_bl',
            'bl_achat' => 'saisie_documents_afficher_prix_sur_bl',
        );
        $retour_regroupement = Script_management::regrouper_fonctionnalite([$nom_nouvelle_fonctionnalite => $correspondance]);

        return $retour_regroupement;
    }
}
