<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Liste_libre;
use App\Eden\Models\Parametre;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Recurrence;
use App\Eden\Models\Table_libre;
use App\Eden\Models\Ligne_divers_document;
use App\Eden\Models\Element_piece_jointe;
use App\Eden\Models\Element_image;

use App\Eden\Variables;

use File;
use Illuminate\Support\Str;
use DB;
use Log;
use PDF;
use Codedge\Fpdf\Fpdf\Fpdf;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
// use PDFMerger;

class Document_management extends Element_management {

    public $fiche = false;
    public $mouvements_de_stock = false;
    public $conditionnements = false;
    public $articles_erp = false;
    public $articles_du_document = false;
    public $ids_articles_du_document = [];
    public $ids_conditionnement_du_document = [];
    public $lignes_articles = false;
    public $calcule_transformations_lignes = [];
    public $stocks_a_mettre_a_jour = [];

    public $documents_changement_reliquat = [];

	public $eviter_verification_articles = false;

    public function management_fiche(){

        $id_element = !empty($this->modele->id) ? $this->modele->id : null;

        if(empty($this->fiche)) {
            $this->fiche = fiche($this->_type_element, $id_element);
            $this->fiche->modele = $this->modele;
        }

        if(!empty(moi_extranet())){

            $table_libre = table_libre($this->_type_element);

            if(empty($table_libre->acces_extranet))
                abort(404);
        }

        return $this->fiche;
    }

    /**
	 *
	 * Retourne le management de la ligne
	 *
	 */
	public function management_ligne($id_ligne = false,$modele_ligne = false) {

		return management($this->_type_element.'_lignes', $id_ligne,$modele_ligne);
	}

	/**
	 *
	 * Vérifie que les clients facturés et livrés sont de la même entité
	 *
	 * @param $modifications le tableau des données passé dans la méthode enregistre()
	 *
	 */
	protected function verifie_client_facture_et_livre_sont_de_la_meme_entite($modifications) {

		if(!isset($modifications['client_id']) || !isset($modifications['client_livraison_id']))
			return true;

		$client_facture = modele('client', $modifications['client_id']);
		$client_livre = modele('client', $modifications['client_livraison_id']);

		if(management('client',$modifications['client_livraison_id'])->existe() && $client_facture->entite_id != $client_livre->entite_id)
			return traduction('messages.php.document.client_facture_et_livre_meme_entite');


		return true;
	}

	/**
	 *
	 * Vérifie qu'il n'y a pas de changement d'entité pour le client livré ou facturé
	 *
	 * @param $modifications le tableau des données passé dans la méthode enregistre()
	 *
	 */
	protected function verifie_pas_de_changement_entite($modifications) {

		if(!isset($modifications['client_id']) && !isset($modifications['client_livraison_id']))
			return true;

		// ce document n'existe pas encore
		if(!isset($this->entite_id))
			return true;

		if(isset($modifications['client_id'])) {

			$client = modele('client', $modifications['client_id']);

			if($client->entite_id != $this->entite_id) {

				return traduction('messages.php.document.changement_entite_client');

			}
		}

		if(isset($modifications['client_livraison_id'])) {

			$client = modele('client', $modifications['client_livraison_id']);

			if($client->entite_id != $this->entite_id) {

				return traduction('messages.php.document.changement_entite_client');
			}
		}

		return true;
	}

	/**
	 *
	 * Vérifie si certains articles ne peuvent pas être utilisés
	 *
	 */
	protected function verifie_article_utilisable_que_sur_les_devis($modifications) {

		if($this->_type_element == 'devis_vente')
			return true;

		if(!isset($modifications['articles']))
			return true;


		foreach($modifications['articles'] as $article) {

			$modele_article = modele('article', $article['article_id']);

			if($modele_article->utilisable_que_sur_les_devis == 1)
				return traduction('messages.php.document.article_utilisable_devis',null,[$modele_article->designation]);
		}

		return true;
	}

	/**
	 *
	 * On vérifie si le document est un document négatif
	 *
	 * s'il est nécessaire de l'autoriser pour un client, il suffit de surcharger cette méthode et renvoyer true
	 *
	 * @param $modifications array les modifications envoyées pour enregistrement
	 *
	 * @return true si tout va bien ou un string (message d'erreur) si erreur il y a
	 *
	 */
	public function verifie_si_document_negatif($modifications) {

		// pas de modification d'articles
		if(!isset($modifications['articles']))
			return true;

		$articles = $modifications['articles'];

        foreach($articles as &$article) {

            if (isset($article['type_tarif']) && strtolower($article['type_tarif']) == 'ttc' && !empty($article['type_tarif'])) {

                $article['tarif'] = $article['tarif'] / ((100 + $article['tva']) / 100);
            }
        }

		$totaux = $this->calcule_total_document($articles);

		// on calcule le total
		if((round($totaux['ht'], 2) < 0 || round($totaux['ttc'], 2) < 0) && fonctionnalite('gescom_autoriser_document_negatif') == 'bloquant')
			return traduction('messages.php.document.enregistrement_impossible_montant_negatif');

		return true;
	}

	/**
	*
	* Vérifications spécifiques sur certains types de document ou par héritage pour les projets clients
	* Doit être utilisé en héritage
	*
	* @param $modifications le tableau des données passé dans la méthode enregistre()
	*
	*/
	protected function verifications_specifiques($modifications) {

		// on vérifie si ce n'est pas un document négatif
		$retour = $this->verifie_si_document_negatif($modifications);

		if($retour !== true)
			return $retour;

		return true;
	}

	/**
	*
	* Si le document est validé, on retire les champs non modifiables suite à validation
	*
	* @param $modifications array le tableau des données qui seront modifiées
	*
	* @return $modifications ce même tableau, éventuellement modifé
	*
	*/
	protected function retraite_champs_non_modifiables_si_document_valide($modifications) {

		if($this->document_modifiable() === true)
			return $modifications;

		// champs modifiables
		$champs = Champ_libre::where('type_element', $this->_type_element)->where('modification_post_validation', 1)->get()->pluck('nom_sql')->toArray();

		$champs_modifiables_quoi_quil_en_soit = $this->champs_blocs_recurrences();

		foreach($modifications as $champ => $osef) {

			if($this->champ_modifiable($champ) === false && !in_array($champ, $champs_modifiables_quoi_quil_en_soit)) {

				unset($modifications[$champ]);
			}
		}

		return $modifications;
	}

	/**
	*
	* @cf description sur Element_management
	*
	*/
	protected function retraite_modifications($modifications) {

        // on tranforme acompte_x en acompte
        $acompte_renseigne = false;
        for ($i = 1; $i <= 3; $i++) {
            if(isset($modifications["acompte_{$i}"]) && !empty($modifications["acompte_{$i}"])) {

                $modifications['acompte'] = $modifications["acompte_{$i}"];
                $modifications['acompte_type'] = $i;
                $acompte_renseigne = true;
            }
            if(isset($modifications["acompte_{$i}"]))
                unset($modifications["acompte_{$i}"]);
        }

		// c'est le cas ou on annule une remise depuis la page de saisie d'un document
		if(!$acompte_renseigne) {

			$modifications['acompte'] = 0;
			$modifications['acompte_type'] = 1;
		}

		// gestion des champs non modifiables si le document est validé
		$modifications = $this->retraite_champs_non_modifiables_si_document_valide($modifications);

		// on ajoute le champ entite_id
		if(isset($modifications['client_id'])) {

			$client = management('client', $modifications['client_id']);

			$modifications['entite_id'] = $client->modele->entite_id;
		}

		// on ajoute le compte bancaire par défaut
		if(!empty($modifications['entite_id']) && (empty($this->modele) || empty($this->modele->compte_bancaire_id)) && empty($modifications['compte_bancaire_id'])) {

			// on vérifie si on a bien le champ compte_bancaire_id dans les champs libres
			$champ_libre = table_libre($this->_type_element)->champs_libres()->where('nom_sql', 'compte_bancaire_id')->first();

			if($champ_libre !== null) {

				$modifications['compte_bancaire_id'] = parametre_entite($modifications['entite_id'], 'compte_bancaire_defaut');
			}

		}

		// On vérifie que les contacts appartiennent bien au client
		if((isset($modifications['client_id']) || isset($modifications['fournisseur_id'])) && isset($modifications['contacts_ids']) && is_array($modifications['contacts_ids'])) {

			$contacts = $modifications['contacts_ids'];
			$nouveaux_contacts = [];

			foreach($contacts as $id_contact) {

				$contact = modele('contact', $id_contact);

				// Client ID si c'est un document vente, fournisseur ID si c'est un document achat.
				if($this->est_une_vente())
					$champ = 'client_id';
				else
					$champ = 'fournisseur_id';

				// On ajoute les contacts qui sont liés au client.
				if($contact->$champ == $modifications[$champ]) {
					$nouveaux_contacts[] = $id_contact;
				}
			}

			$modifications['contacts_ids'] = $nouveaux_contacts;
		}

		// on retraite via le management Element de base
		$modifications = parent::retraite_modifications($modifications);

		return $modifications;
	}

	/**
	 *
	 * Pour gérer le nombre de documents existants pour les clients à l'initialisation de l'ERP
	 * Parfois les factures ne doivent pas commencer à 1 mais à 2153 par exemple si on prend une période en cours où il y a déjà des factures
	 * Cette méthode doit être utilisée en surcharge
	 *
	 * @return int
	 *
	 */
	protected function numerotation_depart() {

		return 1;
	}

	/**
	 *
	 * retourne le nombre de chiffre dans la numérotation des documents
	 * par exemple 5 équivaut des numéros comme 00012
	 * Cette méthode doit être utilisée en surcharge
	 *
	 * @return int
	 *
	 */
	protected function nombre_chiffres_numerotation() {

		return fonctionnalite('nombre_chiffres_numerotation');
	}

	/**
	 *
	 * Retourne le numéro du document actuel
	 *
	 */
	protected function numero_document($reference_document) {

		// on récupère avant le break la chaine pour créer le numéro
		list($chaine_avant_break) = explode('{break}', $reference_document);

		$numerotation_variante = '';

		if(empty($chaine_avant_break))
			$numero = modele($this->_type_element)->avec_inactifs()->sans_profils()->where('valide', 1)->count();
		else {

			// on gère les variantes
			if($this->_type_element == 'devis_vente') {

				$numero = modele($this->_type_element)->avec_inactifs()->sans_profils()->where('reference_document', 'LIKE', $chaine_avant_break.'%')->whereNull('variante_devis_vente_id')->count();
			}
			else {

				if(in_array($this->_type_element, array('acompte_vente', 'facture_vente'))) {

					$numero = modele('acompte_vente')->avec_inactifs()->sans_profils()->where('reference_document', 'LIKE', $chaine_avant_break.'%')->count();
					$numero += modele('facture_vente')->avec_inactifs()->sans_profils()->where('reference_document', 'LIKE', $chaine_avant_break.'%')->count();
				}
				elseif(in_array($this->_type_element, array('acompte_achat', 'facture_achat'))) {

					$numero = modele('acompte_achat')->avec_inactifs()->sans_profils()->where('reference_document', 'LIKE', $chaine_avant_break.'%')->count();
					$numero += modele('facture_achat')->avec_inactifs()->sans_profils()->where('reference_document', 'LIKE', $chaine_avant_break.'%')->count();
				}
				else {

					$numero = modele($this->_type_element)->avec_inactifs()->sans_profils()->where('reference_document', 'LIKE', $chaine_avant_break.'%')->count();
				}
			}
		}

		$numero = $numero + $this->numerotation_depart();

		for($i=1; $i <= $this->nombre_chiffres_numerotation(); $i++)
			$numero = '0'.$numero;

		return substr($numero, $this->nombre_chiffres_numerotation() * -1).$numerotation_variante;
	}

	/**
	 *
	 * Valide un document
	 *
	 * @return true si tout va bien, une erreur (string) sinon
	 *
	 */
	public function valide() {

        if($this->modele->inactif == 1)
            return traduction('messages.php.document.supprime_non_validable',null,[$this->affiche()]);

        $retour = profil_modification($this->_type_element, $this->modele->entite_id, $this->modele);

        if(!$retour)
             return "Vous n'avez pas les droits nécessaires pour éditer cet élément.";

        // Si fonctionnalité activé et document concerné, on ne valide pas le document si un document validé existe avec une date ultérieur au document actuel
        $erreur_sequence = $this->verification_sequence_chronologique();

        if($erreur_sequence !== true)
            return $erreur_sequence;

        // On vérifie si il faut une approbation
        if (moi() !== null) {

            if (!empty(moi()->validation_conges_n_plus_1)) {



                $verifications_specifiques = $this->verification_avant_validation_specifique();

                if ($verifications_specifiques !== false)
                    return $verifications_specifiques;

                $modele_approbation = modele('approbation_workflow')->join('approbation_workflow_profils_necessitant_approbation', 'approbation_workflow_profils_necessitant_approbation.cle_locale', 'approbation_workflow.id')->where('valeur',moi()->profil_id)->where('type_element',$this->_type_element)->where('action',1)->get();

                if(!isset($this->sans_n_plus_1))
                    $this->sans_n_plus_1 = false;

                if (!$modele_approbation->isEmpty()) {

                    foreach ($modele_approbation as $ligne_approbation) {

						foreach(moi()->validation_conges_n_plus_1 as $validateur){
							// Vérifier si action correspond
							// Si action correspond, on ajoute la demande dans la table approbation
							$management_approbation = management('approbation');

							$information_approbation = array(
								'destinataire_id' => $validateur,
								'utilisateur_id' => moi()->id,
								'type_element' => $this->_type_element,
								'element_id' => $this->modele->id,
								'action' => 1,
								'demandeur_initial' => moi()->id,
							);

							$management_approbation->enregistre($information_approbation);
						}
                    }

                    $this->enregistrer_log(Variables::$types_logs['demande_approbation']);

                    return traduction('messages.php.document.approbation_emise');
                }

                // On vérifie si on a un supérieur
                else if(!empty(moi()->validation_conges_n_plus_1) && !$this->sans_n_plus_1) {

                    $modele_approbation = modele('approbation')->where('type_element',$this->_type_element)->where('element_id',$this->modele->id)->first();

                    // Une demande d'approbation été présente
                    if ($modele_approbation != null) {

                        // On enregistre l'approbation
                        $management = management('approbation',$modele_approbation->id);

                        $modifications = array(
                            'approbation' => 1,
                            'date_approbation' => date("Y-m-d H:i:s"),
                        );

                        $management->enregistre($modifications);

                        // On créer la nouvelle demande d'approbation

                        // On récupère le document
                        $document = modele($this->_type_element)->where('id',$this->modele->id)->first();

                        if ($management->modele->demandeur_initial == null)
                            $management->modele->demandeur_initial == moi()->id;

						foreach(moi()->validation_conges_n_plus_1 as $validateur){

							$management_approbation = management('approbation');

							$information_approbation = array(
								'destinataire_id' => $validateur,
								'utilisateur_id' => moi()->id,
								'type_element' => $this->_type_element,
								'element_id' => $document->id,
								'action' => 1,
								'demandeur_initial' => $management->modele->demandeur_initial,
							);

							$management_approbation->enregistre($information_approbation);
						}

                        $this->enregistrer_log(Variables::$types_logs['demande_approbation']);

                        return traduction('messages.php.document.approbation_emise');
                    }
                }
            }
        }

        $modele_avant = clone($this->modele);

        if($this->modele->valide == 1)
            return true;




        /**
        @todo vérifier les droits pour les profils
        */

        /**
        @todo vérifier les conditions pour la validation (comme l'adresse)
        */
        // on vérifie les champs obligatoires des paramétrages avancés
        $parametrage_avance = null;
        if(fonctionnalite('parametrage_avance_des_documents') === true) {

            $parametrage_avance = modele('parametrage_avance_des_documents')->where('client_id', $this->modele->client_id)->where('type_element', $this->_type_element)->first();

            if($parametrage_avance !== null) {

                if(!empty($parametrage_avance->champs_obligatoires)) {

                    $champs_obligatoires = json_decode($parametrage_avance->champs_obligatoires);

                    foreach($champs_obligatoires as $nom_sql) {

                        if(empty($this->modele->{$nom_sql})) {

                            $champ = champ_libre($this->_type_element, $nom_sql)->modele->nom;

                            return traduction('messages.php.document.champ_obligatoire_validation',null,[$champ]);
                        }
                    }
                }
            }
        }

        // est ce qu'on doit bloquer la validation de documents si le client présente un retard de paiement ?

        $bloquer_validation_document_si_client_retard_paiement = fonctionnalite('bloquer_validation_document_si_client_retard_paiement');

        if($this->est_une_vente() && isset($bloquer_validation_document_si_client_retard_paiement[$this->_type_element]) && $bloquer_validation_document_si_client_retard_paiement[$this->_type_element] === true && !empty($this->modele->client_id)) {

            $retour = $this->verifie_si_client_a_retard_de_paiement();

            if($retour !== true)
                return $retour;
        }

        $parametre_validation = parametre('validation_document');

        while($parametre_validation !== null && round(abs(strtotime(date('Y-m-d H:i:s')) - strtotime($parametre_validation)) /60,2) < 10){
            sleep(5);
            $parametre_validation = parametre('validation_document');
        }

        parametre('validation_document',date('Y-m-d H:i:s'));

        try {

            $modifications = array(

                'statut' => 10,
                'valide' => 1,
            );

            // on vérifie que l'entité du document soit la même que l'entité du client/fournisseur sinon on force la valeur

            if(strpos($this->_type_element, 'vente') !== false) {

                $client= modele('client',$this->modele->client_id);

                if($client && $client->entite_id != $this->modele->entite_id){

                    // a la demande de Guillaume, on annule cette règle
                    // $modifications['entite_id']= $client->entite_id;

                }

            }

            else{

                $fournisseur= modele('fournisseur',$this->modele->fournisseur_id);

                if($fournisseur && $fournisseur->entite_id != $this->modele->entite_id){

                    // A la demande de Guillaume on annule cette règle
                    // $modifications['entite_id']= $fournisseur->entite_id;

                }

            }

            // on est obligé de valider le document pour checker la numérotation
            $this->enregistre_modele(array('valide' => 1));

            $verification = $this->verification_creation_reference_document();

            if($verification !== true) {

                $this->enregistre_modele(array('valide' => null));

                parametre_supprimer('validation_document');

                return $verification;
            }

            $this->enregistre_modele($modifications);

            // création de la référence document
            $this->creation_reference_document();

            // on logue la validation du document
            $this->log_validation();

            $this->enregistre_modele(array('pdf' => null));

            $fonctionnalite = fonctionnalite('gescom_type_document_bloquer_pdf_post_validation');
            $fonctionnalite_versionning = fonctionnalite('versionning_document');

            if((isset($fonctionnalite[$this->_type_element]) && $fonctionnalite[$this->_type_element] === true) ||
                (isset($fonctionnalite_versionning[$this->_type_element]) && $fonctionnalite_versionning[$this->_type_element] === true)){

                $this->creation_pdf();
            }

            // méthodes post modification / création
            $erreur_post_validation = $this->methodes_post_validation_document($this->modele);

            // on met à jour le statut
            $this->gere_statut_automatique();

            // on exécute les workflows
            $this->gestion_workflow(4);

            // on gère les indicateurs
            $this->gestion_indicateurs();

            // on gère les notifications
            if(fonctionnalite('notifications') !== false && empty(moi_extranet())) {

                // on récupère les utilisateurs abonnés
                if(strpos($this->_type_element, 'vente') !== false) {

                    $utilisateurs = modele('notification_element')->where('type_element', 'client')->where('element_id', $this->modele->client_id)->get();
                    $tiers = management('client', $this->modele->client_id)->affiche_lien();
                }
                else {

                    $utilisateurs = modele('notification_element')->where('type_element', 'fournisseur')->where('element_id', $this->modele->fournisseur_id)->get();
                    $tiers = management('fournisseur', $this->modele->fournisseur_id)->affiche_lien();
                }

                foreach($utilisateurs as $utilisateur) {

                    $notification = management('notification');

                    $info = array(

                        'date' => date('Y-m-d H:i:s'),
                        'utilisateur_id' => $utilisateur->utilisateur_id,
                        'zone' => 'navbar_notifications',
                        'contenu_html' => moi()->prenom.' '.strtoupper(substr(moi()->nom,0,1)).'. a <b>validé un document</b> pour '.$tiers.' : '.$this->affiche_lien(),
                    );

                    $notification->enregistre($info);
                }
            }
        }
        catch (\Exception | \Throwable $e){
            Log::error('Erreur validation document : '.$e);
        }

        parametre_supprimer('validation_document');

        if($erreur_post_validation !== true)
            return $erreur_post_validation;

		return true;
	}

	/**
	 *
	 * On met à jour le statut du document
	 *
	 * Cette classe est surchargée dans les managements fils
	 *
	 */
	public function gere_statut_automatique() {

	}

	/**
	 *
	 * On vérifie si la validation est possible si le client présente un retard de paiement
	 *
	 */
	protected function verifie_si_client_a_retard_de_paiement() {

		$management_client = management('client', $this->modele->client_id);

		// on va chercher le solde du client
		$solde = $management_client->solde_du();

		$encours_client = $management_client->modele->encours_max_autorise;

		if(empty($encours_client))
			$encours_client = 0;

		if($solde > $encours_client) {

			return traduction('messages.php.document.validation_impossible_retard_client',null,[montant($solde)." " . maquette('devise_application_nom') . "s",$encours_client]);
		}

		return true;
	}

	/**
	 *
	 * Annule le statut réglé d'un document
	 *
	 * @return true si tout va bien, une erreur (string) sinon
	 *
	 *
	 */
	public function annule_reglement() {

		// l'utilisateur a t il le droit de faire cela ?
		$test = profil_creation('paiement', $this->modele->entite_id);

		if($test !== true)
			return $test;

        $modele_avant = clone($this->modele);

		if($this->modele->regle !== 1)
			return true;

		/**
		@todo vérifier les droits pour les profils
		*/

		/**
		@todo vérifier les conditions pour la validation (comme l'adresse)
		*/

        $modifications = array(

			'regle' => 0,
			'date_changement_statut' => date('Y-m-d'),
		);

        $this->enregistre_modele($modifications);

		// on logue le refus du document
        $this->log_annulation_reglement();

		// méthodes post modification / création
		$this->methodes_post_annulation_reglement_document($this->modele);

		return true;
	}

	/**
	 *
	 * Valide le statut réglé d'un document
	 *
	 * @return true si tout va bien, une erreur (string) sinon
	 *
	 *
	 */
	public function valide_reglement() {

		// l'utilisateur a t il le droit de faire cela ?
		$test = profil_creation('paiement', $this->modele->entite_id);

		if($test !== true)
			return $test;

        $modele_avant = clone($this->modele);

		if(!empty($this->modele->regle))
			return true;

		/**
		@todo vérifier les droits pour les profils
		*/

		/**
		@todo vérifier les conditions pour la validation (comme l'adresse)
		*/

        $modifications = array(

			'regle' => 1,
			'date_changement_statut' => date('Y-m-d'),
		);


        $this->enregistre_modele($modifications);

		// on logue le refus du document
        $this->log_validation_reglement();

		// méthodes post modification / création
		$this->methodes_post_validation_reglement_document($this->modele);

		return true;
	}

	/**
	 *
	 * Mise en attente d'un document (accepte = 0)
	 *
	 * @return true si tout va bien, une erreur (string) sinon
	 *
	 */
	public function mise_en_attente() {

        $modele_avant = clone($this->modele);

		if($this->modele->accepte == 0)
			return true;

		/**
		@todo vérifier les droits pour les profils
		*/

		/**
		@todo vérifier les conditions pour la validation (comme l'adresse)
		*/

        $modifications = array(

			'accepte' => 0,
			'date_changement_statut' => date('Y-m-d'),
		);


        $this->enregistre_modele($modifications);

		// on logue la mise en attente du document
        $this->log_mise_en_attente_document();

		// méthodes post modification / création
		$this->methodes_post_validation_mise_en_attente_document($this->modele);

		// on gère les notifications
		if(fonctionnalite('notifications') !== false) {

			// on récupère les utilisateurs abonnés
			if(strpos($this->_type_element, 'vente') !== false) {

				$utilisateurs = modele('notification_element')->where('type_element', 'client')->where('element_id', $this->modele->client_id)->get();
				$tiers = management('client', $this->modele->client_id)->affiche_lien();
			}
			else {

				$utilisateurs = modele('notification_element')->where('type_element', 'fournisseur')->where('element_id', $this->modele->fournisseur_id)->get();
				$tiers = management('fournisseur', $this->modele->fournisseur_id)->affiche_lien();
			}

			foreach($utilisateurs as $utilisateur) {

				$notification = management('notification');

				$info = array(

					'date' => date('Y-m-d H:i:s'),
					'utilisateur_id' => $utilisateur->utilisateur_id,
					'zone' => 'navbar_notifications',
					'contenu_html' => moi()->prenom.' '.strtoupper(substr(moi()->nom,0,1)).'. a <b>remis en attente un devis</b> pour '.$tiers.' : '.$this->affiche_lien(),
				);

				$notification->enregistre($info);
			}
		}

		return true;


	}

	/**
	 *
	 * Enregistre une commande comme facturée
	 *
	 * @return true si tout va bien, une erreur (string) sinon
	 *
	 *
	 */
	public function facture() {

        $modele_avant = clone($this->modele);

		if($this->modele->facture == 1)
			return true;

		/**
		@todo vérifier les droits pour les profils
		*/

		/**
		@todo vérifier les conditions pour la validation (comme l'adresse)
		*/

        $modifications = array(

			'facture' => 1,
			'date_changement_statut' => date('Y-m-d'),
		);

        $this->enregistre_modele($modifications);

		// on logue la validation du document
        $this->log_facturation();

		// méthodes post modification / création
		$this->methodes_post_facturation_document($this->modele);

		return true;
	}

	/**
	 *
	 * Enregistre une commande comme expédiée
	 *
	 * @return true si tout va bien, une erreur (string) sinon
	 *
	 *
	 */
	public function expedie() {

        $modele_avant = clone($this->modele);

		// si on en est là, c'est que tous les articles ont été commandés ou en stocks, donc on force cette valeur
		if(empty($this->modele->commande_fournisseur_realisee))
			$this->commande_fournisseur_realisee();

		// si on en est là, c'est que tous les articles ont été reçus, donc on force cette valeur
		if(empty($this->modele->commande_fournisseur_recue))
			$this->commande_fournisseur_recue();

		if($this->modele->expedie == 1)
			return true;

		/**
		@todo vérifier les droits pour les profils
		*/

		/**
		@todo vérifier les conditions pour la validation (comme l'adresse)
		*/

        $modifications = array(

			'expedie' => 1,
            'statut' => 45,
		);

        $this->enregistre_modele($modifications);

		// on logue la validation du document
        $this->log_expedition();

		// méthodes post modification / création
		$this->methodes_post_expedition_document($this->modele);

		return true;
	}

	/**
	 *
	 * Enregistre le fait que pour une commande vente donnée, tous les articles sont en stocks ou commandés chez le fournisseur
	 *
	 * @return true si tout va bien, une erreur (string) sinon
	 *
	 *
	 */
	public function commande_fournisseur_realisee() {

        $modele_avant = clone($this->modele);

		if($this->modele->commande_fournisseur_realisee == 1)
			return true;

		/**
		@todo vérifier les droits pour les profils
		*/

		/**
		@todo vérifier les conditions pour la validation (comme l'adresse)
		*/

        $modifications = array(

			'commande_fournisseur_realisee' => 1,
            'statut' => 10,
		);

        $this->enregistre_modele($modifications);

		// on logue la validation du document
        $this->log_commande_fournisseur_realisee();

		// méthodes post modification / création
		$this->methodes_post_commande_fournisseur_realisee($this->modele);

		return true;
	}

	/**
	 *
	 * Enregistre le fait que pour une commande de vente tous les articles sont prêts pour expédition
	 *
	 * @return true si tout va bien, une erreur (string) sinon
	 *
	 *
	 */
	public function commande_fournisseur_recue() {

        $modele_avant = clone($this->modele);

		// si on en est là, c'est que tous les articles ont été commandés ou en stocks, donc on force cette valeur
		if(empty($this->modele->commande_fournisseur_realisee))
			$this->commande_fournisseur_realisee();

		if($this->modele->commande_fournisseur_recue == 1)
			return true;

		/**
		@todo vérifier les droits pour les profils
		*/

		/**
		@todo vérifier les conditions pour la validation (comme l'adresse)
		*/

        $modifications = array(

			'commande_fournisseur_recue' => 1,
            'statut' => 15,
		);

        $this->enregistre_modele($modifications);

		// on logue la validation du document
        $this->log_commande_fournisseur_recue();

		// méthodes post modification / création
		$this->methodes_post_commande_fournisseur_recue($this->modele);

		return true;
	}

	/**
	 *
	 * Retourne le journal à utiliser pour un document
	 *
	 */
	public function compta_journal() {

		// on crée l'écriture
		if(strpos($this->_type_element, '_vente') !== false) {

			$journal_id = fonctionnalite('compta_journal_vente');
		}
		else {

			$journal_id = fonctionnalite('compta_journal_achat');
		}

		if(empty($journal_id))
			return false;

		return $journal_id;
	}

	/**
	 *
	 * Comptabilisation
	 *
	 */
	public function comptabilise() {

		// ce document est déjà comptabilisé
		if($this->modele->comptabilise == 1) {

			return traduction('messages.php.document.deja_comptabilise');
		}

		// ce document n'est pas validé
		if($this->modele->valide != 1) {

			return traduction('messages.php.document.non_valide');
		}

		// on va chercher les articles du document
		$articles = $this->articles();

		if(count($articles) == 0) {

			return traduction('messages.php.document.aucun_article').$this->modele->id;
		}

		// on va chercher les comptes articles
		$comptes_articles = array();
		$comptes_articles_inverses = array();
		$comptes_tva = array();
		$comptes_tva_autoliquides = array();

		$montant_tiers = array();
        
		// on va chercher la situation géographique comptable du client
		$situation_geographique = $this->modele->categorie_comptable_id ?? $this->compta_situation_geographique();

		if(empty($situation_geographique)) {

			if($this->est_une_vente()) {

				if(!empty($this->modele->client_id_tiers_payeur))
					return traduction('messages.php.document.categorie_comptable_client_non_renseignee',null,[management('client', $this->modele->client_id_tiers_payeur)->modele->nom]);
				else
					return traduction('messages.php.document.categorie_comptable_client_non_renseignee',null,[management('client', $this->modele->client_id)->modele->nom]);

			}
			else {

				return traduction('messages.php.document.categorie_comptable_fournisseur_non_renseignee',null,[management('fournisseur', $this->modele->fournisseur_id)->modele->nom]);
			}
		}

        $gescom_regle_arrondi_ligne = fonctionnalite('gescom_regle_arrondi_ligne');

        $nombre_de_chiffres_decimaux_sur_les_tarif = fonctionnalite('nombre_de_chiffres_decimaux_sur_les_tarif');

		if($nombre_de_chiffres_decimaux_sur_les_tarif > 2)
			$nombre_de_chiffres_decimaux_sur_les_tarif = 2;

		$article_categorie_comptable_eco_contribution = modele('article_categorie_comptable')
			->where('categorie_comptable_id', $situation_geographique)
			->where('eco_contribution', 1)
			->first();

		foreach($articles as $article) {

			// au cas où
			if($article->remise_globale_ligne === null) {

				$article->remise_globale_ligne = 1;
			}

			list($erreur, $compte_comptable_article, $compte_comptable_tva, $compte_comptable_tva_autoliquidation, $taux_tva_autoliquidation) = $this->comptabilise_compte_article($article, $situation_geographique);

			if($erreur === false)
				return $compte_comptable_article;

			$a_eco_contribution = !empty($article->tarif_eco_contribution) && !empty($article->quantite_unite_eco_contribution) && !empty($article_categorie_comptable_eco_contribution);
			$eco_contribution_incluse = $a_eco_contribution && empty($article->application_eco_contribution);
		
			$montant_ht = $article->tarif * (100 - $article->remise) / 100 * $article->remise_globale_ligne;
			$montant_ec = $a_eco_contribution || (!$a_eco_contribution && !empty($article->application_eco_contribution)) ? $article->tarif_eco_contribution * $article->quantite_unite_eco_contribution : 0;
		
			if($eco_contribution_incluse)
				$montant_ht -= $montant_ec;
			
			if($gescom_regle_arrondi_ligne == 'prix_unitaire'){
				$montant_ht = round($montant_ht,$nombre_de_chiffres_decimaux_sur_les_tarif);
				$montant_ec = round($montant_ec,$nombre_de_chiffres_decimaux_sur_les_tarif);
			}
		
			$montant_ht *= $article->quantite;
			$montant_ec *= $article->quantite;

			$montant_ec_inclus_dans_montant_ht = !empty($montant_ec) && empty($article_categorie_comptable_eco_contribution) && !empty($article->application_eco_contribution);

			if($montant_ec_inclus_dans_montant_ht) {
				$montant_ht += $montant_ec;
			}
		
			if($gescom_regle_arrondi_ligne == 'total'){
				$montant_ht = round($montant_ht, $nombre_de_chiffres_decimaux_sur_les_tarif);
				$montant_ec = round($montant_ec, $nombre_de_chiffres_decimaux_sur_les_tarif);
			}
		
			if(!isset($comptes_articles[$compte_comptable_article]))
				$comptes_articles[$compte_comptable_article] = 0;

			$comptes_articles[$compte_comptable_article] += $montant_ht;

			// on vérifie qu'on a bien un compte tiers
			$compte_tiers = $this->compta_compte_tiers($article);

			if(is_array($compte_tiers) && !empty($compte_tiers['erreur'])) {

				return $compte_tiers['erreur'];
			}

			if($compte_tiers === false) {

				return traduction('messages.php.document.compte_tiers_impossible_ecriture');
			}

			if(!isset($montant_tiers[$compte_tiers]))
				$montant_tiers[$compte_tiers] = 0;

			$montant_tiers[$compte_tiers] += $montant_ht + ($montant_ec_inclus_dans_montant_ht ? 0 : $montant_ec);
		
			$compte_tva_ec = null;
			$taux_tva_ec = null;

			if($a_eco_contribution && $montant_ec != 0){
		
				$compte_ec = $this->est_une_vente() ? $article_categorie_comptable_eco_contribution->compte_produit : $article_categorie_comptable_eco_contribution->compte_charge;
		
				if(!isset($comptes_articles[$compte_ec]))
					$comptes_articles[$compte_ec] = 0;

				$comptes_articles[$compte_ec] += $montant_ec;
		
				$code_tva_ec_id = $this->est_une_vente() ? $article_categorie_comptable_eco_contribution->code_tva_id : $article_categorie_comptable_eco_contribution->code_tva_achat_id;
				$code_tva_ec = modele('code_tva')->where('id', $code_tva_ec_id)->first();
				$taux_tva_ec = $code_tva_ec->taux ?? 0;
				$compte_tva_ec = $code_tva_ec->compte_comptable;
			}
		
			if (!empty($compte_comptable_tva)) {
		
				if (!isset($comptes_tva[$compte_comptable_tva]))
					$comptes_tva[$compte_comptable_tva] = 0;
		
				if (empty($compte_comptable_tva_autoliquidation)) {
		
					$tva_produit = $montant_ht * $article->tva / 100;
					if (fonctionnalite('gescom_document_arrondi_par_ligne'))
						$tva_produit = round($tva_produit, $nombre_de_chiffres_decimaux_sur_les_tarif);
		
					$montant_tiers[$compte_tiers] += $tva_produit;
					$comptes_tva[$compte_comptable_tva] += $tva_produit;
		
				} else {
		
					$tva_produit = $montant_ht * $taux_tva_autoliquidation / 100;
					if (fonctionnalite('gescom_document_arrondi_par_ligne'))
						$tva_produit = round($tva_produit, $nombre_de_chiffres_decimaux_sur_les_tarif);
		
					$comptes_tva[$compte_comptable_tva] += $tva_produit;
		
					if (!isset($comptes_tva_autoliquides[$compte_comptable_tva_autoliquidation]))
						$comptes_tva_autoliquides[$compte_comptable_tva_autoliquidation] = 0;
					$comptes_tva_autoliquides[$compte_comptable_tva_autoliquidation] += $tva_produit;
				}
			}

			if($a_eco_contribution && $montant_ec != 0 && !empty($compte_tva_ec) && $taux_tva_ec > 0) {
		
				$tva_ec = $montant_ec * $taux_tva_ec / 100;
				if (fonctionnalite('gescom_document_arrondi_par_ligne'))
					$tva_ec = round($tva_ec, $nombre_de_chiffres_decimaux_sur_les_tarif);
		
				$montant_tiers[$compte_tiers] += $tva_ec;
		
				if (!isset($comptes_tva[$compte_tva_ec]))
					$comptes_tva[$compte_tva_ec] = 0;
				$comptes_tva[$compte_tva_ec] += $tva_ec;
			}

		}

		// ok normalement on a tous les comptes... on traite.
		$ecriture_id = modele('ecriture_comptable')->orderBy('ecriture_id', 'DESC')->take(1)->first();

		if($ecriture_id === null)
			$ecriture_id = 1 + management('ecriture_comptable')->increment_initial_ecriture_comptable();
		else
			$ecriture_id = $ecriture_id->ecriture_id + 1;

		if(!empty($this->modele->ecart_gestion_ttc)) {

			$ecart_gestion_ttc = $this->modele->ecart_gestion_ttc;

			if(($this->est_une_vente() && $ecart_gestion_ttc > 0) || ($this->est_un_achat() && $ecart_gestion_ttc < 0))
				$compte_comptable = fonctionnalite('ecart_gestion_compte_produit');
			else
				$compte_comptable = fonctionnalite('ecart_gestion_compte_charge');
				
			if(empty($compte_comptable))
				return traduction('messages.php.document.compte_produit_parametrage_categorie_comptable');

			if($ecart_gestion_ttc < 0){
				if(!isset($comptes_articles_inverses[$compte_comptable]))
					$comptes_articles_inverses[$compte_comptable] = 0;

				$comptes_articles_inverses[$compte_comptable] += $ecart_gestion_ttc * -1;
			}
			else{
				if(!isset($comptes_articles[$compte_comptable]))
					$comptes_articles[$compte_comptable] = 0;

				$comptes_articles[$compte_comptable] += $ecart_gestion_ttc;
			}

			$compte_tiers = $this->compta_compte_tiers(null);

			if(!isset($montant_tiers[$compte_tiers]))
				$montant_tiers[$compte_tiers] = 0;

			$montant_tiers[$compte_tiers] += $ecart_gestion_ttc;
		}

		// création de l'écriture

		//on vérifie si les totaux collent bien
		$difference = round(array_sum($comptes_tva) + array_sum($comptes_articles) - array_sum($comptes_articles_inverses), 2) - round(array_sum($montant_tiers) + array_sum($comptes_tva_autoliquides), 2);

		if(abs($difference) > 0.01) {

			return traduction('messages.php.document.comptabilisation_impossible',null,[round(array_sum($comptes_tva) + array_sum($comptes_articles) - array_sum($comptes_articles_inverses), 2),round(array_sum($montant_tiers) + array_sum($comptes_tva_autoliquides), 2)]);
		}

		$compte_auxiliaire = $this->compta_compte_auxiliaire();

		if($compte_auxiliaire === false)
			return traduction('messages.php.document.creation_compte_auxiliaire_impossible',null,[management('client', $this->modele->client_id)->modele->nom." (#".$this->modele->client_id.")","fonctionnalites.compta_compte_auxiliaire_nombre_caracteres"]);

		$journal = $this->compta_journal();

		if($journal === false)
			return traduction('messages.php.document.determiner_journal_impossible')." ".table_libre($this->_type_element)->element;

        $detail_libelle = "";

        if($this->est_une_vente() && !empty($this->modele->client_id))
            $element_pour_detail_libelle = modele('client')->where('id', $this->modele->client_id)->first();

        else if($this->est_un_achat() && !empty($this->modele->fournisseur_id))
            $element_pour_detail_libelle = modele('fournisseur')->where('id', $this->modele->fournisseur_id)->first();

        if($element_pour_detail_libelle !== null)
            $detail_libelle = " - " . $element_pour_detail_libelle->compte_auxiliaire;

		// les comptes de produit ou charge
		foreach($comptes_articles as $compte_id => $montant) {

			if(empty($montant))
				continue;

            $montant = round($montant,$nombre_de_chiffres_decimaux_sur_les_tarif);

			$ecriture = management('ecriture_comptable');

			$infos_ecriture = array(

				'ecriture_id' => $ecriture_id,
				'date' => $this->modele->date,
				'journal_id' => $journal,
				'compte_comptable_id' => $compte_id,
				// 'auxiliaire' => $compte_auxiliaire,
				'auxiliaire' => '',
				'debit' => 0,
				'credit' => $montant,
				'type_element' => $this->_type_element,
				'element_id' => $this->modele->id,
				'entite_id' => $this->modele->entite_id,
				'reference' => $this->modele->reference_document,
				'libelle' => ucfirst(table_libre($this->_type_element)->element).' '.$this->modele->reference_document . $detail_libelle,
			);

			// on inverse si c'est un avoir
			if(in_array($this->_type_element, array('avoir_vente', 'facture_achat')) && $montant >= 0) {

				$infos_ecriture['debit'] = $montant;
				$infos_ecriture['credit'] = 0;
			}
			// on garde si c'est un avoir négatif
			if(in_array($this->_type_element, array('avoir_vente', 'facture_achat')) && $montant < 0) {

				$infos_ecriture['debit'] = 0;
				$infos_ecriture['credit'] = $montant * -1;
			}
			// on inverse si c'est une facture négative
			if(in_array($this->_type_element, array('avoir_achat', 'facture_vente')) && $montant < 0) {

				$infos_ecriture['debit'] = $montant * -1;
				$infos_ecriture['credit'] = 0;
			}



			$retour_1 = $ecriture->enregistre($infos_ecriture);

		}

		foreach($comptes_articles_inverses as $compte_id => $montant) {

			if(empty($montant))
				continue;

            $montant = round($montant,$nombre_de_chiffres_decimaux_sur_les_tarif);

			$ecriture = management('ecriture_comptable');

			$infos_ecriture = array(

				'ecriture_id' => $ecriture_id,
				'date' => $this->modele->date,
				'journal_id' => $journal,
				'compte_comptable_id' => $compte_id,
				'auxiliaire' => '',
				'debit' => $montant,
				'credit' => 0,
				'type_element' => $this->_type_element,
				'element_id' => $this->modele->id,
				'entite_id' => $this->modele->entite_id,
				'reference' => $this->modele->reference_document,
				'libelle' => ucfirst(table_libre($this->_type_element)->element).' '.$this->modele->reference_document . $detail_libelle,
			);

			// on inverse si c'est un avoir
			if(in_array($this->_type_element, array('avoir_vente', 'facture_achat')) && $montant >= 0) {

				$infos_ecriture['debit'] = 0;
				$infos_ecriture['credit'] = $montant;
			}
			// on garde si c'est un avoir négatif
			if(in_array($this->_type_element, array('avoir_vente', 'facture_achat')) && $montant < 0) {

				$infos_ecriture['debit'] = $montant * -1;
				$infos_ecriture['credit'] = 0;
			}
			// on inverse si c'est une facture négative
			if(in_array($this->_type_element, array('avoir_achat', 'facture_vente')) && $montant < 0) {

				$infos_ecriture['debit'] = 0;
				$infos_ecriture['credit'] = $montant * -1;
			}



			$retour_1 = $ecriture->enregistre($infos_ecriture);

		}

		// les comptes de TVA
		foreach($comptes_tva as $compte_id => $montant) {

			if(empty($montant))
				continue;

            $montant = round($montant,$nombre_de_chiffres_decimaux_sur_les_tarif);

			$ecriture = management('ecriture_comptable');

			$infos_ecriture = array(

				'ecriture_id' => $ecriture_id,
				'date' => $this->modele->date,
				'journal_id' => $journal,
				'compte_comptable_id' => $compte_id,
				// 'auxiliaire' => $compte_auxiliaire,
				'auxiliaire' => '',
				'debit' => 0,
				'credit' => $montant,
				'type_element' => $this->_type_element,
				'element_id' => $this->modele->id,
				'entite_id' => $this->modele->entite_id,
				'reference' => $this->modele->reference_document,
                'libelle' => ucfirst(table_libre($this->_type_element)->element).' '.$this->modele->reference_document . $detail_libelle,
            );

			// on inverse si c'est un avoir
			if(in_array($this->_type_element, array('avoir_vente', 'facture_achat')) && $montant >= 0) {

				$infos_ecriture['debit'] = $montant;
				$infos_ecriture['credit'] = 0;
			}
			// on garde si c'est un avoir négatif
			if(in_array($this->_type_element, array('avoir_vente', 'facture_achat')) && $montant < 0) {

				$infos_ecriture['debit'] = 0;
				$infos_ecriture['credit'] = $montant * -1;
			}
			// on inverse si c'est une facture négative
			if(in_array($this->_type_element, array('avoir_achat', 'facture_vente')) && $montant < 0) {

				$infos_ecriture['debit'] = $montant * -1;
				$infos_ecriture['credit'] = 0;
			}

			$retour_2 = $ecriture->enregistre($infos_ecriture);


		}

		// les comptes de TVA autoliquidée
		foreach($comptes_tva_autoliquides as $compte_id => $montant) {

			if(empty($montant))
				continue;

            $montant = round($montant,$nombre_de_chiffres_decimaux_sur_les_tarif);

			$ecriture = management('ecriture_comptable');

			$infos_ecriture = array(

				'ecriture_id' => $ecriture_id,
				'date' => $this->modele->date,
				'journal_id' => $journal,
				'compte_comptable_id' => $compte_id,
				// 'auxiliaire' => $compte_auxiliaire,
				'auxiliaire' => '',
				'debit' => $montant,
				'credit' => 0,
				'type_element' => $this->_type_element,
				'element_id' => $this->modele->id,
				'entite_id' => $this->modele->entite_id,
				'reference' => $this->modele->reference_document,
                'libelle' => ucfirst(table_libre($this->_type_element)->element).' '.$this->modele->reference_document . $detail_libelle,
            );

			// on inverse si c'est un avoir
			if(in_array($this->_type_element, array('avoir_vente', 'facture_achat')) && $montant >= 0) {

				$infos_ecriture['credit'] = $montant;
				$infos_ecriture['debit'] = 0;
			}
			// on garde si c'est un avoir négatif
			if(in_array($this->_type_element, array('avoir_vente', 'facture_achat')) && $montant < 0) {

				$infos_ecriture['credit'] = 0;
				$infos_ecriture['debit'] = $montant * -1;
			}
			// on inverse si c'est une facture négative
			if(in_array($this->_type_element, array('avoir_achat', 'facture_vente')) && $montant < 0) {

				$infos_ecriture['credit'] = $montant * -1;
				$infos_ecriture['debit'] = 0;
			}

			$retour_4 = $ecriture->enregistre($infos_ecriture);


		}

		// le compte de tiers
		foreach($montant_tiers as $id_compte => $montant) {

			if(empty($montant))
				continue;

            $montant = round($montant,$nombre_de_chiffres_decimaux_sur_les_tarif);

			$ecriture = management('ecriture_comptable');

			$infos_ecriture = array(

				'ecriture_id' => $ecriture_id,
				'date' => $this->modele->date,
				'journal_id' => $journal,
				'compte_comptable_id' => $id_compte,
				'auxiliaire' => $compte_auxiliaire,
				// 'debit' => array_sum($comptes_tva) + array_sum($comptes_articles),
				'debit' => $montant,
				'credit' => 0,
				'type_element' => $this->_type_element,
				'element_id' => $this->modele->id,
				'entite_id' => $this->modele->entite_id,
				'reference' => $this->modele->reference_document,
                'libelle' => ucfirst(table_libre($this->_type_element)->element).' '.$this->modele->reference_document . $detail_libelle,
            );

			// on inverse si c'est un avoir
			if(in_array($this->_type_element, array('avoir_vente', 'facture_achat')) && $montant >= 0) {

				$infos_ecriture['credit'] = $montant;
				$infos_ecriture['debit'] = 0;
			}
			// on garde si c'est un avoir négatif
			if(in_array($this->_type_element, array('avoir_vente', 'facture_achat')) && $montant < 0) {

				$infos_ecriture['credit'] = 0;
				$infos_ecriture['debit'] = $montant * -1;
			}
			// on inverse si c'est une facture négative
			if(in_array($this->_type_element, array('avoir_achat', 'facture_vente')) && $montant < 0) {

				$infos_ecriture['credit'] = $montant * -1;
				$infos_ecriture['debit'] = 0;
			}

			$retour_3 = $ecriture->enregistre($infos_ecriture);
		}

		// pour du spé
		$this->compta_ecritures_comptables_supplementaires(array(

			'ecriture_id' => $ecriture_id,
			'auxiliaire' => $compte_auxiliaire,
			'journal' => $journal,
		));

		// on enregistre le document comme comptabilisé
		$this->enregistre_modele(array('comptabilise' => 1));

		// on logue la comptabilisation du document
        $this->log_comptabilisation();

		return true;
	}

	/**
	 *
	 * Pour surcharger en spé
	 *
	 */
	protected function compta_ecritures_comptables_supplementaires($parametres = array()) {

	}


	/**
	 *
	 * Retourne le bon code à utiliser pour les tiers
	 *
	 */
	public function compta_compte_tiers($article) {

		if($this->est_une_vente()) {

			// on regarde si le client a un compte comptable spécifique
			$client = modele('client', $this->modele->client_id);

			if(!empty($client->forcer_compte_comptable))
				return $client->forcer_compte_comptable;

			return fonctionnalite('compta_compte_general_clients');
		}
		else {

			$fournisseur = modele('fournisseur', $this->modele->fournisseur_id);

			return fonctionnalite('compta_compte_general_fournisseurs');
		}
	}

	/**
	 *
	 * On va chercher la situation géographique du client
	 *
	 */
	public function compta_compte_auxiliaire() {

		if($this->est_une_vente()) {

			return management('client', $this->modele->client_id)->compte_auxiliaire();
		}
		else {

			return management('fournisseur', $this->modele->fournisseur_id)->compte_auxiliaire();
		}
	}

	/**
	 *
	 * On va chercher la situation géographique du client ou du fournisseur
	 *
	 */
	public function compta_situation_geographique() {

		if($this->est_une_vente()) {

			if(!empty($this->modele->client_id_tiers_payeur))
				$client = modele('client', $this->modele->client_id_tiers_payeur);
			else
				$client = modele('client', $this->modele->client_id);

			return $client->categorie_comptable_id;
		}

		if($this->est_un_achat()) {

			$fournisseur = modele('fournisseur', $this->modele->fournisseur_id);

			return $fournisseur->categorie_comptable_id;
		}

		return false;
	}

	/**
	 *
	 * On retourne le compte article
	 *
	 */
	public function comptabilise_compte_article($article, $categorie_comptable_id) {

        if(!empty($article->categorie_comptable_article_id)) {
            $parametrage = modele('categorie_comptable_article')
                ->where('id', $article->categorie_comptable_article_id)
                ->first();
        }
        else
            $parametrage = management('article', $article->article_id, $article->modele)->recupere_categorie_comptable($categorie_comptable_id);

		$taux_tva_autoliquidation = false;

		if(empty($parametrage)) {

			return array(false, traduction('messages.php.document.article_parametrage_categorie_comptable',null,[$article->modele->designation]), false, false, false);
		}

		if($this->est_une_vente())
			$code_tva_id = $parametrage->code_tva_id;
		else
			$code_tva_id = $parametrage->code_tva_achat_id;


		// on va chercher le code de TVA
		if(empty($code_tva_id)) {

			return array(false, traduction('messages.php.document.code_tva_parametrage_categorie_comptable'), false, false, false);
		}

		// par défaut, pas d'autoliquidation
		$compte_tva_autoliquidation = false;

		// exonération de TVA
		if($code_tva_id == -1) {

			$compte_tva = false;
		}
		else {

			$code_tva = modele('code_tva')->find($code_tva_id);

			$compte_tva = $code_tva->compte_comptable;
			$taux_tva_autoliquidation = $code_tva->taux;

			if(empty($compte_tva)) {

				return array(false, traduction('messages.php.document.compte_tva_parametrage_code_tva'), false, false, false);
			}

			// on vérifie si c'est de l'autoliquidation
			if(in_array($code_tva->sens, array(2,3))) {

				// on vérifie le compte pour l'autoliquidation
				$compte_tva_autoliquidation = $code_tva->compte_comptable_autoliquidation;

				if(empty($compte_tva_autoliquidation)) {

					return array(false, traduction('messages.php.document.messages.php.document.compte_tva_autoliquidation_parametrage_code_tva'), false, false, false);
				}

			}
		}


		if($this->est_une_vente()) {

			if(empty($parametrage->compte_produit)) {

				return array(false, traduction('messages.php.document.compte_produit_parametrage_categorie_comptable'), false, false, false);
			}

			return array(true, $parametrage->compte_produit, $compte_tva, $compte_tva_autoliquidation, $taux_tva_autoliquidation);
		}
		else {

			if(empty($parametrage->compte_charge)) {

				return array(false, traduction('messages.php.document.compte_charge_parametrage_categorie_comptable'), false, false, false);
			}

			return array(true, $parametrage->compte_charge, $compte_tva, $compte_tva_autoliquidation, $taux_tva_autoliquidation);
		}
	}

	/**
	 *
	 * Creation de la référence du document
	 *
	 * @return true
	 *
	 */
	protected function creation_reference_document() {

		// il y a déjà une référence document
		if(isset($this->modele) && !empty($this->modele->reference_document) && $this->modele->reference_document != 'Pro forma')
			return true;

		// document non validé
		if($this->modele->valide != 1) {

			$this->enregistre_modele(array(

				'reference_document' => 'Pro forma',
				'chaine_affichage' => null,
			));

			return true;
		}

		$reference_document = $this->recupere_reference_document_pour_validation();

		$this->enregistre_modele(array(

			'reference_document' => $reference_document,
			'chaine_affichage' => null,
		));

		return true;
	}

	/**
	 *
	 * On va créer la référence du document et vérifier qu'elle est bien unique
	 *
	 */
	protected function verification_creation_reference_document() {

		// il y a déjà une référence document
		if(isset($this->modele) && !empty($this->modele->reference_document) && $this->modele->reference_document != 'Pro forma')
			return true;

		if($this->est_un_achat())
			return true;

		$reference_document = $this->recupere_reference_document_pour_validation();

		// on vérifie si elle est unique
		$elements_a_verifier = array($this->_type_element => true);

		if(in_array($this->_type_element, array('acompte_vente', 'facture_vente'))) {

			$elements_a_verifier = array('facture_vente' => true, 'acompte_vente' => true);
		}

		foreach($elements_a_verifier as $type_element => $osef) {

			$document = modele($type_element)->where('reference_document', $reference_document)->first();

			if($document !== null)
				return traduction('messages.php.document.reference_deja_utilise',null,[$reference_document]);
		}

		return true;
	}

	/**
	 *
	 * On récupère la référence du document pour une création
	 *
	 */
	protected function recupere_reference_document_pour_validation() {

		// on récupère la chaine de caractères pour la numérotation
		if(in_array($this->_type_element, array('acompte_vente', 'facture_vente'))) {

			$reference_document = parametre_entite($this->modele->entite_id, 'numerotation_facture_vente');
		}
		else {

			$reference_document = parametre_entite($this->modele->entite_id, 'numerotation_'.$this->_type_element);
		}

		// on remplace les variables
		$variables = $this->variables_pour_numerotation_document();

		$reference_document = str_replace(array_keys($variables), $variables, $reference_document);

		// on va voir combien de documents déjà avec ce même préfixe
		$numero_document = $this->numero_document($reference_document);

		// on remplace le numéro et le break
		if(strpos($reference_document, '{numero}') !== false)
			$reference_document = str_replace(array('{break}', '{numero}'), array('', $numero_document), $reference_document);
		else {

			$reference_document = str_replace(array('{break}'), array(''), $reference_document);
			$reference_document .= $numero_document;
		}

		return $reference_document;
	}

	/**
	*
	* @return array un tableau qui contient la liste des variables pour la numérotation des documents
	*
	* Cette méthode peut être étendue via l'héritage pour gérer les cas spécifiques.
	* Attention à bien appeler en 1er parrent::variables_pour_numerotation_document() pour charger les variables standards pour ensuite écraser si nécessaire
	*
	*/
	protected function variables_pour_numerotation_document() {

		return array(

			'{annee-date}' => date('Y', strtotime($this->modele->date)),
			'{mois-date}' => date('m', strtotime($this->modele->date)),
			'{annee-date-2}' => date('y', strtotime($this->modele->date)),
		);
	}

	/**
	*
	* Cette méthode décide s'il faut ou non générer le PDF
	*
	* En standard le PDF est créé systématiquement
	* Pour certains clients, on peut surcharger cette méthode pour créer le PDF plus tard
	* Ou dans certains cas particuliers, comme les imports pour créer les PDF dans un second temps car cela prend trop de temps
	*
	*/
	public function creation_pdf($id_modele_doc = false) {

        return $this->creation_document_pdf($id_modele_doc);
	}

	/**
	*
	* Cette méthode crée le PDF
	*
	*/
	public function creation_document_pdf($id_modele_doc = false) {

		// Si la génération du PDF est désactivée pour ce document, on quitte la fonction
		$generer_document = fonctionnalite('type_document_generer_pdf');

		if(isset($generer_document[$this->_type_element]) && $generer_document[$this->_type_element] == false)
			return true;

        $config_bloquer_pdf_post_validation = fonctionnalite('gescom_type_document_bloquer_pdf_post_validation');

        if($this->modele->valide == 1 &&
            isset($config_bloquer_pdf_post_validation[$this->_type_element]) &&
            $config_bloquer_pdf_post_validation[$this->_type_element] === true &&
            !empty($this->modele->pdf)
        ) {
            return $this->modele->pdf;
        }

        return parent::creation_document_pdf($id_modele_doc);
	}


	private function creation_pdf_parametrage_avance($parametrage_avance, $chemin_final, $nom_du_pdf) {

		// on regarde s'il y a un paramétrage spécifique pour le nombre d'exemplaires
		if($parametrage_avance !== null) {

			if($parametrage_avance->exemplaires > 1) {

				$merger = \PDFMerger::init();

				// on doit dupliquer autant de fois le document
				for($i = 1; $i <= $parametrage_avance->exemplaires; $i++) {

					$merger->addPDF(storage_path('app/'.$chemin_final.$nom_du_pdf));
				}

				$merger->merge();

				$nom_du_pdf = str_replace('.pdf', '_'.$parametrage_avance->exemplaires.'_exemplaires.pdf', $nom_du_pdf);

				$merger->save(storage_path('app/'.$chemin_final.$nom_du_pdf));
			}
		}
	}

	private function parametrage_modele_pdf_pour_generation_pdf() {

		// un paramétrage spécifique pour le client ?
		$modele_pdf = false;
		$parametrage_avance = null;
		if(fonctionnalite('parametrage_avance_des_documents') === true) {

			$parametrage_avance = modele('parametrage_avance_des_documents')->where('client_id', $this->modele->client_id)->where('type_element', $this->_type_element)->first();

			if($parametrage_avance !== null) {

				if(!empty($parametrage_avance->modele) && $parametrage_avance->modele != 'standard') {

					$modele_pdf = 'eden::pdf.'.$this->_type_element.'.'.$parametrage_avance->modele;
				}
			}
		}

		return [$modele_pdf, $parametrage_avance];
	}


	/**
	 *
	 * Gére le versionning du document. Surchargée dans Document_Management
	 *
	 **/
	protected function creation_document_versionning($chemin_final, $nom_du_pdf, $html_pdf = null) {

        // Uniquement pour les documents
		if(isset(fonctionnalite('versionning_document')[$this->_type_element]) && fonctionnalite('versionning_document')[$this->_type_element] == true) {

			// On hash le HTML réellement rendu (plutôt qu'une liste de champs en base) car de
			// nombreux champs techniques ou de statut de workflow (valide, accepte, marges
			// internes, ...) changent à chaque validation/acceptation sans que le document
			// visible par le client ne change.
			$hash_contenu = !empty($html_pdf) ? hash('sha256', $html_pdf) : null;

			$derniere_version = modele('versionning_document')
				->where(['type_element' => $this->_type_element, 'id_document' => $this->modele->id])
				->orderByDesc('id')
				->first();

			// on ne crée pas une nouvelle version si le contenu du document n'a pas changé depuis la dernière
			// (si le hash n'a pas pu être calculé, on crée systématiquement la version par sécurité)
			if($hash_contenu !== null && $derniere_version !== null && $derniere_version->hash_contenu === $hash_contenu) {

				return $nom_du_pdf;
			}

			// on recupère le nombre de versionnage du document
			$numero_version = modele('versionning_document')->where(['type_element' => $this->_type_element, 'id_document' => $this->modele->id])->count();

			if(!empty($numero_version)) {

				// on ajoute le versionning du pdf
				$nom_du_pdf_sans_extension = str_replace('.pdf', '', $nom_du_pdf);
				$nom_du_pdf = $nom_du_pdf_sans_extension.'_'.$numero_version.'.pdf';
			}

			//on ajoute le nouveau versionning
			management('versionning_document')->enregistre([
				'date' => now()->format('Y-m-d'),
				'type_element' => $this->_type_element,
				'id_document' => $this->modele->id,
				'reference' => $this->modele->reference_document,
				'url_document' => $chemin_final.$nom_du_pdf,
				'montant' => $this->modele->montant_document_ttc,
				'hash_contenu' => $hash_contenu,
			]);
		}

        return $nom_du_pdf;
	}


	/**
	 *
	 * Retourne le chemin vers le fichier PDF
	 *
	 */
	public function recupere_chemin_pdf($forcer_regeneration = false, $id_modele_doc = false) {

		$chemin_pdf = $this->modele->pdf;

		if(!empty($id_modele_doc) || $forcer_regeneration || empty($chemin_pdf) || !is_file(storage_path('app/'.$chemin_pdf))) {

            try{

                $chemin_pdf = $this->creation_pdf($id_modele_doc);

            }
            catch (\Exception $e){

                $pdf = PDF::loadView("eden::pdf.erreur_generation_pdf", ['source_erreur' => $e->getMessage() . 'Line :' . $e->getLine()]);
                \Storage::put($this->_type_element . '_' . $this->modele->id . '_' . date('d') . '.pdf', $pdf->output());
                return $this->_type_element . '_' . $this->modele->id . '_' . date('d') . '.pdf';

            }

			$this->reload_modele();
		}

		return $chemin_pdf;
	}


	/**
	 *
	 * Retourne le chemin final et le nom du pdf généré
	 *
	 **/
    protected function recupere_chemin_nom_pdf($id_modele_doc) {

        if($id_modele_doc !== false)
            return parent::recupere_chemin_nom_pdf($id_modele_doc);

		$nom_du_pdf = $this->modele->reference_document.'_'.$this->modele->id.'.pdf';

		$chemin_base = 'gescom/';
		$date = $this->modele->date;
		$date_annee = substr($date,0,4).'/';
		$date_mois = substr($date,5,2).'/';

		// Cas où le dossier "gescom" n'existe pas, on le crée
		if(!File::isDirectory($chemin_base))
	        File::makeDirectory($chemin_base, $mode = 0777, true, true);

		// Cas où le dossier de l'année n'existe pas, le dossier du mois à l'intérieur n'existe donc pas
		if(!File::isDirectory($chemin_base.$date_annee)){

			$chemin_final = $chemin_base.$date_annee.$date_mois;
	        File::makeDirectory($chemin_final, $mode = 0777, true, true);
	    }
	    else{

	    	// Le dossier de l'année existe, on vérifie si celui du mois existe également
	    	$chemin_final = $chemin_base.$date_annee.$date_mois;
		    File::makeDirectory($chemin_final, $mode = 0777, true, true);
	    }

	    return [$chemin_final, $nom_du_pdf];
	}



	protected function genere_donnees_pour_pdf() {

		$articles = $this->articles();

        // on regarde s'il y a une promo ?
		$promo = false;

		foreach($articles as $ligne) {

			if($ligne->remise > 0)
				$promo = true;
		}

		foreach($articles as $index => $article) {

			// on vérifie si l'article à une majoration
			if($article->remise < 0) {

				$article->tarif = $article->tarif * (1 + (abs($article->remise) / 100 ));
				$article->remise = 0;
			}

            if(!empty($article->description) && fonctionnalite('description_wysiwyg_documents') !== true)
                $article->description = nl2br(str_replace(array('<', '>'), array('&lt;', '&gt;'), $article->description));

		}

		// On ne fait ceci que si c'est la facture la plus ancienne
		$acomptes = array();

		if($this->_type_element == 'facture_vente') {

			$factures = $this->documents_lies('facture_vente');

			$facture_la_plus_ancienne['type_element'] = 'facture_vente';
			$facture_la_plus_ancienne['type'] = 'facture';
			$facture_la_plus_ancienne['management'] = management('facture_vente',$this->modele->id);

			foreach($factures as $facture) {

				if($facture['management']->modele->date == $facture_la_plus_ancienne['management']->modele->date && $facture['management']->modele->id < $facture_la_plus_ancienne['management']->modele->id)
					$facture_la_plus_ancienne = $facture;

				elseif($facture['management']->modele->date < $facture_la_plus_ancienne['management']->modele->date)
					$facture_la_plus_ancienne = $facture;
			}

			// On est sur la facture la plus ancienne
			if ($facture_la_plus_ancienne != null && ($this->modele != null && $facture_la_plus_ancienne['management']->modele->id == $this->modele->id)) {

				$acomptes = $this->documents_lies('acompte_vente');

				foreach($acomptes as $id_acompte => $acompte) {

					if(empty($acompte['management']->modele->valide) || !empty($acompte['management']->modele->annulee_par_avoir))
						unset($acomptes[$id_acompte]);
				}
			}

		}

        $calcule_total_document = $this->calcule_total_document();

        $eco_contribution = false;

        foreach ($calcule_total_document['par_ligne_eco_contribution'] as $eco_contribution_ligne) {

            if ($eco_contribution_ligne > 0)
                $eco_contribution = true;
        }

        $lignes_divers = $this->lignes_divers_document();

        $index_ligne = -1;

        foreach($lignes_divers as $ligne){

            if($ligne->ligne == 0) {
                $index_ligne++;
                $ligne->index_ligne = $index_ligne;
            }
        }

        foreach ($articles as $article) {

            $index_ligne++;
            $article->index_ligne = $index_ligne;

            foreach ($lignes_divers as $ligne) {

                $ligne->type_ligne = $ligne->type;

                if ($article->ligne == $ligne->ligne) {

                    $index_ligne++;
                    $ligne->index_ligne = $index_ligne;
                }
            }
        }

        $client_modele = modele('client', $this->modele->client_id);

		$donnees_pour_pdf = array(

			'document' => clone $this->modele,
			'client' => $client_modele,
			'client_management' => management('client', $this->modele->client_id, $client_modele),
			'document_management' => $this,
			'entite_management' => management('entite', $this->modele->entite_id),
			'tableau_tva' => $calcule_total_document['par_tva'],
			'tableau_tva_par_taux' => $calcule_total_document['par_tva'],
			'paiements' => $this->paiements(),
			'echeances' => $this->echeances(),
			'articles' => $articles,
			'lignes_divers' => $lignes_divers,
			'promo' => $promo,
			'nom_document' => table_libre($this->_type_element)->element,
			'type_element' => $this->_type_element,
			'adresse_de_facturation' => management('adresse', $this->modele->adresse_de_facturation),
			'adresse_de_facturation_formatee' => service('pdf_gescom')->adresse_facturation_formatee($this),
			'adresse_de_livraison' => management('adresse', $this->modele->adresse_de_livraison),
			'adresse_de_livraison_formatee' => service('pdf_gescom')->adresse_livraison_formatee($this),
			'calcule_total_document' => $calcule_total_document,
			'acomptes' => $acomptes,
			'contacts' => \DB::table($this->_type_element.'_contacts_ids')->where('cle_locale', $this->modele->id)->get()->toArray(),
			'frais_de_port' => $this->calcule_frais_de_port_via_articles(),
			'eco_contribution' => $eco_contribution,

			// pour les surcharges, pour envoyer des infos spécifiques
			'contenu_specifique_document' => $this->contenu_specifique_document(),
		);

        if(!empty($donnees_pour_pdf['document']['commentaires']))
            $donnees_pour_pdf['document']['commentaires'] = nl2br(str_replace(array('<', '>'), array('&lt;', '&gt;'), $donnees_pour_pdf['document']['commentaires']));

		// si on est dans un document d'achat, on ajoute les infos du fournisseur
		// et on retouche les informations des adresses
		if($this->est_un_achat()) {

			$donnees_pour_pdf['adresse_de_facturation'] = management('adresse_interne', $this->modele->adresse_de_facturation);

			$adresse_fournisseur = modele('adresse')->where('fournisseur_id', $this->modele->fournisseur_id)->first();

			if($adresse_fournisseur !== null)
				$donnees_pour_pdf['adresse_du_fournisseur'] = management('adresse', $adresse_fournisseur->id);
			else
				$donnees_pour_pdf['adresse_du_fournisseur'] = management('adresse');

            $type_adresse = $this->modele->a_livrer_chez_client ? 'adresse' : 'adresse_interne';
			$donnees_pour_pdf['adresse_de_livraison'] = management($type_adresse, $this->modele->adresse_de_livraison);
			$donnees_pour_pdf['fournisseur'] = modele('fournisseur', $this->modele->fournisseur_id);
			$donnees_pour_pdf['fournisseur_management'] = management('fournisseur', $this->modele->fournisseur_id);
		}

		$projet = management('projet');
		if(!empty($this->modele->projet_id)) {

			$projet = management('projet', $this->modele->projet_id);
		}

		$compte_bancaire = management('compte_bancaire');
		if(!empty($this->modele->compte_bancaire_id)) {

			$compte_bancaire = management('compte_bancaire', $this->modele->compte_bancaire_id);
		}

		$contact = management('contact');

		$contact_tmp = $this->modele->contacts()->first();
		if(!empty($contact_tmp)) {

			$contact = management('contact', $contact_tmp->id);
		}

		// on regarde s'il faut remplacer les codes articles par ceux des clients ou ceux des fournisseurs ?
		if(strpos($this->_type_element, 'achat') !== false) {

			$fournisseur = management('fournisseur', $this->modele->fournisseur_id);

			if($fournisseur->modele->utiliser_references_fournisseur == 1) {

				foreach($donnees_pour_pdf['articles'] as $ligne_article) {

					$article_fournisseur = modele('article_fournisseur')
												->where('article_id', $ligne_article->article_id)
												->where('fournisseur_id', $this->modele->fournisseur_id)
												->first();

					if($article_fournisseur === null)
						continue;

					if(empty($article_fournisseur->reference))
						continue;

					// on remplace le code article
					$ligne_article->code_article = $article_fournisseur->reference;
				}
			}
		}

		$donnees_pour_pdf['projet_management'] = $projet;
		$donnees_pour_pdf['projet'] = $projet->modele;
		$donnees_pour_pdf['compte_bancaire_management'] = $compte_bancaire;
		$donnees_pour_pdf['compte_bancaire'] = $compte_bancaire->modele;
		$donnees_pour_pdf['contact_management'] = $contact;
		$donnees_pour_pdf['contact'] = $contact->modele;

		return $donnees_pour_pdf;
	}

	/**
	 *
	 * Retourne true si on ne doit pas envoyer les acomptes au modèle de doc pdf, false si on doit les envoyer
	 *
	 */
	public function utiliser_article_acompte_sur_facture() {

		if($this->_type_element != 'facture_vente')
			return true;

		if(!empty(fonctionnalite('compta_article_id_pour_acompte')))
			return true;

		return false;
	}

	/**
	 *
	 * Calcule le montant des frais de port via la liste des articles
	 *
	 */
	public function calcule_frais_de_port_via_articles($articles = false) {

		if($articles === false)
			$articles = $this->articles();

		$frais_de_port = 0;

		foreach($articles as $article) {

			if($article->modele->type_article == 2) {

				// a priori pas de remise générale sur les frais de port
				$frais_de_port += $article->quantite * $article->tarif * (100 - $article->remise) / 100;
			}
		}

		return $frais_de_port;
	}

	/**
	 *
	 * Fait pour gérer le spécifique
	 *
	 */
	protected function contenu_devis() {

		return '';
	}

	/**
	 *
	 * Pour gérer du contenu spécifique
	 *
	 */
	protected function contenu_specifique_document() {

		return '';
	}

	/**
	*
	* On enregistre le document comme réglé
	*
	* @return void
	*
	*/
	public function enregistre_comme_regle() {

		if ( isset($this->modele) && isset($this->modele->regle) && $this->modele->regle == 1 )
			return;

		/*

		@note Frédéric : je bloque ce contrôle, car en réalité on ne crée pas de paiement
		et il est courant que des ADV aient la possibilité de créer des avoirs (annulation de facture)
		sans pour autant pouvoir créer des paiements

		// l'utilisateur a t il le droit de faire cela ?
		$test = profil_creation('paiement', $this->modele->entite_id);

		if($test !== true)
			return $test;
		*/

		$modifications = array(

			'regle' => 1,
			'date_changement_statut' => date('Y-m-d'),
		);

		if(in_array($this->_type_element, array('facture_vente', 'acompte_vente'))) {

			$modifications['statut'] = 20;
		}
		elseif(in_array($this->_type_element, array('avoir_vente'))) {

			$modifications['statut'] = 30;
		}

		$this->enregistre_modele($modifications);

		// on enregistre le log comme quoi le document est réglé
		$this->log_reglement();
	}

	/**
	 *
	 * Mise à jour du total du document
	 *
	 */
	public function maj_total_document($enregistrement_classique = false) {

		$nombre_de_chiffres_decimaux_sur_les_tarif = fonctionnalite('nombre_de_chiffres_decimaux_sur_les_tarif');

        $articles = $this->lignes_du_document_pour_saisie(false, false);

		$totaux = $this->calcule_total_document($articles);

		$modifications = array(

			'montant_document_ht' => round($totaux['ht'], $nombre_de_chiffres_decimaux_sur_les_tarif),
			'montant_document_ttc' => round($totaux['ttc'], $nombre_de_chiffres_decimaux_sur_les_tarif),
			'montant_document_tva' => round($totaux['tva'], $nombre_de_chiffres_decimaux_sur_les_tarif),
			'solde_document_ttc' => round($totaux['solde_ttc'], $nombre_de_chiffres_decimaux_sur_les_tarif),
		);


		// on enregistre le solde si nécessaire
		$documents_avec_reglements = array('facture_vente', 'avoir_vente', 'acompte_vente', 'facture_achat', 'avoir_achat', 'acompte_achat');

		if(fiche("commande_vente")->presence_module('fiche_commande_vente_paiement'))
			$documents_avec_reglements[] = 'commande_vente';
		if(fiche("devis_vente")->presence_module('fiche_devis_vente_paiement'))
			$documents_avec_reglements[] = 'devis_vente';


		if(!in_array($this->_type_element, $documents_avec_reglements)) {

			unset($modifications['solde_document_ttc']);
		}

		// on remplace
        if($enregistrement_classique)
		    $this->enregistre($modifications);
        else
		    $this->enregistre_modele($modifications);

		// est ce que le solde est à 0 ? Si oui, on enregistre le document comme réglé
		if(in_array($this->_type_element, $documents_avec_reglements) && empty($this->modele->regle)) {

			if($this->modele->solde_document_ttc == 0 && $this->modele->montant_document_ht != 0)
				$this->enregistre_comme_regle();
		}

        $this->ajoute_information_calculer_sur_lignes($totaux,$articles);

		return $modifications;
	}

	/**
	 *
	 * Calcule le total du document
	 *
	 * @param $articles un array ou une collection d'objet articles.
	 *
	 * @return array
	 *
	 */
    public function calcule_total_document($articles = false, $articles_sans_lignes_divers = false) {

		// log_eden('Document_management::calcule_total_document debut', 2);


        $mode_calcul = config('eden.mode_calcul_gescom');

        // pour les achats, on est forcément en HT
        if($this->est_un_achat())
            $mode_calcul = 'ht';

        if($articles === false) {

			if($articles_sans_lignes_divers === false)
				$articles = $this->articles();
			else
				$articles = $articles_sans_lignes_divers;

            if($articles instanceof Collection)
                $articles = $articles->toArray();

            if(isset($this->modele->id)) {

                //On ajoute les remises et les sous totaux
                $lignes = Ligne_divers_document::where('document_id', $this->modele->id)->where('type_element',$this->_type_element)->orderBy('ligne')->get()->toArray();

                $articles_avec_lignes = [];

                foreach($lignes as $ligne){

                    $ligne['type_ligne'] = $ligne['type'];

                    if($ligne['ligne'] == 0)
                        $articles_avec_lignes[] = $ligne;
                }

                foreach ($articles as $article) {

                    $articles_avec_lignes[] = $article;

                    foreach ($lignes as $ligne) {

                        $ligne['type_ligne'] = $ligne['type'];

                        if ($article['ligne'] == $ligne['ligne'])
                            $articles_avec_lignes[] = $ligne;
                    }
                }

                $articles = $articles_avec_lignes;
            }

        }

		// log_eden('Document_management::calcule_total_document recuperation articles', 2);

        // on effectue le calcul
        $totaux = $this->{'calcule_suivant_methode_'.$mode_calcul}($articles);

		// log_eden('Document_management::calcule_total_document calcul', 2);

		// temps_execution('calcule_total_document::calcul', 2);

        // on calcule le solde
        $paiements = $this->paiements();

		// log_eden('Document_management::calcule_total_document paiements', 2);

		// temps_execution('calcule_total_document::paiements', 2);

        $montant_paiements_ttc = 0;

        foreach($paiements as $paiement) {

            if(($this->est_un_achat() && $this->_type_element != 'avoir_achat') || $this->_type_element == 'avoir_vente')
                $montant_paiements_ttc += $paiement->montant * -1;
            else
                $montant_paiements_ttc += $paiement->montant;
        }

        $totaux['solde_ttc'] = $totaux['ttc'] - $montant_paiements_ttc;

        // on regarde s'il y a des acomptes
        // que si on n'est pas dans le cas d'une facture d'avancement,
        // sinon les acomptes sont déjà pris en compte
        if(!empty($this->modele) && $this->modele->type_facture != 1 && $this->modele->type_facture != 2 && $this->_type_element == 'facture_vente' && !$this->utiliser_article_acompte_sur_facture()) {


            // On ne fait ceci que si c'est la facture la plus ancienne
            $factures = $this->documents_lies('facture_vente');

            $facture_la_plus_ancienne['type_element'] = 'facture_vente';
            $facture_la_plus_ancienne['type'] = 'facture';
            $facture_la_plus_ancienne['management'] = management('facture_vente',$this->modele->id);

            foreach($factures as $facture) {

                if($facture['management']->modele->date == $facture_la_plus_ancienne['management']->modele->date && $facture['management']->modele->id < $facture_la_plus_ancienne['management']->modele->id)
                    $facture_la_plus_ancienne = $facture;

                else if($facture['management']->modele->date < $facture_la_plus_ancienne['management']->modele->date)
                    $facture_la_plus_ancienne = $facture;
            }

            // On est sur la facture la plus ancienne
            if ($facture_la_plus_ancienne != null && ($this->modele != null && $facture_la_plus_ancienne['management']->modele->id == $this->modele->id)) {

                $acomptes = $this->documents_lies('acompte_vente');

                foreach($acomptes as $acompte) {

					if(empty($acompte['management']->modele->annulee_par_avoir))
						$totaux['solde_ttc'] -= $acompte['management']->modele->montant_document_ttc;
                }
            }
        }

        return $totaux;
    }

	/**
	 *
	 * Calcule le total du document en partant des prix HT
	 *
	 * @return array
	 *
	 */
	public function calcule_suivant_methode_ht($articles, $frais_de_port_saisie = null) {

		$service = service('calcul_total_sur_document');

		if(isset($this->debug_test) && $this->debug_test === true)
			$service->debug_test = true;

        if($this->articles_du_document === false)
            $this->charger_articles_du_document($articles);

		return $service->calcule('ht', $articles, $this, $frais_de_port_saisie);
	}

	/**
	 *
	 * Calcule le total du document en partant des prix TTC
	 *
	 * @return array
	 *
	 */
	public function calcule_suivant_methode_ttc($articles, $frais_de_port_saisie = null) {

        if($this->articles_du_document === false)
            $this->charger_articles_du_document($articles);

		return service('calcul_total_sur_document')->calcule('ttc', $articles, $this, $frais_de_port_saisie);
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
	protected function methodes_post_modification_document($modele, $modele_avant, $modifications) {

		log_eden("Document_management::methodes_post_modification_document::debut (".$this->_type_element.", ".$this->modele->id.")", 0);

		// si le montant est à 0, on considère comme réglé
		if(empty($this->modele->solde_document_ttc) && $this->modele->valide == 1) {

			if(in_array($this->_type_element, array('facture_vente', 'avoir_vente', 'acompte_vente', 'facture_achat', 'avoir_achat', 'acompte_achat')))
				$this->enregistre_comme_regle();
		}



		// on met à jour les numéros de lots
		$this->gere_numero_de_lots();
		log_eden("Document_management::methodes_post_modification_document::gere_numero_de_lots", 2);

		// on enregistre la marge du document
		$this->calcule_marge();
		log_eden("Document_management::methodes_post_modification_document::calcule_marge", 2);

		// on calcule le reliquat de CA pour les commandes vente
		$this->calcule_reliquat_ca();
		log_eden("Document_management::methodes_post_modification_document::calcule_reliquat_ca", 2);

		// on met à jour la marge sur le projet
		$this->met_a_jour_marge_sur_projet();
		log_eden("Document_management::methodes_post_modification_document::met_a_jour_marge_sur_projet", 2);

		// on met à jour le statut
		$this->gere_statut_automatique();
		log_eden("Document_management::methodes_post_modification_document::gere_statut_automatique", 2);
		
		$validation_automatique = $this->validation_automatique_document();

        // on met à jour les index de recherche des lignes
        if(!$validation_automatique) {

			$this->gerer_pdf_post_modification();

			$this->management_ligne()->trigger_applicatif_elements_multiples($this->lignes_articles()->pluck('id')->toArray());

            $this->maj_index_recherche_lignes($this->modele);
		}

        $this->trigger_applicatif();

        $this->gestion_lignes();

		log_eden("Document_management::methodes_post_modification_document::maj_index_recherche_lignes", 2);

		// on crée un échange automatiquement
		if(empty($modele_avant->id)){

            if($this->est_une_vente())
                service('alimentation_timeline')->alimentation_timeline('creation_'.$this->_type_element, 'client', $this->modele->client_id, traduction('module_sur_fiche.client.timeline.nouveau_document') . " (".table_libre($this->_type_element)->element.") " . traduction('module_sur_fiche.client.timeline.cree') . ": #lien/" . $this->_type_element . "/" . $this->modele->id . "#");
            else
                service('alimentation_timeline')->alimentation_timeline('creation_'.$this->_type_element, 'fournisseur', $this->modele->fournisseur_id, traduction('module_sur_fiche.client.timeline.nouveau_document') . " (".table_libre($this->_type_element)->element.") " . traduction('module_sur_fiche.client.timeline.cree') . ": #lien/" . $this->_type_element . "/" . $this->modele->id . "#");
        }

		log_eden("Document_management::methodes_post_modification_document::alimentation_timeline", 2);
	}

	/**
	 *
	 * On voit si on doit automatiquement valider le document
	 *
	 * @todo voir si on doit retourner un message d'erreur ?!
	 *
	 */
	protected function validation_automatique_document() {

		// document déjà validé
		if(!empty($this->modele->valide))
			return false;

		// possibilité de valider un devis automatiquement
		if(isset(fonctionnalite('valider_document_enregistrement')[$this->_type_element]) && fonctionnalite('valider_document_enregistrement')[$this->_type_element] === true) {
            $this->valide();
            return true;
        }

        return false;
	}

	/**
	 *
	 * Trigger post validation d'un document
	 *
	 * @return boolean
	 *
	 */
	protected function methodes_post_validation_document($modele) {

		// si le montant est à 0, on considère comme réglé
		if(empty($this->modele->solde_document_ttc)) {

			if(in_array($this->_type_element, array('facture_vente', 'avoir_vente', 'acompte_vente', 'facture_achat', 'avoir_achat', 'acompte_achat')))
				$this->enregistre_comme_regle();
		}

		// On va valider les lignes de document
		$this->validation_ligne_docuement($modele);

        $this->maj_index_recherche();

        $this->management_ligne()->trigger_applicatif_elements_multiples($this->lignes_articles()->pluck('id')->toArray());

        $this->trigger_applicatif();

        // on met à jour les index de recherche des lignes
        $this->maj_index_recherche_lignes($modele);

        return true;
	}

	/**
	 *
	 * On va passer "validé" à 1 sur toutes les lignes du document
	 *
	 * @return void
	 *
	 */
	public function validation_ligne_docuement($modele) {
        DB::select('UPDATE '.$this->_type_element.'_lignes SET valide = 1 WHERE document_id = '.$this->modele->id.';');
	}

	/**
	 *
	 * Trigger post refus d'un document
	 *
	 * @return void
	 *
	 */
	protected function methodes_post_annulation_reglement_document($modele) {

	}

	/**
	 *
	 * Trigger post validation reglement d'un document
	 *
	 * @return void
	 *
	 */
	protected function methodes_post_validation_reglement_document($modele) {

	}



	/**
	 *
	 * Trigger post facturation d'un document
	 *
	 * @return void
	 *
	 */
	protected function methodes_post_facturation_document($modele) {

	}

	/**
	 *
	 * Trigger post expédition d'un document
	 *
	 * @return void
	 *
	 */
	protected function methodes_post_expedition_document($modele) {

	}

	/**
	 *
	 * Trigger post commandes fournisseur réalisée
	 *
	 * @return void
	 *
	 */
	protected function methodes_post_commande_fournisseur_realisee($modele) {

	}

	/**
	 *
	 * Trigger post commandes fournisseur reçue
	 *
	 * @return void
	 *
	 */
	protected function methodes_post_commande_fournisseur_recue($modele) {

	}

	/**
	 *
	 * On met à jour la marge sur le projet
	 *
	 */
	protected function met_a_jour_marge_sur_projet() {

		if(empty($this->modele->projet_id))
			return;

		$projet_management = management('projet', $this->modele->projet_id);

		$projet_management->calcul_marge();

	}

	/**
	 *
	 * On met à jour les numéros de lots
	 *
	 */
	protected function gere_numero_de_lots() {

		if(!fonctionnalite('numeros_de_lot'))
			return;

		modele('lot_mouvement')->where('type_element', $this->_type_element)->where('element_id', $this->modele->id)->delete();

		foreach($this->articles() as $article){

			// On regarde si le lot doit être enregistré en base ou non ( on le met que si il n'existe pas )
			if(empty($article->numeros_de_lot))
				continue;

			$lots = json_decode($article->numeros_de_lot);

			if(!is_array($lots))
				continue;

			if(empty($lots))
				continue;

			foreach($lots as $lot) {

				if ($lot->numero_de_lot != null) {

					$modele_lot_count = modele('lot')->where('numero',$lot->numero_de_lot)->count();

					if ($modele_lot_count == 0) {

						// On créer le lot sur la table des lots
						$nouveau_lot = management('lot');
						$informations_lot = array();

						$informations_lot['numero'] = $lot->numero_de_lot;
						$informations_lot['article_id'] = $article->article_id;

						if ($lot->peremption != null)
							$informations_lot['date_peremption'] = $lot->peremption;

						elseif($lot->date_de_peremption != null)
							$informations_lot['date_peremption'] = $lot->date_de_peremption;

						$nouveau_lot->enregistre($informations_lot);
					}

					// A ce stade, le lot est forcément en base car si il n'existait pas on l'a créé au dessus
					$modele_lot = modele('lot')->where('numero',$lot->numero_de_lot)->first();

					// On enregistre le mouvement
					$nouveau_mouvement = management('lot_mouvement');
					$information_mouvement = array();

					// On rempli les informations pour renseigner le mouvement
					$information_mouvement['type_element'] = $this->_type_element;
					$information_mouvement['element_id'] = $this->modele->id;
					$information_mouvement['date'] = $this->modele->date;
					$information_mouvement['quantite'] = $lot->quantite;
					$information_mouvement['lot_id'] = $modele_lot->id;

					$nouveau_mouvement->enregistre($information_mouvement);
				}

			}
		}
	}

	/**
	 *
	 * Permet de calculer les frais de livraison
	 *
	 */
	public function calcule_frais_de_transport($montant_ht, $articles, $formulaire) {

		return 0;
	}


	/**
	 *
	 * Ajoute les frais de port aux totaux des documents
	 *
	 * A noter que les frais de ports ne sont pas inclus dans la remise
	 *
	 */
	public function ajoute_frais_de_port_au_totaux($totaux, $articles, $frais_de_port_saisie) {


		// calcule les frais de ports
		$frais_de_port = $this->calcule_frais_de_transport($totaux['ht'], $articles, $this->modele);

		$frais_de_port_ttc = $frais_de_port * 1.2;

		// Si on a saisie les frais de port dans le document, on se base sur cette valeur
		if(!empty($frais_de_port_saisie)) {

			$frais_de_port = $frais_de_port_saisie / 1.20;
			$frais_de_port_ttc = $frais_de_port_saisie;
		}

        if($frais_de_port == 0) {
            $totaux['frais_de_transport'] = $frais_de_port;
            return $totaux;
        }

		// puis on les ajoute

		$totaux['ht'] += $frais_de_port;
		$totaux['ttc'] += $frais_de_port_ttc;
		$totaux['tva'] += $frais_de_port * 0.2;

		if(!isset($totaux['par_tva'][20])) {

			$totaux['par_tva'][20] = array(

				'ht' => 0,
				'tva' => 0,
				'ttc' => 0,
                'eco_contribution' => 0,
                'eco_contribution_tva' => 0,
                'eco_contribution_inclus' => 0,
                'tva_sans_eco_contribution' => 0,
			);
		}

		$totaux['par_tva'][20]['ht'] += $frais_de_port;
		$totaux['par_tva'][20]['ttc'] += $frais_de_port_ttc;
		$totaux['par_tva'][20]['tva'] += $frais_de_port * 0.2;

		// idem avant la remise
		$totaux['avant_remise']['ht'] += $frais_de_port;
		$totaux['avant_remise']['ttc'] += $frais_de_port_ttc;
		$totaux['avant_remise']['tva'] += $frais_de_port * 0.2;

		if(!isset($totaux['avant_remise']['par_tva'][20])) {

			$totaux['avant_remise']['par_tva'][20] = array(

				'ht' => 0,
				'tva' => 0,
				'ttc' => 0,
                'eco_contribution' => 0,
                'eco_contribution_tva' => 0,
                'eco_contribution_inclus' => 0,
                'tva_sans_eco_contribution' => 0,
			);
		}

		$totaux['avant_remise']['par_tva'][20]['ht'] += $frais_de_port;
		$totaux['avant_remise']['par_tva'][20]['ttc'] += $frais_de_port_ttc;
		$totaux['avant_remise']['par_tva'][20]['tva'] += $frais_de_port * 0.2;

		$totaux['frais_de_transport'] = $frais_de_port;

		if(!empty($frais_de_port_saisie))
			$totaux['frais_de_transport'] = $frais_de_port_ttc;

		return $totaux;
	}

	/**
	 *
	 * Trigger post annulation de facture
	 *
	 * Cette méthode est appelée après la suppression des factures et la création d'un avoir
	 *
	 * @return void
	 *
	 */
	protected function methodes_post_annulation_par_avoir($avoir) {

	}

	/**
	 *
	 * On surcharge la méthode enregistre pour gérer les lignes des documents
	 *
	 */
	public function enregistre($modifications = array(), $modele = false) {

        if(!defined('gestion_lignes') && !isset($this->management_principal))
            define('gestion_lignes', $this->_type_element);

		if(isset($modifications['valide']))
			return traduction('messages.php.document.modification_champ_valide_enregistre');

		if(isset($this->modele))
			$modele_avant = clone($this->modele);
		else
			$modele_avant = modele($this->_type_element);

		// on retraite le tableau $modifications en fonction des règles de gestion
		$modifications = $this->retraite_modifications($modifications);

		// on vérifie que les clients livrés et facturés sont de la même entité
		$retour = $this->verifie_client_facture_et_livre_sont_de_la_meme_entite($modifications);

		if($retour !== true)
			return $retour;

		// on vérifie qu'il n'y a pas de changement d'entité chez les clients
		$retour = $this->verifie_pas_de_changement_entite($modifications);

		if($retour !== true)
			return $retour;

		// on vérifie si les articles utilisés peuvent être utilisés autre part que sur les devis
		$retour = $this->verifie_article_utilisable_que_sur_les_devis($modifications);

		if($retour !== true)
			return $retour;

        // Vérifie s'il y a des articles non disponibles
        if(isset($modifications['articles'])) {
            $retour = $this->verifie_articles_disponibles($modifications);
            if ($retour !== true)
                return $retour;
        }

		// vérifications spécifiques aux documents ou aux projets
		$retour = $this->verifications_specifiques($modifications);

		if($retour !== true)
			return $retour;

		// on récupère les articles dans une variable
		$articles = [];

		if(isset($modifications['articles'])) {

            $this->charger_articles_du_document($modifications['articles']);

            $articles = $modifications['articles'];

			$retour = $this->verifie_articles_supprimes();

			if($retour !== true)
				return $retour;

			unset($modifications['articles']);
		}

		// on récupère les lignes divers dans une variable
		$lignes_divers = false;

		if(isset($modifications['lignes_divers'])) {

			$lignes_divers = $modifications['lignes_divers'];
			unset($modifications['lignes_divers']);
		}


		// on récupère les infos de la récurrence
		$recurrence = false;

		// on récupère les informations lées à la récurrence
		if(isset($modifications['recurrence_activee'])) {

			$recurrence = $this->recupere_informations_recurrence($modifications);

		}

        // on gére l'adresse de facturation si elle est désactivé à l'affichage
        $modifications = $this->gestion_adresse_facturation($modifications);

        // on test d'enregistrer les articles
        if(!empty($articles)) {

            $creation = false;

            if(!isset($this->modele) || $this->modele->exists == false) {
                $creation = true;
                $this->modele = $modele_avant;
            }

            $retour_article = $this->enregistre_articles($articles, true);

            if($creation)
                $this->modele = null;

            if($retour_article != 'test_ok' && !empty($retour_article))
                return traduction('messages.php.document.erreur_enregistrement_article') . ' : ' .$retour_article;
        }

        $this->fonction_a_eviter[] = 'retraite_modifications';
        $this->fonction_a_eviter[] = 'maj_index_recherche';
        $this->fonction_a_eviter[] = 'trigger_applicatif';
        $this->fonction_a_eviter[] = 'log_modifications';

		$retour = parent::enregistre($modifications, $modele);

		log_eden("Document_management::enregistre::après parent::enregistre (".$this->_type_element.", ".$this->modele->id.")", 0);

		if($retour !== true)
			return $retour;

        // on enregistre les lignes_divers
        if($lignes_divers !== false) {
            $articles = $this->enregistre_lignes_divers($lignes_divers, $articles);
        }

		// on enregistre les articles
        if(!empty($articles)) {

			log_eden("Document_management::enregistre::avant enregistre_articles (".$this->_type_element.", ".$this->modele->id.")", 0);
            $this->enregistre_articles($articles);
			log_eden("Document_management::enregistre::après enregistre_articles (".$this->_type_element.", ".$this->modele->id.")", 0);
        }

		// on enregistre la récurrence
		if($recurrence !== false) {

			$this->enregistre_recurrence($recurrence);
		}

		// mise à jour du total du document
		$totaux = $this->maj_total_document();
		log_eden("Document_management::maj_total_document  (".$this->_type_element.", ".$this->modele->id.")");

        if(!empty($this->informations_logs))
            $this->log_modifications(
                $this->modele,
                $this->informations_logs['modele_avant'],
                array_merge($this->informations_logs['modifications'],$totaux));

		// création de la référence document
		$this->creation_reference_document();

		// méthodes post modification / création
		$this->methodes_post_modification_document($this->modele, $modele_avant, $modifications);

		log_eden("Document_management::methodes_post_modification_document::fin dans enregistre()  (".$this->_type_element.", ".$this->modele->id.")");

		// on gère la récurrence à durée déterminée
		$this->gere_recurrence_a_duree_determinee();

        // On met à jour l'index recherche après que les articles soient enregistrés
		$this->maj_index_recherche();

		log_eden("Document_management::gere_recurrence_a_duree_determinee  (".$this->_type_element.", ".$this->modele->id.")");

		//On copie les échéances si le document un devis vente transformé en commande vente
        $post = request()->post();

        if(isset($post["type_element_source"]) && isset($post["id_element_source"]) && $post["type_element_source"] == 'devis_vente' && $this->_type_element == 'commande_vente'){

            management($post['type_element_source'], $post['id_element_source'])->copie_echeances($this);

        }

		return true;
	}

    /**
     *
     * Vérifie si on essaie d'enregistrer un document avec des articles non disponibles
     *
     */
    public function verifie_articles_disponibles($modifications) {

        $articles_non_disponibles = array();
        $articles_inactifs = array();

        $lignes_document = $modifications['articles'];
        $this->charger_articles_du_document($lignes_document);
        $articles = $this->articles_du_document;

        foreach($lignes_document as $article) {

            $this->verifie_article_enfants($article, $articles, $articles_non_disponibles, $articles_inactifs);

        }

        if(!empty($articles_non_disponibles))
            return traduction('messages.php.document.article_non_utilisable')." : \n" .implode(", ", $articles_non_disponibles);

        if(!empty($articles_inactifs))
            return traduction('messages.php.document.article_inactif')." : \n" .implode(", ", $articles_inactifs);

        return true;
    }

    /**
     *
     * Vérifie si les articles enfants sont inactifs ou non utilisable (récursif)
     *
     */
    public function verifie_article_enfants($article, $articles, &$articles_non_disponibles, &$articles_inactifs) {

        $article_dans_document = null;
        foreach ($articles as $article_item) {

            if(!is_array($article_item))
                $article_item = $article_item->toArray();

            if (
                (
                    isset($article['article_id'])
                    && isset($article_item['id']) && $article_item['id'] == $article['article_id']
                )
                || (
                    isset($article['article_enfant_id'])
                    && isset($article_item['id']) && $article_item['id'] == $article['article_enfant_id'])
            ) {
                $article_dans_document = $article_item;
                break;
            }
        }

        if(empty($article_dans_document) || $article_dans_document['inactif'] == 1) {
            $articles_inactifs[] = $article['designation'];
        }

        else if(
            $article_dans_document['disponible_pour_saisie'] == 3
            || (!in_array($this->_type_element, Variables::$documents_vente_gescom) && $article_dans_document['disponible_pour_saisie'] == 1)
            || (!in_array($this->_type_element, Variables::$documents_achat_gescom) && $article_dans_document['disponible_pour_saisie'] == 2)
            || (!in_array($this->_type_element, Variables::$documents_avoir_et_retour_vente) && $article_dans_document['disponible_pour_saisie'] == 4)
        ) {
            $articles_non_disponibles[] = $article['designation'];
        }

        if(!empty($article['nomenclature'])){

            if(!is_array($article['nomenclature']))
                $article['nomenclature'] = json_decode($article['nomenclature'], true);

            foreach($article['nomenclature'] as $article_enfant) {

                if(!is_array($article_enfant))
                    $article_enfant = (array) $article_enfant;

                $this->verifie_article_enfants($article_enfant, $articles,$articles_non_disponibles, $articles_inactifs);
            }
        }
    }


	/**
	 *
	 * Vérifie si on essaie d'enregistrer un document avec des articles supprimés
	 *
	 */
	public function verifie_articles_supprimes() {

		$articles_supprimes = array();

        $ids_article_document = $this->ids_articles_du_document;

        $articles_modele_supprimes = modele('article')
            ->avec_inactifs()
            ->whereIn('id',$ids_article_document)
            ->where('inactif',1)
            ->get();

		foreach($articles_modele_supprimes as $article_modele) {
            $articles_supprimes[] = management('article',$article_modele->id,$article_modele)->affiche().' (#'.$article_modele->id.')';
		}

		if(!empty($articles_supprimes))
			return traduction('messages.php.document.article_non_utilisable_supprime')." : \n".implode("\n", $articles_supprimes);

		return true;
	}

	/**
	 *
	 * Pour une duplication, on ajoute les articles et les lignes divers
	 *
	 */
	protected function retouche_donnees_pour_duplication($infos) {

		$infos['articles'] = $this->articles();

		foreach($infos['articles'] as $article) {

			unset($article->id);
		}

		$infos['lignes_divers'] = Ligne_divers_document::where('type_element', $this->_type_element)->where('document_id', $this->modele->id)->get()->toArray();

        foreach($infos['lignes_divers'] as &$ligne_divers) {

            if(isset($ligne_divers->type) && $ligne_divers->type == 'regroupement')
                $ligne_divers->id_temporaire = true;

            else
			    unset($ligne_divers['id']);
		}

		return $infos;
	}

	/**
	 *
	 * Lorsqu'on enregistre un document, on récupère les informations de la récurrence
	 *
	 */
	protected function recupere_informations_recurrence(&$modifications) {

		$recurrence = array();

        $champs_recurrence = $this->champs_blocs_recurrences();

        foreach($champs_recurrence as $champ){

            if(isset($modifications[$champ])){
                $recurrence[$champ] = $modifications[$champ];
                unset($modifications[$champ]);
            }
        }

		return $recurrence;
	}

	/**
	 *
	 * Récupère tous les documents liés à un document
	 *
	 * @param $type_element : si false = on retourne tous les documents, si défini on retourne que les documents de type $type_element
	 *
	 */
	public function documents_lies($type_element = false, $calcule_lien_direct = false) {

		if(!$this->existe())
			return array();

		$documents_lies = array();

		// on va tous les chercher via la requête SQL récursive, basée sur les transformations à la ligne
		$lignes_liees = service('documents_lies')->tous_documents_lies_recursif($this->_type_element, $this->modele->id);

		// la chaîne d'ascendance/descendance directe (sans les documents "frères"), pour la mise en
		// surbrillance à l'affichage uniquement : ce 2nd parcours a un coût, on ne le fait donc pas
		// pour les nombreux usages internes de documents_lies() (calculs de totaux, recherche de la
		// dernière facture/d'un avoir existant, etc.) qui n'exploitent jamais lien_direct
		$directement_lies = $calcule_lien_direct
			? service('documents_lies')->documents_directement_lies($this->_type_element, $this->modele->id)
			: array();

		foreach($lignes_liees as $ligne_liee) {

			// c'est le document lui-même
			if($ligne_liee->type_element == $this->_type_element && $ligne_liee->id_document == $this->modele->id)
				continue;

			if($type_element !== false && $ligne_liee->type_element != $type_element)
				continue;

			$lien_direct = isset($directement_lies[$ligne_liee->type_element.'#'.$ligne_liee->id_document]);

			service('documents_lies')->ajoute_document_au_tableau_des_documents_lies($documents_lies, $ligne_liee->type_element, $ligne_liee->id_document, $lien_direct);
		}

		return $documents_lies;
	}

	/**
	 *
	 * Parmi le document actuel et ses documents liés, regroupe ceux qui appartiennent à une récurrence
	 * (plusieurs documents peuvent partager la même récurrence, ex : plusieurs occurrences d'une facture récurrente)
	 *
	 * Retourne un tableau [id_recurrence => [...documents de cette récurrence (même format que documents_lies)...]]
	 *
	 */
	public function documents_par_recurrence($documents_lies) {

		$tous_les_documents = $documents_lies;

		// on ajoute le document actuel, qui peut lui aussi appartenir à une récurrence
		if($this->existe())
			service('documents_lies')->ajoute_document_au_tableau_des_documents_lies($tous_les_documents, $this->_type_element, $this->modele->id);

		$documents_par_recurrence = array();
		$ids_recurrence_traites = array();

		foreach($tous_les_documents as $document) {

			$id_recurrence = $document['modele']->id_recurrence ?? null;

			if(empty($id_recurrence) || in_array($id_recurrence, $ids_recurrence_traites))
				continue;

			$ids_recurrence_traites[] = $id_recurrence;

			$documents_de_la_recurrence = array();

			$documents = modele($document['type_element'])
				->where('id_recurrence', $id_recurrence)
				->orderBy('date')
				->orderBy('id')
				->get();

			foreach($documents as $document_de_la_recurrence) {

				service('documents_lies')->ajoute_document_au_tableau_des_documents_lies($documents_de_la_recurrence, $document['type_element'], $document_de_la_recurrence->id);
			}

			// inutile de regrouper une récurrence qui n'a qu'une seule occurrence
			if(count($documents_de_la_recurrence) > 1)
				$documents_par_recurrence[$id_recurrence] = $documents_de_la_recurrence;
		}

		return $documents_par_recurrence;
	}

	/**
	 *
	 * Retourne le modèle récurrence lié à ce document
	 *
	 */
	public function recurrence() {

		if(!isset($this->modele) || empty($this->modele))
			return null;

		return Recurrence::where('id', $this->modele->id_recurrence)->where(function($requete) {
						$requete->where('inactif', 0)->orWhereNull('inactif');
					})->first();
	}

	/**
	 *
	 * On enregistre les informations de la récurrence
	 *
	 */
	public function enregistre_recurrence($info_recurrence) {

		if(empty($info_recurrence['recurrence_activee'])) {

			// on annule la récurrence s'il y a lieu
			if(!empty($this->modele->id_recurrence)) {

				$recurrence = $this->recurrence();

				if($recurrence !== null) {

					$recurrence->inactif = 1;
					$recurrence->save();
				}
			}

			return true;
		}

		if(isset($info_recurrence['id_recurrence']) && !empty($info_recurrence['id_recurrence']))
			$managament_recurrence = management('eden_recurrence_elements',$info_recurrence['id_recurrence']);
		else
			$managament_recurrence = management('eden_recurrence_elements');

        $donnees_recurrence = array();

        $champs = $this->champs_blocs_recurrences();

        foreach($champs as $champ){

            if(in_array($champ,['id_recurrence','recurrence_activee']))
                continue;

            if(isset($info_recurrence[$champ]))
                $donnees_recurrence[$champ] = $info_recurrence[$champ];
        }

		$donnees_recurrence['type_element'] = $this->_type_element;

		// on enregistre le fait que l'on crée ou non cette récurrence
		$this->creation_recurrence = false;

		// la récurrence n'existe pas, il faut enregistrer la date de prochaine récurrence pour la RDI
		if($managament_recurrence->modele === null || $managament_recurrence->modele->exists === false) {

			// on récupère la date de la prochaine récurrence
            if(in_array($donnees_recurrence['mode_recurrence'], [0, 2, 3]) || !empty($donnees_recurrence['generation_progressive'])){
			    $donnees_recurrence['rdi_prochaine_occurence'] = $this->prochaine_date_recurrence($donnees_recurrence);
				$donnees_recurrence['rdd_occurences'] = null;
				$donnees_recurrence['rdd_periodicite'] = null;
				$donnees_recurrence['rdd_date_generation'] = null;
			}
            else{
                // que dans le cas d'une récurrence à durée déterminée
                $this->creation_recurrence = true;
				$donnees_recurrence['rdi_prochaine_occurence'] = null;
				$donnees_recurrence['rdi_mois_generation'] = null;
				$donnees_recurrence['rdi_date_generation'] = null;
			}

		}

		$managament_recurrence->enregistre($donnees_recurrence);

		// on enregistre sur le modèle
		$this->enregistre_modele(array('id_recurrence' => $managament_recurrence->modele->id));
	}

	/**
	 *
	 * Calcule la prochaine date pour la récurrence
	 *
	 */
	protected function prochaine_date_recurrence($recurrence,$date = null) {

        if($date == null)
            $date = $this->modele->date;

        $rdi_date_generation = $recurrence['rdi_date_generation'] == 0 ? 't' : substr('0'.$recurrence['rdi_date_generation'], -2);

		// on prend le 1er du mois suivant la date du document
        if($recurrence['mode_recurrence'] == 0)
		    $date_recurrence = date('Y-m-'.$rdi_date_generation, strtotime(formate_date('Y-m', $date).'-01 +1 month'));
        // on prend le 1er de l'année suivant la date du document
        else if($recurrence['mode_recurrence'] == 2)
            $date_recurrence = date('Y-'.substr('0'.$recurrence['rdi_mois_generation'], -2).'-'.$rdi_date_generation, strtotime(formate_date('Y', $date).'-01-01 +1 year'));
        // on prend le 1er du trimestre suivant la date du document
        else if($recurrence['mode_recurrence'] == 3)
            $date_recurrence = date('Y-m-'.$rdi_date_generation, strtotime(formate_date('Y-m', $date).'-01 +3 month'));
        else {

            if($recurrence['rdd_periodicite'] == 0 || $recurrence['rdd_periodicite'] == 3) {

				$nb_mois = $recurrence['rdd_periodicite'] == 0 ? 1 : 3;

                // Dernier jour du mois
                if ($recurrence['rdd_date_generation'] == 0) {

                    $date_recurrence = date('Y-m-t', strtotime($date . " +{$nb_mois} month"));

                    // Sinon, le jour sélectionné
                } else {

                    $rdd_date_generation = $recurrence['rdd_date_generation'];

                    // on ajoute un 0 à la date, afin d'avoir le bon format date => 01-02-2020 à la place 1-02-2020
                    if ($rdd_date_generation <= 9)
                        $rdd_date_generation = '0' . $rdd_date_generation;

                    $date_recurrence = date('Y-m-' . $rdd_date_generation, strtotime($date . " +{$nb_mois} month"));
                }
            }
            else if ($recurrence['rdd_periodicite'] == 1){

                $rdd_date_generation = $recurrence['rdd_date_generation'];
                $rdd_mois_generation = $recurrence['rdd_mois_generation'];

                if ($rdd_date_generation <= 9)
                    $rdd_date_generation = '0' . $rdd_date_generation;

                if ($rdd_mois_generation <= 9)
                    $rdd_mois_generation = '0' . $rdd_mois_generation;

                $date_recurrence = date('Y-'.$rdd_mois_generation.'-' . $rdd_date_generation, strtotime($date . ' +1 year'));

            }

        }

        return $date_recurrence;
	}

	/**
	 *
	 * Gestion de la récurrence à durée déterminée
	 *
	 * A l'enregistrement du document, si on vient de créer une récurrence à durée déterminée,
	 * Il faut alors dupliquer le document autant de fois que demandé
	 *
	 */
	protected function gere_recurrence_a_duree_determinee() {


		// on n'est pas dans le cadre d'une récurrence à durée déterminée
		if(!isset($this->creation_recurrence))
			return;

		// on n'est pas dans le cas de l'enregistrement d'une récurrence
		if($this->creation_recurrence !== true)
			return;

		// ok on doit alors dupliquer le document X fois
		$recurrence = $this->recurrence();

		// on n'est pas en récurrence rdd
		if(empty($recurrence->rdd_date_generation) || ($recurrence->rdd_periodicite == 1 && empty($recurrence->rdd_mois_generation)))
			return;

        $ancienne_date = null;

		for($i=1; $i<=$recurrence->rdd_occurences; $i++) {

			$modifications = array();

            $modifications['date'] = $this->prochaine_date_recurrence($recurrence, $ancienne_date);

            $ancienne_date = $modifications['date'];

			// On calcule la date de réglement
			$modifications['date_de_reglement'] = $this->calcule_date_reglement($this->modele->modalite_paiement_id, $modifications['date']);

			$retour = $this->transformer_document('facture_vente',$modifications);

            if($retour[0] !== true)
                return $retour[0];

            $document = $retour[1];

            // on renseigne l'id_recurrence
            $document->enregistre_modele(array('id_recurrence' => $this->modele->id_recurrence));

		}
	}


	/**
	 *
	 * On enregistre la prochaine récurrence
	 *
	 */
	protected function calcule_date_reglement($modalite_paiement_id, $date_facturation) {

		$date_facturation = formate_date('Y-m-d', $date_facturation);
		$modalite_paiement = modele('modalite_paiement', $modalite_paiement_id);

		if(empty($modalite_paiement) || empty($modalite_paiement_id))
			return array('en' => formate_date('Y-m-d', $date_facturation), 'fr' => formate_date('d/m/Y', $date_facturation));

		if(!empty($modalite_paiement->nombre_de_jours))
			$date_facturation = date('Y-m-d', strtotime($date_facturation." +".$modalite_paiement->nombre_de_jours." days "));

		if(!empty($modalite_paiement->fin_de_mois))
			$date_facturation = date('Y-m-t', strtotime($date_facturation));

		return response()->json(array('en' => formate_date('Y-m-d', $date_facturation), 'fr' => formate_date('d/m/Y', $date_facturation)));
	}


	/**
	 *
	 * On enregistre la prochaine récurrence
	 *
	 */
	public function prochaine_recurrence() {

		Log::info("Recurrence : dans Document_management (".$this->_type_element.", ".$this->modele->id."), laravel start : ".LARAVEL_START);

		// on récupère les infos de la récurrence
		$recurrence = $this->recurrence();

        $derniere_recurrence = modele($this->_type_element)->where('id_recurrence', $recurrence->id)->orderBy('date', 'desc')->first()->date;

		$modifications = array();

        $modifications['date'] = $this->prochaine_date_recurrence($recurrence,$derniere_recurrence);

		/*
		@todo gérer correctement la date de règlement pour les factures
		*/
		if(in_array($this->_type_element, array('acompte_vente', 'facture_vente', 'avoir_vente', 'acompte_achat', 'facture_achat', 'avoir_achat'))) {

			// c'est le cas par défaut
			$modifications['date_de_reglement'] = $modifications['date'];


			// on gère le cas ou il y a une modalité de paiement
			if(isset($this->modele->modalite_paiement_id) && !empty($this->modele->modalite_paiement_id)) {

				$modalite_paiement_management = management('modalite_paiement', $this->modele->modalite_paiement_id);

				$modifications['date_de_reglement'] = $modalite_paiement_management->calcule_date_reglement($modifications['date']);
			}
		}

		// on enregistre
		$retour = $this->transformer_document($this->_type_element,$modifications);

		if($retour[0] !== true)
			return $retour[0];

        $document = $retour[1];

		// on renseigne l'id_recurrence
		$document->enregistre_modele(array('id_recurrence' => $this->modele->id_recurrence));

        $prochaine_occurence = true;

        if($recurrence->mode_recurrence == 1){
            $nombre_documents = modele($this->_type_element)
                ->avec_inactifs()
                ->where('id_recurrence',$recurrence->id)
                ->count() - 1;

            if($nombre_documents == $recurrence->rdd_occurences)
                $prochaine_occurence = false;
        }

        if($prochaine_occurence)
            $recurrence->rdi_prochaine_occurence = $this->prochaine_date_recurrence($recurrence,$recurrence->rdi_prochaine_occurence);
        else
            $recurrence->rdi_prochaine_occurence = null;

		$recurrence->save();

        $date_prochaine_generation = $recurrence->rdi_prochaine_occurence;

        if(!empty($recurrence->delai_generation))
            $date_prochaine_generation = date('Y-m-d',strtotime($recurrence->rdi_prochaine_occurence.' -'.$recurrence->delai_generation.' days'));

		if($date_prochaine_generation != null && $date_prochaine_generation <= date('Y-m-d'))
			$document->prochaine_recurrence();

		return true;
	}

	/**
	 *
	 * Ajoute un paiement sur un document
	 *
	 * @param $type_element le type_element du document
	 * @param $id_element l'id_element du document
	 * @param $informations array les données pour l'enregistrement
	 *
	 */
	public function ajouter_paiement($type_element, $id_element, $informations) {

		return management($type_element, $id_element)->enregistre_paiement($informations);
	}

	/**
	 *
	 * Ajoute un paiement sur un document
	 *
	 * @param $type_element le type_element du document
	 * @param $id_element l'id_element du document
	 * @param $informations array les données pour l'enregistrement
	 *
	 */
	public function enregistre_paiement($donnees) {

		$management_paiement = management('paiement');

		$modele_avant = clone($this->modele);


		/*
		@todo retourner une erreur
		*/
		if(!isset($informations['compte_bancaire_id']))
			$informations['compte_bancaire_id'] = 0;

		if(!isset($informations['date']))
			$informations['date'] = date('Y-m-d');

		// on rajoute quelques champs en dur
		$donnees['entite_id'] = $this->modele->entite_id;
		$donnees['id_document'] = $this->modele->id;
		$donnees['type_element'] = $this->_type_element;

		if(empty($donnees['titre']))
			$donnees['titre'] = traduction('document.paiement_document.titre').' '.$this->modele->reference_document;

		if($this->est_une_vente()) {

			if(!empty($this->modele->client_id_tiers_payeur))
				$donnees['client_id'] = $this->modele->client_id_tiers_payeur;
			else
				$donnees['client_id'] = $this->modele->client_id;
		}

		if($this->est_un_achat()) {

			$donnees['fournisseur_id'] = $this->modele->fournisseur_id;
		}



		$retour = $management_paiement->enregistre($donnees);

		// on met à jour les données du modèle du document
		$this->reload_modele();

        $this->log_paiement($modele_avant);

		return $retour;
	}

	/**
	 *
	 * Retourne les articles du document (en incluant les lignes de nomenclatures)
	 *
	 */
	public function articles_avec_lignes_nomenclature() {

		return $this->articles(array(), false, true);
	}

	/**
	 *
	 * Retourne les articles du document
	 *
	 * @return array si pas de modèle lié au document
	 * @return une collection eloquent dans le cas contraire
	 *
	 */
	public function articles($ids = array(), $articles_precharges = false, $inclure_lignes_nomenclature = false, $inclure_modele = true) {

		if(isset($this->modele->id)) {

            $lignes_bdd = $this->modele_lignes()
                ->where('document_id', $this->modele->id)
                ->orderBy('ligne')
                ->get();

            if($inclure_lignes_nomenclature === false)
                $articles = $lignes_bdd->filter(function ($article) {
                    return empty($article->nomenclature_ligne_parent);
                })->values();
            else
                $articles = $lignes_bdd;

            $this->gestion_nomenclatures($articles,$lignes_bdd);
			// on va précharger les articles
			if($articles_precharges === false) {

				$ids_article = array();

				foreach($articles as $index => $article) {
					$ids_article[] = $article->article_id;
				}

				$articles_precharges = modele('article')
					->whereIn('id', $ids_article)
					->get()->keyBy('id');

				$champs_libres_multi_selection = champs_libres_multiselection('article');

				// on va chercher les valeurs pour les multi sélections
				foreach($champs_libres_multi_selection as $champ_libre) {

					$modele_table_pivot = table_libre_existe($champ_libre->table_pivot) ? modele($champ_libre->table_pivot) : \DB::table($champ_libre->table_pivot);

					$valeurs_multiselection[$champ_libre->nom_sql] = $modele_table_pivot
						->whereIn('cle_locale',$ids_article)
						->get()
						->groupBy('cle_locale')->map(function($valeurs){
							return $valeurs->pluck('valeur');
						})->toArray();
				}

				foreach($articles_precharges as $article_precharge){
					foreach ($valeurs_multiselection as $nom_sql => $valeurs) {
						$article_precharge->{$nom_sql} = $valeurs[$article_precharge->id] ?? [];
					}
				}
			}

			// log_eden("Document_management::articles::articles_precharges", 3);

			if(!empty($articles) && $inclure_modele) {

				foreach($articles as $index => $article) {

				    if(is_array($ids) && !empty($ids) && !in_array($article->article_id,$ids)){

                        unset($articles[$index]);
                        continue;
                    }

					if($articles_precharges !== false && isset($articles_precharges[$article->article_id])) {

						$article_modele = management('article', $article->article_id, $articles_precharges[$article->article_id])->modele;
					}
					else {

						$article_modele = modele('article', $article->article_id);
					}

					if(empty($article->code_article))
						$article->code_article = $article_modele->code_article;

					$article->modele = $article_modele;

                    if($article->unite != $article_modele->unite)
					    $article->unite = $article_modele->unite;

                    if(empty($article->conditionnement))
						$article->conditionnement = 0;

					// on vient ajouter les achats
					if(fonctionnalite('achats_sur_les_documents') === true) {

						$article->achats = modele('achat_sur_document')
									->where('type_element', $this->_type_element)
									->where('document_id', $this->modele->id)
									->where('ligne', $article->ligne)
									->get()
									->toArray();
					}
					else {

						$article->achats = array();
					}
				}
			}

			// log_eden("Document_management::articles::boucle", 3);

			return $articles;
		}

		return array();
	}

    /**
     *
     * Permet de gérer les données des nomenclatures et des produits assemblés
     *
     */
    public function gestion_nomenclatures($articles,$lignes_bdd){

        $articles_ids = $lignes_bdd->pluck('article_id')->toArray();

        $modeles_articles = modele('article')->avec_inactifs()->whereIn('id', $articles_ids)->get()->keyBy('id');

        $produits_assembles = $modeles_articles->where('type_article',3)->values();

        $details_stocks_par_article = false;

        // Gestion des produits assemblés
        if($produits_assembles->isNotEmpty()) {

            $this->articles_nomenclature = modele('article')->hydrate(DB::select('
                WITH RECURSIVE cte AS (
                    SELECT *
                    FROM composition_article
                    WHERE article_id IN (' . implode(',',$produits_assembles->pluck('id')->toArray()) . ')
                    AND COALESCE(composition_article.inactif,0) = 0
                    UNION ALL
                    SELECT t.*
                    FROM composition_article t
                    JOIN cte ON t.article_id = cte.article_enfant_id
                    WHERE COALESCE(t.inactif,0) = 0
                )
                SELECT article.*
                FROM cte
                JOIN article ON cte.article_enfant_id = article.id;
            '))->keyBy('id');

            $this->structure_nomenclature = collect(DB::select('
                WITH RECURSIVE cte AS (
                    SELECT *
                    FROM composition_article
                    WHERE COALESCE(composition_article.inactif,0) = 0
                    AND article_id IN (' . implode(',',$produits_assembles->pluck('id')->toArray()) . ')
                    UNION ALL
                    SELECT t.*
                    FROM composition_article t
                    JOIN cte ON t.article_id = cte.article_enfant_id
                    WHERE COALESCE(t.inactif,0) = 0
                )
                SELECT cte.*
                FROM cte
            '))->groupBy('article_id');

            $this->eco_contribution_active = false;
            $this->fournisseur_id = $this->modele->fournisseur_id ?? false;

            if(!empty($this->modele->client_id)){

                $client = modele('client', $this->modele->client_id);

                $this->eco_contribution_active = !empty($client->eco_contribution) && $client->eco_contribution == 1;
            }

            $details_stocks_par_article = service('stocks')->details_stocks_par_article(
                array_unique(array_merge($articles_ids,array_keys($this->articles_nomenclature->toArray()))));

            $this->stocks = $details_stocks_par_article;

            foreach($lignes_bdd->filter(function($article) use ($produits_assembles){
                return in_array($article->article_id,$produits_assembles->pluck('id')->toArray());
            }) as $article){

				$article->tarif_force = $article->tarif;
				$article->prix_achat_force = $article->prix_achat;

                $article->nomenclature = modele('article')->hydrate($this->gestion_recuperation_nomenclature(
                    $produits_assembles->where('id',$article->article_id)->first()
                ));
            }
        }

        if($details_stocks_par_article === false)
            $details_stocks_par_article = service('stocks')->details_stocks_par_article($articles_ids);

        $conditionnements_possibles = [];

        if(fonctionnalite('utiliser_conditionnement')) {
            if ($this->est_une_vente())
                $conditionnements_possibles = modele('conditionnement')
                    ->whereIn('article_id', $articles_ids)->get()
                    ->groupBy('article_id')
                    ->map(function($conditionnements){
                        return $conditionnements->keyBy('id');
                    });
            else
                $conditionnements_possibles = modele('article_fournisseur')
                    ->select('conditionnement.*', 'article_fournisseur.tarif as tarif', 'article_fournisseur.conditionnement_id as conditionnement_id')
                    ->join('conditionnement', 'article_fournisseur.conditionnement_id', '=', 'conditionnement.id')
                    ->whereIn('article_fournisseur.article_id', $articles_ids)
                    ->where('fournisseur_id', $this->modele->fournisseur_id)->get()
                    ->groupBy('article_id')->map(function($conditionnements){
                        return $conditionnements->keyBy('conditionnement_id');
                    });
        }

        $conditionnements_par_article = [];

        foreach($modeles_articles as $modele_article) {

            $conditionnements = [
                0 => array(
                    'affichage' => !empty($modele_article->unite) ? management('article', $modele_article->id, $modele_article)->champ('unite')->affiche() : 'Unité'
                ),
            ];

            if (!empty($conditionnements_possibles[$modele_article->id])) {

                foreach($conditionnements_possibles[$modele_article->id] as $id => $conditionnement) {

                    $conditionnement['affichage'] = management('conditionnement', $id,$conditionnement)->affiche($modele_article);

                    $conditionnements[$id] = $conditionnement;
                }

            }

            $conditionnements_par_article[$modele_article->id] = $conditionnements;
        }

        $this->chargement_enfants_nomenclature($articles,$lignes_bdd,$modeles_articles,$details_stocks_par_article,$conditionnements_par_article);
    }

    /**
     * @param $articles
     * @param $lignes_bdd
     * @param $modeles_articles
     * @param $details_stocks_par_article
     * @param $conditionnements_par_article
     * @return void
     *
     *
     * Charge de maniére récursive les données des nomenclatures
     *
     */
    public function chargement_enfants_nomenclature($articles,$lignes_bdd,$modeles_articles,$details_stocks_par_article,$conditionnements_par_article,$enfants_par_parent = false){

        if($enfants_par_parent === false)
            $enfants_par_parent = $lignes_bdd->groupBy('nomenclature_ligne_parent');

        foreach($articles as $article){

			if(empty($modeles_articles[$article->article_id]))
				continue;

            $article->modele = $modeles_articles[$article->article_id];
            $article->code_article = $article->modele->code_article;
            $article->type_article = $article->modele->type_article;
            $article->stock = $details_stocks_par_article[$article->article_id]['stock_disponible'] ?? [];
            $article->conditionnement_possible = $conditionnements_par_article[$article->article_id] ?? [];

            if($article->type_article == 1) {

                $enfants_nomenclatures = isset($enfants_par_parent[$article->id])
                    ? $enfants_par_parent[$article->id]->values()
                    : collect();

				foreach($enfants_nomenclatures as $enfant) {
					if($enfant->quantite != 0 && $article->quantite != 0){
						$enfant->quantite_avant = $enfant->quantite;
						$enfant->quantite = $enfant->quantite / ($article->quantite_avant ?? $article->quantite);
					}
				}

                $article->afficher_nomenclature = false;

                if ($enfants_nomenclatures->isNotEmpty()) {

                    $this->chargement_enfants_nomenclature($enfants_nomenclatures, $lignes_bdd, $modeles_articles, $details_stocks_par_article, $conditionnements_par_article, $enfants_par_parent);
                    $article->nomenclature = $enfants_nomenclatures;
                } else
                    $article->nomenclature = [];

				foreach($enfants_nomenclatures as $enfant) {
					unset($enfant->quantite_avant);
				}
            }
			else if($article->type_article != 3)
				$article->nomenclature = null;
        }
    }

	/**
	 *
	 * Retourne les lignes divers du document
	 *
	 * @return array si pas de modèle lié au document
	 * @return une collection eloquent dans le cas contraire
	 *
	 */
	public function lignes_divers_document() {

		if(isset($this->modele->id)) {

			$lignes_divers = Ligne_divers_document::where('type_element', $this->_type_element)->where('document_id', $this->modele->id)->orderBy('ligne')->orderBy('position')->get();

			return $lignes_divers;
		}

		return array();
	}

	/**
	*
	* @cf cf description sur Element_management
	*
	* On utilise cette méthode en surcharge car les urls pour les documents de gestion commerciale sont différentes
	*
	*/
	public function lien_vers_element($id_element = false) {

		if($id_element === false)
			$id_element = $this->modele->id;

		return route('document.afficher', [$this->_type_element, $id_element]);
	}

	/**
	 *
	 * Retourne les paiements du document
	 *
	 * @return une collection
	 *
	 */
	public function paiements() {

		if(!isset($this->modele->id))
			return collect(array());

		$paiements = modele('paiement');

		$paiements = $paiements->where('type_element', $this->_type_element)->where('id_document', $this->modele->id);

		// on utilise ça au lieu de ->get() car pour une raison X ou Y ça prend 70x moins de temps !
		$paiements = select($paiements);

		foreach($paiements as $paiement) {

			$management = management('paiement', $paiement);

			$paiement->compte_bancaire_id_txt = $management->champ('compte_bancaire_id')->affiche();
			$paiement->mode_paiement_id_txt = $management->champ('mode_paiement_id')->affiche();
			$paiement->date_txt = $management->champ('date')->affiche();
		}

		return collect($paiements);
	}

    /**
	 *
	 * Retourne les echeances du document
	 *
	 * @return une collection
	 *
	 */
	public function echeances() {

        if(!isset($this->modele->id))
            return collect(array());

        if(!in_array($this->_type_element, ['facture_vente', 'devis_vente', 'commande_vente']))
            return collect(array());

        $echeances = modele('echeance')->where('type_element', $this->_type_element)->where('element_id', $this->modele->id)->get();

        return $echeances;

	}

    /**
     *
     * Retourne les paiements non rattache à un document
     *
     * @return une collection
     *
     */
    public function paiements_non_rattache() {

        if(!isset($this->modele->id))
            return collect(array());

		if($this->est_une_vente()) {

			$paiements = modele('paiement')
				->zero_ou_null('type_element')
				->zero_ou_null('id_document')
				->zero_ou_null('neutralise')
				->where('client_id', $this->modele->client_id)
				// ->zero_ou_null('rapproche')
				->get();
		}
		else {

			$paiements = modele('paiement')
				->zero_ou_null('type_element')
				->zero_ou_null('id_document')
				->zero_ou_null('neutralise')
				->where('fournisseur_id', $this->modele->fournisseur_id)
				// ->zero_ou_null('rapproche')
				->get();
		}

        if($paiements === null)
            return collect(array());

        return collect($paiements);
    }

	/**
	 *
	 * On remplit des données par défaut pour la transformation
	 *
	 * Cette méthode est surchargée dans les héritiers de cette classe (Facture_vente_management, etc)
	 *
	 */
	public function donnees_avant_transformation($management_element_origine) {

	}

    /**
     *
     * On modifie certaines données après la transformation
     *
     */
    public function donnees_apres_transformation($management_element_origine) {

        $this->modele->date = date('Y-m-d');
    }

	/**
	 *
	 * Utilisé pour ajouter des colonnes spécifiques pour les projets clients
	 *
	 */
	protected function enregistre_articles_colonnes_specifiques(&$informations_ligne, $article) {

		$articles_sur_document = table_libre('article_sur_document')->champs_libres()->get();

		foreach($articles_sur_document as $champ_libre) {

			$nom_sql = $champ_libre->nom_sql;

			if($nom_sql == 'modifie_le' || $nom_sql == 'cree_le' || $nom_sql == 'cree_par' || $nom_sql == 'modifie_par')
				continue;

			if(isset($article[$nom_sql])) {

				if($article[$nom_sql] == 'undefined' || $article[$nom_sql] == 'null') {

					if(in_array($champ_libre->type, array(2,3)))
						$article[$nom_sql] = 0;
					else
						$article[$nom_sql] = null;
				}

				$informations_ligne[$nom_sql] = $article[$nom_sql];
			}
		}
	}

	/**
	 *
	 * Enregistre les articles liés au document
	 *
	 * @reutrn void
	 *
	 */
	protected function enregistre_articles($articles, $test_enregistre = false) {

		if(!$this->eviter_verification_articles){
			// si le document est validé et qu'on ne peut pas le modifier, on ne doit pas pouvoir toucher aux articles
			if(in_array($this->_type_element, array('acompte_vente', 'facture_vente', 'avoir_vente'))) {

				if($this->existe() && $this->modele->valide == 1)
					return;
			}

			// si le document est validé et qu'on ne peut pas le modifier, on ne doit pas pouvoir toucher aux articles
			if(in_array($this->_type_element, array('facture_achat', 'acompte_achat', 'avoir_achat'))) {

				if($this->existe() && $this->modele->valide == 1 && $this->modele->comptabilise == 1)
					return;
			}
		}

		// on active le buffer sql
		buffer_sql()->active();

		// on supprime les achats
		modele('achat_sur_document')->where('type_element', $this->_type_element)->where('document_id', $this->modele->id)->delete();

        // Liste des ids des lignes qui l'on va actualiser au cours de la boucle
		if($this->existe()) {

			$ids_lignes_non_supprimes = $this->modele_lignes()->where('document_id', $this->modele->id)->get()->pluck('id', 'id')->toArray();
		}
		else {

			$ids_lignes_non_supprimes = array();
		}

		$client = modele('client', $this->modele->client_id);

		if($this->est_un_achat())
			$fournisseur = modele('fournisseur', $this->modele->fournisseur_id);

		// on ajoute les articles
		foreach($articles as $id => $article) {

			if(empty($article['article_id']))
				continue;

			// au cas ou l'utilisateur ne remplisse pas certains champs...
			if(empty($article['quantite']))
				$article['quantite'] = 0;

			// au cas ou l'utilisateur ne remplisse pas certains champs...
			if(empty($article['designation']))
				$article['designation'] = '';

			// au cas ou l'utilisateur ne remplisse pas certains champs...
			if(empty($article['tarif']))
				$article['tarif'] = 0;

			// l'article
			$ligne = false;

            if(isset($article['id']) && !empty($article['id'])) {

                $ligne = management($this->_type_element.'_lignes', $article['id']);

                unset($ids_lignes_non_supprimes[$article['id']]);
            }


            if(!$ligne)
                $ligne = management($this->_type_element.'_lignes');

            $ligne->management_parent = $this;

			$informations_ligne = array(

				'remise_globale_ligne' => 1,
				'document_id' => $this->modele->id,
				'ligne' => $id,
				'article_id' => $article['article_id'],
				'designation' => $article['designation'],
				'quantite' => str_replace(',', '.', $article['quantite']),
				'tarif' => str_replace(',', '.', $article['tarif']),
				'tarif_saisi' => str_replace(',', '.', $article['tarif']),

				// il y a quelques valeurs qu'on va initialiser quoi qu'il en soit
				// notamment pour gérer les suppressions
				'calculateur' => null,
				'coefficient' => null,
			);

			if($this->est_une_vente())
				$informations_ligne['client_id_ligne'] = $this->modele->client_id;
			else
				$informations_ligne['fournisseur_id_ligne'] = $this->modele->fournisseur_id;

            if(isset($article['tarif_saisi']))
				$informations_ligne['tarif_saisi'] = str_replace(',', '.', $article['tarif_saisi']);

            if(fonctionnalite('calculateur_sur_document') && isset($article['calculateur']) && !in_array($article['calculateur'], ['null', 'undefined'], true))
                $informations_ligne['calculateur'] = $article['calculateur'];

            if(fonctionnalite('entrepot_sur_ligne') && isset($article['entrepot_id']))
                $informations_ligne['entrepot_id'] = $article['entrepot_id'];

            if(fonctionnalite('utiliser_les_coefficients')){
                if(isset($article['coefficient']))
                    $informations_ligne['coefficient'] = $article['coefficient'];
                if(isset($article['coefficient_article']))
                    $informations_ligne['coefficient_article'] = $article['coefficient_article'];
                if(isset($article['coefficient_regroupement']))
                    $informations_ligne['coefficient_regroupement'] = $article['coefficient_regroupement'];
                if(isset($article['coefficient_devis']))
                    $informations_ligne['coefficient_devis'] = $article['coefficient_devis'];
            }

            // Gestion de l'éco contribution
            $informations_ligne['categorie_eco_contribution_id'] = $article['categorie_eco_contribution_id'] ?? null;

            $informations_ligne['application_eco_contribution'] = $article['application_eco_contribution'] ?? null;

            $informations_ligne['quantite_unite_eco_contribution'] = $article['quantite_unite_eco_contribution'] ?? null;

            $informations_ligne['tarif_eco_contribution'] = $article['tarif_eco_contribution'] ?? null;

            if(fonctionnalite('regroupement_articles_documents') && isset($article['couleur_regroupement']))
                $informations_ligne['couleur_regroupement'] = $article['couleur_regroupement'];

            if(fonctionnalite('regroupement_articles_documents') && isset($article['regroupement_id']))
                $informations_ligne['regroupement_id'] = $article['regroupement_id'];

            $champs_supplementaires_a_enregistrer = $this->options_lignes_divers_champs_supplementaires_enregistrement();

            foreach($champs_supplementaires_a_enregistrer as $nom_champ => $actif) {
                if (isset($article[$nom_champ]))
                    $informations_ligne[$nom_champ] = $article[$nom_champ];
            }

			$this->enregistre_articles_colonnes_specifiques($informations_ligne, $article);

			if($this->_type_element == 'facture_vente' && in_array($this->modele->type_facture, array(1,2))) {

				if(isset($article['avancement_precedent']))
					$informations_ligne['avancement_precedent'] = $article['avancement_precedent'];
				else
					$informations_ligne['avancement_precedent'] = 0;

				if(isset($article['avancement_actuel']))
					$informations_ligne['avancement_actuel'] = $article['avancement_actuel'];
				else
					$informations_ligne['avancement_actuel'] = 0;

				if($this->modele->type_facture == 2)
					$informations_ligne['avancement_actuel'] = 100;

				if(in_array($informations_ligne['avancement_actuel'], array('undefined', 'null')))
					$informations_ligne['avancement_actuel'] = 0;

				if(in_array($informations_ligne['avancement_precedent'], array('undefined', 'null')))
					$informations_ligne['avancement_precedent'] = 0;

				if(!empty($informations_ligne['avancement_actuel'])) {

					if(empty($informations_ligne['avancement_precedent']) || $informations_ligne['avancement_precedent'] == 'undefined')
						$informations_ligne['avancement_precedent'] = 0;

					$informations_ligne['avancement_pourcentage'] = ($informations_ligne['avancement_actuel'] - $informations_ligne['avancement_precedent']) / 100;
				}
			}

			if($this->_type_element == 'bl_vente') {

				if(isset($article['preparation_partielle'])) {

					$informations_ligne['preparation_partielle'] = $article['preparation_partielle'];
				}
			}


			if($this->_type_element == 'commande_achat') {

				if(isset($article['commande_client_id_origine'])) {

					$informations_ligne['commande_client_id_origine'] = $article['commande_client_id_origine'];
				}

				if(isset($article['type_element_origine'])) {

					$informations_ligne['type_element_origine'] = $article['type_element_origine'];
				}
			}

			if($client->forcer_tva_0 != 1 && isset($article['tva'])) {

			    $informations_ligne['tva'] = $article['tva'];
			} else {

				$informations_ligne['tva'] = 0;
			}

			// pour les achats on est forcément en ht
			if($this->est_un_achat()) {

				$article['type_tarif'] = 'ht';
			}

			if(isset($article['type_tarif']) && strtolower($article['type_tarif']) == 'ttc' && !empty($article['type_tarif'])) {

				$informations_ligne['tarif'] = $article['tarif'] / ((100 + $informations_ligne['tva']) / 100) ;
			}

			$modele_article = modele('article', $article['article_id']);

			$informations_a_tester = array(

				'nomenclature' => null,
				'tarif_force' => null,
				'prix_achat_force' => null,
				'declinaison_id' => null,
				'numeros_de_lot' => null,
				'numero_de_serie' => null,
				'code_article' => $modele_article->code_article,
				'afficher_photo' => 0,
				'prix_achat' => 0,
				'remise' => 0,
				'unite' => null,
				'conditionnement' => null,
                'disponibilite' => null,
				'couleur_regroupement' => null,
				'description' => null,
				'type_element_source' => null,
				'id_element_source' => null,
				'id_ligne_source' => null,
				'feuille_de_temps_ids' => null,
				'utilisateur_id' => null,
				'categorie_comptable_article_id' => null,
			);


            if(fonctionnalite('gescom_masquer_lignes_du_document') && isset($article['masquer_ligne']))
                $informations_ligne['masquer_ligne'] = intval($article['masquer_ligne']);

			if(!in_array($this->_type_element, array('bl_vente', 'commande_vente')) || !fonctionnalite('entrepot_sur_ligne'))
                $informations_ligne['entrepot_id'] = null;

			foreach($informations_a_tester as $nom_champ => $valeur_defaut) {

				$informations_ligne[$nom_champ] = $valeur_defaut;

				if(!isset($article[$nom_champ]))
					continue;

				if(empty($article[$nom_champ]))
					continue;

				if($article[$nom_champ] == 'undefined')
					continue;

				if($article[$nom_champ] == 'null')
					continue;

                $informations_ligne[$nom_champ] = $article[$nom_champ];
			}

			// on regarde pour forcer la TVA en fonction de la catégorie comptable
            $this->gere_taux_tva_categorie_comptable($this->est_un_achat() ?
                $fournisseur : $client, $informations_ligne, $modele_article);

			if(isset($article['type_tarif']) && strtolower($article['type_tarif']) == 'ttc' && !empty($article['type_tarif'])) {

				$informations_ligne['tarif']= $article['tarif'] / ((100 + $informations_ligne['tva']) / 100) ;
			}

			if(isset($informations_ligne['cle_externe']))
				unset($informations_ligne['cle_externe']);

			// on va regarder si l'enregistrement est nécessaire (pour gagner en perf)
			log_eden("On va enregistrer la ligne dans le document", 2);
			log_eden("On enregistre la ligne dans le document : ".$informations_ligne['designation']);
			if($ligne->existe()) {
				log_eden("Elle existe", 2);

				foreach($informations_ligne as $champ => $valeur) {

					if($ligne->modele->$champ != $valeur)
						log_eden("On compare la valeur pour le champ $champ, et c'est bien différent : $valeur != ".$ligne->modele->$champ, 2);
					else {

						unset($informations_ligne[$champ]);
					}
				}
			}

            if(!empty($informations_ligne['nomenclature']) && !is_array($informations_ligne['nomenclature']) && !is_object($informations_ligne['nomenclature'])) {
                $informations_ligne['nomenclature'] = json_decode($informations_ligne['nomenclature']);
			}
			
			$ligne->fonction_a_eviter = $this->fonction_a_eviter;
            $ligne->fonction_a_eviter[] = 'maj_index_recherche';
            $ligne->fonction_a_eviter[] = 'notifications';

			/**
			 *
			 * @todo il faudrait vérifier si l'enregistrement a bien fonctionné ?
			 *
			 */
			// log_eden("On va enregistrer les informations suivantes");
			// log_eden($informations_ligne);
			if(!empty($informations_ligne)) {
                if($test_enregistre) {
                    $retour_test = $ligne->test_enregistre($informations_ligne);

                    if($retour_test != 'test_ok')
                        return $retour_test;

                    continue;
                }

                $ligne->enregistre($informations_ligne);
            }

			// les achats
			if(!isset($article['achats']) || empty($article['achats']))
				continue;

			if(is_array($article['achats'])) {

				// quand on duplique un document par exemple, les achats sont renseignés mais en tant qu'array directement
				$achats = $article['achats'];
			}
			else {

				// quand on enregistre un document via formulaire, les achats arrivent sous forme json encodés
				$achats = json_decode($article['achats']);
			}

			if(empty($achats))
				continue;

			foreach($achats as $achat) {

				$achat_management = management('achat_sur_document');

				// c'est le cas quand on duplique un document
				if(is_array($achat))
					$achat = (object) $achat;

				// on vérifie que l'achat à une designation
				// L'achat ne peut avoir de désignation, si l'utilisateur a supprimé la ligne achat
				if($achat->designation != "") {

					if(!isset($achat->tarif))
						$achat->tarif = 0;

					if(!isset($achat->quantite))
						$achat->quantite = 0;

					if(!isset($achat->remise))
						$achat->remise = 0;

					if(!isset($achat->tva))
						$achat->tva = 0;

					if(!isset($achat->article_id))
						$achat->article_id = 0;

					if(!isset($achat->fournisseur_id))
						$achat->fournisseur_id = 0;

					$info = array(
						'fournisseur_id' => $achat->fournisseur_id,
						'article_id' => $achat->article_id,
						'designation' => $achat->designation,
						'quantite' => $achat->quantite,
						'tarif' => $achat->tarif,
						'remise' => $achat->remise,
						'tva' => $achat->tva,
						'type_element' => $this->_type_element,
						'document_id' => $this->modele->id,
						'projet_id' => $this->modele->projet_id,
						'ligne' => $id,
					);

					$achat_management->enregistre($info);

				}
			}
		}

		// On a enregistré tous les articles, on remonte les id lignes des articles dans les nomenclature chez les parents
		// $this->enregistre_id_ligne_des_articles_dans_nomenclatures_dans_nomenclatures_parents();

		// On supprime les lignes qui n'apparaissent plus
		$informations_ligne_origine = array();

		foreach($ids_lignes_non_supprimes as $id){

			// on vérifie que ce n'est pas une ligne de nomenclature
			if(!empty($this->management_ligne($id)->modele->nomenclature_ligne_parent))
				continue;

			// avant de supprimer la ligne, il va falloir stocker les infos de la ligne d'origine pour la mettre à jour
			$management = management($this->_type_element.'_lignes', $id);

            $management->management_parent = $this;

			$type_element_source = $management->modele->type_element_source;
			$id_element_source = $management->modele->id_element_source;

            if($management->existe()) {

                if($test_enregistre) {
                    $retour_test = $management->test_supprime();

                    if($retour_test != 'test_ok')
                        return $retour_test;

                    continue;
                }

                $management->supprime();
            }
			// on doit mettre à jour la ligne d'origine
			if(!empty($type_element_source) && !empty($id_element_source)) {

				if(!isset($informations_ligne_origine[$type_element_source]))
					$informations_ligne_origine[$type_element_source] = array();

				if(!in_array($id_element_source, $informations_ligne_origine[$type_element_source]))
					$informations_ligne_origine[$type_element_source][] = $id_element_source;
			}
		}

        if($test_enregistre)
            return 'test_ok';

		foreach($informations_ligne_origine as $type_element => $liste_id_element) {

			foreach($liste_id_element as  $id_element) {

				$management = management($type_element, $id_element);

				$articles = $management->articles();

				foreach($articles as $article) {

					$reliquat = $management->calcule_reliquat_pour_ligne($article, $article['id']);

					if($reliquat == $article['quantite']) {

						// non traité
						$transforme = 0;
						$transforme_reliquat = $article['quantite'];
					}
					elseif($reliquat < $article['quantite']) {

						// partiellement traité
						$transforme = 1;
						$transforme_reliquat = $reliquat;
					}
					else {

						// traité
						$transforme = 2;
						$transforme_reliquat = 0;
					}

					// on enregistre le changement sur l'élément source
					if($transforme != $article['transforme'] || $transforme_reliquat != $article['transforme_reliquat']) {


						$modifications_ligne = array(

							'transforme' => $transforme,
							'transforme_reliquat' => $transforme_reliquat,
						);

						$management_lignes = management($type_element.'_lignes', $article['id']);

						$retour = $management_lignes->enregistre($modifications_ligne);
					}
				}
			}
		}

		buffer_sql()->execute();
        buffer_sql()->desactive();
	}

	/**
	 *
	 * En fonction de la catégorie comptable du client, on affecte le bon taux de tva à l'article
	 *
	 */
	protected function gere_taux_tva_categorie_comptable($client_ou_fournisseur, &$informations_ligne, $modele_article) {

		$categorie_comptable = $this->modele->categorie_comptable_id ?? $client_ou_fournisseur->categorie_comptable_id;

		if(empty($categorie_comptable))
			return;

        $type = $this->est_un_achat() ? 'achat' : 'vente';

        $service_document = service('document');

        if(!empty($informations_ligne['categorie_comptable_article_id'])){

            $parametrage = modele('categorie_comptable_article')
                ->where('id',$informations_ligne['categorie_comptable_article_id'])
                ->first();

            $tva = $service_document->recupere_taux_article_categorie_comptable($parametrage,$type);

            if(empty($tva))
                return;
        }
        else {
            $taux_de_tva = $service_document
                ->recupere_taux_tva_pour_article_et_categorie_comptable($modele_article, $categorie_comptable, $type);

            if ($taux_de_tva === false)
                return;

            $tva = $taux_de_tva['taux'];
        }

		$informations_ligne['tva'] = $tva;
	}

	/**
	 *
	 * Enregistre les lignes divers liées au document
	 *
	 * @reutrn void
	 *
	 */
	protected function enregistre_lignes_divers($lignes_divers, $articles = []) {

        // Liste des ids des lignes qui l'on va actualiser au cours de la boucle
        $ids_lignes_non_supprimes = Ligne_divers_document::where('type_element', $this->_type_element)->where('document_id', $this->modele->id)->get()->pluck('document_id','id');

        $ligne_diverses_regroupement = false;

        // on ajoute les articles
		foreach($lignes_divers as &$ligne_divers) {

            $id_temporaire = null;

            if($ligne_divers['type'] == 'commentaire' && fonctionnalite('commentaires_wysiwyg_documents') === true && !empty($ligne_divers['commentaire_wysiwyg']))
                $ligne_divers['contenu'] = $ligne_divers['commentaire_wysiwyg'];

            if($ligne_divers['type'] == 'note_interne' && fonctionnalite('notes_internes_wysiwyg_documents') === true && !empty($ligne_divers['note_interne_wysiwyg']))
                $ligne_divers['contenu'] = $ligne_divers['note_interne_wysiwyg'];

            if($ligne_divers['type'] == 'commentaire' && empty($ligne_divers['contenu']))
				continue;

			if($ligne_divers['type'] == 'note_interne' && empty($ligne_divers['contenu']))
				continue;

			if($ligne_divers['type'] == 'remise' && empty($ligne_divers['remise']) && empty($ligne_divers['type_remise']))
                continue;

            if($ligne_divers['type'] == 'regroupement' && isset($ligne_divers['id_temporaire']) && ($ligne_divers['id_temporaire'] === true || $ligne_divers['id_temporaire'] === "true")){

                $id_temporaire = $ligne_divers['id'];
                unset($ligne_divers['id']);

            }

			unset($ligne_divers['id_temporaire']);

			// La ligne divers
            if(isset($ligne_divers['id'])) {
                $ligne = Ligne_divers_document::where('id', $ligne_divers['id'])->first();

                unset($ids_lignes_non_supprimes[$ligne_divers['id']]);
            }
            else {

                $ligne = new Ligne_divers_document;
				$ligne->type = $ligne_divers['type'];
            }

			$ligne->document_id = $this->modele->id;
			$ligne->type_element = $this->_type_element;

			if($ligne->type == "commentaire"){

                if(isset($ligne_divers['contenu']))
                    $ligne->contenu = $ligne_divers['contenu'];
                else
                    $ligne->contenu = '';
            }

            if($ligne->type == "note_interne"){

                if(isset($ligne_divers['contenu']))
                    $ligne->contenu = $ligne_divers['contenu'];
                else
                    $ligne->contenu = '';
            }

            if($ligne->type == 'regroupement')
                $ligne->contenu = $ligne_divers['contenu'] ?? 0;

			if(isset($ligne_divers['format']))
                $ligne->format = $ligne_divers['format'];

            if(isset($ligne_divers['nom']))
				$ligne->nom = $ligne_divers['nom'];
			else
				$ligne->nom = '';

            if(isset($ligne_divers['calculateur']) && !in_array($ligne_divers['calculateur'], ['null', 'undefined'], true))
                $ligne->calculateur = $ligne_divers['calculateur'];

            if(isset($ligne_divers['coefficient']))
                $ligne->coefficient = $ligne_divers['coefficient'];

            if($ligne->type == "coefficient") {

                $ligne->type_coefficient = $ligne_divers['type_coefficient'];
                $ligne->quantite = $ligne_divers['quantite'];
				// $ligne->coefficient = json_encode(array('id' => 1, 'nom' => $ligne_divers['nom'], 'type' => 0, 'quantite' => $ligne_divers['quantite']));
			}

			$ligne->ligne = $ligne_divers['ligne'];
			$ligne->position = $ligne_divers['position'];
			$ligne->type = $ligne_divers['type'];

            if(isset($ligne_divers['couleur_regroupement']))
                $ligne->couleur_regroupement = $ligne_divers['couleur_regroupement'];

			$ligne->id_style_ligne_document = 0;

			if(isset($ligne_divers['id_style_ligne_document']))
				$ligne->id_style_ligne_document = $ligne_divers['id_style_ligne_document'];

			// si c'est une option
			if($ligne_divers['type'] == 'option') {

                $ligne->quantite = $ligne_divers['quantite'];
                $ligne->tarif = $ligne_divers['tarif'];
                $ligne->remise = $ligne_divers['remise'];
            }

			// si c'est une remise
            if($ligne_divers['type'] == 'remise') {

                $ligne->remise = $ligne_divers['remise'];
                $ligne->type_remise = $ligne_divers['type_remise'];
            }

            $champs_supplementaires_a_enregistrer = $this->options_lignes_divers_champs_supplementaires_enregistrement();

            foreach($champs_supplementaires_a_enregistrer as $nom_champ => $actif) {
                if (isset($ligne_divers[$nom_champ]))
                    $ligne->$nom_champ = $ligne_divers[$nom_champ];
            }

			$ligne->save();

            if(isset($champs_supplementaires_a_enregistrer['regroupement_id']))
                unset($champs_supplementaires_a_enregistrer['regroupement_id']);

            if($ligne->type == "regroupement" ){

                $ligne_diverses_regroupement = true;

                foreach ($lignes_divers as &$ligne_divers_tmp){

                    if (isset($id_temporaire) && array_key_exists('regroupement_id', $ligne_divers_tmp) && $ligne_divers_tmp['regroupement_id'] == $id_temporaire && (!isset($ligne_divers_tmp['id']) || $ligne_divers_tmp['id'] !== $id_temporaire))
                        $ligne_divers_tmp['regroupement_id'] = $ligne->id;

                }

                foreach ($articles as &$article){

                    if(isset($id_temporaire) && array_key_exists('regroupement_id', $article) && ($article['regroupement_id'] == $id_temporaire || $ligne->id == $article['regroupement_id'])) {

                        if (isset($id_temporaire) && $article['regroupement_id'] == $id_temporaire)
                            $article['regroupement_id'] = $ligne->id;

                        foreach ($champs_supplementaires_a_enregistrer as $nom_champ => $actif) {

                            if (isset($ligne->$nom_champ))
                                $article[$nom_champ] = $ligne->$nom_champ;
                        }
                    }
                }

            }
		}

        if($ligne_diverses_regroupement){

            foreach ($articles as &$article){
                if(empty($article['regroupement_id']) || $article['regroupement_id'] == 'false' ){
                    foreach ($champs_supplementaires_a_enregistrer as $nom_champ => $actif) {
                        if (!isset($article[$nom_champ]))
                            $article[$nom_champ] = 'null';
                    }
                }
            }

        }

        //On supprime les lignes qui n'apparaissent plus
        foreach($ids_lignes_non_supprimes as $id => $document_id){

            $ligne = Ligne_divers_document::where('id', $id)->first();

            $ligne->delete();
        }

        return $articles;

	}

	/**
	 *
	 * Règles de gestion: pas possible de supprimer un document qui est à l'origine d'un autre
	 *
	 */
	public function supprime($modele = false) {

		if($modele === false && !empty($this->modele))
			$modele = $this->modele;

		$documents_posterieurs = $this->documents_posterieurs($this->articles());

        if(!empty($documents_posterieurs))
            return traduction('messages.php.document.suppression_impossible_documents_posterieurs');

		return parent::supprime($modele);
	}

	/**
	 *
	 * On interdit d'annuler la suppression de certains type_element (les factures si elles sont validées par exemple)
	 *
	 */
	public function annule_suppression() {

		if(isset($this->modele->annulee_par_avoir) && !empty($this->modele->annulee_par_avoir))
			return traduction('messages.php.document.retablir_document_annule_par_avoir');

		return parent::annule_suppression();
	}


	/**
	 *
	 * On gère la cloture mensuelle (ne pas pouvoir faire bouger les factures ou avoirs du mois passé après le X du mois)
	 *
	 */
	public function verifie_cloture_comptable($modifications = array()) {

		if(empty(fonctionnalite('cloture_comptable_mensuelle_le')))
			return true;

		if(!empty($modifications['date']))
			$modifications['date'] = formate_date('Y-m-d', $modifications['date']);

		// on récupère la bonne date
		if($this->existe()) {

			$date = $this->modele->date;

			// on doit gérer le cas un peu particulier, ou il y a une date sur un document existant, et on la modifie
			if(isset($modifications['date'])) {

				// elle est inférieure à la date initiale du document
				// cela répond au cas ou par exemple, on prend un document de ce mois ci et on le redate à une date du passé
				if($modifications['date'] < $date) {

					$date = $modifications['date'];
				}
			}

		}
		else
			$date = $modifications['date'];


		// c'est un document du mois passé
		if(formate_date('Y-m', $date) == date('Y-m', strtotime('last month'))) {

			// on regarde la date du jour
			if(date('d') < fonctionnalite('cloture_comptable_mensuelle_le'))
				return true;

			return false;
		}
		// autre cas du passé : on ne peut jamais modifier
		elseif(formate_date('Y-m', $date) < date('Y-m', strtotime('last month'))) {

			return false;
		}
		// ce mois ci ou futur : on peut toujours modifier
		else {

			return true;
		}

	}

    public function liste_colonnes_options(){

        $liste_options = ['zoom','lien'];

        $liste_options = array_merge($liste_options,parent::liste_colonnes_options());

        if(in_array('apercu',$liste_options))
            unset($liste_options[array_search('apercu',$liste_options)]);


        $liste_options[] = 'pdf';

        return $liste_options;
    }

	/**
	 *
	 *
	 * Retourne les actions sur les listes
	 * Peut être surchargé pour ajouter des actions sur mesure en fonction des éléments ou du spécifique
	 *
	 */
	public function actions_a_afficher($id_liste) {

		// on recupère les actions principales
		$actions = parent::actions_a_afficher($id_liste);

		// on supprime la suppression classique
		unset($actions['supprimer']);

		$actions['imprimer_pdf'] = '<span class="dropdown-item" @click="modale_imprimer_pdf = true;"><i class="fa fa-fw fa-check"></i><span v-html="$root.traduction(\'interface.listes.imprimer\')"></span></span>';

		$actions['valider_documents'] = '<span class="dropdown-item" @click="modale_valider_documents = true;"><i class="fa fa-fw fa-check"></i><span v-html="$root.traduction(\'interface.listes.valider\')"></span></span>';

		$actions['fusionner_documents'] = '<span class="dropdown-item" @click="modale_fusionner = true"><i class="fa fa-fw fa-check"></i><span v-html="$root.traduction(\'interface.listes.fusionner\')"></span></span>';

		if(in_array($this->_type_element, Variables::$documents_comptabilisable) && profil($this->_type_element, $this->modele->entite_id ?? null, 'comptabilisation') && 
			fonctionnalite('listes_factures_autres_action_comptabiliser') !== false)
			$actions['comptabiliser_documents'] = '<span class="dropdown-item" @click="modale_comptabiliser_documents = true"><i class="fa fa-fw fa-check"></i><span v-html="$root.traduction(\'interface.listes.comptabiliser\')"></span></span>';

		if(in_array($this->_type_element, array('devis_vente'))) {

			$actions['accepter_documents'] = '<span class="dropdown-item" @click="modale_accepter_documents = true"><i class="fa fa-fw fa-check"></i><span v-html="$root.traduction(\'interface.listes.accepter\')"></span></span>';
			$actions['refuser_documents'] = '<span class="dropdown-item" @click="modale_refuser_documents = true"><i class="fa fa-fw fa-check"></i><span v-html="$root.traduction(\'interface.listes.refuser\')"></span></span>';
		}

		if(in_array($this->_type_element, array('bl_vente','commande_vente','devis_vente', 'bl_achat')))
			$actions['facturer_documents'] = '<span class="dropdown-item" @click="modale_facturer_documents = true"><i class="fa fa-fw fa-check"></i><span v-html="$root.traduction(\'interface.listes.facturer\')"></span></span>';

        if(in_array($this->_type_element, array('devis_vente')))
            $actions['commander_documents'] = '<span class="dropdown-item" @click="modale_commander_documents = true;"><i class="fa fa-fw fa-check"></i><span v-html="$root.traduction(\'interface.listes.commander\')"></span></span>';

		if(in_array($this->_type_element, array('facture_vente', 'avoir_vente')) && modele('entite')->where('facturation_electronique_active', 1)->exists())
			$actions['envoyer_facturation_electronique'] = '<span class="dropdown-item" @click="modale_envoyer_facturation_electronique = true"><i class="fa fa-fw fa-file-export"></i><span v-html="$root.traduction(\'interface.listes.envoyer_facturation_electronique\')"></span></span>';

		$actions['supprimer_documents'] = '<span class="dropdown-item" @click="modale_supprimer_documents = true"><i class="fa fa-fw fa-trash"></i><span v-html="$root.traduction(\'interface.listes.supprimer\')"></span></span>';

		$table_libre = table_libre($this->_type_element);

		return $actions;
	}

    public function actions_listes($liste_libre){

        $actions = parent::actions_listes($liste_libre);

        if (isset($actions['supprimer_documents']) && !profil($this->_type_element, 0, 'suppression_en_masse'))
            unset($actions['supprimer_documents']);

        return $actions;
    }

	/**
	*
	*
	*
	*/
	public function retourne_pour_api($donnees = array()) {

		$donnees = parent::retourne_pour_api($donnees);

		$donnees['modele']['articles'] = $this->articles();

		return $donnees;
	}

	/**
	 *
	 * Retourne un modèle avec des données par défaut
	 *
	 */
	public function modele_par_defaut() {

		$modele = parent::modele_par_defaut();

		$modele->date = date('Y-m-d');

		return $modele;
	}

	/**
	 *
	 * Fusionne les documents (par exemple, permet de passer de 5 factures à une seule)
	 *
	 */
	public function fusionner_documents($formulaire, $donnees_a_ajouter = []) {

		$management = management($formulaire->type_element, $formulaire->ids[0]);
		$nouveau_management  = $management->duplique_avec_modifications(array('date' => date('Y-m-d')));

        if(!is_object($nouveau_management))
            return $nouveau_management;

		$articles_a_fusionner = collect([]);

		$ligne = 0;
		$position = 0;

		foreach($formulaire->ids as $id_document) {

			$management = management($formulaire->type_element, $id_document);

			$articles = $management->lignes_du_document_pour_saisie($formulaire->type_element, false);
            $articles = $management->traitement_articles_duplication($articles, false);

			$max_ligne = $articles->filter(function($article){
                return empty($article->type);
            })->max('ligne') ?? 0;

            $articles->map(function($article) use ($ligne,$position){

                if(!empty($article->type) && $article->ligne == 0)
                    $article->position += $position;

                $article->ligne += $ligne;

                return $article;
            });

			$position = $articles->where('ligne',$max_ligne)->max('position') ?? 0;

            $position++;

			$ligne += $max_ligne;

            $message_type = $management->nom_ligne_total_pour_fusion_de_documents();

            $ligne_divers = new Ligne_divers_document();
            $ligne_divers->type_element = $formulaire->type_element;
            $ligne_divers->contenu = $message_type;
            $ligne_divers->ligne = $ligne;
            $ligne_divers->position = $position;
            $ligne_divers->type = 'sous_total';
            $ligne_divers->nom = $message_type;

            $articles->push($ligne_divers);

            $position++;

            $ligne_divers = new Ligne_divers_document();
            $ligne_divers->type_element = $formulaire->type_element;
            $ligne_divers->contenu = '';
            $ligne_divers->ligne = $ligne;
            $ligne_divers->position = $position;
            $ligne_divers->type = 'saut_de_ligne';
            $ligne_divers->nom = '';

            $articles->push($ligne_divers);

            $articles_a_fusionner = $articles_a_fusionner->merge($articles);
		}

		$donnees_a_enregistrer = [
            'articles' => $articles_a_fusionner->filter(function($article){
                return empty($article->type);
            })->keyBy('ligne')->toArray(),
            'lignes_divers' =>
                array_values($articles_a_fusionner->filter(function($article){
                return !empty($article->type);
            })->map(function($ligne_divers) {
                if(!empty($ligne_divers->choix_code_article))
                    unset($ligne_divers->choix_code_article);

                if(isset($ligne_divers->coefficient))
                    unset($ligne_divers->coefficient);

                return $ligne_divers;
            })->toArray())
        ];

		if(!empty($donnees_a_ajouter))
			$donnees_a_enregistrer = array_merge($donnees_a_enregistrer, $donnees_a_ajouter);

		$nouveau_management->enregistre($donnees_a_enregistrer);

		return $nouveau_management;
	}

	/**
	 *
	 * Retourne la syntaxe pour la ligne de total dans les fusion de documents
	 *
	 */
	protected function nom_ligne_total_pour_fusion_de_documents() {

		return 'Total '.str_replace('_vente', '', $this->_type_element).' #'.$this->modele->reference_document;
	}

	/**
	 *
	 * Retourne la syntaxe pour la ligne de total dans les fusion de documents
	 *
	 */
	protected function nom_ligne_titre_pour_fusion_de_documents($type_element, $modele) {

		return traduction('document.lignes_diverses.titre.titre_separation_automatique') . ' ' . traduction('tables_libres.' . $type_element . '.nom_table').' #'.$modele->reference_document;
	}

	/**
	 *
	 * Retouche le modèle pour la création des documents (pour des cas très spéifiques... par exemple les datetimes)
	 *
	 */
	public function retouche_modele_pour_formulaire_document() {}

	/**
	 *
	 * Retourne les infos de la récurrence pour la saisie des documents
	 *
	 */
	public function retourne_recurrence_pour_saisie_document() {

        if(!$this->management_fiche()->presence_module('recurrence'))
            return false;

        $recurrence = $this->recurrence();

        if($recurrence != null)
            $recurrence->recurrence_activee = 1;

		return $recurrence;
	}

	/**
	 *
	 * Transforme le document en un autre document
	 *
	 * @param string $type_element : le type_element de l'élément à créer
	 * @param string $modifications : un tableau de données s'il est nécessaire de les forcer
	 *
	 * @return array : la 1ere valeur est le résultat (true si ça a fonctionné, le message d'erreur sinon),
	 * la 2eme valeur est le nouvel élément créé
	 *
	 */
	public function transformer_document($type_element, $modifications = array()) {

		$nouveau_document = management($type_element);

        $this->charge_valeurs_champs_multiselection();

		$champs_libres_origine = table_libre($this->_type_element)->champs_libres()->get()->pluck('nom_sql')->toArray();
		$champs_libres_destination = table_libre($type_element)->champs_libres()->get()->pluck('nom_sql')->toArray();

		$donnees = $nouveau_document->retourne_informations_par_defaut();

		foreach($champs_libres_origine as $nom_sql) {

			if(in_array($nom_sql, $champs_libres_destination))
				$donnees[$nom_sql] = $this->modele->$nom_sql;
		}

		// on ajoute les articles

		$donnees['articles'] = $this->recupere_articles_pour_transformation(true, $type_element);

		$donnees = $nouveau_document->retraite_donnees_pour_transformation($donnees, $this,$modifications);

        // quelques modifications à la volée
		foreach($modifications as $champ => $valeur) {

			$donnees[$champ] = $valeur;
		}

		// on ajoute les commentaires / lignes divers
		$lignes_divers = array();

		$lignes_divers_du_document = Ligne_divers_document::where('type_element', $this->_type_element)->where('document_id', $this->modele->id)->get();

		foreach($lignes_divers_du_document as $ligne_divers) {

			$ligne_divers_a_jouter = array();

			$ligne_divers_a_jouter['contenu'] = $ligne_divers->contenu;
			$ligne_divers_a_jouter['nom'] = $ligne_divers->nom;
			$ligne_divers_a_jouter['ligne'] = $ligne_divers->ligne;
			$ligne_divers_a_jouter['position'] = $ligne_divers->position;
			$ligne_divers_a_jouter['type'] = $ligne_divers->type;
			$ligne_divers_a_jouter['id_style_ligne_document'] = $ligne_divers->id_style_ligne_document;
			$ligne_divers_a_jouter['quantite'] = $ligne_divers->quantite;
			$ligne_divers_a_jouter['tarif'] = $ligne_divers->tarif;
			$ligne_divers_a_jouter['remise'] = $ligne_divers->remise;

			$lignes_divers[] = $ligne_divers_a_jouter;
		}

		$donnees['lignes_divers'] = $lignes_divers;

		$resultat = $nouveau_document->enregistre($donnees);

        if($this->_type_element == 'devis_vente' && $type_element == 'commande_vente')
            $this->copie_echeances($nouveau_document);

        $this->dechargement_valeurs_multiselection();

		return array($resultat, $nouveau_document);
	}

	/**
	 *
	 * Retourne le nom du statut pour les documents liés
	 *
	 */
	public function retourne_nom_statut_pour_documents_lies() {

    $affichage = $this->champ('statut')->affiche();

		if(empty($affichage))
			return 'Sans valeur';

		return $affichage;
	}

	/**
	 *
	 * Récupère le tableau des articles pour une transformation
	 *
	 * Cette méthode ajoute automatiquement les infos comme le document d'origine
	 *
	 */

	public function recupere_articles_pour_transformation($uniquement_non_traites = true, $type_element_destination = false) {

		$articles = $this->articles();

		$articles_pour_transformation = array();

		// on retouche les articles pour ajouter les infos du document source
		foreach($articles as $id => $article) {

            // on gère le cas où il y a un conditionnement
			$management_ligne = $this->management_ligne($article['id'],$article);

			// on regarde le reliquat
			if(in_array($type_element_destination, array('avoir_vente','avoir_achat','acompte_vente','acompte_achat'))) {

				$article->quantite = $article['quantite'];
			}
			else {

				$reliquat = $this->calcule_reliquat_pour_ligne($article, $article['id']);


                if($reliquat <= 0 && $uniquement_non_traites === true) {

					$articles->forget($id);
					continue;
				}

                $conditionnement = $management_ligne->conditionnement_de_la_ligne();
                $article->quantite = $reliquat / $conditionnement;
			}

			$article->type_element_source = $this->_type_element;
			$article->id_element_source = $this->modele->id;
			$article->id_ligne_source = $article['id'];

            if(!empty($article->regroupement_id))
                unset($article->regroupement_id);

            if(!empty($article->couleur_regroupement))
                unset($article->couleur_regroupement);

            if(!isset($article->entrepot_id))
                $article->entrepot_id = 0;

			$article->conditionnement = $management_ligne->modele->conditionnement;

            // on gère le cas ou c'est une nomenclature, dans ce cas, on doit ajouter les infos des lignes d'origine
            if(!empty($article->modele) && $article->modele->type_article == 1)
			    $management_ligne->ajoute_info_ligne_source_nomenclature($this->_type_element, $this->modele->id);

            $articles_pour_transformation[$article->ligne] = $article;

			// sinon on risque d'écraser une ligne
			$article->id = null;
		}

        $this->mise_a_jour_eco_contribution_lignes($articles_pour_transformation);

		return collect($articles_pour_transformation);
	}

    /**
	 *
	 * Récupère le tableau des lignes diverses pour une transformation
	 *
	 * Cette méthode ajoute automatiquement les infos comme le document d'origine
	 *
	 */
	public function recupere_lignes_divers_pour_transformation() {

		$lignes_divers = $this->lignes_divers_document();

        $lignes_divers_pour_transformation = array();

		// on retouche les lignes divers pour ajouter les infos du document source
		foreach($lignes_divers as $ligne_divers) {

            if($ligne_divers->type == 'regroupement')
                $ligne_divers->id_temporaire = true;
            else
                unset($ligne_divers->id);

            $lignes_divers_pour_transformation[] = $ligne_divers;
		}

		return collect($lignes_divers_pour_transformation);
	}

	/**
	 *
	 * Calcule le reliquat pour une ligne
	 *
	 */
	public function calcule_reliquat_pour_ligne($article, $id_ligne_source) {

		$nombre_transforme = $this->calcule_nombre_transformations_ligne($id_ligne_source);

		$conditionnement_de_la_ligne = management($this->_type_element.'_lignes', $article['id'], $article)->conditionnement_de_la_ligne();

		return ($article['quantite'] * $conditionnement_de_la_ligne) - $nombre_transforme;
	}

	/**
	 *
	 * Calcule la quantité de transformation d'une ligne
	 *
	 * Par exemple, si une ligne d'un devis a une ligne avec quantité = 10
	 * Et il y a deux commandes avec des lignes qui ont pour source cette ligne,
	 * avec des quantités de 3 et 4, cette méthode doit retourner 7
	 *
	 */
	public function calcule_nombre_transformations_ligne($id_ligne_source) {

		$type_element_source = $this->_type_element;
		$id_element_source = $this->modele->id;

		// on va chercher les lignes qui sont déterminées via celle ci
		if($this->_type_element == 'devis_vente') {

			$documents = array('commande_vente', 'facture_vente');
		}
		elseif($this->_type_element == 'commande_vente') {

			$documents = array('bl_vente','bon_preparation_vente','facture_vente');
		}
		elseif($this->_type_element == 'bon_preparation_vente') {

			$documents = array('bl_vente');
		}
		elseif($this->_type_element == 'bl_vente') {

			$documents = array('facture_vente','bon_retour_vente');
		}
        elseif($this->_type_element == 'commande_achat') {

			$documents = array('bl_achat');
		}
        elseif($this->_type_element == 'bl_achat') {

            $documents = array('facture_achat','bon_retour_achat');
        }

		if(!isset($documents))
			return 0;

		$nombre_transforme = 0;

		foreach($documents as $type_element) {

            if(!isset($this->calcule_transformations_lignes[$type_element])){

                $lignes = modele($type_element . '_lignes')
                    ->select($type_element . '_lignes.*')->where(array(

                    $type_element . '_lignes.type_element_source' => $type_element_source,
                    $type_element . '_lignes.id_element_source' => $id_element_source,
                ));

                if ($type_element == 'facture_vente')
                    $lignes->whereNull('avoir_vente_lignes.id')
                        ->leftJoin('avoir_vente_lignes', function ($join) use($type_element) {
                            $join->where('avoir_vente_lignes.type_element_source','facture_vente')
                                ->on('avoir_vente_lignes.id_ligne_source',$type_element . '_lignes.id');
                        });
				else if ($type_element == 'facture_achat')
                    $lignes->whereNull('avoir_achat_lignes.id')
                        ->leftJoin('avoir_achat_lignes', function ($join) use($type_element) {
                            $join->where('avoir_achat_lignes.type_element_source','facture_achat')
                                ->on('avoir_achat_lignes.id_ligne_source',$type_element . '_lignes.id');
                        });

                $lignes = $lignes->get()->groupBy('id_ligne_source');

                $this->calcule_transformations_lignes[$type_element] = $lignes;
            }

            if(empty($this->calcule_transformations_lignes[$type_element][$id_ligne_source]))
                continue;

            $lignes = $this->calcule_transformations_lignes[$type_element][$id_ligne_source];

			foreach($lignes as $ligne) {

				$nombre = $ligne->quantite;

                $management_ligne = management($type_element.'_lignes', $ligne->id, $ligne);

                // On stocke les managements lignes parents ou les managements entetes
                if(!isset($this->managements_entete[$type_element][$ligne->document_id]))
                    $this->managements_entete[$type_element][$ligne->document_id] = management($type_element,$ligne->document_id);

                $management_ligne->management_parent = $this->managements_entete[$type_element][$ligne->document_id];

                if(!empty($ligne->nomenclature_ligne_parent)){

                    $nomenclature_id = $ligne->nomenclature_ligne_parent;

                    if(!isset($this->managements_parent_ligne[$type_element][$nomenclature_id]))
                        $this->managements_parent_ligne[$type_element][$nomenclature_id] = management($type_element.'_lignes',$nomenclature_id);

                    $management_ligne->management_ligne_parent = $this->managements_parent_ligne[$type_element][$nomenclature_id];
                }

				// on regarde s'il y a un conditionnement
				$conditionnement_de_la_ligne = $management_ligne->conditionnement_de_la_ligne();

				$nombre_transforme += $nombre * $conditionnement_de_la_ligne;
			}
		}

		return $nombre_transforme;
	}


	protected function date_pour_annulation_par_avoir() {

		return date('Y-m-d');
	}

	/**
	 *
	 * Retraite les données avant la transformation d'un document vers un autre
	 *
	 * @param array $donnees le tableau des données qui va être inséré pour le nouvel élément
	 * @param object $management_origine le management de l'élément d'origine de la transformation
	 *
	 * Note : attention, l'élément courant n'existe pas encore, du coup $this->modele est actuellement vide
	 *
	 */
	protected function retraite_donnees_pour_transformation($donnees, $management_origine,$modifications) {

        if(!isset($modifications['date']))
		    $donnees['date'] = $management_origine->date_pour_annulation_par_avoir();

		// toutes les valeurs par défaut

		$champs_a_reinitialiser = array(

			'valide',
			'date_changement_statut',
			'comptabilise',
			'annulee_par_avoir',
			'valide_n1',
			'regle',
			'relance_1',
			'relance_2',
			'relance_3',
			'relance_4',
			'relance_1_ok',
			'relance_2_ok',
			'relance_3_ok',
			'relance_4_ok',
			'commentaires_recouvrement',
			'annule',
			'lien_interface_paiement_payline',
			'lien_interface_paiement_stripe',
			'date_de_reglement_reelle',
			'delai_de_reglement',
			'annulee_v1',
			'document_relu',
			'document_relu_par',
			'document_relu_le',
			'statut',
			'envoye_par_mail',
			'retard',
			'statut_approbation',
			'facture_id_source',
			'acompte_id_source',
			'avoir_partiel',
            'avoir_total',
			'statut_facturation_electronique',
			'facturation_electronique_flow_id',
		);

        if($management_origine->_type_element != $this->_type_element)
            $champs_a_reinitialiser[] = 'type_modele_document';

		foreach($champs_a_reinitialiser as $champ) {

			if(isset($donnees[$champ]))
				unset($donnees[$champ]);
		}

		$donnees['reference_document'] = '';

		if(fonctionnalite('gescom_calcul_date_de_reglement_automatique') 
			&& !empty($donnees['date']) 
			&& !empty($donnees['modalite_paiement_id'])){

			$modalite_paiement_management = management('modalite_paiement', $donnees['modalite_paiement_id']);

			$date_de_reglement = $modalite_paiement_management->calcule_date_reglement($donnees['date']);

			if(!empty($date_de_reglement))
				$donnees['date_de_reglement'] = $date_de_reglement;
		}

		return $donnees;
	}

	/**
	 * On supprime chaque mouvement de crédit lié au document que l'on vient d'annuler
	 */
	protected function recredite_credits_utilises() {

		$credits = modele('credit')
			->where('source_type_element', $this->_type_element)
			->where('source_element_id', $this->modele->id)
			->get();

		if($credits->count() > 0){
			foreach($credits as $credit){
				$credit_management = management('credit', $credit->id)->supprime();
			}
		}
	}

	/**
	 *
	 * On ajoute aux colonnes les champs de la table article_sur_document
	 *
	 */
	public function champs_sur_ligne_document($colonnes) {

        foreach($colonnes as $nom_colonne => $colonne){

            if(isset($colonne['sous_colonnes'])){

                foreach($colonne['sous_colonnes'] as $nom_sous_colonne => $sous_colonne){

                    $colonnes[$nom_sous_colonne] = true;
                }
            }

            $colonnes[$nom_colonne] = true;

        }

        if(isset($colonnes['avancement']) && $colonnes['avancement'] === true) {

            $colonnes['avancement_precedent'] = true;
            $colonnes['avancement_actuel'] = true;

        }

        // quelques colonnes en dur
        $colonnes['tarif'] = true;
        $colonnes['remise'] = true;
        $colonnes['tva'] = true;
        $colonnes['prix_achat'] = true;
        $colonnes['description'] = true;
        $colonnes['entrepot_id'] = fonctionnalite('entrepot_sur_ligne');
        $colonnes['calculateur'] = fonctionnalite('calculateur_sur_document');
        $colonnes['couleur_regroupement'] = fonctionnalite('regroupement_articles_documents');
        $colonnes['regroupement_id'] = fonctionnalite('regroupement_articles_documents');
        $colonnes['disponibilite'] = (isset(fonctionnalite('ajout_colonne_disponibilite_sur_documents')[$this->_type_element]) ? fonctionnalite('ajout_colonne_disponibilite_sur_documents')[$this->_type_element] : false);

		if($this->est_une_vente() && fonctionnalite('utiliser_les_tarifs_forces_pour_les_nomenclatures')) {
            $colonnes['tarif_force'] = true;
            $colonnes['prix_achat_force'] = true;
        }

        $colonnes['coefficient'] = true;
        $colonnes['tarif_net'] = true;
        $colonnes['total'] = true;

        if(!empty($colonnes['unite']))
            $colonnes['conditionnement'] = true;

		// à gérer plus tard
		// pour le moment on a du spé chez les clients (amc) par exemple
		// mais ça ne concerne que les ventes, pas les achats
		if($this->est_un_achat())
			return $colonnes;

		$articles_sur_document = table_libre('article_sur_document')->champs_libres()->get()->pluck('nom_sql')->toArray();

		foreach($articles_sur_document as $nom_sql) {

			if($nom_sql == 'modifie_le' || $nom_sql == 'cree_le' || $nom_sql == 'cree_par' || $nom_sql == 'modifie_par')
				continue;

			$colonnes[$nom_sql] = true;
		}

        return $colonnes;
	}

	/**
	 *
	 * Défini quelles sont les colonnes à afficher pour la saisie des documents
	 *
	 * Peut être surchargé pour ajouter des colonnes sur mesure
	 *
	 */
	public function colonnes_articles() {

        if($this->existe() === false)
            $articles_modifiables = true;
        else
            $articles_modifiables = $this->articles_modifiables();

		if(fonctionnalite('type_calcul_du_pourcentage_marge') === 'prix_d_achat') {
			$marge_pourcentage_calculable = true;
			$ordre_marge_brute_pourcentage = 5;
		}
		else {
			$marge_pourcentage_calculable = false;
			$ordre_marge_brute_pourcentage = 13;
		}

		$colonnes_articles = array(
            'utilisateur_id' => array (
                'nom' => 'Utilisateur',
                'ordre' => -1,
				'type_colonne' => 'selection_element',
            ),
            'code_article' => array (
              'nom' => 'Code Article',
              'ordre' => 0,
			  'type_colonne' => 'select',
            ),
            'designation' => array(
                'nom' => 'Désignation',
                'ordre' => 1,
				'type_colonne' => 'texte',
            ),
            'quantite' => array(
                'nom' => 'Qté',
                'ordre' => 2,
				'type_colonne' => 'montant',
            ),
            'unite' => array(
                'nom' => 'Unité',
                'ordre' => 3,
				'type_colonne' => 'select',
            ),
			'prix_achat' => array(
                'nom' => 'PA',
                'ordre' => 4,
				'type_colonne' => 'montant',
            ),
            'tarif' => array(
                'nom' => 'PU',
                'ordre' => 6,
				'type_colonne' => 'montant',
            ),
            'remise' => array(
                'nom' => 'Rem (%)',
                'ordre' => 7,
				'type_colonne' => 'montant',
            ),
            'tarif_net' => array(
                'nom' => 'Tarif NET',
                'ordre' => 8,
				'type_colonne' => 'montant',
            ),
			'marge_brute_montant' => array(
                'nom' => 'Marge',
                'ordre' => 10,
				'type_colonne' => 'montant',
            ),
			'marge_brute_pourcentage' => array(
                'nom' => 'Marge (%)',
                'ordre' => $ordre_marge_brute_pourcentage,
				'marge_pourcentage_calculable' => $marge_pourcentage_calculable,
				'type_colonne' => 'montant',
            ),
			'marge' => array(
                'nom' => 'Marge',
                'ordre' => 12,
				'type_colonne' => 'montant',
            ),
			'marge_pourcentage' => array(
                'nom' => 'Marge (%)',
                'ordre' => 13,
				'type_colonne' => 'montant',
            ),
            'total' => array(
                'nom' => 'HT',
                'ordre' => 14,
				'type_colonne' => 'montant',
            ),
            'eco_contribution' => array(
                'nom' => 'Eco-contribution',
                'ordre' => 15,
                'condition_v_if' => 'eco_contribution_active',
				'type_colonne' => 'montant',
            ),
			'tva' => array(
                'nom' => 'TVA',
                'ordre' => 16,
				'type_colonne' => 'select',
            ),
            'categorie_comptable_article_id' => array(
                'nom' => 'Catégorie comptable',
                'ordre' => 17,
				'type_colonne' => 'selection_element',
            ),
            'total_ttc' => array(
                'nom' => 'TTC',
                'ordre' => 18,
				'type_colonne' => 'montant',
            ),
        );

		$fonctionnalite_colonnes = $this->est_une_vente() ? fonctionnalite('documents_colonnes_a_afficher_vente') : fonctionnalite('documents_colonnes_a_afficher_achat');

        foreach($fonctionnalite_colonnes as $colonne_par_defaut => $valeur){

            if($valeur !== true)
                unset($colonnes_articles[$colonne_par_defaut]);
        }

        if($articles_modifiables === true && fonctionnalite('choix_code_article_sur_saisie_document')){

			$colonnes_articles['code_article'] = array(
				'nom' => 'Code Article',
				'ordre' => 0,
				'type_colonne' => 'select',
			);
        }

		// stock sur les devis
		if(($this->_type_element == 'devis_vente' && fonctionnalite('gescom_stocks_dispo_sur_devis') === true) ||
            ($this->_type_element == 'commande_vente' && fonctionnalite('gescom_stocks_dispo_sur_commande') === true)
        ) {

            $colonnes_articles['stock'] = array(
                'nom' => 'Stock',
                'ordre' => 9,
				'type_colonne' => 'montant',
            );
        }

		// numero_de_serie
		if(fonctionnalite('numeros_de_serie') === true && isset(fonctionnalite('type_document_numero_de_serie')[$this->_type_element]) && fonctionnalite('type_document_numero_de_serie')[$this->_type_element]) {

            $colonnes_articles['designation']['sous_colonnes']['numero_de_serie'] = array(
                'ordre' => 7,
            );
		}

        //tarif force
        if(fonctionnalite('utiliser_les_tarifs_forces_pour_les_nomenclatures') && $articles_modifiables === true && $this->est_une_vente() ) {

            $colonnes_articles['designation']['sous_colonnes']['tarif_force'] = array(
                'ordre' => 0,
            );
		}

        $saisie_documents_afficher_prix = fonctionnalite('saisie_documents_afficher_prix');

		// c'est un BL, on n'affiche pas les tarifs, tva, etc
		if(isset($saisie_documents_afficher_prix[$this->_type_element]) && $saisie_documents_afficher_prix[$this->_type_element] === false) {

			if(isset($colonnes_articles['tarif']))
				$colonnes_articles['tarif']['masquer'] = true;

			if(isset($colonnes_articles['tva']))
				$colonnes_articles['tva']['masquer'] = true;

			if(isset($colonnes_articles['remise']))
				$colonnes_articles['remise']['masquer'] = true;

			if(isset($colonnes_articles['tarif_net']))
        		$colonnes_articles['tarif_net']['masquer'] = true;

			if(isset($colonnes_articles['total']))
				unset($colonnes_articles['total']);

			if(isset($colonnes_articles['total_ttc']))
				unset($colonnes_articles['total_ttc']);
		}

		// c'est une facture, on affiche l'avancement
		if($this->_type_element == 'facture_vente' && fonctionnalite('gescom_document_avancement') === true) {

            $colonnes_articles['designation']['sous_colonnes']['avancement'] = array(
                'ordre' => 4,
            );
		}

		// les disponibilités ?
		if(!empty(fonctionnalite('ajout_colonne_disponibilite_sur_documents')[$this->_type_element])) {

            $colonnes_articles['designation']['sous_colonnes']['disponibilite'] = array(
                'ordre' => 8,
            );
		}

        if($this->est_une_vente()){

            $colonnes_articles['designation']['sous_colonnes']['numeros_de_lot'] = array(
                'ordre' => 9,
            );

            if(fonctionnalite('utiliser_les_coefficients')) {
                $colonnes_articles['designation']['sous_colonnes']['coefficient'] = array(
                    'ordre' => 6,
                );
            }

            if(fonctionnalite('entrepot_sur_ligne') && in_array($this->_type_element,['bl_vente','commande_vente'])){

                $colonnes_articles['designation']['sous_colonnes']['entrepot_id'] = array(
                    'ordre' => 6,
                );
            }
        }

        if(fonctionnalite('saisie_documents_devise_etrangere')){

            if(fonctionnalite('documents_devise_etrangere_champ_conversion') == 'tarif'  && isset($colonnes_articles['tarif'])) {
                $colonnes_articles['tarif_devise'] = array(
                    'nom' => 'PU',
                    'nom_dynamique' => "' (' + code_devise +')'",
                    'condition_v_if' => 'utilisation_devise_etrangere()',
					'type_colonne' => 'select',
                    'ordre' => 5,
                );
            }

            if(fonctionnalite('documents_devise_etrangere_champ_conversion') == 'prix_achat'  && isset($colonnes_articles['prix_achat'])) {
                $colonnes_articles['prix_achat_devise'] = array(
                    'nom' => 'PA',
                    'nom_dynamique' => "' (' + code_devise +')'",
                    'condition_v_if' => 'utilisation_devise_etrangere()',
					'type_colonne' => 'select',
                    'ordre' => 3,
                );
            }
        }

        foreach($colonnes_articles as &$colonne){

            if(isset($colonne['sous_colonnes'])){

                uasort($colonne['sous_colonnes'], function($a, $b) {
                    return $a['ordre'] - $b['ordre'];
                });
            }
        }

        uasort($colonnes_articles, function($a, $b) {
            return $a['ordre'] - $b['ordre'];
        });
		
		return $colonnes_articles;
	}

	public function colonnes_articles_lignes_entieres() {

		$colonnes_articles = array(
			
            'description' => array (
                'nom' => 'Description',
                'ordre' => 1,
            ),
        );

		return $colonnes_articles;
	}


	/**
	 *
	 * Retourne la liste des vues à afficher
	 * Les vues sont stockées dans le répertoire vues : formulaires/include/document/includes/options
	 *
	 */
    public function retourne_options_articles() {

    	$articles_modifiables = $this->articles_modifiables();

    	$vues = [];

    	// Remplacer article
    	if(fonctionnalite('gescom_remplacement_article')[$this->_type_element] && $this->remplacement_article_autorise() === true)
			$vues[] = 'gescom_remplacement_article';

    	// Ajouter description
    	if($articles_modifiables === true)
			$vues[] = 'description_lignes';

    	// Déplier/ replier les nomenclatures
    	if(fonctionnalite('gescom_nomenclature_afficher_lignes') && $articles_modifiables === true && $this->est_une_vente())
			$vues[] = 'gescom_nomenclature_afficher_lignes';

    	// Photo sur le PDF
		if(fonctionnalite('gescom_photo_article_sur_document') && $articles_modifiables === true && $this->est_une_vente())
			$vues[] = 'gescom_photo_article_sur_document';

		// Activer les tarifs forcés sur les nomenclatures
		if(fonctionnalite('utiliser_les_tarifs_forces_pour_les_nomenclatures') && $articles_modifiables === true && $this->est_une_vente())
			$vues[] = 'utiliser_les_tarifs_forces_pour_les_nomenclatures';

		// Ajouter numéro de lot
		if(fonctionnalite('numeros_de_lot') === true && $articles_modifiables === true && fonctionnalite('gescom_afficher_option_ajout_lot')[$this->_type_element])
			$vues[] = 'numeros_de_lot';

		// masquer des lignes sur les devis
		if(fonctionnalite('gescom_masquer_lignes_du_document') && $this->est_une_vente() && $articles_modifiables === true)
			$vues[] = 'gescom_masquer_lignes_du_document';

        $vues[] = 'historique';

		// Coefficient
		if($articles_modifiables === true){

            if(fonctionnalite('utiliser_les_coefficients'))
			    $vues[] = 'utiliser_les_coefficients';

            $vues[] = 'ajout_ligne_suite';

		    // Supprimer la ligne
			$vues[] = 'supprimer_ligne';
        }

		return $vues;
    }

    /**
     *
     * Retourne la liste des vues à afficher
     * Les vues sont stockées dans le répertoire vues : formulaires/include/document/includes/options
     *
     */
    public function retourne_options_lignes_diverses() {

        $vues = [];

        $vues[] = 'ajout_ligne_suite';

        // Supprimer la ligne
        $vues[] = 'supprimer_ligne';

        return $vues;
    }


    /**
	 *
	 * Défini quelles sont les colonnes à afficher sur les lignes divers si il y en a
	 *
	 * Peut être surchargé pour ajouter des options sur mesure
	 *
	 */
	public function options_lignes_divers() {

        $options_lignes_divers = array();

        $ordre = 1;

        if(fonctionnalite('saisie_documents_utiliser_les_titres')){
            $options_lignes_divers['titre'] = array(
                'ordre' => $ordre,
                'format' => 'bouton_titre',
            );

            $ordre++;
        }

        if(fonctionnalite('saisie_documents_utiliser_les_sauts_de_ligne')){
            $options_lignes_divers['saut_de_ligne'] = array(
                'ordre' => $ordre,
                'format' => 'bouton_titre',
            );

            $ordre++;
        }

        if(fonctionnalite('saisie_documents_utiliser_les_sauts_de_page')){
            $options_lignes_divers['saut_de_page'] = array(
                'ordre' => $ordre,
                'format' => 'bouton_titre',
            );

            $ordre++;
        }

        if(fonctionnalite('saisie_documents_utiliser_les_sous_totaux')){
            $options_lignes_divers['sous_total'] = array(
                'ordre' => $ordre,
                'format' => 'bouton_titre',
            );

            $ordre++;
        }

        if(fonctionnalite('saisie_documents_utiliser_les_commentaires')){
            $options_lignes_divers['commentaire'] = array(
                'ordre' => $ordre,
                'format' => 'bouton_titre',
            );

            $ordre++;
        }

        if(fonctionnalite('saisie_documents_utiliser_les_remises')){
            $options_lignes_divers['remise'] = array(
                'ordre' => $ordre,
                'format' => 'bouton_titre',
            );

            $ordre++;
        }

        if(fonctionnalite('saisie_documents_utiliser_les_images')){
            $options_lignes_divers['image'] = array(
                'ordre' => $ordre,
                'format' => 'bouton_titre',
            );

            $ordre++;
        }

        if(fonctionnalite('saisie_documents_utiliser_les_notes_internes')){
            $options_lignes_divers['note_interne'] = array(
                'ordre' => $ordre,
                'format' => 'bouton_titre',
            );

            $ordre++;
        }

        if(fonctionnalite('regroupement_articles_documents')){

            $options_lignes_divers['regroupement'] = array(
                'ordre' => $ordre,
                'format' => 'bouton_icone',
                'icone' => 'fas fa-layer-group',
            );

            $options_lignes_divers['regroupement_fermeture'] = array(
                'non_visible' => true,
            );

            $ordre++;

            if($this->_type_element == 'devis_vente') {
                $options_lignes_divers['option'] = array(
                    'ordre' => $ordre,
                    'format' => 'bouton_icone',
                    'icone' => 'fas fa-stream',
                );

                $ordre++;
            }
        }

        if($this->est_une_vente()  && fonctionnalite('utiliser_les_coefficients')){
            $options_lignes_divers['coefficient'] = array(
                    'ordre' => $ordre,
                    'format' => 'bouton_icone',
                    'icone' => "fas fa-euro-sign",
            );

            $ordre++;
        }

        if(fonctionnalite('calculateur_sur_document')){
            $options_lignes_divers['calculateur'] = array(
                    'ordre' => $ordre,
                    'format' => 'bouton_icone',
                    'icone' => "fas fa-calculator",
            );

            $ordre++;
        }

        if(fonctionnalite('entrepot_sur_ligne') && in_array($this->_type_element,['bl_vente','commande_vente'])){

            $options_lignes_divers['entrepot'] = array(
                    'ordre' => $ordre,
                    'format' => 'fichier',
            );

            $ordre++;
        }

        if(fonctionnalite('utiliser_reglage_marge_par_nature') && $this->est_une_vente()){
            $options_lignes_divers['marge_par_nature'] = array(
                    'ordre' => $ordre,
                    'format' => 'bouton_icone',
                    'icone' => "fa fa-percent",
            );

            $ordre++;
        }

		if($this->existe()) {

			$options_lignes_divers['enregistre_lignes'] = array(
					'ordre' => $ordre,
					'format' => 'bouton_icone',
					'icone' => "fa fa-save",
			);

			$ordre++;
		}

        foreach($options_lignes_divers as $index => &$option){
            $option['nom'] = traduction('document.blocs.saisie_des_articles.actions.'.$index);
            $option['aide'] = traduction('document.blocs.saisie_des_articles.aide.'.$index);
        }

        return $options_lignes_divers;

    }

    /**
     *
     * Permet de gérer l'enregistrement de champs libres supplementaires sur les lignes divers
     *
     * Peut être surchargé pour ajouter des champs supplémentaires
     *
     */
    public function options_lignes_divers_champs_supplementaires_enregistrement(){

        $options = array();

        $options['regroupement_id'] = fonctionnalite('regroupement_articles_documents');

        $options['id_temporaire'] = fonctionnalite('regroupement_articles_documents');

        return $options;
    }

	/**
	 *
	 * Sur la saisie d'une commande (vente), il y a une option pour dire qu'elle est expédiée
	 *
	 */
	protected function actions_sur_formulaire_commande_expedie(&$vues) {

		if($this->_type_element == 'commande_vente' && empty($this->modele->expedie) && $this->modele->valide == 1)
			$vues[] = 'commande_vente_expediee';
	}

	/**
	 *
	 * Sur la saisie d'une commande (vente), il y a une option pour dire que les commandes fournisseur associées ont été réalisées
	 *
	 */
	protected function actions_sur_formulaire_commande_fournisseur_realisee(&$vues) {

		if($this->_type_element == 'commande_vente' && empty($this->modele->commande_fournisseur_realisee) && $this->modele->valide == 1)
			$vues[] = 'commande_vente_commande_fournisseur_realisee';
	}

	/**
	 *
	 * Sur la saisie d'une commande (vente), il y a une option pour dire que les commandes fournisseur associées ont été reçues
	 *
	 */
	protected function actions_sur_formulaire_commande_fournisseur_recue(&$vues) {

		if($this->_type_element == 'commande_vente' && empty($this->modele->commande_fournisseur_recue) && $this->modele->valide == 1)
			$vues[] = 'commande_vente_commande_fournisseur_recue';
	}

	/**
	 *
	 * Sur la saisie d'un document, utilisé pour demander une approbation manuelle
	 *
	 */
	public function actions_sur_formulaire_demander_approbation_manuelle(&$vues) {

		// on regarde s'il y a un workflow de demande d'approbation
		$workflow_approbation = modele('approbation_workflow')->where('type_element', $this->_type_element)->where('action', 3)->first();

		if($workflow_approbation === null)
			return;

		// on regarde si l'approbation a déjà été faite
		$approbation_existante = modele('approbation')
									->where('type_element', $this->_type_element)
									->where('element_id', $this->modele->id)
									->where('action', 3)
									->orderBy('id','DESC')
									->first();

		// // On n'affiche pas le bouton que si la demande est validé
		if($approbation_existante !== null){

			if ($approbation_existante->approbation == 1)
				return;
		}

		$vues[] = [
            'id' => 'approbation_manuelle',
            'ordre' => 9
        ];
	}

    /**
     *
     * Sur la saisie d'une facture (vente), il y a une option pour dire que la facture a été envoyée
     *
     * Le statut 5 correspond à A envoyer, et l'objectif de cette action est de passer le statut à 10, qui est "non réglée"
     *
     */
    protected function actions_sur_formulaire_facture_a_envoyer(&$vues) {

		if($this->_type_element == 'facture_vente' && $this->modele->statut <= 5)
            $vues[] = 'facture_vente_statut_a_envoyer';
    }

    /**
     *
     * Sur la saisie d'une commande (vente), il y a une option pour dire que la commande est en statut Accusée de réception
     *
     */
    protected function actions_sur_formulaire_commande_accuse_de_reception(&$vues) {

        if($this->_type_element == 'commande_vente' && $this->modele->statut == 3 )
            $vues[] = 'commande_vente_statut_accuse_de_reception';
    }

	/**
	 *
	 * Défini quelles sont les lignes à afficher pour la saisie des documents
	 *
	 */

	public function lignes_du_document_pour_saisie($type_element_destination = false, $calcule_reliquat = false, $articles_tmp = array()) {

		if(empty($articles_tmp)) {

			if($calcule_reliquat === true)
				$articles_tmp = $this->recupere_articles_pour_transformation(false, $type_element_destination);
			else
				$articles_tmp = $this->articles();
		}

        if($calcule_reliquat === true)
		    $lignes_divers = $this->recupere_lignes_divers_pour_transformation();
        else
			$lignes_divers = $this->lignes_divers_document();

		// temps_execution('lignes_du_document_pour_saisie::etape 2', 2);

		// on va chercher les articles obligatoires
		$articles_obligatoires = modele('article')->where('obligatoire_sur_les_documents', 1)->get();

		$articles_du_document = array();

		// on regarde s'il y a des lignes divers avant les articles ?
        $this->ajoute_lignes_divers_pour_ligne($articles_du_document, $lignes_divers, 0);

		$choix_code_article_sur_saisie_document = fonctionnalite('choix_code_article_sur_saisie_document');
		$gescom_nomenclature_afficher_lignes = fonctionnalite('gescom_nomenclature_afficher_lignes');

		// on retouche le tarif si on est en HT
		$mode_calcul = config('eden.mode_calcul_gescom');

		// pour les achats, on est forcément en HT
		if($this->est_un_achat())
			$mode_calcul = 'ht';

		$articles_ids_pour_les_stocks = array();

		foreach($articles_tmp as $index => $article) {

			if($calcule_reliquat === true){
				$reliquat = $this->calcule_reliquat_pour_ligne($article, $article['id']);

				if($reliquat == 0 && !in_array($type_element_destination, array('avoir_vente','avoir_achat','acompte_vente','acompte_achat'))) {
					$this->ajoute_lignes_divers_pour_ligne($articles_du_document, $lignes_divers, $article->ligne);
					unset($articles_tmp[$index]);
					continue;
				}
			}

			if(!empty($article->numeros_de_lot))
				$article->numeros_de_lot = json_decode($article->numeros_de_lot);
			else
				$article->numeros_de_lot = [];

            if(!empty($article->calculateur))
                $article->calculateur = json_decode($article->calculateur);

            if(!empty($article->coefficient))
                $article->coefficient = json_decode($article->coefficient);

			if(strtolower($mode_calcul) == 'ttc') {

				if(empty($article->tva))
					$article->tva = 0;

				$article->tarif = $article->tarif * ((100 + $article->tva) / 100);
			}

			if($choix_code_article_sur_saisie_document)
				$article->choix_code_article = management('article', $article->article_id)->choix_code_article();


			$article->tarif_initial = $article->tarif;

			// on ajoute l'article
			$articles_du_document[] = $article;

			// on regarde s'il y a des lignes divers ?
            $this->ajoute_lignes_divers_pour_ligne($articles_du_document, $lignes_divers, $article->ligne);
		}

		// on vérifie les articles obligatoires ?
		if($articles_obligatoires !== null && $this->est_une_vente() && !$this->existe()) {

			foreach($articles_obligatoires as $article_obligatoire) {

				$present = false;
				foreach($articles_du_document as $article) {

					if(is_array($article)) {

						if($article['article_id'] == $article_obligatoire->id)
							$present = true;
					}
					else {

						if($article->article_id == $article_obligatoire->id)
							$present = true;
					}
				}

				if($present)
					continue;

				$management_article = management('article', $article_obligatoire->id);

				$codes_article = $management_article->choix_code_article();

				// il n'est pas sur le document, donc on l'ajoute
				$articles_du_document[] = array(

					'article_id' => $article_obligatoire->id,
					'designation' => $article_obligatoire->designation,
					'tarif_saisi' => $article_obligatoire->tarif,
					'tarif' => $article_obligatoire->tarif,
					'tarif_initial' => $article_obligatoire->tarif,
					'prix_achat' => $article_obligatoire->prix_d_achat,
					'quantite' => 1,
					'tva' => $article_obligatoire->taux_de_tva,
					'remise' => 0,
					'modele' => $article_obligatoire,
					'nomenclature' => [],
					'numeros_de_lot' => [],
					'numero_de_serie' => null,
					'disponibilite' => null,
					'description' => null,
					'entrepot_id' => fonctionnalite('entrepot_id_par_defaut'),
					'achats' => [],
					'avancement_precedent' => 0,
					'avancement_actuel' => 0,
					'stock' => $management_article->stock_actuel(),
					'choix_code_article' => $codes_article,
					'code_article' => array_key_first($codes_article),
					'conditionnement_possible' => $this->conditionnements_possibles($article_obligatoire->id),
				);
			}
		}

		// temps_execution('lignes_du_document_pour_saisie::etape 4', 2);

		// si on est dans le cas d'un BL de vente, on n'ajoute pas au document les articles qui ont une quantité nulle
		if($type_element_destination == 'bl_vente') {

			foreach($articles_du_document as $id => $article) {

				if(empty($article->quantite) && !isset($article->type_ligne))
					unset($articles_du_document[$id]);
			}
		}

		foreach($articles_du_document as $id => $article) {

			if(!empty($article->modele))
				$article['choix_code_article'] = management('article', $article->article_id,$article->modele)->choix_code_article();
		}


		// temps_execution('lignes_du_document_pour_saisie::etape 5', 2);

		// on doit forcément faire ça pour réinitialiser les clés, sinon le toString() de Laravel le transforme en objet JS au lieu d'un tableau
		// et ça pose pb sur la saisie des documents
		$articles_du_document_finaux = array();

		foreach($articles_du_document as $article) {

			$articles_du_document_finaux[] = $article;
		}

		return collect($articles_du_document_finaux);
	}

	/**
	 *
	 * Retourne les conditionnements possibles pour un article
	 *
	 */
	public function conditionnements_possibles($id_article, $id_fournisseur = false) {

		if($id_fournisseur === false && $this->est_un_achat())
			$id_fournisseur = $this->modele->fournisseur_id;

		if($this->est_une_vente())
			$conditionnements_possibles = modele('conditionnement')->where('article_id',$id_article)->get()->keyBy('id');
		else
			$conditionnements_possibles = modele('article_fournisseur')->select('conditionnement.*','article_fournisseur.tarif as tarif','article_fournisseur.conditionnement_id as conditionnement_id')->join('conditionnement', 'article_fournisseur.conditionnement_id', '=', 'conditionnement.id')->where('article_fournisseur.article_id',$id_article)->where('fournisseur_id', $id_fournisseur)->get()->keyBy('conditionnement_id');

		$conditionnements_finaux = array(

			0 => array('affichage' => management('article', $id_article)->champ('unite')->affiche()),

		);

        if($conditionnements_finaux[0]['affichage'] == "")
            $conditionnements_finaux[0]['affichage'] = "Unité";

		foreach($conditionnements_possibles as $id => $conditionnement) {

			$conditionnement['affichage'] = management('conditionnement', $id,$conditionnement)->affiche();

			$conditionnements_finaux[$id] = $conditionnement;
		}

		return $conditionnements_finaux;
	}

	/**
	 *
	 * L'historique du document pour la page de saisie
	 *
	 */
	public function historique() {

		// On va chercher l'ensemble de l'historique
		$historique = parent::historique();

        $paiements = modele('paiement')->avec_inactifs()
            ->where('type_element', $this->_type_element)
            ->where('id_document', $this->modele->id)
            ->get();

        foreach($paiements as $paiement){

            $management_paiement = management('paiement',$paiement->id,$paiement);

            $historique_paiements = $management_paiement->historique();

            foreach($historique_paiements as $historique_paiement){
                $historique_paiement->intitule = traduction(Variables::$historique_intitule[$historique_paiement->type_action][1])
                    .' '.traduction('tables_libres.paiement.element').' '.$management_paiement->modele->titre;
            }

            $historique = $historique->merge($historique_paiements);

        }

        return $historique->sortByDesc('date')->values();
	}

	/**
	 *
	 * Retourne la liste des transformations possibles pour un document (un devis en commande, etc)
	 *
	 */
    public function transformations_possibles($transformations_possibles = array()) {

        // Si le document n'est pas validé, ou qu'il est annulé, on ne peut pas le transformer
        if(empty($this->modele) || $this->modele->valide != 1 || $this->modele->annule == 1)
            return array();

        // on retraite en fonction de la config
        foreach(Variables::$documents_gescom as $type_element) {
            if(isset($transformations_possibles[$type_element]) && fonctionnalite('gescom_'.$type_element) !== true) {
                unset($transformations_possibles[$type_element]);
            }
        }
        $transformations_possibles_finales = array();

        //On modifie les clés du tableau pour l'affichage
        foreach($transformations_possibles as $type_element => $url) {

            $type_element_destination = management($type_element)->est_un_achat();

            if($type_element_destination)
                $type_element_destination = 'achat';
            else
                $type_element_destination = 'vente';

            if(($type_element == 'avoir_achat' && $this->est_un_achat()) || ($type_element == 'avoir_vente' && $this->est_une_vente())) {

                $transformations_possibles_finales[$type_element_destination][$type_element . ' partiel'] = $url;
                $transformations_possibles_finales[$type_element_destination][$type_element . ' total'] = route('document.supprimer', array($this->_type_element, $this->modele->id));
            }
            else {

                $transformations_possibles_finales[$type_element_destination][$type_element] = $url;
            }

            unset($transformations_possibles[$type_element]);
        }
        return $transformations_possibles_finales;
    }

    /**
     *
     * Retourne la liste des transformations possibles pour un document (un devis en commande, etc)
     *
     */
    public function affichage_transformations_possibles() {

        $transformations_possibles_traitees = [];
        $transformations_possibles = $this->transformations_possibles();

        foreach ($transformations_possibles as $categorie_transformation => $transformations){

            if(empty($transformations))
                continue;

            $transformations_possibles_traitees[$categorie_transformation] = [];

            foreach ($transformations as $type => $route){

                $array_tmp = explode( " ", $type);

                $type_element = $array_tmp[0];

                $titre_supplementaire = false;

                if(isset($array_tmp[1]))
                    $titre_supplementaire = $array_tmp[1];

                $traduction = ucfirst(table_libre($type_element)->element);

                if($titre_supplementaire !== false)
                    $traduction .= " " . traduction('interface.transformations_possibles.' . $titre_supplementaire);

                if($categorie_transformation == 'vente')
                    $traduction .= " (" . traduction('interface.transformations_possibles.client') . ")";
                else
                    $traduction .= " (" . traduction('interface.transformations_possibles.fournisseur') . ")";

                $transformations_possibles_traitees[$categorie_transformation][str_replace(' ', '_', $type)] = [
                    'nom' => $traduction,
                    'url' => $route
                ];


            }

        }

        return $transformations_possibles_traitees;

    }

    /**
     *
     * Retourne les modeles de relances (existe uniquement pour la spécification sur facture_vente et acompte_vente)
     *
     *
     */
    public function affichage_modeles_de_relances(){

        return [];

    }

	/**
	 *
	 * On logue le fait d'avoir enregistré comme réglé un document
	 *
	 * @return void
	 *
	 */
    public function log_reglement() {

        return $this->enregistrer_log(Variables::$types_logs['reglement']);
    }

	/**
	 *
	 * On logue le fait d'avoir enregistré un paiement sur un document
	 *
	 * @return void
	 *
	 */
    public function log_paiement() {

        return $this->enregistrer_log(Variables::$types_logs['saisie_paiement']);
    }

	/**
	*
	* On logue la validation d'un document
	*
	* @return void
	*
	*/
    public function log_validation() {

        return $this->enregistrer_log(Variables::$types_logs['validation']);
    }

	/**
	*
	* On logue l'acceptation d'un document
	*
	* @return void
	*
	*/
    public function log_acceptation() {

        return $this->enregistrer_log(Variables::$types_logs['acceptation']);
	}

	/**
	 *
	 * On logue le refus d'un document
	 *
	 * @return void
	 *
	 */
    public function log_refus() {

        return $this->enregistrer_log(Variables::$types_logs['refus']);
    }

	/**
	 *
	 * On logue le refus d'un document
	 *
	 * @return void
	 *
	 */
    public function log_annulation_devis() {

        return $this->enregistrer_log(Variables::$types_logs['annulation_devis']);
    }

	/**
	 *
	 * On logue le refus d'un document
	 *
	 * @return void
	 *
	 */
    public function log_annulation_reglement() {

        return $this->enregistrer_log(Variables::$types_logs['annulation_reglement']);
	}

	/**
	 *
	 * On logue le reglement d'un document
	 *
	 * @return void
	 *
	 */
    public function log_validation_reglement() {

        return $this->enregistrer_log(Variables::$types_logs['validation_reglement']);
    }

	/**
	 *
	 * On logue la facturation d'un document
	 *
	 * @return void
	 *
	 */
    public function log_facturation() {

        return $this->enregistrer_log(Variables::$types_logs['facturation']);
    }

	/**
	 *
	 * On logue l'expedition d'un document
	 *
	 * @return void
	 *
	 */
    public function log_expedition() {

        return $this->enregistrer_log(Variables::$types_logs['expedition']);
    }

	/**
	 *
	 * On logue l'expedition d'un document
	 *
	 * @return void
	 *
	 */
    public function log_commande_fournisseur_realisee() {

        return $this->enregistrer_log(Variables::$types_logs['commande_fournisseur_realisee']);
    }

	/**
	 *
	 * On logue l'expedition d'un document
	 *
	 * @return void
	 *
	 */
    public function log_commande_fournisseur_recue() {

        return $this->enregistrer_log(Variables::$types_logs['commande_fournisseur_recue']);
    }

	/**
	 *
	 * On logue l'envoi par mail
	 *
	 * @return void
	 *
	 */
    public function log_envoi_par_mail() {

        return $this->enregistrer_log(Variables::$types_logs['envoi_par_mail']);
    }

	/**
	 *
	 * On logue la comptabilisation d'un document
	 *
	 * @return void
	 *
	 */
    public function log_comptabilisation() {

        return $this->enregistrer_log(Variables::$types_logs['comptabilisation']);
	}


	/**
	 *
	 * Le log le fait qu'un document ait été mis en attente d'un retour client
	 *
	 */
	public function log_mise_en_attente_document() {

        return $this->enregistrer_log(Variables::$types_logs['mise_en_attente']);
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
	 * Trigger post suppression
	 *
	 * @return void
	 *
	 */
	protected function methodes_post_suppression($modele) {

		parent::methodes_post_suppression($modele);

        if(!defined('gestion_lignes'))
            define('gestion_lignes',$this->_type_element);

		// on supprime les lignes
		$articles = $this->articles();

        $this->charger_articles_du_document($articles);

        $this->lignes_articles();

		foreach($articles as $article) {

            $management_ligne = management($this->_type_element.'_lignes', $article['id'], $article);

            $management_ligne->management_parent = $this;

			$management_ligne->supprime();
		}

		// On détache les paiements sur ce document si il y en a
		$paiements = modele('paiement')->where('id_document', $this->modele->id)->where('type_element', $this->_type_element)->get();

		if (!$paiements->isEmpty() && empty($this->modele->annulee_par_avoir)) {

			// On le / les détaches
			foreach ($paiements as $paiement) {

				$paiement->type_element = null;
				$paiement->id_document = null;

				$paiement->save();
			}
		}

		// Si lot_mouvement, on les supprime
		$lots_mouvement = modele('lot_mouvement')->where('element_id', $this->modele->id)->where('type_element', $this->_type_element)->get();

		if (!$lots_mouvement->isEmpty() && !( $this->_type_element == "facture_vente" && $this->modele->annulee_par_avoir == 1 && $this->modele->valide == 1)) {

			foreach ($lots_mouvement as $lot_mouvement) {

				$lot_mouvement->inactif = 1;
				$lot_mouvement->save();

				// On vérifie si le lot ne contient plus de mouvement
				$lots_mouvement_lot = modele('lot_mouvement')->where('lot_id',$lot_mouvement->lot_id)->get();

				if ($lots_mouvement_lot->isEmpty()) {

					$lot_a_supprimer = modele('lot', $lot_mouvement->lot_id);
					$lot_a_supprimer->inactif = 1;
					$lot_a_supprimer->save();
				}
			}
		}

        $this->gestion_lignes();

        $this->management_ligne()->trigger_applicatif_elements_multiples($this->lignes_articles()->pluck('id')->toArray());
	}

	/**
	 *
	 * A t on le droit de faire des changements d'entités sur cet élément ? oui si proforma, non sinon
	 *
	 */
	public function modification_entite_autorisee() {

		if(empty($this->modele->valide))
			return true;

		return false;
	}

	/**
	 *
	 * On récupère la liste des modèles sur mesure pour impression pour le management courant
	 *
	 * @return array
	 *
	 */
	public function recupere_documents_pour_impressions() {

        // on va chercher la liste des modules disponibles
        $modeles_impressions = array();

        // le répertoire standard
		if(is_dir(app_path('Eden/Views/pdf/'.$this->_type_element))) {

			$repertoire = scandir(app_path('Eden/Views/pdf/'.$this->_type_element));

			foreach($repertoire as $fichier) {

				if($fichier == '.' || $fichier == '..')
					continue;

				$fichier = str_replace('.blade.php', '', $fichier);

				$nom_modele = str_replace('_', ' ', $fichier);
				$nom_modele = ucfirst($nom_modele);

				$modeles_impressions[$fichier] = $nom_modele;
			}
		}

        // le spécifique
        if(is_dir(resource_path('views/vendor/eden/fiches/pdf/'))) {

            $repertoire = scandir(resource_path('views/vendor/eden/pdf/'.$this->_type_element));

            foreach($repertoire as $fichier) {

                if($fichier == '.' || $fichier == '..')
                    continue;

                $fichier = str_replace('.blade.php', '', $fichier);
                $nom_modele = str_replace('.', ' ', $fichier);
                $nom_modele = ucfirst($nom_modele);

                $modeles_impressions[$fichier] = $nom_modele;
            }
        }

        return $modeles_impressions;
	}

	/**
	 *
	 * Retourne les colonnes à sélectionner pour les informations à afficher sur la liste des articles quand on fait une recherche sur un document
	 * Le but de cette méthode est de pouvoir être surchargée pour ajouter des informations sur certains projets
	 *
	 */
	public function retourne_colonnes_selection_articles_recherche_sur_document() {

		return array(
            'article.*',
			'designation',
			'code_article',
			'tarif_force',
			'prix_d_achat',
			'famille_id',
			'type_article',
			'chaine_tags_recherche'
		);
	}

	/**
	 *
	 * Retourne la liste des articles disponibles pour la saisie
	 *
	 */
	public function recupere_articles_disponibles_pour_la_saisie($parametres) {

		$articles = $this->requete_article_disponible_pour_saisie($parametres)->get();

		$familles = modele('famille')->get()->pluck('nom', 'id');
		$article_pack = modele('article_contenu_pack')->get()->pluck('article_id', 'article_id');

		$article_fournisseur = modele('article_fournisseur')
                ->whereIn('article_id',$articles->pluck('id')->toArray())
				->get()
				->groupBy('article_id');

        $gescom_affichage_prix_achat_liste_articles = fonctionnalite('gescom_affichage_prix_achat_liste_articles');

		if(fonctionnalite('recherche_article_document_gescom_avec_image') === true)
			$images = Element_image::where('type_element', 'article')->get()->pluck('chemin', 'element_id')->toArray();

		// on ajoute automatiquement les traductions
		foreach($articles as $article) {

            $management_article = management('article',$article->id,$article);

            $article['code_article'] = $management_article->affiche();

			$article->nom_famille = $familles[$article->famille_id] ?? '';
			$article->pack = isset($article_pack[$article->id]) ? 1 : 0;
			$article->image = $images[$article->id] ?? null;
			$article->ref = null;

			if(isset($article_fournisseur[$article->id])) {

				$reference_des_fournisseurs = $article_fournisseur[$article->id]->pluck('reference')->toArray();
				$article->ref = implode(', ',$reference_des_fournisseurs);
			}
		}

        if($articles->isNotEmpty())
            management('article')->applique_conditions_commerciales($articles,$parametres['parametres_article']);

        foreach($articles as $article){
			
            if(fonctionnalite('utiliser_les_tarifs_forces_pour_les_nomenclatures') &&
                ($article['type_article'] == 1 || $article['type_article'] == 3)){

                if(!empty($article['tarif_force'])) {

                    $article['tarif'] = $article['tarif_force'];
                    unset($article['tarif_force']);
                }

                if(!empty($article['prix_achat_force'])) {

                    $article['prix_d_achat'] = $article['prix_achat_force'];
                    unset($article['prix_achat_force']);
                }
            }
        }

		return $articles;
	}

	/**
	 * 
	 * Retourne la requête pour récupérer les articles disponibles pour la saisie
	 * @param array $parametres
	 * @param string $parametres['recherche'] La recherche à effectuer
	 * @param int $parametres['nombre_elements'] Le nombre d'éléments
	 * @param int $parametres['pagination'] La pagination à appliquer
	 * @return \Illuminate\Database\Eloquent\Builder
	 * 
	 */
	public function requete_article_disponible_pour_saisie($parametres){

		$recherche = $parametres['recherche'];

        $nombre_elements = $parametres['nombre_elements'];

        $pagination = $parametres['pagination'];

        $recherches = explode(' ', $recherche);

		$articles = modele('article')->take($parametres['nombre_elements']);

        $select = implode(',',$this->retourne_colonnes_selection_articles_recherche_sur_document());

        if(!empty($recherches) && (count($recherches) > 1 || $recherches[0] != '%') ) {

            $select.= ",chaine_tags_recherche LIKE '%" . $recherche . "%' as ordre_chaine_complete,(";

            foreach ($recherches as $index => $recherche_tmp) {

                if($index > 0)
                    $select.= '+';

                $select .="MATCH (chaine_tags_recherche) AGAINST ('" . $recherche_tmp . "')";

                $articles->where("chaine_tags_recherche", 'like', '%' . $recherche_tmp . '%');
            }

            $select.=')/'.sizeof($recherches).' as ordre';

            $resultat = $articles->select(DB::raw($select))->orderBy('chaine_tags_recherche');
        }

        $articles = $resultat->skip($pagination * $parametres['nombre_elements'])->take($parametres['nombre_elements']);

        if($this->est_un_achat()) {
            $articles = $articles->where(function($r) {

					$r->whereIn('disponible_pour_saisie',array(0,2))->orWhereNull('disponible_pour_saisie');
				});
        }

        else {
            $valeurs = array(0, 1);
            if(in_array($this->_type_element, Variables::$documents_avoir_et_retour_vente))
                $valeurs[] = 4;

            $articles = $articles->where(function($r) use ($valeurs) {

					$r->whereIn('disponible_pour_saisie',$valeurs)->orWhereNull('disponible_pour_saisie');
				});
        }

		return $articles;
	}

	/**
	 *
	 * Retourne true si le type_element est un document de vente
	 *
	 */
	public function est_une_vente() {

		if(strpos($this->_type_element, '_vente') !== false) {

			return true;
		}

		return false;
	}

	/**
	 *
	 * Retourne true si le type_element est un document achat
	 *
	 */
	public function est_un_achat() {

		if(strpos($this->_type_element, '_achat') !== false) {

			return true;
		}

		return false;
	}

	/**
	 *
	 * Facture les documents (par exemple, transforme 5 commandes en une facture)
	 *
	 * @param $formulaire le formulaire qui provient de la liste libre
	 * @param $type_element string, le type element d'origine (commande_vente par exemple)
	 *
	 */
	public function facturer_documents($formulaire, $type_element) {

		$est_achat = $this->est_un_achat();
		$champ_tiers = $est_achat ? 'fournisseur_id' : 'client_id';
		$type_tiers  = $est_achat ? 'fournisseur' : 'client';
		$type_facture = $est_achat ? 'achat' : 'vente';

		$documents = modele($type_element)->whereIn('id', $formulaire->ids)->get()->keyBy('id');

		$tiers_ids = $documents->pluck($champ_tiers);
		$tiers     = modele($type_tiers)->whereIn('id', $tiers_ids)->get()->keyBy('id');

		$documents_par_tiers = [];

		foreach ($formulaire->ids as $element_id) {
			$tiers_id = $documents[$element_id]->$champ_tiers;

			if ($tiers[$tiers_id]->refuse_facturation_groupee == 1) {
				$tiers_id .= uniqid('', true);
			}

			if (!isset($documents_par_tiers[$tiers_id])) {
				$documents_par_tiers[$tiers_id] = ['type_element' => $type_element, 'ids' => []];
			}

			$documents_par_tiers[$tiers_id]['documents'][$element_id] = management($type_element, $element_id, $documents[$element_id]);
		}

		return $this->traiter_facturation_par_tiers($documents_par_tiers, $formulaire, $type_element, $tiers, $type_facture);
	}

	private function traiter_facturation_par_tiers($documents_par_tiers, $formulaire, $type_element, $tiers, $type_facture){

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
			'comptabilise',
			'annulee_par_avoir',
			'reference_document',
			'pdf',
			'id_recurrence',
			'maj_droits',
			'chaine_tags_recherche',
			'date_changement_statut',
            'type_modele_document',
            'valide_n1',
            'relance_1',
            'relance_2',
            'relance_3',
            'relance_4',
            'relance_1_ok',
            'relance_2_ok',
            'relance_3_ok',
            'relance_4_ok',
            'commentaires_recouvrement',
            'lien_interface_paiement_payline',
            'lien_interface_paiement_stripe',
            'date_de_reglement_reelle',
            'delai_de_reglement',
            'annulee_v1',
            'document_relu',
            'document_relu_par',
            'document_relu_le',
            'statut',
            'envoye_par_mail',
            'retard',
            'statut_approbation',
            'facture_id_source',
            'acompte_id_source',
		);

		$nombre_documents = 0;
		$nombre_factures = 0;
        $factures_crees = [];

		// Pour chaque client
		// Attention la variable $client_id n'est pas forcément un vrai client_id,
		// Notamment dans le cas ou on ne doit pas rassembler plusieurs commandes sur une même facture pour un client,
		// dans ce cas là, on ajoute un uniqid au client_id, il ne faut donc surtout pas s'en servir comme d'un id de client comme d'habitude
		foreach($documents_par_tiers as $tiers_id => $donnees_fusion) {

			// On prends les informations du premier document
			$management = Arr::first($donnees_fusion['documents']);

			// on vérifie si le montant du document est > à 0 (paramétrage)
			if(($type_element == "commande_vente" && fonctionnalite('gescom_ne_pas_facturer_commande_vente_lors_fusion_vers_facture') == true ) || ($type_element == "bl_".$type_facture && fonctionnalite('gescom_ne_pas_facturer_bl_lors_fusion_vers_facture') == true )) {

				if($management->modele->montant_document_ttc == 0)
					continue;
			}


			// On crée un nouvel élément facture_vente
			$nouveau_management = management('facture_'.$type_facture);

			// On génère la liste des infos à conserver
			$champs_libres = table_libre('facture_'.$type_facture)->champs_libres()->get()->pluck('nom_sql')->toArray();

			$infos = array();
			foreach($management->modele->getAttributes() as $champ => $valeur) {

				if(in_array($champ, $champs_non_transformables))
					continue;

				if(!in_array($champ, $champs_libres))
					continue;

				$infos[$champ] = $valeur;
			}

			// On rajoute la date
			$infos['date'] = date('Y-m-d');

			// la date de règlement
			$infos['date_de_reglement'] = date('Y-m-d');

			$champ_modele = 'modele_document_defaut_facture_'.$type_facture;

            if(!empty($tiers[$tiers_id]->$champ_modele))
                $infos['type_modele_document'] = $tiers[$tiers_id]->$champ_modele;

			// on gère le cas ou il y a une modalité de paiement
			if(isset($infos['modalite_paiement_id']) && !empty($infos['modalite_paiement_id'])) {

				$modalite_paiement_management = management('modalite_paiement', $infos['modalite_paiement_id']);

				$infos['date_de_reglement'] = $modalite_paiement_management->calcule_date_reglement(date('Y-m-d'));
			}

			// On enregistre afin de générer un id à l'élément
			$infos = $nouveau_management->retouche_infos_pour_facturation_depuis_autre_document($infos);

			$retour = $nouveau_management->enregistre($infos);

			if($retour !== true) {

				return ['retour' => false, 'message' => $retour];
			}

			// Puis les articles et lignes divers
			$articles_a_fusionner = [];
			$lignes_divers = [];
			$count = 0;
			$ligne_article = 1;

			// Pour chacun des documents du client
			foreach($documents_par_tiers[$tiers_id]['documents'] as $id_document => $management) {

				$nombre_documents++;

				$articles = $management->recupere_articles_pour_transformation(true, $type_element);

                $lignes_divers_tmp = $management->recupere_lignes_divers_pour_transformation();

				$nombre_article = count($articles);
				$count_nombre_article = 0;

                if(fonctionnalite('gescom_separation_dans_facturer_documents')) {

                    $message_type = $management->nom_ligne_titre_pour_fusion_de_documents($type_element, $management->modele);

                    if(!empty($lignes_divers) && $lignes_divers[count($lignes_divers) - 1]['ligne'] == $count)
                        $position_separation = $lignes_divers[count($lignes_divers) - 1]['position']+1;
                    else
                        $position_separation = 0;

                    $ligne_divers = [
                        'document_id' => $nouveau_management->modele->id,
                        'type_element' => $formulaire->type_element,
                        'contenu' => $message_type,
                        'ligne' => intval($count),
                        'position' => $position_separation,
                        'type' => 'titre',
                        'nom' => $message_type
                    ];

                    $lignes_divers[] = $ligne_divers;

                }

                foreach ($lignes_divers_tmp as $ligne_divers_tmp){

                    if($ligne_divers_tmp->ligne == 0){

                        unset($ligne_divers_tmp->id);

                        $ligne_divers_tmp->ligne = $count;

                        if(empty($lignes_divers))
                            $ligne_divers_tmp->position = 0;
                        else
                            $ligne_divers_tmp->position = $lignes_divers[count($lignes_divers) - 1]['position'];

                        $lignes_divers[] = $ligne_divers_tmp;
                    }

                }

				// Pour chaque article
				foreach($articles as $article) {

					$count_nombre_article++;
					$count++;

                    foreach ($lignes_divers_tmp as $ligne_divers_tmp){

                        if($ligne_divers_tmp->ligne == $article->ligne){

                            unset($ligne_divers_tmp->id);

                            $ligne_divers_tmp->ligne = $ligne_article;

                            if($lignes_divers[count($lignes_divers) - 1]['ligne'] == $ligne_divers_tmp->ligne)
                                $ligne_divers_tmp->position = $lignes_divers[count($lignes_divers) - 1]['position']+1;
                            else
                                $ligne_divers_tmp->position = 0;

                            $lignes_divers[] = $ligne_divers_tmp;
                        }

                    }

					$articles_a_fusionner[$ligne_article] = $article->toArray();
					$ligne_article++;

					//si c'est le dernier article on ajoute la ligne divers
					if($count_nombre_article == $nombre_article && fonctionnalite('gescom_separation_sous_total_dans_facturer_documents')) {

						$message_type = $management->nom_ligne_total_pour_fusion_de_documents();

                        if($lignes_divers[count($lignes_divers) - 1]['ligne'] == $ligne_divers_tmp->ligne)
                            $position_separation = $lignes_divers[count($lignes_divers) - 1]['position']+1;
                        else
                            $position_separation = 0;

						$ligne_divers = [
							'document_id' => $nouveau_management->modele->id,
							'type_element' => $formulaire->type_element,
							'contenu' => $message_type,
							'ligne' => intval($count),
							'position' => $position_separation,
							'type' => 'sous_total',
							'nom' => $message_type
						];

						$lignes_divers[] = $ligne_divers;


						$ligne_divers = [
							'document_id' => $nouveau_management->modele->id,
							'type_element' => $formulaire->type_element,
							'contenu' => '',
							'ligne' => intval($count),
							'position' => $position_separation,
							'type' => 'saut_de_ligne',
							'nom' => ''
						];

						$lignes_divers[] = $ligne_divers;

					}
				}

				// on regarde si on doit ajouter un acompte
				if($this->est_une_vente()) 
					$nouveau_management->ajoute_article_acompte($management, $articles_a_fusionner);
			}

			// On ajoute articles et lignes divers
			$infos['articles'] 		= $articles_a_fusionner;
			$infos['lignes_divers'] = $lignes_divers;

			// On enregistre le tout
			$retour = $nouveau_management->enregistre($infos);

			// puis finalement, on gère le transfert éventuel des paiements
			// Pour chacun des documents du client
			if(in_array($formulaire->type_element, array('devis_'.$type_facture, 'commande_'.$type_facture))) {

				foreach($documents_par_tiers[$tiers_id]['documents'] as $id_document => $document) {

					$paiements_lies_au_document = modele('paiement')
														->sans_profils()
														->where('type_element', $formulaire->type_element)
														->where('id_document', $id_document)
														->get();

					foreach($paiements_lies_au_document as $paiement) {

						$modifications = array(

							'type_element' => 'facture_'.$type_facture,
							'id_document' => $nouveau_management->modele->id,
						);

						$paiement_management = management('paiement', $paiement->id);

						$paiement_management->enregistre($modifications);
					}

					// il faut mettre à jour le solde de la commande
					$document->maj_total_document();
				}
			}

            $factures_crees[] = $nouveau_management->affiche_lien();
			$nombre_factures++;

			return ['retour' => true, 'nombre_documents' => $nombre_documents, 'nombre_factures' => $nombre_factures, 'factures_crees' => $factures_crees];
		}
	}

    /**
     *
     * Transforme un document en commande_vente
     *
     */
    public function commander_documents($formulaire, $type_element)
    {

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
            'comptabilise',
            'annulee_par_avoir',
            'reference_document',
            'pdf',
            'id_recurrence',
            'maj_droits',
            'chaine_tags_recherche',
            'date_changement_statut',
        );

        $nombre_documents = 0;
        $nombre_commandes = 0;


        // Pour chaque client
        foreach ($formulaire->ids as $element_id) {

            // On prends les informations du premier document
            $management = management($type_element, $element_id);

            // On crée un nouvel élément commande_vente
            $nouveau_management = management('commande_vente');

            // On génère la liste des infos à conserver
            $champs_libres = table_libre('commande_vente')->champs_libres()->get()->pluck('nom_sql')->toArray();
            $infos = array();
            foreach ($management->modele->getAttributes() as $champ => $valeur) {

                if (in_array($champ, $champs_non_transformables))
                    continue;

                if (!in_array($champ, $champs_libres))
                    continue;

                $infos[$champ] = $valeur;
            }

            // On rajoute la date
            $infos['date'] = date('Y-m-d');

            // On enregistre afin de générer un id à l'élément
            $infos = $nouveau_management->retouche_infos_pour_commande_depuis_autre_document($infos);

            $retour = $nouveau_management->enregistre($infos);

            if ($retour !== true) {

                return ['retour' => false, 'message' => $retour];
            }

            $nombre_documents++;

            // Si c'est un document possedant transforme_en_commande, on modifie
            if ($management->modele->transforme_en_commande == null) {

                $modification = array('transforme_en_commande' => 1);
                $management->enregistre($modification);
            }


            // On ajoute articles et lignes divers
            $infos['articles'] = $management->recupere_articles_pour_transformation(true, $type_element);
            $infos['lignes_divers'] = $management->recupere_lignes_divers_pour_transformation();

            // On enregistre le tout
            $retour = $nouveau_management->enregistre($infos);

            $nombre_commandes++;
        }

        return ['retour' => true, 'nombre_documents' => $nombre_documents, 'nombre_commandes' => $nombre_commandes];
    }

	/**
	 *
	 * On calcule le reliquat de CA pour les commandes vente, en fonction de ce qui a déjà été livré
	 *
	 */
	public function calcule_reliquat_ca() {

		if($this->_type_element != 'commande_vente')
			return true;

        DB::select('UPDATE '.$this->_type_element.'_lignes as l
            SET total_reliquat = IF(l.quantite > 0,l.total * l.transforme_reliquat / l.quantite,0),
            total_marge_reliquat = IF(l.quantite > 0,(l.total - l.quantite * l.prix_achat) * l.transforme_reliquat / l.quantite,0)
            WHERE document_id = '.$this->modele->id);

        DB::select('UPDATE '.$this->_type_element.' as d
            INNER JOIN
            (
                SELECT l.document_id as id,
                SUM(l.total_reliquat) as montant_document_reliquat,
                SUM(l.total_marge_reliquat) as montant_document_marge_reliquat
                FROM '.$this->_type_element.'_lignes l
                WHERE l.document_id = '.$this->modele->id.'
                GROUP BY l.document_id
            ) d2
            ON d.id = d2.id
            SET d.montant_document_reliquat = d2.montant_document_reliquat,
            d.montant_document_marge_reliquat = d2.montant_document_marge_reliquat
            WHERE d.id = '.$this->modele->id);
	}

	/**
	 *
	 * Ajoute les informations calculer sur les lignes du document
	 *
	 */
	public function ajoute_information_calculer_sur_lignes($totaux,$articles) {

        $lignes_du_document = $this->modele_lignes()
								->where('document_id', $this->modele->id)
								->where(function($r) {

									$r->whereNull('nomenclature_ligne_parent')->orWhere('nomenclature_ligne_parent', 0);
								})
								->orderBy('ligne')
								->get()->keyBy('id');

        $modeles_articles = $this->articles_du_document()->keyBy('id');

		foreach($articles as $index => $article) {

            if (isset($article['type_ligne']))
                continue;

            $modele_ligne = $lignes_du_document[$article['id']];

            $modele_article = null;

            if (isset($modeles_articles[$modele_ligne->article_id]))
                $modele_article = $modeles_articles[$modele_ligne->article_id];

            // pas de remise globale sur les frais de port
            if (!empty($modele_article) && $modele_article->type_article != 2) {

                $modele_ligne->remise_globale_ligne = (1 - $totaux['remise_en_pourcentage_avant_prorata']);
            } else {

                $modele_ligne->remise_globale_ligne = 1;
            }

            $total_de_la_ligne = $totaux['par_ligne'][$index];
            $tarif_apres_remise = $total_de_la_ligne * floatval($modele_ligne->remise_globale_ligne);

			$management_ligne = $this->management_ligne($modele_ligne->id,$modele_ligne);

			$management_ligne->enregistre_modele(
                array(
                    'total' => $total_de_la_ligne,
                    'tarif_apres_remise' => $tarif_apres_remise
                )
            );
		}
	}

	/**
	 *
	 * Calcule la marge d'un document
	 *
	 */
	public function calcule_marge() {

		if(!$this->est_une_vente())
			return;

		$articles = $this->articles();

		$total_prix_achat = 0;
		$total_prix_vente = 0;

		foreach($articles as $article) {

			$total_prix_achat += $article->prix_achat * $article->quantite;
			$total_prix_vente += ($article->tarif * $article->quantite) * (1 - ($article->remise / 100)) * $article->remise_globale_ligne;

		}

		// on regarde les remise appliquée

		$marge = $total_prix_vente - $total_prix_achat;

		log_eden("Document_management::calcule_marge::avant enregistrement", 3);
		$this->enregistre_modele(['marge' => round($marge, 2)]);
		log_eden("Document_management::calcule_marge::après enregistrement", 3);
	}

	/**
	 *
	 * Cette méthode crée le PDF via le modele
	 *
	 */
	public function creation_document_pdf_sur_mesure($modele_document) {


		$donnees_pour_pdf = $this->$modele_document();

		$nom_du_pdf = $modele_document.'.pdf';

		$pdf = PDF::loadView('eden::pdf.'.$this->_type_element.'.'.$modele_document, $donnees_pour_pdf);

		return $pdf->stream();
	}

	/**
	 *
	 * On retourne une modale pour la relecture
	 *
	 */
	protected function modale_pour_relecture() {

		$donnees = $this->utilisateurs_pour_relecture();

		if(is_array($donnees) && !empty($donnees))
			return $donnees;

		return array();
	}

	protected function utilisateurs_pour_relecture() {

		$donnees =  modele('utilisateur')->liste_utilisateurs_visibles();

		return array("message" => traduction('messages.php.document.relire_document'), "donnees" => $donnees);
	}

	public function enregistre_relecture($utilisateur) {

		false;
	}


	public function document_a_envoyer_pour_relecture() {

		return url('/').'/eden/document/'.$this->_type_element.'/'.$this->modele->id;
	}

	/**
	 *
	 * Retourne le sujet du l'email de relecture
	 *
	 */
	public function retourne_sujet_du_mail_pour_relecture() {

		return moi()->prenom ." vient d'éditer un document";
	}

	/**
	 *
	 * Envoie l'email de relecture
	 *
	 */
	public function envoie_email_pour_relecture($formulaire) {

		// retourne l'url du document qui sera affiché dans le PDF
		$url = $this->document_a_envoyer_pour_relecture();
		$url_du_pdf = $this->modele->pdf;
		$sujet_du_mail = $this->retourne_sujet_du_mail_pour_relecture();

		$variables_pour_la_vue = array(

			'email' => moi()->email,
			'url' => $url,
			'type_element' => $formulaire['type_element'],
            'langue' => modele('utilisateur')->where('email', $formulaire['employe'])->value('langue'),
		);

        // on prépare les données pour envoyer le mail
        $service_email = service('email');

        $parametres_email = [
            'type_configuration' => 1,
            'destinataire' => [$formulaire['employe']],
            'sujet' => $sujet_du_mail,
            'cc' => [moi()->email],
            'pieces_jointes' => [storage_path('app/'.$url_du_pdf)],
        ];

        return $service_email->envoyer('eden::mails.envoi_email_relecture', $variables_pour_la_vue, $parametres_email);
	}

	/**
	 *
	 *
	 * Envoie emails de relance
	 *
	 */
	public function envoyer_email_de_relance($utilisateurs) {

        $utilisateurs_par_langue = array();

        foreach($utilisateurs as $langue => $email_utilisateur)
            $utilisateurs_par_langue[$langue][] = $email_utilisateur;

        // on prépare les données pour envoyer le mail
        $service_email = service('email');

        foreach($utilisateurs_par_langue as $langue => $emails_utilisateurs){

            $parametres_email = [
                'type_configuration' => 1,
                'destinataire' => $emails_utilisateurs,
                'sujet' => 'Relance pour le document : '.str_replace('_vente', '', $this->_type_element).' - '. $this->modele->reference_document,
            ];

            $variables_email = [
                'management' => $this,
                'langue' => $langue
            ];

            $retour = $service_email->envoyer('eden::mails.envoi_email_relance', $variables_email, $parametres_email);

            // Si on n'arrive pas à envoyer le premier mail, on ne pourra pas envoyer les autres car c'est la même config email
            // Autant renvoyer l'erreur dès le premier essai s'il y en a
            if($retour !== true)
                return $retour;
        }

		return true;
	}

	/**
	 *
	 *
	 * On vérifie les informations  de la factures
	 *
	 */
	public function post_verification_document() {

		// On vérifie que les contacts associés au document appartiennent bien au client relié au document.
		$contacts = \DB::table($this->_type_element.'_contacts_ids')->where('cle_locale', $this->modele->id)->get();

		foreach($contacts as $lien_contact) {
			$contact = modele('contact', $lien_contact->valeur);

			// Client ID si c'est un document vente, fournisseur ID si c'est un document achat.
			if($this->est_une_vente())
				$champ = 'client_id';
			else
				$champ = 'fournisseur_id';

			// On supprime les contacts qui ne sont pas liés au même client.
			if($contact->$champ != $this->modele->$champ) {
				\DB::table($this->_type_element.'_contacts_ids')
					->where('id', $lien_contact->id)
					->delete();
			}
		}

		return array();
	}

	/**
	 *
	 * Retourne les badges à afficher sur la saisie d'un document (validé, réglé, etc...)
	 *
	 */
	public function badge_dans_titre_sur_saisie_document($badges = array()) {

        // on est en création
        if($this->existe() === false)
            return '';

        $les_tags = $this->tags_pour_liste($this->modele);

        $tableau_des_tags = explode('<br/>', $les_tags);

        $badges = array_merge($badges, $tableau_des_tags);

		// cas particulier pour les documents qui se règlent

		if(in_array($this->_type_element, array('facture_achat', 'facture_vente', 'avoir_achat', 'avoir_vente', 'acompte_vente', 'acompte_achat'))) {

			if(!empty($this->modele->valide)) {
                if($this->modele->regle != 1)
                    $badges[] = '<span class="badge badge-danger">'.traduction('interface.document.badge_titre_saisie_document.solde_a_regler').' {{ document.solde_document_ttc | montant }} </span>';
			}

			return implode(' ', $badges);
		}

		return implode(' ', $badges);
	}

	/**
	 *
	 *
	 * Permet d'afficher différentes actions supplémentaires
	 *
	 */
	public function actions_supplementaires_sur_saisie_document($actions = array()) {

        return implode(' ', $actions);
	}

	/**
	 *
	 * Vérifie et calcule le coupon réduction
	 *
	 */
	public function calcule_coupon_reduction($coupon_reduction, $date_facture, $client_id, $total_ttc, $articles) {

		$date_debut_coupon_reduction = strtotime($coupon_reduction->debut_validite);
		$date_fin_coupon_reduction = strtotime($coupon_reduction->fin_validite);

		// on vérifie les dates
		if (($date_facture < $date_debut_coupon_reduction) || ($date_facture > $date_fin_coupon_reduction))
			return ['succes' => false, 'message' => traduction('messages.php.document.coupon_expire')];

		// on vérifie si c'est un nouveau client
		if($coupon_reduction->nouveau_client_seulement == 1) {

			$facture_vente_par_client = modele('facture_vente')->where('client_id', $client_id)->get();

			if($facture_vente_par_client->isNotEmpty())
				return ['succes' => false, 'message' => traduction('messages.php.document.coupon_valable_nouveaux_clients')];
		}

		// on vérifie si c'est un coupon nominatif
		if($coupon_reduction->client_id !== null && $coupon_reduction->nominatif == 1) {

			if($coupon_reduction->client_id != $client_id)
				return ['succes' => false, 'message' => traduction('messages.php.document.coupon_invalide_client')];
		}

		$somme_total_pour_famille = $total_ttc;

		//on vérifie le minimum commande
		if($coupon_reduction->minimum_de_commande !== null) {

			$tableau_liste_famille = json_decode($coupon_reduction->liste_familles_coupon, true);

			if(!empty($tableau_liste_famille)){

				$somme_total_pour_famille = 0;
				foreach($articles as $article) {

					$famille_id = modele('article', $article['article_id'])->famille_id;

					if(in_array($famille_id, $tableau_liste_famille)) {

						if(!isset($article['remise']))
							$article['remise'] = 0;

						$tarif = (floatval($article['tarif']) * floatval($article['quantite'])) * (100 + $article['tva']) / 100 * (100 - $article['remise']) / 100;
						$somme_total_pour_famille += $tarif;
					}
				}

				if($somme_total_pour_famille < $coupon_reduction->minimum_de_commande)
					return ['succes' => false, 'message' => traduction('messages.php.document.erreur_minimun_montant_beneficier_coupon',null,[$coupon_reduction->minimum_de_commande. " " . maquette('devise_application_symbole')])];
			}
			else {

				if($total_ttc < $coupon_reduction->minimum_de_commande)
					return ['succes' => false, 'message' => traduction('messages.php.document.erreur_minimun_montant_beneficier_coupon',null,[$coupon_reduction->minimum_de_commande. " " . maquette('devise_application_symbole')])];
			}
		}

		// ok, maintenant on calcule le montant...
		$valeur = $coupon_reduction->valeur;

		// réduction en devises
		if($coupon_reduction->type_de_reduction === 1)
			$valeur = round($coupon_reduction->valeur / $somme_total_pour_famille * 100, 2);
		elseif($coupon_reduction->type_de_reduction === 2)
			$valeur = round($coupon_reduction->valeur, 2);
		
		return [
			'succes' => true, 
			'message' => traduction('messages.php.document.coupon_applique', null, [
				round($coupon_reduction->valeur, 2) . ($coupon_reduction->type_de_reduction === 1 ? maquette('devise_application_symbole') : '%')
			]),
			'id_coupon' => $coupon_reduction->id, 
			'coupon_reduction' => $coupon_reduction, 
			'valeur' => $valeur
		];

	}

	/**
	 *
	 *
	 * Calcule le montant de la valeur d'un coupon réduction pour une commande
	 * Utile uniquement pour le rapport analyse coupon réduction
	 *
	 */
	public function calcule_coupon_reduction_pour_montant_coupon($coupon_reduction) {

		$articles = $this->articles();
		$total_ttc = $this->modele->montant_document_ttc;

		$tableau_liste_famille = json_decode($coupon_reduction->liste_familles_coupon, true);

		$somme_total_pour_famille = $total_ttc;

		if(!empty($tableau_liste_famille)){

			$somme_total_pour_famille = 0;
			foreach($articles as $article) {

				$famille_id = modele('article', $article['article_id'])->famille_id;

				if(in_array($famille_id, $tableau_liste_famille)) {

					$tarif = (floatval($article['tarif']) * floatval($article['quantite'])) * (100 + $article['tva']) / 100 * (100 - $article['remise']) / 100;
					$somme_total_pour_famille += $tarif;
				}
			}
		}


		if($somme_total_pour_famille != 0) {

			// ok, maintenant on calcule le montant...
			$valeur = $coupon_reduction->valeur;

			if($coupon_reduction->type_de_reduction == 1) {

				$valeur = round($coupon_reduction->valeur / $somme_total_pour_famille * 100, 2);
			}


			$montant_coupon_reduction_en_pourcentage = $valeur / 100;
			$montant_reduction_coupon_reduction = round($this->modele->montant_document_ht *  $montant_coupon_reduction_en_pourcentage, 2);

			$this->enregistre_modele(['montant_reduction_coupon_reduction' => $montant_reduction_coupon_reduction]);
		}

		return true;
	}


	public function calcule_la_remise_document($montant_de_la_facture, $type_de_reduction, $remise) {

		$nombre_de_chiffres_decimaux_sur_les_tarif = fonctionnalite('nombre_de_chiffres_decimaux_sur_les_tarif');

		$remise_en_pourcentage = false;

		if($type_de_reduction == 2)
			$remise_en_pourcentage = true;


		if($remise_en_pourcentage) {

			$remise_en_euro = round($montant_de_la_facture * ($remise / 100),$nombre_de_chiffres_decimaux_sur_les_tarif);
			$remise_en_pourcentage = $remise;
		}
		else {

			$montant_avec_remise = $montant_de_la_facture - $remise;
			$remise_en_pourcentage = round((1 - ($montant_avec_remise / $montant_de_la_facture)) * 100,$nombre_de_chiffres_decimaux_sur_les_tarif);
			$remise_en_euro = $remise;

		}

		return ['remise_en_euro' => $remise_en_euro, 'remise_en_pourcentage' => $remise_en_pourcentage];

	}


	/**
	 *
	 *
	 * Enregistre log lors d'une demande de relecture
	 *
	 */
	public function enregistre_log_demande_de_relecture($donnees = array()) {

		return $this->enregistrer_log(Variables::$types_logs['demande_de_relecture']);
	}

	/**
	 *
	 * Regarde s'il est nécessaire de notifier des utilisateurs pour des modifications sur des éléments
	 *
	 */
	protected function notifications_elements_parents($modele, $modele_avant, $modifications) {

        //On ne crée pas de notifs quand un document est créé depuis l'extranet (ex: aana avec les commandes_vente)
        if(!empty(moi_extranet()))
            return;

		if(fonctionnalite('notifications') !== true)
			return;

		// on récupère les utilisateurs abonnés
		if(strpos($this->_type_element, 'vente') !== false) {

			$utilisateurs = modele('notification_element')->where('type_element', 'client')->where('element_id', $this->modele->client_id)->get();
			$tiers = management('client', $this->modele->client_id)->affiche_lien();
		}
		else {

			$utilisateurs = modele('notification_element')->where('type_element', 'fournisseur')->where('element_id', $this->modele->fournisseur_id)->get();
			$tiers = management('fournisseur', $this->modele->fournisseur_id)->affiche_lien();
		}

		foreach($utilisateurs as $utilisateur) {

			$notification = management('notification');

			$info = array(

				'date' => date('Y-m-d H:i:s'),
				'utilisateur_id' => $utilisateur->utilisateur_id,
				'zone' => 'navbar_notifications',
				'contenu_html' => 'Le '.date('d/m').' à '.date('H:i').' '.moi()->prenom.' '.strtoupper(substr(moi()->nom,0,1)).'. a créé ou modifié un document pour '.$tiers.' : '.$this->affiche_lien(),
			);

			$notification->enregistre($info);
		}
	}

	/**
	 *
	 * Récupère les contacts du client
	 *
	 */
	public function contacts_du_client() {

		$contacts = array();

		if(!empty($this->modele) && !empty($this->modele->client_id)) {

            $contacts = management('contact')->recuperer_contact_element('client', $this->modele->client_id)->toArray();
		}

		return $contacts;
	}

	/**
	 *
	 * Ajoute un article à un tableau pour préparer les articles à commander chez un fournisseur
	 *
	 */
	protected function ajoute_article_au_tableau_des_articles_a_commander_chez_un_fournisseur(&$articles_a_afficher, $ligne, $id_ligne_source, $coefficient_nomenclature = 1, $regroupement_id = null) {

		if(is_array($ligne)) {

			if(isset($ligne['conditionnement']))
				$conditionnement = $ligne['conditionnement'];
			else
				$conditionnement = null;

			if(isset($ligne['nomenclature']))
				$nomenclature_ligne = $ligne['nomenclature'];
			else
				$nomenclature_ligne = null;

			$quantite = $ligne['quantite'];
			$designation = $ligne['designation'];

            if(isset($ligne['prix_achat']))
                $prix_achat = $ligne['prix_achat'];
            else
                $prix_achat = null;

			if(isset($ligne['article_id']))
				$article_id = $ligne['article_id'];
			elseif(isset($ligne['article_enfant_id']))
                $article_id = $ligne['article_enfant_id'];
            else
                $article_id = null;
		}
		else {

			$conditionnement = $ligne->conditionnement;
			$quantite = $ligne->quantite;
			$article_id = $ligne->article_id;
            $prix_achat = $ligne->prix_achat;
			$nomenclature_ligne = $ligne->nomenclature;
			$designation = $ligne->designation;
		}

		if(empty($article_id))
			return;

		if(!empty($nomenclature_ligne)) {

			if(!is_array($nomenclature_ligne))
				$nomenclature = json_decode($nomenclature_ligne, true);
			else
                $nomenclature = $nomenclature_ligne;

			if(!empty($nomenclature)) {

                $articles_erp = $this->articles_erp()->keyBy('id')->toArray();

				foreach($nomenclature as $ligne_de_la_nomenclature)  {

                    $modele = null;

                    if(isset($ligne_de_la_nomenclature['modele']))
                        $modele = $ligne_de_la_nomenclature['modele'];

                    else if(!empty($ligne_de_la_nomenclature['article_id']) && !empty($articles_erp[$ligne_de_la_nomenclature['article_id']]))
                        $modele = $articles_erp[$ligne_de_la_nomenclature['article_id']];

                    if(empty($modele) || (empty($modele['stockable']) && $modele['type_article'] != 1) || !empty($modele['ne_pas_reapprovisionner']))
                        continue;

					$this->ajoute_article_au_tableau_des_articles_a_commander_chez_un_fournisseur($articles_a_afficher, $ligne_de_la_nomenclature, $id_ligne_source, $quantite * $coefficient_nomenclature, $regroupement_id);
				}

                return;

			}
		}

		$quantite_conditionnee = $quantite;

		if(!empty($conditionnement)) {

			$conditionnement = modele('conditionnement', $conditionnement);

			if(!empty($conditionnement) && !empty($conditionnement->id)) {

				$quantite_conditionnee = $quantite * $conditionnement->quantite;
			}
		}

		$articles_a_afficher[] = array(
			// 'article' => modele('article', $article_id),
			'article_id' => $article_id,
			'quantite' => $quantite_conditionnee * $coefficient_nomenclature,
			'quantite_conditionnee' => $quantite_conditionnee * $coefficient_nomenclature,
			'type_element_source' => $this->_type_element,
			'id_element_source' => $this->modele->id,
			'id_ligne_source' => $id_ligne_source,
			'regroupement_id' => $regroupement_id,
            'designation' => $designation,
            'prix_achat' => $prix_achat,
		);

	}

	/**
	 *
	 * Retourne tous les fournisseurs dispo pour les articles du document
	 *
	 */
	public function fournisseurs_par_article() {

		if(empty($this->modele) || empty($this->modele->valide) || !in_array($this->_type_element, array('commande_vente', 'devis_vente')))
			return array(
                'fournisseurs_par_article' => [],
                'regroupements' => [],
            );

		temps_execution('fournisseurs_par_article::debut', 3);

		$articles = $this->articles(array(), false, false);

        $lignes_divers = $this->lignes_divers_document();

		temps_execution('fournisseurs_par_article::recuperation lignes', 3);

		$articles_a_afficher = [];

        $regroupement_retour = [];

		foreach ($lignes_divers as $ligne_divers) {

            if ($ligne_divers->type == "regroupement")
                $regroupement_retour[] = $ligne_divers;

            if ($ligne_divers->ligne != 0)
                continue;

            if ($ligne_divers->type == "regroupement")
                $regroupement_id = $ligne_divers->id;

        }

        foreach ($articles as $ligne) {

			if (empty($ligne->modele))
                continue;

            // On affiche uniquement les articles qui doivent être reapprovisionnés
            if (!empty($ligne->modele->ne_pas_reapprovisionner))
                continue;

            // On affiche uniquement les articles qui sont stockables
            if (empty($ligne->modele->stockable) && $ligne->modele->type_article != 1)
                continue;


            if(!empty($regroupement_id))
                $this->ajoute_article_au_tableau_des_articles_a_commander_chez_un_fournisseur($articles_a_afficher, $ligne, $ligne->id,1, $regroupement_id);
            else
                $this->ajoute_article_au_tableau_des_articles_a_commander_chez_un_fournisseur($articles_a_afficher, $ligne, $ligne->id);

            foreach ($lignes_divers as $ligne_divers) {

                if ($ligne_divers->ligne != $ligne->ligne)
                    continue;

                if($ligne_divers->type == "regroupement")
                    $regroupement_id = $ligne_divers->id;

                if($ligne_divers->type == "regroupement_fermeture" && isset($regroupement_id))
                    unset($regroupement_id);

            }
        }

		// on va chercher les modèles des articles
		$ids_articles = collect($articles_a_afficher)->pluck('article_id')->toArray();

		$modeles = modele('article')->avec_inactifs()->whereIn('id', $ids_articles)->get()->keyBy('id');

		foreach($articles_a_afficher as $id => $article) {

            if(!empty($modeles[$article['article_id']]))
                $articles_a_afficher[$id]['article'] = $modeles[$article['article_id']];
		}

		temps_execution('fournisseurs_par_article::après première boucle', 3);

		$stocks_par_article = modele('stocks')
			->select(DB::raw('SUM(stock_actuel) as stock_actuel'))
			->whereIn('article_id', collect($articles_a_afficher)->pluck('article_id')->toArray())
			->groupBy('article_id')->get()->pluck('stock_actuel','article_id');

		$articles_fournisseurs_par_article = modele('article_fournisseur')
										->whereIn('article_id', collect($articles_a_afficher)->pluck('article_id')->toArray())
										->orderBy('fournisseur_prioritaire', 'desc')
										->get()->groupBy('article_id');

		foreach($articles_a_afficher as $id => $infos_article) {

            if(empty($infos_article['article']))
                continue;

			$article = $infos_article['article'];
			$quantite = $infos_article['quantite'];

			$articles_a_afficher[$id]['quantite'] = $quantite;

			$management_article = management('article', $article->id, $article);

			// on récupère l'unité
			$unite = $management_article->champ('unite')->affiche();
			$articles_a_afficher[$id]['unite'] = $unite;

			// on récupère le stock actuel
			$articles_a_afficher[$id]['stock_actuel'] = $stocks_par_article[$article->id] ?? 0;

			// on récupère les tarifs par fournisseur
			$tarifs_par_fournisseurs = array();

			$articles_fournisseurs = $articles_fournisseurs_par_article[$article->id] ?? [];

			foreach($articles_fournisseurs as $article_fournisseur) {

				if(!isset($tarifs_par_fournisseurs[$article_fournisseur->fournisseur_id])) {

					$tarifs_par_fournisseurs[$article_fournisseur->fournisseur_id] = array(

						'fournisseur' => management('fournisseur', $article_fournisseur->fournisseur_id)->affiche(),
						'fournisseur_id' => $article_fournisseur->fournisseur_id,
						'articles' => array(),
						'commandes' => array(),
						'conditionnement_selectionne' => false,
					);
				}

                $management_conditionnement = management('conditionnement', $article_fournisseur->conditionnement_id);
                $conditionnement = $management_conditionnement->modele;

				$tarif = $article_fournisseur->tarif. maquette('devise_application_symbole');

				if(empty($article_fournisseur->tarif))
					$tarif = 'tarif non renseigné';

				$infos_fournisseur = array(

					'id_article_fournisseur' => $article_fournisseur->id,
					'reference' => $article_fournisseur->reference,
					'conditionnement' => $conditionnement,
					'conditionnement_texte' => null,
					'tarif' => $article_fournisseur->tarif,
					'tarif_texte' => $article_fournisseur->tarif. maquette('devise_application_symbole'),
					'prioritaire' => $article_fournisseur->fournisseur_prioritaire,
					'affichage' => $management_conditionnement->champ('nom')->affiche($conditionnement->nom).' ('.$management_conditionnement->champ('quantite')->affiche($conditionnement->quantite).' '.$unite.', '.$management_conditionnement->champ('tarif')->affiche($tarif).')',
					'affichage_unite' => $management_conditionnement->champ('nom')->affiche($conditionnement->nom),
				);

				if($article_fournisseur->fournisseur_prioritaire == 1) {

					$infos_fournisseur['affichage'] = $management_conditionnement->champ('nom')->affiche($conditionnement->nom).' ('.$management_conditionnement->champ('quantite')->affiche($conditionnement->quantite).' '.$unite.', '.$management_conditionnement->champ('tarif')->affiche($tarif).', fournisseur prioritaire)';
				}

				if(empty($conditionnement) || empty($article_fournisseur->conditionnement_id)) {

					if($article_fournisseur->fournisseur_prioritaire == 1) {

						$infos_fournisseur['affichage'] = "A l'unité (".$management_conditionnement->champ('tarif')->affiche($tarif).', fournisseur prioritaire)';
					}
					else {

						$infos_fournisseur['affichage'] = "A l'unité (".$management_conditionnement->champ('tarif')->affiche($tarif).')';
					}
				}

				if(!empty($article_fournisseur->conditionnement))
					$infos_fournisseur['tarif_texte'] .= ', conditionné par '.$article_fournisseur->conditionnement;

				$tarifs_par_fournisseurs[$article_fournisseur->fournisseur_id]['articles'][] = $infos_fournisseur;

			}

			// on va ajouter les commandes existantes
            $articles_a_afficher[$id] = $this->ajout_commandes_existantes($tarifs_par_fournisseurs, $articles_a_afficher[$id]);

		}

		temps_execution('fournisseurs_par_article::après 2eme boucle', 3);

		return array(
            'fournisseurs_par_article' => $articles_a_afficher,
            'regroupements' => $regroupement_retour,
        );
	}

	/**
	 *
	 * Retourne les ids des articles du document dont la fiche article a été supprimée définitivement
	 *
	 */
	public function articles_supprimes() {

		if(empty($this->modele) || empty($this->modele->id))
			return array();

		$articles_du_document = modele($this->_type_element . '_lignes')
			->where('document_id', $this->modele->id)
			->where('article_id', '>', 0)
			->distinct()
			->pluck('article_id');

		if($articles_du_document->isEmpty())
			return array();

		$articles_existants = modele('article')
			->sans_profils()
			->avec_inactifs()
			->whereIn('id', $articles_du_document)
			->pluck('id');

		return $articles_du_document->diff($articles_existants)->values()->all();
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

			$tags[] = '<span class="badge badge-success">'.traduction('interface.listes.tags_pour_liste.valide').'</span>';

		}

		return implode('<br/>', $tags);
	}

	/**
	 *
	 * Retourne l'adresse de livraison
	 *
	 */
	public function retourne_adresse_de_livraison() {

		// Si une adresse texte existe, on l'a renvoi en premier
		if ( !empty($this->modele->adresse_de_livraison_texte) )
			return nl2br($this->modele->adresse_de_livraison_texte);

		// Sinon, si on a une adresse via la table, on génère la chaine et on la renvoi
		if ( !empty($this->modele->adresse_de_livraison) ) {
			$adresse = modele('adresse', $this->modele->adresse_de_livraison);

			return $adresse->societe.'<br>'.
					( !empty($adresse->nom) || !empty($adresse->prenom) ? $adresse->prenom.' '.$adresse->nom.'<br>' : '' ).
					$adresse->adresse.'<br>'.

					(!empty($adresse->adresse_complement) ? $adresse->adresse_complement.'<br>' : '').

					$adresse->code_postal.' '.$adresse->ville;

		}

		// Si on a rien trouvé, on renvoi rien.
		return '';
	}


	/**
	 *
	 * Retourne l'adresse de facturation
	 *
	 */
	public function retourne_adresse_de_facturation() {

		// Si une adresse texte existe, on l'a renvoi en premier
		if ( !empty($this->modele->adresse_de_facturation_texte) )
			return nl2br($this->modele->adresse_de_facturation_texte);

		// Sinon, si on a une adresse via la table, on génère la chaine et on la renvoi
		if ( !empty($this->modele->adresse_de_facturation) ) {
			$adresse = modele('adresse', $this->modele->adresse_de_facturation);

			return $adresse->societe.'<br>'.
					( !empty($adresse->nom) || !empty($adresse->prenom) ? $adresse->prenom.' '.$adresse->nom.'<br>' : '' ).
					$adresse->adresse.'<br>'.

					(!empty($adresse->adresse_complement) ? $adresse->adresse_complement.'<br>' : '').

					$adresse->code_postal.' '.$adresse->ville;

		}

		// Si on a rien trouvé, on renvoi rien.
		return '';
	}

	/**
	 *
	 * Retourne true si il faut afficher les deux adresses
	 *
	 */
	public function adresse_de_livraison_differente_adresse_de_facturation() {

		// Si une des adresse est vide, on retourne false.
		// C'est à dire que seule une des adresses a été remplie, inutile d'afficher les deux.
		// Edit : pose plus de problème qu'elle n'en résoud, si seule l'adresse de livraison est remplie et qu'on affiche d'abord l'adresse de facturation
		/*
		if ($this->retourne_adresse_de_livraison() == '' || $this->retourne_adresse_de_facturation() == '')
			return false;
			*/

		// Sinon, on retourne true si elles sont différentes, false si elles sont identiques
		return ($this->retourne_adresse_de_livraison() != $this->retourne_adresse_de_facturation() );
	}

	/**
	 *
	 * Retourne les pièces jointes liées à un document
	 *
	 */
	public function pieces_jointes($filtre = false,$id_dossier_parent = null,$avec_documents_confidentiels = true) {

        if($id_dossier_parent==0){

            $id_dossier_parent=null;
        }

        if($filtre === false)
            $pieces_jointes =  Element_piece_jointe::where('type_element', $this->_type_element)->where('element_id', $this->modele->id)->orderBy('ordre')->get();
		else
            $pieces_jointes = Element_piece_jointe::where('type_element', $this->_type_element)->where('element_id', $this->modele->id)->where('lier_au_document', $filtre)->orderBy('ordre')->get();

        foreach($pieces_jointes as $id => $piece_jointe) {

        	if ($piece_jointe->dossier_parent != null && !$avec_documents_confidentiels) {

				$modele = modele('dossier_bibliotheque', $piece_jointe->dossier_parent);
				if ($modele->confidentiel == 1) {
					$pieces_jointes->forget($id);
					continue;
				}
			}

            $nom_fichier = $piece_jointe->chemin;

            $piece_jointe->url_sur_serveur = 'public/'.$nom_fichier;

            $infos_pj = pathinfo(asset('public/'.$nom_fichier));

            if(!isset($infos_pj['extension']))
                $piece_jointe->extension = '???';
            else
                $piece_jointe->extension = $infos_pj['extension'];

            if (is_file(storage_path('app/public/'.$nom_fichier))) {

                $piece_jointe->poids = round(filesize(storage_path('app/public/'.$nom_fichier)) / 1000) .' Ko';
            } else {

                $piece_jointe->poids = 'fichier non présent';
            }

        }

        return $pieces_jointes;
	}

	public function pieces_jointes_a_joindre_pdf($filtre = false,$id_dossier_parent = null,$avec_documents_confidentiels = true) {

		return $this->pieces_jointes($filtre);
	}

	/**
	 *
	 * Retourne true si un champ est modifiable (en fonction du statut du document : validé, comptabilisé, etc & du paramétrage du champ (modification_post_validation))
	 *
	 */
	public function champ_modifiable($nom_sql) {

		// le document est modifiable : OK
		if($this->document_modifiable() === true)
			return true;

		// sinon il faut que le champ soit modifiable
		if(!empty(champ_libre_modele($this->_type_element, $nom_sql)->modification_post_validation))
			return true;

		// dans les autres cas, non modifiable
		return false;
	}

	/**
	 *
	 * Retourne true si un document est modifiable (en fonction du statut du document : validé, comptabilisé, etc)
	 *
	 */
	public function document_modifiable() {

		// le document n'existe pas : modifiable
		if($this->existe() === false)
			return true;

		// document non validé : modifiable
		if(empty($this->modele->valide))
			return true;

        if($this->est_un_achat() && empty($this->modele->comptabilise) && fonctionnalite('modification_document_valide')[$this->_type_element])
            return true;

        if($this->est_une_vente() && fonctionnalite('modification_document_valide')[$this->_type_element])
            return true;

		// dans les autres cas, le document n'est pas modifiable
		return false;
	}

	/**
	 *
	 *
	 * Retourne un boolean pour savoir si on peut modifier les informations du documents
	 *
	 */
	public function articles_modifiables() {

		return $this->document_modifiable();
	}

	/**
	 *
	 *
	 * Retourne un tableau de document pour l'export sepa
	 *
	 *
	 */
	public function genere_export_sepa($ids) {

		if(!is_dir(storage_path('app/export_sepa/')))
			mkdir(storage_path('app/export_sepa/'));

		// on récupère les factures à envoyer pour l'export SEPA
		$documents_a_traiter = $this->recupere_documents_pour_export_sepa($ids);

		// on génère les fichiers de l'export SEPA
		$documents_a_exporter = $this->retourne_fichiers_pour_export_sepa($documents_a_traiter);

		return $documents_a_exporter;
	}

	/**
	 *
	 *
	 * Créer et retourne les fichiers pour l'export sepa
	 *
	 */
	public function retourne_fichiers_pour_export_sepa($documents) {

		// Spécifique à Mtarget, surcharger la méthode en spécifique ?
		$comptes_bancaires = modele('compte_bancaire')->get();

		// on récupère le compte bancaire paramétré pour l'export SEPA
		$compte_bancaire_sepa = modele('compte_bancaire')->where('utilise_export_sepa', 1)->first();

		if($compte_bancaire_sepa === null || empty($compte_bancaire_sepa->iban) || empty($compte_bancaire_sepa->bic)) {

			return traduction('messages.php.document.compte_bancaire_sepa_non_parametre');
		}

		$donnees_csv = '';
		$donnees_csv .= 'nom du client;code du client;montant;date;recur;compte comptable client;reference_facture'."\n";
		$nom_fichier_csv = 'sepa_'.time().'.csv';

		$documents_a_retourner = [];

		foreach($documents as $recurrence => $documents_tries_par_reccurence) {

			$first_ou_recur = 'FRST';
			if($recurrence == 1)
				$first_ou_recur = 'RCUR';

			foreach($documents_tries_par_reccurence as $date => $documents_tries_par_date) {

				foreach($documents_tries_par_date as $compte_bancaire_id => $documents_tries_par_compte_bancaire_id) {

					$compte_bancaire = $comptes_bancaires->where('id', $compte_bancaire_id)->first();

					if($compte_bancaire === null)
						continue;

					$cle_unique = uniqid();

					$nom_fichier_xml = 'export_'.$first_ou_recur.'_'.str_replace('-', '', $date).'_'.$compte_bancaire->nom.'_'.$cle_unique.'.xml';
					$nom_ficher_txt = 'export_compta_'.$first_ou_recur.'_'.str_replace('-', '', $date).'_'.$compte_bancaire->nom.'_'.$cle_unique.'.txt';

					$donnees_txt = $this->genere_export_txt($documents_tries_par_compte_bancaire_id, $date);
					$donnees_xml = $this->genere_export_sepa_xml($documents_tries_par_compte_bancaire_id, $date, $first_ou_recur, $compte_bancaire_sepa);

					file_put_contents(storage_path('app/export_sepa/'.$nom_fichier_xml), $donnees_xml);
					file_put_contents(storage_path('app/export_sepa/'.$nom_ficher_txt), $donnees_txt);

					$documents_a_retourner[] = storage_path('app/export_sepa/'.$nom_fichier_xml);
					$documents_a_retourner[] = storage_path('app/export_sepa/'.$nom_ficher_txt);

					foreach($documents_tries_par_compte_bancaire_id as $document) {

						$avec_reccurence = 'non';
						if($recurrence == 1)
							$avec_reccurence = 'oui';

						$donnees_csv .= $document->denomination.";".$document->code_client.";".$document->montant.";".$document->date_de_la_facture.";".$avec_reccurence.";-;".$document->reference_document."\n";
					}
				}
			}
		}

		file_put_contents(storage_path('app/export_sepa/'.$nom_fichier_csv), $donnees_csv);

		$documents_a_retourner[] = storage_path('app/export_sepa/'.$nom_fichier_csv);

		return $documents_a_retourner;
	}

	/**
	 *
	 *
	 * Retourne un xml pour l'export sepa
	 *
	 */
	public function genere_export_sepa_xml($documents, $date, $first_ou_recur, $compte_bancaire_sepa) {

		// @todo, à placer dans management spécifique

		$nombre_de_documents = $documents->count();

		$total_documents = 0;
		foreach($documents as $document) {
			$total_documents += round($document->montant,2);
		}

		$iban_creancier = htmlspecialchars(str_replace(' ', '', $compte_bancaire_sepa->iban), ENT_XML1 | ENT_QUOTES, 'UTF-8');
		$bic_creancier = htmlspecialchars(str_replace(' ', '', $compte_bancaire_sepa->bic), ENT_XML1 | ENT_QUOTES, 'UTF-8');

		$data_xml =
		"<?xml version=\"1.0\" encoding=\"utf-8\"?>
		<Document xmlns=\"urn:iso:std:iso:20022:tech:xsd:pain.008.001.02\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"urn:iso:std:iso:20022:tech:xsd:pain.008.001.02 pain.008.001.02.xsd\">
		<CstmrDrctDbtInitn>
		<GrpHdr>
		<MsgId>EDENPME/4997/".time()."</MsgId>
		<CreDtTm>".date('Y-m-d').'T'.date('H:i:s')."</CreDtTm>
		<NbOfTxs>".$nombre_de_documents."</NbOfTxs>
		<CtrlSum>".number_format($total_documents, 2, '.', '')."</CtrlSum>
		<InitgPty>
		<Nm>M TARGET</Nm>
		</InitgPty>
		</GrpHdr>\n
		<PmtInf>
		<PmtInfId>MTARGET/".microtime()."</PmtInfId>
		<PmtMtd>DD</PmtMtd>
		<NbOfTxs>".$nombre_de_documents."</NbOfTxs>
		<CtrlSum>".number_format($total_documents, 2, '.', '')."</CtrlSum>
		<PmtTpInf>
		<SvcLvl>
		<Cd>SEPA</Cd>
		</SvcLvl>
		<LclInstrm>
		<Cd>CORE</Cd>
		</LclInstrm>
		<SeqTp>".$first_ou_recur."</SeqTp>
		</PmtTpInf>
		<ReqdColltnDt>".$date."</ReqdColltnDt>
		<Cdtr>
		<Nm>M TARGET</Nm>
		</Cdtr>
		<CdtrAcct>
		<Id>
		<IBAN>".$iban_creancier."</IBAN>
		</Id>
		</CdtrAcct>
		<CdtrAgt>
		<FinInstnId>
		<BIC>".$bic_creancier."</BIC>
		</FinInstnId>
		</CdtrAgt>
		<ChrgBr>SLEV</ChrgBr>
		<CdtrSchmeId>
		<Id>
		<PrvtId>
		<Othr>
		<Id>FR15ZZZ627621</Id>
		<SchmeNm>
		<Prtry>SEPA</Prtry>
		</SchmeNm>
		</Othr>
		</PrvtId>
		</Id>
		</CdtrSchmeId>
		";

		foreach($documents as $document) {

			$data_xml .= "<DrctDbtTxInf>
				<PmtId>
				<EndToEndId>Abonnement M TARGET</EndToEndId>
				</PmtId>
				<InstdAmt Ccy=\"EUR\">".number_format($document->montant, 2, '.', '')."</InstdAmt>
				<DrctDbtTx>
				<MndtRltdInf>
				<MndtId>".$document->numero_du_mandat."</MndtId>
				<DtOfSgntr>".$document->date_signature_du_mandat."</DtOfSgntr>
				</MndtRltdInf>
				</DrctDbtTx>
				<DbtrAgt>
				<FinInstnId>
				<BIC>".$document->bic."</BIC>
				</FinInstnId>
				</DbtrAgt>
				<Dbtr>
				<Nm>".htmlspecialchars($document->denomination, ENT_XML1 | ENT_QUOTES, 'UTF-8')."</Nm>
				</Dbtr>
				<DbtrAcct>
				<Id>
				<IBAN>".$document->iban."</IBAN>
				</Id>
				</DbtrAcct>
				</DrctDbtTxInf>
				";
		}

		$data_xml .= "</PmtInf>
		</CstmrDrctDbtInitn>
		</Document>";

		return $data_xml;
	}

	/**
	 *
	 *
	 * Retourne du texte pour l'export sepa
	 *
	 */
	public function genere_export_txt($documents, $date) {

		// @todo, à placer dans management spécifique

		$montant_fichier = 0;
		$donnees_txt = '';

		foreach($documents as $document) {

			$document_management = management('facture_vente', $document->facture_vente_id);

			$lignes = $document_management->articles();

			$totaux = $document_management->calcule_total_document($lignes);

			$montant_tiers = array();

			foreach($lignes as $index_ligne => $ligne) {

				$montant_ttc = $totaux['par_ligne_ttc'][$index_ligne];

				$compte_tiers = $document_management->compta_compte_tiers($ligne);

				// @tod gérer la gestion des retours d'erreurs
				if(is_array($compte_tiers) && !empty($compte_tiers['erreur'])) {

					return $compte_tiers['erreur'];
				}

				if($compte_tiers === false) {

					return traduction('messages.php.document.compte_tiers_impossible_ecriture');
				}

				if(!isset($montant_tiers[$compte_tiers]))
					$montant_tiers[$compte_tiers] = 0;

				$montant_tiers[$compte_tiers] += $montant_ttc;
			}

			foreach($montant_tiers as $id_compte => $montant) {

				// date
				$donnees_txt .= date('d/m/Y', strtotime($document->date_de_la_facture)).';';

				$client_management = management('client', $document->client_id);

				// compte : le compte comptable + le compte auxiliaire
				$donnees_txt .= modele('compte_comptable', $id_compte)->numero_de_compte.$client_management->compte_auxiliaire().';';

				$donnees_txt .= ';';

				// numéro document(3)
				$donnees_txt .= 'E-PRLV SEPA '.$document->reference_document.';';

				// Libellé 1 (4)
				// $donnees_txt .= ';';

				// débit  (5) &	crédit (6)
				$donnees_txt .= '0;'.round($montant,2).';';

				// abrégé journal (7)
				$donnees_txt .= 'BC;';

				// Numéro pièce (8)
				$donnees_txt .= ';';

				// date d'échéance
				$donnees_txt .= date('d/m/Y', strtotime($document->date_de_la_facture));

				$donnees_txt .= ';';
				$donnees_txt .= '1';

				// retour chariot
				$donnees_txt .= "\r\n";

				$montant_fichier += round($montant,2);

			}


		}







		$donnees_txt .= date('d/m/Y', strtotime($date)).';';

		$donnees_txt .= '4710000;';
		$donnees_txt .= ';';

		// compte
		$donnees_txt .= 'E-PRLV SEPA;';

		// numéro document(3)
		// $donnees_txt .= ';';

		$donnees_txt .= round($montant_fichier,2).';';

		// Libellé 1 (4)
		$donnees_txt .= '0;';

		// débit  (5) &	crédit (6)

		// abrégé journal (7)
		$donnees_txt .= 'BC;';

		// Numéro pièce (8)
		$donnees_txt .= ';';

		// date d'échéance
		$donnees_txt .= date('d/m/Y', strtotime($date));

		$donnees_txt .= ';';

		$donnees_txt .= '1';

		// retour chariot
		$donnees_txt .= "\r\n";

		return $donnees_txt;
	}

	/**
	 *
	 *
	 * Retourne les documents qu'on doit traiter pour l'export sepa
	 *
	 */
	public function recupere_documents_pour_export_sepa($ids) {

		// au cas où
		\DB::select("update client set recur=0 where recur is null");
		\DB::select("update client set recur=0 where recur=2");

		// Spécifique à Mtarget, surcharger la méthode en spécifique ?
		return modele($this->_type_element)
				->join('client', 'client.id', '=', 'facture_vente.client_id')
				->whereIn('facture_vente.id', $ids)
				->where('facture_vente.valide', 1)
				->where('facture_vente.montant_document_ht', '>' , 0)
				->select('facture_vente.date_de_reglement as date_de_la_facture',
				'facture_vente.id as facture_vente_id',
				'client.id as client_id',
				'client.compte_bancaire_id as cb',
				'client.recur as recurrence',
				'client.numero_du_mandat as numero_du_mandat',
				'client.date_signature_du_mandat as date_signature_du_mandat',
				'client.bic as bic',
				'client.iban as iban',
				'client.denomination as denomination',
				'facture_vente.montant_document_ttc as montant',
				'facture_vente.reference_document as reference_document',
				'client.code_client as code_client')
				->get()
				->groupBy(['recurrence', 'date_de_la_facture', 'cb']);
	}

	/**
	 *
	 *
	 * Retourne un tableau de document pour l'export relance PDF
	 *
	 *
	 */
	public function genere_export_relance_pdf($ids,$modele_a_utiliser) {

		$documents_a_traiter = $this->recupere_documents_pour_export_relance_pdf($ids);

		$documents_a_exporter = $this->retourne_fichiers_pour_export_relance_pdf($documents_a_traiter,$modele_a_utiliser);

		return $documents_a_exporter;
	}

	/**
	 *
	 *
	 * Créer et retourne les fichiers pour l'export relance PDF
	 *
	 */
	public function retourne_fichiers_pour_export_relance_pdf($documents,$modele_pdf) {

		$merger = \PDFMerger::init();

		// On génère un document par client
		foreach ($documents as $id_client => $factures_client) {

			$donnees_pour_pdf = array();

			$donnees_pour_pdf['factures'] = $factures_client;

			// On va chercher le client
			$donnees_pour_pdf['client'] = modele('client')->where('id',$id_client)->first();

			$donnees_pour_pdf['utilisateur'] = moi();

			// On récupère le modèle de PDF
			$donnees_pour_pdf['modele_pdf'] = modele('modele_de_document')->first();
			$donnees_pour_pdf['date_actuelle'] = date("d/m/Y");

			$pdf = PDF::loadView('eden::recouvrement.'.$modele_pdf, $donnees_pour_pdf);
			$pdf->setPaper('a4', 'portrait');
			$nom_du_ficher = "export_recouvrement_".$id_client."_".time().".pdf";

			$document = \Storage::put("public/recouvrement/pdf/$nom_du_ficher", $pdf->output());

			$documents_a_retourner[] = storage_path('app/public/recouvrement/pdf/'.$nom_du_ficher);

			$merger->addPDF(storage_path('app/public/recouvrement/pdf/'.$nom_du_ficher));
		}

		$merger->merge();
		$nom_merge = 'relances_'.time().'.pdf';
		$merger->save(storage_path('app/public/recouvrement/'.$nom_merge));

		return $nom_merge;
	}

	/**
	 *
	 *
	 * Retourne les documents qu'on doit traiter pour l'export relance_pdf
	 *
	 */
	public function recupere_documents_pour_export_relance_pdf($ids) {

		$documents_a_retourner = array();

		// On récupère toutes les factures ventes selectionnées et on les regroupe par clients
		foreach ($ids as $id_facture_vente) {

			$document = modele('facture_vente')->where('id',$id_facture_vente)->first();

			$documents_a_retourner[$document->client_id][] = $document;
		}

		return $documents_a_retourner;
	}

    /**
     *
     * Retourne les dossiers liées à un document et aux documents
     *
     */
    public function dossiers() {

        $id_element = $this->modele->id;

//        if(!empty(moi()->id_microsoft) && config('fonctionnalites_integrations.sharepoint_utiliser_synchronisation') === true) {
//
//            $sharepoint = service('microsoft_sharepoint');
//
//            $type_dossier = false;
//
//            if($this->est_une_vente())
//                $type_dossier = 'vente';
//            else if($this->est_un_achat())
//                $type_dossier = 'achat';
//
//            $id_dossier_element_sharepoint = $sharepoint->verifier_existence_dossiers_elements_fiche($this->_type_element, $id_element, $type_dossier);
//
//            $dossiers_sharepoint = $sharepoint->recuperer_contenu_dossier($id_dossier_element_sharepoint)[1];
//
//            // création automatique des dossiers communs
//            $dossiers = modele('dossier_bibliotheque')
//                ->where('dossier_parent','=',null)
//                ->where('type_element', $this->_type_element)
//                ->WhereNull('element_id')
//                ->get();
//
//            // On crée les dossiers si nécessaire
//            $dossier_cree = false;
//            foreach ($dossiers as $dossier) {
//
//                // On parcourt les dossiers pour vérifier si les dossiers existent déjà ou si il faut les créer
//                $present = false;
//                foreach ($dossiers_sharepoint as $dossier_sharepoint) {
//                    if($dossier_sharepoint->nom == $dossier->nom) {
//
//                        $present = true;
//                        continue;
//                    }
//                }
//
//                // Le dossier n'existe pas, on le crée
//                if(!$present) {
//
//                    $sharepoint->creer_dossier($dossier->nom, $id_dossier_element_sharepoint);
//                    $dossier_cree = true;
//                }
//            }
//
//            // Si on a créé des dossiers, on recharge la liste
//            if($dossier_cree)
//                $dossiers_sharepoint = $sharepoint->recuperer_contenu_dossier($id_dossier_element_sharepoint)[1];
//
//            return collect($dossiers_sharepoint);
//        }

        $dossiers = modele('dossier_bibliotheque')
            ->where('dossier_parent','=',null)
            ->where('type_element', $this->_type_element)
            ->where(function($r) use ($id_element) {
                $r->where('element_id', $id_element)->orWhereNull('element_id');
            })
            ->get();

        return $dossiers;
    }

	/**
	 *
	 * Vérifie si tous les articles du document stockables sont en stock
	 *
	 * @return true si c'est OK, sinon retourne un message d'erreur STRING
	 *
	 */
	public function verifie_si_les_articles_sont_en_stock() {

		$articles = $this->articles();

		foreach($articles as $article) {

			if(empty($article->modele->stockable))
				continue;

			// on doit gérer le cas des nomenclatures
			$modele_article = $article->modele;

			if($modele_article->type_article == 1) {

				$nomenclature = $article->nomenclature;

				foreach($nomenclature as $article_dans_nomenclature) {

					$management = management('article', $article_dans_nomenclature->article_id);

					$stocks = $management->stock_actuel();

					if($stocks < 0)
						return traduction('messages.php.document.validation_impossible_stock')." ".$management->affiche();
				}
			}
			else {

				$management = management('article', $article->article_id);

				$stocks = $management->stock_actuel();

				/*
				@note : a priori le calcul est faux car dans $stocks les quantité du document sont déjà prises en compte, du coup on les compte 2 fois...

				if($stocks - $article->quantite < 0)
					return "Il n'est pas possible de valider ce document, car il n'y a pas assez de stock pour l'article ".$management->affiche();
				*/

				if($stocks < 0)
					return traduction('messages.php.document.validation_impossible_stock')." ".$management->affiche();
			}

		}

		return true;
	}

	/**
	 *
	 *
	 * Retourne les champs qu'on peut remplacer sur le modèle de relance
	 *
	 */
	public function retourne_champs_modele_relance() {

		// on recupère les valeurs des champs en bdd
		$valeurs_des_champs = $this->modele->toArray();

		// on instancie les variables hors bdd

		// on recupère la date du jour
		$valeurs_des_champs['date_du_jour'] = now()->format('d/m/Y');

		// on recupère le nombre de jours de retard
		$valeurs_des_champs['jours_de_retard'] = nombre_de_jours_entre_deux_dates(now()->format('Y-m-d'), $valeurs_des_champs['date_de_reglement']);

		return $valeurs_des_champs;
	}

	/**
	 *
	 *
	 * Retiourne le contenu de la relance avec les bonnes valeurs
	 *
	 */
	public function remplace_variable_modele_relance($contenu) {

		// on recupère les champs libres
		$champs_libres = Champ_libre::where('type_element',$this->_type_element)->get();

		$valeurs_des_champs = $this->retourne_champs_modele_relance();

		foreach($valeurs_des_champs as $champ => $valeur) {

			$cl = $champs_libres->where('nom_sql', $champ)->first();

			if(!empty($cl)) {

				// si le champ libre est de type date, on le formate
				if($cl->type == 4)
					$valeur = formate_date('d/m/Y', $valeur);
			}

			// on remplace la variable par la valeur du document
			$contenu = str_replace("{{document[$champ]}}", $valeur, $contenu);
		}

		return $contenu;
	}

	/**
	 *
	 *
	 * Crée le PDF de relance
	 *
	 */
	public function genere_pdf_modele_relance($contenu_html) {

		$pdf = PDF::loadView('eden::pdf.modele_relance', compact('contenu_html'));

       	$nom_document = 'modele_relance_'.time().'.pdf';

		$pdf->save(storage_path('app/public/'.$nom_document));

		return 'storage/'.$nom_document;
	}

	/**
	 *
	 * Crée les details d'une ligne
	 *
	 */
	public function recuperer_details_ligne_pour_liste($id_element,$type_element,$id_liste_parent) {

        $type_element_lignes = $type_element.'_lignes';

        $liste_libre = Liste_libre::where('type_element',$type_element_lignes)->where('id_rapport','detail_ligne_'.$type_element)->first();

        if(empty($liste_libre))
            exception(traduction('messages.php.rapport_detail_ligne_inexistant'));

		// On va chercher les infos qui nous interessent
		$management = management($type_element, $id_element);

		// On appelle la vue qui affiche les infos que l'on veux via un render()
		$vue = "eden::listes.includes.details_ligne_document";

        $liste_management = liste($type_element_lignes,'detail_ligne_'.$type_element);

        $nombres_de_lignes = modele($type_element_lignes)->where('document_id',$id_element)->count();

        $filtres_pour_fiche = array(
            'document_id' => $id_element,
            'nomenclature_ligne_parent' => null,
        );

        $donnees_liste = $liste_management->recupere_liste($liste_libre->id,[
            'filtres_pour_fiche' => $filtres_pour_fiche,
            'nombre_par_pages' => $nombres_de_lignes,
            'id_liste_parent' => $id_liste_parent,
        ]);

        $donnees_liste['lignes_selectionnees'] = array();
        $donnees_liste['desactiver_checkbox'] = true;

		$vue_render = view($vue, array(
			'management' => $management,
			'id_liste' => $liste_libre->id,
			'type_element' => $type_element_lignes,
			'id_element' => $id_element,
			'liste_libre' => $liste_libre,
			'donnees_liste' => $donnees_liste,
            'filtres_pour_fiche' => $filtres_pour_fiche,
            'id_liste_parent' => $id_liste_parent,
		))->render();

		return array('composant' => $vue_render);
	}

	/**
	 *
	 * Retourne une phrase d'aide contextuelle
	 *
	 */
	public function aide_contextuelle() {

		if(!$this->existe())
			return array();

		// le document n'est pas encore validé
		if(empty($this->modele->valide)) {

			return array('texte' => traduction('messages.php.aide_contextuelle.document.brouillon_a_valider').' <span class="fa fa-fw fa-check"></span>',
                'id_aide' => 'saisie_document_validation');
		}

		if($this->_type_element == 'devis_vente') {

			return $this->aide_contextuelle_devis_vente();
		}

		if($this->_type_element == 'commande_vente') {

			return $this->aide_contextuelle_commande_vente();
		}

		return array();
	}

	/**
	 *
	 * Vérifie si il faut une approbation
	 *
	 */
	public function verification_approbation_neccessaire() {

		$retour = false;

		// On vérifie si il faut une approbation
		if (moi() !== null) {

			$modele_approbation = modele('approbation_workflow')->join('approbation_workflow_profils_necessitant_approbation', 'approbation_workflow_profils_necessitant_approbation.cle_locale', 'approbation_workflow.id')->where('valeur',moi()->profil_id)->where('type_element',$this->_type_element)->where('action',1)->get();

			if (!$modele_approbation->isEmpty())
				$retour = true;
		}

		return $retour;
	}

	/**
	 *
	 * Vérifie si le document est actuellement concerné par une demande d'approbation
	 *
	 */
	public function verification_document_concerne_par_approbation() {

		$retour = false;

		if ($this->modele == null)
			return false;

		// On vérifie si le document est concerné par une approbation en cours
		$modele_approbation = modele('approbation')->where('element_id',$this->modele->id)->where('type_element',$this->_type_element)->where('approbation',null)->get();
		$modele_approbation_2 = modele('approbation')->where('element_id',$this->modele->id)->where('type_element',$this->_type_element)->where('approbation',0)->get();

		if (!$modele_approbation->isEmpty() || !$modele_approbation_2->isEmpty())
				$retour = true;

		return $retour;
	}

	/**
	 *
	 * Vérifie si le document est actuellement concerné par une demande d'approbation qui est validé par l'utilisateur
	 *
	 */
	public function verification_demande_approbation_concerne() {

		$retour = false;

		if(empty(moi()))
			return false;

		// Le document est concerné par une demande d'approbation
		if ($this->verification_document_concerne_par_approbation()) {

			// Vérification si l'utilisateur est la personne concernée
			$modele_approbation = modele('approbation')->where('element_id',$this->modele->id)->where('type_element',$this->_type_element)->where('approbation',null)->where('destinataire_id',moi()->id)->first();

			// Oui
			if ($modele_approbation != null)
				$retour = true;
		}

		return $retour;
	}

	/**
	 *
	 * Vérifie si le document à été refusé afin de permettre de refaire un tour d'approbation
	 *
	 */
	public function verification_document_approbation_refuse() {

		$retour = false;

		// On vérifie si le document à vu sa dernière demande d'approbation refusé
		$modele_approbation = modele('approbation')->where('element_id',$this->modele->id)->where('type_element',$this->_type_element)->where('approbation',2)->first();

		// Oui
		if ($modele_approbation != null)
			$retour = true;

		return $retour;
	}

    /**
     *
     * Change le statut d'une facture, de "A envoyer" à "Non réglée" => ça veut dire que la facture a été transmis au client par email ou par courrier
     *
     * @return true si tout va bien, une erreur (string) sinon
     *
     *
     */
    public function indique_document_comme_envoye() {

        $modifications = array(

            'statut' => 10,
        );

        $this->enregistre_modele($modifications);

        return true;
    }

    /**
     *
     * Enregistre une commande comme Accusée de récéption envoyé
     *
     * @return true si tout va bien, une erreur (string) sinon
     *
     *
     */
    public function accuse_de_reception_envoye() {


       $modifications = array(

           'statut' => 5,
       );

       $this->enregistre_modele($modifications);

        return true;
    }

    /**
     *
     * Permet de vérifié selon les règles spécifique si l'utilisateur a le profil lui permettant de consulter les documents
     *
     * @return true si utilisateur a acces au document
     *
     *
     */
    public function droit_acces_a_fiche(){

    	return true;
    }

    /**
     *
     * Permet de gérer la gstion de l'adresse de facturation si l'affichage est désactivé sur le document
     *
     * @return true si utilisateur a acces au document
     *
     *
     */
    public function gestion_adresse_facturation($modifications){

		if($this->management_fiche()->presence_module('adresse_de_facturation'))
			return $modifications;

		if(!$this->est_une_vente())
			return $modifications;

		if(empty($modifications['client_id']))
			return $modifications;

		$adresse_id = null;

		$adresse = modele('adresse')->where('client_id',$modifications['client_id'])->where('adresse_par_defaut',1)->first();

		if($adresse == null){

			$adresse = modele('adresse')->where('client_id',$modifications['client_id'])->first();

		}

		if($adresse !== null)
			$adresse_id = $adresse->id;


		if(($this->existe() && $this->modele->client_id != $modifications['client_id']) || $this->existe() == false){

			$modifications['adresse_de_facturation'] = $adresse_id;

		}



        return $modifications;
    }

    /**
     *
     * Permet de controler la validation en spécifique
     *
     * @return false pour pouvoir continuer le WF d'approbation
     *
     *
     */
    public function verification_avant_validation_specifique(){

    	return false;
	}

    /**
     *
     * Permet de mettre à jour les index de recherche des lignes du document
     *
     */
    public function maj_index_recherche_lignes($modele){

		$lignes_du_document = modele($this->_type_element."_lignes")->where('document_id', $modele->id)->select("id")->pluck("id");

		if ($lignes_du_document->isEmpty())
			return;

        management($this->_type_element."_lignes")->maj_index_recherche($lignes_du_document->toArray());
    }

    /**
     *
     * Fonction surchargeable qui permet de modifier les articles du document lors de la transformation manuelle
     *
     */
    public function traitement_sur_les_articles_pour_transformation(&$articles,$management_element_origine){

        return true;
    }

	/**
	 *
	 * Récupère le contenu d'un pack d'articles
	 *
	 */
	public function recupere_contenu_pack_pour_document($article_id) {

		return modele('article_contenu_pack')->where('article_id', $article_id)->orderBy('ordre')->get();
	}

    /**
     *
     * Fonction qui permet de vérifier l'intégrité de la séquence chronologique d'un document à la validation
     *
     */
    public function verification_sequence_chronologique(){

        if(!in_array($this->_type_element, array('facture_vente','avoir_vente','acompte_vente')))
            return true;

        if(!fonctionnalite('gestion_integrite_sequence_chronologique'))
            return true;

		$document_valide_le_plus_recent = modele($this->_type_element)
				->zero_ou_null('inactif')
				->where('valide',1)
				->where('entite_id', $this->modele->entite_id)
				->orderBy('date', 'DESC');

		if(fonctionnalite('gestion_integrite_sequence_chronologique_par_annee')) {

			$document_valide_le_plus_recent = $document_valide_le_plus_recent->where('date', 'like', formate_date('Y', $this->modele->date).'%');
		}

		$document_valide_le_plus_recent = $document_valide_le_plus_recent->first();

        // Rien à vérifier et pas d'autre type à vérifier
        if(!in_array($this->_type_element, array('facture_vente','acompte_vente'))) {

            if($document_valide_le_plus_recent === null)
                return true;

            if($this->modele->date < $document_valide_le_plus_recent->date)
                return traduction('messages.php.document.validation_impossible_date',null,[$this->affiche()]);

            return true;
		}

        $type_element_seconde_verification = null;

        // Les factures et acomptes étant liés, on doit également vérifier les acomptes et factures lors de la vérification
        if($this->_type_element == 'facture_vente')
            $type_element_seconde_verification = 'acompte_vente';
        else if($this->_type_element == 'acompte_vente')
            $type_element_seconde_verification = 'facture_vente';

		$type_element_seconde_verification = modele($type_element_seconde_verification)
				->zero_ou_null('inactif')
				->where('valide',1)
				->where('entite_id', $this->modele->entite_id)
				->orderBy('date', 'DESC');

		if(fonctionnalite('gestion_integrite_sequence_chronologique_par_annee')) {

			$type_element_seconde_verification = $type_element_seconde_verification->where('date', 'like', formate_date('Y', $this->modele->date).'%');
		}

        $second_document_verification = $type_element_seconde_verification->first();

        if($second_document_verification !== null &&
            ($document_valide_le_plus_recent == null || $document_valide_le_plus_recent->date < $second_document_verification->date))
            $document_valide_le_plus_recent = $second_document_verification;

		if($document_valide_le_plus_recent == null)
            return true;

        if($this->modele->date < $document_valide_le_plus_recent->date)
            return traduction('messages.php.document.validation_impossible_date',null,[$this->affiche()]);

        return true;
    }

    /**
     *
     * On copie les écheances du document transfomé vers le nouveau document
     *
     */
    public function copie_echeances($management_nouveau_document){

        $echeances_document_source = modele('echeance')->where('type_element', $this->_type_element)->where('element_id', $this->modele->id)->get();

        if(empty($echeances_document_source))
            return;

        foreach ($echeances_document_source->toArray() as $echeance_document_source){

            unset($echeance_document_source['id']);
            $echeance_document_source['type_element'] = $management_nouveau_document->_type_element;
            $echeance_document_source['element_id'] = $management_nouveau_document->modele->id;

            management('echeance')->enregistre($echeance_document_source);

        }

    }

    /**
     *
     * On recalcul les échéances du document
     *
     */
    public function recalcul_echeances(){

        $echeances = modele('echeance')->where('element_id', $this->modele->id)->where('type_element', $this->_type_element)->get();

        foreach ($echeances as $echeance){

            management('echeance', $echeance->id)->enregistre($echeance->toArray());

        }

    }

	/**
	 *
	 * Retourne true si le remplacement d'articles est autorisé
	 *
	 */
	public function remplacement_article_autorise() {

		if(!in_array($this->_type_element, array('acompte_vente', 'facture_vente', 'avoir_vente', 'acompte_achat', 'facture_achat', 'avoir_achat')))
			return true;

		if(empty($this->modele->comptabilise))
			return true;

		return false;
	}

    /**
     *
     * Retourne les articles pour le document fournisseur
     *
     */
    public function recupere_article_pour_transformation_document_fournisseur($articles_document_fournisseur){

        $commandes_fournisseur = array();

        foreach($articles_document_fournisseur as $article_a_commander) {

            // pas de sélection d'article
            if(empty($article_a_commander['article_fournisseur']))
                continue;

            // pas de quantité renseignée
            if(empty($article_a_commander['quantite_commandee']))
                continue;

            $article_fournisseur = modele('article_fournisseur', $article_a_commander['article_fournisseur']);
            $article = modele('article', $article_fournisseur->article_id);

            $id_fournisseur = $article_fournisseur->fournisseur_id;

            if(!isset($commandes_fournisseur[$id_fournisseur]))
                $commandes_fournisseur[$id_fournisseur] = array();

            $id_commande = 'nouvelle_commande';

            if(!empty($article_a_commander['commande']))
                $id_commande = $article_a_commander['commande'];

            if(!isset($commandes_fournisseur[$id_fournisseur][$id_commande]))
                $commandes_fournisseur[$id_fournisseur][$id_commande] = array();

            // on ajoute un article à la commande
            $infos_article = array(

                'article_id' => $article_fournisseur->article_id,
                'tarif' => $article_fournisseur->tarif,
                'code_article' => $article_fournisseur->reference,
                'designation' => $article_fournisseur->designation,
                'conditionnement' => $article_fournisseur->conditionnement_id,
                'type_element_source' => $this->_type_element,
                'id_element_source' => $this->modele->id,
                'id_ligne_source' => $article_a_commander['id_ligne_source'],
                'quantite' => $article_a_commander['quantite_commandee'],
            );

            if(empty($infos_article['code_article']))
                $infos_article['code_article'] = $article->code_article;

            if(empty($infos_article['designation']))
                $infos_article['designation'] = $article->designation;

            $commandes_fournisseur[$id_fournisseur][$id_commande][] = $infos_article;
        }

        return $commandes_fournisseur;

    }

    /**
     *
     * Récupère les commandes liés au fournisseur
     * Utilisé pour la spécification
     *
     */
    public function ajout_commandes_existantes($tarifs_par_fournisseurs, $article){

        foreach($tarifs_par_fournisseurs as $id_article_par_fournisseur => $infos) {

            $commandes = modele('commande_achat')->where('fournisseur_id', $infos['fournisseur_id'])->zero_ou_null('valide')->get();

            $tarifs_par_fournisseurs[$id_article_par_fournisseur]['commandes'] = $commandes;
        }

        $article['tarifs_par_fournisseurs'] = $tarifs_par_fournisseurs;

        return $article;

    }

    /**
     *
     * On annule les documents
     *
     */
    public function annuler_documents($formulaire, $type_element){

        return ['retour' => true, 'nombre_documents' => count($formulaire->ids), 'nombre_annules' => 0];

    }

    public function donnees_transformation_document_fournisseur(){

        return [];

    }

    /**
     *
     * Permet de récupérer les mouvements de stocks liés au document
     *
     */
    public function mouvements_de_stock($forcer_recuperation = false){

        if($this->mouvements_de_stock !== false && $forcer_recuperation === false)
            return $this->mouvements_de_stock;

		$mouvements_de_stock = modele('mouvement_de_stock')
			->where('type_document', 'bl_achat')
			->where('document_id', $this->modele->id)
			->get();

        $this->mouvements_de_stock = $mouvements_de_stock;

        return $this->mouvements_de_stock;
    }

    public function mise_a_jour_tarif($articles,$parametres,$tarif_uniquement = false,$application_valeurs = false){

        $ids_articles = [];

        $this->donnees_articles_recursif('article_id',$ids_articles,$articles);

        if($tarif_uniquement){

            $modeles_articles = modele('article')->whereIn('id',$ids_articles)->get()->keyBy('id');

            $ids_compositions = $modeles_articles->whereIn('id',collect($articles)->pluck('article_id'))->whereIn('type_article',[1,3])->pluck('id')->toArray();
        }
        else{
            $modeles_articles = modele('article')->whereIn('id',collect($articles)->pluck('article_id'))->get()->keyBy('id');

            $ids_compositions = $modeles_articles->whereIn('type_article',[1,3])->pluck('id')->toArray();

            $this->stocks = service('stocks')->details_stocks_par_article($ids_articles);
        }

        $informations_articles = [];

        $this->eco_contribution_active = false;
        $this->fournisseur_id = $parametres['fournisseur_id'] ?? false;

        if(!empty($parametres['client_id'])){

            $client = modele('client', $parametres['client_id']);

            $this->eco_contribution_active = !empty($client->eco_contribution) && $client->eco_contribution == 1;
        }

        if(!empty($ids_compositions)) {
            $this->articles_nomenclature = modele('article')->hydrate(DB::select('
                WITH RECURSIVE cte AS (
                    SELECT *
                    FROM composition_article
                    WHERE article_id IN (' . implode(',',$ids_compositions) . ')
                    AND COALESCE(composition_article.inactif,0) = 0
                    UNION ALL
                    SELECT t.*
                    FROM composition_article t
                    JOIN cte ON t.article_id = cte.article_enfant_id
                    WHERE COALESCE(t.inactif,0) = 0
                )
                SELECT article.*
                FROM cte
                JOIN article ON cte.article_enfant_id = article.id;
            '))->keyBy('id');

            $this->structure_nomenclature = collect(DB::select('
                WITH RECURSIVE cte AS (
                    SELECT *
                    FROM composition_article
                    WHERE COALESCE(composition_article.inactif,0) = 0
                    AND article_id IN (' . implode(',',$ids_compositions) . ')
                    UNION ALL
                    SELECT t.*
                    FROM composition_article t
                    JOIN cte ON t.article_id = cte.article_enfant_id
                    WHERE COALESCE(t.inactif,0) = 0
                )
                SELECT cte.*
                FROM cte
                GROUP BY cte.article_id,cte.article_enfant_id
            '))->groupBy('article_id');
        }

        foreach($articles as $index => $article){

            if(empty($article['article_id']))
                continue;

            $modele_article = $modeles_articles[$article['article_id']] ?? null;

            if(empty($modele_article))
                continue;

            $management_article = management('article',$article['article_id'],$modele_article);

            $informations_articles[$index]['id'] = $article['article_id'];

            if($modele_article['type_article'] == 1 || $modele_article['type_article'] == 3){
                if($tarif_uniquement){
                    $composition_actuel = $this->gestion_recuperation_nomenclature($modele_article);
                    $informations_articles[$index]['composition'] = $this->mise_a_jour_tarif_nomenclature($article['nomenclature'],$composition_actuel,$modeles_articles);
                }
                else
                    $informations_articles[$index]['composition'] = $this->gestion_recuperation_nomenclature($modele_article);

                $informations_articles[$index]['tarif_force'] = $management_article->modele->tarif_force;
                $informations_articles[$index]['prix_achat_force'] = null;
            }
            else {
                $informations_articles[$index]['prix_achat'] = $management_article->recuperation_prix_achat_via_conditionnement($article['conditionnement'] ?? null);

                if($this->est_un_achat())
                    $informations_articles[$index]['tarif'] = $informations_articles[$index]['prix_achat'];
                else
                    $informations_articles[$index]['tarif'] = $management_article->recuperation_tarif_via_conditionnement($article['conditionnement'] ?? null);
            }

            $informations_articles[$index]['quantite'] = $article['quantite'];
        }

        $informations_articles = modele('article')->hydrate($informations_articles);

        management('article')->applique_conditions_commerciales($informations_articles,$parametres);

        $informations_articles = $informations_articles->map(function($article){
            unset($article->id);
            return $article;
        });

		if($application_valeurs)
			return $this->application_valeurs_articles($articles,$parametres,$tarif_uniquement,$informations_articles->toArray());

        return $informations_articles;
    }

    public function mise_a_jour_tarif_nomenclature($compositions,$composition_actuel,$modeles_articles){

        $composition_tarifs = [];

        foreach($compositions as $index_composition => $composition){

            $article_id = $composition['article_enfant_id'] ?? $composition['article_id'];

            if(empty($modeles_articles[$article_id]))
                continue;

            if(!empty($composition['nomenclature'])){
                $composition_tarifs[$index_composition]['composition'] = $this->mise_a_jour_tarif_nomenclature($composition['nomenclature'],$composition_actuel[$index_composition]['nomenclature'],$modeles_articles);
            }
            else{
                 $article_nomenclature = collect($composition_actuel)
                     ->where('article_id',$article_id)->first();

                 if(empty($article_nomenclature))
                     $article_nomenclature = collect($composition_actuel)
                         ->where('article_enfant_id',$article_id)->first();

                 if(!empty($article_nomenclature)){
                     $composition_tarifs[$index_composition]['tarif'] = $article_nomenclature['tarif'];
                     $composition_tarifs[$index_composition]['prix_achat'] = $article_nomenclature['prix_achat'];
                 }
                 else{

                     $management_article = management('article',$article_id,$modeles_articles[$article_id]);

                     $composition_tarifs[$index_composition]['tarif'] = $management_article->recuperation_tarif_via_conditionnement($composition['conditionnement'] ?? null);
                     $composition_tarifs[$index_composition]['prix_achat'] = $management_article->recuperation_prix_achat_via_conditionnement($composition['conditionnement'] ?? null);
                 }
            }
        }

        return $composition_tarifs;
    }

    /**
     *
     * Permet de récupérer les articles et de les stocker dans le management
     *
     */
    public function articles_erp(){

        if($this->articles_erp !== false)
            return $this->articles_erp;

        $articles_erp = modele('article')->get();

        $this->articles_erp = $articles_erp;

        return $articles_erp;
    }

     /**
     *
     * Permet de récupérer les articles du document
     *
     */
    public function articles_du_document(){

        if($this->articles_du_document !== false)
            return $this->articles_du_document;

        $this->charger_articles_du_document();

        return $this->articles_du_document;
    }

    /**
     *
     * Permet de charger les articles du document et de les stocker dans le management
     *
     */
    public function charger_articles_du_document($articles = array()){

		$articles = $articles instanceof Collection ? $articles->toArray() : $articles;

        $ids_articles_du_document = array();

        if(!empty($articles))
            $this->donnees_articles_recursif('article_id',$ids_articles_du_document,$articles);

        if(!empty($this->modele->id))
            $ids_articles_du_document = array_merge($ids_articles_du_document,$this->modele_lignes()
                ->where('document_id', $this->modele->id)
                ->select('article_id')
                ->get()->pluck('article_id')->toArray());

        $this->ids_articles_du_document = array_unique($ids_articles_du_document);

        $this->articles_du_document = modele('article')->whereIn('id',$this->ids_articles_du_document)->get();

        $this->charger_conditionnements_du_document($articles);
    }

    /**
     *
     * Permet de récupérer les conditionnements présent dans le document
     *
     */
    public function conditionnements(){

        if($this->conditionnements !== false)
            return $this->conditionnements;

        $this->charger_conditionnements_du_document();

        return $this->conditionnements;

    }

    /**
     *
     * Permet de charger les conditionnement du document et de les stocker dans le management
     *
     */
    public function charger_conditionnements_du_document($articles = array()){

        $ids_conditionnement_du_document = array();

        if(!empty($articles))
            $this->donnees_articles_recursif('conditionnement',$ids_conditionnement_du_document,$articles);

        if(!empty($this->modele->id))
            $ids_conditionnement_du_document = array_merge($ids_conditionnement_du_document,$this->modele_lignes()
                ->where('document_id', $this->modele->id)
                ->select('conditionnement')
                ->get()->pluck('conditionnement')->toArray());

        $this->ids_conditionnement_du_document = array_unique($ids_conditionnement_du_document);

        $this->conditionnements = modele('conditionnement')->whereIn('id',$this->ids_conditionnement_du_document)->get();
    }

    /**
     * @param $colonne
     * @param $donnees_articles_recursif
     * @param $articles
     * @return void
     *
     * Permet de récupérer une donnée des  articles du document récursivement
     *
     */
    function donnees_articles_recursif($colonne,&$donnees_articles_recursif, $articles) {

        foreach($articles as $article){

            if(!is_array($article))
                $article = (array) $article;

            if($colonne == 'article_id') {
                if (!empty($article['article_id']))
                    $donnees_articles_recursif[] = $article['article_id'];
                else if (!empty($article['article_enfant_id']))
                    $donnees_articles_recursif[] = $article['article_enfant_id'];
            }
            else if(!empty($article[$colonne]))
                $donnees_articles_recursif[] = $article[$colonne];

            if(!empty($article['nomenclature'])){

                $nomenclature = $article['nomenclature'];

                if(!is_array($nomenclature)) {
                    try {
                        $nomenclature = json_decode($nomenclature, true);
                    } catch (\Exception $e) {
                        continue;
                    }
                }

                $this->donnees_articles_recursif($colonne,$donnees_articles_recursif,$nomenclature);

            }
        }
    }

    /**
     *
     * Permet de récupérer les lignes articles
     *
     */
    public function lignes_articles($ajout = false){

        if($this->lignes_articles !== false && $ajout == false)
            return $this->lignes_articles;

        if($this->lignes_articles == false)
            $this->lignes_articles = $this->articles(array(),false, true, false);

        if($ajout !== false)
            $this->lignes_articles->push($ajout);

        return $this->lignes_articles;
    }

    /**
     *
     * Met à jour l'éco-contribution lors de la transformation
     *
     */
    public function mise_a_jour_eco_contribution_lignes(&$articles_du_document){

		if($this->est_un_achat())
			return;

        $type_element_figeant = fonctionnalite('eco_contribution_document_fixant_valeur');

        if($this->_type_element == $type_element_figeant)
            return;

        if(!empty($this->modele->client_id)){

            $client = modele('client')->where('id',$this->modele->client_id)->first();

            if(!empty($client) && $client->eco_contribution != 1)
                return;
        }

        if(!empty($articles_du_document)){

            $documents = $this->documents_anterieurs($articles_du_document);

            $types_elements = array_keys($documents);

            if(in_array($type_element_figeant,$types_elements))
                return;
        }

        // On récupére les modéles des articles
        $articles = $this->articles_du_document();

        $eco_contributions_par_article = [];

        foreach($articles as $article){

            if($article->type_article == 1)
                continue;

            $eco_contributions_par_article[$article->id] = management('article',$article->id,$article)->eco_contribution();
        }

        foreach($articles_du_document as $article_du_document){

            $article_id = $article_du_document->article_id;

            if(!empty($eco_contributions_par_article[$article_id])){
                $article_du_document->categorie_eco_contribution_id = $eco_contributions_par_article[$article_id]['categorie_eco_contribution_id'];
                $article_du_document->application_eco_contribution = $eco_contributions_par_article[$article_id]['application_eco_contribution'];
                $article_du_document->tarif_eco_contribution = $eco_contributions_par_article[$article_id]['tarif_eco_contribution'];
                $article_du_document->quantite_unite_eco_contribution = $eco_contributions_par_article[$article_id]['quantite_unite_eco_contribution'];
            }
            else{
                $article_du_document->categorie_eco_contribution_id = null;
                $article_du_document->application_eco_contribution = null;
                $article_du_document->tarif_eco_contribution = 0;
                $article_du_document->quantite_unite_eco_contribution = 1;
            }
        }
    }

    /**
     *
     * Permet de récupérer tous les documents antérieurs à un document
     *
     */
    public function documents_anterieurs($articles){

        $sources_par_type_element = array();

        foreach($articles as $article){

            if(empty($article['type_element_source']) || empty($article['id_element_source']))
                continue;

            if(!isset($sources_par_type_element[$article['type_element_source']]))
                $sources_par_type_element[$article['type_element_source']] = [];

            if(!in_array($article['id_element_source'],$sources_par_type_element[$article['type_element_source']]))
                $sources_par_type_element[$article['type_element_source']][] = $article['id_element_source'];
        }

        $vus = array();

        foreach($sources_par_type_element as $type_element => $elements_id)
            foreach($elements_id as $element_id)
                $vus += service('documents_lies')->documents_directement_lies($type_element, (int) $element_id, false);

        $documents_anterieurs = array();

        foreach(array_keys($vus) as $cle){

            list($type_element, $id_document) = explode('#', $cle);

            $documents_anterieurs[$type_element][] = (int) $id_document;
        }

        return $documents_anterieurs;
    }

    /**
     *
     * Permet de récupérer tous les documents postérieur à un document
     *
     */
    public function documents_posterieurs($articles){

        $articles_ids = $articles->pluck('id')->toArray();

        $documents_gescom = Champ_libre::where('nom_sql','type_element_source')
            ->get()->pluck('type_element')->toArray();

        $documents_posterieurs = [];

        $types_elements = Variables::$documents_gescom;

        foreach($types_elements as $type_element){

            if(!in_array($type_element.'_lignes',$documents_gescom))
                continue;

            $resultat = modele($type_element)
                ->join($type_element.'_lignes',$type_element.'_lignes.document_id',$type_element.'.id')
                ->select($type_element.'.*')
                ->where($type_element.'_lignes.type_element_source',$this->_type_element)
                ->whereIn($type_element.'_lignes.id_ligne_source',$articles_ids)
                ->groupBy($type_element.'.id')->get()->toArray();

            $documents_posterieurs = array_merge($documents_posterieurs,$resultat);
        }

        return $documents_posterieurs;
    }

    /**
     * @return void
     *
     * Permet de mettre à jour les stocks pour toutes les lignes à la fin de l'enregistrement pour les perfs
     *
     */
    public function gestion_lignes(){

        foreach($this->stocks_a_mettre_a_jour as $document){

            if(!empty($document['mise_a_jour_stocks']))
                service('mouvement_de_stock')->genere_mouvement_de_stock($document['management'],$document['mise_a_jour_stocks']);

            if(!empty($document['suppresion_stocks']))
                service('mouvement_de_stock')->genere_mouvement_de_stock($document['management'],$document['suppresion_stocks'],false);
        }

        foreach($this->documents_changement_reliquat as $management_document){

            $management_document->calcule_reliquat_ca();
            $management_document->gere_statut_automatique();
        }
    }

    /**
     * @param $lignes_a_gerer
     * @return mixed
     *
     * Permet de récupérer les quantités pour les mouvements de stocks
     *
     */
    public function quantite_lignes_mouvement($lignes_a_gerer){

        return $lignes_a_gerer->pluck('quantite','id')->toArray();
    }

    /**
     *
     * Traitement des articles avant une duplication
     *
     */
    public function traitement_articles_duplication($articles, $vider_source = true){

        foreach($articles as &$article){

            if(!empty($article->nomenclature) && !empty($article->modele) && $article->modele->type_article == 1)
                $article->nomenclature = $this->traitement_articles_duplication($article->nomenclature, $vider_source);

            if(isset($article->type) && $article->type == 'regroupement')
                $article->id_temporaire = true;
            else if(isset($article->id))
			    unset($article->id);

			if($vider_source){
				$article->type_element_source = null;
				$article->id_element_source = null;
				$article->id_ligne_source = null;
			}
        }

        return $articles;
    }

    public function champs_blocs_recurrences(){
        return [
            'recurrence_activee',
            'mode_recurrence',
            'rdi_date_generation',
            'rdi_id_modele',
            'rdd_date_generation',
            'rdd_occurences',
            'rdd_periodicite',
            'rdi_mois_generation',
            'rdd_mois_generation',
            'id_recurrence',
            'delai_generation',
            'generation_progressive',
        ];
    }

    public function article_pour_document($article_id, $parametres,$article_modele = false){

        $management_article = management('article', $article_id, $article_modele);

        $article = clone $management_article->modele;

		$management_article->charge_valeurs_champs_multiselection($article);

        $article->contenu_pack = $this->recupere_contenu_pack_pour_document($article_id);

        $this->eco_contribution_active = false;

        $client_id = $parametres['client_id'] ?? null;
        $fournisseur_id = $parametres['fournisseur_id'] ?? null;

        if($client_id !== false && $client_id != 'undefined'){

            $client = modele('client', $client_id);

            $this->eco_contribution_active = !empty($client->eco_contribution) && $client->eco_contribution == 1;
        }

        // on gère la catégorie comptable
        if($client_id !== false && $client_id != 'undefined')
            $categorie_comptable = $parametres['categorie_comptable_id'] ?? modele('client', $client_id)->categorie_comptable_id;
        else
            $categorie_comptable = $parametres['categorie_comptable_id'] ?? modele('fournisseur', $fournisseur_id)->categorie_comptable_id;

        $informations_tva = service('document')->recupere_taux_tva_pour_article_et_categorie_comptable($article, $categorie_comptable, 'vente');

        if($informations_tva !== false){
            $article->taux_de_tva = $informations_tva['taux'];

            if(!empty($informations_tva['article_categorie_comptable']))
                $article->categorie_comptable_article_defaut = $informations_tva['article_categorie_comptable'];
        }

        if($article->type_article == 1 || $article->type_article == 3) {

            $this->articles_nomenclature = modele('article')->hydrate(DB::select('
                WITH RECURSIVE cte AS (
                    SELECT *
                    FROM composition_article
                    WHERE article_id = ' . $article->id . '
                    AND COALESCE(composition_article.inactif,0) = 0
                    UNION ALL
                    SELECT t.*
                    FROM composition_article t
                    JOIN cte ON t.article_id = cte.article_enfant_id
                    WHERE COALESCE(t.inactif,0) = 0
                )
                SELECT article.*
                FROM cte
                JOIN article ON cte.article_enfant_id = article.id;
            '))->keyBy('id');

            $this->structure_nomenclature = collect(DB::select('
                WITH RECURSIVE cte AS (
                    SELECT *
                    FROM composition_article
                    WHERE COALESCE(composition_article.inactif,0) = 0
                    AND article_id = ' . $article->id . '
                    UNION ALL
                    SELECT t.*
                    FROM composition_article t
                    JOIN cte ON t.article_id = cte.article_enfant_id
                    WHERE COALESCE(t.inactif,0) = 0
                )
                SELECT cte.*
                FROM cte
            '))->groupBy('article_id');

            $this->fournisseur_id = $fournisseur_id;

            $this->stocks = service('stocks')->details_stocks_par_article(array_keys($this->articles_nomenclature->toArray()));

            $article->composition = $this->gestion_recuperation_nomenclature($article);
        }

        if(!empty($parametres['quantite']))
            $article->quantite = $parametres['quantite'];

		if(!empty($parametres['conditionnement_id']))
            $article->conditionnement_id = $parametres['conditionnement_id'];

        $management_article->applique_conditions_commerciales(collect([&$article]),$parametres);

        if($this->eco_contribution_active && $article->type_article != 1) {
            // Récupération de l'éco contribution si elle est paramétré sur l'article
            $eco_contribution = $management_article->eco_contribution(request()->date_document ?? null);

            foreach($eco_contribution as $champ => $valeur){

                $article->{$champ} = $valeur;
            }
        }

        $inclure_entrepots_reserves = false;
        $article->stock = service('stocks')->details_stocks_par_article($article_id)[$article_id]['stock_disponible'];

        if(isset($article->modele_de_calculateur_id) && !empty($article->modele_de_calculateur_id)){
            $calculateur = modele('modele_de_calculateur')->where('id', $article->modele_de_calculateur_id)->first();

            $article->calculateur = json_decode($calculateur['calculateur']);
        }

        if($fournisseur_id == false)
            $article->conditionnement_possible = $this->conditionnements_possibles($article_id);
        else
            $article->conditionnement_possible = $this->conditionnements_possibles($article_id, $fournisseur_id);

        $article->entrepot_id = 0;

        $fonctionnalite_colonnes = $this->est_une_vente() ? fonctionnalite('documents_colonnes_a_afficher_vente') : fonctionnalite('documents_colonnes_a_afficher_achat');
        if(fonctionnalite('choix_code_article_sur_saisie_document') == true || !empty($fonctionnalite_colonnes['code_article']))
            $article->choix_code_article = $management_article->choix_code_article();

        if($client_id !== false) {

            // on vérifie si le client a des règles spécifiques concernant l'article
            $article = management('client', $client_id)->regles_pour_article($article);
        }

        return $article;
    }

    public function gestion_recuperation_nomenclature($article){

        $composition_article_a_afficher = [];

        $compositions = $this->structure_nomenclature[$article->id] ?? [];

        foreach($compositions as $composition){

            $element = [];

            $article_de_la_composition = $this->articles_nomenclature[$composition->article_enfant_id] ?? null;

            if($article_de_la_composition == null)
                continue;

            $management_article_composition = management('article',$article_de_la_composition->id,$article_de_la_composition);

            $prix_achat = 0;

            // on a un prix renseigné directement dans la nomenclature
            if($composition->prix_achat > 0){
                $prix_achat = $composition->prix_achat;
				$element['prix_achat_force'] = $composition->prix_achat;
			}
            else {

                if($article_de_la_composition !== null)
                    $prix_achat = $management_article_composition->recuperation_prix_achat_via_conditionnement($composition->conditionnement);

            }

            if($composition->tarif > 0)
                $tarif = $composition->tarif;
            else if($article_de_la_composition->tarif > 0)
                $tarif = $management_article_composition->recuperation_tarif_via_conditionnement($composition->conditionnement);
            else
                $tarif = 0;

            if($composition->tarif > 0)
                $element['tarif_force'] = $composition->tarif;
            else if($article_de_la_composition->tarif_force > 0)
                $element['tarif_force'] = $article_de_la_composition->tarif_force;

            if(isset($article_de_la_composition->modele_de_calculateur_id) && !empty($article_de_la_composition->modele_de_calculateur_id)){
                $calculateur = modele('modele_de_calculateur')->where('id', $article_de_la_composition->modele_de_calculateur_id)->first();

                $article_de_la_composition->calculateur = json_decode($calculateur['calculateur']);
            }

            $element['code_article'] = $article_de_la_composition->code_article;
            $element['designation'] = $article_de_la_composition->designation;
            $element['quantite'] = $composition->quantite;

            $element['tarif'] = $tarif;
            $element['prix_achat'] = $prix_achat;
            $element['unite'] = $article_de_la_composition->unite;
            $element['article_enfant_id'] = $composition->article_enfant_id;
            $element['conditionnement'] = $composition->conditionnement;
            $element['type_article'] = $article_de_la_composition->type_article;

            if($this->eco_contribution_active && $article_de_la_composition->type_article != 1) {
                $eco_contribution = $management_article_composition->eco_contribution(request()->date_document ?? null);

                $element = array_merge($element,$eco_contribution);
            }

            $element['modele'] = $article_de_la_composition->toArray();

            if($article_de_la_composition->type_article == 1 || $article_de_la_composition->type_article == 3)
                $element['nomenclature'] = $this->gestion_recuperation_nomenclature($article_de_la_composition);

            if($this->fournisseur_id == false)
                $element['conditionnement_possible'] = $this->conditionnements_possibles($composition->article_enfant_id);
            else
                $element['conditionnement_possible'] = $this->conditionnements_possibles($composition->article_enfant_id, $this->fournisseur_id);

            if($composition->tarif !== null)
                $element['tarif'] = $composition->tarif;

            if($composition->prix_achat !== null)
                $element['prix_achat'] = $composition->prix_achat;

            $element['stock'] = isset($this->stocks[$composition->article_enfant_id]) ? $this->stocks[$composition->article_enfant_id]['stock_disponible'] : null;

            $composition_article_a_afficher[] = $element;
        }

        return $composition_article_a_afficher;
    }

    public function retourne_infos_transformation_commande_fournisseur($commande, $articles, $id_fournisseur, $champs_correspondances) {
        $infos_maj_commande = array();

        if(!$commande->existe()) {
            $infos_maj_commande['articles'] = $articles;

            $infos_maj_commande['date'] = date('Y-m-d');
            $infos_maj_commande['date_de_reception'] = date('Y-m-d');
            $infos_maj_commande['fournisseur_id'] = $id_fournisseur;


            foreach($champs_correspondances as $champ_libre){

                if(!empty($fournisseurs[$id_fournisseur]->{$champ_libre->correspondance_fiche_tiers}))
                    $infos_maj_commande[$champ_libre->nom_sql] = $fournisseurs[$id_fournisseur]->{$champ_libre->correspondance_fiche_tiers};

            }

            $infos_maj_commande['entrepot_id'] = $this->modele->entrepot_id;
        }
        else
            $infos_maj_commande['articles'] = $articles;

        return $infos_maj_commande;
    }

    public function ajoute_lignes_divers_pour_ligne(&$articles_du_document,$lignes_divers,$position){
        foreach($lignes_divers as $ligne_divers) {

            if($ligne_divers->ligne == $position) {

                $ligne_divers->type_ligne = $ligne_divers->type;

                if(isset($ligne_divers->calculateur) && !empty($ligne_divers->calculateur))
                    $ligne_divers->calculateur = json_decode($ligne_divers->calculateur);

                if(isset($ligne_divers->coefficient) && !empty($ligne_divers->coefficient))
                    $ligne_divers->coefficient = json_decode($ligne_divers->coefficient);

                $articles_du_document[] = $ligne_divers;
            }
        }
    }

	/**
	 * 
	 * Applique les valeurs des articles du document en fonction des informations fournies.
	 * @param array $articles Les articles du document.
	 * @param array $parametres Les paramètres du document.
	 * @param bool $tarif_uniquement Indique si seules les valeurs tarifaires doivent être appliquées.
	 * @param array $informations_articles Les informations des articles à appliquer.
	 * @return array Les articles du document avec les valeurs appliquées.
	 * 
	 */
	public function application_valeurs_articles($articles,$parametres,$tarif_uniquement,$informations_articles){

		$gestion_composition = function(){
			$nomenclatures = [];
			$tarif_total = 0;
			$prix_achat_total = 0;

			foreach ($compositions as $composition) {
				$nomenclature = [];
				if (isset($composition['nomenclature']) && !empty($composition['nomenclature'])) {
					$nomenclature = $composition['nomenclature'];
				}

				$conditionnement = $composition['conditionnement'] ?? 0;

				$article_dans_nomenclature = [
					'code_article' => $composition['code_article'] ?? null,
					'designation' => $composition['designation'] ?? null,
					'quantite' => $composition['quantite'] ?? 0,
					'tarif' => $composition['tarif'],
					'prix_achat' => $composition['prix_achat'],
					'article_id' => $composition['article_enfant_id'] ?? null,
					'stock' => $composition['stock'] ?? null,
					'unite' => $composition['unite'] ?? null,
					'conditionnement_possible' => $composition['conditionnement_possible'] ?? null,
					'conditionnement' => $conditionnement,
					'type_article' => $composition['type_article'] ?? null,
					'afficher_nomenclature' => false,
					'nomenclature' => $nomenclature,
					'calculateur' => $composition['calculateur'] ?? null,
					'modele_de_calculateur_id' => $composition['modele_de_calculateur_id'] ?? null,
					'modele' => $composition['modele'] ?? null,
				];

				if (!empty($this->eco_contribution_active)) {
					$article_dans_nomenclature['categorie_eco_contribution_id'] = $composition['categorie_eco_contribution_id'] ?? null;
					$article_dans_nomenclature['tarif_eco_contribution'] = $composition['tarif_eco_contribution'] ?? null;
					$article_dans_nomenclature['application_eco_contribution'] = $composition['application_eco_contribution'] ?? null;
					$article_dans_nomenclature['quantite_unite_eco_contribution'] = $composition['quantite_unite_eco_contribution'] ?? null;
				}

				if (method_exists($this, 'est_une_vente') && $this->est_une_vente()) {
					if (!empty($composition['tarif_force'])) {
						$article_dans_nomenclature['tarif'] = $composition['tarif_force'];
						$article_dans_nomenclature['tarif_force'] = $composition['tarif_force'];
						$article_dans_nomenclature['tarif_initial'] = $composition['tarif_force'];
						$article_dans_nomenclature['affichage_tarif_force'] = true;
					}
					if (!empty($composition['prix_achat_force'])) {
						$article_dans_nomenclature['prix_achat'] = $composition['prix_achat_force'];
						$article_dans_nomenclature['prix_achat_force'] = $composition['prix_achat_force'];
					}
				}

				$nomenclatures[] = $article_dans_nomenclature;
				$tarif_total += $composition['quantite'] * $composition['tarif'];
				$prix_achat_total += $composition['quantite'] * $composition['prix_achat'];
			}

			$tarif = round($tarif_total, 2);
			$prix_achat = round($prix_achat_total, 2);

			return [$nomenclatures, $tarif, $prix_achat];
		};

		foreach ($informations_articles as $index_article => $informations) {

			if (!isset($articles[$index_article]))
				continue;

			$article_du_document = &$articles[$index_article];

			foreach ($informations as $attribut => $valeur) {
				if ($attribut === 'composition') {
					if ($tarif_uniquement) {
						$this->gestion_composition_tarif_uniquement($article_du_document, $valeur);
					} else {
						list($nomenclatures, $tarif, $prix_achat) = $gestion_composition($valeur);
						$article_du_document['nomenclature'] = $nomenclatures;
						$article_du_document['tarif'] = $tarif;
						$article_du_document['prix_achat'] = $prix_achat;
					}
				} else {
					$article_du_document[$attribut] = $valeur;
				}
			}
		}

		return $articles;
	}

	/**
	 * 
	 * Gère la composition tarif uniquement pour les articles.
	 * @param array $article L'article à traiter.
	 * @param array $compositions_nouveaux_tarifs Les nouvelles compositions tarifaires.
	 * 
	 */
	public function gestion_composition_tarif_uniquement(&$article, $compositions_nouveaux_tarifs)
	{
		$tarif_total = 0;
		$prix_achat_total = 0;

		if (empty($article['nomenclature']) || !is_array($article['nomenclature'])) {
			return;
		}

		foreach ($article['nomenclature'] as $index_nomenclature => &$nomenclature) {
			if (!empty($nomenclature['nomenclature']) && is_array($nomenclature['nomenclature'])) {
				$this->gestion_composition_tarif_uniquement($nomenclature, $compositions_nouveaux_tarifs[$index_nomenclature]['composition']);
			} else {
				$nomenclature['tarif'] = $compositions_nouveaux_tarifs[$index_nomenclature]['tarif'];
				$nomenclature['prix_achat'] = $compositions_nouveaux_tarifs[$index_nomenclature]['prix_achat'];
			}
			$tarif_total += $nomenclature->quantite * $nomenclature->tarif;
			$prix_achat_total += $nomenclature->quantite * $nomenclature->prix_achat;
		}

		$article['tarif'] = round($tarif_total, 2);
		$article['prix_achat'] = round($prix_achat_total, 2);
	}

	public function gerer_pdf_post_modification(){
		
        $config_bloquer_pdf_post_validation = fonctionnalite('gescom_type_document_bloquer_pdf_post_validation');

        //On passe le pdf a null ce qui nous permet de le regénérer
        if($this->modele->valide != 1 || empty($config_bloquer_pdf_post_validation[$this->_type_element])) {

            $this->enregistre_modele(array('pdf' => null));

                // création du PDF si la fonctionnalite de versionning est activé
            if((isset(fonctionnalite('versionning_document')[$this->_type_element]) && fonctionnalite('versionning_document')[$this->_type_element] == true)){

                $this->creation_pdf();
            }
        }
    }
}
