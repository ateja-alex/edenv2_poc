<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Table_libre;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use mysql_xdevapi\Exception;

class S20230901_01_correction_data implements Script
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


            // Pour chaque champ, si l'une des valeurs est null, on delete le lien, il ne peux pas fonctionner
            foreach ($list_champs as $champ) {
                try {
                    $table_pivot = $champ["table_pivot"];

                    Db::select("DELETE FROM `{$table_pivot}` WHERE cle_locale = 0 OR cle_etrangere = 0 OR cle_locale = NULL OR cle_etrangere = NULL");
			} catch (\Exception $e) {
                    Log::warning("Erreur de l'execution du script S20230901_01_correction_data: supression des valeurs inutile dans les tables pivots : {$e}");
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

            // Pour chaque on update le 0 en NULL
            foreach ($list_champs as $champ) {
                try {
                    Db::select("UPDATE `{$champ["type_element"]}` SET `{$champ["nom_sql"]}` = NULL WHERE `{$champ["nom_sql"]}` = 0");
                } catch (\Exception $e) {
                    Log::warning("Erreur de l'execution du script S20230901_01_correction_data: mise a NULL des valeurs à 0 : {$e}");
                }
            }

            return true;
        }
        catch (\Exception $e){
            Log::error("Erreur lors de l'execution du script S20230901_01_correction_data : {$e}");
            return false;
        }
	}
}