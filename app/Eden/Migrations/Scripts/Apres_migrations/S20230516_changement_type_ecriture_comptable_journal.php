<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Script_management;

class S20230516_changement_type_ecriture_comptable_journal implements Script {

    public function execute() {

        $champs_libres = Champ_libre::where('nom_sql', 'journal_id')->where('type_element', 'ecriture_comptable')->get();

        $nouvelles_informations = array(
            'type' => 42,
            'type_element_ajax' => 'journal_comptable',
        );

        Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, $champs_libres);

        return true;
    }
}

