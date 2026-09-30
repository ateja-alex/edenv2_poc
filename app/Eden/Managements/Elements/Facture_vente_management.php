<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Exceptions\Eden_exception;
use App\Eden\Models\Facture_vente_ligne;
use App\Eden\Variables;
use DB;

class Facture_vente_management extends Facture_management {
	
	use Facturation_electronique_document_trait;
	
	/**
	 *
	 * Retourne une instance du modèle pour gérer les lignes des documents
	 * Nous sommes obligés de passer par ce type de méthode car la gestion s'effectue par une classe commune
	 * (Document_management) qui est utilisée pour les factures, les devis... ces éléments ayant le même type de comportement
	 *
	 */
	public function modele_lignes() {

		return new Facture_vente_ligne;
	}

	/**
	 *
	 * On remplit des données par défaut pour la transformation
	 *
	 * Cette méthode est surchargée dans les héritiers de cette classe (Facture_vente_management, etc)
	 *
	 */
	public function donnees_avant_transformation($management_element_origine) {

		// la date de règlement
		$this->modele->date_de_reglement = date('Y-m-d');

		parent::donnees_avant_transformation($management_element_origine);
	}

	/**
	 *
	 * On va chercher la situation géographique du client
	 *
	public function compta_situation_geographique() {

		return management('client', $this->modele->client_id)->situation_geographique_comptable();
	}
	 */

	/**
	 *
	 * Retourne le bon code à utiliser pour les tiers
	 *
	 */
	public function compta_compte_tiers($article) {

		// on regarde si le client a un compte comptable spécifique
		if(!empty($this->modele->client_id_tiers_payeur))
			$client = modele('client', $this->modele->client_id_tiers_payeur);
		else
			$client = modele('client', $this->modele->client_id);

		if(!empty($client->forcer_compte_comptable))
			return $client->forcer_compte_comptable;

		return fonctionnalite('compta_compte_general_clients');
	}

	/**
	 *
	 * On va chercher la situation géographique du client
	 *
	 */
	public function compta_compte_auxiliaire() {
		
		if(!empty($this->modele->client_id_tiers_payeur))
			return management('client', $this->modele->client_id_tiers_payeur)->compte_auxiliaire();
		else
			return management('client', $this->modele->client_id)->compte_auxiliaire();
	}

	/**
	 *
	 * Vérifie si une facture peut être modifiée (même si elle est validée) en fonction de sa date
	 *
	 * Il y a certains champs qui sont modifiables quoi qu'il en soit (validée ou pas),
	 * et pour certains clients, il faut pouvoir limiter dans le temps les modifications possible de ces factures
	 *
	 * Cette méthode retourne true si la modification est possible, un message d'erreur (string) sinon
	 *
	 */
	protected function verifie_modification_possible_suivant_date_facture($modifications = array()) {

		// la fonctionnalité n'est pas bloquée, dont modifiable quoi qu'il en soit
		if(fonctionnalite('autorise_a_modifier_factures_de_plus_de_x_jours') !== true)
			return true;

		// il faut que la facture existe
		if(empty($this->modele))
			return true;

		// et qu'elle soit validée
		if(empty($this->modele->valide))
			return true;

		// il faut que la facture ait plus de 30 jours
		if($this->modele->date >= date('Y-m-d', strtotime("now -30 days")))
			return true;

		// il faut que l'utilisateur soit connecté (au cas ou, cas spécifiques pour certains projets, via ecommerce ou taches cron par exemple)
		if(empty(moi()))
			return true;

		// et enfin, il faut que l'utilisateur ne soit pas autorisé à faire cela
		if(moi()->autorise_a_modifier_factures_de_plus_de_x_jours == 1)
			return true;

		// cas spécifique pour le commentaire recouvrement, toujours modifiable
		if(count($modifications) == 1 && key($modifications) == 'commentaires_recouvrement')
			return true;

		return traduction('messages.php.facture.modification_30_jours');
	}

	/**
	 *
	 * On vérifie si l'utilisateur a le droit de modifier une facture de plus de 30 jours
	 *
	 */
	public function enregistre($modifications = array(), $modele = false) {

		$retour = $this->verifie_modification_possible_suivant_date_facture($modifications);

		if($retour !== true)
			return $retour;

		if($this->verifie_cloture_comptable($modifications) === false)
			return traduction('messages.php.document.modification_passe',null,[fonctionnalite('cloture_comptable_mensuelle_le')]);

		// ok on laisse le standard faire
		return parent::enregistre($modifications, $modele);
	}

	/**
     *
     * On gère la suppression par avoir
     *
     */
    public function supprime($modele = false) {

		$retour = $this->verifie_modification_possible_suivant_date_facture();

		if($retour !== true)
			return $retour;

		$modification_possible = $this->verifie_cloture_comptable();

		if($modification_possible === false)
			return traduction('messages.php.document.modification_passe',null,[fonctionnalite('cloture_comptable_mensuelle_le')]);

        //empecher la suppression d'une facture validée si la fonctionnalité est activée
        if(fonctionnalite('gescom_suppression_facture_valide') == 'empecher_suppression'  && $this->modele->valide == 1)
            return traduction('messages.php.facture.suppression_impossible_facture_valide');
		    
        // si la fonctionnalité n'est pas activée, on laisse la suppression standard
        if(fonctionnalite('gescom_suppression_facture_valide') == 'supprimer')
            return parent::supprime($modele);
		

		if($modele === false && !empty($this->modele))
			$modele = $this->modele;

		// la facture est déjà annulée par un avoir
		if($this->modele->annulee_par_avoir == 1)
			return traduction('messages.php.facture.annulation_impossible_avoir');

		// la facture n'est pas validée, on laisse le standard
		if($this->modele->valide != 1)
			return parent::supprime($modele);

        if(isset($this->test_suppression) && $this->test_suppression === true)
            return 'test_ok' ;

		// Creation de l'avoir
		$modifications = array(

			'commentaires' => 'Facture à l\'origine de cet avoir: '. $modele->reference_document,
			'facture_id_source' => $modele->id,
		);

		$retour = $this->transformer_document('avoir_vente', $modifications);

		// Modification commentaire de l'avoir
		if($retour[0] !== true)
			return $retour[0];

		$avoir = $retour[1];

		// Validation de l'avoir
		$avoir->valide();

		// Changement de statut de la facture
		$this->methodes_post_annulation_par_avoir($avoir);
		
		// on recalcule les soldes sur les documents
		$this->maj_total_document();
		$avoir->maj_total_document(true);

		// on change les status que s'il n'y a pas de paiements sur la facture
		if(round($this->modele->solde_document_ttc, 2) == round($this->modele->montant_document_ttc, 2)) {
			
			$this->enregistre_comme_regle();

			$avoir->enregistre_comme_regle();
		}

		// Recrédit des crédits utilisés
		$this->recredite_credits_utilises();

		// on enregistre qu'elle a été supprimée
		$this->enregistre(array('annulee_par_avoir' => 1));

		// on logue la suppression par avoir
		$this->enregistrer_log(Variables::$types_logs['annulation_par_avoir']);

		return true;
    }

	/**
	 *
	 * Mise à jour du total du document
	 *
	 */
	public function maj_total_document($enregistrement_classique = false) {
		
		$modifications = parent::maj_total_document($enregistrement_classique);
		
		if(!$this->existe())
			return $modifications;
		
		$decimales = fonctionnalite('nombre_de_chiffres_decimaux_sur_les_tarif');
		
		// on va voir quels sont les avoirs liés à cette facture
		$avoirs = modele('avoir_vente')->where('facture_id_source', $this->modele->id)->get();
		
		$solde_facture = round($this->modele->solde_document_ttc, $decimales);
		
		foreach($avoirs as $avoir) {
			
			$avoir_management = management('avoir_vente', $avoir->id, $avoir);
			
			$totaux_avoir = $avoir_management->calcule_total_document();
			
			if($solde_facture >= round($totaux_avoir['solde_ttc'], $decimales)) {
				
				// on met à jour le solde de la facture
				$modification_facture = array('solde_document_ttc' => round($solde_facture - $totaux_avoir['solde_ttc'], $decimales));
				
				$this->enregistre_modele($modification_facture);
				
				// on met à jour le solde de l'avoir
				$modification_avoir = array('solde_document_ttc' => 0, 'regle' => 1);
				
				$avoir_management->enregistre_modele($modification_avoir);
				
				$solde_facture -= $totaux_avoir['solde_ttc'];
			}
			elseif($solde_facture > 0 && $solde_facture < round($totaux_avoir['solde_ttc'])) {
				
				
				// on met à jour le solde de l'avoir
				$modification_avoir = array('solde_document_ttc' => round($totaux_avoir['solde_ttc'] - $solde_facture, $decimales));
				
				$avoir_management->enregistre_modele($modification_avoir);
				
				// on met à jour le solde de la facture
				$modification_facture = array('solde_document_ttc' => 0);
				
				$this->enregistre_modele($modification_facture);
				
				$solde_facture = 0;
			}
			else {
				
				if(round($totaux_avoir['solde_ttc'], $decimales) != $avoir->solde_document_ttc) {
					
					$modification_avoir = array('solde_document_ttc' => round($totaux_avoir['solde_ttc'], $decimales));
				
					$avoir_management->enregistre_modele($modification_avoir);
				}
			}
		}
		
		return $modifications;
	}

	/**
	 *
	 * On regarde si un acompte est lié à la facture, si c'est le cas on retire le lien
	 *
	 */
	protected function methodes_post_annulation_par_avoir($avoir) {

		parent::methodes_post_annulation_par_avoir($avoir);

		$this->gere_liaison_acompte_apres_annulation_par_avoir();
	}

	/**
	 *
	 * On gère la suppression du lien entre l'acompte et la facture quand on crée un avoir pour la facture
	 *
	 */
	public function gere_liaison_acompte_apres_annulation_par_avoir() {

		// pas d'acompte
		if(modele('lien_acompte_facture')->where('facture_vente_id', $this->modele->id)->first() === null)
			return true;

		$liens = modele('lien_acompte_facture')->where('facture_vente_id', $this->modele->id)->get();

		foreach($liens as $lien) {

			$lien->delete();
		}

		return true;
	}

	/**
	 *
	 * Quand on annule une facture par avoir, par défaut on prend la date du jour
	 *
	 */
	protected function date_pour_annulation_par_avoir() {

		return date("Y-m-d");
	}

	/**
	 *
	 * Crée un mouvement de stock
	 *
	 */
    public function creer_mouvement_stock($parametre = "document") {

		return parent::creer_mouvement_stock($this->_type_element.'_livree');
	}


	/**
	 *
	 * Retourne la date à prendre en compte pour la gestion des stocks
	 *
	 */
	public function date_pour_mouvement_de_stock() {

		return $this->modele->date;
	}

	/**
	 *
	 * Affiche une liste de tags pour les listes
	 *
	 */
	public function tags_pour_liste($modele) {

		$tags = array();

		if($modele->valide != 1) {

			$tags[] = '<span class="badge badge-default">'.traduction('interface.listes.tags_pour_liste.pro_forma').'</span>';
		}
		else {

			if($modele->regle == 1) {

				$tags[] = '<span class="badge badge-success">'.traduction('interface.listes.tags_pour_liste.reglee').'</span>';
			}
			else {

				$tags[] = '<span class="badge badge-danger">'.traduction('interface.listes.tags_pour_liste.non_reglee').'</span>';
			}
		}

		if($modele->annulee_par_avoir == 1 && $modele->annulee_v1 == 1) {

			$tags[] = '<span class="badge badge-warning">'.traduction('interface.listes.tags_pour_liste.annulee').' V1</span>';

		}

		if($modele->annulee_par_avoir == 1) {

			$tags[] = '<span class="badge badge-warning">'.traduction('interface.listes.tags_pour_liste.annulee_par_avoir').'</span>';
		}

		if($modele->comptabilise == 1) {

			$tags[] = '<span class="badge badge-warning" style="background: #249e8e">'.traduction('interface.listes.tags_pour_liste.comptabilisee').'</span>';
		}
		
		if($modele->avoir_partiel == 1) {

			$tags[] = '<span class="badge badge-warning">'.traduction('interface.listes.tags_pour_liste.avoir_partiel').'</span>';
		}

		if($modele->avoir_total == 1) {

			$tags[] = '<span class="badge badge-warning">'.traduction('interface.listes.tags_pour_liste.avoir_total').'</span>';
		}

		$tag_facturation_electronique = $this->tag_facturation_electronique($modele);

		if(!empty($tag_facturation_electronique))
			$tags[] = $tag_facturation_electronique;

		return implode('<br/>', $tags);
	}

	/**
	 *
	 * On ajoute les liens pour l'interface de paiement Payline et Stripe
	 * On enregistre comme "transformées" les devis.
	 *
	 */
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {

		parent::methodes_post_modification($modele, $modele_avant, $modifications);

		// on crée le liens vers les interfaces de paiement
		$this->cree_lien_interface_paiement();
	}

	/**
	 *
	 * Mise à jour des statuts des autres documents liés
	 *
	 */
	protected function methodes_post_modification_document($modele, $modele_avant, $modifications) {

		parent::methodes_post_modification_document($modele, $modele_avant, $modifications);

		// on met à jour les autres documents (le champ transforme_en_facture)
		$this->mise_a_jour_autres_documents();

        $documents_sources = modele('facture_vente_lignes')->select('id_element_source', 'type_element_source')->where('document_id', $this->modele->id)->whereIn('type_element_source',['commande_vente','bl_vente'])->groupBy('id_element_source')->get();

        foreach($documents_sources as $document_source) {

            management($document_source['type_element_source'], $document_source['id_element_source'])->mise_a_jour_statut_facture();
        }

		// on met à jour le statut
		$this->mise_a_jour_champ_statut();

        $this->recalcul_echeances();
	}

	/**
	 *
	 *
	 * Lors de la création d'une facture, on regarde si on doit automatiquement ajouter une ligne d'article pour les acomptes liés au même groupe de document
	 *
	 */
	public function ajoute_article_acompte($management_element_origine, &$articles_du_document) {

		// la fonctionnalité n'est pas activée
		if(empty(fonctionnalite('compta_article_id_pour_acompte')))
			return;

		$article_id_pour_acompte = fonctionnalite('compta_article_id_pour_acompte');

		// est ce qu'il y a un acompte lié au groupe de documents ?
		$acomptes = $management_element_origine->documents_lies('acompte_vente');

		if(empty($acomptes))
			return;

		$acomptes_a_ajouter = array();

		foreach($acomptes as $acompte) {

			// on regarde si l'acompte est déjà lié à une facture
			if(modele('lien_acompte_facture')->where('acompte_vente_id', $acompte['management']->modele->id)->first() !== null)
				continue;

			// l'acompte est annulé par avoir
			if(!empty($acompte['management']->modele->annulee_par_avoir))
				continue;

			// il n'est pas lié à une facture, on regarde pour le lier
			$acompte_a_ajouter = array();
            $total_acompte = 0;

			// on va chercher les articles de l'acompte
			$articles_de_lacompte = $acompte['management']->articles();

			foreach($articles_de_lacompte as $article) {

				$total_ht_ligne = $article->tarif * $article->quantite * (100 - $article->remise) / 100 * $article->remise_globale_ligne;

                $total_acompte += $total_ht_ligne;

                if($acompte['management']->modele->acompte_type === 1)
                    $total_ht_ligne *= $acompte['management']->modele->acompte / 100;

				if(!empty($total_ht_ligne)) {

					if(empty($article->tva))
						$article->tva = 0;

					if(!isset($acompte_a_ajouter[$article->tva]))
						$acompte_a_ajouter[$article->tva] = 0;

					$acompte_a_ajouter[$article->tva] += $total_ht_ligne;
				}
			}

            if ($acompte['management']->modele->acompte_type === 2){
                $total_acompte = 0;
                
                foreach($acompte_a_ajouter as $tva => $total) {
                    $total_acompte += $total * (1 + $tva / 100);
                }
            }

            if(in_array($acompte['management']->modele->acompte_type, [2, 3])) {
                $montant_acompte = $acompte['management']->modele->acompte / $total_acompte;

                foreach($acompte_a_ajouter as $tva => $total) {
                    if ($acompte['management']->modele->acompte_type === 2)
                        $acompte_a_ajouter[$tva] *= $montant_acompte;
                }
            }

			$acomptes_a_ajouter[$acompte['management']->modele->id] = $acompte_a_ajouter;
		}
		
		if(is_array($articles_du_document))
			$max_ligne_id = collect($articles_du_document)->max('ligne');
		else
			$max_ligne_id = $articles_du_document->max('ligne');

		foreach($acomptes_a_ajouter as $id_acompte_vente => $total_par_tva) {

			$acompte_vente = modele('acompte_vente', $id_acompte_vente);

			foreach($total_par_tva as $tva => $montant_ht) {

				$max_ligne_id++;

				$nouvelle_ligne = array(

					'article_id' => $article_id_pour_acompte,
					'ligne' => $max_ligne_id,
					'quantite' => 1,
					'designation' => "Acompte ".$acompte_vente->reference_document." du ".formate_date('d/m/Y', $acompte_vente->date)." (TVA $tva"."%)",
					'tarif' => $montant_ht * -1,
					'tva' => $tva,
					'remise' => 0,
					'type_element_source' => 'acompte_vente',
					'achats' => [],
					'nomenclature' => [],
					'numeros_de_lot' => [],
					'id_element_source' => $id_acompte_vente,
					'modele' => modele('article', $article_id_pour_acompte),
				);

				$articles_du_document[] = $nouvelle_ligne;
			}

			/*
			$lien_acompte_facture = management('lien_acompte_facture');

			$info = array(

				'acompte_vente_id' => $id_acompte_vente,
				'facture_vente_id' => $this->modele->id,
			);

			$lien_acompte_facture->enregistre($info);
			*/
		}


	}

	/**
	 * 
	 * On met à jour automatiquement le champ statut
	 *
	 */
	public function mise_a_jour_champ_statut() {

		// on regarde si la facture a été envoyée par mail
		if($this->modele->envoye_par_mail == 1) {

			// on est déjà sur un statut après "envoyé"
			if($this->modele->statut > 5) {

				// rien à faire donc
				return;
			}

			$this->enregistre_modele(array('statut' => 10));

			return;
		}

		// le statut par défaut
		if(empty($this->modele->statut)) {

			$this->enregistre_modele(array('statut' => 5));
		}
	}

	/**
	 *
	 * On met à jour automatiquement les autres documents
	 *
	 */
	public function mise_a_jour_autres_documents() {

		// On enregistre comme "transformées" les commandes.
		$documents_lies = $this->documents_lies('commande_vente');

		foreach($documents_lies as $commande) {

			$commande['management']->enregistre_modele(['transforme_en_facture' => 1]);
		}

		// On enregistre comme "transformées" les devis.
		$documents_lies = $this->documents_lies('devis_vente');

		foreach($documents_lies as $devis) {

			$devis['management']->enregistre_modele(['transforme_en_facture' => 1]);
		}

		// On enregistre comme "transformées" les devis.
		/*
		$documents_lies = $this->documents_lies('bl_vente');

		foreach($documents_lies as $bl) {

			$bl['management']->enregistre_modele(['transforme_en_facture' => 1, 'statut' => 40]);
		}
		*/
	}

    /**
     *
     * On met à jour automatiquement les autres documents
     *
     */
    public function mise_a_jour_autres_documents_post_suppression() {

        // On enregistre comme "transformées" les commandes.
        $documents_lies = $this->documents_lies('commande_vente');

        foreach($documents_lies as $commande) {

            $commande['management']->enregistre_modele(['transforme_en_facture' => 0]);
        }

        // On enregistre comme "transformées" les devis.
        $documents_lies = $this->documents_lies('devis_vente');

        foreach($documents_lies as $devis) {

            $devis['management']->enregistre_modele(['transforme_en_facture' => 0]);
        }

        // On enregistre comme "transformées" les devis.
        $documents_lies = $this->documents_lies('bl_vente');

        foreach($documents_lies as $bl) {

            $bl['management']->enregistre_modele(['transforme_en_facture' => 0]);
        }
    }

	/**
	 *
	 * Crée un lien vers l'ERP pour régler la facture via Payline, Stripe, Etc
	 *
	 */
	public function cree_lien_interface_paiement() {

		if(!empty($this->modele->lien_interface_paiement_payline))
			return;

		$lien_interface_paiement_payline = route('paiement_facture', ['payline', $this->modele->id, md5('eden' . $this->modele->id)]);
		$lien_interface_paiement_stripe = route('paiement_facture', ['stripe', $this->modele->id, md5('eden' . $this->modele->id)]);

		$this->enregistre_modele(array('lien_interface_paiement_payline' => $lien_interface_paiement_payline, 'lien_interface_paiement_stripe' => $lien_interface_paiement_stripe, ));
	}

	/**
	 *
	 * Retourne le sujet du l'email de relecture
	 *
	 */
	public function retourne_sujet_du_mail_pour_relecture() {

		return moi()->prenom ." vient d'éditer une facture";
	}

	/**
	 *
	 * Retouche les informations de la facture générée lors de la transforamtion d'un BL ou d'une commande en facture
	 *
	 * Cette méthode, à surcharger, sert notamment à ajouter les champs obligatoires
	 *
	 */
	public function retouche_infos_pour_facturation_depuis_autre_document($infos) {

		return $infos;
	}

	/**
	 *
	 * On ajoute les relances
	 *
	 */
	protected function methodes_post_validation_document($modele) {

		$this->genere_dates_relances();

		parent::methodes_post_validation_document($modele);

		// on regarde s'il y a une commande liée à la facture, auquel cas on la passe en facturée
		$this->passe_commande_en_facturee();
		
		$comptabiliser_automatiquement = fonctionnalite('comptabiliser_automatiquement');
			
		if(!empty($comptabiliser_automatiquement['facture_vente']))
			return $this->comptabilise();

        return true;
	}

    /**
     *
     * Trigger post suppression
     *
     * @return void
     *
     */
    protected function methodes_post_suppression($modele) {

        parent::methodes_post_suppression($modele);

        //On supprime les échéances si le document n'est pas validé
        if(!$modele->valide && $this->management_fiche()->presence_module('echeances') === true){

            $echeances_a_supprimer = modele('echeance')->where('type_element', 'facture_vente')->where('element_id', $modele->id)->get();

            foreach ($echeances_a_supprimer as $echeance){
                management('echeance', $echeance->id)->enregistre_modele(['inactif' => 1]);
            }
        }

		// on supprime les liens acomptes / factures
		$liens_acompte_facture = modele('lien_acompte_facture')->where('facture_vente_id', $this->modele->id)->get();

		foreach($liens_acompte_facture as $lien) {

			modele('lien_acompte_facture', $lien->id)->delete();
		}

        $this->mise_a_jour_autres_documents_post_suppression();

    }

	/**
	 *
	 * Si une commande est liée à la facture, il faut que la commande passe en facturée
	 * Pour éviter les erreurs, on passe les commandes en facturées automatiquement que si elles sont d'abord expédiée
	 *
	 */
	public function passe_commande_en_facturee() {

		$commandes = $this->documents_lies('commande_vente');

		foreach($commandes as $commande) {

			$commande_management = $commande['management'];

			if($commande_management->modele->statut < 45)
				continue;

			// ok on a le statut expédié pour la commande, du coup on peut la passer en facturer
			$commande_management->enregistre_modele(array('statut' => 50));
		}
	}

	/**
	 *
	 * Enregistre les dates de relance
	 *
	 */
	protected function genere_dates_relances() {

		// on va chercher le client
		$client_management = management('client', $this->modele->client_id);

		if(empty($client_management->modele->groupe_recouvrement_id)) {

			$groupe_recouvrement = modele('groupe_recouvrement')->where('par_defaut', 1)->first();

			if($groupe_recouvrement === null)
				return;

			// on met à jour le client
			$client_management->enregistre(array('groupe_recouvrement_id' => $groupe_recouvrement->id));
		}
		else {

			// on va chercher le groupe de recouvrement
			$groupe_recouvrement = modele('groupe_recouvrement', $client_management->modele->groupe_recouvrement_id);
		}

		$relances = array();

		for($i=1; $i <=4; $i++) {

			if(!empty($groupe_recouvrement->{'delai_relance_'.$i})) {

				$delai = $groupe_recouvrement->{'delai_relance_'.$i};

				$relances['relance_'.$i] = date('Y-m-d', strtotime($this->modele->date_de_reglement." +$delai days"));
			}

		}

		if(!empty($relances)) {

			$this->enregistre($relances);
		}

	}

	/**
	 *
	 * Retourne le contrat VAD à utiliser (payline) pour cette facture
	 *
	 * Par défaut on retourne le contrat qui est dans la config
	 * Cette méthode est prévue pour être surchargée quand il y a plusieurs contrats VAD par exemple
	 *
	 */
	public function payline_contrat_vad() {

		return config('laravel-payline-sdk.webservices_settings.CONTRACT_NUMBER');
	}

	/**
	 *
	 * Colonne spécifique pour le rapport règlements à recevoir
	 *
	 */
	public function colonne_reglement_a_recevoir($modele) {

		return '<input type="text" class="js_reglement_a_recevoir" document_id="'.$modele->id.'" name="montant_paiement_'.$modele->id.'"/>
				<span class="btn btn-default btn-xs" onClick="$(this).parent().find(\'input\').val('.$modele->solde_document_ttc.')">Tout</span>';
	}

	/**
	 *
	 * Retourne le montant de la tva à 0% pour cette facture
	 *
	 */
	public function montant_tva_0($modele) {

		return montant(DB::table('facture_vente_lignes')->where('document_id', $modele->id)->where('tva', 0)->select(DB::raw("SUM(tarif * quantite * (1 - remise / 100)) as montant_ht"))->first()->montant_ht);
	}

	/**
	 *
	 * Retourne le montant de la tva à 5.5% pour cette facture
	 *
	 */
	public function montant_tva_5_5($modele) {

		return montant(DB::table('facture_vente_lignes')->where('document_id', $modele->id)->where('tva', 5.5)->select(DB::raw("SUM(tarif * quantite * (1 - remise / 100)) as montant_ht"))->first()->montant_ht);
	}

	/**
	 *
	 * Retourne le montant de la tva à 10% pour cette facture
	 *
	 */
	public function montant_tva_10($modele) {

		return montant(DB::table('facture_vente_lignes')->where('document_id', $modele->id)->where('tva', 10)->select(DB::raw("SUM(tarif * quantite * (1 - remise / 100)) as montant_ht"))->first()->montant_ht);
	}

	/**
	 *
	 * Retourne le montant de la tva à 20% pour cette facture
	 *
	 */
	public function montant_tva_20($modele) {

		return montant(DB::table('facture_vente_lignes')->where('document_id', $modele->id)->where('tva', 20)->select(DB::raw("SUM(tarif * quantite * (1 - remise / 100)) as montant_ht"))->first()->montant_ht);
	}

	/**
	 *
	 * On surcharge pour ajouter le calcul de la date de règlement réelle
	 *
	 */
	public function enregistre_comme_regle() {

		parent::enregistre_comme_regle();

		$this->calcule_date_de_reglement_reelle();

	}

	/**
	 *
	 * Calcule date de réglement réelle
	 *
	 */
	public function calcule_date_de_reglement_reelle() {


		if (empty($this->modele->regle))
			return ;

		// Sélection du dernier paiement reçu pour cette facture
		$paiement = modele('paiement')
						->where('type_element', 'facture_vente')
						->orderByDesc('date')
						->where('id_document', $this->modele->id)
						->first();
		if (!$paiement)
			return;

		//On calcule le delai en jours entre cette date et date de facturation
		$delai = (strtotime($paiement->date) - strtotime($this->modele->date) );
		$delai = round($delai / (3600*24));

		// on enregistre la date_de_reglement_reelle et le délai
		$this->enregistre_modele([
					'date_de_reglement_reelle' => $paiement->date,
					'delai_de_reglement' => $delai,
				]);
	}

	/**
	 *
	 * Certains clients souhaitent utiliser la date de facturation pour le module de recouvrement
	 * Il faut donc surcharger cette méthode
	 *
	 */
	public function date_a_utiliser_pour_recouvrement() {

		return 'date_de_reglement';
	}

	/**
	 *
	 * Certains clients veulent des blocages au prélèvement (ne pas prélever en fin de mois, ...)
	 * Il faut donc surcharger cette méthode. Ne pas la modifier en standard !
	 *
	 */
	public function autoriser_prelevement_automatique($modele = null) {

		return true;
	}


	/**
	 *
	 * Contenu de la colonne pour le recouvrement pour enregistrer les relances
	 *
	 */
	public function liste_enregistre_relance($modele, $arguments) {

        // On va chercher si il y a une relance sur ce document, ce type de relance ( null si non )
        $la_relance = modele('relance_recouvrement')->where('type_relance',$arguments['relance_id'])->where('facture_vente_id',$modele->id)->get();

        $texte_de_retour = '<span id_element="'.$modele['id'].'" type="'.$arguments['relance_id'].'" type_element="facture_vente" class="css__lien js_liste_ajouter_relance">'.traduction('interface.listes.ajouter').'</span>
        <span :chargement="chargement_'.$modele['id'].'_'.$arguments['relance_id'].'" class="js_chargement" style="display: none;">
            <img src="'.asset('eden/images/ajax_loader.gif').'" height="30">
        </span>';

        if (!$la_relance->isEmpty()) {

           foreach ($la_relance as $relance) {
           		$texte_de_retour .= '<div style="display: flex;"><span class="mb-1"><span class="fas fa-check" aria-hidden="true" style="color: green;"></span> <span class="badge badge-success">'.date('d/m/Y',strtotime($relance->date)).'</span> <span class="badge badge-danger js_liste_supprimer_relance" id_relance="'.$relance->id.'">X</span> <span class="js_suppression_" style="display: none;"><img src="'.asset('eden/images/ajax_loader.gif').'" height="30"></span></span></div>
            ';
           }

        }

		return $texte_de_retour;
	}

	/**
	 *
	 * Permet de retoucher la liste des actions possibles depuis une liste de facture_vente
	 *
	 */
	public function actions_a_afficher($id_liste) {

		// on recupère les actions principales
		$actions = parent::actions_a_afficher($id_liste);

		unset($actions['supprimer_documents']);

		if(fonctionnalite('gescom_affacturage') === true)
			$actions['affacturage_documents'] = '<span class="dropdown-item" @click="modale_affacturage_documents = true;"><i class="fa fa-fw fa-check"></i><span v-html="$root.traduction(\'interface.listes.affacturage\')"></span></span>';


		$actions['export_sepa'] = '<span class="dropdown-item" @click="modale_export_sepa = true;"><i class="fa fa-fw fa-check"></i><span v-html="$root.traduction(\'interface.listes.export_sepa\')"></span></span>';
        $actions['relance_pdf'] = '<a class="dropdown-item" href="#" data-toggle="modal" data-target="#modal_relance_pdf_'.$id_liste.'"><i class="fa fa-fw fa-file-pdf"></i><span v-html="$root.traduction(\'interface.listes.relance_pdf\')"></span></a>';
		$actions['suppression_facture_vente'] = '<span class="dropdown-item" @click="modale_supprimer_documents = true"><i class="fa fa-fw fa-trash"></i><span v-html="$root.traduction(\'interface.listes.supprimer\')"></span></span>';

		return $actions;
	}

	/**
	 *
	 * Permet de retoucher les données pour une création de facture à l'avancement depuis un projet
	 *
	 * Cette méthode est fait pour surcharger le standard en cas de champ spécifiques obligatoires par exemple
	 *
	 */
	public function retouche_donnes_pour_creation_facture_avancement_depuis_projet($id_projet, $donnees) {

		return $donnees;
	}

	/*protected function utilisateurs_pour_relecture() {

	}*/

    /**
     *
     * Récupère le tableau des articles pour une transformation
     *
     * On retouche le prix des articles de l'avoir en fonction du prix de la facture d'avancement
     *
     */

    public function recupere_articles_pour_transformation($uniquement_non_traites = true, $type_element_destination = false) {

        $articles = parent::recupere_articles_pour_transformation($uniquement_non_traites, $type_element_destination);

        if($this->modele->type_facture !== 1 && $this->modele->type_facture !== 2)
			return $articles;

		if($type_element_destination !== false && $type_element_destination == "avoir_vente")
			return $articles;

        foreach ($articles as $article){

            if($article->avancement_pourcentage === null || $article->avancement_pourcentage === false)
                $avancement_pourcentage = 0;
            else
                $avancement_pourcentage = $article->avancement_pourcentage;

            $article->tarif = $article->tarif * $avancement_pourcentage;

            if(!empty($article->nomenclature)){

                foreach ($article->nomenclature as $nomenclature){

                    $nomenclature->tarif = $nomenclature->tarif * $avancement_pourcentage;

                    if(!empty($nomenclature->nomenclature)){

                        foreach ($nomenclature->nomenclature as $sous_nomenclature){

                            $sous_nomenclature->tarif = $sous_nomenclature->tarif * $avancement_pourcentage;

                        }
                    }

                }
            }

        }

        return $articles;
    }

		/**
		 *
     * Retourne la liste des transformations possibles pour un document (un devis en commande, etc)
     *
     */
    public function transformations_possibles($transformations_possibles = array()) {

        $transformations_possibles['devis_vente'] = route('document.transformer', [$this->_type_element, $this->modele->id, 'devis_vente']);
        $transformations_possibles['bon_retour_vente'] = route('document.transformer', [$this->_type_element, $this->modele->id, 'bon_retour_vente']);

        // Si la facture est annulée par avoir, on empêche la création de nouveau avoir
        if($this->modele->annulee_par_avoir !== 1) {

            $transformations_possibles['avoir_vente'] = route('document.transformer', [$this->_type_element, $this->modele->id, 'avoir_vente']);

        }

        return parent::transformations_possibles($transformations_possibles);

    }
    
    public function affichage_modeles_de_relances(){
        
        $modeles_de_relances = [];

        for($i = 1; $i < 5; $i++){

            if(!empty(fonctionnalite('modele_relance_' . $i)))
                $modeles_de_relances[$i] = traduction('document.actions.transformations_possibles.modele_de_relance_' . $i);

        }
        
        return $modeles_de_relances;

    }

}
