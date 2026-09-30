<?php
return [
    'nom' => 'article_recurrent',
    'nom_sql' => 'article_recurrent',
    'joins' => '',
    'tables' => '',
    'alias_tables' => '',
    'table_par_defaut' => '',
    'type_de_vue' => '1',
    'requete' => "CREATE OR REPLACE VIEW article_recurrent AS SELECT 
                    CONCAT(recur.id,'0',docs.article_id) as id,
                    docs.client_id,
                    recur.id as recurrence_id,
                    type_document,
                    document_id,
                    docs.article_id,
                    article.famille_id,
                    docs.quantite,
                    docs.tarif,
                    depuis_le,
                    jusquau,
                    mode_recurrence,
                    IF(recur.inactif = 1,0,1) as actif
                    FROM `eden_recurrence_elements` as recur
                    LEFT JOIN (
                        SELECT devis_vente.id as document_id,devis_vente.id_recurrence,devis_vente.client_id,devis_vente_lignes.article_id, devis_vente_lignes.tarif, devis_vente_lignes.quantite,'devis_vente' as type_document,devis_vente.date,depuis_le,jusquau
                        FROM devis_vente_lignes
                        JOIN devis_vente ON devis_vente_lignes.document_id = devis_vente.id
                        JOIN (
                        SELECT MAX(devis_vente.id) as document_id,devis_vente_lignes.article_id,MIN(devis_vente.date) as depuis_le,MAX(devis_vente.date) as jusquau
                        FROM devis_vente
                        JOIN devis_vente_lignes ON devis_vente_lignes.document_id = devis_vente.id
                        WHERE id_recurrence != 0 AND id_recurrence IS NOT NULL AND devis_vente.valide = 1
                        GROUP BY CONCAT(id_recurrence,'0',article_id)
                        ) lignes_max ON lignes_max.article_id = devis_vente_lignes.article_id AND lignes_max.document_id = devis_vente_lignes.document_id
                    UNION
                    SELECT commande_vente.id as document_id,commande_vente.id_recurrence,commande_vente.client_id,commande_vente_lignes.article_id, commande_vente_lignes.tarif, commande_vente_lignes.quantite,'commande_vente' as type_document,commande_vente.date,depuis_le,jusquau
                        FROM commande_vente_lignes
                        JOIN commande_vente ON commande_vente_lignes.document_id = commande_vente.id
                        JOIN (
                        SELECT MAX(commande_vente.id) as document_id,commande_vente_lignes.article_id,MIN(commande_vente.date) as depuis_le,MAX(commande_vente.date) as jusquau
                        FROM commande_vente
                        JOIN commande_vente_lignes ON commande_vente_lignes.document_id = commande_vente.id
                        WHERE id_recurrence != 0 AND id_recurrence IS NOT NULL AND commande_vente.valide = 1
                        GROUP BY CONCAT(id_recurrence,'0',article_id)
                        ) lignes_max ON lignes_max.article_id = commande_vente_lignes.article_id AND lignes_max.document_id = commande_vente_lignes.document_id
                    UNION
                    SELECT bon_preparation_vente.id as document_id,id_recurrence,bon_preparation_vente.client_id,bon_preparation_vente_lignes.article_id, bon_preparation_vente_lignes.tarif, bon_preparation_vente_lignes.quantite,'bon_preparation_vente' as type_document,bon_preparation_vente.date,depuis_le,jusquau 
                        FROM bon_preparation_vente_lignes
                        JOIN bon_preparation_vente ON bon_preparation_vente_lignes.document_id = bon_preparation_vente.id
                        JOIN (
                        SELECT MAX(bon_preparation_vente.id) as document_id,bon_preparation_vente_lignes.article_id,MIN(bon_preparation_vente.date) as depuis_le,MAX(bon_preparation_vente.date) as jusquau
                        FROM bon_preparation_vente
                        JOIN bon_preparation_vente_lignes ON bon_preparation_vente_lignes.document_id = bon_preparation_vente.id
                        WHERE id_recurrence != 0 AND id_recurrence IS NOT NULL AND bon_preparation_vente.valide = 1
                        GROUP BY CONCAT(id_recurrence,'0',article_id)
                        ) lignes_max ON lignes_max.article_id = bon_preparation_vente_lignes.article_id AND lignes_max.document_id = bon_preparation_vente_lignes.document_id
                    UNION
                    SELECT bl_vente.id as document_id,bl_vente.id_recurrence,bl_vente.client_id,bl_vente_lignes.article_id, bl_vente_lignes.tarif, bl_vente_lignes.quantite,'bl_vente' as type_document,bl_vente.date,depuis_le,jusquau
                        FROM bl_vente_lignes
                        JOIN bl_vente ON bl_vente_lignes.document_id = bl_vente.id
                        JOIN (
                        SELECT MAX(bl_vente.id) as document_id,bl_vente_lignes.article_id,MIN(bl_vente.date) as depuis_le,MAX(bl_vente.date) as jusquau
                        FROM bl_vente
                        JOIN bl_vente_lignes ON bl_vente_lignes.document_id = bl_vente.id
                        WHERE id_recurrence != 0 AND id_recurrence IS NOT NULL AND bl_vente.valide = 1
                        GROUP BY CONCAT(id_recurrence,'0',article_id)
                        ) lignes_max ON lignes_max.article_id = bl_vente_lignes.article_id AND lignes_max.document_id = bl_vente_lignes.document_id
                    UNION
                    SELECT bon_retour_vente.id as document_id,bon_retour_vente.id_recurrence,bon_retour_vente.client_id,bon_retour_vente_lignes.article_id, bon_retour_vente_lignes.tarif, bon_retour_vente_lignes.quantite,'bon_retour_vente' as type_document,bon_retour_vente.date,depuis_le,jusquau 
                        FROM bon_retour_vente_lignes
                        JOIN bon_retour_vente ON bon_retour_vente_lignes.document_id = bon_retour_vente.id
                        JOIN (
                        SELECT MAX(bon_retour_vente.id) as document_id,bon_retour_vente_lignes.article_id,MIN(bon_retour_vente.date) as depuis_le,MAX(bon_retour_vente.date) as jusquau
                        FROM bon_retour_vente
                        JOIN bon_retour_vente_lignes ON bon_retour_vente_lignes.document_id = bon_retour_vente.id
                        WHERE id_recurrence != 0 AND id_recurrence IS NOT NULL AND bon_retour_vente.valide = 1
                        GROUP BY CONCAT(id_recurrence,'0',article_id)
                        ) lignes_max ON lignes_max.article_id = bon_retour_vente_lignes.article_id AND lignes_max.document_id = bon_retour_vente_lignes.document_id
                    UNION
                    SELECT acompte_vente.id as document_id,acompte_vente.id_recurrence,acompte_vente.client_id,acompte_vente_lignes.article_id, acompte_vente_lignes.tarif, acompte_vente_lignes.quantite,'acompte_vente' as type_document,acompte_vente.date,depuis_le,jusquau 
                        FROM acompte_vente_lignes
                        JOIN acompte_vente ON acompte_vente_lignes.document_id = acompte_vente.id
                        JOIN (
                        SELECT MAX(acompte_vente.id) as document_id,acompte_vente_lignes.article_id,MIN(acompte_vente.date) as depuis_le,MAX(acompte_vente.date) as jusquau
                        FROM acompte_vente
                        JOIN acompte_vente_lignes ON acompte_vente_lignes.document_id = acompte_vente.id
                        WHERE id_recurrence != 0 AND id_recurrence IS NOT NULL AND acompte_vente.valide = 1
                        GROUP BY CONCAT(id_recurrence,'0',article_id)
                        ) lignes_max ON lignes_max.article_id = acompte_vente_lignes.article_id AND lignes_max.document_id = acompte_vente_lignes.document_id
                    UNION
                    SELECT facture_vente.id as document_id,facture_vente.id_recurrence,facture_vente.client_id,facture_vente_lignes.article_id, facture_vente_lignes.tarif, facture_vente_lignes.quantite,'facture_vente' as type_document,facture_vente.date,depuis_le,jusquau 
                        FROM facture_vente_lignes
                        JOIN facture_vente ON facture_vente_lignes.document_id = facture_vente.id
                        JOIN (
                        SELECT MAX(facture_vente.id) as document_id,facture_vente_lignes.article_id,MIN(facture_vente.date) as depuis_le,MAX(facture_vente.date) as jusquau
                        FROM facture_vente
                        JOIN facture_vente_lignes ON facture_vente_lignes.document_id = facture_vente.id
                        WHERE id_recurrence != 0 AND id_recurrence IS NOT NULL AND facture_vente.valide = 1 AND facture_vente.valide = 1
                        GROUP BY CONCAT(id_recurrence,'0',article_id)
                        ) lignes_max ON lignes_max.article_id = facture_vente_lignes.article_id AND lignes_max.document_id = facture_vente_lignes.document_id
                    UNION
                    SELECT avoir_vente.id as document_id,avoir_vente.id_recurrence,avoir_vente.client_id,avoir_vente_lignes.article_id, avoir_vente_lignes.tarif, avoir_vente_lignes.quantite,'avoir_vente' as type_document,avoir_vente.date,depuis_le,jusquau
                        FROM avoir_vente_lignes
                        JOIN avoir_vente ON avoir_vente_lignes.document_id = avoir_vente.id
                        JOIN (
                        SELECT MAX(avoir_vente.id) as document_id,avoir_vente_lignes.article_id,MIN(avoir_vente.date) as depuis_le,MAX(avoir_vente.date) as jusquau
                        FROM avoir_vente
                        JOIN avoir_vente_lignes ON avoir_vente_lignes.document_id = avoir_vente.id
                        WHERE id_recurrence != 0 AND id_recurrence IS NOT NULL AND avoir_vente.valide = 1
                        GROUP BY CONCAT(id_recurrence,'0',article_id)
                        ) lignes_max ON lignes_max.article_id = avoir_vente_lignes.article_id AND lignes_max.document_id = avoir_vente_lignes.document_id
                    UNION
                    SELECT devis_achat.id as document_id,devis_achat.id_recurrence,devis_achat.client_id,devis_achat_lignes.article_id, devis_achat_lignes.tarif, devis_achat_lignes.quantite,'devis_achat' as type_document,devis_achat.date,depuis_le,jusquau
                        FROM devis_achat_lignes
                        JOIN devis_achat ON devis_achat_lignes.document_id = devis_achat.id
                        JOIN (
                        SELECT MAX(devis_achat.id) as document_id,devis_achat_lignes.article_id,MIN(devis_achat.date) as depuis_le,MAX(devis_achat.date) as jusquau
                        FROM devis_achat
                        JOIN devis_achat_lignes ON devis_achat_lignes.document_id = devis_achat.id
                        WHERE id_recurrence != 0 AND id_recurrence IS NOT NULL AND devis_achat.valide = 1
                        GROUP BY CONCAT(id_recurrence,'0',article_id)
                        ) lignes_max ON lignes_max.article_id = devis_achat_lignes.article_id AND lignes_max.document_id = devis_achat_lignes.document_id
                    UNION
                    SELECT commande_achat.id as document_id,commande_achat.id_recurrence,commande_achat.client_id,commande_achat_lignes.article_id, commande_achat_lignes.tarif, commande_achat_lignes.quantite,'commande_achat' as type_document,commande_achat.date,depuis_le,jusquau 
                        FROM commande_achat_lignes
                        JOIN commande_achat ON commande_achat_lignes.document_id = commande_achat.id
                        JOIN (
                        SELECT MAX(commande_achat.id) as document_id,commande_achat_lignes.article_id,MIN(commande_achat.date) as depuis_le,MAX(commande_achat.date) as jusquau
                        FROM commande_achat
                        JOIN commande_achat_lignes ON commande_achat_lignes.document_id = commande_achat.id
                        WHERE id_recurrence != 0 AND id_recurrence IS NOT NULL AND commande_achat.valide = 1
                        GROUP BY CONCAT(id_recurrence,'0',article_id)
                        ) lignes_max ON lignes_max.article_id = commande_achat_lignes.article_id AND lignes_max.document_id = commande_achat_lignes.document_id
                    UNION
                    SELECT bl_achat.id as document_id,bl_achat.id_recurrence,bl_achat.client_id,bl_achat_lignes.article_id, bl_achat_lignes.tarif, bl_achat_lignes.quantite,'bl_achat' as type_document,bl_achat.date,depuis_le,jusquau
                        FROM bl_achat_lignes
                        JOIN bl_achat ON bl_achat_lignes.document_id = bl_achat.id
                        JOIN (
                        SELECT MAX(bl_achat.id) as document_id,bl_achat_lignes.article_id,MIN(bl_achat.date) as depuis_le,MAX(bl_achat.date) as jusquau
                        FROM bl_achat
                        JOIN bl_achat_lignes ON bl_achat_lignes.document_id = bl_achat.id
                        WHERE id_recurrence != 0 AND id_recurrence IS NOT NULL  AND bl_achat.valide = 1
                        GROUP BY CONCAT(id_recurrence,'0',article_id)
                        ) lignes_max ON lignes_max.article_id = bl_achat_lignes.article_id AND lignes_max.document_id = bl_achat_lignes.document_id
                    UNION
                    SELECT acompte_achat.id as document_id,acompte_achat.id_recurrence,acompte_achat.client_id,acompte_achat_lignes.article_id, acompte_achat_lignes.tarif, acompte_achat_lignes.quantite,'acompte_achat' as type_document,acompte_achat.date,depuis_le,jusquau 
                        FROM acompte_achat_lignes
                        JOIN acompte_achat ON acompte_achat_lignes.document_id = acompte_achat.id
                        JOIN (
                        SELECT MAX(acompte_achat.id) as document_id,acompte_achat_lignes.article_id,MIN(acompte_achat.date) as depuis_le,MAX(acompte_achat.date) as jusquau
                        FROM acompte_achat
                        JOIN acompte_achat_lignes ON acompte_achat_lignes.document_id = acompte_achat.id
                        WHERE id_recurrence != 0 AND id_recurrence IS NOT NULL AND acompte_achat.valide = 1
                        GROUP BY CONCAT(id_recurrence,'0',article_id)
                        ) lignes_max ON lignes_max.article_id = acompte_achat_lignes.article_id AND lignes_max.document_id = acompte_achat_lignes.document_id
                    UNION
                    SELECT facture_achat.id as document_id,facture_achat.id_recurrence,facture_achat.client_id,facture_achat_lignes.article_id, facture_achat_lignes.tarif, facture_achat_lignes.quantite,'facture_achat' as type_document,facture_achat.date,depuis_le,jusquau 
                        FROM facture_achat_lignes
                        JOIN facture_achat ON facture_achat_lignes.document_id = facture_achat.id
                        JOIN (
                        SELECT MAX(facture_achat.id) as document_id,facture_achat_lignes.article_id,MIN(facture_achat.date) as depuis_le,MAX(facture_achat.date) as jusquau
                        FROM facture_achat
                        JOIN facture_achat_lignes ON facture_achat_lignes.document_id = facture_achat.id
                        WHERE id_recurrence != 0 AND id_recurrence IS NOT NULL AND facture_achat.valide = 1
                        GROUP BY CONCAT(id_recurrence,'0',article_id)
                        ) lignes_max ON lignes_max.article_id = facture_achat_lignes.article_id AND lignes_max.document_id = facture_achat_lignes.document_id
                    UNION
                    SELECT avoir_achat.id as document_id,avoir_achat.id_recurrence,avoir_achat.client_id,avoir_achat_lignes.article_id, avoir_achat_lignes.tarif, avoir_achat_lignes.quantite,'avoir_achat' as type_document,avoir_achat.date,depuis_le,jusquau
                        FROM avoir_achat_lignes
                        JOIN avoir_achat ON avoir_achat_lignes.document_id = avoir_achat.id
                        JOIN (
                        SELECT MAX(avoir_achat.id) as document_id,avoir_achat_lignes.article_id,MIN(avoir_achat.date) as depuis_le,MAX(avoir_achat.date) as jusquau
                        FROM avoir_achat
                        JOIN avoir_achat_lignes ON avoir_achat_lignes.document_id = avoir_achat.id
                        WHERE id_recurrence != 0 AND id_recurrence IS NOT NULL AND avoir_achat.valide = 1
                        GROUP BY CONCAT(id_recurrence,'0',article_id)
                        ) lignes_max ON lignes_max.article_id = avoir_achat_lignes.article_id AND lignes_max.document_id = avoir_achat_lignes.document_id
                    UNION
                    SELECT bon_retour_achat.id as document_id,bon_retour_achat.id_recurrence,bon_retour_achat.client_id,bon_retour_achat_lignes.article_id, bon_retour_achat_lignes.tarif, bon_retour_achat_lignes.quantite,'bon_retour_achat' as type_document,bon_retour_achat.date,depuis_le,jusquau 
                        FROM bon_retour_achat_lignes
                        JOIN bon_retour_achat ON bon_retour_achat_lignes.document_id = bon_retour_achat.id
                        JOIN (
                        SELECT MAX(bon_retour_achat.id) as document_id,bon_retour_achat_lignes.article_id,MIN(bon_retour_achat.date) as depuis_le,MAX(bon_retour_achat.date) as jusquau
                        FROM bon_retour_achat
                        JOIN bon_retour_achat_lignes ON bon_retour_achat_lignes.document_id = bon_retour_achat.id
                        WHERE id_recurrence != 0 AND id_recurrence IS NOT NULL AND bon_retour_achat.valide = 1
                        GROUP BY CONCAT(id_recurrence,'0',article_id)
                        ) lignes_max ON lignes_max.article_id = bon_retour_achat_lignes.article_id AND lignes_max.document_id = bon_retour_achat_lignes.document_id
                    ) docs ON recur.id = docs.id_recurrence
                    JOIN article ON docs.article_id = article.id
                    WHERE docs.article_id IS NOT NULL AND docs.article_id != 0
                    GROUP BY CONCAT(recur.id,'0',docs.article_id);",
    'autres_conditions' => '',
];