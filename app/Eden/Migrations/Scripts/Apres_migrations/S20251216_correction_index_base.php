<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Maintenance_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Tables_libres;
use App\Eden\Models\Table_libre;
use DB;
use Throwable;

class S20251216_correction_index_base implements Script {

    public function execute(){

        $erreurs = [];

        $foreign_keys_existants = collect(DB::select('SELECT table_name,constraint_name FROM information_schema.table_constraints WHERE table_schema="'.env('DB_DATABASE').'" AND constraint_type="FOREIGN KEY"'))
            ->groupBy('table_name')->map(fn($champs) => $champs->pluck('constraint_name'))->toArray();

        foreach($foreign_keys_existants as $table_name => $indexes){
            foreach($indexes as $index_name){
                try{
                    DB::statement('ALTER TABLE `'.$table_name.'` DROP FOREIGN KEY `'.$index_name.'`');
                } catch(\Exception | Throwable $e){
                    $erreurs[] = 'Impossible de supprimer la FOREIGN KEY '.$index_name.' sur la table '.$table_name.' : '.$e->getMessage();
                }
            }
        }

        $index_existants_par_type_element = collect(DB::select('SELECT table_name,index_name FROM information_schema.statistics WHERE table_schema="'.env('DB_DATABASE').'"'))
            ->groupBy('table_name')->map(fn($champs) => $champs->pluck('index_name'))->toArray();

        foreach($index_existants_par_type_element as $table_name => $indexes){
            foreach($indexes as $index_name){
                if($index_name != 'PRIMARY'){
                    try{
                        DB::statement('ALTER TABLE `'.$table_name.'` DROP INDEX `'.$index_name.'`');
                    } catch(\Exception | Throwable $e){
                        $erreurs[] = 'Impossible de supprimer l\'index '.$index_name.' sur la table '.$table_name.' : '.$e->getMessage();
                    }
                }
            }
        }

        if(!empty($erreurs))
            throw new \Exception('Erreurs rencontrées lors de la suppression des index : '.print_r($erreurs, true));

        Maintenance_management::maj_tables_libres();
        Maintenance_management::maj_champs_libres();

        $tables_libres = Table_libre::whereNotIn('type_element', array_keys(Tables_libres::tables_libres()))->get();

        foreach($tables_libres as $table_libre){
            try{
                DB::select('ALTER TABLE`' . $table_libre->type_element . '` ADD FULLTEXT INDEX `chaine_tags_recherche` (`chaine_tags_recherche`)');
            } catch(\Exception | Throwable $e){
            }
        }

        return true;
    }
}