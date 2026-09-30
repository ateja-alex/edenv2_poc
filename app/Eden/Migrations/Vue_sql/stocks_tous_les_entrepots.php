<?php
return [
    'nom' => 'stocks_tous_les_entrepots',
    'nom_sql' => 'stocks_tous_les_entrepots',
    'joins' => '',
    'tables' => '',
    'alias_tables' => '',
    'table_par_defaut' => '',
    'type_de_vue' => '1',
    'requete' => 'CREATE OR REPLACE VIEW stocks_tous_les_entrepots AS select spc.article_id as id, spc.chaine_tags_recherche, spc.famille_id,spc.article_id as article_id,spc.fournisseur_id,
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
                from stocks_tous_les_entrepots_par_conditionnement spc
                group by spc.article_id;',
    'autres_conditions' => '',
];