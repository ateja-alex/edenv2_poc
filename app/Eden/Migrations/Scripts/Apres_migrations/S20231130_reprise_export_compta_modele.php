<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use Illuminate\Support\Facades\DB;

class S20231130_reprise_export_compta_modele implements Script {

    public function execute() {

        DB::select('UPDATE export_compta_modele SET type_element = "ecriture_comptable"');

        $champs_libres = Champ_libre::where('nom_sql', 'export_compta_modele_id')->where('type_element', 'export_compta_colonne')->get();

        $nouvelles_informations = array(
            'type' => 42,
            'type_element_ajax' => 'export_compta_modele',
        );

        Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, $champs_libres);

        return true;
    }

}