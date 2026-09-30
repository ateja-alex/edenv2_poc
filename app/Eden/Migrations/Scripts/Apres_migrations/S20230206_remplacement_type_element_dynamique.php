<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

use App\Eden\Managements\Parametrage\Champ_libre_management;
use App\Eden\Managements\Script_management;

use App\Eden\Models\Champ_libre;

class S20230206_remplacement_type_element_dynamique implements Script
{

    public function execute()
    {

        $array_elements_a_modifier = array();

        $array_elements_a_modifier[] = array(
            'type_element' => 'echange',
            'element_id' => 'element_id',
            'nom_sql' => 'type_element',
            'types_disponibles' => array_keys(service('echange')->liste_echanges_possibles()),
        );

        Script_management::passage_champ_type_element_dynamique($array_elements_a_modifier);

        return true;
    }
}
