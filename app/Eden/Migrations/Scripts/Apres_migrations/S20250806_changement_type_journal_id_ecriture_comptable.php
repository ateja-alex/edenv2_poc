<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Script_management;
use Illuminate\Support\Facades\DB;

class S20250806_changement_type_journal_id_ecriture_comptable implements Script {

    public function execute() {

        $champs_libres = Champ_libre::where('type', 20)->where('liste_choix', 30)->get();

        $nouvelles_informations = array(
            'type' => 42,
            'type_element_ajax' => 'journal_comptable',
            'liste_choix' => null
        );

        Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, $champs_libres, true);

        return true;
    }
}