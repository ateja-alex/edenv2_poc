<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Managements\Script_management;
use App\Eden\Models\Champ_libre;
use DB;

class S20220929_modification_champ_ecriture_id implements Script {

    public function execute() {

        Script_management::change_type_de_colonne_sur_table('ecriture_comptable', 'ecriture_id', 'INT(11)');

		$champs_libres = Champ_libre::where('nom_sql', 'ecriture_id')->where('type_element','ecriture_comptable')->get();

        $nouvelles_informations = array(
            'type' => 2,
        );

        Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations,$champs_libres);


        return true;
    }
}
