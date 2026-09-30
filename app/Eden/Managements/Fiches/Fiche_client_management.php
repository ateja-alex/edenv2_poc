<?php

namespace App\Eden\Managements\Fiches;

use App\Eden\Managements\Fiche_management;
use App\Eden\Managements\Listes_management;
use App\Eden\Models\Profil_familial_client;

use App\Eden\Models\Client_centre_d_interet;
use App\Eden\Models\Facture_vente_lignes;
use App\Eden\Models\Recurrence;
use App\Eden\Models\Liste_libre;
use DB;
use App\Eden\Variables;

/**
 * Gestion des fiches clients
 */
class Fiche_client_management extends Fiche_management {
		
	/**
	 *
	 * Prépare les données pour la fiche
	 *
	 * @attention si vous utilisez cette méthode dans les classes filles, il faut faire appelle à parent::prepare_donnees_pour_fiche($donnees)
	 *
	 */
	public function prepare_donnees_pour_fiche($donnees = array()) {
		
		temps_execution('Fiche: debut prepare_donnees_pour_fiche (enfant std)');

		// on prépare la liste des factures
		$liste_management = new Listes_management();

        // on récupère les modules utilisés pour optimiser
		$modules = $this->modules_utilises();
		temps_execution('Fiche::prepare_donnees_pour_fiche::modules_utilises()');

		$client_management = management('client', $this->id_element);
		temps_execution('Fiche::prepare_donnees_pour_fiche::management()');
		
		$donnees['titre'] = $client_management->affiche_fiche_type();
		temps_execution('Fiche::prepare_donnees_pour_fiche::affiche_fiche_type()');

        // pour gérer le cas des fiches clients uniques sur plusieurs entités
		$tous_les_id_du_client = $client_management->clients_pour_master_id_client();
		temps_execution('Fiche::prepare_donnees_pour_fiche::clients_pour_master_id_client()');
		
		// on va chercher les données de base
		$donnees = parent::prepare_donnees_pour_fiche($donnees);
				
		temps_execution('Fiche::prepare_donnees_pour_fiche::emails_recus()');

        // on va ajouter les crédits & leurs indicateurs
		if(in_array('credits', $modules)) {
			
			$donnees['credits'] = $this->credits();
			$donnees['indicateurs_credits'] = $this->indicateurs_credits();
			$donnees['client_id_pour_credits'] = $client_management->client_id_pour_credits();
		}
		temps_execution('Fiche::prepare_donnees_pour_fiche::credits()');

		// les paiements
		// $donnees['paiements'] = $this->paiements();

		// On instancie l'id client afin de le récupérer automatiquement lorsqu'on va ajouter un paiement
		// $donnees['paiement_fiche_client'] = management('paiement')->modele_par_defaut();
		// $donnees['paiement_fiche_client']->client_id = $this->id_element;

		temps_execution('Fiche::prepare_donnees_pour_fiche::paiement_fiche_client()');
		
        // les contacts
		// if(in_array('liste_contacts', $modules))
			// $donnees['contacts'] = $this->contacts();
		
		temps_execution('Fiche::prepare_donnees_pour_fiche::contacts()');
		
		$nb_pages = 20;
		
		// les projets
		if(in_array('liste_projets', $modules)) {
			
			$donnees['projets'] = $this->projets();
			$donnees['options_liste_projets'] = [
													'page' => 1,
													'nombre_pages_pour_vue' => ceil(count($donnees['projets']) / $nb_pages),
													'tri' => 'id',
													'direction_tri' => false,
													'id_liste' => Liste_libre::where('type_element','projet')->first()->id,
												];
			$donnees['projets'] = $this->prepare_pagination($donnees['projets'], 1, $nb_pages);
			$donnees['projets_pour_affichage'] = $donnees['projets']->keyBy('id');
		}
		temps_execution('Fiche::prepare_donnees_pour_fiche::liste_projets()');
			
		// on récupère le profil familial
		if(in_array('profil_familial_client', $modules))
			$donnees['profil_familial'] = Profil_familial_client::where('client_id', $this->id_element)->first();

		temps_execution('Fiche::prepare_donnees_pour_fiche::profil_familial_client()');		

		// le paramétrage avancé des documents
		if(in_array('parametrage_avance_des_documents', $modules))
			$donnees['parametrage_avance_des_documents'] = $this->parametrage_avance_des_documents();
		temps_execution('Fiche::prepare_donnees_pour_fiche::parametrage_avance_des_documents()');

		// le CA sur 24 mois en haut de page
		// Fonction qui ralentit le script
		if(in_array('graphique_ca_24_mois', $modules))
			$donnees['fiche_client_indicateur_haut_de_page_ca_24_mois'] = $this->fiche_client_indicateur_haut_de_page_ca_24_mois();
		temps_execution('Fiche::prepare_donnees_pour_fiche::graphique_ca_24_mois()');

        if(in_array('coupons_reductions', $modules))
			$donnees['coupons_reductions'] = $this->coupons_reductions();
		temps_execution('Fiche::prepare_donnees_pour_fiche::coupons_reductions()');

        // on récupère les données pour le recouvrement
		if(in_array('recouvrement', $modules))
			$donnees['recouvrement'] = $this->recouvrement();
		temps_execution('Fiche::prepare_donnees_pour_fiche::recouvrement()');

        // on va ajouter les abonnements
        if(in_array('indicateurs', $modules)) {

            $donnees['indicateurs'] = $this->indicateurs();
        }
        
		temps_execution('Fiche::prepare_donnees_pour_fiche::indicateurs()');
        // temps_execution_recapitulatif();
		
		return $donnees;
	}

    /**
     * @return Array
     *
     * Permet de récupérer les options des fils arianes
     *
     */
    public function options_fil_ariane($donnees){

        $options_fil_ariane = parent::options_fil_ariane($donnees);

        $index_suppression = array_search('suppression', array_column($options_fil_ariane, 'id'));

        if($index_suppression !== false)
            unset($options_fil_ariane[$index_suppression]);

        if(!empty(moi()))
            $options_fil_ariane[] = [
                'id' => 'planifier_intervention',
                'ordre' => -1
            ];

        $options_fil_ariane[] = [
            'id' => 'modale_suppression_element',
            'parametres' => [
                'type_element_options' => 'client', 'include_depuis_fiche' => true
            ],
            'ordre' => 0,
			'option_a_droite' => true,
        ];

        if(fonctionnalite('gescom_document')['devis_vente'] || fonctionnalite('gescom_document')['facture_vente'] || fonctionnalite('gescom_document')['avoir_vente'])
            $options_fil_ariane[] = [
                'id' => 'creation_document',
                'ordre' => -4
            ];

        $options_fil_ariane[] = [
            'id' => 'fusion',
            'ordre' => -3
        ];

        $options_fil_ariane[] = [
            'id' => 'impression_pdf',
            'ordre' => -1
        ];

        return $options_fil_ariane;
    }

    /**
     *
     * On ajoute le CA, l'encours et le montant des devis du client
     *
     */
    public function indicateurs() {

        $factures_chiffre_affaire = modele('facture_vente')
            ->where('client_id', $this->id_element)
            ->where('date', '>', date('Y-m-d', strtotime('-1 year')))
            ->sum('montant_document_ht');

        $avoirs_chiffre_affaire = modele('avoir_vente')
            ->where('client_id', $this->id_element)
            ->where('date', '>', date('Y-m-d', strtotime('-1 year')))
            ->sum('montant_document_ht');

        $chiffre_affaire = $factures_chiffre_affaire - $avoirs_chiffre_affaire;

        $factures_encours = modele('facture_vente')
            ->where('client_id', $this->id_element)
            ->zero_ou_null('regle')
            ->where('valide', 1)
            ->sum('montant_document_ht');

        $avoirs_encours = modele('avoir_vente')
            ->where('client_id', $this->id_element)
            ->zero_ou_null('regle')
            ->where('valide', 1)
            ->sum('montant_document_ht');

        $encours = $factures_encours - $avoirs_encours;

        $montant_devis = modele('devis_vente')
            ->where('client_id', $this->id_element)
            ->where('date', '>', date('Y-m-d', strtotime('-1 year')))
            ->sum('montant_document_ht');

        return array('chiffre_affaire' => $chiffre_affaire, 'encours' => $encours, 'montant_devis' => $montant_devis);
    }


	/**
	 *
	 * Retourne les coupons réductions pour le client
	 *
	 * @return collection
	 *
	 */
	public function coupons_reductions() {
		
		$coupons_reductions = modele('coupon_reduction')->where('client_id', $this->id_element)->get()->toArray();
		
		return $coupons_reductions;
	}
	
	/**
	 *
	 * Retourne les crédits
	 *
	 * @return collection
	 *
	 */
	public function credits() {
		
		$credits = array();

		// les locations / abonnements
		if(fonctionnalite('gestion_credits') !== true)
			return $credits;
		
		$credit_management = management('credit');
		
		$clients_id = management('client', $this->id_element)->clients_pour_master_id_client();
		
		$credits_tmp = modele('credit')->whereIn('client_id', $clients_id)->orderBy('date', 'DESC')->get();
        
		foreach($credits_tmp as $credit_tmp) {
			
			$element_origine = false;
			
			if(!empty($credit_tmp->source_type_element) && !empty($credit_tmp->source_element_id))
				$element_origine = management($credit_tmp->source_type_element, $credit_tmp->source_element_id)->affiche_lien();
			
			$credit = array(
				
				'id' => $credit_tmp->id,
				'element_origine' => $element_origine,
				'nombre' => $credit_tmp->nombre,
				'date' => $credit_tmp->date,
				'origine' => $credit_management->champ('origine')->affiche($credit_tmp->origine),
			);
			
			$credits[] = $credit;
		}
		
		return $credits;
	}
	
	/**
	 * 
	 * Utilisé en spécifique pour changer l'id_client pour la recherche de crédits
	 * 
	 */
	public function client_id_pour_credits() {
		
		return $this->id_element;
	}
	
	/**
	 *
	 * Retourne les indeicateurs pour les crédits
	 *
	 * @return collection
	 *
	 */
	public function indicateurs_credits() {
		
		$indicateurs = array(
		
			'nombre_credits' => 0,
		);
		
		// les locations / abonnements
		if(fonctionnalite('gestion_credits') === true) {
			
			$credit_management = management('credit');
			
			$credits = modele('credit')->whereIn('client_id', management('client', $this->id_element)->clients_pour_master_id_client())->orderBy('date', 'DESC')->get();
			
			foreach($credits as $credit) {
				
				$indicateurs['nombre_credits'] += $credit->nombre;
			}
		}
		
		return $indicateurs;
	}
	
	/**
	 *
	 * Retourne les documents pour ce client
	 *
	 * @return collection
	 *
	 */
	public function commerce() {

		return $this->commerce_avec_tri();
	}

	/**
	 * 
	 * Ajoute la possibilité de trier les données du module commerce sur les fiches clients
	 * 
	 */
	public function commerce_avec_tri($tri = 'date', $direction = 'desc') {
		
		$documents = array();
		
		$documents_autorises = session()->get('fiche_filtres_commerce_'.$this->type_element);
		
		foreach(Variables::$documents_gescom as $type_element) {
			
			if(in_array($type_element, array('acompte_vente', 'acompte_achat')))
				continue;
			
			// on n'utilise pas ce type de document
			if(fonctionnalite('gescom_'.$type_element) !== true)
				continue;
			
			// on regarde si le client a filtré sur ce type de document
			if($documents_autorises != null && (!isset($documents_autorises[$type_element]) || $documents_autorises[$type_element] !== true))
				continue;
			
			// ok on peut ajouter ces documents
			
			// on crée la requête par défaut
			$clients_id = management('client', $this->id_element)->clients_pour_master_id_client();
			
			if(strpos($type_element, 'achat') === false)
				$requete = modele($type_element)->sans_orderby()->whereIn('client_id', $clients_id)->orderBy('date', 'DESC');
			else
				$requete = modele($type_element)->sans_orderby()->where('fournisseur_id', $this->id_element)->orderBy('date', 'DESC');
			
			// on regarde les conditions supplémentaires
			
			// le filtre sur les projets
			if($documents_autorises !== null && isset($documents_autorises['projet_selectionne']) && $documents_autorises['projet_selectionne'] != -1)
				$requete->where('projet_id', $documents_autorises['projet_selectionne']);
			
			// les filtres spécifiques aux types de documents
			if($documents_autorises !== null && $type_element == 'devis_vente') {
				
				if(isset($documents_autorises['devis_vente_en_cours']) && $documents_autorises['devis_vente_en_cours'] === true) {
					
					$requete->where(function($requete_tmp) { $requete_tmp->where('accepte', 0)->orWhereNull('accepte'); });
				}
				if(isset($documents_autorises['devis_vente_acceptes']) && $documents_autorises['devis_vente_acceptes'] === true) {
					
					$requete->where('accepte', 1);
				}
				if(isset($documents_autorises['devis_vente_refuses']) && $documents_autorises['devis_vente_refuses'] === true) {
					
					$requete->where('accepte', 2);
				}
			}
			if($documents_autorises != null && $type_element == 'facture_vente') {
				
				if(isset($documents_autorises['facture_vente_non_reglees']) && $documents_autorises['facture_vente_non_reglees'] === true) {
					
					$requete->where(function($requete_tmp) { $requete_tmp->where('regle', 0)->orWhereNull('regle'); });
				}
				if(isset($documents_autorises['facture_vente_reglees']) && $documents_autorises['facture_vente_reglees'] === true) {
					
					$requete->where('regle', 1);
				}
			}
			
			$documents[$type_element] = $requete->get();
		}
		
		return $this->prepare_donnees_commerce_avec_tri($documents, $tri, $direction);
	}

	/**
	 *
	 * Retourne les paiements liés au client
	 *
	 * @return collection
	 *
	 */
	public function paiements() {

		$paiements = modele('paiement')->whereIn('client_id', management('client', $this->id_element)->clients_pour_master_id_client())->orderBy('date')->get();

		if($paiements === null)
			return collect(array());

		$management = management('paiement');

		foreach($paiements as $paiement) {

			$paiement->mode_paiement_id = $management->champ('mode_paiement_id')->affiche($paiement->mode_paiement_id);
			$paiement->date = $management->champ('date')->affiche($paiement->date);
			$paiement->lien_document = '';

			if(!empty($paiement->type_element)) {

				$management = management($paiement->type_element, $paiement->id_document);
				$paiement->lien_document = $management->affiche_lien();
			}
		}

		return $paiements;
	}

	/**
	 *
	 * Retourne les projets liés au client
	 *
	 * @return collection
	 *
	 */
	public function projets() {

		$projets = modele('projet')->whereIn('client_id', management('client', $this->id_element)->clients_pour_master_id_client())->get();

		if($projets === null)
			return collect(array());

		return collect($projets);
	}

	public function projets_avec_filtres($filtres = array()) {


		$projets = modele('projet')
					->whereIn('client_id', management('client', $this->id_element)->clients_pour_master_id_client());

		foreach ($filtres as $filtre) {
			
			if ( strtolower($filtre[1]) == 'in' ) {

				$projets = $projets->whereIn($filtre[0], $filtre[2]);
			} else {

				$projets = $projets->where($filtre[0], $filtre[1], $filtre[2]);
			}
		}
        
		$projets = $projets->get();

		if($projets === null)
			return collect(array());

		return collect($projets);
	}

	/**
	 *
	 * Retourne les taches liées au client
	 *
	 * @return collection
	 *
	 */
	public function taches() {

		$taches = modele('tache')->whereIn('client_id', management('client', $this->id_element)->clients_pour_master_id_client())->get();

		if($taches === null)
			return collect(array());

		return collect($taches);
	}

	/**
	 *
	 * Retourne les centres d'intérêts liés au client
	 *
	 * @return collection
	 *
	 */
	public function centres_d_interet() {

		$centres_d_interet = null;

		$client_centres_d_interet = Client_centre_d_interet::where('client_id', $this->id_element)->get();

		$centres_d_interet_parent = modele('centre_d_interet')->where('parent_id', 0)->get();
        
		foreach($centres_d_interet_parent as $centre_d_interet_parent) {

			$listes_centres_d_interet[$centre_d_interet_parent->nom] = modele('centre_d_interet')->where('parent_id', $centre_d_interet_parent->id)->get();

			$centres_d_interet[$centre_d_interet_parent->nom] = array();

			foreach($listes_centres_d_interet[$centre_d_interet_parent->nom] as &$centre_d_interet) {

				if($client_centres_d_interet->where('centre_d_interet_id', $centre_d_interet->id)->count() > 0)
					$centre_d_interet->checked = true;
				else
					$centre_d_interet->checked = false;

					$centres_d_interet[$centre_d_interet_parent->nom][] = $centre_d_interet;
			}
		}

		if($centres_d_interet === null)
			return collect(array());

		return collect($centres_d_interet);
	}
	
	/**
	 *
	 * Retourne les paramétrages avancés des documents (modèles à utiliser, nombre d'exemplaires, champs obligatoires...)
	 *
	 * @return collection
	 *
	 */
	public function parametrage_avance_des_documents() {
		
		$parametrage_avance_des_documents = array();

		$parametrage_avance_des_documents['parametrages'] = modele('parametrage_avance_des_documents')->where('client_id', $this->id_element)->get()->keyBy('type_element')->toArray();
		
		// on va chercher les vues possibles pour chaque type de document
		foreach(Variables::$documents_gescom as $type_element) {
			
			if(strpos($type_element, 'achat') !== false)
				continue;
			
			// on rempli les paramétrages au cas ou
			if(!isset($parametrage_avance_des_documents['parametrages'][$type_element])) {
				
				$parametrage_avance_des_documents['parametrages'][$type_element] = array(
					'champs_obligatoires' => [],
					'type_element' => $type_element,
					'type_element_nom' => table_libre($type_element)->nom_table,
					'modele' => 'standard',
					'exemplaires' => 1,
				);
			}
			else {
				
				$parametrage_avance_des_documents['parametrages'][$type_element]['champs_obligatoires'] = json_decode($parametrage_avance_des_documents['parametrages'][$type_element]['champs_obligatoires']);
				$parametrage_avance_des_documents['parametrages'][$type_element]['type_element_nom'] = table_libre($type_element)->nom_table;
			}
			
			if(is_dir(base_path('resources/views/vendor/eden/pdf/'.$type_element))) {
				
				$vues = array();
				
				$repertoire = scandir(base_path('resources/views/vendor/eden/pdf/'.$type_element));
		
				foreach($repertoire as $fichier) {
					
					if($fichier == '.' || $fichier == '..')
						continue;
					
					$index_tableau = str_replace('.blade.php', '', $fichier);
					
					$vues[] = $index_tableau;
				}
				
				$parametrage_avance_des_documents['vues'][$type_element] = $vues;
			}
			else {
				
				$parametrage_avance_des_documents['vues'][$type_element] = array();
			}
		}
		
		return $parametrage_avance_des_documents;
	}

	/**
	 *
	 * Liste des articles facturés pour un client
	 *
	 */
	public function articles_factures($dates = null) {
		
		$ids_du_client = management('client', $this->id_element)->clients_pour_master_id_client();
		
		$factures = modele('facture_vente')
					->select(DB::raw('SUM(quantite * tarif * (100 - remise) / 100) as montant_ht, article_id, COUNT(*) as quantite'))
					->join('facture_vente_lignes', 'facture_vente.id', '=', 'facture_vente_lignes.document_id')
					->groupBy('article_id')
					->whereIn('client_id', $ids_du_client)
					->orderBy('montant_ht', 'DESC');



		if(!empty($dates)) {
			$factures = $factures->whereBetween('facture_vente.date',  array($dates['date_debut'], $dates['date_fin']));
		}

		$factures = $factures->get();
	
		foreach($factures as $facture) {
			
			$facture->article_id = management('article', $facture->article_id)->affiche_lien();
		}
		
		return $factures;
	}
	
	/**
	 *
	 * Liste des articles facturés par famille pour un client
	 *
	 */
	public function articles_factures_par_famille($dates = null) {
		
		$montants_par_article = modele('facture_vente')
					->select(DB::raw('SUM(quantite * tarif * (100 - remise) / 100) as montant_ht, article_id, COUNT(*) as quantite'))
					->join('facture_vente_lignes', 'facture_vente.id', '=', 'facture_vente_lignes.document_id')
					->groupBy('article_id')
					->where('client_id', $this->id_element);

		
		if(!empty($dates)) {
			$montants_par_article = $montants_par_article->whereBetween('facture_vente.date', array($dates['date_debut'], $dates['date_fin']));
		}

		$montants_par_article = $montants_par_article->get();

		$montants_par_famille = array();
		
		foreach($montants_par_article as $montant_par_article) {
			
			$article_management = management('article', $montant_par_article->article_id);
			
			$famille_analyse = $article_management->famille_analyse();
			
			if(!isset($montants_par_famille[$famille_analyse]))
				$montants_par_famille[$famille_analyse] = 0;
			
			$montants_par_famille[$famille_analyse] += $montant_par_article->montant_ht;
		}
		
		
		return $montants_par_famille;
	}

	/**
	 *
	 * Retourne les infos pour générer un graphique en haut de page pour afficher le CA sur 24 mois
	 *
	 * @return collection
	 *
	 */
	public function fiche_client_indicateur_haut_de_page_ca_24_mois() {

		$infos_graphique = array(
			
			'legende' => array(),
			'n' => array(),
			'n_moins_1' => array(),
		);
		
		$il_y_a_13_mois = date('Y-m-01', strtotime("now - 12 months"));
		$il_y_a_24_mois = date('Y-m-01', strtotime("now - 24 months"));
		
		
		$factures = modele('facture_vente')
							->select(DB::raw('SUM(montant_document_ht) as montant, left(date, 7) as periode'))
							->where('valide', 1)
							->where('date', '>=', $il_y_a_24_mois)
							->where('date', '<=', date('Y-m-t'))
							->whereIn('client_id', management('client', $this->id_element)->clients_pour_master_id_client())
							->groupBy(DB::raw("left(date, 7)"))
							->get()
							->pluck('montant', 'periode');
							
		$avoirs = modele('avoir_vente')
							->select(DB::raw('SUM(montant_document_ht) as montant, left(date, 7) as periode'))
							->where('valide', 1)
							->where('date', '>=', $il_y_a_24_mois)
							->where('date', '<=', date('Y-m-t'))
							->whereIn('client_id', management('client', $this->id_element)->clients_pour_master_id_client())
							->groupBy(DB::raw("left(date, 7)"))
							->get()
							->pluck('montant', 'periode');
		
		$date_courante_n_moins_1 = $il_y_a_24_mois;
		$date_courante = $il_y_a_13_mois;
		
		while($date_courante <= date('Y-m-01')) {
			
			// les légendes
			$infos_graphique['legende'][] = "'".Variables::mois_de_lannee_format_3(date('m', strtotime($date_courante)))."'";
			
			// année N		
			/*
			$factures = modele('facture_vente')
							->select(DB::raw('SUM(montant_document_ht) as montant'))
							->where('valide', 1)
							->where('date', '>=', $date_courante)
							->where('date', '<=', date('Y-m-t', strtotime($date_courante)))
							->whereIn('client_id', management('client', $this->id_element)->clients_pour_master_id_client())
							->first();
			
			$avoirs = modele('avoir_vente')
							->select(DB::raw('SUM(montant_document_ht) as montant'))
							->where('valide', 1)
							->where('date', '>=', $date_courante)
							->where('date', '<=', date('Y-m-t', strtotime($date_courante)))
							->whereIn('client_id', management('client', $this->id_element)->clients_pour_master_id_client())
							->first();
			*/
			
			$index_date = formate_date('Y-m', $date_courante);
			
			if(!isset($factures[$index_date]))
				$factures[$index_date] = 0;
			
			if(!isset($avoirs[$index_date]))
				$avoirs[$index_date] = 0;
			
			$infos_graphique['n'][] = $factures[$index_date] - $avoirs[$index_date];
			
			// année N-1	
			/*
			$factures = modele('facture_vente')
							->select(DB::raw('SUM(montant_document_ht) as montant'))
							->where('valide', 1)
							->where('date', '>=', date('Y-m-01', strtotime("$date_courante -12 months")))
							->where('date', '<=', date('Y-m-t', strtotime("$date_courante -12 months")))
							->whereIn('client_id', management('client', $this->id_element)->clients_pour_master_id_client())
							->first();
							
			$avoirs = modele('avoir_vente')
							->select(DB::raw('SUM(montant_document_ht) as montant'))
							->where('valide', 1)
							->where('date', '>=', date('Y-m-01', strtotime("$date_courante -12 months")))
							->where('date', '<=', date('Y-m-t', strtotime("$date_courante -12 months")))
							->whereIn('client_id', management('client', $this->id_element)->clients_pour_master_id_client())
							->first();
			*/
			
			$index_date = formate_date('Y-m', $date_courante_n_moins_1);
			
			if(!isset($factures[$index_date]))
				$factures[$index_date] = 0;
			
			if(!isset($avoirs[$index_date]))
				$avoirs[$index_date] = 0;
			
			$infos_graphique['n_moins_1'][] = $factures[$index_date] - $avoirs[$index_date];
			
			// on incrémente
			$date_courante = date('Y-m-01', strtotime("$date_courante + 1 month"));
			$date_courante_n_moins_1 = date('Y-m-01', strtotime("$date_courante_n_moins_1 + 1 month"));
		}
		
		return $infos_graphique;
	}
	
	public function recouvrement() {
		
		$donnees = array();
		
		$factures_du_client = modele('facture_vente')->zero_ou_null('regle')->where('valide', 1)->where('client_id', $this->id_element)->get();
		$acompte_du_client = modele('acompte_vente')->zero_ou_null('regle')->where('valide', 1)->where('client_id', $this->id_element)->get();

		// On s'occupe des commentaires
		$commentaires = array();

		foreach ($factures_du_client as $facture) {
			
			$facture->facture = management('facture_vente', $facture->id)->affiche_lien();
			if ($facture->commentaires_recouvrement != null)
				$commentaires['facture_'.$facture->id] = $facture; 
		}

		foreach ($acompte_du_client as $acompte) {
			
			$acompte->facture = management('acompte_vente', $acompte->id)->affiche_lien();
			if ($acompte->commentaires_recouvrement != null)
				$commentaires['acompte_'.$acompte->id] = $acompte; 
		}

		// l'indicateur
		$donnees['solde_encours'] = $factures_du_client->sum('solde_document_ttc') + $acompte_du_client->sum('solde_document_ttc');
		
		$relances = modele('relance_recouvrement')->where('client_id', $this->id_element)->orderBy('date', 'DESC')->get();
		
		foreach($relances as $relance) {
			
			if(empty($relance->type_element))
				$relance->type_element = 'facture_vente';
			
			$relance->facture = management($relance->type_element, $relance->facture_vente_id)->affiche_lien();
			$relance->type = management('relance_recouvrement', $relance->id)->champ('type_relance')->affiche();
		}
		
		$donnees['relances'] = $relances;
		$donnees['commentaires_relance'] = $commentaires;
		
		return $donnees;
	}


    /**
     *
     * Envoie les blocs que l'on peut imprimer sur la fiche
     *
     */
    public function blocs_impression_fiche(){

        return array(
            'informations_principales' => array(
                'nom' => "Informations principales :",
                'blocs' => array(
                    'bloc_client' => 'Généralités',
                    'bloc_contact' => 'Contacts',
                    'bloc_adresse' => 'Adresses',
                    'bloc_taches' => 'Tâches',
                    'bloc_ticket_client' => 'Tickets clients',
                    'bloc_taches_rdv' => 'RDV',
                ),
            ),
            'documents' => array(
                'nom' => "Documents :",
                'blocs' => array(
                    'bloc_devis_vente' => 'Devis',
                    'bloc_commande_vente' => 'Commandes',
                    'bloc_facture_vente' => 'Factures',
                    'bloc_avoir_vente' => 'Avoirs',
                ),
            ),
            'projets' => array(
                'nom' => "Projets :",
                'blocs' => array(
                    'bloc_projet' => 'Projets',
                ),
            ),
            'informations_diverses' => array(
                'nom' => "Informations diverses :",
                'blocs' => array(
                    'bloc_echange' => 'Echanges commerciaux',
                    'bloc_paiement' => 'Paiements',
                ),
            )
        );
    }

}
