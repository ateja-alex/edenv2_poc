<?php
return [
    'nom' => 'stocks_tous_les_entrepots_par_conditionnement',
    'nom_sql' => 'stocks_tous_les_entrepots_par_conditionnement',
    'joins' => '',
    'tables' => '',
    'alias_tables' => '',
    'table_par_defaut' => '',
    'type_de_vue' => '1',
    'requete' => 'CREATE OR REPLACE VIEW stocks_tous_les_entrepots_par_conditionnement AS select CONCAT(a.id,c.id) as id, a.chaine_tags_recherche, famille_id, c.id as conditionnement_id,a.id as article_id,a.fournisseur_id,
                    coalesce(si.stock_initial,0) + coalesce(mnr.quantite, 0) as stock_actuel,
                    (coalesce(si.stock_initial,0) + coalesce(mnr.quantite, 0)) / coalesce(c.quantite,1) as stock_actuel_conditionnement,
                    coalesce(sr.quantite, 0) as stock_reserve,
                    coalesce(sr.quantite, 0) / coalesce(c.quantite,1) as stock_reserve_conditionnement,
                    coalesce(si.stock_initial,0) + coalesce(mnr.quantite, 0) + coalesce(sr.quantite,0) as stock_disponible,
                    (coalesce(si.stock_initial,0) + coalesce(mnr.quantite, 0) + coalesce(sr.quantite,0)) / coalesce(c.quantite,1) as stock_disponible_conditionnement,
                    coalesce(sa.quantite,0) as stock_achete,
                    coalesce(sa.quantite,0) / coalesce(c.quantite,1) as stock_achete_conditionnement,
                    coalesce(si.stock_initial,0) + coalesce(mnr.quantite, 0) + coalesce(sr.quantite,0) + coalesce(sa.quantite,0) as stock_a_terme,
                    (coalesce(si.stock_initial,0) + coalesce(mnr.quantite, 0) + coalesce(sr.quantite,0) + coalesce(sa.quantite,0)) / coalesce(c.quantite,1) as stock_a_terme_conditionnement,
                    (coalesce(si.stock_initial,0) +coalesce(mnr.quantite,0)) * coalesce(prix_d_achat,0) as valorisation_stock_actuel,
                    ((coalesce(si.stock_initial,0) +coalesce(mnr.quantite,0)) * coalesce(prix_d_achat,0)) / coalesce(c.quantite,1) as valorisation_stock_actuel_conditionnement,
                    coalesce(r90j.quantite,0) as rotation_90j,
                    coalesce(r90j.quantite,0) / coalesce(c.quantite,1) as rotation_90j_conditionnement,
                    coalesce(r365j.quantite,0) as rotation_365j,
                    coalesce(r365j.quantite,0) / coalesce(c.quantite,1) as rotation_365j_conditionnement,
                    coalesce(sea.seuil_mini,0) as seuil_mini,
                    coalesce(sea.seuil_mini,0) * coalesce(c.quantite,1)  as seuil_mini_unite,
                    coalesce(sea.seuil_alerte,0) as seuil_alerte,
                    coalesce(sea.seuil_alerte,0) * coalesce(c.quantite,1) as seuil_alerte_unite,
                    \'seuil_article\' as lien_type_element_2,
                    sea.id as lien_id_2
                from article a
                JOIN (
                    SELECT id,article_id,quantite
                    FROM conditionnement
                    WHERE coalesce(conditionnement.inactif, 0) = 0
                    UNION SELECT 0 as id,0,1 as article_id
                ) as c ON (a.id = c.article_id OR c.id = 0)

                 left join (
                    SELECT SUM(coalesce(stock_initial.stock_initial,0)) as stock_initial,article_id,  coalesce(conditionnement_id,0) as conditionnement_id
                    FROM stock_initial
                    WHERE coalesce(stock_initial.inactif, 0) = 0
                    GROUP BY article_id,coalesce(conditionnement_id,0)
                ) si ON
                    a.id = si.article_id
                    AND c.id = si.conditionnement_id
                left join (
                    select SUM(coalesce(mds.quantite,0)) as quantite,mds.article_id,coalesce(conditionnement_id,0) as conditionnement_id
                    from mouvement_de_stock mds
                    WHERE coalesce(mds.inactif, 0) = 0
                    AND coalesce(mds.reserve,0) = 0
                    group by article_id,coalesce(conditionnement_id,0)
                ) mnr ON
                    a.id = mnr.article_id
                    AND c.id = mnr.conditionnement_id
                left join (
                    select SUM(coalesce(mds.quantite,0)) as quantite,mds.article_id,coalesce(conditionnement_id,0) as conditionnement_id
                    from mouvement_de_stock mds
                    WHERE coalesce(mds.inactif, 0) = 0
                    AND mds.reserve = 1
                    AND mds.quantite < 0
                    group by article_id,coalesce(conditionnement_id,0)
                ) sr ON
                    a.id = sr.article_id
                    AND c.id = sr.conditionnement_id
                left join (
                    select SUM(coalesce(mds.quantite,0)) as quantite,mds.article_id,coalesce(conditionnement_id,0) as conditionnement_id
                    from mouvement_de_stock mds
                    WHERE coalesce(mds.inactif, 0) = 0
                    AND mds.reserve = 1
                    AND mds.quantite > 0
                    group by article_id,coalesce(conditionnement_id,0)
                ) sa ON
                    a.id = sa.article_id
                    AND c.id = sa.conditionnement_id
                left join (
                    select -SUM(coalesce(mds.quantite,0)) / 3 as quantite,mds.article_id,coalesce(conditionnement_id,0) as conditionnement_id
                    from mouvement_de_stock mds
                    WHERE coalesce(mds.inactif, 0) = 0
                    AND coalesce(mds.reserve,0) = 0
                    AND mds.quantite <= 0
                    AND mds.type_de_mouvement in (0,6)
                    AND date >= DATE(NOW() - INTERVAL 3 MONTH)
                    group by article_id,coalesce(conditionnement_id,0)
                ) r90j ON
                    a.id = r90j.article_id
                    AND c.id = r90j.conditionnement_id
                left join (
                    select -SUM(coalesce(mds.quantite,0)) / 12 as quantite,mds.article_id,coalesce(conditionnement_id,0) as conditionnement_id
                    from mouvement_de_stock mds
                    WHERE coalesce(mds.inactif, 0) = 0
                    AND coalesce(mds.reserve,0) = 0
                    AND mds.quantite <= 0
                    AND mds.type_de_mouvement in (0,6)
                    AND date >= DATE(NOW() - INTERVAL 12 MONTH)
                    group by article_id,coalesce(conditionnement_id,0)
                ) r365j ON
                    a.id = r365j.article_id
                    AND c.id = r365j.conditionnement_id
                left join seuil_article sea ON
                    a.id = sea.article_id
                    AND coalesce(sea.inactif,0) = 0
                    AND c.id = coalesce(sea.conditionnement_id,0)
                where coalesce(a.inactif, 0) = 0
                and a.stockable = 1
                group by a.id,c.id;',
    'autres_conditions' => '',
];
