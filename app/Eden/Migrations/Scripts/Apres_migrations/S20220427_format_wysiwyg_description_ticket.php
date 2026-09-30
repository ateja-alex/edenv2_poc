<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Parametrage\Champ_libre_management;

class S20220427_format_wysiwyg_description_ticket implements Script {

    public function execute() {

        $champ_desc = Champ_libre::where('type_element', 'suivi_recette_easydev')->where('nom_sql','description')->first();

        $champ_desc['format_champ'] = "wysiwyg";

        Champ_libre_management::enregistre('suivi_recette_easydev',$champ_desc);

        return true;
    }
}
