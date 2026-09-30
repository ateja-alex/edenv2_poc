<?php

namespace App\Eden\Managements\Services;

/**
 * 
 * Ce service sert juste à calculer les totaux sur les documents de gestion commerciale
 * 
 * Ce code aurait pu être dans document_management, mais il a été placé ici
 * Dans le seul but de limiter le nombre de méthodes dans document_management
 * 
 */
class Calcul_total_sur_document_service {
	
	/**
	 * 
	 * initialise les champs totaux renvoyés par les méthodes calcule_suivant_methode_ht et calcule_suivant_methode_ttc
	 * 
	 */
	protected function initialise_totaux() {
		
		$totaux = array(

			'ht' => 0,
            'ht_avec_eco_contribution' => 0,
			'ht_hors_frais_de_livraison' => 0,
			'eco_contribution' => 0,
			'eco_contribution_inclus' => 0,
			'tva' => 0,
			'ttc' => 0,
			'ttc_sans_eco_contribution' => 0,
            'somme_pa_articles' => 0,
            'somme_pu_articles' => 0,
            'marge_brute_montant' => 0,
            'marge_nette_montant' => 0,
            'marge_brute_pourcentage' => 0,
            'marge_nette_pourcentage' => 0,
			'eco_contribution_ttc' => 0,
			'eco_contribution_inclus_ttc' => 0,
			'par_tva' => array(),
			'par_ligne' => array(),
			'par_ligne_tarif_net' => array(),
            'par_ligne_marge_brute_montant' => array(),
            'par_ligne_marge_brute_pourcentage' => array(),
            'par_ligne_marge_nette_montant' => array(),
            'par_ligne_marge_nette_pourcentage' => array(),
			'par_ligne_eco_contribution' => array(),
			'par_ligne_eco_contribution_inclus' => array(),
			'par_ligne_ttc' => array(),
		);
        
        return $totaux;
	}
	
	/**
	 * 
	 * Retourne true si on doit forcer la TVA à 0
	 * 
	 */
	protected function force_tva_a_zero() {
		
		$forcer_tva_0 = false;
		
		if(!empty($this->modele) && is_object($this->modele) && isset($this->modele->client_id)) {
			
			$client = modele('client', $this->modele->client_id);
			
			if($client->forcer_tva_0 == 1)
				$forcer_tva_0 = true;
		}
		
		return $forcer_tva_0;
	}
	
	/**
	 *
	 * Calcule le total du document en partant des prix HT
	 *
	 * @return array
	 *
	 */
	public function calcule($methode, $articles, $management, $frais_de_port_saisie = null) {

		$nombre_de_chiffres_decimaux_sur_les_tarif = fonctionnalite('nombre_de_chiffres_decimaux_sur_les_tarif');

		// on transfère le modèle du management dans le service
		$this->modele = $management->modele;
		
		$totaux = $this->initialise_totaux();
		
		$forcer_tva_0 = $this->force_tva_a_zero();

		// on calcule les frais de port pour les articles en ligne
		// c'est un type_article = 2 sur l'article
		$montant_frais_de_port_ht = 0;
		$montant_frais_de_port_ttc = 0;
		
		$modeles_articles = array();
		$ids_articles = array();
		$lignes_du_document = array();

		if($management->existe()) {

			// on met à jour les lignes
			$lignes_du_document = $management->modele_lignes()->where('document_id', $this->modele->id)->get();

			foreach($lignes_du_document as $ligne) {

				$ids_articles[$ligne->article_id] = $ligne->article_id;
			}
		}

		$modeles_articles = $management->articles_du_document()->keyBy('id');

        $options_regroupement_id = [];

        $gescom_regle_arrondi_ligne = fonctionnalite('gescom_regle_arrondi_ligne');

		if ($articles != []) {

            $tableau_sous_total = array(
                'ht' => 0.00,
                'tarif_net' => 0.00,
                'eco_contribution' => 0.00,
                'eco_contribution_inclus' => 0.00,
                'ttc' => 0.00,
                'total_option' => []
            );

            foreach($articles as $article) {
                
                if(isset($article['type_ligne'])) {
                    if($article['type_ligne']== 'sous_total'){

                        $totaux['par_ligne'][] =  $tableau_sous_total['ht'];
                        $totaux['par_ligne_tarif_net'][] =  $tableau_sous_total['tarif_net'];
                        $totaux['par_ligne_eco_contribution'][] =  $tableau_sous_total['eco_contribution'];
                        $totaux['par_ligne_eco_contribution_inclus'][] =  $tableau_sous_total['eco_contribution_inclus'];
                        $totaux['par_ligne_ttc'][] =  $tableau_sous_total['ttc'];
                        
                        $tableau_sous_total = array(
                            'ht' => 0.00,
                            'tarif_net' => 0.00,
                            'eco_contribution' => 0.00,
                            'eco_contribution_inclus' => 0.00,
                            'ttc' => 0.00
                        );
                    }else{
                        
                        $totaux['par_ligne'][] = '';
                        $totaux['par_ligne_tarif_net'][] = '';
                        $totaux['par_ligne_eco_contribution'][] = '';
                        $totaux['par_ligne_eco_contribution_inclus'][] = '';
                        $totaux['par_ligne_ttc'][] = '';
                    }

                    if($article['type_ligne'] == 'regroupement' && (
                        (!empty($article['contenu']) && $article['contenu'] == 1) ||
                        (!empty($article['regroupement_id'])
                            && in_array($article['regroupement_id'],array_keys($options_regroupement_id)))
                        )) {

                        $parent = !empty($article['regroupement_id'])
                            && in_array($article['regroupement_id'],array_keys($options_regroupement_id)) ?
                            $article['regroupement_id'] : $article['id'];

                        $options_regroupement_id[$article['id']] = $parent;

                        if(!isset($totaux['total_option'][$parent]))
                            $totaux['total_option'][$article['id']] = [
                                'nom' => $article['nom'],
                                'ht' => 0,
                                'ttc' => 0
                            ];
                    }

                    continue;
                }

				// c'est le cas ou on calcule via ajax, article arrive en tant que tableau
				$article = (object) $article;

                $modele_article = null;

				if(isset($modeles_articles[$article->article_id]))
					$modele_article = $modeles_articles[$article->article_id];

				if(!isset($article->tva))
					$article->tva = "0";
				
				if($forcer_tva_0 === false) {

					$tva = (string) $article->tva;
				} 
				else {

					$tva = "0";
				}
				
				if(empty($tva) || $tva == 'null')
					$tva = "0";
				
				if(empty($article->remise) || $article->remise == 'NaN' || $article->remise == 'null')
					$article->remise = 0;

                if(empty($article->tarif) || $article->tarif == 'NaN' || $article->tarif == 'null')
					$article->tarif = 0;
                
                if(empty($article->prix_achat) || $article->prix_achat == 'NaN' || $article->prix_achat == 'null')
					$article->prix_achat = 0;
                
				if($management->est_une_vente()){
					if(!isset($article->coefficient_article) || empty($article->coefficient_article) || $article->coefficient_article == 'NaN' || $article->coefficient_article == 'null')
						$article->coefficient_article = 0;

					if(!isset($article->coefficient_regroupement) || empty($article->coefficient_regroupement) || $article->coefficient_regroupement == 'NaN' || $article->coefficient_regroupement == 'null')
						$article->coefficient_regroupement = 0;

					if(!isset($article->coefficient_devis) || empty($article->coefficient_devis) || $article->coefficient_devis == 'NaN' || $article->coefficient_devis == 'null')
						$article->coefficient_devis = 0;
				}

                if(!isset($article->application_eco_contribution))
                    $article->application_eco_contribution = null;

                if(!isset($article->tarif_eco_contribution))
                    $article->tarif_eco_contribution = 0;

                if(!isset($article->quantite_unite_eco_contribution))
                    $article->quantite_unite_eco_contribution = 1;

				if(!isset($article->tarif) || empty($article->tarif) || $article->tarif == 'NaN' || $article->tarif == 'null')
					$article->tarif = 0;
				
				if(!isset($article->quantite) || empty($article->quantite) || $article->quantite == 'NaN' || $article->quantite == 'null')
					$article->quantite = 0;

				// au cas où
				$article->quantite = str_replace(',', '.', $article->quantite);
				$article->tarif = str_replace(',', '.', $article->tarif);
				$article->remise = str_replace(',', '.', $article->remise);
				$tva = str_replace(',', '.', $tva);

                $eco_contribution = $this->calcul_eco_contribution($article,1,$modeles_articles, $management->_type_element);
                $eco_contribution_inclus = $this->calcul_eco_contribution($article,0,$modeles_articles, $management->_type_element);

                // Calcul du total des lignes
                $total_ligne = floatval($article->tarif) *
                    (100 - floatval($article->remise)) / 100;

                if($management->est_une_vente())
                    $total_ligne *= (100 + floatval($article->coefficient_article)) / 100 *
                        (100 + floatval($article->coefficient_regroupement)) / 100 *
                        (100 + floatval($article->coefficient_devis)) / 100;

                $tarif_net = round($total_ligne,2);

                $totaux['par_ligne_tarif_net'][] = $tarif_net;
                $tableau_sous_total['tarif_net'] += $tarif_net;

                if($gescom_regle_arrondi_ligne == 'prix_unitaire')
                    $total_ligne = $tarif_net;

                $total_ligne = $total_ligne * floatval($article->quantite);

                if($methode == 'ttc') {

                    $total_ligne += $eco_contribution;

                    $total_ligne = $total_ligne * (100 + $tva) / 100;

                }

                if($gescom_regle_arrondi_ligne == 'total' || $methode == 'ttc')
                    $total_ligne = round($total_ligne,2);
				

				if(!empty($modele_article) && $modele_article->type_article == 2) {
					
					if($methode == 'ttc')
						$montant_frais_de_port_ht += $total_ligne / ((100 + $tva) / 100);
					else
						$montant_frais_de_port_ht += $total_ligne;
						
				}
					
				if(!empty($modele_article) && $modele_article->type_article == 2)
					$montant_frais_de_port_ttc += $total_ligne;
				
				// on regarde l'avancement si nécessaire
				if(isset($this->modele->type_facture) && ($this->modele->type_facture == 1 || $this->modele->type_facture == 2)) {
					
					if(empty($article->avancement_actuel))
						$article->avancement_actuel = 0;
					
					if(empty($article->avancement_precedent))
						$article->avancement_precedent = 0;
					
					// facture finale
					if($this->modele->type_facture == 2)
						$article->avancement_actuel = 100;
					
					if(empty($article->avancement_actuel) || in_array($article->avancement_actuel, array('undefined', 'null')))
						$article->avancement_actuel = 0;
					
					if(empty($article->avancement_precedent) || in_array($article->avancement_precedent, array('undefined', 'null')))
						$article->avancement_precedent = 0;
					
					$total_ligne *= ($article->avancement_actuel - $article->avancement_precedent) / 100;
				}

				if($methode == 'ttc') {
					
					$par_ligne = $total_ligne / ((100 + $tva) / 100);
					$par_ligne_ttc = $total_ligne;
				}
				else {

                    $par_ligne = montant($total_ligne, $nombre_de_chiffres_decimaux_sur_les_tarif, '.', '') ;
                    
                    $par_ligne_ttc = round(($total_ligne + $eco_contribution) * (100 + $tva) / 100, $nombre_de_chiffres_decimaux_sur_les_tarif);
				}

                if(!empty($article->regroupement_id) && isset($options_regroupement_id[$article->regroupement_id])) {
                    $totaux['total_option'][$options_regroupement_id[$article->regroupement_id]]['ht'] += $par_ligne;
                    $totaux['total_option'][$options_regroupement_id[$article->regroupement_id]]['ttc'] += $par_ligne_ttc;
                }
                else {

                    if(!isset($totaux['par_tva'][$tva]))
                        $totaux['par_tva'][$tva] = array(
                            $methode => 0,
                            'eco_contribution' => 0,
                            'eco_contribution_tva' => 0,
                            'eco_contribution_inclus' => 0
                        );

                    if (fonctionnalite('gescom_document_arrondi_par_ligne'))
                        $totaux['par_tva'][$tva][$methode] += round($total_ligne, 2);
                    else
                        $totaux['par_tva'][$tva][$methode] += $total_ligne;

                    $totaux['par_tva'][$tva]['eco_contribution'] += $eco_contribution;
                    $totaux['par_tva'][$tva]['eco_contribution_tva'] += round($eco_contribution * ($tva / 100), $nombre_de_chiffres_decimaux_sur_les_tarif);
                    $totaux['par_tva'][$tva]['eco_contribution_inclus'] += $eco_contribution_inclus;
                }
                
                $totaux['somme_pa_articles'] += $article->prix_achat * $article->quantite;
                $totaux['somme_pu_articles'] += $article->tarif * $article->quantite;
                
                $totaux['par_ligne'][] = $par_ligne;
                $totaux['par_ligne_eco_contribution'][] = $eco_contribution;
                $totaux['par_ligne_eco_contribution_inclus'][] = $eco_contribution_inclus;
                $totaux['par_ligne_ttc'][] = $par_ligne_ttc;

                $tableau_sous_total['ht'] += $par_ligne;
                $tableau_sous_total['eco_contribution'] += $eco_contribution;
                $tableau_sous_total['eco_contribution_inclus'] += $eco_contribution_inclus;
                $tableau_sous_total['ttc'] += $par_ligne_ttc;
            }
		}

        $this->applique_remise_pour_lignes_sur_totaux($articles, $totaux, $methode);

		// on retraite
		foreach($totaux['par_tva'] as $taux_tva => $montant) {

			if(empty($taux_tva) || $taux_tva === 'null')
				$taux_tva = 0;
			
			if($methode == 'ht') {
				
				$tva = round($montant['ht'] * $taux_tva / 100, $nombre_de_chiffres_decimaux_sur_les_tarif);
                $tva_avec_eco_contribution = round(($montant['ht'] + $montant['eco_contribution']) * $taux_tva / 100, $nombre_de_chiffres_decimaux_sur_les_tarif);

				$totaux['ht'] += $montant['ht'];
                $totaux['ht_avec_eco_contribution'] += $montant['ht'] + $montant['eco_contribution'];
				$totaux['eco_contribution'] += $montant['eco_contribution'];
				$totaux['eco_contribution_inclus'] += $montant['eco_contribution_inclus'];
				$totaux['eco_contribution_ttc'] += round($montant['eco_contribution'] * (1 + $taux_tva / 100), $nombre_de_chiffres_decimaux_sur_les_tarif);
                $totaux['eco_contribution_inclus_ttc'] += round($montant['eco_contribution_inclus'] * (1 + $taux_tva / 100), $nombre_de_chiffres_decimaux_sur_les_tarif);
                $totaux['tva'] += $tva_avec_eco_contribution;
				$totaux['ttc_sans_eco_contribution'] += $montant['ht'] + $tva;
                $totaux['ttc'] += $montant['ht'] + $montant['eco_contribution'] + $tva_avec_eco_contribution;

				// on met à jour le tableau total
                $totaux['par_tva'][$taux_tva]['tva_sans_eco_contribution'] = $tva;
                $totaux['par_tva'][$taux_tva]['ttc'] = $montant['ht'] + $montant['eco_contribution'] + $tva_avec_eco_contribution;
                $totaux['par_tva'][$taux_tva]['tva'] = $tva_avec_eco_contribution;
			}
			else if($methode == 'ttc'){
				
				$ht = round(($montant['ttc'] / (1+ $taux_tva / 100)) - $montant['eco_contribution'], $nombre_de_chiffres_decimaux_sur_les_tarif);

				$totaux['ht'] += $ht;
                $totaux['ht_avec_eco_contribution'] += $ht + $montant['eco_contribution'];
                $totaux['eco_contribution'] += $montant['eco_contribution'];
                $totaux['eco_contribution_inclus'] += $montant['eco_contribution_inclus'];
                $totaux['eco_contribution_ttc'] += round($montant['eco_contribution'] * (1 + $taux_tva / 100), $nombre_de_chiffres_decimaux_sur_les_tarif);
                $totaux['eco_contribution_inclus_ttc'] += round($montant['eco_contribution_inclus'] * (1 + $taux_tva / 100), $nombre_de_chiffres_decimaux_sur_les_tarif);
				$totaux['ttc'] += $montant['ttc'];

				// on met à jour le tableau total
				$totaux['par_tva'][$taux_tva]['ht'] = $ht;
                $totaux['par_tva'][$taux_tva]['tva'] = $montant['ttc'] - $ht - $montant['eco_contribution'];
                $totaux['par_tva'][$taux_tva]['tva_sans_eco_contribution'] = $montant['ttc'] - $ht - $montant['eco_contribution'];
			}

            $totaux['par_tva'][$taux_tva]['eco_contribution_inclus'] = round($montant['eco_contribution_inclus'] * $taux_tva / 100, $nombre_de_chiffres_decimaux_sur_les_tarif);
		}

        $totaux['eco_contribution'] = round($totaux['eco_contribution'],$nombre_de_chiffres_decimaux_sur_les_tarif);
        $totaux['eco_contribution_tva'] = round($totaux['eco_contribution_ttc'] - $totaux['eco_contribution'],$nombre_de_chiffres_decimaux_sur_les_tarif);
        $totaux['eco_contribution_inclus'] = round($totaux['eco_contribution_inclus'],$nombre_de_chiffres_decimaux_sur_les_tarif);

		// y'a une remise ?
		$remise_en_pourcentage = 0;
	    $totaux['avant_remise'] = $totaux;
        
		// temps_execution("Calcule total dans service::etape 2", 3);

        $this->applique_remise_pour_lignes_sur_totaux($totaux, $articles, $methode);
		$totaux = $this->calcule_marges_a_la_ligne($totaux, $articles);

		// une remise globale ?
		if(!empty($this->modele->remise_globale) && !empty($totaux['ttc'])) {
			
			// c'est une remise en €, on calcule sur le ttc
			if($this->modele->remise_globale_type  == 2) {
				
				if(fonctionnalite('type_remise_globale_en_montant') == 'HT') {
					
					$remise_en_pourcentage = $this->modele->remise_globale / ($totaux['ht'] - $montant_frais_de_port_ht);
				}
				else {
					
					$remise_en_pourcentage = $this->modele->remise_globale / ($totaux['ttc'] - $montant_frais_de_port_ttc);
				}
			}
			else {
				
				$remise_en_pourcentage = $this->modele->remise_globale / 100;
			}
		}
		
		// un coupon reduction ?
		if(!empty($this->modele->coupon_reduction)) {
			
			$retour = $management->calcule_coupon_reduction(modele('coupon_reduction', $management->modele->coupon_reduction), strtotime(formate_date('Y-m-d', $management->modele->date)), $management->modele->client_id, $totaux['ttc'], $articles);
			
			if($retour['succes'] === true) {

				$remise_en_pourcentage += ($retour['valeur'] / 100);

			}
		}

		$totaux['remise_en_pourcentage_avant_prorata'] = $remise_en_pourcentage;

		// on applique la remise
		if(!empty($remise_en_pourcentage)) {
			
			// on doit calculer une proportion de remise pour les frais de port
			$remise_en_pourcentage *= ($totaux['ht'] - $montant_frais_de_port_ht) / $totaux['ht'];

			// on applique la remise
			foreach($totaux as $cle => $valeur) {
				
				if(!in_array($cle, array(
                    'par_tva',
                    'eco_contribution',
                    'eco_contribution_ttc',
                    'eco_contribution_inclus_ttc',
                    'eco_contribution_tva',
                    'eco_contribution_inclus',
                    'avant_remise',
                    'marge_brute_montant',
                    'marge_nette_montant',
                    'marge_brute_pourcentage',
                    'marge_nette_pourcentage',
                    'somme_pu_articles',
                    'somme_pa_articles',
                    'par_ligne',
                    'par_ligne_tarif_net',
                    'par_ligne_ttc',
                    'par_ligne_eco_contribution',
                    'par_ligne_eco_contribution_inclus',
                    'par_ligne_marge_brute_montant',
                    'par_ligne_marge_brute_pourcentage',
                    'par_ligne_marge_nette_montant',
                    'par_ligne_marge_nette_pourcentage',
                    'remise_en_pourcentage_avant_prorata'
                ))) {

                    $totaux[$cle] = $valeur * (1-$remise_en_pourcentage);
				}
			}
			
			// on applique la remise
			foreach($totaux['par_tva'] as $taux_tva => $totaux_pour_cette_tva) {
				
				foreach($totaux_pour_cette_tva as $cle => $valeur) {

                    if(!in_array($cle, array(
                        'eco_contribution',
                        'eco_contribution_tva',
                        'eco_contribution_inclus'
                    ))){

                        $totaux['par_tva'][$taux_tva][$cle] = $valeur * (1 - $remise_en_pourcentage);

                    }
				}
			}
		}

        // On ajoute les eco-contrib après l'application des remises, il est interdit de remiser l'eco-contrib
        $totaux['ttc'] = 0;
        $totaux['ht_avec_eco_contribution'] = 0;
        $totaux['tva'] = 0;
        $totaux['ttc_sans_eco_contribution'] = 0;
        foreach($totaux['par_tva'] as $taux_tva => $montant) {

            if(empty($taux_tva) || $taux_tva === 'null')
                $taux_tva = 0;

            if($methode == 'ht') {

                $tva_avec_eco_contribution = round(($montant['ht'] + $montant['eco_contribution']) * ($taux_tva / 100), $nombre_de_chiffres_decimaux_sur_les_tarif);

                $totaux['ht_avec_eco_contribution'] += $montant['ht'] + $montant['eco_contribution'];
                $totaux['tva'] += $tva_avec_eco_contribution;
                $totaux['ttc'] += $montant['ht'] + $montant['eco_contribution'] + $tva_avec_eco_contribution;
                $totaux['par_tva'][$taux_tva]['ttc'] = $montant['ht'] + $montant['eco_contribution'] + $tva_avec_eco_contribution;
                $totaux['par_tva'][$taux_tva]['tva'] = $tva_avec_eco_contribution;
            }
            else if($methode == 'ttc'){

                $totaux['ht_avec_eco_contribution'] += $montant['ht'] + $montant['eco_contribution'];
                $totaux['tva'] += $totaux['ttc'] - $montant['ht'] - $montant['eco_contribution'];
                $totaux['ttc_sans_eco_contribution'] += $montant['ttc']- $montant['eco_contribution'];
                $totaux['par_tva'][$taux_tva]['tva'] = $montant['ttc'] - $montant['ht'] - $montant['eco_contribution'];
                $totaux['par_tva'][$taux_tva]['tva_sans_eco_contribution'] = $montant['ttc'] - $montant['ht'] - $montant['eco_contribution'];
            }

        }

        if(!empty($this->modele->ecart_gestion_ttc)){
            $totaux['ttc'] += $this->modele->ecart_gestion_ttc;
            $totaux['ttc_sans_eco_contribution'] += $this->modele->ecart_gestion_ttc;
        }
		
		// on crée un total spécifique pour les totaux (calcul des marges notamment)
		$totaux['ht_hors_frais_de_livraison'] = $totaux['ht'] - $montant_frais_de_port_ht;
        
		// on ajoute les frais de port
		$totaux = $management->ajoute_frais_de_port_au_totaux($totaux, $articles, $frais_de_port_saisie);
        
        // On calcule les marges du document
        $totaux['marge_brute_montant'] = $totaux['somme_pu_articles'] - $totaux['eco_contribution_inclus'] - $totaux['somme_pa_articles'];
        $totaux['marge_nette_montant'] = $totaux['ht_hors_frais_de_livraison'] - $totaux['eco_contribution_inclus'] - $totaux['somme_pa_articles'];
        
        if(fonctionnalite('type_calcul_du_pourcentage_marge') == 'prix_de_vente'){

            $totaux['marge_brute_pourcentage'] = $totaux['somme_pu_articles'] == 0 ? 0 : round($totaux['marge_brute_montant'] / ($totaux['somme_pu_articles'] - $totaux['eco_contribution_inclus']) * 100, 2);
            $totaux['marge_nette_pourcentage'] = $totaux['ht_hors_frais_de_livraison'] == 0 ? 0 : round($totaux['marge_nette_montant'] / ($totaux['ht_hors_frais_de_livraison'] - $totaux['eco_contribution_inclus']) * 100, 2);
        }
        else {

            $totaux['marge_brute_pourcentage'] = $totaux['somme_pa_articles'] == 0 ? 0 : round($totaux['marge_brute_montant'] / $totaux['somme_pa_articles'] * 100, 2);
            $totaux['marge_nette_pourcentage'] = $totaux['somme_pa_articles'] == 0 ? 0 : round($totaux['marge_nette_montant'] / $totaux['somme_pa_articles'] * 100, 2);
        }

		return $this->applique_acompte_sur_totaux($totaux);
	}
	
	/**
	 * 
	 * On applique l'acompte sur le calcul du montant total du document
	 * 
	 */
	protected function applique_acompte_sur_totaux($totaux) {
		
		$nombre_de_chiffres_decimaux_sur_les_tarif = fonctionnalite('nombre_de_chiffres_decimaux_sur_les_tarif');

		// on vient traiter les acomptes
		if(!empty($this->modele->acompte) && $this->modele->acompte != 0) {
			
			$totaux['avant_acompte'] = $totaux;
			
			$acompte = $this->modele->acompte / 100;
			
			// on doit calculer le % d'acompte
			if($this->modele->acompte_type == 2) {
				
				$acompte = $this->modele->acompte / $totaux['ttc'];
			}
			// on doit calculer le % d'acompte
			if($this->modele->acompte_type == 3) {

				$acompte = $this->modele->acompte / $totaux['ht'];
			}

			// on doit appliquer le % d'acompte de partout
			$totaux['ht'] = round($totaux['ht'] * $acompte,$nombre_de_chiffres_decimaux_sur_les_tarif);
			$totaux['ht_avec_eco_contribution'] = round($totaux['ht_avec_eco_contribution'] * $acompte,$nombre_de_chiffres_decimaux_sur_les_tarif);
			$totaux['ttc'] = round($totaux['ttc'] * $acompte,$nombre_de_chiffres_decimaux_sur_les_tarif);
			$totaux['ttc_sans_eco_contribution'] = round($totaux['ttc_sans_eco_contribution'] * $acompte,$nombre_de_chiffres_decimaux_sur_les_tarif);
			$totaux['tva'] = round($totaux['tva'] * $acompte,$nombre_de_chiffres_decimaux_sur_les_tarif);

			foreach($totaux['par_tva'] as $tva => $totaux_par_tva) {

				$totaux_par_tva['ht'] = round($totaux_par_tva['ht'] * $acompte,$nombre_de_chiffres_decimaux_sur_les_tarif);
				$totaux_par_tva['ttc'] = round($totaux_par_tva['ttc'] * $acompte,$nombre_de_chiffres_decimaux_sur_les_tarif);
				$totaux_par_tva['tva'] = round($totaux_par_tva['tva'] * $acompte,$nombre_de_chiffres_decimaux_sur_les_tarif);
				$totaux_par_tva['tva_sans_eco_contribution'] = round($totaux_par_tva['tva_sans_eco_contribution'] * $acompte,$nombre_de_chiffres_decimaux_sur_les_tarif);

				$totaux['par_tva'][$tva] = $totaux_par_tva;
			}

			foreach($totaux['par_ligne'] as $ligne => $total) {

                if(is_numeric($total))
				    $totaux['par_ligne'][$ligne] = round($total * $acompte,$nombre_de_chiffres_decimaux_sur_les_tarif);
			}
			
			foreach($totaux['par_ligne_ttc'] as $ligne => $total) {

                if(is_numeric($total))
				    $totaux['par_ligne_ttc'][$ligne] = round($total * $acompte,$nombre_de_chiffres_decimaux_sur_les_tarif);
			}
			
			$totaux['avant_remise']['ht'] = round($totaux['avant_remise']['ht'] * $acompte,$nombre_de_chiffres_decimaux_sur_les_tarif);
			$totaux['avant_remise']['ht_avec_eco_contribution'] = round($totaux['avant_remise']['ht_avec_eco_contribution'] * $acompte,$nombre_de_chiffres_decimaux_sur_les_tarif);
			$totaux['avant_remise']['ttc'] = round($totaux['avant_remise']['ttc'] * $acompte,$nombre_de_chiffres_decimaux_sur_les_tarif);
			$totaux['avant_remise']['ttc_sans_eco_contribution'] = round($totaux['avant_remise']['ttc_sans_eco_contribution'] * $acompte,$nombre_de_chiffres_decimaux_sur_les_tarif);
			$totaux['avant_remise']['tva'] = round($totaux['avant_remise']['tva'] * $acompte,$nombre_de_chiffres_decimaux_sur_les_tarif);
			
			foreach($totaux['avant_remise']['par_tva'] as $tva => $totaux_par_tva) {
				
				$totaux_par_tva['ht'] = round($totaux_par_tva['ht'] * $acompte,$nombre_de_chiffres_decimaux_sur_les_tarif);
				$totaux_par_tva['ttc'] = round($totaux_par_tva['ttc'] * $acompte,$nombre_de_chiffres_decimaux_sur_les_tarif);
				$totaux_par_tva['tva'] = round($totaux_par_tva['tva'] * $acompte,$nombre_de_chiffres_decimaux_sur_les_tarif);
                $totaux_par_tva['tva_sans_eco_contribution'] = round($totaux_par_tva['tva_sans_eco_contribution'] * $acompte,$nombre_de_chiffres_decimaux_sur_les_tarif);

				$totaux['avant_remise']['par_tva'][$tva] = $totaux_par_tva;
			}
			
			foreach($totaux['avant_remise']['par_ligne'] as $ligne => $total) {

                if(is_numeric($total))
				    $totaux['avant_remise']['par_ligne'][$ligne] = round($total * $acompte,$nombre_de_chiffres_decimaux_sur_les_tarif);
			}
			
			foreach($totaux['avant_remise']['par_ligne_ttc'] as $ligne => $total) {

                if(is_numeric($total))
				    $totaux['avant_remise']['par_ligne_ttc'][$ligne] = round($total * $acompte,$nombre_de_chiffres_decimaux_sur_les_tarif);
			}

		}

		return $totaux;
	}

    protected function applique_remise_pour_lignes_sur_totaux($articles, &$totaux, $methode) {

        $index_derniere_remise = -1;

        $nombre_de_chiffres_decimaux_sur_les_tarif = fonctionnalite('nombre_de_chiffres_decimaux_sur_les_tarif');

        // On parcoure tous les articles et on récupére les remises
        foreach ($articles as $index_ligne_remise => $article) {

            if (isset($article['type_ligne']) && $article['type_ligne'] == "remise") {

                if (empty($article['type_remise']) || empty($article['remise'])){
                    $index_derniere_remise = $index_ligne_remise;
                    continue;
                }
                    
                $remise_en_pourcentage = null;

                $total_ligne = array_sum(array_filter($totaux['par_ligne'], function($key) use ($index_derniere_remise, $index_ligne_remise,$articles) {
                        return $key > $index_derniere_remise && $key < $index_ligne_remise && !isset($articles[$key]['type_ligne']);
                    }, ARRAY_FILTER_USE_KEY));

                if($article['type_remise'] == 1){

                    if($total_ligne > 0)
                        $remise_en_pourcentage = $article['remise'] * 100 / $total_ligne;
                }
                else
                    $remise_en_pourcentage = $article['remise'];

                $totaux['par_ligne'][$index_ligne_remise] = round($total_ligne * ( 1 - $remise_en_pourcentage / 100),$nombre_de_chiffres_decimaux_sur_les_tarif);

                if($remise_en_pourcentage === null){
                    $index_derniere_remise = $index_ligne_remise;
                    continue;
                }

                $par_ligne = array_map(function($key) use ($index_derniere_remise, $index_ligne_remise, $totaux, $remise_en_pourcentage, $nombre_de_chiffres_decimaux_sur_les_tarif, $articles) {

                    $valeur = $totaux['par_ligne'][$key];

                    if($key > $index_derniere_remise && $key < $index_ligne_remise && !isset($articles[$key]['type_ligne']))
                        return round($valeur * (100 - $remise_en_pourcentage) / 100,$nombre_de_chiffres_decimaux_sur_les_tarif);

                    return $valeur;
                }, array_keys($totaux['par_ligne']));

                $par_ligne_ttc = array_map(function($key) use ($index_derniere_remise, $index_ligne_remise, &$totaux, $remise_en_pourcentage, $nombre_de_chiffres_decimaux_sur_les_tarif, $articles, $par_ligne, $methode) {

                    $valeur = $totaux['par_ligne_ttc'][$key];

                    if($key > $index_derniere_remise && $key < $index_ligne_remise && !isset($articles[$key]['type_ligne'])){

                        $tva = $articles[$key]['tva'] ?? 0;
                        $valeur_tva = $totaux['par_ligne'][$key] * $tva / 100;
                        $valeur_tva_remise = $par_ligne[$key] * $tva / 100;
                        $totaux['par_tva'][$tva][$methode] -= ($totaux['par_ligne'][$key] - $par_ligne[$key]);

                        return round($valeur - ($totaux['par_ligne'][$key] - $par_ligne[$key]) - ($valeur_tva - $valeur_tva_remise),$nombre_de_chiffres_decimaux_sur_les_tarif);
                    }

                    return $valeur;
                }, array_keys($totaux['par_ligne_ttc']));

                $totaux['par_ligne'] = $par_ligne;
                $totaux['par_ligne_ttc'] = $par_ligne_ttc;
 
                $index_derniere_remise = $index_ligne_remise;
            }

        }
    }

    public function calcul_eco_contribution($article,$type,$modeles_articles, $type_document = null){

        $article = (object) $article;

        if(isset($type_document) && $type_document == 'acompte_vente')
            return 0;

        if(!isset($article->application_eco_contribution))
            $article->application_eco_contribution = null;

        if(!isset($article->tarif_eco_contribution))
            $article->tarif_eco_contribution = 0;

        if(!isset($article->quantite_unite_eco_contribution))
            $article->quantite_unite_eco_contribution = 1;

        $article_id = !empty($article->article_enfant_id) ? $article->article_enfant_id : $article->article_id;

        $modele_article = $modeles_articles[$article_id] ?? null;

        if(empty($modele_article->type_article) || $modele_article->type_article != 1){

            $application = $article->application_eco_contribution == 1 ? 1 : 0;

            if($application != $type)
                return 0;

            $valeur = $article->tarif_eco_contribution * $article->quantite_unite_eco_contribution;

            if($valeur > 0 && $valeur < 0.01)
                $valeur = 0.01;

            return round($valeur,2) * floatval($article->quantite);
        }

        $montant = 0;

        if(!empty($article->nomenclature)) {

            if(!is_array($article->nomenclature))
                $article->nomenclature = json_decode($article->nomenclature,true);

            foreach($article->nomenclature as $nomenclature) {

                $montant += $this->calcul_eco_contribution($nomenclature,$type,$modeles_articles);
            }
        }

        return $montant * $article->quantite;
    }

    public function calcule_marges_a_la_ligne($totaux, $articles) {
        
        $articles = array_values(gettype($articles) === 'array' ? $articles : $articles->toArray());

        foreach($articles as $index => $article){

            if(isset($article['type_ligne'])){

                $totaux['par_ligne_marge_brute_montant'][] = '';
                $totaux['par_ligne_marge_brute_pourcentage'][] = '';
                $totaux['par_ligne_marge_nette_montant'][] = '';
                $totaux['par_ligne_marge_nette_pourcentage'][] = '';
                continue;
            }

            if(empty($article['tarif']) || $article['tarif'] == 'NaN' || $article['tarif'] == 'null')
                $article['tarif'] = 0;
            
            if(empty($article['prix_achat']) || $article['prix_achat'] == 'NaN' || $article['prix_achat'] == 'null')
                $article['prix_achat'] = 0;

            // $totaux['par_ligne_eco_contribution_inclus'][$index] prend déjà en compte la quantité de la ligne
            $eco_contribution_inclus = $totaux['par_ligne_eco_contribution_inclus'][$index];
            
            $marge_brute = $article['quantite'] === 0 ? 0 : (($article['tarif'] - $article['prix_achat']) * $article['quantite']) - $eco_contribution_inclus;
            $marge_nette = $article['quantite'] === 0 ? 0 : $totaux['par_ligne'][$index] - $eco_contribution_inclus - ($article['prix_achat'] * $article['quantite']);

            if(fonctionnalite('type_calcul_du_pourcentage_marge') == 'prix_de_vente'){

                $marge_brute_pourcentage = $article['tarif'] == 0 || $article['quantite'] == 0 ? null : round($marge_brute / (($article['tarif'] * $article['quantite']) - $eco_contribution_inclus) * 100, 2);
                $marge_pourcentage = $totaux['par_ligne'][$index] == 0 ? null : round($marge_nette / ($totaux['par_ligne'][$index] - $eco_contribution_inclus) * 100, 2);
            }
            else {

                $marge_brute_pourcentage = $article['prix_achat'] == 0 || $article['quantite'] == 0 ? null : round($marge_brute / ($article['prix_achat'] * $article['quantite']) * 100, 2);
                $marge_pourcentage = $article['prix_achat'] == 0 || $article['quantite'] == 0 ? null : round($marge_nette / ($article['prix_achat'] * $article['quantite']) * 100, 2);
            }

            $totaux['par_ligne_marge_brute_montant'][] = $marge_brute;
            $totaux['par_ligne_marge_brute_pourcentage'][] = $marge_brute_pourcentage;
            $totaux['par_ligne_marge_nette_montant'][] = $marge_nette;
            $totaux['par_ligne_marge_nette_pourcentage'][] = $marge_pourcentage;
        }

        return $totaux;
    }
}

