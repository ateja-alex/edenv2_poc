<?php 
    return [
        'nom' => 'Conditions commerciales des articles',
        'nom_sql' => 'article_conditions_commerciales',
        'type_de_vue' => '1',
        'table_par_defaut' => 'condition_commerciale',
        'requete' => 'CREATE OR REPLACE VIEW article_conditions_commerciales AS SELECT cc.id,article.id as article_id,cc.famille_id famille_id,client_id,catalogue_tarif_id,conditionnement,palier_quantite,cc.tarif,remise,prix_achat,cc.inactif,
            IF(cc.article_id = article.id,1,0) as modification_possible
            FROM article 
            LEFT JOIN (
                WITH RECURSIVE cte AS (
                    SELECT id as article_parent,\'article\' as type,id as id_type,famille_id
                    FROM article
                    UNION ALL
                    SELECT article_parent,\'famille\' as type,famille.id as id_type,famille.parent_id as famille_id
                    FROM cte
                    JOIN famille ON famille.id = cte.famille_id
                )
                SELECT * FROM cte
            ) familles ON CONVERT(familles.article_parent,INT) = article.id
            JOIN condition_commerciale cc ON familles.id_type = IF(familles.type = \'article\',cc.article_id,cc.famille_id) AND COALESCE(cc.inactif,0) = 0
            LEFT JOIN catalogue_tarif ct ON ct.id = cc.catalogue_tarif_id
            WHERE ct.id IS NULL OR (ct.date_debut < now() AND COALESCE(ct.date_fin,now()) >= now())
            GROUP BY article.id,client_id,catalogue_tarif_id,conditionnement,palier_quantite;',
    ];