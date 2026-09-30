<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use Illuminate\Support\Facades\Log;
use DB;

class S20251020_changer_type_chaine_tags_recherche implements Script {

    public function execute(){

        $tables = DB::table('information_schema.COLUMNS as c')
            ->join('information_schema.TABLES as t', function($join) {
                $join->on('c.TABLE_NAME', '=', 't.TABLE_NAME')
                    ->on('c.TABLE_SCHEMA', '=', 't.TABLE_SCHEMA');
            })
            ->where('c.table_schema', env('DB_DATABASE'))
            ->where('c.column_name', 'chaine_tags_recherche')
            ->where('t.TABLE_TYPE', 'BASE TABLE')
            ->get();


        foreach($tables as $table){
            if($table->DATA_TYPE != 'longtext'){
                $collation = $table->COLLATION_NAME ? ' COLLATE '.$table->COLLATION_NAME : '';
                try {
                    DB::statement('ALTER TABLE `'.$table->TABLE_NAME.'` MODIFY COLUMN `chaine_tags_recherche` longtext NULL'.$collation.';');
                } catch (\Exception $e) {
                    Log::warning("Erreur de l'execution du script S20251020_changer_type_chaine_tags_recherche : Erreur de changement de type de la colonne chaine_tags_recherche de la table ".$table->TABLE_NAME. " : ".$e->getMessage());
                    return false;
                }
                
                management($table->TABLE_NAME)->maj_index_recherche();
            }
        }   
        return true;
    }    
}