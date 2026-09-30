<?php
return [
    'nom' => 'categorie_comptable_article',
    'nom_sql' => 'categorie_comptable_article',
    'joins' => '',
    'tables' => '',
    'alias_tables' => '',
    'creation_liste' => '1',
    'table_par_defaut' => 'article_categorie_comptable',
    'type_de_vue' => '1',
    'requete' => 'CREATE OR REPLACE VIEW categorie_comptable_article AS SELECT 
                                acc.id as id,
                                acc.chaine_tags_recherche,
                                article.id as article_id,
                                acc.code_tva_id,
                                acc.code_tva_achat_id,
                                acc.compte_produit,
                                acc.compte_charge,
                                acc.categorie_comptable_id,
                                IF(COALESCE(code_tva.sens,0) != 2,COALESCE(code_tva.taux,0),0) as taux_tva,
                                IF(COALESCE(code_tva_achat.sens,0) != 2,COALESCE(code_tva_achat.taux,0),0) as taux_tva_achat,
                                acc.id as article_categorie_comptable_id,
                                acc.famille_id as famille_id,
                                IF(acc.article_id =  article.id,1,0) as modification_possible
                     FROM (
 		SELECT article.id, SUBSTRING_INDEX(GROUP_CONCAT(
                            acc.id ORDER BY familles.ordre
                        ), "," COLLATE utf8mb4_general_ci, 1) as id_article_categorie_comptable
                        FROM article
                        JOIN (
                        WITH RECURSIVE cte AS (
                            SELECT id as article_parent,"article" as type,id as id_type,famille_id,1 as ordre
                            FROM article
                            UNION ALL
                            SELECT article_parent,"famille" as type,famille.id as id_type,famille.parent_id as famille_id,cte.ordre+1 as ordre
                            FROM cte
                            JOIN famille ON famille.id = cte.famille_id
                        )
                        SELECT * FROM cte
                    ) familles ON familles.article_parent = article.id
                    JOIN article_categorie_comptable acc ON ((familles.type = "article" AND familles.id_type = acc.article_id) OR (familles.type = "famille" AND familles.id_type = acc.famille_id)) AND COALESCE(acc.inactif,0) = 0 AND COALESCE(acc.eco_contribution, 0) = 0
                    GROUP BY article.id, categorie_comptable_id
    ) as valeurs
JOIN article ON valeurs.id = article.id
JOIN article_categorie_comptable acc ON acc.id = id_article_categorie_comptable
LEFT JOIN code_tva ON code_tva.id = acc.code_tva_id
LEFT JOIN code_tva code_tva_achat ON code_tva_achat.id = acc.code_tva_achat_id;',
    'autres_conditions' => '',
];
