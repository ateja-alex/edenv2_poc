<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use DB;

class S20250728_suppression_mvts_stock_inutiles implements Script{

    public function execute(){

        if(fonctionnalite('activer_gestion_stock') == false){

            DB::delete('DELETE element_log_detail FROM element_log_detail
                JOIN element_log as el ON element_log_detail.id_element_log = el.id_element_log 
                WHERE el.type_element = "mouvement_de_stock"');

            DB::delete('DELETE FROM element_log WHERE type_element = "mouvement_de_stock"');

            DB::delete('DELETE FROM mouvement_de_stock');

            return true;
        }

        $ids = modele('mouvement_de_stock')
            ->join('article', 'mouvement_de_stock.article_id', '=', 'article.id')
            ->zero_ou_null('article.stockable')
            ->pluck('mouvement_de_stock.id')
            ->toArray();

        if(!empty($ids)){

            DB::delete('DELETE element_log_detail FROM element_log_detail  
                JOIN element_log as el ON element_log_detail.id_element_log = el.id_element_log 
                WHERE el.type_element = "mouvement_de_stock" 
                AND el.id_element IN (' . implode(', ', $ids) . ')');

            DB::delete('DELETE FROM element_log
                WHERE type_element = "mouvement_de_stock" 
                AND id_element IN (' . implode(', ', $ids) . ')');

            DB::delete('DELETE FROM mouvement_de_stock WHERE id IN (' . implode(', ', $ids) . ')');
        }
        
        return true;
    }
}