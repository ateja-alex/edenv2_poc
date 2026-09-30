<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;
use Illuminate\Support\Facades\Schema;

class S20241206_changement_type_colonne_type_vue implements Script {

    public function execute() {

        if(Schema::hasTable('eden_formulaireslibres_champs') && Schema::hasColumn('eden_formulaireslibres_champs', 'type_vue'))
            Script_management::change_type_de_colonne_sur_table('eden_formulaireslibres_champs', 'type_vue', 'VARCHAR(300)');

        return true;
    }
}

