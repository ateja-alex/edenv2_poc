<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Script_management;

class S20230417_changement_champ_conditionnement_fournisseur implements Script {

    public function execute() {

        $champs_libres = Champ_libre::where('type_element','article_fournisseur')->where('nom_sql','conditionnement_id')->get();

        $nouvelles_informations = array(
            'format_champ' => '',
        );

        $retour = Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations,$champs_libres);

        return $retour;
    }
}
