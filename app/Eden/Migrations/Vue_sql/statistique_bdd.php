<?php 
    return [
        'nom' => 'statistique_bdd',
        'nom_sql' => 'statistique_bdd',
        'type_de_vue' => '1',
        'table_par_defaut' => '',
        'requete' => 'CREATE OR REPLACE VIEW statistique_bdd AS 
            SELECT ROW_NUMBER() OVER (ORDER BY (data_length + index_length) DESC) as id, 
            table_name as `Table`, table_rows as NbEnregistrements,
            ROUND(((data_length + index_length) / 1024 / 1024), 2) AS Taille_Mo,
            ROUND(((data_length ) / 1024 / 1024), 2) as Taille_Data_Mo,
            ROUND(((index_length ) / 1024 / 1024), 2) as Taille_Index_Mo,
            ROUND(((data_length + index_length) / 1024 / 1024) * 100.00 / (select sum((data_length + index_length) / 1024 / 1024) 
            FROM information_schema.TABLES
            WHERE table_schema = "' . env('DB_DATABASE') . '" ), 2) as Pourcentage
            FROM information_schema.TABLES
            WHERE table_schema = "' . env('DB_DATABASE') . '"
            ORDER BY (data_length + index_length) DESC',
    ];