<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Parametrage\Table_libre_management;
use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use DB;

class S20250904_performance_migrations implements Script
{

    public function execute(){

        DB::select('ALTER TABLE eden_champslibres DROP COLUMN profils_modification;');
        DB::select('ALTER TABLE eden_champslibres DROP COLUMN appliquer_profils_modification;');
        DB::select('ALTER TABLE eden_champslibres DROP COLUMN profils_creation;');
        DB::select('ALTER TABLE eden_champslibres DROP COLUMN appliquer_profils_creation;');

        if(is_dir(app_path('Migrations'))) {

            $repertoire_champs_libres = scandir(app_path('Migrations'));

            foreach ($repertoire_champs_libres as $fichier) {

                if (in_array($fichier, array('.', '..', 'Rapports', 'Listes_libres', 'Listes_libres_fiches', 'Listes_libres_export', 'Tables', 'Formulaires_libres', 'Sous_formulaire', 'Champs_libres_listes', 'Champs_libres_listes_formatees', 'Listes_libres_couleurs', 'Listes_libres_autresvues', 'Scripts', 'Vue_sql')))
                    continue;

                $type_element = str_replace('.php','',$fichier);

                Table_libre_management::generer_fichier_migration($type_element,true);

            }

        }

        $types_elements = [
            1 => 'utilisateur',
            2 => 'famille',
            79 => 'tableau_de_bord',
            74 => 'modele_email',
            64 => 'equipe'
        ];

        foreach($types_elements as $liste_choix => $type_element){

            $champsr_simple = Champ_libre::where('type', 20)->where('liste_choix', $liste_choix)->get();
            $champs_multiple = Champ_libre::where('type', 11)->where('liste_choix', $liste_choix)->get();

            Script_management::liste_formatee_a_autre_type_champ([
                'type' => 42,
                'type_element_ajax' => $type_element,
            ], $champsr_simple);

            Script_management::liste_formatee_a_autre_type_champ([
                'type' => 10,
                'type_element_ajax' => $type_element,
            ], $champs_multiple);
        }

        return true;
    }
}