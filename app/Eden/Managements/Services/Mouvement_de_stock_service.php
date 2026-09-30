<?php

namespace App\Eden\Managements\Services;

use DB;

class Mouvement_de_stock_service {

    public function parametres_mouvement_de_stock($type_element){

        $stocks_reserves = [
            'commande_vente',
            'commande_achat'
        ];

        return ['reserve' => in_array($type_element,$stocks_reserves) ? 1 : 0];
    }

    public function genere_mouvement_de_stock($entete_document,$management_lignes,$recreation = true){

        $articles_erp = $entete_document->articles_du_document()->keyBy('id');

        $lignes_article = $entete_document->lignes_articles()->keyBy('id');

        $parametres = $this->parametres_mouvement_de_stock($entete_document->_type_element);

        $lignes_a_gerer = collect([]);
        $managements_a_gerer = collect([]);

        foreach($management_lignes as $management_ligne){

            $modele_ligne = $management_ligne->modele;

            $article = isset($articles_erp[$modele_ligne->article_id]) ? $articles_erp[$modele_ligne->article_id] : null;

            if(empty($article) || $article->type_article == 1 || $article->stockable != 1)
                continue;

            if(in_array($modele_ligne->id,$lignes_a_gerer->pluck('id')->toArray()))
                continue;

            $lignes_a_gerer->push($modele_ligne);
            $managements_a_gerer->push($management_ligne);
        }

        $ids_lignes = $lignes_a_gerer->pluck('id')->toArray();

        // on les efface tous
		$mouvements = modele('mouvement_de_stock')
			->where('type_document', $entete_document->_type_element)
			->whereIn('ligne_id', $ids_lignes);

        foreach($parametres as $nom => $valeur){

            $mouvements = $mouvements->where($nom,$valeur);
        }

        $mouvements = $mouvements->get();

        foreach($mouvements as $mouvement){
            management('mouvement_de_stock', $mouvement->id, $mouvement)->supprime();
        }

        if($recreation !== true)
            return;

        $quantite_par_ligne = $entete_document->quantite_lignes_mouvement($lignes_a_gerer);

        $entrepot_id_par_defaut = fonctionnalite('entrepot_id_par_defaut');

        foreach($managements_a_gerer as $management) {

            $ligne = $management->modele;

            // Si la ligne est en document annule alors il ne faut pas recreer les mouvements
            if(!empty($ligne->document_annule))
                continue;

            // on regarde si on est dans une nomenclature ou un produit fini
            // si on est dans un produit fini, pas de stock à mouvementer
            if (!empty($ligne->nomenclature_ligne_parent)) {

                $nomenclature_parent = $ligne->nomenclature_ligne_parent;

                while ($nomenclature_parent != null) {

                    if (empty($lignes_article[$nomenclature_parent]))
                        $nomenclature_parent = false;

                    $ligne_article_parent = $lignes_article[$nomenclature_parent];

                    if (empty($articles_erp[$ligne_article_parent->article_id]))
                        $nomenclature_parent = false;

                    $article_parent = $articles_erp[$ligne_article_parent->article_id];

                    if ($article_parent->type_article == 3)
                        $nomenclature_parent = false;
                    
                    if($nomenclature_parent !== false)
                        $nomenclature_parent = $ligne_article_parent->nomenclature_ligne_parent;
                }

                if($nomenclature_parent === false)
                    continue;
            }

            $quantite_ligne = $quantite_par_ligne[$ligne->id] ?? 0;

            if($quantite_ligne <= 0)
                continue;

			$conditionnement_id = $ligne->conditionnement;

			$quantite_par_conditionnement = $management->conditionnement_de_la_ligne();

            if(!empty($ligne->entrepot_id))
                $entrepot_id = $ligne->entrepot_id;
            elseif(!empty($entete_document->modele->entrepot_id))
			    $entrepot_id = $entete_document->modele->entrepot_id;
            else
                $entrepot_id = $entrepot_id_par_defaut;

            if($entete_document->_type_element == 'commande_achat'){
                $quantite = $quantite_ligne;
                $quantite_conditionnement = $quantite_ligne / $quantite_par_conditionnement;
            }
            else if(($entete_document->est_une_vente() && $entete_document->_type_element != 'bon_retour_vente') ||
                $entete_document->_type_element == 'bon_retour_achat'){
                $quantite = $quantite_ligne * $quantite_par_conditionnement * -1;
                $quantite_conditionnement = $quantite_ligne * -1;
            }
            else {
                $quantite = $quantite_ligne * $quantite_par_conditionnement;
                $quantite_conditionnement = $quantite_ligne;
            }

            if(fonctionnalite('gescom_eclatement_automatique_conditionnement_bl_achat') === true && $entete_document->_type_element == 'bl_achat') {
                $conditionnement_id = null;
                $quantite_conditionnement = $quantite;
            }

            $date = $entete_document->modele->date;

            if($entete_document->_type_element == 'commande_achat')
                $date = $ligne->date_de_reception;

            if(empty($date) || $date == '0000-00-00')
                $date = date('Y-m-d');

			$informations = array(
				'ligne_id' => $ligne->id,
				'type_document' => $entete_document->_type_element,
				'document_id' => $ligne->document_id,
				'date' => $date,
				'article_id' => $ligne->article_id,
				'entrepot_id' => $entrepot_id,
				'projet_id' => $entete_document->modele->projet_id,
				'type_de_mouvement' => 0,
				'quantite' => $quantite,
				'quantite_conditionnement' => $quantite_conditionnement,
				'conditionnement_id' => $conditionnement_id,
			);

            foreach($parametres as $nom => $valeur){
                $informations[$nom] = $valeur;
            }

			$management = management('mouvement_de_stock');

//            $management->fonction_a_eviter[] = 'maj_index_recherche';
            $management->fonction_a_eviter[] = 'notifications';

			$management->enregistre($informations);
        }

        //@todo mise à jour en masse des index de recherche des mouvements_de_stocks crées
    }
}