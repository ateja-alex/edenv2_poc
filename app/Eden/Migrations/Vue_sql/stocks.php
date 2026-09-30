<?php 
    return [
        'nom' => 'Stocks',
        'nom_sql' => 'stocks',
        'joins' => '',
        'tables' => '',
        'alias_tables' => '',
        'table_par_defaut' => '',
        'type_de_vue' => '1',
        'requete' => 'CREATE OR REPLACE VIEW stocks AS select CONCAT(spc.article_id,spc.entrepot_id) as id, spc.chaine_tags_recherche, spc.famille_id, spc.entrepot_id,spc.article_id as article_id,spc.fournisseur_id,spc.adressage,
            SUM(spc.stock_actuel) as stock_actuel,
            SUM(spc.stock_reserve) as stock_reserve,
            SUM(spc.stock_disponible) as stock_disponible,
            SUM(spc.stock_achete) as stock_achete,
            SUM(spc.stock_a_terme) as stock_a_terme,
            SUM(spc.valorisation_stock_actuel) as valorisation_stock_actuel,
            SUM(spc.rotation_90j) as rotation_90j,
            SUM(spc.rotation_365j) as rotation_365j,
            SUM(spc.seuil_mini_unite) as seuil_mini,
            SUM(spc.seuil_alerte_unite) as seuil_alerte
        from stocks_par_conditionnement spc
        group by spc.article_id,spc.entrepot_id;',
        'autres_conditions' => '',
    ];