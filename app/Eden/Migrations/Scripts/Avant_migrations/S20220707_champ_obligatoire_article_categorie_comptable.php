<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Script_management;

class S20220707_champ_obligatoire_article_categorie_comptable implements Script {

    public function execute() {

        $champs_libres = Champ_libre::whereIn('nom_sql',array('categorie_comptable_id','article_id'))->where('type_element','article_categorie_comptable')->get();

        $nouvelles_informations = array(
            'obligatoire' => 1,
        );

        $retour = Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations,$champs_libres);

        return $retour;
    }
}
