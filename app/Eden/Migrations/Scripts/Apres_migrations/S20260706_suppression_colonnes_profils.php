<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Maintenance_management;
use App\Eden\Managements\Parametrage\Liste_libre_management;
use App\Eden\Managements\Parametrage\Table_libre_management;
use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Liste_libre;
use DB;

class S20260706_suppression_colonnes_profils implements Script{
    public function execute(){
        DB::select('ALTER TABLE listes_libres_colonnes DROP COLUMN profils;');
        DB::select('ALTER TABLE eden_formulaireslibres_champs DROP COLUMN profils;');
        DB::select('ALTER TABLE eden_listes_libres_calculs DROP COLUMN profils;');
        DB::select('ALTER TABLE eden_listes_libres_filtres DROP COLUMN profils;');

        if(is_dir(app_path('Migrations'))) {

            $repertoire_champs_libres = scandir(app_path('Migrations'));

            foreach ($repertoire_champs_libres as $fichier) {

                if (in_array($fichier, array('.', '..', 'Rapports', 'Listes_libres', 'Listes_libres_fiches', 'Listes_libres_export', 'Tables', 'Formulaires_libres', 'Sous_formulaire', 'Champs_libres_listes', 'Champs_libres_listes_formatees', 'Listes_libres_couleurs', 'Listes_libres_autresvues', 'Scripts', 'Vue_sql')))
                    continue;

                $type_element = str_replace('.php','',$fichier);

                Table_libre_management::generer_fichier_migration($type_element,true);

            }

            $repertoires_listes = ['Rapports', 'Listes_libres', 'Listes_libres_fiches', 'Listes_libres_export', 'Listes_libres_autresvues'];

            foreach ($repertoires_listes as $listes) {

                $listes_migrations_listes = [];

                $chemin_dossier = app_path('Migrations/' . $listes);

                if (!is_dir($chemin_dossier))
                    continue;

                $fichiers = scandir($chemin_dossier);

                foreach ($fichiers as $fichier) {

                    if (in_array($fichier, ['.', '..']))
                        continue;

                    $nom_liste = str_replace('.php', '', $fichier);

                    $listes_migrations_listes[] = $nom_liste;

                }

                $listes_concernees = Liste_libre::where(function($query) use ($listes_migrations_listes) {
                    $query->whereIn('id_rapport', $listes_migrations_listes)
                        ->orWhere(function($sous_query) use ($listes_migrations_listes) {
                            $sous_query->where(function($q) {
                                    $q->whereNull('id_rapport')
                                        ->orWhere('id_rapport', '');
                                })
                                ->whereIn('type_element', $listes_migrations_listes);
                        });
                })
                ->get()
                ->unique('id');

                foreach ($listes_concernees as $liste) {
                    Liste_libre_management::generer_fichier_migration_liste_libre($liste->id, true);
                }
            }

            $chemin_dossier = app_path('Migrations/Formulaires_libres');

            if (is_dir($chemin_dossier)){
                $fichiers = scandir($chemin_dossier);

                foreach ($fichiers as $fichier) {

                    if (in_array($fichier, ['.', '..']))
                        continue;

                    $nom_formulaire = str_replace('.php', '', $fichier);

                    Maintenance_management::generer_fichier_migration_formulaire($nom_formulaire);
                }
            }
        }

        return true;
    }
}