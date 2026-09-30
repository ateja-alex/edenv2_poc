<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Migrations\Scripts\Script;

class S20231031_script_migration_vue_sql implements Script {

    public function execute() {

        $vue_sql = \App\Eden\Champs_libres::migration_vue_sql(true);

        foreach ($vue_sql as $cle => $valeur){

            $vue_existante = modele('vue_sql')->select('id','nom','joins','tables','alias_tables','table_par_defaut','type_de_vue','requete','autres_conditions')->where('nom', $valeur['nom'])->first();

            if(empty($vue_existante))
                continue;

            $vue_existante = $vue_existante->toArray();
            $vue_existante_id = $vue_existante['id'];

            unset($vue_existante['id']);
            $difference = array_diff($vue_existante, $valeur);

            if(!empty($difference)){
                $vue_sql_management = management('vue_sql', $vue_existante_id, $vue_existante);
                $vue_sql_management->generer_migration();
            }

        }
        return true;
    }
}