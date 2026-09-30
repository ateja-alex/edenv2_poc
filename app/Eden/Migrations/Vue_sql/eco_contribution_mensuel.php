<?php
    return [
        'nom' => 'eco_contribution_mensuel',
        'nom_sql' => 'eco_contribution_mensuel',
        'joins' => '',
        'tables' => '',
        'alias_tables' => '',
        'table_par_defaut' => '',
        'type_de_vue' => '1',
        'requete' => 'CREATE OR REPLACE VIEW eco_contribution_mensuel AS
            SELECT
            lec.id,
            DATE_ADD(lec.date_document, INTERVAL -DAY(lec.date_document)+1 DAY) as date,
            feo.eco_organisme_id as eco_organisme_id,
            lec.categorie_eco_contribution_id,
            SUM(lec.quantite * COALESCE(lec.quantite_unite_eco_contribution,1)) as quantite_unite,
            ceo.unite as unite,
            SUM(ROUND(IF(lec.tarif_eco_contribution * COALESCE(lec.quantite_unite_eco_contribution,1) < 0.01, 0.01, lec.tarif_eco_contribution * COALESCE(lec.quantite_unite_eco_contribution,1)) * lec.quantite,2)) as eco_contribution,
            SUM(ROUND(IF(lec.tarif_eco_contribution * COALESCE(lec.quantite_unite_eco_contribution,1) < 0.01, 0.01, lec.tarif_eco_contribution * COALESCE(lec.quantite_unite_eco_contribution,1)) * lec.quantite * lec.tva /100,2)) as tva
            FROM (
                    (SELECT
            fvl.id,
            fvl.document_id,
            fv.date as date_document,
            fvl.article_id,
            fvl.quantite,
            fvl.tarif_eco_contribution,
            fvl.quantite_unite_eco_contribution,
            fvl.remise,
            fvl.categorie_eco_contribution_id,
            IF(fvl.nomenclature_ligne_parent > 0,tva_lignes.tva,fvl.tva) as tva
            FROM facture_vente_lignes fvl
            JOIN facture_vente fv ON fv.id = fvl.document_id
            LEFT JOIN (
                WITH RECURSIVE cte AS (
                    SELECT *, facture_vente_lignes.tva as tva_parent
                    FROM facture_vente_lignes
                    WHERE nomenclature_ligne_parent IS NULL
                    UNION ALL
                    SELECT t.*,cte.tva_parent as tva_parent
                    FROM facture_vente_lignes t
                    JOIN cte ON t.nomenclature_ligne_parent = cte.id
                )
                SELECT cte.id, tva_parent as tva
                FROM cte
                WHERE cte.tarif_eco_contribution > 0
                AND cte.nomenclature_ligne_parent > 0
            ) AS tva_lignes ON tva_lignes.id = fvl.id
            WHERE fvl.tarif_eco_contribution> 0 AND fv.valide = 1)
            UNION
            ( SELECT avl.id,
            avl.document_id,
            av.date as date_document,
            avl.article_id,
            -1 * avl.quantite,
            avl.tarif_eco_contribution,
            avl.quantite_unite_eco_contribution,
            avl.remise,
            avl.categorie_eco_contribution_id,
            IF(avl.nomenclature_ligne_parent > 0,tva_lignes.tva,avl.tva) as tva
            FROM avoir_vente_lignes avl
            JOIN avoir_vente av ON av.id = avl.document_id
            LEFT JOIN (
                WITH RECURSIVE cte AS (
                    SELECT *, avoir_vente_lignes.tva as tva_parent
                    FROM avoir_vente_lignes
                    WHERE nomenclature_ligne_parent IS NULL
                    UNION ALL
                    SELECT t.*,cte.tva_parent as tva_parent
                    FROM avoir_vente_lignes t
                    JOIN cte ON t.nomenclature_ligne_parent = cte.id
                )
                SELECT cte.id, tva_parent as tva
                FROM cte
                WHERE cte.tarif_eco_contribution > 0
                AND cte.nomenclature_ligne_parent > 0
            ) AS tva_lignes ON tva_lignes.id = avl.id
            WHERE avl.tarif_eco_contribution> 0 AND av.valide = 1)
            ) as lec
            JOIN categorie_eco_contribution ceo ON ceo.id = lec.categorie_eco_contribution_id
            JOIN famille_eco_contribution feo ON feo.id = ceo.famille_id
            GROUP BY year(lec.date_document), month(lec.date_document),lec.categorie_eco_contribution_id
            HAVING SUM(ROUND(IF(lec.tarif_eco_contribution * COALESCE(lec.quantite_unite_eco_contribution,1) < 0.01, 0.01, lec.tarif_eco_contribution * COALESCE(lec.quantite_unite_eco_contribution,1)) * lec.quantite,2)) != 0;',
        'autres_conditions' => '',
    ];
