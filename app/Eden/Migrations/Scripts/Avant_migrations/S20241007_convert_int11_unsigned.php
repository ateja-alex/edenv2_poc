<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Champs_libres;
use App\Eden\Librairies\Budgea\Exception;
use App\Eden\Listes_libres;
use App\Eden\Managements\Parametrage\Champ_libre_management;
use App\Eden\Managements\Parametrage\Tables_libres_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Rapport_libre;
use App\Eden\Models\Table_libre;
use App\Eden\Tables_libres;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

// Migration des champs historique en liste libre/formater et 42 en int11 unsigned
class S20241007_convert_int11_unsigned implements Script
{
    public function execute()
    {
        try {
            DB::select("SET foreign_key_checks = 0;");
            // On modifie le charset des colonnes nom_sql, type_element, type_element_ajax et table_pivot pour eviter les problèmes
            DB::select("ALTER TABLE eden_champslibres MODIFY nom_sql varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            DB::select("ALTER TABLE eden_champslibres MODIFY type_element varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            DB::select("ALTER TABLE eden_champslibres MODIFY type_element_ajax varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            DB::select("ALTER TABLE eden_champslibres MODIFY table_pivot varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

            // On liste tout les champs libre de type 42, 10, 11, 12 qui se sont pas en int unsigned 11
            $champ_list_change = [];

            $champ_lignes = DB::table("eden_champslibres as C")
                ->join('information_schema.COLUMNS as IC', function ($join) {
                    $join->on("IC.COLUMN_NAME", "=", DB::raw("C.nom_sql COLLATE utf8mb4_unicode_ci"))
                    ->on("IC.TABLE_NAME", "=", DB::raw("C.type_element COLLATE utf8mb4_unicode_ci"));
                })
                ->join('information_schema.TABLES as IT', function ($join) {
                    $join->on("IT.TABLE_NAME", "=", "IC.TABLE_NAME")
                        ->on("IC.TABLE_SCHEMA", "=", "IT.TABLE_SCHEMA")
                        ->where("IT.TABLE_SCHEMA", "=", env("DB_DATABASE"))
                        ->where("IT.TABLE_TYPE", "=", "BASE TABLE");
                })
                ->whereIn("C.type", [42, 10, 11, 12])
                ->where("IC.COLUMN_TYPE", "!=", 'int(11) unsigned')
                ->where("C.inactif", "=", 0)
                ->select("IC.TABLE_NAME", "IC.COLUMN_NAME")
                ->distinct()
                ->get();

            foreach ($champ_lignes as $champ) {
                $champ_list_change[$champ->TABLE_NAME][$champ->COLUMN_NAME]["nom_sql"] = $champ->COLUMN_NAME;
            }

            // On liste tout les colonnes ID des table lié aux champs de type 42, 10, 11, 12 qui se sont pas en int unsigned 11
            $champ_lignes = DB::table("eden_champslibres as C")
                ->join('information_schema.COLUMNS as IC', function ($join) {
                    $join->on("IC.TABLE_NAME", "=", DB::raw("C.type_element_ajax COLLATE utf8mb4_unicode_ci"))
                        ->where("IC.COLUMN_NAME", "=", "id");
                })
                ->join('information_schema.TABLES as IT', function ($join) {
                    $join->on("IT.TABLE_NAME", "=", "IC.TABLE_NAME")
                        ->on("IC.TABLE_SCHEMA", "=", "IT.TABLE_SCHEMA")
                        ->where("IT.TABLE_SCHEMA", "=", env("DB_DATABASE"))
                        ->where("IT.TABLE_TYPE", "=", "BASE TABLE");
                })
                ->whereIn("C.type", [42, 10, 11, 12])
                ->where("IC.COLUMN_TYPE", "!=", 'int(11) unsigned')
                ->where("C.inactif", "=", 0)
                ->select("IC.TABLE_NAME", "IC.COLUMN_NAME")
                ->distinct()
                ->get();

            foreach ($champ_lignes as $champ) {
                $champ_list_change[$champ->TABLE_NAME][$champ->COLUMN_NAME]["nom_sql"] = $champ->COLUMN_NAME;
            }

            // On liste tout les champs des tables pivot pour les champs de type 10, 11, 12
            $champ_lignes = DB::table("eden_champslibres as C")
                ->join('information_schema.COLUMNS as IC', function ($join) {
                    $join->on("IC.TABLE_NAME", "=", DB::raw("C.table_pivot COLLATE utf8mb4_unicode_ci"))
                        ->whereIn("IC.COLUMN_NAME", ["cle_locale", "cle_etrangere"]);
                })
                ->join('information_schema.TABLES as IT', function ($join) {
                    $join->on("IT.TABLE_NAME", "=", "IC.TABLE_NAME")
                        ->on("IC.TABLE_SCHEMA", "=", "IT.TABLE_SCHEMA")
                        ->where("IT.TABLE_SCHEMA", "=", env("DB_DATABASE"))
                        ->where("IT.TABLE_TYPE", "=", "BASE TABLE");
                })
                ->whereIn("C.type", [10, 11, 12])
                ->where("IC.COLUMN_TYPE", "!=", 'int(11) unsigned')
                ->where("C.inactif", "=", 0)
                ->select("IC.TABLE_NAME", "IC.COLUMN_NAME")
                ->distinct()
                ->get();

            foreach ($champ_lignes as $champ) {
                $champ_list_change[$champ->TABLE_NAME][$champ->COLUMN_NAME]["nom_sql"] = $champ->COLUMN_NAME;
            }

            Champ_libre_management::maj_champs_libres($champ_list_change, "int(11) unsigned");
        } catch (\Exception $e) {
            DB::select("SET foreign_key_checks = 1;");

            Log::error("Erreur lors l'execution du script de migration des ID en int(11à unsigned : \n" . $e->getMessage() . "\n" . $e->getTraceAsString());
            return false;
        }

        DB::select("SET foreign_key_checks = 1;");
        return true;
    }
}
