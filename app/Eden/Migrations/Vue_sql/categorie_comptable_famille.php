<?php
return [
    'nom' => 'categorie_comptable_famille',
    'nom_sql' => 'categorie_comptable_famille',
    'joins' => '',
    'tables' => '',
    'alias_tables' => '',
    'creation_liste' => '1',
    'table_par_defaut' => 'article_categorie_comptable',
    'type_de_vue' => '1',
    'requete' => 'CREATE OR REPLACE VIEW categorie_comptable_famille AS SELECT
    a2.id,
    a2.code_tva_id,
    a2.code_tva_achat_id,
    a2.compte_produit,
    a2.compte_charge,
    a2.categorie_comptable_id,
    a2.id as article_categorie_comptable_id,
    valeurs.id as famille_id,
    a2.famille_id as famille_origine_id,
    IF(valeurs.id = a2.famille_id,1,0) as modification_possible
    FROM (
 		SELECT famille.id, SUBSTRING_INDEX(GROUP_CONCAT(
                            a2.id ORDER BY familles.ordre
                        ), "," COLLATE utf8mb4_general_ci, 1) as id_article_categorie_comptable
                        FROM famille
                        JOIN (
                            WITH RECURSIVE cte AS (
                                SELECT id,id AS famille_parent,1 as ordre
                                FROM famille
                                UNION ALL
                                SELECT famille.parent_id as id,cte.famille_parent,cte.ordre+1 as ordre
                                 FROM cte
                                JOIN famille ON famille.id = cte.id AND parent_id  > 0
                            )
                            SELECT * FROM cte
                        ) familles ON famille.id = familles.famille_parent
                        JOIN article_categorie_comptable a2 ON a2.famille_id = familles.id AND COALESCE(a2.inactif,0) = 0
                        GROUP BY famille.id,a2.categorie_comptable_id
    ) as valeurs
    JOIN article_categorie_comptable a2 ON a2.id = id_article_categorie_comptable;',
    'autres_conditions' => '',
];