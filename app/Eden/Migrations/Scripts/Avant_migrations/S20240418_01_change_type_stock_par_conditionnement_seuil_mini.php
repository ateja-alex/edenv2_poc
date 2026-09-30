<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use Illuminate\Support\Facades\DB;

class S20240418_01_change_type_stock_par_conditionnement_seuil_mini implements Script
{

    /* Correction du type pour les

    -colonne seuil_mini
    -seuil_mini_unite

    dans les tables
    -stocks_par_conditionnement
    -stocks_tous_les_entrepots_par_conditionnement
    -stocks
    -seuil_article
    -stocks_tous_les_entrepots

    */
    public function execute()
    {
        try {
            DB::select("UPDATE eden_champslibres SET type = 3 WHERE type_element = 'stocks_par_conditionnement' AND nom_sql = 'seuil_mini'");
            DB::select("UPDATE eden_champslibres SET type = 3 WHERE type_element = 'stocks_par_conditionnement' AND nom_sql = 'seuil_mini_unite'");
            DB::select("UPDATE eden_champslibres SET type = 3 WHERE type_element = 'stocks_tous_les_entrepots_par_conditionnement' AND nom_sql = 'seuil_mini'");
            DB::select("UPDATE eden_champslibres SET type = 3 WHERE type_element = 'stocks_tous_les_entrepots_par_conditionnement' AND nom_sql = 'seuil_mini_unite'");
            DB::select("UPDATE eden_champslibres SET type = 3 WHERE type_element = 'stocks_tous_les_entrepots' AND nom_sql = 'seuil_mini'");
            DB::select("UPDATE eden_champslibres SET type = 3 WHERE type_element = 'stocks' AND nom_sql = 'seuil_mini'");
            DB::select("UPDATE eden_champslibres SET type = 3 WHERE type_element = 'seuil_article' AND nom_sql = 'seuil_mini'");
        } catch (\Exception $e) {
            return new \Exception("Erreur lors de la mise à jour des type = 3 dans eden_champslibres pour les seuils minis." + $e->getMessage());
        }
        return true;
    }
}
