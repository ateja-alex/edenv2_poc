<?php 
    return [
        'nom' => 'facture_vente_lignes_eco_contribution',
        'nom_sql' => 'facture_vente_lignes_eco_contribution',
        'joins' => '',
        'tables' => '',
        'alias_tables' => '',
        'table_par_defaut' => '',
        'type_de_vue' => '1',
        'requete' => 'CREATE OR REPLACE VIEW facture_vente_lignes_eco_contribution AS 
            (SELECT 
            fvl.id as id,
            fvl.document_id as document_id,
            fv.reference_document as reference_document,
            fv.date as date_document,
            fvl.article_id as article_id,
            fvl.quantite as quantite,
                IF(fvl.tarif_eco_contribution * COALESCE(fvl.quantite_unite_eco_contribution,1) < 0.01, 0.01, fvl.tarif_eco_contribution * COALESCE(fvl.quantite_unite_eco_contribution,1) ) * fvl.quantite * IF(fvl.nomenclature_ligne_parent > 0,tva_lignes.tva,fvl.tva) /100  as tva,
            (100 - COALESCE(fvl.remise,0)) / 100 * fvl.tarif * fvl.quantite as tarif,
            IF(fvl.application_eco_contribution IS NULL OR fvl.application_eco_contribution = 0, IF(fvl.tarif_eco_contribution * COALESCE(fvl.quantite_unite_eco_contribution,1) < 0.01, 0.01, fvl.tarif_eco_contribution * COALESCE(fvl.quantite_unite_eco_contribution,1) )* fvl.quantite , 0) as eco_contribution_inclue,
            IF(fvl.application_eco_contribution = 1, IF(fvl.tarif_eco_contribution * COALESCE(fvl.quantite_unite_eco_contribution,1) < 0.01, 0.01, fvl.tarif_eco_contribution * COALESCE(fvl.quantite_unite_eco_contribution,1) ) * fvl.quantite , 0) as eco_contribution_en_sus,
            fvl.categorie_eco_contribution_id as categorie_eco_contribution_id,
            fvl.quantite * COALESCE(fvl.quantite_unite_eco_contribution,1) as quantite_unite,
            ceo.unite as unite,
            feo.eco_organisme_id as eco_organisme_id
            FROM facture_vente_lignes fvl
            JOIN facture_vente fv ON fv.id = fvl.document_id
            JOIN categorie_eco_contribution ceo ON ceo.id = fvl.categorie_eco_contribution_id
            JOIN famille_eco_contribution feo ON feo.id = ceo.famille_id 
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
            (SELECT 
            avl.id as id,
            avl.document_id as document_id,
            av.reference_document as reference_document,
            av.date as date_document,
            avl.article_id as article_id,
            -1 * avl.quantite as quantite,
            -1 * IF(avl.tarif_eco_contribution * COALESCE(avl.quantite_unite_eco_contribution,1) < 0.01, 0.01, avl.tarif_eco_contribution * COALESCE(avl.quantite_unite_eco_contribution,1) ) * avl.quantite  * IF(avl.nomenclature_ligne_parent > 0,tva_lignes.tva,avl.tva) /100  as tva,
            -1 * (100 - COALESCE(avl.remise,0)) / 100 * avl.tarif * avl.quantite as tarif,
            -1 * IF(avl.application_eco_contribution IS NULL OR avl.application_eco_contribution = 0, IF(avl.tarif_eco_contribution * COALESCE(avl.quantite_unite_eco_contribution,1) < 0.01, 0.01, avl.tarif_eco_contribution * COALESCE(avl.quantite_unite_eco_contribution,1) )  * avl.quantite, 0) as eco_contribution_inclue,
            -1 * IF(avl.application_eco_contribution = 1, IF(avl.tarif_eco_contribution * COALESCE(avl.quantite_unite_eco_contribution,1) < 0.01, 0.01, avl.tarif_eco_contribution * COALESCE(avl.quantite_unite_eco_contribution,1) )  * avl.quantite , 0) as eco_contribution_en_sus,
            avl.categorie_eco_contribution_id as categorie_eco_contribution_id,
            -1 * avl.quantite * COALESCE(avl.quantite_unite_eco_contribution,1) as quantite_unite,
            ceo.unite as unite,
            feo.eco_organisme_id as eco_organisme_id
            FROM avoir_vente_lignes avl
            JOIN avoir_vente av ON av.id = avl.document_id
            JOIN categorie_eco_contribution ceo ON ceo.id = avl.categorie_eco_contribution_id
            JOIN famille_eco_contribution feo ON feo.id = ceo.famille_id 
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
            WHERE avl.tarif_eco_contribution> 0 AND av.valide = 1);',
        'autres_conditions' => '',
    ];