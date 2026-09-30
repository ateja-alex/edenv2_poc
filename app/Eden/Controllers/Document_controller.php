<?php

namespace App\Eden\Controllers;

use App\Http\Controllers\Controller;

use App\Eden\Models\Elements\Utilisateur;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Recurrence;
use App\Eden\Models\Coupon_reduction;
use App\Eden\Models\Commande_vente_ligne;

use App\Eden\Variables;

use Illuminate\Http\Request;

use Mail;
use ZipArchive;
use DB;
use Session;
use \Schema;

class Document_controller extends Controller {

	/**
	 *
	 * Gère la transformation d'un document en gestion commerciale
	 *
	 * @param $type_element le type élément d'origine
	 * @param $id_element l'ID élément d'origine
	 * @param $type_element_destination le type élément de destination
	 *
	 */
	public function transformer($type_element, $id_element, $type_element_destination, $date = null, $variante = null) {

		$management = management($type_element_destination);

		// on va chercher les valeurs des champs multi sélection
		$management->charge_valeurs_champs_multiselection();

		$management_element_origine = management($type_element, $id_element);

        $duplication = $type_element_destination == $type_element;

		// c'est le cas d'une duplication
		if($duplication || ($type_element == 'facture_vente' && $type_element_destination == 'devis_vente')) {

			$articles_document = $management_element_origine->recupere_articles_pour_transformation(false, $type_element_destination);
			$articles_du_document = $management_element_origine->lignes_du_document_pour_saisie($type_element_destination, false);
            $articles_du_document = $management_element_origine->traitement_articles_duplication($articles_du_document);
		}
		else {

			$articles_du_document = $management_element_origine->lignes_du_document_pour_saisie($type_element_destination, true);
			$articles_document = $management_element_origine->recupere_articles_pour_transformation(true, $type_element_destination);
		}

		$lignes_divers_document = $management_element_origine->recupere_lignes_divers_pour_transformation();

		// on gère le cas ou c'est une variante d'une variante
		if($variante) {

			if(!empty($management_element_origine->modele->variante_devis_vente_id))
				$variante = $management_element_origine->modele->variante_devis_vente_id;
		}

        $management->traitement_sur_les_articles_pour_transformation($articles_du_document,$management_element_origine);

		if($type_element_destination == 'facture_vente') {

			$management->ajoute_article_acompte($management_element_origine, $articles_du_document);
		}

		// on ajoute les infos de l'avancement si nécessaire

		if(fonctionnalite('gescom_document_avancement') === true) {

			// on va chercher les factures et les acomptes
			$factures = $management_element_origine->documents_lies('facture_vente');
			$acomptes = $management_element_origine->documents_lies('acompte_vente');

            // on va chercher la dernière
			$derniere_facture = false;

			foreach($factures as $infos_facture) {

				if($derniere_facture === false)
					$derniere_facture = $infos_facture['management'];

				if($derniere_facture->modele->date < $infos_facture['management']->modele->date)
					$derniere_facture = $infos_facture['management'];

			}

			// on regarde s'il y a une facture
			if($derniere_facture !== false) {

				$articles_derniere_facture = $derniere_facture->articles()->keyBy('ligne')->toArray();

				foreach($articles_du_document as $article) {

					if(isset($article->ligne)) {

						if(isset($articles_derniere_facture[$article->ligne])) {

							$article->avancement_precedent = $articles_derniere_facture[$article->ligne]['avancement_actuel'];
							$article->avancement_actuel = $articles_derniere_facture[$article->ligne]['avancement_actuel'];
						}
					}

				}
			}
			// on s'il y avait un acompte ?
			else {

				// y'avait un acompte
				if(!empty($acomptes)) {

					// on récupère le 1er acompte, qui servira pour l'avancement précédent initial
					$acompte_management = array_values($acomptes)[0]['management'];

					$totaux = $acompte_management->calcule_total_document();

                    // le montant de l'acompte TTC
                    if(isset($totaux['avant_acompte']['ttc']))
					    $acompte_ttc = $acompte_management->modele->montant_document_ttc / $totaux['avant_acompte']['ttc'] * 100;
                    else
                        $acompte_ttc = 0;

					// finalement on indique le % d'avancement initial
					foreach($articles_du_document as $article) {

						$article['avancement_precedent'] = $acompte_ttc;
						$article['avancement_actuel'] = $acompte_ttc;
					}
				}
			}
		}

		// on va chercher les articles
		if(fonctionnalite('afficher_code_article_recherche') === false) {

			$concat = "designation";
		} else {

			$concat = "code_article,' - ',designation";
		}

		// on instancie un modèle par défaut
		$management->modele = modele_par_defaut($type_element_destination);

		// on remplit avec des données par défaut en fonction du type_element
		$management->donnees_avant_transformation($management_element_origine);

		// on copie les données
		foreach($management_element_origine->modele->getAttributes() as $champ => $valeur) {

			// les champs non modifiables sur tous les documents
			$champs_non_transformables = array(

				'id',
				'valide',
				'annule',
				'cree_le',
				'cree_par',
				'modifie_le',
				'modifie_par',
				'accepte',
				'regle',
				'livre',
				'date_changement_statut',
				'statut_facturation_electronique',
				'facturation_electronique_flow_id',
			);

			if(in_array($champ, $champs_non_transformables))
				continue;

			// on vérifie si le champ existe sur la table de destination
			$colonnes = bdd_colonnes($type_element_destination);

			// le champ n'existe pas
			if(!in_array($champ, $colonnes))
				continue;


			$management->modele->$champ = $valeur;
		}

        $management->donnees_apres_transformation($management_element_origine);

		// on retraite... (à améliorer pour prévoir les surcharges)
		$management->modele->valide = 0;
		$management->modele->annule = 0;
		$management->modele->inactif = 0;
		$management->modele->comptabilise = 0;

		if ($date != null) {

			// On utilise la date qui a été passée en paramètre, si présente
			$management->modele->date = formate_date('Y-m-d', $date);
		} else {

			// Sinon, on utilise la date du modele à dupliquer
			$management->modele->date = formate_date('Y-m-d', $management->modele->date);
		}

        if(!empty(fonctionnalite('delai_expiration_devis')))
            $management->modele->date_expiration = date('Y-m-d', strtotime($management->modele->date.' +'.fonctionnalite('delai_expiration_devis').' days'));

		if(isset($management->modele->date_de_reglement))
			$management->modele->date_de_reglement = formate_date('Y-m-d', $management->modele->date_de_reglement);

		/*
		if(view()->exists('eden::formulaires.creer_'.$management->_type_element.'_specifique')) {

			$view = 'eden::formulaires.creer_'.$management->_type_element.'_specifique';
		}
		else {

			$view = 'eden::formulaires.creer_'.$management->_type_element;
		}
		*/

		// Si le paramètre pourcentage_facture est présent et supérieur à zéro, on redéfini les articles du document pour modifier le tarif saisi
		$pourcentage_facture = floatval(request()->pourcentage_facture);
		if (!empty($pourcentage_facture) && $pourcentage_facture > 0) {

			$articles_document_2 = $articles_document->map(function($article) use ($pourcentage_facture) {

				  $article = $article->toArray();
				  $article['tarif_saisi'] = $article['tarif_saisi'] * $pourcentage_facture / 100 ;
				  return (object)($article);
				});

			$articles_document = $articles_document_2 ;
		}

		$colonnes_articles = $management->colonnes_articles();
		$colonnes_articles_enregistrement = $management->champs_sur_ligne_document($colonnes_articles);
		$options_lignes_divers = $management->options_lignes_divers();
        $options_lignes_divers_champs_supplementaires_enregistrement = $management->options_lignes_divers_champs_supplementaires_enregistrement($options_lignes_divers);

		// On transforme la variable $articles_du_document en tableau, puis en JSON
		if(is_object($articles_du_document)) {

			$articles_du_document = (array)$articles_du_document->toArray();
			$articles_du_document = json_encode($articles_du_document);
		}
		$taux_de_tva = modele('code_tva')->distinct('taux')->orderBy('taux')->get()->pluck('taux', 'taux')->toArray();

		$modeles_commentaire = modele('modele_commentaire')->orderBy('nom')->get();
		$modeles_commentaire_vierge = modele('modele_commentaire');

		$noms_unites = modele('article_unite')->get()->pluck('nom','id');
        $noms_unites[0] = "Unité";

		// Si avoir partiel, on ajoute commentaire
		if ($management->_type_element == "avoir_vente") {

			if ($management->modele->commentaires != null)
				$management->modele->commentaires .= "\n";

			$management->modele->commentaires .= traduction('messages.php.document.facture_origine_avoir')." : ".$management_element_origine->modele->reference_document;
		}

        if(strpos($management_element_origine->_type_element, 'vente') !== false) {

			$management->modele->contacts_ids = $management_element_origine->modele->contacts()->get()->pluck('id')->toArray();
		}

        $management_fiche = $management->management_fiche();

		$donnees_fiche = $management_fiche->prepare_donnees_pour_fiche();

		$coupon_reduction = !empty($management->modele->coupon_reduction) ? 
			modele('coupon_reduction')->where('id', $management->modele->coupon_reduction)->first() : null;
		
		if(isset($coupon_reduction))
			$coupon_reduction->message = traduction('messages.php.document.coupon_applique', null, [
				round($coupon_reduction->valeur, 2) . ($coupon_reduction->type_de_reduction === 1 ? '%' : maquette('devise_application_symbole'))
			]);

		$fonctionnalite_colonnes = $management->est_une_vente() ? fonctionnalite('documents_colonnes_a_afficher_vente') : fonctionnalite('documents_colonnes_a_afficher_achat');

		return view('eden::formulaires.creer_document', [
			'type_element' => $type_element,
            'structure' => $donnees_fiche['structure'],
            'listes_sur_fiche' => $donnees_fiche['listes_sur_fiche'],
            'watch_pour_vuejs' => $donnees_fiche['watch_pour_vuejs'],
			'options_fil_ariane' => $donnees_fiche['options_fil_ariane'],
			'colonnes_articles' => $colonnes_articles,
			'colonnes_articles_lignes_entieres' => $management->colonnes_articles_lignes_entieres(),
			'colonnes_articles_enregistrement' => $colonnes_articles_enregistrement,
			'options_articles' => $management->retourne_options_articles(),
			'colonne_options_lignes_diverses' => $management->retourne_options_lignes_diverses(),
            'options_lignes_divers' => $options_lignes_divers,
            'options_lignes_divers_champs_supplementaires_enregistrement' => $options_lignes_divers_champs_supplementaires_enregistrement,
			'articles_du_document' => $articles_du_document,
			'management' => $management,
            'lignes_divers' => $lignes_divers_document,
			'contacts' => collect($management->contacts_du_client()),
			'modes_de_paiement' => management('paiement')->champ('mode_paiement_id')->valeurs_possibles,
			'comptes_bancaires' => management('paiement')->champ('compte_bancaire_id')->valeurs_possibles,
			'variante' => $variante,
			'verification_approbation' => $management->verification_approbation_neccessaire(),
			'verification_document_concerne_par_approbation' => $management->verification_document_concerne_par_approbation(),
			'verification_demande_approbation_concerne' => $management->verification_demande_approbation_concerne(),
			'type_element_source' => $management_element_origine->_type_element,
			'id_element_source' => $management_element_origine->modele->id,
			'taux_de_tva' => $taux_de_tva,
			'modeles_commentaire' => $modeles_commentaire,
			'modeles_commentaire_vierge' => $modeles_commentaire_vierge,
			'noms_unites' => $noms_unites,
			'en_cours_de_transformation' => true,
			'duplication' => $duplication,
			'coupon_reduction' => $coupon_reduction,
			'fonctionnalite_colonnes' => $fonctionnalite_colonnes,
		]);
	}

	/**
	 *
	 * Transforme le devis vente en devis achat fournisseur
	 *
	 */
	public function transformer_document_fournisseur($type_element_origine, $id_element_origine, $type_element_destination) {

		$management_element_origine = management($type_element_origine, $id_element_origine);

		$articles_document = $management_element_origine->articles();

		// est ce qu'on doit bloquer la validation de documents si le client présente un retard de paiement ?
		if(fonctionnalite('bloquer_validation_document_si_client_retard_paiement')[$type_element_destination] === true && !empty($management_element_origine->modele->client_id)) {

			// on va chercher le solde du client
			$solde = management('client', $management_element_origine->modele->client_id)->solde_du();

			if($solde > 0) {

				return redirect()->back()->withErrors(traduction('messages.php.document.generation_impossible_retard_paiement',null,[montant($solde)." ". maquette('devise_application_nom') ."s"]));
			}
		}

		// on récupère les fournisseurs de tous les produits du devis
		$fournisseurs = array();

		$articles_sans_fournisseur = array();

		foreach($articles_document as $ligne) {

			$articles_fournisseurs = modele('article_fournisseur')->where('article_id', $ligne->article_id)->orderBy('fournisseur_prioritaire', 'desc')->get();

			$fournisseur_trouve = false;

            if(!empty($ligne->regroupement_id)) {
                unset($ligne->regroupement_id);
                unset($ligne->couleur_regroupement);
            }

			foreach($articles_fournisseurs as $article_fournisseur) {

				if(!empty($article_fournisseur->tarif)) {

					$ligne->tarif = $article_fournisseur->tarif;
					$ligne->tarif_saisi = $article_fournisseur->tarif;
				}
				else {

					$ligne->tarif = 0;
					$ligne->tarif_saisi = 0;
				}

				$fournisseurs[$article_fournisseur->fournisseur_id][] = $ligne;

				$fournisseur_trouve = true;
				break;
			}

			if($fournisseur_trouve === false) {

				$articles_sans_fournisseur[] = traduction('messages.php.document.article_sans_fournisseur',null,[$ligne->designation,$ligne->article_id]);
			}
		}

		// certains articles n'ont pas de fournisseur
		if(!empty($articles_sans_fournisseur)) {

			return redirect()->back()->withErrors($articles_sans_fournisseur);
		}

		// on créé un document par fournisseur
		$documents_a_creer = array();
        $documents_erreur = array();
		$documents_crees = array();

		foreach($fournisseurs as $fournisseur_id => $articles) {

            $donnees = $management_element_origine->donnees_transformation_document_fournisseur();

			$management = management($type_element_destination);

			$donnees['date'] = date('Y-m-d');
			$donnees['fournisseur_id'] = $fournisseur_id;
			$donnees['projet_id'] = $management_element_origine->modele->projet_id;
			// $donnees['date_de_reglement'] = date('Y-m-d');
			$donnees['objet'] = "Document généré depuis le document ".$management_element_origine->modele->reference_document." (".table_libre($type_element_origine)->element.")";

			foreach($articles as $article_ligne) {

				$article_ligne_tableau = $article_ligne->toArray();

				$article_ligne_tableau['type_element_source'] = $type_element_origine;
				$article_ligne_tableau['id_element_source'] = $id_element_origine;
				$article_ligne_tableau['id_ligne_source'] = $article_ligne->id;

				$donnees['articles'][] = $article_ligne_tableau;
			}

			$retour = $management->test_enregistre($donnees);

            if($retour != 'test_ok')
                $documents_erreur[] = $retour;

            $documents_a_creer[] = $donnees;

		}

        if(!empty($documents_erreur))
            return redirect()->back()->withErrors($documents_erreur);

        foreach ($documents_a_creer as $donnees){

            $management = management($type_element_destination);

            $management->enregistre($donnees);

            $documents_crees[] = traduction('messages.php.document.element_fournisseur_cree',null,[ucfirst(table_libre($type_element_destination)->element)])." : ".$management->affiche_lien();

        }


		return redirect()->back()->with('confirmations', $documents_crees);
	}

	/**
	 *
	 * Transforme un document client en fournisseur, mais avec les articles pré choisis par fournisseurs
	 *
	 */
	public function transformer_document_fournisseur_avec_articles($type_element_origine, $id_element_origine, $type_element_destination) {

		$formulaire = request()->all();

		$management_element_origine = management($type_element_origine, $id_element_origine);

		// est ce qu'on doit bloquer la validation de documents si le client présente un retard de paiement ?
		if(fonctionnalite('bloquer_validation_document_si_client_retard_paiement')[$type_element_destination] === true && !empty($management_element_origine->modele->client_id)) {

			// on va chercher le solde du client
			$solde = management('client', $management_element_origine->modele->client_id)->solde_du();

			if($solde > 0) {

				return redirect()->back()->withErrors(traduction('messages.php.document.generation_impossible_retard_paiement',null,[montant($solde)." ". maquette('devise_application_nom') ."s"]));
			}
		}

		// tableau qui va contenir toutes les commandes fournisseur à créer
		$commandes_fournisseur = array();

		if(!empty($formulaire['commandes_fournisseur']) && is_array($formulaire['commandes_fournisseur']))
            $commandes_fournisseur = $management_element_origine->recupere_article_pour_transformation_document_fournisseur($formulaire['commandes_fournisseur']);

		$documents_crees = array();
		$documents_echecs = array();

        $fournisseurs = modele('fournisseur')->whereIn('id', array_keys($commandes_fournisseur))->get()->keyBy('id');
        $champs_correspondances = Champ_libre::where('type_element', $type_element_destination)->where('correspondance_fiche_tiers', '!=', '')->get();

		foreach($commandes_fournisseur as $id_fournisseur => $infos_par_commande) {

            if(empty($fournisseurs[$id_fournisseur]))
                continue;

			foreach($infos_par_commande as $id_commande => $articles) {

				// on doit créer une commande à la volée
				if($id_commande != 'nouvelle_commande') {

					// on doit récupérer les articles de la commande existante
					$commande = management('commande_achat', $id_commande);
					$articles_de_la_commande = $commande->articles();
				}
				else {

					$commande = management('commande_achat');
					$articles_de_la_commande = array();
				}

				// on merge les articles
                if(!empty($articles_de_la_commande) && !is_array($articles_de_la_commande))
				    $articles = array_merge($articles_de_la_commande->toArray(), $articles);
                else
                    $articles = array_merge($articles_de_la_commande, $articles);

                $infos_maj_commande = $management_element_origine->retourne_infos_transformation_commande_fournisseur($commande, $articles, $id_fournisseur, $champs_correspondances);

				// on enregistre la commande
				$retour = $commande->enregistre($infos_maj_commande);

				if($retour === true)
					$documents_crees[] = traduction('messages.php.document.element_fournisseur_cree',null,[ucfirst(table_libre($type_element_destination)->element)])." : ".$commande->affiche_lien();

				else
					$documents_echecs[] = $retour;
			}
		}

		return json_encode(
						array(
							'succes' => (count($documents_echecs) > 0 ? false : true),
							'documents_crees' => $documents_crees,
							'documents_echecs' => $documents_echecs,
						)
					);
	}

	/**
	 *
	 * Cette méthode est appelée lorsqu'on vient d'enregistrer un document
	 *
	 * Elle rappelle la méthode creer() classique, mais elle ajoute le paramètre enregistrement = true,
	 * qui permet de déclencher certaines actions
	 *
	 */
	public function creer_post_enregistrement($type_element, $id) {

		return $this->creer($type_element, $id, true);
	}

	/**
	 *
	 * Cette méthode est appelée lorsqu'on vient d'enregistrer un document en appuyant sur la touche afficher le pdf
	 *
	 * Elle rappelle la méthode creer() classique, mais elle ajoute le paramètre enregistrement = true, et afficher_pdf = true
	 * qui permet de déclencher certaines actions
	 *
	 */
	public function creer_post_enregistrement_avec_pdf($type_element, $id) {

		return $this->creer($type_element, $id, true, array(), true);
	}

	/**
	 *
	 * Affiche le formulaire de création standard avec l'élement pré saisi
	 *
	 */
	public function creer_avec_element($type_element,$champ,$element_id) {

        $parametres = array();

        $champ_libre = Champ_libre::where('type_element',$type_element)->where('nom_sql',$champ)->first();

        if($champ_libre !== null){

            $management_element = management($champ_libre->type_element_ajax,$element_id);

            if(!empty($management_element->modele)) {

                $parametres[$champ] = $element_id;

                $management_element->ajout_parametres_creation_document_avec_element($parametres, $type_element);
            }
        }

        return $this->creer($type_element, false, false, $parametres);
	}

	/**
	 *
	 * Affiche le formulaire de création standard
	 *
	 * @param $enregistrement = si on vient d'enregistrer ce document à l'instant.
	 * cette variable est utilisée notamment pour faire certaines actions métier (afficher une popup, faire une vérification, etc)
	 * Au moment ou on vient juste d'enregistrer le document
	 *
	 */
	public function creer($type_element, $id = false, $enregistrement = false, $donnees_par_defaut = array()) {

		temps_execution('debut document_controller', 1);

	    if($id == false) {
            $nom_page = '<strong>' . traduction('interface.historique.creation_document') . '</strong> (' . table_libre($type_element)->element . ')';
            $url = 'eden/document/' . $type_element;
        }

	    else{
            $nom_page = '<strong>' . management($type_element, $id)->affiche() . '</strong> (' . table_libre($type_element)->element . ')';
            $url = 'eden/document/' . $type_element.'/'.$id;
        }
        
        enregistrer_log_historique($url, $nom_page);

		$management = management($type_element, $id);

        if($id !== false && !$management->existe())
            return redirect()->route('document.creer',$type_element);

        $profil_acces = $id === false ? profil_creation($type_element) : profil_lecture($type_element,$management->modele->entite_id,$management->modele);

        if(!$profil_acces)
            abort(403);

		// on va chercher les valeurs des champs multi sélection
		$management->charge_valeurs_champs_multiselection();

		$champs = Champ_libre::where('type_element', $type_element)->get()->pluck('nom', 'nom_sql');

		// on va chercher les documents liés
		$documents_lies = $management->documents_lies(false, true);
		$documents_par_recurrence = $management->documents_par_recurrence($documents_lies);

		temps_execution('document_controller::documents_lies', 1);

		// les infos sur la récurrence
		$recurrence = $management->retourne_recurrence_pour_saisie_document();

		// ne fait rien en std, c'est pour des surcharges
		$management->retouche_modele_pour_formulaire_document();

		temps_execution('document_controller::retouche_modele_pour_formulaire_document', 1);

		// s'il faut précharger des données, en fonction de l'url (par exemple le client ou le projet)
		if(!empty($donnees_par_defaut)) {

			$management->modele = $management->modele_par_defaut();

			foreach($donnees_par_defaut as $champ => $valeur) {

                if($champ == 'articles')
                    continue;

				$management->modele->{$champ} = $valeur;
			}
		}

		if(!empty($management->modele)) {

			$management->modele->date = formate_date('Y-m-d', $management->modele->date);

			if(isset($management->modele->date_de_reglement))
				$management->modele->date_de_reglement = formate_date('Y-m-d', $management->modele->date_de_reglement);
		}

        $colonnes_articles = $management->colonnes_articles();

		$colonnes_articles_enregistrement = $management->champs_sur_ligne_document($colonnes_articles);
        $options_lignes_divers = $management->options_lignes_divers();
        $options_lignes_divers_champs_supplementaires_enregistrement = $management->options_lignes_divers_champs_supplementaires_enregistrement($options_lignes_divers);

		$verification_droits = $management->droit_acces_a_fiche();

		$taux_de_tva = modele('code_tva')->distinct('taux')->orderBy('taux')->get()->pluck('taux', 'taux')->toArray();

		$modeles_commentaire = modele('modele_commentaire')->orderBy('nom')->get();
		$modeles_commentaire_vierge = modele('modele_commentaire');

        $noms_unites = modele('article_unite')->get()->pluck('nom','id');
        $noms_unites[0] = "Unité";

		temps_execution('document_controller::divers 1', 1);

		if (!$verification_droits)
			return redirect()->route('base_eden.fiche.pas_droits',['type_element' => $management->_type_element]);

        $management_fiche = $management->management_fiche();

        $donnees_fiche = $management_fiche->prepare_donnees_pour_fiche();

		if(fonctionnalite('gescom_afficher_recap_documents_lies_avec_details') && $management->existe())
			service('documents_lies')->ajoute_document_au_tableau_des_documents_lies($documents_lies, $management->_type_element, $management->modele->id, true);

		$articles_tmp = $donnees_par_defaut['articles'] ?? $management->articles();
		temps_execution('document_controller::après articles()', 1);

		$articles_du_document = $management->lignes_du_document_pour_saisie(false, false, $articles_tmp);
		temps_execution('document_controller::lignes_du_document_pour_saisie', 1);
        $articles_supprimes = $management->articles_supprimes();
		temps_execution('document_controller::articles_supprimes', 1);
		$lignes_divers_document = $management->lignes_divers_document();
		temps_execution('document_controller::lignes_divers_document', 1);
		$calcule_total_document = $management->calcule_total_document(false, $articles_tmp);
		temps_execution('document_controller::calcule_total_document', 1);
		$recupere_documents_pour_impressions = $management->recupere_documents_pour_impressions();
		temps_execution('document_controller::recupere_documents_pour_impressions', 1);
        $transformations_possibles = [];
        $modeles_de_relances = [];
        if($management->existe()) {
            $transformations_possibles = $management->affichage_transformations_possibles();
            $modeles_de_relances = $management->affichage_modeles_de_relances();
        }
		temps_execution('document_controller::transformations_possibles', 1);
		$verification_approbation_neccessaire = $management->verification_approbation_neccessaire();
		temps_execution('document_controller::verification_approbation_neccessaire', 1);
		$verification_document_concerne_par_approbation = $management->verification_document_concerne_par_approbation();
		temps_execution('document_controller::verification_document_concerne_par_approbation', 1);
		$verification_demande_approbation_concerne = $management->verification_demande_approbation_concerne();
		temps_execution('document_controller::verification_demande_approbation_concerne', 1);
		$coupon_reduction = !empty($management->modele->coupon_reduction) ? 
			modele('coupon_reduction')->where('id', $management->modele->coupon_reduction)->first() : null;
		
		if(isset($coupon_reduction))
			$coupon_reduction->message = traduction('messages.php.document.coupon_applique', null, [
				round($coupon_reduction->valeur, 2) . ($coupon_reduction->type_de_reduction === 1 ? '%' : maquette('devise_application_symbole'))
			]);

		$fonctionnalite_colonnes = $management->est_une_vente() ? fonctionnalite('documents_colonnes_a_afficher_vente') : fonctionnalite('documents_colonnes_a_afficher_achat');

		$informations_pour_document = [

            'type_element' => $type_element,
			'structure' => $donnees_fiche['structure'],
			'listes_sur_fiche' => $donnees_fiche['listes_sur_fiche'],
			'watch_pour_vuejs' => $donnees_fiche['watch_pour_vuejs'],
			'options_fil_ariane' => $donnees_fiche['options_fil_ariane'],
			'colonnes_articles' => $colonnes_articles,
			'colonnes_articles_lignes_entieres' => $management->colonnes_articles_lignes_entieres(),
			'colonnes_articles_enregistrement' => $colonnes_articles_enregistrement,
			'options_articles' => $management->retourne_options_articles(),
            'colonne_options_lignes_diverses' => $management->retourne_options_lignes_diverses(),
            'options_lignes_divers' => $options_lignes_divers,
            'options_lignes_divers_champs_supplementaires_enregistrement' => $options_lignes_divers_champs_supplementaires_enregistrement,
			'articles_du_document' => $articles_du_document,
            'articles_supprimes' => $articles_supprimes,
			'management' => $management,
            'lignes_divers' => $lignes_divers_document,
			'recurrence' => $recurrence,
			'documents_lies' => $documents_lies,
			'documents_par_recurrence' => $documents_par_recurrence,
			'modes_de_paiement' => management('paiement')->champ('mode_paiement_id')->valeurs_possibles,
			'comptes_bancaires' => management('paiement')->champ('compte_bancaire_id')->valeurs_possibles,
			'champs' => $champs,
			'totaux' => $calcule_total_document,
			'modeles_impressions' => $recupere_documents_pour_impressions,
			'transformations_possibles' => $transformations_possibles,
			'modeles_de_relances' => $modeles_de_relances,
			'verification_approbation' => $verification_approbation_neccessaire,
			'verification_document_concerne_par_approbation' => $verification_document_concerne_par_approbation,
			'verification_demande_approbation_concerne' => $verification_demande_approbation_concerne,
            'taux_de_tva' => $taux_de_tva,
            'noms_unites' => $noms_unites,
            'modeles_commentaire' => $modeles_commentaire,
            'modeles_commentaire_vierge' => $modeles_commentaire_vierge,
			'coupon_reduction' => $coupon_reduction,
			'fonctionnalite_colonnes' => $fonctionnalite_colonnes,
		];

		temps_execution('document_controller::recuperation des donnees', 1);



		return view('eden::formulaires.creer_document', $informations_pour_document);
	}

	/**
	 *
	 * Crée une nouvelle facture via un projet, et c'est forcément une facture d'avancement
	 *
	 * Pour simplifier le code, cette méthode va directement générer la facture et va rediriger le client vers la facture ainsi créée
	 *
	 */
	public function creer_facture_avancement_depuis_projet($projet_id) {

        $facture_avancement_non_valide = modele('facture_vente')
            ->where(function($requete){
                $requete->whereNull('valide');
                $requete->orWhere('valide','0');
            })->where('projet_id',$projet_id)->where('type_facture',1)->first();

        if(!empty($facture_avancement_non_valide))
            return redirect()->back()->with(['erreur' => traduction('messages.php.document.facture_avancement_proforma_en_cours',null,[table_libre('projet')->element])]);

		log_eden('Document_controller::creer_facture_avancement_depuis_projet::debut');

		// on récupère le premier devis du projet
		$devis = modele('devis_vente')->where('projet_id', $projet_id)->where('accepte', 1);

        $devis = management('projet', $projet_id)->requete_devis_pour_facture_avancement($devis);

        $devis = $devis->first();

		if(empty($devis))
            return redirect()->back()->with(['erreur' =>traduction('messages.php.document.devis_non_accepte_sur_projet')]);

		$informations_facture = array(

			'date' => date('Y-m-d'),
			'date_de_reglement' => date('Y-m-d'),
			'projet_id' => $devis->projet_id,
			'client_id' => $devis->client_id,
			'type_facture' => 1,
		);

		$devis = modele('devis_vente')->where('projet_id', $projet_id)->where('accepte', 1);

        $devis = management('projet', $projet_id)->requete_devis_pour_facture_avancement($devis);

        $devis = $devis->get();

		log_eden('Document_controller::creer_facture_avancement_depuis_projet::selection des devis');

		$ligne = 0;
		$position = 0;

		$articles = array();
		$lignes_divers = array();

		foreach($devis as $un_devis) {

			$management_element_origine = management('devis_vente', $un_devis->id);

			// maintenant, il faut créer le tableau des articles, en cumulant tous les articles du devis
			$articles_du_document = $management_element_origine->lignes_du_document_pour_saisie('facture_vente', false);

            $ajout_ligne_pour_document_actuel = $ligne;

            // Si la fonctionnalité est activée on ajoute pour chaque Devis source une ligne divers de type titre avec comme texte la référence du Devis
            if(fonctionnalite('gescom_afficher_titres_separations_facturation_avancement') === true) {

                //On récupère la ligne du dernier article si elle existe pour positioner correctement la ligne divers
                $ligne_dernier_article = 0;
                if (!empty($articles))
                    $ligne_dernier_article = $articles[count($articles)]['ligne'];

                //On récupère la position de la dernière ligne divers si elle existe pour positioner correctement la nouvelle ligne divers

                if (!empty($lignes_divers) && !empty($articles) && $articles[count($articles)]['ligne'] === $lignes_divers[count($lignes_divers) - 1]['ligne']) {
                    $position++;
                }
                else
                    $position = 0;

                $ligne_article = array(

                    'ligne' => $ligne_dernier_article,
                    'nom' => 'Devis ' . $un_devis->reference_document,
                    'type_ligne' => 'titre',
                    'type' => 'titre',
                    'position' => $position,
                );

                $lignes_divers[] = $ligne_article;

            }

			foreach($articles_du_document as $article) {

				if(isset($article->article_id) && !empty($article->article_id)) {

                    $coefficient_article = 0;
                    $coefficient_devis = 0;
                    $coefficient_regroupement = 0;
                    if(!empty($article->coefficient_article))
                        $coefficient_article = $article->coefficient_article;
                    if(!empty($article->coefficient_devis))
                        $coefficient_devis = $article->coefficient_devis;
                    if(!empty($article->coefficient_regroupement))
                        $coefficient_regroupement = $article->coefficient_regroupement;

                    foreach ($article->nomenclature as $nomenclature){

                        $nomenclature->tarif = $nomenclature->tarif * (100 + $coefficient_article) / 100 * (100 + $coefficient_regroupement) / 100 * (100 + $coefficient_devis) / 100;

                        if(empty($nomenclature->nomenclature))
                            continue;

                        foreach($nomenclature->nomenclature as $sous_nomenclature){

                            $sous_nomenclature->tarif = $sous_nomenclature->tarif * (100 + $coefficient_article) / 100 * (100 + $coefficient_regroupement) / 100 * (100 + $coefficient_devis) / 100;

                        }

                    }

					$ligne_article = array(

						'article_id' => $article->article_id,
						'ligne' => $article->ligne + $ajout_ligne_pour_document_actuel,
						'designation' => $article->designation,
						'quantite' => $article->quantite,
						'tarif' => $article->tarif * (100 + $coefficient_article) / 100 * (100 + $coefficient_regroupement) / 100 * (100 + $coefficient_devis) / 100,
						'remise' => $article->remise,
						'tva' => $article->tva,
						'afficher_photo' => $article->afficher_photo,
						'masquer_ligne' => $article->masquer_ligne,
						'code_article' => $article->code_article,
						'prix_achat' => $article->prix_achat,
						'nomenclature' => $article->nomenclature,
						'unite' => $article->unite,
						'description' => $article->description,
						'numero_de_serie' => $article->numero_de_serie,
						'numeros_de_lot' => $article->numeros_de_lot,
						'disponibilite' => $article->disponibilite,
						'conditionnement' => json_encode($article->conditionnement),
						'type_element_source' => 'devis_vente',
						'id_element_source' => $article->document_id,
                        'couleur_regroupement' => $article->couleur_regroupement,
						'id_ligne_source' => $article->id,
					);

					// on va chercher l'avancement précédent
					$avancement_precedent = modele('facture_vente_lignes')
						->where('type_element_source', 'devis_vente')
						->where('id_element_source', $article->document_id)
						->where('id_ligne_source', $article->id)
						->orderBy('id', 'desc')
                        ->first();

					if(!empty($avancement_precedent)) {

						$ligne_article['avancement_actuel'] = $avancement_precedent['avancement_actuel'];
						$ligne_article['avancement_precedent'] = $avancement_precedent['avancement_actuel'];
					}
					else {

						$ligne_article['avancement_actuel'] = 0;
						$ligne_article['avancement_precedent'] = 0;
					}

					$articles[$ligne_article['ligne']] = $ligne_article;

					$ligne++;
				}
				else {

                    if($article->type_ligne == 'coefficient')
                        continue;

                    if($lignes_divers[count($lignes_divers) - 1]['ligne'] == $article->ligne + $ajout_ligne_pour_document_actuel)
                        $position++;
                    else
                        $position = 0;

					$ligne_article = array(

						'contenu' => $article->contenu,
						'ligne' => $article->ligne + $ajout_ligne_pour_document_actuel,
						'position' => $position,
						'type' => $article->type,
						'nom' => $article->nom,
						'quantite' => $article->quantite,
						'tarif' => $article->tarif,
						'remise' => $article->remise,
						'tva' => $article->tva,
						'id_style_ligne_document' => $article->id_style_ligne_document,
						'type_remise' => $article->type_remise,
						'format' => $article->format,
						'couleur_regroupement' => $article->couleur_regroupement,
						'type_ligne' => $article->type_ligne,
					);

					$lignes_divers[] = $ligne_article;
				}
			}
		}

		$informations_facture['articles'] = $articles;
		$informations_facture['lignes_divers'] = $lignes_divers;

		log_eden('Document_controller::creer_facture_avancement_depuis_projet::preparation des articles');

		$facture_management = management('facture_vente');

		// éventuellement on ajoute des données (gestion du spécifique)
		$informations_facture = $facture_management->retouche_donnes_pour_creation_facture_avancement_depuis_projet($projet_id, $informations_facture);

		// on enregistre la facture
		$retour = $facture_management->enregistre($informations_facture);

		log_eden('Document_controller::creer_facture_avancement_depuis_projet::enregistrement de la facture');

		if($retour !== true)
            return redirect()->back()->with(['erreur' => $retour]);

		return redirect()->route('document.afficher', ['facture_vente', $facture_management->modele->id]);
	}

	/**
	*
	* Teste l'enregistrement d'un document
	*
	*/
	public function test_enregistrer(Request $formulaire, $type_element) {

		if($type_element == 'facture_vente')
			$donnees = $formulaire->except('_token', 'id', 'input_coupon_reduc_document', 'afficher_pdf_apres_enregistrement');
		else
			$donnees = $formulaire->except('_token', 'id', 'input_coupon_reduc_document', 'coupon_reduction', 'afficher_pdf_apres_enregistrement');

		if(strpos($type_element, '_vente') !== false && !request()->has('cacher_totaux_sur_pdf')) {

			$donnees['cacher_totaux_sur_pdf'] = 0;
		}
		
		if($formulaire->has('id') && !empty($formulaire->get('id'))) {

			$management = management($type_element, $formulaire->input('id'));
		}
		else {

			$management = management($type_element);
		}

		$retour = $management->test_enregistre($donnees);

		if($retour === 'test_ok') {

			return json_encode(['test_succes' => true]);
		}
		else {

			return json_encode(['test_succes' => false, 'erreur' => $retour]);
		}

	}


	/**
	 *
	 * Enregistre que les lignes du document
	 *
	 */
	public function document_enregistrer_lignes(Request $formulaire, $type_element) {

		$management = management($type_element, $formulaire->input('id'));

		$donnees = array();
		$donnees['articles'] = $formulaire->articles;

		$management->enregistre($donnees);

		return json_encode(['success' => true]);
	}


	/**
	*
	* Enregistre un document
	*
	*/
	public function enregistrer(Request $formulaire, $type_element) {

		$paiement_rattache = false;

		log_eden("debut enregistrement dans document controller", 2);

		// On stock les paiements sélectionnées
		if(isset($formulaire->paiement_rattache)) {

			$paiement_rattache = explode(',',$formulaire->paiement_rattache);
		}

		if($type_element == 'facture_vente')
			$donnees = $formulaire->except('_token', 'id', 'input_coupon_reduc_document', 'paiement_rattache', 'afficher_pdf_apres_enregistrement');
		else
			$donnees = $formulaire->except('_token', 'id', 'input_coupon_reduc_document', 'coupon_reduction', 'paiement_rattache', 'afficher_pdf_apres_enregistrement');

		if(strpos($type_element, '_vente') !== false && !request()->has('cacher_totaux_sur_pdf')) {

			$donnees['cacher_totaux_sur_pdf'] = 0;
		}

		// si l'utilisateur a supprimé les lignes divers, on force un tableau vide pour forcer l'enregistrement
		if(!isset($donnees['lignes_divers']))
			$donnees['lignes_divers'] = array();

		if($formulaire->has('id') && !empty($formulaire->get('id'))) {

			$management = management($type_element, $formulaire->input('id'));
		}
		else {

			$management = management($type_element);
		}

		log_eden("après chargement management dans document controller", 2);

		$management->enregistrement_depuis_fiche = true;

		if(!isset($donnees['articles']))
		    $donnees['articles'] = array();

		if(!empty($donnees['type_element_source'])) {

		    $type_element_source = $donnees['type_element_source'];
		    $id_element_source = $donnees['id_element_source'];

			unset($donnees['type_element_source']);
			unset($donnees['id_element_source']);
		}
		else {

			$type_element_source = false;
			$id_element_source = false;
		}

		$retour = $management->enregistre($donnees);

		if($retour !== true)
			return response()->json(['retour' => false, 'erreur' => $retour]);

		// Maintenant que le document est enregistré, on peut rattacher les paiement
		if($paiement_rattache !== false) {

			// On a au moins un paiement a rattacher
			if($paiement_rattache[0] != "") {

				foreach($paiement_rattache as $id_paiement) {

					$paiement = modele('paiement')->where('id',$id_paiement)->first();

					// Si le paiement est deja rattaché, on skip ( double verif )
					if ($paiement->id_document == null) {

						$paiement->id_document = $management->modele->id;
						$paiement->type_element = $type_element;

						$paiement->save();
					}
				}

				// on doit peut être mettre à jour le solde, on vérifie
				$management->enregistre(array());
			}
		}

		// on regarde si on doit automatiquement transférer des paiements
		if($type_element_source !== false) {

			// on transfère les paiements de la commande vers la facture
			if($management->_type_element == 'facture_vente' && $type_element_source == 'commande_vente') {

				$paiements_lies_a_la_commande = modele('paiement')
													->sans_profils()
													->where('type_element', 'commande_vente')
													->where('id_document', $id_element_source)
													->get();

				foreach($paiements_lies_a_la_commande as $paiement) {

					$modifications = array(

						'type_element' => 'facture_vente',
						'id_document' => $management->modele->id,
					);

					$paiement_management = management('paiement', $paiement->id);

					$paiement_management->enregistre($modifications);
				}

				// il faut mettre à jour le solde de la commande
				$document = management('commande_vente', $id_element_source);
				$document->maj_total_document();
			}

			// on transfère les paiements de la commande vers l'acompte
			if($management->_type_element == 'acompte_vente' && $type_element_source == 'commande_vente') {

				$paiements_lies_a_la_commande = modele('paiement')
													->sans_profils()
													->where('type_element', 'commande_vente')
													->where('id_document', $id_element_source)
													->get();

				foreach($paiements_lies_a_la_commande as $paiement) {

					$modifications = array(

						'type_element' => 'acompte_vente',
						'id_document' => $management->modele->id,
					);

					$paiement_management = management('paiement', $paiement->id);

					$paiement_management->enregistre($modifications);
				}

				// il faut mettre à jour le solde de la commande
				$document = management('commande_vente', $id_element_source);
				$document->maj_total_document();
			}

			// on enregistre l'id de l'élément source si c'est un avoir
			if($management->_type_element == 'avoir_vente') {

				if($type_element_source == 'facture_vente')
					$management->enregistre_modele(array('facture_id_source' => $id_element_source));
				elseif($type_element_source == 'acompte_vente')
					$management->enregistre_modele(array('acompte_id_source' => $id_element_source));

				// obligé de l'appeler ici pour gérer les cas de création d'avoir pour
				// que le solde de la facture soit mis à jour dès le 1er enregistrement
				$management->maj_total_document();
			}
		}

		$retour_post_verification = $management->post_verification_document();
		$management->reload_modele();
		$management->charge_valeurs_champs_multiselection();

		$articles_tmp = $management->articles();
		$articles_du_document = $management->lignes_du_document_pour_saisie(false, false, $articles_tmp);
		$recurrence = $management->retourne_recurrence_pour_saisie_document();

		$documents_lies = $management->documents_lies(false, true);
		$documents_par_recurrence = $management->documents_par_recurrence($documents_lies);

		if(fonctionnalite('gescom_afficher_recap_documents_lies_avec_details'))
			service('documents_lies')->ajoute_document_au_tableau_des_documents_lies($documents_lies, $management->_type_element, $management->modele->id, true);

		return response()->json([
			'retour' => true,
			'informations_supplementaires' => $retour_post_verification,
			'modele' => $management->modele ,
			'articles' => $articles_du_document,
			'recurrence' => $recurrence,
			'documents_lies' => $documents_lies,
			'documents_par_recurrence' => $documents_par_recurrence
		]);
		
	}

	/**
	 *
	 * Valide un document
	 *
	 */
	public function valider($type_element, $id) {

		// on va chercher la facture
		$management = management($type_element, $id);

		$retour = $management->valide();

        if($retour !== true)
            return redirect()->back()->withErrors($retour)->withInput();

		return redirect()->route('document.afficher', array($type_element, $id));
	}

	/**
	*
	* Accepte un document
	*
	*/
	public function accepter($type_element, $id) {

		// on va chercher la facture
		$management = management($type_element, $id);

		$retour = $management->accepte();

		if(is_string($retour))
			return response()->json([
				'retour' => false, 
				'erreur' => $retour
			]);
		
		if(fonctionnalite('gescom_creation_commande_automatique_validation_devis'))
			return response()->json([
				'retour' => true,
				'redirection' => route('document.afficher', array($retour->_type_element, $retour->modele->id))
			]);

		$articles_tmp = $management->articles();
		$articles_du_document = $management->lignes_du_document_pour_saisie(false, false, $articles_tmp);
		$recurrence = $management->retourne_recurrence_pour_saisie_document();
		$documents_lies = $management->documents_lies(false, true);
		$documents_par_recurrence = $management->documents_par_recurrence($documents_lies);

		return response()->json([
				'retour' => true,
				'modele' => $management->modele,
				'articles' => $articles_du_document,
				'recurrence' => $recurrence,
				'documents_lies' => $documents_lies,
				'documents_par_recurrence' => $documents_par_recurrence
		]);
	}

	/**
	 *
	 * Annuler un document
	 *
	 */
	public function annuler($type_element, $id) {

        if($type_element != 'commande_vente')
            return response()->json(['retour' => traduction('messages.php.document.documents_ne_sont_pas_commandes_ventes')]);

		$management = management($type_element, $id);

		$retour = $management->enregistre(['annule' => 1]);

        return response()->json(['retour' => $retour]);
	}

	/**
	 *
	 * Annuler un document
	 *
	 */
	public function annuler_devis($type,$id) {

        $type_element = 'devis_'.$type;;

		// on va chercher la facture
		$management = management($type_element, $id);

		$retour = $management->annule_devis();

        if($retour !== true)
            return redirect()->back()->withErrors($retour)->withInput();

		return redirect()->route('document.afficher', array($type_element, $id));
	}

	/**
	 *
	 * Refuser un document
	 *
	 */
	public function refuser($type,$id) {

        $type_element = 'devis_'.$type;

		// on va chercher la facture
		$management = management($type_element, $id);

		$retour = $management->refuse();

        if($retour !== true)
            return redirect()->back()->withErrors($retour)->withInput();

		return redirect()->route('document.afficher', array($type_element, $id));
	}

	/**
	 *
	 * Expédier un document (une commande)
	 *
	 */
	public function expedier($type_element, $id) {

		// on va chercher la facture
		$management = management($type_element, $id);

		$retour = $management->expedie();

        if($retour !== true)
            return redirect()->back()->withErrors($retour)->withInput();

		return redirect()->route('document.afficher', array($type_element, $id));
	}

	/**
	 *
	 * Enregistre que tous les articles ont été commandés ou sont en stock (une commande)
	 *
	 */
	public function commande_fournisseur_realisee($id) {

		// on va chercher la facture
		$management = management('commande_vente', $id);

		$retour = $management->commande_fournisseur_realisee();

        if($retour !== true)
            return redirect()->back()->withErrors($retour)->withInput();

		return redirect()->route('document.afficher', array('commande_vente', $id));
	}

	/**
	 *
	 * Enregistre que tous les articles sont dispo pour expédition (une commande)
	 *
	 */
	public function commande_fournisseur_recue($id) {

		// on va chercher la facture
		$management = management('commande_vente', $id);

		$retour = $management->commande_fournisseur_recue();

        if($retour !== true)
            return redirect()->back()->withErrors($retour)->withInput();

		return redirect()->route('document.afficher', array($type_element, $id));
	}

	/**
	 *
	 * Annule statut réglé pour un document
	 *
	 */
	public function annule_reglement($type_element, $id) {

		// on va chercher la facture
		$management = management($type_element, $id);

		$retour = $management->annule_reglement();

		return redirect()->route('document.afficher', array($type_element, $id));
	}

	/**
	 *
	 * Valide statut réglé pour un document
	 *
	 */
	public function valide_reglement($type_element, $id) {

		// on va chercher la facture
		$management = management($type_element, $id);

		$retour = $management->valide_reglement();

		return redirect()->route('document.afficher', array($type_element, $id));
	}

	/**
	 *
	 * Splite le document
	 *
	 * Explication métier :
	 * Parfois on passe une commande chez un même fournisseur pour plusieurs projets clients différents
	 * Du coup il est compliqué d'avoir un suivi propre par projet étant donné qu'il y a une seule commande pour une multitude de projet
	 * Nous avons donc développé cette méthode qui permet de découper / spliter la commande fournisseur en une seule commande par projet
	 * Ce qui simplifie grandement tout le suivi
	 *
	 */
	public function spliter($type_element, $id) {

		$document = management($type_element, $id);
		$articles_par_id_origine = $document->articles()->groupBy('commande_client_id_origine');

		$articles = $document->articles();

		$articles_par_type_element_origine = array();

		foreach($articles as $article) {

			$articles_par_type_element_origine[$article->type_element_origine][$article->commande_client_id_origine][] = $article;

		}

		// on crée une commande fournisseur pour chaque commande client
		foreach($articles_par_type_element_origine as $type_element_origine => $articles_par_id_origine) {

			foreach($articles_par_id_origine as $id_origine => $articles) {

				$management_element_origine = management($type_element_origine, $id_origine);
				$nouveau_document = management($type_element);

				$donnees = array();
				$donnees['date'] = $document->modele->date;
				$donnees['date_de_reception'] = $document->modele->date_de_reception;
				$donnees['fournisseur_id'] = $document->modele->fournisseur_id;
				$donnees['objet'] = $document->modele->objet;
				$donnees['projet_id'] = $management_element_origine->modele->objet;

				$donnees['id_master'] = $id;
				$donnees['articles'] = $articles;

				$erreur = $nouveau_document->enregistre($donnees);

				// on valide la commande fournisseur
				if($erreur === true) {

					$nouveau_document->valide();
				}

			}
		}

		// on supprime la commande d'origine (celle qui regroupe toutes les commandes fournisseurs que l'on vient de créer)
		$document->supprime();


		return redirect()->back()->with('message', traduction('messages.php.document.split_reussi'));
	}

	/**
	 *
	 *
	 * Mise en attente d'un document (devis)
	 *
	 */
	public function mise_en_attente($type,$id) {

        $type_element = 'devis_'.$type;

		// on va chercher la facture
		$management = management($type_element, $id);

		$retour = $management->mise_en_attente();

		return redirect()->route('document.afficher', array($type_element, $id));

	}

	/**
	 *
	 * Récupère les infos du client pour les afficher sur la saisie des documents
	 *
	 * Notamment, l'encours
	 *
	 * En surcharge on pourrait imaginer :
	 *
	 * Les conditions de paiements, des infos de facturation...
	 *
	 */
	public function infos_client($client_id) {

		$management = management('client', $client_id);

		return response()->json($management->infos_client_pour_gestion_commerciale());
	}

	/**
	 *
	 * Récupère les infos du fournisseur pour les afficher sur la saisie des documents
	 *
	 * Notamment, l'encours
	 *
	 * En surcharge on pourrait imaginer :
	 *
	 * Les conditions de paiements, des infos de facturation...
	 *
	 */
	public function infos_fournisseur($fournisseur_id) {

		$management = management('fournisseur', $fournisseur_id);

		return response()->json($management->infos_fournisseur_pour_gestion_commerciale());
	}

	/**
	 *
	 *
	 * Récupère les infos du projet pour les afficher sur la saisie des documents
	 *
	 */
	public function infos_projet($projet_id) {

		$management = management('projet', $projet_id);

		return response()->json($management->infos_projet_pour_gestion_commerciale());
	}

	/**
	 *
	 * Supprime un document depuis la page de création
	 *
	 */
	public function supprimer($type_element, $id_element) {

		// on tente la suppression
		$management = management($type_element, $id_element);

		if(!empty($management->modele->valide))
			$document_valide = true;
		else
			$document_valide = false;

		$retour = $management->supprime();

		if($retour === true) {

			if(in_array($type_element, array('facture_vente', 'acompte_vente', 'facture_achat', 'acompte_achat'))) {

                if($management->est_une_vente())
                    $type_element_avoir = "avoir_vente";
                else
                    $type_element_avoir = "avoir_achat";

                $avoir = $management->documents_lies($type_element_avoir);

                $lien_avoir = "";

                if(!empty($avoir)) {
                    $management_avoir = array_shift($avoir)['management'];
                    $lien_avoir = ' (' . $management_avoir->affiche_lien() . ')';
                }

				if($document_valide && fonctionnalite('gescom_suppression_facture_valide') == 'creer_avoir')
                    return redirect()->route('base_eden.liste.index', array($type_element))->with('message', traduction('messages.php.document.document_supprime_par_avoir') . $lien_avoir);

			}

			// on redirige vers la liste
			return redirect()->route('base_eden.liste.index', array($type_element))->with('message', traduction('messages.php.document.document_supprime'));
		}
		else {

			// y'a une erreur, on redirige vers le formulaire
			return redirect()->route('document.afficher', array($type_element, $id_element))->withErrors([$retour]);
		}

	}

	/**
	*
	* Ajoute un paiement sur un document
	*
	* @param $type_element le type_element du document
	* @param $id_element l'id_element du document
	* @param $formulaire la request avec les données du paiement
	*
	*/
	public function ajouter_paiement($type_element, $id_element, Request $formulaire) {

		$management_document = management($type_element, $id_element);

        // les informations standards
		$informations = array(

			'date' => $formulaire->date,
			'montant_saisi' => $formulaire->montant_saisi,
			'type' => $formulaire->type,
			'mode_paiement_id' => $formulaire->mode_paiement_id,
			'compte_bancaire_id' => $formulaire->compte_bancaire_id,
		);



		// on regarde s'il y a des informations spécifiques à gérer
		$donnees_formulaire = $formulaire->all();

		$champs_libres_paiement = table_libre('paiement')->champs_libres()->get()->pluck('nom_sql')->toArray();

		foreach($donnees_formulaire as $nom_sql => $info) {

			if(empty($info))
				continue;

			if(!in_array($nom_sql, $champs_libres_paiement))
				continue;

			$informations[$nom_sql] = $info;

		}

		// on ajoute le paiement
		$retour = $management_document->ajouter_paiement($type_element, $id_element, $informations);

		$management_document->reload_modele();

        if(strpos($management_document->_type_element, 'vente') !== false) {

			$management_document->modele->contacts_ids = $management_document->modele->contacts()->get()->pluck('id')->toArray();
		}

		if(!empty($management_document->modele)) {

			$management_document->modele->date = formate_date('Y-m-d', $management_document->modele->date);

			if(isset($management_document->modele->date_de_reglement))
				$management_document->modele->date_de_reglement = formate_date('Y-m-d', $management_document->modele->date_de_reglement);
		}

		return response()->json(array(

			'retour' => $retour,
			'paiements' => $management_document->paiements(),
			'document' => $management_document->modele,
		));
	}

    /**
     *
     * Ajoute un paiement existant sur un document
     *
     * @param $type_element le type_element du document
     * @param $id_element l'id_element du document
     * @param $formulaire la request avec l'id du paiement
     *
     */
    public function ajouter_paiement_existant($type_element, $id_element, Request $formulaire) {

		$management = management('paiement', $formulaire->id_paiement);

        $modele = $management->modele;

        if(!empty($modele->type_element) || !empty($modele->id_document)) {

            return response()->json(array('retour' => traduction('messages.php.document.paiement_deja_rapproche')));
        }

        $retour = $management->enregistre([
            'type_element' => $type_element,
            'id_document' => $id_element,
        ]);

        $management_document = management($type_element, $id_element);

        // on met à jour les données du modèle du document
		$management_document->reload_modele();

        if(strpos($management_document->_type_element, 'vente') !== false) {

			$management_document->modele->contacts_ids = $management_document->modele->contacts()->get()->pluck('id')->toArray();
		}

		if(!empty($management_document->modele)) {

			$management_document->modele->date = formate_date('Y-m-d', $management_document->modele->date);

			if(isset($management_document->modele->date_de_reglement))
				$management_document->modele->date_de_reglement = formate_date('Y-m-d', $management_document->modele->date_de_reglement);
		}

        return response()->json(array(

            'retour' => $retour,
            'paiements' => $management_document->paiements(),
            'paiements_non_rattache' => $management_document->paiements_non_rattache(),
            'document' => $management_document->modele,
        ));
    }

	/**
	*
	* Supprime un paiement sur un document
	*
	* @param $id l'id du paiement en question
	*
	*/
	public function supprimer_paiement($id) {

		$management_paiement = management('paiement', $id);

		$management_document = management($management_paiement->modele->type_element, $management_paiement->modele->id_document);

		// on supprime le paiement
		$retour = $management_paiement->supprime();

		// on met à jour les données du modèle du document
		$management_document->reload_modele();

        if(strpos($management_document->_type_element, 'vente') !== false) {

			$management_document->modele->contacts_ids = $management_document->modele->contacts()->get()->pluck('id')->toArray();
		}

		if(!empty($management_document->modele)) {

			$management_document->modele->date = formate_date('Y-m-d', $management_document->modele->date);

			if(isset($management_document->modele->date_de_reglement))
				$management_document->modele->date_de_reglement = formate_date('Y-m-d', $management_document->modele->date_de_reglement);
		}

		return response()->json(array(

			'retour' => $retour,
			'paiements' => $management_document->paiements(),
			'document' => $management_document->modele,
		));
	}

	/**
	*
	* Détache un paiement sur un document
	*
	* @param $id l'id du paiement en question
	*
	*/
	public function detacher_paiement($id) {

		$management_paiement = management('paiement', $id);

		$management_document = management($management_paiement->modele->type_element, $management_paiement->modele->id_document);

		$modifications_paiement = array(
			'id_document' => null,
			'type_element' => null,
		);

		$management_paiement->enregistre_modele($modifications_paiement);

		// on met à jour les données du modèle du document
		$management_document->reload_modele();

		$management_document->maj_total_document();

        if(strpos($management_document->_type_element, 'vente') !== false) {

			$management_document->modele->contacts_ids = $management_document->modele->contacts()->get()->pluck('id')->toArray();
		}

		if(!empty($management_document->modele)) {

			$management_document->modele->date = formate_date('Y-m-d', $management_document->modele->date);

			if(isset($management_document->modele->date_de_reglement))
				$management_document->modele->date_de_reglement = formate_date('Y-m-d', $management_document->modele->date_de_reglement);
		}

		return response()->json(array(

			'retour' => true,
			'paiements' => $management_document->paiements(),
			'document' => $management_document->modele,
		));
	}

	/**
	 *
	 * Calcule le total d'un document via un tableau d'articles passé en paramètres
	 *
	 * @param $formulaire array ['articles' => [['quantite' => 1, 'tarif' => 4, 'tva' => 10], [...]]]
	 *
	 * @return array [$total_ht, $total_ttc, $total_ttc_apres_remise]
	 *
	 */
	public function calculer_total(Request $formulaire) {

		// c'est vide
		if(!is_array($formulaire->articles))
			return array('ht' => 0, 'ht_hors_frais_de_livraison' => 0, 'ttc' => 0, 'ttc_apres_remise' => 0, 'frais_de_transport' => 0);

		// on va calculer le total

		// on prend facture_vente par défaut (cette méthode n'est pas basée sur un type élément en particulier)
		$management = management($formulaire->type_element);

		if($management->modele == null)
			$management->modele = modele($formulaire->type_element);

		// on met à jour remise_globale et remise_globale_type si nécessaire
		if($formulaire->remise_globale != 0){

			$management->modele->remise_globale = $formulaire->remise_globale;
			$management->modele->remise_globale_type = $formulaire->remise_globale_type;
		}

		if(!empty($formulaire->ecart_gestion_ttc))
			$management->modele->ecart_gestion_ttc = $formulaire->ecart_gestion_ttc;

		if(!empty($formulaire->coupon_reduction))
			$management->modele->coupon_reduction = $formulaire->coupon_reduction;

		if(!empty($formulaire->client_id))
			$management->modele->client_id = $formulaire->client_id;

		if(!empty($formulaire->date))
			$management->modele->date = formate_date('Y-m-d', $formulaire->date);

		if(!empty($formulaire->type_facture))
			$management->modele->type_facture = $formulaire->type_facture;

		$mode_calcul = config('eden.mode_calcul_gescom');

		// pour les achats, on est forcément en HT
		if($management->est_un_achat())
			$mode_calcul = 'ht';

		$articles = $formulaire->articles;

		if(strtolower($mode_calcul) == 'ttc') {

			// on divise tous les prix par la TVA car les tarifs ont été saisis en TTC

			foreach($articles as $id => $article) {

				if(empty($article['tva']))
					$article['tva'] = 0;

				$articles[$id]['tarif'] = $article['tarif'] / ((100 + $article['tva']) / 100);
			}
		}

		$frais_de_port_saisie = null;

		if(fonctionnalite('frais_de_port_sur_documents_commerciaux') === true) {

			if(!empty($formulaire->frais_de_port_saisie))
				$frais_de_port_saisie = floatval($formulaire->frais_de_port_saisie);
		}

		$totaux = $management->{'calcule_suivant_methode_'.$mode_calcul}($articles, $frais_de_port_saisie);

		return array(

			'ht' => $totaux['avant_remise']['ht'],
			'ttc' => $totaux['avant_remise']['ttc'],
			'ttc_apres_remise' => $totaux['ttc'],
			'ht_apres_remise' => $totaux['ht'],
			'eco_contribution' => $totaux['eco_contribution'],
			'eco_contribution_inclus' => $totaux['eco_contribution_inclus'],
			'somme_pa_articles' => $totaux['somme_pa_articles'],
            'somme_pu_articles' => $totaux['somme_pu_articles'],
			'marge_brute_montant' => $totaux['marge_brute_montant'],
            'marge_nette_montant' => $totaux['marge_nette_montant'],
            'marge_brute_pourcentage' => $totaux['marge_brute_pourcentage'],
            'marge_nette_pourcentage' => $totaux['marge_nette_pourcentage'],
			'ht_hors_frais_de_livraison' => $totaux['ht_hors_frais_de_livraison'],
			'frais_de_transport' => $totaux['frais_de_transport'],
			'par_ligne' => $totaux['par_ligne'],
			'par_ligne_tarif_net' => $totaux['par_ligne_tarif_net'],
			'par_ligne_tarif_article' => $totaux['par_ligne_tarif_article'] ?? null,
			'par_ligne_marge_brute_montant' => $totaux['par_ligne_marge_brute_montant'],
            'par_ligne_marge_brute_pourcentage' => $totaux['par_ligne_marge_brute_pourcentage'],
            'par_ligne_marge_nette_montant' => $totaux['par_ligne_marge_nette_montant'],
            'par_ligne_marge_nette_pourcentage' => $totaux['par_ligne_marge_nette_pourcentage'],
			'par_ligne_ttc' => $totaux['par_ligne_ttc'],
			'total_option' => $totaux['total_option'] ?? [],
            'par_tva' => $totaux['par_tva'],
		);
	 }

	/**
	 *
	 * Totaux d'un document déjà enregistré (dont la ventilation de TVA en 'par_tva'),
	 * à partir de son seul identifiant, contrairement à calculer_total qui recalcule
	 * à la volée depuis les lignes transmises par le formulaire de saisie.
	 *
	 */
	public function totaux($type_element, $id_element) {

		if(!in_array($type_element, \App\Eden\Variables::$documents_gescom))
			return response()->json(['retour' => false]);

		$management = management($type_element, $id_element);

		if(empty($management->modele))
			return response()->json(['retour' => false]);

		return response()->json(array_merge(['retour' => true], $management->calcule_total_document()));
	}

	/**
	 *
	 * On calcule la date de règlement en fonction de la date de facturation & du délai de paiement
	 *
	 */
	public function calcule_date_reglement(Request $formulaire) {

		$date_facturation = formate_date('Y-m-d', $formulaire->date_facturation);
		$modalite_paiement_id = $formulaire->modalite_paiement_id;

		$modalite_paiement_management = management('modalite_paiement', $modalite_paiement_id);

		$date_de_reglement = $modalite_paiement_management->calcule_date_reglement($date_facturation);

		return response()->json(array(
			'en' => formate_date('Y-m-d', $date_de_reglement),
			'fr' => formate_date('Y-m-d', $date_de_reglement)
		));
	}

	/**
	 *
	 * On calcule la date d'expiration en fonction de la date de devis & de la config
	 *
	 */
	public function calcule_date_expiration() {

		$date_devis = formate_date('Y-m-d', request()->date);

		$date_expiration = date('Y-m-d', strtotime($date_devis.' +'.fonctionnalite('delai_expiration_devis').' days'));

		return response()->json(array('en' => formate_date('Y-m-d', $date_expiration), 'fr' => formate_date('Y-m-d', $date_expiration)));
	}

	/**
	 *
	 *
	 * Retourne un PDF selon sa version
	 *
	 */
	public function afficher_pdf_versionning($id) {

		$versionning = modele('versionning_document', $id);

		return response()->download(storage_path('app/'.$versionning->url_document));
	}

	/**
	 *
	 * Retourne un tableau avec la liste des familles et sous familles, ainsi que les articles pour la préselection des articles en gescom
	 *
	 */
	public function recupere_catalogue_articles() {

		$articles = modele('article')->where(function($query) {
			$query->where('archive', 0);
			$query->orWhereNull('archive');
		})->get();

		$articles_par_famille = array();

		foreach($articles as $article) {

			if(!isset($articles_par_famille[$article->famille_id]))
				$articles_par_famille[$article->famille_id] = array();

			$articles_par_famille[$article->famille_id][] = $article;
		}

		$catalogue = array();

		$familles = modele('famille')->where('parent_id', 0)->orderBy('ordre')->get();

		foreach($familles as $index => $famille) {

			$catalogue[$index] = management('famille')->contenu_famille($famille, $articles_par_famille);

			if(empty($catalogue[$index]['sous_familles']) && empty($catalogue[$index]['articles']))
				unset($catalogue[$index]);
		}

		return $catalogue;
	}

	/**
	 *
	 * Retourne un article sélectionné pour un document (gestion des packs d'articles, des tarifs par client, par entité, par palier...)
	 *
	 */
    /**
     *
     * Retourne un article sélectionné pour un document (gestion des packs d'articles, des tarifs par client, par entité, par palier...)
     *
     */
    public function recupere_article_pour_document($article_id, $type_element, Request $request) {
        return response()->json(
            management($type_element)->article_pour_document($article_id,$request->all())
        );
    }

	/**
	 *
	 * Retourne le PDF basé sur le modèle
	 *
	 */
	public function impression_sur_mesure($type_element, $id, $modele_doc) {

		$management = management($type_element, $id);

		return $management->creation_document_pdf_sur_mesure($modele_doc);
	}

	/**
	 *
	 * Imprimer des documents en masse
	 *
	 * @todo faire la gestion des erreurs
	 *
	 */
	public function reception_lignes_en_masse(Request $formulaire) {

		$resultat = array('succes' => 0, 'erreurs' => array());

        $commandes_achats_lignes = modele('commande_achat_lignes')->whereIn('id', $formulaire->ids_element)->get()->keyBy('id');

        $id_commandes_achats = $commandes_achats_lignes->pluck('document_id');

        $commandes_achats = modele('commande_achat')->whereIn('id', $id_commandes_achats)->get()->keyBy('id');

		foreach($formulaire->ids_element as $id_commande_achat_ligne) {

            $commande_achat_ligne = $commandes_achats_lignes[$id_commande_achat_ligne];

            $commande_achat = $commandes_achats[$commande_achat_ligne->document_id];

            $management = management('commande_achat', $commande_achat->id, $commande_achat);

			$management->enregistre_reception_ligne($commande_achat_ligne);

			$resultat['succes']++;

		}

		return response()->json(array('retour' => true, 'resultat' => $resultat));
	}

	/**
	 *
	 * Imprimer des documents en masse du module commerce
	 *
	 */
	public function imprimer_en_masse_documents_commerce(Request $formulaire) {

		$documents = json_decode($formulaire->documents);

		$merger = \PDFMerger::init();

		foreach($documents as $document) {

			$devis_vente = $document->devis_vente;
			$commande_vente = $document->commande_vente;
			$facture_vente = $document->facture_vente;
			$bl_vente = $document->bl_vente;
			$avoir_vente = $document->bl_vente;
			$acompte_vente = $document->bl_vente;
		}

		//@todo refactoring lorsque le test est ok
		foreach($devis_vente as $id_devis_vente) {

			$modele = modele('devis_vente', $id_devis_vente);
			$merger->addPDF(storage_path('app/'.$modele->pdf));
		}

		foreach($facture_vente as $id_facture_vente) {

			$modele = modele('facture_vente', $id_facture_vente);
			$merger->addPDF(storage_path('app/'.$modele->pdf));
		}

		foreach($commande_vente as $id_commande_vente) {

			$modele = modele('commande_vente', $id_commande_vente);
			$merger->addPDF(storage_path('app/'.$modele->pdf));
		}

		foreach($bl_vente as $id_bl_vente) {

			$modele = modele('bl_vente', $id_bl_vente);
			$merger->addPDF(storage_path('app/'.$modele->pdf));
		}

		foreach($avoir_vente as $id_avoir_vente) {

			$modele = modele('avoir_vente', $id_avoir_vente);
			$merger->addPDF(storage_path('app/'.$modele->pdf));
		}

		foreach($acompte_vente as $id_acompte_vente) {

			$modele = modele('acompte_vente', $id_acompte_vente);
			$merger->addPDF(storage_path('app/'.$modele->pdf));
		}


		$merger->merge();

		$merger->download('documents.pdf');
	}

	/**
	 *
	 * Fusionner des documents en masse du module commerce
	 *
	 */
	public function fusionner_documents_en_masse(Request $formulaire) {

        //Gestion des vues
        $type_element= service('vue_sql')->recupere_type_element($formulaire->type_element);

		//on vérifie si les documents sont valides
		foreach($formulaire->ids as $id_document) {

			$management = management($type_element, $id_document);

			if($management->modele->valide != 1)
				return response()->json(['retour' => false, 'message' => traduction('messages.php.document.document_non_valide',null,[$management->modele->id])]);
		}

		$element_fusion = management($type_element)->fusionner_documents($formulaire);

        if(!is_object($element_fusion))
            return response()->json(['retour' => false, 'message' => $element_fusion]);

		// si les documents sont valides, on les supprime
		foreach($formulaire->ids as $id_document) {

			 $management = management($formulaire->type_element, $id_document);
			 $management->supprime();
		}


		return response()->json(['retour' => true]);
	}

	/**
	 *
	 * Valider des documents en masse
	 *
	 */
	public function valider_en_masse(Request $formulaire) {

		$documents = $formulaire->ids;

		$parametre_validation = parametre('validation_en_masse_document');

        while($parametre_validation !== null && round(abs(strtotime(date('Y-m-d H:i:s')) - strtotime($parametre_validation)) /60,2) < 10){
            sleep(5);
            $parametre_validation = parametre('validation_en_masse_document');
        }

        parametre('validation_en_masse_document',date('Y-m-d H:i:s'));

		//Gestion des vues
        $type_element = service('vue_sql')->recupere_type_element($formulaire->type_element);

		$retours = array();

		foreach($documents as $id_document) {

			$management = management($type_element, $id_document);
			$retour = $management->valide();

            if($retour !== true)
			    $retours[] = $retour;
		}

		parametre_supprimer('validation_en_masse_document');

        if(!empty($retours))
            return response()->json(array('retour' => false,'message' => implode("\n",$retours)));

		return response()->json(array(true, $retours));
	}

	/**
	 *
	 * Export SEPA
	 *
	 */
	public function export_sepa_en_masse(Request $formulaire) {

		$documents = $formulaire->ids;

        //Gestion des vues
        $type_element= service('vue_sql')->recupere_type_element($formulaire->type_element);

		$documents_a_retourner = management($type_element)->genere_export_sepa($documents);

		// le compte bancaire SEPA n'est pas paramétré (ou autre erreur) : on retourne le message
		if(!is_array($documents_a_retourner)) {

			return response()->json(['retour' => false, 'message' => $documents_a_retourner]);
		}

		// Si il y a plus d'un document, on retourne une fichier zip
		if(count($documents_a_retourner) > 1) {

			$nom_zip = 'export_sepa_'.time().'.zip';

            $zip = new ZipArchive();

            if ($zip->open(storage_path('app/public/'.$nom_zip), ZIPARCHIVE::CREATE | \ZIPARCHIVE::OVERWRITE ) === TRUE) {

                foreach ($documents_a_retourner as $document)
                    $zip->addFile($document, basename($document));

            }

            $zip->close();

			return response()->json(['retour' => true, 'chemin' => \URL::to('storage/'.$nom_zip)]);
		}

		// un seul document généré : on le copie dans le stockage public pour pouvoir le télécharger
		$nom_fichier = basename($documents_a_retourner[0]);

		copy($documents_a_retourner[0], storage_path('app/public/'.$nom_fichier));

		return response()->json(['retour' => true, 'chemin' => \URL::to('storage/'.$nom_fichier)]);
	}

	/**
	 *
	 * Relance PDF
	 *
	 */
	public function export_relance_pdf_en_masse(Request $formulaire) {

		$modele_a_utiliser = $formulaire->form['modele_pdf'];
		$documents = $formulaire->parametres['ids_elements'];

		$nom_merge = management('facture_vente')->genere_export_relance_pdf($documents,$modele_a_utiliser);

        return response()->json(['retour' => true, 'chemin' => \URL::to('storage/recouvrement/'.$nom_merge)]);
	}

	/**
	 *
	 * Accepter des documents en masse
	 *
	 */
	public function accepter_en_masse(Request $formulaire) {

		$documents = $formulaire->ids;

		$retours = array();

        //Gestion des vues
        $type_element = service('vue_sql')->recupere_type_element($formulaire->type_element);

		foreach($documents as $id_document) {

			$management = management($type_element, $id_document);

			$retours[] = $management->accepte();
		}

		return response()->json(array(true, $retours));
	}

	/**
	 *
	 * Refuser des documents en masse
	 *
	 */
	public function refuser_en_masse(Request $formulaire) {

		$documents = $formulaire->ids;

        //Gestion des vues
        $type_element = service('vue_sql')->recupere_type_element($formulaire->type_element);

		$retours = array();

		foreach($documents as $id_document) {

			$management = management($type_element, $id_document);

			$retours[] = $management->refuse();
		}

		return response()->json(array(true, $retours));
	}

	/**
	 *
	 * Annuler des documents
	 *
	 */
	public function annuler_documents(Request $formulaire) {

		log_eden("document_controller::annuler_documents::debut", 2);

        $type_element = "commande_vente";

		log_eden("document_controller::annuler_documents::recupere_type_element", 2);

        //on vérifie si les documents sont valides
		foreach($formulaire->ids as $id_document) {

			$management = management($type_element, $id_document);

			if($management->modele->valide != 1)
				return response()->json(['retour' => false, 'message' => traduction('messages.php.document.document_non_valide',null,[$management->modele->reference_document])]);

			if($management->modele->annule == 1)
				return response()->json(['retour' => false, 'message' => traduction('messages.php.document.document_deja_annule',null,[$management->modele->reference_document])]);
		}

		log_eden("document_controller::annuler_documents::apres boucle", 2);

		if(!isset($formulaire->ids[0]))
			return response()->json(traduction('messages.php.element_introuvable'));

		$retour = management($type_element)->annuler_documents($formulaire, $type_element);

		log_eden("document_controller::annuler_documents::apres appel à annuler documents", 2);

		return response()->json($retour);
	}

    /**
	 *
	 * Facturer des documents
	 *
	 */
	public function facturer_documents(Request $formulaire, $type_element) {

		log_eden("document_controller::facturer_documents::debut", 2);

        //Gestion des vues
        $type_element = service('vue_sql')->recupere_type_element($type_element);

		log_eden("document_controller::facturer_documents::recupere_type_element", 2);

        //on vérifie si les documents sont valides
		foreach($formulaire->ids as $id_document) {

			$management = management($type_element, $id_document);

			if($management->modele->valide != 1)
				return response()->json(['retour' => false, 'message' => traduction('messages.php.document.document_non_valide',null,[$management->modele->reference_document])]);

			/*

			@note frédéric 30/03/2022 => on passe maintenant par les statuts

			if($management->modele->transforme_en_facture == 1)
				return response()->json(['retour' => false, 'message' => 'Le  document '. $management->modele->reference_document .' est déjà facturé.']);
			*/

			if($management->modele->statut == 50)
				return response()->json(['retour' => false, 'message' => traduction('messages.php.document.document_deja_facture',null,[$management->modele->reference_document])]);
		}

		log_eden("document_controller::facturer_documents::apres boucle", 2);

		if(!isset($formulaire->ids[0]))
			return response()->json(traduction('messages.php.element_introuvable'));

		$retour = management($type_element)->facturer_documents($formulaire, $type_element);

		log_eden("document_controller::facturer_documents::apres appel à facturer documents", 2);

		return response()->json($retour);
	}

    /**
     *
     * Commander des documents
     *
     */
    public function commander_documents(Request $formulaire) {

        //on vérifie si les documents sont valides
        foreach($formulaire->ids as $id_document) {

            $management = management('devis_vente', $id_document);

            if($management->modele->valide != 1)
                return response()->json(['retour' => false, 'message' => traduction('messages.php.document.document_non_valide',null,[$management->modele->reference_document])]);

            if($management->modele->transforme_en_commande == 1)
                return response()->json(['retour' => false, 'message' => traduction('messages.php.document.document_deja_facture',null,[$management->modele->reference_document])]);
        }

        if(!isset($formulaire->ids[0]))
            return response()->json(traduction('messages.php.element_introuvable'));

        $retour = management('devis_vente')->commander_documents($formulaire, 'devis_vente');

        return response()->json($retour);
    }

	/**
	 *
	 * Enregistre la réception d'une commande fournisseur
	 *
	 */
	public function commande_achat_recue($id_commande_achat_ligne) {

        $commande_achat_ligne = modele('commande_achat_lignes', $id_commande_achat_ligne);

        $commande_achat = modele('commande_achat', $commande_achat_ligne->document_id);

        $management = management('commande_achat', $commande_achat->id, $commande_achat);

		return $management->enregistre_reception_ligne($commande_achat_ligne);
	}

	/**
	 *
	 * Enregistre la réception d'une commande fournisseur
	 *
	 */
	public function commande_fournisseur_partiellement_recue($id_commande_achat_ligne) {

        $commande_achat_ligne = modele('commande_achat_lignes', $id_commande_achat_ligne);

        $commande_achat = modele('commande_achat', $commande_achat_ligne->document_id);

        $management = management('commande_achat', $commande_achat->id, $commande_achat);

		return $management->enregistre_reception_ligne($commande_achat_ligne, request()->quantite);
	}

	/**
	 *
	 * Retourne les lignes de bl achat qui concernent la réception d'une commande achat
	 *
	 */
	public function retourne_lignes_bl_achat_pour_commande_achat_ligne($id_commande_achat_ligne) {

		$lignes = modele('bl_achat_lignes')->where('id_ligne_source', $id_commande_achat_ligne)->where('type_element_source', 'commande_achat')->get();

		foreach($lignes as $ligne) {

			$ligne->bl_achat = modele('bl_achat', $ligne->document_id);
		}

		return response()->json($lignes);
	}

	/**
	 *
	 * Permet d'envoyer un mail pour faire relire le document (par un collègue ou un N+1)
	 *
	 */
	public function envoyer_email_relecture(Request $request) {

		$formulaire = $request->all();

		$employe = modele('utilisateur')->where('email', $formulaire['employe'])->first();
		$management = management($formulaire['type_element'], $formulaire['id_element']);

		$management->enregistre_relecture($employe);

		// on enregistre la relecture dans les logs
		$management->enregistre_log_demande_de_relecture();

		$retour = $management->envoie_email_pour_relecture($formulaire);

        if($retour === true)
		    return response()->json(array('success' => true));
        else
            return response()->json(array('success' => false, 'retour' => $retour));
	}


	/**
	 *
	 * Permet de relancer une liste d'utilisateurs sur un document gescom
	 *
	 */
	public function relancer_utilisateurs(Request $request, $type_element, $id) {

		$utilisateurs_par_langue = modele('utilisateur')->whereIn('id', $request->utilisateurs_a_relancer)->pluck('email', 'langue')->toArray();

		$management = management($type_element, $id);

		$retour = $management->envoyer_email_de_relance($utilisateurs_par_langue);

        if($retour === true)
		    return response()->json(['succes' => true]);
        else
            return response()->json(['succes' => false, 'message' => $retour]);
	}

	/**
	 *
	 * Applique coupon réduction
	 *
	 */
	public function calcule_coupon_reduction(Request $formulaire) {

		$code = $formulaire->coupon;

		$coupon_reduction = modele('coupon_reduction')->where('code', $code)->first();

		if($coupon_reduction === null) {

			return response()->json(['succes' => false, 'message' => traduction('messages.php.document.coupon_inexistant')]);
		}

		// on vérifie si le coupon de réduction a déjà été utilisé (uniquement usage unique)
		if($coupon_reduction->usage_unique == 1) {

			$coupon_reduction_a_usage_unique = modele('coupon_reduction_client', $coupon_reduction->id)->exists();

			// s'il existe, on retourne une erreur
			if($coupon_reduction_a_usage_unique == true) {

				return response()->json(['succes' => false, 'message' => traduction('messages.php.document.coupon_deja_existant')]);
			}
		}

		$date_facture = strtotime(formate_date('Y-m-d', $formulaire->document['date']));
		$client_id = $formulaire->document['client_id'];
		$articles = $formulaire->articles;

		// on vérifie si le document est une création ou pas
		if(isset($formulaire->document['montant_document_ttc']))
			$total_ttc = $formulaire->document['montant_document_ttc'];
		else
			$total_ttc = management('facture_vente')->calcule_total_document($articles)['solde_ttc'];

		$management_document = management('facture_vente');
		$retour = $management_document->calcule_coupon_reduction($coupon_reduction, $date_facture, $client_id, $total_ttc, $articles);

		return response()->json($retour);

	}




	/**
	 *
	 * Articles via fournisseur : Retourne la liste des articles associés à un fournisseur
	 *
	 */
	public function articles_via_fournisseur(Request $donnees) {

		$donnees = $donnees->all();
		
		$familles = modele('article_fournisseur')
							->join('article', 'article.id', 'article_id')
							->join('famille', 'famille.id', 'famille_id')
							->zero_ou_null('article.inactif')
							->zero_ou_null('article_fournisseur.inactif')
							->where('article_fournisseur.fournisseur_id', $donnees['fournisseur_id'])
							->select('famille.nom', 'famille.id')
							->groupBy('famille.id')
							->get();

		$articles = modele('article')
                            ->select(DB::raw('article.unite as unite_id,seuil_article.*,article_fournisseur.*,article.*,COALESCE(article_fournisseur.tarif,article.prix_d_achat) as tarif'))
							->where('article_fournisseur.fournisseur_id', $donnees['fournisseur_id'])
							->join('article_fournisseur', 'article.id', 'article_fournisseur.article_id')
                            ->leftJoin('seuil_article', 'article.id', 'seuil_article.article_id')
							->zero_ou_null('article_fournisseur.inactif')
							->zero_ou_null('article.inactif')
							->orderBy('article.designation')
							->groupBy('article_fournisseur.id')
							->get();

		// $base_article = modele('article')->get()->keyBy('id');

		$articles_ids = array_keys($articles->keyBy('article_id')->toArray());

		$infos_stock = service('stocks')->details_stocks_par_article($articles_ids);

		$article_management = management('article');

		$articles_a_retourner = collect();

		foreach($articles as $id => $article) {

			$article->id = $article->article_id;
			$article->quantite = 0;

			$article_management = management('article', $article->article_id);
			$article_standard = $article_management->modele;

			// article supprimé ?
			if(empty($article_standard) || $article_standard->inactif == 1) {

				$articles->forget($id);
				continue;
			}

			// on ne peut pas commander une nomenclature, un article frais de port ou un assemblage chez un fournisseur, ça n'a pas de sens
			if(in_array($article_standard->type_article, array(1,2,3))) {

				$articles->forget($id);
				continue;
			}

            if(!empty($article->unite_id))
                $article->unite = modele('article_unite', $article->unite_id)->nom;

            // On récupère le détail du conditionnement
            if(isset($article->conditionnement_id)){

                $conditionnement_detail = modele('conditionnement')->where('id', $article->conditionnement_id)->first();

				if(!empty($conditionnement_detail))
            		$article->conditionnement_affiche = $conditionnement_detail['nom'] . " (" . management('conditionnement')->champ('quantite')->affiche($conditionnement_detail['quantite']) . " " . $article->unite . ")";

            }

			// on va chercher les différentes infos de stock
			if(empty($donnees['entrepot_id'])) {

				$article->stock_actuel = $infos_stock[$article_standard->id]['stock_actuel']['total'] ?? 0;
				$article->stock_reserve = $infos_stock[$article_standard->id]['stock_reserve']['total'] ?? 0;
				$article->stock_disponible = $infos_stock[$article_standard->id]['stock_disponible']['total'] ?? 0;
				$article->stock_achete = $infos_stock[$article_standard->id]['stock_achete']['total'] ?? 0;
				$article->stock_a_terme = $infos_stock[$article_standard->id]['stock_a_terme']['total'] ?? 0;
			}
			else {

				$article->stock_actuel = $infos_stock[$article_standard->id]['stock_actuel']['par_entrepot'][$donnees['entrepot_id']]['total'] ?? 0;
				$article->stock_reserve = $infos_stock[$article_standard->id]['stock_reserve']['par_entrepot'][$donnees['entrepot_id']]['total'] ?? 0;
				$article->stock_disponible = $infos_stock[$article_standard->id]['stock_disponible']['par_entrepot'][$donnees['entrepot_id']]['total'] ?? 0;
				$article->stock_achete = $infos_stock[$article_standard->id]['stock_achete']['par_entrepot'][$donnees['entrepot_id']]['total'] ?? 0;
				$article->stock_a_terme = $infos_stock[$article_standard->id]['stock_a_terme']['par_entrepot'][$donnees['entrepot_id']]['total'] ?? 0;
			}

			if(!isset($nombre_articles_sortis[$article_standard->id]))
				$nombre_articles_sortis[$article_standard->id] = 0;

			$article->vitesse_de_rotation = $article_management->retourne_vitesse_rotation();

			// on va chercher le plus gros mois sur les 12 derniers mois.
			$max_un_mois = 0;
			$date_debut = date('Y-m-d', strtotime('now -30 days'));

			for($i=1; $i<=1; $i++) {

				$date_fin = date('Y-m-d', strtotime($date_debut.' +30 days'));

				$sorties_30_jours = modele('mouvement_de_stock')
											->where('article_id', $article->article_id)
											->zero_ou_null('reserve')
											->where('date', '>=', $date_debut)
											->where('date', '<=', $date_fin)
											->where('quantite', '<', 0)
											->zero_ou_null('reserve')
											->sum('quantite');

				if($sorties_30_jours < $max_un_mois)
					$max_un_mois = $sorties_30_jours;

				$date_debut = $date_fin;
			}

			$article->max_un_mois = $max_un_mois;

			$article->designation = $article_standard->designation;

			if(empty($article->reference)) {

				$article->reference = $article_standard->code_article;
			}

			$article->id = $article->article_id;
			$article->famille_id = $article_standard->famille_id;
			$article->type_article = $article_standard->type_article;

			$articles_a_retourner->push($article);
		}

		if($articles_a_retourner->isNotEmpty())
			management('article')->applique_conditions_commerciales($articles_a_retourner, $donnees);

		$articles_a_retourner = $articles_a_retourner->sortBy('designation');

		return [ 'articles' => $articles_a_retourner->values(), 'familles' => $familles ];
	}

	/**
	 *
	 * Mise à jour du prix d'achat d'une liste d'articles donnée via le bloc de mise à jour des prix sur les commandes achat
	 *
	 */
	public function maj_prix_achat_article(Request $request){

		$articles = $request->post('selection');
        $articles_en_erreur = array();
        $retour = true;

		foreach($articles as $article){

            try{

                if(!empty($article['condition_commerciale_id']))
                    management('condition_commerciale', $article['condition_commerciale_id'])->enregistre(['prix_achat' => $article['nouveau_prix']]);
                else
                    management('article', $article['id'])->enregistre(['prix_d_achat' => $article['nouveau_prix']]);
            } catch(\Exception $erreur){

                $articles_en_erreur[] = $article['designation'];
            }
		}

        if(count($articles_en_erreur) > 0)
            $retour = traduction('messages.php.document.maj_prix_articles_commande_achat.erreur', null, [implode(', ', $articles_en_erreur)]);

		return ['retour' => $retour];
	}

	/**
	 *
	 * Récupère la liste des articles dans les commandes ventes non validées
	 *
	 */
	public function articles_prix_achats_differents(Request $request){

		$articles = $request->post('articles');
		$id_document = $request->post('document_id');
		$type_element = $request->post('type_element');
		$articles_a_retourner = [];

		$date_document = modele($type_element, $id_document)->date ?? null;

		foreach($articles as $index => $article){

			$ligne = modele($type_element.'_lignes')
						->join('article_fournisseur', function($join) use ($type_element){
							$join->on($type_element.'_lignes.article_id', '=', 'article_fournisseur.article_id')
								->on('article_fournisseur.fournisseur_id', '=', $type_element.'_lignes.fournisseur_id_ligne');
						})
						->join('article', 'article.id', '=', 'article_fournisseur.article_id')
						->select($type_element.'_lignes.*', 'article_fournisseur.id as article_fournisseur_id', 'article_fournisseur.reference', 'article.prix_d_achat')
						->where($type_element.'_lignes.id', $article['id'])
						->where('document_id', $id_document)
						->first();

			if(empty($ligne))
				continue;

			$conditions_commerciales = modele('condition_commerciale')
				->where('article_fournisseur_id', $ligne->article_fournisseur_id)
				->where(DB::raw('COALESCE(conditionnement,0)'), $ligne->conditionnement ?: 0)
				->get();

			$condition_retenue = $conditions_commerciales->first(function($condition) use ($date_document){

				if(empty($condition->catalogue_tarif_id))
					return false;

				$catalogue_tarif = modele('catalogue_tarif')->find($condition->catalogue_tarif_id);

				return !empty($catalogue_tarif)
					&& (empty($date_document) || (
						$catalogue_tarif->date_debut <= $date_document
						&& (empty($catalogue_tarif->date_fin) || $catalogue_tarif->date_fin >= $date_document)
					));
			});

			if(empty($condition_retenue))
				$condition_retenue = $conditions_commerciales->first(function($condition){
					return empty($condition->catalogue_tarif_id);
				});

			if(!empty($condition_retenue)){
				$condition_commerciale_id = $condition_retenue->id;
				$prix_achat_old = $condition_retenue->prix_achat;
			}
			else {
				$condition_commerciale_id = null;
				$prix_achat_old = $ligne->prix_d_achat;
			}

			if(round($prix_achat_old ?? 0, 2) == round($ligne->tarif ?? 0, 2))
				continue;

			$ligne_a_envoyer = $ligne->toArray();
			$ligne_a_envoyer['condition_commerciale_id'] = $condition_commerciale_id;
			$ligne_a_envoyer['prix_achat_old'] = $prix_achat_old;

			$articles_a_retourner[] = $ligne_a_envoyer;
		}

		return $articles_a_retourner;
	}

	/**
	 *
	 *
	 * Retourne le modèle de relance avec les bonnes variables
	 *
	 */
	public function transformer_modele_relance($type_element, $id, Request $request) {

		$contenu = management($type_element, $id)->remplace_variable_modele_relance($request->modele);

		return response()->json(['retour' => true, 'contenu' => $contenu]);
	}

	public function generer_modele_relance($type_element, $id, Request $request) {

		$chemin = management($type_element, $id)->genere_pdf_modele_relance($request->modele);

		return response()->json(['retour' => true, 'chemin' => $chemin]);
	}

	/**
	 *
	 * Remplace un article dans une table de lignes
	 *
	 */
	public function remplace_id_article($article_id, $ligne_id, $type_element) {

		// on va chercher la ligne
		$ligne_management = management($type_element.'_lignes', $ligne_id);

		$document_management = management($type_element, $ligne_management->modele->document_id);

		// on vérifie que c'est autorisé
		if(!fonctionnalite('gescom_remplacement_article')[$type_element])
			return response()->json(array('resultat' => false));

		if($document_management->remplacement_article_autorise() !== true)
			return response()->json(array('resultat' => false));

		$article_modele = modele('article', $article_id);

		// ok on remplace
		$ligne_management->enregistre(array(

			'article_id' => $article_id,
			'code_article' => $article_modele->code_article,
		));

		return response()->json(array('resultat' => true, 'code_article' => $article_modele->code_article));
	}

	/**
	 *
	 * Pré-rempli l'adresse si une adresse par défaut est définie
	 *
	 */
	public function recuperer_adresse_par_defaut(Request $formulaire) {

		if ($formulaire->client_id == null)
			return response()->json(['retour' => false, 'adresse_id' => null]);

		$modele_adresse = modele('adresse')->where('client_id',$formulaire->client_id)->where('adresse_par_defaut',1)->first();

		if ($modele_adresse != null)
			return response()->json(['retour' => true, 'adresse_id' => $modele_adresse->id]);

		else
			return response()->json(['retour' => false, 'adresse_id' => null]);
	}

	/**
	 *
	 * Pré-rempli l'adresse si une adresse par défaut est définie
	 *
	 */
	public function recuperer_adresse_par_defaut_fournisseur(Request $formulaire) {

		if ($formulaire->fournisseur_id == null)
			return response()->json(['retour' => false, 'adresse_id' => null]);

		$modele_adresse = modele('adresse')->where('fournisseur_id',$formulaire->fournisseur_id)->where('adresse_par_defaut',1)->first();

		if ($modele_adresse != null)
			return response()->json(['retour' => true, 'adresse_id' => $modele_adresse->id]);

		else
			return response()->json(['retour' => false, 'adresse_id' => null]);
	}

    /**
     *
     * Passe le statut d'un document à A envoyer
     *
     */
    public function indique_document_comme_envoye($id) {

        // on va chercher la facture
        $management = management('facture_vente', $id);

        $retour = $management->indique_document_comme_envoye();

        if($retour !== true)
            return redirect()->back()->withErrors($retour)->withInput();

        return redirect()->route('document.afficher', array($type_element, $id));
    }

    /**
     *
     * Passe le statut d'un document à Accusé de récéption
     *
     */
    public function accuse_de_reception_envoye($id) {

        // on va chercher la facture
        $management = management('commande_vente', $id);

        $retour = $management->accuse_de_reception_envoye();

        if($retour !== true)
            return redirect()->back()->withErrors($retour)->withInput();

        return redirect()->route('document.afficher', array($type_element, $id));
    }

	/**
	 *
	 * >Renvoie les tarifs actuels et les nomenclature des articles donnés afin de les mettre à jour
	 *
	 */
	public function mise_a_jour_tarif(Request $request,$tarif_uniquement = false){

		$articles = $request->articles;
		$type_element = $request->type_element;
        $parametres = $request->parametres;

        return response()->json(management($type_element)->mise_a_jour_tarif($articles,$parametres,$tarif_uniquement === 'true'));

	}

    /**
     * @param Request $request
     * @return mixed
     *
     * Permet de renvoyer le modèle de calculateur
     *
     */
    public function recupere_modele_de_calculateur(Request $request){

        $calculateur = modele('modele_de_calculateur')->where('id', $request->modele_de_calculateur_id)->first();

        return response()->json(['calculateur' => json_decode($calculateur['calculateur'])]);
    }

    /**
     *
     * Permet d'envoyer la signature d'un document à un email
     *
     */
    public function envoyer_demande_signature(Request $formulaire,$type_element,$id_element){

        $formulaire = $formulaire->all();
        // on va chercher le document
        $management = management($type_element, $id_element);

        $retour = $management->envoyer_demande_signature($formulaire['email_signataire']);

        if($retour !== true)
            return json_encode(array('retour' => false,'message' => $retour));

        return json_encode(array('retour' => true));

    }

    public function annulation_totale($id_element, $appel_depuis_liste = false){

        $type_element = 'commande_vente';

        $retour = management($type_element, $id_element)->enregistre(['annule' => 1]);

        if($retour !== true)
            return redirect()->back()->withErrors($retour)->withInput();

        if($appel_depuis_liste)
            return redirect()->back();

        return redirect()->route('document.afficher', array($type_element, $id_element));

    }

    public function verification_articles_supprimes(Request $request){

        $type_element = $request->type_element;
        $id_element = $request->id_element;
        $articles_document = $request->articles_document ?? [];

        if($type_element != "commande_vente")
            return response()->json(['retour' => false]);

        $articles = modele($type_element . '_lignes')->where('document_id', $id_element)->zero_ou_null('nomenclature_ligne_parent')->get();

        $articles_supprimes = false;

        foreach ($articles as $article){

            $article_supprime = true;

            foreach ($articles_document as $article_document){

                if(!isset($article_document['id']))
                    continue;

                if($article_document['id'] == $article['id']) {
                    $article_supprime = false;

                    if(isset($article_document['nomenclature']) && !empty($article_document['nomenclature'])){

                        if(!is_array($article_document['nomenclature']))
                            $article_document['nomenclature'] = json_decode($article_document['nomenclature']);

                        $article_supprime = $this->verification_articles_nomenclature_supprimes(modele($type_element . '_lignes')->where('document_id', $id_element)->where('nomenclature_ligne_parent', $article_document['id'])->get(),$article_document['nomenclature'], $type_element, $id_element);

                    }
                    break;
                }

            }

            if($article_supprime == true) {
                $articles_supprimes = true;
                break;
            }

        }

        return response()->json(['retour' => $articles_supprimes]);

    }

    public function verification_articles_nomenclature_supprimes($articles_bdd, $articles_nomenclature, $type_element, $id_element)
    {

        $articles_supprimes = false;

        foreach ($articles_bdd as $article) {

            $article_supprime = true;

            foreach ($articles_nomenclature as $article_nomenclature) {

                if (isset($article_nomenclature['article_enfant_id'])) {
                    $article_id = $article_nomenclature['article_enfant_id'];
                } else if (isset($article_nomenclature['article_id'])) {
                    $article_id = $article_nomenclature['article_id'];
                } else
                    continue;

                if ($article_id == $article['article_id']) {
                    $article_supprime = false;
                    if (isset($article_nomenclature['nomenclature']) && !empty($article_nomenclature['nomenclature'])) {

                        if (!is_array($article_nomenclature['nomenclature']))
                            $article_nomenclature['nomenclature'] = json_decode($article_nomenclature['nomenclature']);

                        $article_supprime = $this->verification_articles_nomenclature_supprimes(modele($type_element . '_lignes')->where('document_id', $id_element)->where('nomenclature_ligne_parent', $article['id'])->get(), $article_nomenclature['nomenclature'], $type_element, $id_element);

                    }
                    break;
                }
            }

            if ($article_supprime == true) {
                $articles_supprimes = true;
                break;
            }

        }

        return $articles_supprimes;
    }

    /**
     *
     * Crée le pdf et l'affiche pour la prévisualition pré validation
     *
     */
    public function previsualisation_pdf($type_element,$element_id){

        management($type_element,$element_id)->creation_pdf();

        return response()->json(route('base_eden.element.afficher_pdf',[$type_element,$element_id]));
    }

    public function previsualisation_facturation_electronique($type_element,$element_id){

        $management = management($type_element,$element_id);

        if(empty($management->modele->pdf))
            $management->creation_pdf();

        return response()->json([
            'chemin_pdf' => route('base_eden.element.afficher_pdf', [$type_element, $element_id]),
            'xml_facturx' => $management->apercu_facturx($management->modele->pdf),
            'xml_facturx_lisible' => $management->apercu_facturx_lisible($management->modele->pdf),
            'erreurs_facturx' => $management->verification_facturx($management->modele->pdf),
        ]);
    }

    /**
     *
     * Envoi en facturation électronique d'une sélection de documents.
     * Aucun envoi n'est réalisé si un seul document est en défaut.
     *
     */
    public function envoyer_facturation_electronique_en_masse(Request $formulaire) {

        $type_element = service('vue_sql')->recupere_type_element($formulaire->type_element);

        if(!in_array($type_element, ['facture_vente', 'avoir_vente']))
            return response()->json(['retour' => false, 'message' => traduction('messages.php.facture_vente.facturation_electronique_en_masse.type_element_non_gere')]);

        $managements = [];
        $erreurs = [];

        foreach($formulaire->ids ?? [] as $id_document) {

            $management = management($type_element, $id_document);

            $messages = $this->controle_envoi_facturation_electronique($management);

            if(!empty($messages)) {

                $erreurs[] = [
                    'reference' => $management->modele->reference_document,
                    'messages' => $messages,
                ];

                continue;
            }

            $managements[] = $management;
        }

        if(!empty($erreurs))
            return response()->json(['retour' => true, 'erreurs' => $erreurs]);

        foreach($managements as $management)
            $management->enregistre(['statut_facturation_electronique' => 1]);

        return response()->json(['retour' => true, 'erreurs' => [], 'succes' => count($managements)]);
    }

    /**
     *
     * Contrôles préalables à l'envoi : le document doit être validé, pas déjà transmis,
     * rattaché à une entité active en facturation électronique, et son Factur-X doit être conforme
     *
     */
    private function controle_envoi_facturation_electronique($management) {

        if(empty($management->modele->valide))
            return [traduction('messages.php.facture_vente.facturation_electronique_en_masse.document_non_valide')];

        if(!empty($management->modele->statut_facturation_electronique) && $management->modele->statut_facturation_electronique != 3)
            return [traduction('messages.php.facture_vente.facturation_electronique_en_masse.document_deja_envoye')];

        if(empty($management->modele->entite_id) || management('entite', $management->modele->entite_id)->modele->facturation_electronique_active != 1)
            return [traduction('messages.php.facture_vente.facturation_electronique_en_masse.entite_non_active')];

        if(empty($management->modele->pdf))
            $management->creation_pdf();

        if(empty($management->modele->pdf))
            return [traduction('messages.php.facture_vente.facturation_electronique_en_masse.pdf_absent')];

        return collect($management->verification_facturx($management->modele->pdf))->pluck('message')->all();
    }

    /**
     *
     * Permet de récupérer les adresses
     *
     */
    public function mise_a_jour_adresses(Request $request){

        $client_id = $request->client_id ?? null;
        $projet_id = $request->projet_id ?? null;
        $type = $request->type;

        $retour = array();

        if(!empty($client_id)){

            $retour['client'] = modele('adresse')
                ->where('client_id', $client_id)
                ->where(function($r) use ($type) { $r->whereIn('type_adresse', array(0,$type,3))->orWhereNull('type_adresse'); })
                ->get();
        }

        if(!empty($projet_id))
            $retour['projet'] = modele('adresse')
                ->where('projet_id', $projet_id)
                ->where(function($r) use ($type){ $r->whereIn('type_adresse', array(0,$type,3))->orWhereNull('type_adresse'); })
                ->get();

        return response()->json($retour);
		}

		/**
		 *
     * Recherche un article
     *
     */
    public function rechercher_article(Request $formulaire,$type_element) {

        $donnees_pour_recherche = management($type_element)->recupere_articles_disponibles_pour_la_saisie($formulaire->all())->toArray();

        usort($donnees_pour_recherche, function($a, $b){

            if($a['ordre_chaine_complete'] == $b['ordre_chaine_complete']){

                if($a['ordre'] == $b['ordre'])
                    return $a['designation'] > $b['designation'] ? 1 : -1;

                return $a['ordre'] > $b['ordre'] ? -1 : 1;
            }

            return $a['ordre_chaine_complete'] > $b['ordre_chaine_complete'] ? -1 : 1;
        });


        return response()->json($donnees_pour_recherche);
    }

    /**
     *
     * Met à jour l'éco-contribution lors de la transformation, le changement de date ou l'activation du bouton de mises à jours des tarfis
     *
     */
    public function mise_a_jour_eco_contribution(){

        $document = request()->document;
        $articles = request()->articles;
        $type_element = request()->type_element;

        if(!empty($document['id']))
            $management_element = management($type_element,$document['id']);
        else
            $management_element = management($type_element);

        $nouvelle_date = $document['date'];

        $type_element_figeant = fonctionnalite('eco_contribution_document_fixant_valeur');

        if(!empty($articles) && $type_element != $type_element_figeant){

            $documents = $management_element->documents_anterieurs($articles);

            $types_elements = array_keys($documents);

            if(in_array($type_element_figeant,$types_elements))
                return response()->json(
                    array(
                        'maj_possible' => false
                    )
                );
        }

        $eco_contributions_par_article = [];

        $articles_id = array_unique(collect($articles)->pluck('article_id')->toArray());

        $modeles_articles = modele('article')->whereIn('id',$articles_id)->get()->keyBy('id');

        foreach($articles_id as $article_id){

            if(!isset($modeles_articles[$article_id]))
                continue;

            $eco_contributions_par_article[$article_id] = management('article',$article_id,$modeles_articles[$article_id])->eco_contribution($nouvelle_date);
        }

        return response()->json(
            array(
                'eco_contributions_par_article' => $eco_contributions_par_article,
                'maj_possible' => true
            )
        );
    }

    /**
     * @param $type_element
     * @param $element_id
     *
     * Permet de récupérer les paiements non rapprochés en fonction des infos d'un document
     *
     */
    public function paiements_non_rattaches($type_element,$element_id){

        return response()->json(management($type_element,$element_id)->paiements_non_rattache());
    }

    /**
     * @param $type_element
     * @param $element_id
     * @return \Illuminate\Http\JsonResponse
     *
     * Permet de récupérer les fournisseurs disponibles pour les articles du document, à l'ouverture de la modale de transformation
     *
     */
    public function fournisseurs_par_article($type_element,$element_id){

        return response()->json(management($type_element,$element_id)->fournisseurs_par_article());
    }

    /**
     * @param $type_element
     * @param $element_id
     * @return \Illuminate\Http\JsonResponse
     *
     * Permet de mettre à jour les tags d'un élément, ainsi que les transformations possibles
     *
     */
    public function mise_a_jour_tags($type_element,$element_id){

        $management = management($type_element,$element_id);

        return response()->json(array(
            'tags_pour_liste' => explode('<br/>',$management->tags_pour_liste($management->modele)),
            'solde_document_ttc' => $management->modele->solde_document_ttc,
            'regle' => $management->modele->regle,
            'transformations_possibles' => $management->affichage_transformations_possibles(),
            'modeles_de_relances' => $management->affichage_modeles_de_relances(),
        ));
    }

    public function conditions_commerciales(Request $request){

        $ids = $request->ids;
        $parametres = $request->parametres;

        $articles_modeles = modele('article')->whereIn('id',$ids)->get()->keyBy('id');

        management('article')
            ->applique_conditions_commerciales($articles_modeles,$parametres);

        $conditions_commerciales = [];

        foreach($articles_modeles as $article_modele){

            $conditions_commerciales[] = [
                'article_id' => $article_modele->id,
                'conditions_commerciales' => $article_modele->conditions_commerciales
            ];
        }

        return response()->json($conditions_commerciales);
    }

	public function suppression_manuelle_reliquat(){

		$ids_lignes = request()->ids_lignes;

		$lignes = modele('commande_achat_lignes')->whereIn('id', $ids_lignes)->get();
		$etat = request()->suppression_manuelle_reliquat;

		foreach($lignes as $ligne){

			if(($ligne->recue == 1 || $ligne->suppression_manuelle_reliquat == 1) && $etat == 1)
				continue;

			if($ligne->suppression_manuelle_reliquat == 0 && $etat == 0)
				continue;

			$retour = management('commande_achat_lignes', $ligne->id, $ligne)->enregistre([
				'suppression_manuelle_reliquat' => $etat
			]);
		
			if($retour !== true)
				$erreurs[] = $retour;
		}

		$informations_retour = [];

		if(!empty(request()->id_document)){

			$management = management('commande_achat', request()->id_document);

			$management->reload_modele();
			$management->charge_valeurs_champs_multiselection();

			$articles_tmp = $management->articles();
			$articles_du_document = $management->lignes_du_document_pour_saisie(false, false, $articles_tmp);
			$recurrence = $management->retourne_recurrence_pour_saisie_document();

			$documents_lies = $management->documents_lies(false, true);
			$documents_par_recurrence = $management->documents_par_recurrence($documents_lies);

			if(fonctionnalite('gescom_afficher_recap_documents_lies_avec_details'))
				service('documents_lies')->ajoute_document_au_tableau_des_documents_lies($documents_lies, $management->_type_element, $management->modele->id, true);

			$informations_retour = [
				'modele' => $management->modele,
				'articles' => $articles_du_document,
				'recurrence' => $recurrence,
				'documents_lies' => $documents_lies,
				'documents_par_recurrence' => $documents_par_recurrence
			];
		}

		if(isset($erreurs))
			return response()->json(array_merge(['succes' => false, 'message' => implode(', ', $erreurs)], $informations_retour));

		return response()->json(array_merge(['succes' => true], $informations_retour));
	}

}
