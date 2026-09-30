<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Script_management;
use DB;

class S20250709_type_champ_validateur_utilisateur implements Script
{
    public function execute(){

        $noms_champs = ['validation_conges_n_plus_1','validation_conges_n_plus_2','validation_ndf_n_plus_1','validation_ndf_n_plus_2'];

        $champs_libres = Champ_libre::whereIn('nom_sql',$noms_champs)
            ->where('type_element', 'utilisateur')->get();

        if(empty($champs_libres))
            return true;

        $nouvelles_informations = array(
            'type' => 10,
            'type_element_ajax' => 'utilisateur',
        );

        Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, $champs_libres);

        foreach($champs_libres as $champ_libre){

            $nom_de_la_table = 'utilisateur_' . $champ_libre->nom_sql;

            if(\Schema::hasTable($nom_de_la_table))
                continue;

            //on vérifie que la table n'existe pas avant de procéder à la création

            $champ_libre->table_pivot = $nom_de_la_table;

            \Schema::create($nom_de_la_table, function ($table) {
                $table->increments('id')->unsigned();
                $table->integer('cle_locale')->unsigned();
                $table->integer('valeur')->unsigned();
            });

            $champ_libre->save();

            DB::select('INSERT INTO '.$nom_de_la_table.' (cle_locale,valeur)
                SELECT id,'.$champ_libre->nom_sql.' FROM utilisateur WHERE COALESCE('.$champ_libre->nom_sql.',0) > 0
            ');
            DB::select('UPDATE utilisateur SET '.$champ_libre->nom_sql.' = NULL');
        }

        return true;
    }
}