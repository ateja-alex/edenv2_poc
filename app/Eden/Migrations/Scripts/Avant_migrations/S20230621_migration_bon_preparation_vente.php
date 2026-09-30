<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Migrations\Scripts\Script;
use Illuminate\Support\Facades\DB;

class S20230621_migration_bon_preparation_vente implements Script {

    public function execute() {

        if(is_file(app_path('Migrations/bon_preparation_vente_lignes.php'))) {

            $contenu_tmp = file_get_contents(app_path('Migrations/bon_preparation_vente_lignes.php'));

            $contenu_tmp = str_replace(
                [
                    "'type_element_ajax' => \"bl_vente\"",
                    "'nom' => \"Bl\"",
                    "'type_element_ajax' => \"bl_vente_lignes\"",
                ],
                [
                    "'type_element_ajax' => \"bon_preparation_vente\"",
                    "'nom' => \"Bon de préparation\"",
                    "'type_element_ajax' => \"bon_preparation_vente_lignes\"",
                ],
                $contenu_tmp
            );

            $chemin_avec_nom_document = app_path('Migrations/bon_preparation_vente_lignes.php');

            if (file_exists($chemin_avec_nom_document) == true)
                unlink($chemin_avec_nom_document);

            $fichier = fopen($chemin_avec_nom_document, "x+");
            fputs($fichier, $contenu_tmp );
            fclose($fichier);
        }

        $champ_libre = champ_libre_modele('bon_preparation_vente_lignes','document_id');

        $nom_contrainte = 'CE_champ_libre_' .$champ_libre->id_cl;

        try {
            DB::select('ALTER TABLE `bon_preparation_vente_lignes` DROP FOREIGN KEY `' . $nom_contrainte . '`;');
        }
        catch (\Exception $e ){

        }

        $champ_libre = champ_libre_modele('bon_preparation_vente_lignes','nomenclature_ligne_parent');

        $nom_contrainte = 'CE_champ_libre_' .$champ_libre->id_cl;

        try {
            DB::select('ALTER TABLE `bon_preparation_vente_lignes` DROP FOREIGN KEY `' . $nom_contrainte . '`;');
        }
        catch (\Exception $e ){

        }

        return true;
    }
}
