<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Parametrage\Liste_libre_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Liste_libre;
use App\Eden\Models\Liste_libre_filtre;

class S20260820_suppression_doublons_filtres_listes_libres implements Script {

    public function execute(){

        $doublons = Liste_libre_filtre::select('liste_libre_id', 'nom_sql', 'type_element', 'rapport_id')
            ->groupBy('liste_libre_id', 'nom_sql', 'type_element', 'rapport_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $listes_libres_id_a_regenerer = [];

        foreach($doublons as $doublon) {

            $filtres = Liste_libre_filtre::where('liste_libre_id', $doublon->liste_libre_id)
                ->where('nom_sql', $doublon->nom_sql)
                ->where('type_element', $doublon->type_element)
                ->where('rapport_id', $doublon->rapport_id)
                ->orderBy('id')
                ->get();

            // on garde le premier (le plus ancien), on supprime les autres
            $filtres->shift();

            foreach($filtres as $filtre)
                $filtre->delete();

            $listes_libres_id_a_regenerer[$doublon->liste_libre_id] = $doublon->liste_libre_id;
        }

        foreach($listes_libres_id_a_regenerer as $liste_libre_id) {

            $liste_libre = Liste_libre::find($liste_libre_id);

            if(empty($liste_libre))
                continue;

            $chemin_dossier_migrations = app_path().'/Migrations/Listes_libres';
            $nom_fichier = $liste_libre->type_element;

            if(!empty($liste_libre->id_rapport)) {

                $nom_fichier = $liste_libre->id_rapport;

                if($liste_libre->liste_sur_fiche == 1)
                    $chemin_dossier_migrations = app_path().'/Migrations/Listes_libres_fiches';
                else if($liste_libre->export == 1)
                    $chemin_dossier_migrations = app_path().'/Migrations/Listes_libres_export';
                else
                    $chemin_dossier_migrations = app_path().'/Migrations/Rapports';
            }

            $chemin_fichier = $chemin_dossier_migrations.'/'.$nom_fichier.'.php';

            if(file_exists($chemin_fichier))
                Liste_libre_management::generer_fichier_migration_liste_libre($liste_libre_id, true);
        }

        return true;
    }
}
