<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Eden\Variables;

class S20260828_index_documents_lies implements Script {

    protected $colonnes = ['type_element_source', 'id_element_source', 'id_ligne_source'];

    public function execute(){

        foreach(Variables::$documents_gescom as $type_element){

            $table = $type_element.'_lignes';

            if(!Schema::hasTable($table) || !Schema::hasColumns($table, $this->colonnes))
                continue;

            // le script doit pouvoir etre rejoue : sur certains projets l'index a deja ete cree a la main
            if($this->index_existe($table, 'idx_src') || $this->index_existe($table, 'idx_src_ids'))
                continue;

            try {

                DB::statement('ALTER TABLE `'.$table.'` ADD INDEX `idx_src` (`type_element_source`, `id_element_source`, `id_ligne_source`)');
            }
            catch(\Throwable $e){

                // type_element_source est un champ libre declare en varchar(300) : en utf8mb4 la cle
                // depasse la longueur maximale sur les tables en row_format COMPACT. On se rabat alors
                // sur les deux colonnes numeriques, qui suffisent a rendre la recherche des descendants
                // selective (id_element_source en tete, le type etant verifie ensuite)
                DB::statement('ALTER TABLE `'.$table.'` ADD INDEX `idx_src_ids` (`id_element_source`, `id_ligne_source`)');
            }
        }

        return true;
    }

    /**
     *
     * Indique si un index de ce nom existe deja sur la table
     *
     */
    protected function index_existe($table, $nom_index){

        $index = DB::select('SELECT 1 FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1', [$table, $nom_index]);

        return !empty($index);
    }
}