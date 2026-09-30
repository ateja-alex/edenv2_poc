<?php

namespace App\Eden\Managements\Elements;

use DB;

class Projet_management extends Element_management {

	/**
	 *
	 * Comment doit on présenter les résultats de la recherche
	 *
	 */
	public function affichage_pour_recherche() {

		return "nom AS affichage_pour_recherche";
	}


	/**
	 *
	 * Calcul de la marge brutte et nette du projet
	 *
	 * Parametres disponibles :
	 * dates => [date_debut => Y-m-d, date_fin => Y-m-d]
	 *
	 */
	public function calcul_marge($parametres = array()){

		// on calcule le chiffre d'affaires
		$type_element = fonctionnalite('calcul_marges_type_element');

		if(empty($type_element))
			$type_element = 'devis_vente';

		$enregistrement_resultats = true;

		if(!isset($parametres['dates'])) {

			$ca_devis_vente 	= modele('devis_vente')->where('projet_id', $this->modele->id)->where('accepte', 1)->sum('montant_document_ht');
			$ca_commande_vente  = modele('commande_vente')->where('projet_id', $this->modele->id)->where('valide', 1)->sum('montant_document_ht');
			$ca_facture_vente 	= modele('facture_vente')->where('projet_id', $this->modele->id)->where('valide', 1)->sum('montant_document_ht');
			$ca_avoir_vente 	= modele('avoir_vente')->where('projet_id', $this->modele->id)->where('valide', 1)->sum('montant_document_ht');
		}
		else {

			// on n'enregistre pas en BDD les résultats si on a un filtre
			$enregistrement_resultats = false;

			$ca_devis_vente 	= modele('devis_vente')->where('projet_id', $this->modele->id)->where('accepte', 1)->whereBetween('date', array($parametres['dates']['date_debut'], $parametres['dates']['date_fin']))->sum('montant_document_ht');
			$ca_commande_vente  = modele('commande_vente')->where('projet_id', $this->modele->id)->where('valide', 1)->whereBetween('date', array($parametres['dates']['date_debut'], $parametres['dates']['date_fin']))->sum('montant_document_ht');
			$ca_facture_vente 	= modele('facture_vente')->where('projet_id', $this->modele->id)->where('valide', 1)->whereBetween('date', array($parametres['dates']['date_debut'], $parametres['dates']['date_fin']))->sum('montant_document_ht');
			$ca_avoir_vente 	= modele('avoir_vente')->where('projet_id', $this->modele->id)->where('valide', 1)->whereBetween('date', array($parametres['dates']['date_debut'], $parametres['dates']['date_fin']))->sum('montant_document_ht');
		}


		$ca_facture_vente -= $ca_avoir_vente;

		if ($type_element == 'facture_vente') {

			$ca = $ca_facture_vente;
		}
		elseif ($type_element == 'commande_vente') {

			$ca = $ca_commande_vente;
		}
		else {

			$ca = $ca_devis_vente;
		}

		$achats = 0;
		$achats_sur_document = 0;

		// le montant des factures achats
		if(fonctionnalite('calcul_marges_facture_achat')) {

			$achats = modele('facture_achat')->where('projet_id', $this->modele->id)->where('valide', 1)->sum('montant_document_ht');
		}


		// les achats via les BL de vente
		if(fonctionnalite('calcul_marges_achats_via_bl_vente')) {

			$achats = modele('bl_vente')->join('bl_vente_lignes', 'bl_vente.id', 'bl_vente_lignes.document_id')->where('projet_id', $this->modele->id)->where('bl_vente.valide', 1)->sum(DB::raw('quantite * prix_achat'));
		}

		// les achats saisis sur les documents
		if(fonctionnalite('calcul_marges_achats_saisis_sur_documents')) {

			// les devis acceptés du projet
			$devis_acceptes = modele('devis_vente')->where('projet_id', $this->modele->id)->where('accepte', 1)->pluck('id');

			$achats_sur_document = modele('achat_sur_document')
										->where('projet_id', $this->modele->id)
										->where('type_element', 'devis_vente')
										->whereIn('document_id', $devis_acceptes)
										->sum(DB::raw('quantite * tarif * (100 - COALESCE(remise,0) ) / 100'));
		}
		// par défaut on récupère les prix d'achats directement sur le devis
		else {

			// les devis acceptés du projet
			if($type_element == 'devis_vente') {

				$documents_pour_achats = modele($type_element)->where('projet_id', $this->modele->id)->where('accepte', 1)->pluck('id');
			}
			else {

				$documents_pour_achats = modele($type_element)->where('projet_id', $this->modele->id)->pluck('id');
			}

			$achats_sur_document = DB::table($type_element.'_lignes')
									->whereIn('document_id', $documents_pour_achats)
									->sum(DB::raw('quantite * prix_achat * (100 - COALESCE(remise,0) ) / 100'));
		}

		// le cout du temps
		// on va chercher le cout par utilisateur
		$details_cout_par_utilisateur = array();



		if(!isset($parametres['dates'])) {

			$couts_rh = modele('feuille_de_temps')
						->where('type_element', 'projet')
						->where('element_id', $this->modele->id)
						->select(DB::raw('SUM(cout) as total_cout, utilisateur_id'))
						->groupBy('utilisateur_id')
						->get();
		}
		else {

			$couts_rh = modele('feuille_de_temps')
                        ->where('type_element', 'projet')
						->where('element_id', $this->modele->id)
						->whereBetween('date', array($parametres['dates']['date_debut'], $parametres['dates']['date_fin']))
						->select(DB::raw('SUM(cout) as total_cout, utilisateur_id'))
						->groupBy('utilisateur_id')
						->get();
		}

		$total_couts_rh = 0;

		foreach($couts_rh as $cout) {

			$details_cout_par_utilisateur[$cout->utilisateur_id] = $cout->total_cout;
			$total_couts_rh += $cout->total_cout;
		}

		// puis les couts fixes
		$couts_fixe_production = modele('cout_fixe_production')->where('entite_id', $this->modele->entite_id)->orderBy('date_de_prise_en_compte', 'DESC')->get();

		$couts_fixes_totaux = 0;
		$dernier_cout_fixe = false;

		foreach($couts_fixe_production as $cout_fixe_production) {

			if(!isset($parametres['dates'])) {

				if($dernier_cout_fixe === false) {

					$duree_totale = modele('feuille_de_temps')
						->where('type_element', 'projet')
						->where('element_id', $this->modele->id)
						->where('date', '>=', $cout_fixe_production->date_de_prise_en_compte)
						->sum('duree');
				}
				else {

					$duree_totale = modele('feuille_de_temps')
						->where('type_element', 'projet')
						->where('element_id', $this->modele->id)
						->where('date', '>=', $cout_fixe_production->date_de_prise_en_compte)
						->where('date', '<', $dernier_cout_fixe->date_de_prise_en_compte)
						->sum('duree');
				}
			}
			else {

				if($dernier_cout_fixe === false) {

					$duree_totale = modele('feuille_de_temps')
						->where('type_element', 'projet')
						->where('element_id', $this->modele->id)
						->where('date', '>=', $cout_fixe_production->date_de_prise_en_compte)
						->whereBetween('date', array($parametres['dates']['date_debut'], $parametres['dates']['date_fin']))
						->sum('duree');
				}
				else {

					$duree_totale = modele('feuille_de_temps')
						->where('type_element', 'projet')
						->where('element_id', $this->modele->id)
						->where('date', '>=', $cout_fixe_production->date_de_prise_en_compte)
						->where('date', '<', $dernier_cout_fixe->date_de_prise_en_compte)
						->whereBetween('date', array($parametres['dates']['date_debut'], $parametres['dates']['date_fin']))
						->sum('duree');
				}
			}


			$couts_fixes_totaux += $duree_totale * $cout_fixe_production->cout;

			$dernier_cout_fixe = $cout_fixe_production;
		}

		$total_couts_rh += $couts_fixes_totaux;

		$total_achats = $achats + $achats_sur_document;

		// on enregistre la marge
		if($enregistrement_resultats) {

			$this->enregistre_modele(array(
				'marge_nette_devis' => $ca_devis_vente - $total_achats - $total_couts_rh,
				'marge_brute_devis' => $ca_devis_vente - $total_achats,
				'marge_nette_commande' => $ca_commande_vente - $total_achats - $total_couts_rh,
				'marge_brute_commande' => $ca_commande_vente - $total_achats,
				'marge_nette_facture' => $ca_facture_vente - $total_achats - $total_couts_rh,
				'marge_brute_facture' => $ca_facture_vente - $total_achats,
				'marge_nette' => $ca - $total_achats - $total_couts_rh,
				'marge_brute' => $ca - $total_achats,
				'montant_ca' => $ca,
				'cout_rh' => $total_couts_rh,
				'montant_achats' => $total_achats,
			));
		}

		return array(
			'montant_total' => $ca,
			'montant_facture' => 0, // @note frédéric : je ne sais pas ce que c'est
			'couts_rh' => $total_couts_rh,
			'details_cout_par_utilisateur' => $details_cout_par_utilisateur,
			'achats_sur_document' => $achats_sur_document,
			'achats' => $achats,
			'marge_brute' => $ca - $total_achats,
			'marge_net' => $ca - $total_achats - $total_couts_rh,
		);
	}


	/**
	 *
	 * Retourne la marge brute
	 *
	 */
	public function marge_brute($marge) {

		return $marge['marge_brute'];
	}

	/**
	 *
	 * Retourne la marge nette
	 *
	 */
	public function marge_nette($marge) {

		return $marge['marge_net'];
	}

	/**
	 *
	 * Calcule le détail des heures réalisées pour le projet
	 *
	 */
	public function heures_details(){

		$donnees = ['temps_realise' => 0, 'temps_par_utilisateur' => array()];

		$feuilles_de_temps = modele('feuille_de_temps')
            ->where('type_element', 'projet')
            ->where('element_id', $this->modele->id)->get();

		if($feuilles_de_temps->count() > 0) {

			$donnees['temps_realise'] = $feuilles_de_temps->sum('duree');

			$utilisateurs = $feuilles_de_temps->pluck('utilisateur_id')->unique();

			foreach($utilisateurs as $utilisateur_id) {

				$donnees['temps_par_utilisateur'][$utilisateur_id] = $feuilles_de_temps->where('utilisateur_id', $utilisateur_id)->sum('duree');
			}
		}

		return $donnees;
	}

	/**
	*
	* Trigger post création ou modification
	*
	* @note pour les documents de gestion commerciale il faut appeler methodes_post_modification_document()
	* Cette méthode est appelée après l'enregistrement des articles, du pdf et de la référence document
	*
	* @return void
	*
	*/
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {

		parent::methodes_post_modification($modele, $modele_avant, $modifications);

		// on met à jour la valeur pondérée
        if(!empty($this->modele->valeur) && !empty($this->modele->probabilite)){

            $valeur_ponderee = $this->modele->valeur * $this->modele->probabilite / 100;
            $this->enregistre_modele(array('valeur_ponderee' => $valeur_ponderee));
        }

        // on crée un échange automatiquement
		if(empty($modele_avant->id) && fonctionnalite('alimentation_timeline_creation_projet'))
			service('alimentation_timeline')->alimentation_timeline('creation_'.$this->_type_element, 'client', $this->modele->client_id, traduction('module_sur_fiche.client.timeline.nouveau_projet') . " : #lien/" . $this->_type_element . "/" . $this->modele->id . "#");
	}

	/*
	 *
	 * Création du numéro de projet
	 *
	 */
	public function numero_de_projet() {

		if(!empty($this->modele->numero_de_projet))
			return;

		$modifications_numero = array('numero_de_projet' => $this->nouveau_numero_projet());

		$this->enregistre_modele($modifications_numero);
	}

	/**
	 *
	 * Calcule du nouveau numero de projet
	 *
	 */
	protected function nouveau_numero_projet($modifications = false) {

		// on prend le projet avec le numéro le plus élevé et on l'incrémente
		if($modifications === false) {

			$dernier_projet = modele('projet')->where('entite_id', $this->modele->entite_id)->orderBy('numero_de_projet', 'DESC')->first();
		}
		else {

			$dernier_projet = modele('projet')->where('entite_id', $modifications['entite_id'])->orderBy('numero_de_projet', 'DESC')->first();
		}

		if($dernier_projet === null) {

			$dernier_numero = 0;
		} else {

			$dernier_numero = $dernier_projet->numero_de_projet;
		}

		if(is_int($dernier_numero))
			return $dernier_numero + 1;
		else
			return 1;
	}


	/**
	 *
	 *
	 * Retourne l'affichage  sur la saisie des temps
	 *
	 */
	public function retourne_affichage_projet($projet) {


		return $projet;
	}

	/**
	 *
	 * Récupère les infos du projet pour la gestion commerciale
	 *
	 * Cette méthode est appelée lorsqu'on crée ou modifie un document
	 *
	 */
	public function infos_projet_pour_gestion_commerciale() {

		if(empty($this->modele) || empty($this->modele->id)) {

			return array(

				'entite_id' => null,
				'modele' => modele('projet'),
				'adresse' => null,
				'adresses_livraison' => [],
			);
		}

		$adresse = modele('adresse')->where('projet_id', $this->modele->id)->first();

		// on va chercher la liste des adresses
		$adresses_livraison = modele('adresse')->where('projet_id', $this->modele->id)->where(function($r) { $r->whereIn('type_adresse', array(0,2,3))->orWhereNull('type_adresse'); })->get();

		return array(

			'entite_id' => $this->modele->entite_id,
			'modele' => $this->modele,
			'adresse' => $adresse,
			'adresses_livraison' => $adresses_livraison,
		);
	}

    /*
     *
     * Créé pour pouvoir spécifier les filtres par défaut des activités sur la saisie des temps
     *
     */
    public function retourne_filtres_activite(){

        return [0 => true, 1 => true, 2 => false, 3 => false, 'prioritaires' => false];

    }

    /**
     *
     * Retourne des conditions supplémentaires pour la récupération des devis servant à la création d'une facture d'avancement
     *
     */
    public function requete_devis_pour_facture_avancement($requete){

        return $requete;

    }

    /**
     *
     * Gestion des parametres lors de la création d'un document à partir de cet élément
     * Ici on ajoute directement le client lié au projet
     *
     */
    public function ajout_parametres_creation_document_avec_element(&$parametres, $type_element_destination = false){

        if(!empty($this->modele->client_id))
            $parametres['client_id'] = $this->modele->client_id;
    }
}
