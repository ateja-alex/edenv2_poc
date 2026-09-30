<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Script_management;

class S20250825_nom_page_historique_taille_champ implements Script {

    public function execute() {

        $champs_libres = Champ_libre::where('nom_sql', 'nom_page')->where('type_element', 'log_historique')->get();

        $nouvelles_informations = array(
            'type' => 6,
        );

        Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, $champs_libres, true);

        return true;
    }

}