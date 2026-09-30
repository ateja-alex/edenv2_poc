<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Script_management;

class S20231020_changement_type_affectation_tache implements Script {

    public function execute() {

        $champs_libres = Champ_libre::where(function($condition){
            $condition->where(function($where){
                $where->where('nom_sql', 'affectation')->where('type_element', 'tache');
            })->orWhere(function($where){
                $where->where('nom_sql', 'employe_id')->where('type_element', 'employe_demande_conge');
            });
        })->get();

        $nouvelles_informations = array(
            'type' => 42,
            'type_element_ajax' => 'utilisateur',
        );

        Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, $champs_libres);

        return true;
    }
}

