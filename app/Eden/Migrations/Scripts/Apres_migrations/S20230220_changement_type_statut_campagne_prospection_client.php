<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Script_management;


class S20230220_changement_type_statut_campagne_prospection_client implements Script {

    public function execute() {

        $champs_libres = Champ_libre::where('nom_sql', 'statut')->where('type_element', 'campagne_de_prospection_client')->get();

        if(empty($champs_libres))
            return true;

        $nouvelles_informations = array(
            'type' => 20,
            'liste_choix' => 610,
        );

        Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, $champs_libres);

        return true;
    }
}

