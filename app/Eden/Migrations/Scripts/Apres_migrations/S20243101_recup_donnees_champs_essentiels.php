<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Parametrage\Table_libre_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class S20243101_recup_donnees_champs_essentiels implements Script
{
    public function execute()
    {
        $type_element_load_from_migration = function(){
            $type_elements = [];

            $repertoires_tables_libres = scandir(app_path('Migrations'));
            foreach ($repertoires_tables_libres as $fichier) {
                if (in_array($fichier, array('.', '..', 'Rapports', 'Listes_libres', 'Listes_libres_fiches', 'Listes_libres_export', 'Tables', 'Formulaires_libres', 'Sous_formulaire', 'Champs_libres_listes', 'Champs_libres_listes_formatees', 'Listes_libres_couleurs', 'Listes_libres_autresvues', 'Scripts', 'Vue_sql')))
                    continue;

                $type_elements[] = str_replace('.php', '', $fichier);
            }
            return $type_elements;
        };

        $champs_libres_essentiels = [];
        if (Schema::hasColumn('eden_champslibres', 'essentiel'))
            $champs_libres_essentiels = Champ_libre::where('essentiel', 1)->get()->groupBy('type_element');

        if (is_dir(app_path('Migrations'))) {

            foreach ($type_element_load_from_migration() as $type_element) {

                $table_libre = table_libre($type_element);

                if (empty($table_libre->affichage_dans_liste) && !empty($champs_libres_essentiels[$type_element])) {

                    $chaine_affichage_tableau = [];

                    foreach ($champs_libres_essentiels[$type_element] as $champ_libre) {
                        array_push($chaine_affichage_tableau, '#' . $champ_libre['nom_sql'] . '#');
                    }

                    $chaine_affichage = implode(', ', $chaine_affichage_tableau);

                    $table_libre->affichage_dans_liste = $chaine_affichage;

                    $table_libre->save();
                }

            }
        }

        if (Schema::hasColumn('eden_champslibres', 'essentiel'))
            Schema::table('eden_champslibres', function (Blueprint $table) {
                $table->dropColumn('essentiel');
            });

        //On regenere les migrations sans le champ essentiel
        foreach ($type_element_load_from_migration() as $type_element)
            Table_libre_management::generer_fichier_migration($type_element, true);

        return true;
    }
}
