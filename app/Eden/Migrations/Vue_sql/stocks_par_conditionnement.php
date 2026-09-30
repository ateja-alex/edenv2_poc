<?php
    return [
        'nom' => 'stocks_par_conditionnement',
        'nom_sql' => 'stocks_par_conditionnement',
        'joins' => '',
        'tables' => '',
        'alias_tables' => '',
        'table_par_defaut' => '',
        'type_de_vue' => '1',
        'requete' => 'CREATE OR REPLACE VIEW stocks_par_conditionnement AS SELECT 
            CONCAT(a.id, e.id, c.id) AS id,
            a.chaine_tags_recherche, 
            a.famille_id, 
            e.id AS entrepot_id,
            c.id AS conditionnement_id,
            a.id AS article_id,
            a.fournisseur_id,
            si.adressage,
            si.quantite_conditionnement,
        
            -- Stock actuel
            COALESCE(si.stock_initial, 0) + SUM(CASE WHEN COALESCE(mds.reserve, 0) = 0 THEN COALESCE(mds.quantite, 0) ELSE 0 END) AS stock_actuel,
            (COALESCE(si.stock_initial, 0) + SUM(CASE WHEN COALESCE(mds.reserve, 0) = 0 THEN COALESCE(mds.quantite, 0) ELSE 0 END)) / COALESCE(c.quantite, 1) AS stock_actuel_conditionnement,
        
            -- Stock réservé
            SUM(CASE WHEN mds.reserve = 1 AND mds.quantite < 0 THEN COALESCE(mds.quantite, 0) ELSE 0 END) AS stock_reserve,
            SUM(CASE WHEN mds.reserve = 1 AND mds.quantite < 0 THEN COALESCE(mds.quantite, 0) ELSE 0 END) / COALESCE(c.quantite, 1) AS stock_reserve_conditionnement,
        
            -- Stock acheté
            SUM(CASE WHEN mds.reserve = 1 AND mds.quantite > 0 THEN COALESCE(mds.quantite, 0) ELSE 0 END) AS stock_achete,
            SUM(CASE WHEN mds.reserve = 1 AND mds.quantite > 0 THEN COALESCE(mds.quantite, 0) ELSE 0 END) / COALESCE(c.quantite, 1) AS stock_achete_conditionnement,
        
            -- Stock disponible et stock à terme
            (COALESCE(si.stock_initial, 0) + SUM(CASE WHEN COALESCE(mds.reserve, 0) = 0 THEN COALESCE(mds.quantite, 0) ELSE 0 END) + 
            SUM(CASE WHEN mds.reserve = 1 AND mds.quantite < 0 THEN COALESCE(mds.quantite, 0) ELSE 0 END)) AS stock_disponible,
            ((COALESCE(si.stock_initial, 0) + SUM(CASE WHEN COALESCE(mds.reserve, 0) = 0 THEN COALESCE(mds.quantite, 0) ELSE 0 END) + 
            SUM(CASE WHEN mds.reserve = 1 AND mds.quantite < 0 THEN COALESCE(mds.quantite, 0) ELSE 0 END)) / COALESCE(c.quantite, 1)) AS stock_disponible_conditionnement,
        
            (COALESCE(si.stock_initial, 0) + SUM(COALESCE(mds.quantite, 0))) AS stock_a_terme,
            (COALESCE(si.stock_initial, 0) + SUM(COALESCE(mds.quantite, 0))) / COALESCE(c.quantite, 1) AS stock_a_terme_conditionnement,
        
            -- Valorisation du stock actuel
            (COALESCE(si.stock_initial, 0) + SUM(CASE WHEN COALESCE(mds.reserve, 0) = 0 THEN COALESCE(mds.quantite, 0) ELSE 0 END)) * COALESCE(a.prix_d_achat, 0) AS valorisation_stock_actuel,
            ((COALESCE(si.stock_initial, 0) + SUM(CASE WHEN COALESCE(mds.reserve, 0) = 0 THEN COALESCE(mds.quantite, 0) ELSE 0 END)) * COALESCE(a.prix_d_achat, 0)) / COALESCE(c.quantite, 1) AS valorisation_stock_actuel_conditionnement,
        
            -- Rotation des stocks sur 90 jours et 365 jours
            SUM(CASE WHEN mds.type_de_mouvement = 0 AND mds.quantite <= 0 AND mds.date >= DATE(NOW() - INTERVAL 3 MONTH) THEN -COALESCE(mds.quantite, 0) / 3 ELSE 0 END) AS rotation_90j,
            SUM(CASE WHEN mds.type_de_mouvement = 0 AND mds.quantite <= 0 AND mds.date >= DATE(NOW() - INTERVAL 3 MONTH) THEN -COALESCE(mds.quantite, 0) / 3 ELSE 0 END) / COALESCE(c.quantite, 1) AS rotation_90j_conditionnement,
        
            SUM(CASE WHEN mds.type_de_mouvement = 0 AND mds.quantite <= 0 AND mds.date >= DATE(NOW() - INTERVAL 12 MONTH) THEN -COALESCE(mds.quantite, 0) / 12 ELSE 0 END) AS rotation_365j,
            SUM(CASE WHEN mds.type_de_mouvement = 0 AND mds.quantite <= 0 AND mds.date >= DATE(NOW() - INTERVAL 12 MONTH) THEN -COALESCE(mds.quantite, 0) / 12 ELSE 0 END) / COALESCE(c.quantite, 1) AS rotation_365j_conditionnement,
        
            -- Seuils
            COALESCE(sea.seuil_mini, 0) AS seuil_mini,
            COALESCE(sea.seuil_mini, 0) * COALESCE(c.quantite, 1) AS seuil_mini_unite,
            COALESCE(sea.seuil_alerte, 0) AS seuil_alerte,
            COALESCE(sea.seuil_alerte, 0) * COALESCE(c.quantite, 1) AS seuil_alerte_unite,
        
            -- Liens
            "stock_initial" AS lien_type_element_1,
            si.id AS lien_id_1,
            "seuil_article" AS lien_type_element_2,
            sea.id AS lien_id_2
        
        FROM 
            article a
        JOIN (
            SELECT id, article_id, quantite 
            FROM conditionnement 
            WHERE COALESCE(inactif, 0) = 0
            UNION 
            SELECT 0 AS id, 0 AS article_id, 1 AS quantite
        ) c ON (a.id = c.article_id OR c.id = 0)
        JOIN (SELECT entrepot_id, article_id FROM
                (SELECT entrepot_id, article_id,inactif
                FROM mouvement_de_stock
                UNION
                SELECT entrepot_id, article_id,inactif
                FROM stock_initial) tmp
                WHERE COALESCE(entrepot_id,0) != 0
                AND COALESCE(inactif,0) = 0
                GROUP BY entrepot_id, article_id
        ) entrepots_ids_par_article ON entrepots_ids_par_article.article_id = a.id
        join entrepot as e ON e.id = entrepots_ids_par_article.entrepot_id 
        LEFT JOIN mouvement_de_stock mds ON a.id = mds.article_id AND c.id = COALESCE(mds.conditionnement_id, 0) AND e.id = COALESCE(mds.entrepot_id) AND COALESCE(mds.inactif, 0) = 0
        LEFT JOIN stock_initial si ON COALESCE(si.inactif, 0) = 0 AND a.id = si.article_id AND c.id = COALESCE(si.conditionnement_id, 0) AND e.id = COALESCE(si.entrepot_id) AND COALESCE(si.inactif, 0) = 0
        LEFT JOIN seuil_article sea ON a.id = sea.article_id AND COALESCE(sea.inactif, 0) = 0 AND c.id = COALESCE(sea.conditionnement_id, 0) AND COALESCE(sea.inactif, 0) = 0
        
        WHERE COALESCE(a.inactif, 0) = 0 AND a.stockable = 1
        GROUP BY a.id, e.id, c.id, si.id, sea.id;',
        'autres_conditions' => '',
    ];
