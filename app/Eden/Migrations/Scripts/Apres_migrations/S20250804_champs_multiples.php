<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Models\Champ_libre;
use App\Eden\Migrations\Scripts\Script;
use Illuminate\Support\Facades\DB;
use App\Eden\Managements\Parametrage\Table_libre_management;

class S20250804_champs_multiples implements Script {

    public function execute(){

        $champs_libres = Champ_libre::whereIn('type', [11, 12])->get();

        $types_elements = $champs_libres->pluck('type_element')->unique()->toArray();

        foreach($champs_libres as $champ_libre){

            $champ_libre->type_reference = $champ_libre->type == 11 ? 20 : 1;
            $champ_libre->type = 10;
            $champ_libre->save();
        }

        $champs_libres = Champ_libre::where('type', 10)->whereNull('type_reference')->get();

        $types_elements = array_merge($types_elements, $champs_libres->pluck('type_element')->unique()->toArray());

        foreach($champs_libres as $champ_libre){
            $champ_libre->type_reference = 42;
            $champ_libre->save();
        }

        $champs_libres = Champ_libre::where('nom_sql', 'cle_etrangere')->whereIn('type_element', 
            Champ_libre::select('table_pivot')->whereNotNull('table_pivot')->groupBy('table_pivot')
        )->get();

        $types_elements = array_merge($types_elements, $champs_libres->pluck('type_element')->unique()->toArray());

        foreach($champs_libres as $champ_libre){
            $champ_libre->nom_sql = 'valeur';
            $champ_libre->save();
        }

        if(is_dir(app_path('Migrations'))) {

            $repertoire_champs_libres = scandir(app_path('Migrations'));

            foreach ($repertoire_champs_libres as $fichier) {

                if (in_array($fichier, array('.', '..', 'Rapports', 'Listes_libres', 'Listes_libres_fiches', 'Listes_libres_export', 'Tables', 'Formulaires_libres', 'Sous_formulaire', 'Champs_libres_listes', 'Champs_libres_listes_formatees', 'Listes_libres_couleurs', 'Listes_libres_autresvues', 'Scripts', 'Vue_sql')))
                    continue;

                $type_element = str_replace('.php','',$fichier);

                if(!in_array($type_element, array_unique($types_elements)))
                    continue;

                Table_libre_management::generer_fichier_migration($type_element,true);

            }

        }

        $champs_multiples = Champ_libre::where('type',10)
            ->join('eden_tableslibres','eden_champslibres.type_element','=','eden_tableslibres.type_element')
            ->whereRaw('COALESCE(eden_tableslibres.vue_sql,0) = 0')
            ->get();
        $database = DB::getDatabaseName();

        foreach($champs_multiples as $champ_multiple){
            $table_pivot = $champ_multiple->table_pivot;
            $colonnes = DB::getSchemaBuilder()->getColumnListing($table_pivot);
            if(in_array('valeur', $colonnes)){
                continue;
            } else {
                DB::select('ALTER TABLE `'.$table_pivot.'` ADD `valeur` INT(11) UNSIGNED');
                DB::update('UPDATE `'.$table_pivot.'` SET `valeur` = `cle_etrangere`');

                $contrainte_ce = DB::selectOne("
                    SELECT CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
                    FROM information_schema.KEY_COLUMN_USAGE
                    WHERE TABLE_NAME = ?
                    AND COLUMN_NAME = 'cle_etrangere'
                    AND CONSTRAINT_SCHEMA = ?
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ", [$table_pivot, $database]);

                if ($contrainte_ce) {

                    DB::statement("
                        ALTER TABLE `$table_pivot`
                        DROP FOREIGN KEY `{$contrainte_ce->CONSTRAINT_NAME}`
                    ");

                    DB::statement("
                        ALTER TABLE `$table_pivot`
                        ADD CONSTRAINT `{$contrainte_ce->CONSTRAINT_NAME}`
                        FOREIGN KEY (`valeur`) REFERENCES `{$contrainte_ce->REFERENCED_TABLE_NAME}`(`{$contrainte_ce->REFERENCED_COLUMN_NAME}`)
                        ON DELETE CASCADE ON UPDATE CASCADE
                    ");
                    
                }

                DB::select('ALTER TABLE `'.$table_pivot.'` DROP COLUMN `cle_etrangere`');
            }   
        }
        return true;
    }
}

?>