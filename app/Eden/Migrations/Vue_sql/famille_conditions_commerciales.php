<?php 
    return [
        'nom' => 'famille_conditions_commerciales',
        'nom_sql' => 'famille_conditions_commerciales',
        'type_de_vue' => '1',
        'table_par_defaut' => 'condition_commerciale',
        'requete' => 'CREATE OR REPLACE VIEW famille_conditions_commerciales AS SELECT cc.id,familles.famille_parent as famille_id,cc.famille_id as famille_origine_id, client_id,catalogue_tarif_id,conditionnement,palier_quantite,cc.tarif,remise,prix_achat,
                IF(cc.famille_id = familles.famille_parent,1,0) as modification_possible
                FROM famille
                LEFT JOIN (
                    WITH RECURSIVE cte AS (
                        SELECT id,id AS famille_parent
                        FROM famille
                        UNION ALL
                        SELECT famille.parent_id as id,cte.famille_parent
                            FROM cte
                        JOIN famille ON famille.id = cte.id AND parent_id  > 0
                    )
                    SELECT * FROM cte
                ) familles ON CONVERT(familles.famille_parent ,INT) = famille.id
                JOIN condition_commerciale cc ON familles.id= cc.famille_id AND COALESCE(cc.inactif,0) = 0
                LEFT JOIN catalogue_tarif ct ON ct.id = cc.catalogue_tarif_id
                WHERE ct.id IS NULL OR (ct.date_debut < now() AND COALESCE(ct.date_fin,now()) >= now())
                GROUP BY familles.famille_parent,client_id,catalogue_tarif_id,palier_quantite,conditionnement;',
    ];