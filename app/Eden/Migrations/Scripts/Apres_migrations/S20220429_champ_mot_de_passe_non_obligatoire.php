<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Parametrage\Champ_libre_management;

class S20220429_champ_mot_de_passe_non_obligatoire implements Script {

    public function execute() {

        $champ_mot_de_passe = Champ_libre::where('type_element', 'utilisateur')->where('nom_sql','mot_de_passe')->first();

        if(empty($champ_mot_de_passe['obligatoire']))
            return true;

        $champ_mot_de_passe['obligatoire'] = 0;

        Champ_libre_management::enregistre('utilisateur',$champ_mot_de_passe);

        return true;
    }
}
