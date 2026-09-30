<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Table_libre;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use mysql_xdevapi\Exception;

class S20230901_02_migration_id_unsigned implements Script
{
	/**
	 * @return true
	 *
	 * Script de migration des champs 42 & 10 en int si != int
	 *
	 */
	public function execute()
	{
        try {
            //Suppression de tt les FK :
            $fks = DB::select("SELECT table_name, constraint_name FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = '" . env('DB_DATABASE') . "'");

            foreach ($fks as $fk) {
                try {
                    DB::statement("ALTER TABLE " . $fk->table_name . " DROP FOREIGN KEY " . $fk->constraint_name);
                } catch (\Exception $e) {
                    Log::warning("Erreur de l'execution du script S20230901_02_migration_id_unsigned: supression des valeurs inutile dans les tables pivots : {$e}");
                    return false;
                }
            }

            //changement des PK :
            $table_libres = Table_libre::where("vue_sql", 0)->orWhere("vue_sql", null)->get();

            foreach ($table_libres as $table_libre) {
                try {
                    Script_management::change_type_de_colonne_sur_table($table_libre["type_element"], "id", 'INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;');
                } catch (\Exception $e) {
                    Log::warning("Erreur de l'execution du script S20230901_02_migration_id_unsigned : Erreur de changement de type de la PK de la table {$table_libre["type_element"]}  : {$e}");
                    return false;
                }
            }

            //Type 10 : N <-> N
            //On récupère tous les champs libres de tables uniquement en type 10
            $list_champs = Champ_libre::leftjoin('eden_tableslibres', 'eden_champslibres.type_element', '=', 'eden_tableslibres.type_element')
                ->where(function($where){
                    $where->where("eden_tableslibres.vue_sql", 0)->orWhereNull("eden_tableslibres.vue_sql");
                })
                ->where(function($where){
                    $where->where("eden_champslibres.type", 10)->orWhere("eden_champslibres.type", 11)->orWhere("eden_champslibres.type", 12);
                })
                ->get();


            // Pour chaque champ, on change les types en INT(11) unsigned
            foreach ($list_champs as $champ) {
                try {
                    $table_pivot = $champ["table_pivot"];

                    Script_management::change_type_de_colonne_sur_table($table_pivot, "cle_locale", 'INT(11) unsigned NOT NULL');
                    Script_management::change_type_de_colonne_sur_table($table_pivot, "cle_etrangere", 'INT(11) unsigned NOT NULL');
                } catch (\Exception $e) {
                    Log::warning("Erreur de l'execution du script S20230901_02_migration_id_unsigned : Erreur lors du changement de type dans la table {$table_pivot} : {$e}");
                    return false;
                }
            }

            //Type 42 : 1 <-> N
            //On récupère tous les champs libres de tables uniquement en 42
            $list_champs = Champ_libre::leftjoin('eden_tableslibres', 'eden_champslibres.type_element', '=', 'eden_tableslibres.type_element')
                ->where(function($condition){
                    $condition->where("eden_tableslibres.vue_sql", 0)->orWhere("eden_tableslibres.vue_sql", null);
                })
                ->where("eden_champslibres.type", 42)
                ->get();

            // Pour chaque champ, on change les types en INT(11) unsigned
            foreach ($list_champs as $champ) {
                try {
                    Script_management::change_type_de_colonne_sur_table($champ["type_element"], $champ["nom_sql"], 'INT(11) unsigned');
                } catch (\Exception $e) {
                    Log::warning("Erreur de l'execution du script S20230901_02_migration_id_unsigned : Erreur de changement de type dans la table {$champ["type_element"]}, colonne {$champ["nom_sql"]} : {$e}");
                    return false;
                }
            }

            return true;
        }
        catch (\Exception $e){
            Log::error("Erreur de l'execution du script S20230901_02_migration_id_unsigned : {$e}");
            return false;
        }
	}
}