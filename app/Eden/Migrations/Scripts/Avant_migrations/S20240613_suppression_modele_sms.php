<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Listes_libres;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Rapport_libre;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class S20240613_suppression_modele_sms implements Script
{
    public function execute()
    {
        // On delete les migrations spé (hors Rapport)
        $app_path = app_path();
        $migration_list = [
            "/Migrations/modele_sms.php",
            "/Migrations/Formulaires_libres/modele_sms.php",
            "/Migrations/Listes_libres/modele_sms.php",
            "/../storage/app/eden_fiche_modele_sms.php",
        ];

        foreach ($migration_list as $migration) {
            if (file_exists($app_path . $migration)) {
                unlink($app_path . $migration);
            }
        }


        //Champs libres
        $champslibres = Champ_libre::where('type_element', "modele_sms")->get();

        foreach ($champslibres as $champslibre)
            DB::select("DELETE FROM eden_champslibres_listes WHERE id_cl = $champslibre->id_cl");


        DB::select("DELETE FROM eden_champslibres_valeur_defaut_creation WHERE type_element = 'modele_sms'");
        DB::select("DELETE FROM eden_champslibres WHERE type_element = 'modele_sms'");

        //Formulaire
        DB::select("DELETE FROM eden_formulaireslibres_champs WHERE type_element = 'modele_sms'");
        DB::select("DELETE FROM eden_formulaireslibres WHERE type_element = 'modele_sms'");

        //Tables Libres
        $listeLibres = DB::table("eden_listeslibres")->where('type_element', "modele_sms")->get();
        foreach ($listeLibres as $listeLibre) {
            DB::select("DELETE FROM  	eden_listes_libres_autresvues WHERE liste_libre_id_1 = $listeLibre->id OR liste_libre_id_2 = $listeLibre->id");
            DB::select("DELETE FROM eden_listes_libres_calculs WHERE liste_libre_id = $listeLibre->id");
            DB::select("DELETE FROM eden_listes_libres_couleur WHERE liste_libre_id = $listeLibre->id");
            DB::select("DELETE FROM eden_listes_libres_filtres WHERE liste_libre_id = $listeLibre->id");
            DB::select("DELETE FROM eden_listes_libres_filtres_enregistres WHERE liste_id = $listeLibre->id");
        }

        DB::select("DELETE FROM eden_listeslibres WHERE type_element = 'modele_sms'");

        //Rapports
        $rapportLibres = Rapport_libre::where('type_element', "modele_sms")->get();
        foreach ($rapportLibres as $rapportLibre) {
            //On delete la migration spé du rapport
            $migration = "/Migrations/Rapports/$rapportLibre->id_rapport.php";

            if (file_exists($app_path . $migration)) {
                unlink($app_path . $migration);
            }

            //On clean la table
            DB::select("DELETE FROM eden_rapports_parametres WHERE id = $rapportLibre->id");
        }

        DB::select("DELETE FROM eden_rapports WHERE type_element = 'modele_sms'");

        //License
        DB::select("DELETE FROM licence_ensemble_element WHERE nom = 'modele_sms'");
        DB::select("DELETE FROM licence_element WHERE nom = 'modele_sms'");

        //Table
        DB::select("DELETE FROM eden_tableslibres WHERE type_element = 'modele_sms'");

        if (Schema::hasTable('modele_sms')) {
            Schema::drop("modele_sms");
        }


        return true;
    }
}
