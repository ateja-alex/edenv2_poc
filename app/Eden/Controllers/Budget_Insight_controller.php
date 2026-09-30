<?php

namespace App\Eden\Controllers;

use App\Eden\Managements\Cron_management;
use App\Eden\Variables;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eden\Managements\Listes_management;
use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Models\Liste_libre;
use App\Eden\Models\Champ_libre;
use Illuminate\Support\Facades\Log;
use URL;

class  Budget_Insight_controller extends Controller {
	
	/**
	 * 
	 * Enregistre le token de budget insight après avoir créé une synchro
	 * 
	 */
	public function enregistre_token($formulaire) {

		$budget_insight = management('budget_insight');
		
		$afficher_les_donnees = false;

    	// Si le code est passé en paramètre, on récupère le token et on redirige vers la vue
    	if(isset($formulaire['code'])) {
    		
    		$retour = $budget_insight->get_token($formulaire['code']);
				
			if(!isset($retour->access_token)) {
				
				dd_eden('Impossible de récupérer le Token.', $retour, __file__, __line__);
				
				return redirect()->route('budget_insight.index')->withError(traduction('messages.php.budget_insight.token_introuvable'));
			}
		}
		
		
		
		$budget_insight_synchro = management('budget_insight_synchro');
		$retour_creation = $budget_insight_synchro->enregistre(['token' => $retour->access_token]);
		
		// on lance une premièer synchro
		$budget_insight_management = management('budget_insight');
		
		$infos_connexion = $budget_insight_management->recupere_banques($budget_insight_synchro->modele->token);
			
		$comptes = $budget_insight_management->recupere_comptes($budget_insight_synchro->modele->token);
		
		$budget_insight_management->enregistre_compte($comptes, $budget_insight_synchro->modele, $infos_connexion);
		
		return redirect()->route('budget_insight.index')->withMessage(traduction('messages.php.budget_insight.connexion_banque',null,[URL::to('/eden/budget_insight/parametrage_synchro')]));
	}
	
	/**
	 * 
	 * Affiche la page de rapprochement
	 * 
	 */
    public function afficher() {
		
		$formulaire = request()->all();

    	// Si le code est passé en paramètre, on récupère le token et on redirige vers la vue
		// @todo voir si on peut pas faire une route directement pour cela ?!
    	if(isset($formulaire['code'])) {

    		return $this->enregistre_token($formulaire);
		}
        else if(isset($formulaire['id_connection'])) {
            $budget_insight_comptes = modele('budget_insight_comptes')
                ->where('id_connection', $formulaire['id_connection'])
                ->get();

            foreach($budget_insight_comptes as $budget_insight_compte){
                management('budget_insight_comptes', $budget_insight_compte->id, $budget_insight_compte)->enregistre([
                    'error' => null,
                    'state' => null
                ]);
            }
        }
		
		$budget_insight = management('budget_insight');
		$afficher_les_donnees = false;
		
		// on va chercher l'id de la liste libre
		$liste_libre = Liste_libre::where('type_element', 'budget_insight_transaction')->first();
		
		$mode_de_paiement = modele('mode_paiement')->get();

		$entites = modele('entite')->select('entite.*')->distinct()->join('budget_insight_comptes', 'entite_id', 'entite.id');

        if(!empty(moi()->entites))
            $entites->whereIn('entite.id', moi()->entites);

        $entites = $entites->get();

		return view('eden::budget_insight', [
			'management_element' => management('budget_insight_transaction'),
			'comptes' => modele('budget_insight_comptes')->orderBy('entite_id')->orderBy('ordre')->join('entite', 'budget_insight_comptes.entite_id', 'entite.id')->where('display', '1')->select('budget_insight_comptes.*', 'entite.nom as nomEntite')->get(),
			'entites' => $entites,
			'id_liste' => $liste_libre->id,
			'mode_de_paiement' => $mode_de_paiement,
		]);
    }
	
	/**
	 * 
	 * Permet de saisir une opération de tréso directement depuis le relevé bancaire
	 * 
	 */
	public function saisie_operation_treso() {

		$infos_paiement = request()->paiement;
		
		$infos_paiement['rapproche'] = 1;
		
		$management = management('paiement');
		
		$erreur = $management->enregistre($infos_paiement);
		
		if($erreur === true) {
			
			$comptabiliser_automatiquement = fonctionnalite('comptabiliser_automatiquement');
			
			if(!empty($comptabiliser_automatiquement['paiement'])) {
                $retour_comptabilisation = $management->comptabilise();

                if($retour_comptabilisation !== true)
                    return response()->json(array('erreur' => true, 'message' => $retour_comptabilisation));
            }
			
			return response()->json(array('erreur' => false, 'message' => $erreur)); 
		}
		
		return response()->json(array('erreur' => true, 'message' => $erreur)); 
	}
	
    public function synchronisation($jours = 3) {

        try {

            $retour = management('budget_insight')->synchronisation($jours);

        } catch(\Exception $erreur){

            $retour = false;
            \Log::error('Erreur lors de la synchronisation Budget Insight. Message : ' . $erreur->getMessage() . ' Stacktrace : ' . $erreur->getTraceAsString());
        }
		
    	if ($retour !== true)
            return response()->json(array('success' => false));
			
        return response()->json(array(
            'success' => true,
            'comptes' => modele('budget_insight_comptes')->orderBy('entite_id')->orderBy('ordre')->join('entite', 'budget_insight_comptes.entite_id', 'entite.id')->where('display', '1')->select('budget_insight_comptes.*', 'entite.nom as nomEntite')->get()
        ));
    }

	/**
	 * 
	 * Mise à jour d'une connexion
	 * 
	 */
	public function mise_a_jour_connexion($id) {
		
		$synchro = modele('budget_insight_synchro', $id);
		
		$management = management('budget_insight');
		
		$retour = $management->recupere_lien_mise_a_jour_connexion($synchro->token);
		
		return redirect()->away($retour);
	}
	
	/**
	 * 
	 * Récupère la liste des factures à régler
	 * 
	 */
	public function recupere_facture_pour_paiement() {
		
		// on récupère les filtres, pour limiter le nombre de transactions
		//$liste = Liste_libre::where('type_element', 'budget_insight_transaction')->first();
		
		//$parametres = Rapports_management::recupere_parametres('liste_'.$liste->id);

		//$transaction = modele('budget_insight_transaction', request()->transaction_id);
		$entite_id = request()->entite_id;
				
		// filtre entité unique
		if(fonctionnalite('budget_insight_filtre_entite_pour_traitement_transactions') === false) {
			
			$factures = modele('facture_vente')->where(function($r) { 
					$r->where('regle', 0)->orWhereNull('regle');
				})
				->where('valide', 1)
				->where('solde_document_ttc', '!=', 0)
				->get();

		}
		else {
			
			$factures = modele('facture_vente')->where(function($r) { 
					$r->where('regle', 0)->orWhereNull('regle');
				})
				->where('valide', 1)
				->where('entite_id', $entite_id)
				->where('solde_document_ttc', '!=', 0)
				->get();
		}

		foreach($factures as $facture) {
			
			$client_management = management('client', $facture->client_id);
			
			$facture->client_id = $client_management->affiche_lien();
			$facture->date = formate_date('d/m/Y', $facture->date);
		}
			
		return response()->json($factures);
	}
	
	/**
	 * 
	 * Récupère la liste des commandes à régler
	 * 
	 */
	public function recupere_commande_pour_paiement() {
		
		$entite_id = request()->entite_id;
				
		// filtre entité unique
		if(fonctionnalite('budget_insight_filtre_entite_pour_traitement_transactions') === false) {
			
			$commandes = modele('commande_vente')
				->where('valide', 1)
				->where('solde_document_ttc', '>', 0)
				->get();

		}
		else {
			
			$commandes = modele('commande_vente')
				->where('valide', 1)
				->where('entite_id', $entite_id)
				->where('solde_document_ttc', '>', 0)
				->get();
		}

		foreach($commandes as $commande) {
			
			$client_management = management('client', $commande->client_id);
			
			$commande->client_id = $client_management->affiche_lien();
			$commande->date = formate_date('d/m/Y', $commande->date);
		}
			
		return response()->json($commandes);
	}
	
	/**
	 * 
	 * Récupère la liste des acomptes à régler
	 * 
	 */
	public function recupere_acompte_pour_paiement() {
		
		$entite_id = request()->entite_id;
				
		// filtre entité unique
		if(fonctionnalite('budget_insight_filtre_entite_pour_traitement_transactions') === false) {
			
			$acomptes = modele('acompte_vente')
				->zero_ou_null('regle')
				->where('valide', 1)
				->where('solde_document_ttc', '>', 0)
				->get();

		}
		else {
			
			$acomptes = modele('acompte_vente')
				->zero_ou_null('regle')
				->where('valide', 1)
				->where('entite_id', $entite_id)
				->where('solde_document_ttc', '>', 0)
				->get();
		}

		foreach($acomptes as $acompte) {
			
			$client_management = management('client', $acompte->client_id);
			
			$acompte->client_id = $client_management->affiche_lien();
			$acompte->date = formate_date('d/m/Y', $acompte->date);
		}
			
		return response()->json($acomptes);
	}

    /**
	 *
	 * Récupère la liste des avoirs à régler
	 *
	 */
	public function recupere_avoir_pour_paiement() {

		$entite_id = request()->entite_id;

		// filtre entité unique
		if(fonctionnalite('budget_insight_filtre_entite_pour_traitement_transactions') === false) {

			$commandes = modele('avoir_vente')
				->where('valide', 1)
				->where('solde_document_ttc', '>', 0)
				->get();

		}
		else {

			$commandes = modele('avoir_vente')
				->where('valide', 1)
				->where('entite_id', $entite_id)
				->where('solde_document_ttc', '>', 0)
				->get();
		}

		foreach($commandes as $commande) {

			$client_management = management('client', $commande->client_id);

			$commande->client_id = $client_management->affiche_lien();
			$commande->date = formate_date('d/m/Y', $commande->date);
		}

		return response()->json($commandes);
	}
	
	/**
	 * 
	 * Récupère la liste des factures achat à régler
	 * 
	 */
	public function recupere_facture_achat_pour_paiement() {
		
		$entite_id = request()->entite_id;
				
		// filtre entité unique
		if(fonctionnalite('budget_insight_filtre_entite_pour_traitement_transactions') === false) {
			
			$documents = modele('facture_achat')
				->zero_ou_null('regle')
				->where('valide', 1)
				->where('solde_document_ttc', '>', 0)
				->get();

		}
		else {
			
			$documents = modele('facture_achat')
				->zero_ou_null('regle')
				->where('valide', 1)
				->where('entite_id', $entite_id)
				->where('solde_document_ttc', '>', 0)
				->get();
		}

		foreach($documents as $document) {
			
			$fournisseur_management = management('fournisseur', $document->fournisseur_id);
			
			$document->fournisseur_id = $fournisseur_management->affiche_lien();
			$document->date = formate_date('d/m/Y', $document->date);
		}
			
		return response()->json($documents);
	}

    /**
	 *
	 * Récupère la liste des avoirs achat à régler
	 *
	 */
	public function recupere_avoir_achat_pour_paiement() {

		$entite_id = request()->entite_id;

		// filtre entité unique
		if(fonctionnalite('budget_insight_filtre_entite_pour_traitement_transactions') === false) {

			$documents = modele('avoir_achat')
				->zero_ou_null('regle')
				->where('valide', 1)
				->where('solde_document_ttc', '>', 0)
				->get();

		}
		else {

			$documents = modele('avoir_achat')
				->zero_ou_null('regle')
				->where('valide', 1)
				->where('entite_id', $entite_id)
				->where('solde_document_ttc', '>', 0)
				->get();
		}

		foreach($documents as $document) {

			$fournisseur_management = management('fournisseur', $document->fournisseur_id);

			$document->fournisseur_id = $fournisseur_management->affiche_lien();
			$document->date = formate_date('d/m/Y', $document->date);
		}

		return response()->json($documents);
	}

    /**
	 *
	 * Récupère la liste des notes de frais à régler
	 *
	 */
	public function recupere_note_de_frais_pour_paiement() {

		$entite_id = request()->entite_id;

		// filtre entité unique
		if(fonctionnalite('budget_insight_filtre_entite_pour_traitement_transactions') === false) {

			$notes_de_frais = modele('note_de_frais')
				->zero_ou_null('rembourse')
				->where('accepte', 1)
				->where('montant_ttc', '>', 0)
				->get();

		}
		else {

			$notes_de_frais = modele('note_de_frais')
				->zero_ou_null('rembourse')
				->where('accepte', 1)
				->where('entite_id', $entite_id)
				->where('montant_ttc', '>', 0)
				->get();
		}

		foreach($notes_de_frais as $note_de_frais) {

            $utilisateur_management = management('utilisateur', $note_de_frais->utilisateur_id);

			$note_de_frais->utilisateur_id = $utilisateur_management->affiche_lien();

            $client_management = management('client', $note_de_frais->client_id);

			$note_de_frais->client_id = $client_management->affiche_lien();

			$note_de_frais->date = formate_date('d/m/Y', $note_de_frais->date);
		}

		return response()->json($notes_de_frais);
	}
	
	/**
	 * 
	 * Récupère la liste des paiements à rapprocher
	 * 
	 */
	public function recupere_paiements_non_rapproches() {
		
		$transaction_id = request()->transaction_id ;
		$transaction = modele('budget_insight_transaction', $transaction_id);
		
		if(fonctionnalite('budget_insight_filtre_entite_pour_traitement_transactions') === false) {
			
			$paiements = modele('paiement')
				->where(function($r) { 
					$r->where('rapproche', 0)
					->orWhereNull('rapproche');
				})
				->where('date', '>=', date('Y-m-d', strtotime($transaction->date.' -12 months')))
				->where('date', '<=', date('Y-m-d', strtotime($transaction->date.' +12 months')))
				->get();

		}
		else {
			
			$paiements = modele('paiement')
				->where(function($r) { 
					$r->where('rapproche', 0)
					->orWhereNull('rapproche');
				})
				->where('entite_id', $transaction->entite_id)
				->where('date', '>=', date('Y-m-d', strtotime($transaction->date.' -12 months')))
				->where('date', '<=', date('Y-m-d', strtotime($transaction->date.' +12 months')))
				->get();
		}
		
		
		
			
		foreach($paiements as $paiement) {
			
			if(!empty($paiement->client_id)) {
				
				$client_management = management('client', $paiement->client_id);
				
				$paiement->client_id = 'Client : '.$client_management->affiche_lien();
				
			}
			elseif(!empty($paiement->fournisseur_id)) {
				
				$fournisseur_management = management('fournisseur', $paiement->fournisseur_id);
				
				$paiement->client_id = 'Fournisseur : '.$fournisseur_management->affiche_lien();
			}
			
			$paiement->date = formate_date('d/m/Y', $paiement->date);
			
			$paiement->reference_document = '';
			
			if(!empty($paiement->type_element) && !empty($paiement->id_document)) {
				
				$document_modele = modele($paiement->type_element, $paiement->id_document);
				$paiement->reference_document = $document_modele->reference_document;
			}
		}
			
		return response()->json($paiements);
	}
	
	/**
	 * 
	 * Récupère la liste des bordereaux à rapprocher
	 * 
	 */
	public function recupere_bordereaux_non_rapproches() {
		
		$transaction_id = request()->transaction_id ;
		$transaction = modele('budget_insight_transaction', $transaction_id);
		
		$bordereaux = modele('bordereau')
				->zero_ou_null('statut')
				->where('date', '>=', date('Y-m-d', strtotime($transaction->date.' -2 months')))
				->where('date', '<=', date('Y-m-d', strtotime($transaction->date.' +2 months')))
				->get();
		
		return response()->json($bordereaux);
	}
	
	/**
	 * 
	 * Sépare une transaction des paiements
	 * 
	 */
	public function separe_transaction() {
		
		$transaction_id = request()->transaction_id;
		
		$transaction = management('budget_insight_transaction', $transaction_id);
		
		$paiements = modele('paiement')->where('transaction_id', $transaction_id)->get();
		
		$erreurs = array();
						
		// On supprime les paiements liés à cette transaction
		foreach($paiements as $paiement) {
			
			$management = management('paiement', $paiement->id, $paiement);
			
			// on dé rapproche l'opération avant de le supprimer
			$management->enregistre_modele(array('rapproche' => 0));
			
			$retour = $management->supprime();
			
			if($retour !== true) {
				
				if(isset($erreurs[$retour])) {
					
					$erreurs[$retour]++;
				}
				else {
					
					$erreurs[$retour] = 1;
				}
			}
		}
		
		$transaction->enregistre(['statut_eden' => null, 'a_rapprocher' => $transaction->modele->value]);
		
		// Surcharge paiements supprime pour factures/reglées
			
		return response()->json(array('erreurs' => $erreurs));
	}
	
	/**
	 * 
	 * Sépare une transaction des paiements
	 * 
	 */
	public function recupere_paiements() {
		$transaction_id = request()->transaction_id;
		$transaction = management('budget_insight_transaction', $transaction_id);
		$paiements = modele('paiement')
						->where('transaction_id', $transaction_id)
						->get();
			
		return response()->json($paiements);
	}	
	
	/**
	 * 
	 * Enregistre les paiements pour une ou plusieurs factures
	 * 
	 */
	public function enregistre_paiement($type_element) {
		
		// on va saisir les paiements
		$elements = request()->elements;

		$transaction = management('budget_insight_transaction', request()->transaction_id);
		if($transaction->modele->a_rapprocher == 0)
			return response()->json(traduction('messages.php.budget_insight.transaction_deja_rapprochee'));
		
		// on va chercher le compte bancaire de la transaction
		$compte_bancaire_reel = modele('budget_insight_comptes', $transaction->modele->id_account);

		if(empty($compte_bancaire_reel->compte_bancaire_id)) {

			return response()->json(array('resultat' => false, 'erreur' => traduction('messages.php.budget_insight.compte_eden_introuvable')));
		}

        $montant_total_a_rapprocher = abs($transaction->modele->a_rapprocher);
		
		// @todo récupérer le mode de paiement et le compte bancaire !!!
		
		$retour = true;
		
		foreach($elements as $id_document) {
			
			$element_management = management($type_element, $id_document);
			
			$paiement = management('paiement');
			
			if($transaction->modele->a_rapprocher < 0) {
				
				$type = 1;
				$montant_ttc = abs($transaction->modele->a_rapprocher);
			}
			else {
				
				$type = 0;
				$montant_ttc = abs($transaction->modele->a_rapprocher);
			}

            $solde_ttc = $type_element == 'note_de_frais' ? $element_management->modele->montant_rembourse : $element_management->modele->solde_document_ttc;
			
			if(count($elements) > 1) {

                if($montant_total_a_rapprocher < $solde_ttc)
                    $montant_ttc = $montant_total_a_rapprocher;
				else
				    $montant_ttc = $solde_ttc;

                $montant_total_a_rapprocher-= $montant_ttc;

                if($montant_total_a_rapprocher < 0)
                    $montant_total_a_rapprocher = 0;
			}

            if($montant_ttc == 0)
                continue;
			
			// on gère le cas ou le montant de la facture est < au montant du paiement
			if($montant_ttc > $solde_ttc)
				$montant_ttc = $solde_ttc;

			$mode_paiement_eden_transaction = modele('correspondance_mode_paiement_budget_insight_eden')->where('mode_paiement_insight', $transaction->modele->type)->first();

			if($mode_paiement_eden_transaction == null){

				$mode_paiement_eden_transaction['mode_paiement_eden'] = 0;

			}

            $titre = traduction('document.paiement_budget_insight.titre') .' '. ($type_element == 'note_de_frais' ? $element_management->modele->date : $element_management->modele->reference_document);
			
			$infos = array(
				
				'client_id' => $element_management->modele->client_id,
				'rapproche' => 1,
				'transaction_id' => request()->transaction_id,
				'date' => $transaction->modele->date,
				'montant_saisi' => $montant_ttc,
				'type' => $type,
				'mode_paiement_id' => $mode_paiement_eden_transaction['mode_paiement_eden'],
				'compte_bancaire_id' => $compte_bancaire_reel->compte_bancaire_id ?? null,
				'entite_id' => $element_management->modele->entite_id,
				'titre' => $titre,
				'type_element' => $type_element,
				'id_document' => $element_management->modele->id,
			);
			
			if(!empty($element_management->modele->client_id_tiers_payeur))
				$infos['client_id'] = $element_management->modele->client_id_tiers_payeur;
			
			if(in_array($type_element, Variables::$documents_achat_gescom)) {
				
				unset($infos['client_id']);
				$infos['fournisseur_id'] = $element_management->modele->fournisseur_id;
				
			}
			
			$retour = $paiement->enregistre($infos);
			
			if($retour !== true) {
				
				return response()->json(array('resultat' => false, 'erreur' => $retour));
			}
			else {
				
				$comptabiliser_automatiquement = fonctionnalite('comptabiliser_automatiquement');
			
				if(!empty($comptabiliser_automatiquement['paiement'])) {
                    $retour_comptabilisation = $paiement->comptabilise();

                    if($retour_comptabilisation !== true)
                        return response()->json(['resultat' => false, 'erreur' => $retour_comptabilisation]);
                }
			}
		}
		
		$transaction->maj_statut_rapprochement();
		
			
		return response()->json(array('resultat' => true));
	}
	
	/**
	 * 
	 * Enregistre les rapprochements pour un ou plusieurs paiements
	 * 
	 */
	public function enregistre_rapprochement() {
		
		// on va saisir les paiements
		$paiements = request()->paiements_rapproches;
		
		$transaction = management('budget_insight_transaction', request()->transaction_id);
		
		// on va chercher le compte bancaire de la transaction
		$compte_bancaire_reel = modele('budget_insight_comptes', $transaction->modele->id_account);
		
		if(empty($compte_bancaire_reel->compte_bancaire_id)) {
			
			return response()->json(array('resultat' => false, 'erreur' => traduction('messages.php.budget_insight.compte_eden_introuvable')));
		}

        $erreur_paiements = [];
        $erreur_comptabilisation = [];

		foreach($paiements as $id_paiement) {
			
			$paiement_management = management('paiement', $id_paiement);
			
			$modifications = array(
				
				'rapproche' => 1,
				'transaction_id' => request()->transaction_id,
				// 'date' => $transaction->modele->date,
				'compte_bancaire_id' => $compte_bancaire_reel->compte_bancaire_id,
			);
			
			$erreur = $paiement_management->enregistre_modele($modifications);

            if($erreur === true) {

                $comptabiliser_automatiquement = fonctionnalite('comptabiliser_automatiquement');

                if (!empty($comptabiliser_automatiquement['paiement'])) {
                    $comptabilisation = $paiement_management->comptabilise();

                    if ($comptabilisation !== true)
                        $erreur_comptabilisation[$id_paiement] = $comptabilisation;
                }

            } else {
                $erreur_paiements[$id_paiement] = $erreur;
            }
		}

        $erreur = '';

        if(!empty($erreur_paiements)) {
            $erreur = traduction('messages.php.budget_insight.erreur_enregistrement_paiements', null, [implode(' ,', array_keys($erreur_paiements))]);
        }
        if(!empty($erreur_comptabilisation)) {

            if(strlen($erreur) > 0)
                $erreur .= " \n ";

            $erreur .= traduction('messages.php.budget_insight.erreur_enregistrement_paiements_comptabilisation', null, [implode(' ,', array_keys($erreur_comptabilisation))]);
        }

        if(strlen($erreur) > 0) {
            Log::error($erreur . '
            Message d\'erreur enregistrement paiement : ' . implode(' \n ', $erreur_paiements) . '\n 
            Message d\'erreur comptabilisation : ' . implode(' \n ', $erreur_comptabilisation) . '\n'
            );
            return response()->json(array('resultat' => false, 'erreur' => $erreur));
        }
		
		$transaction->maj_statut_rapprochement();
		
			
		return response()->json(array('resultat' => true));
	}
	
	/**
	 * 
	 * Enregistre le rapprochement d'un bordereau
	 * 
	 */
	public function enregistre_rapprochement_bordereau() {

		// on va saisir les paiements
		$bordereaux = request()->bordereaux_rapproches;
		
		$transaction = management('budget_insight_transaction', request()->transaction_id);
		
		// on va chercher le compte bancaire de la transaction
		$compte_bancaire_reel = modele('budget_insight_comptes', $transaction->modele->id_account);
		
		if(empty($compte_bancaire_reel->compte_bancaire_id)) {
			
			return response()->json(array('resultat' => false, 'erreur' => traduction('messages.php.budget_insight.compte_eden_introuvable')));
		}
		
		// dans un premier temps on vérifie qu'aucun paiement du bordereau n'est déjà rapproché
		foreach($bordereaux as $id_bordereau) {
			
			// on va chercher les chèques du bordereau
			$paiements = modele('paiement')->where('bordereau_id', $id_bordereau)->get();
			
			foreach($paiements as $paiement) {

				if(empty($paiement))
					return response()->json(array('resultat' => true, 'erreur' => traduction('messages.php.budget_insight.paiement_bordereau_inexistant',null,[$paiement->paiement_id])));
				
				if(!empty($paiement->rapproche))
					return response()->json(array('resultat' => true, 'erreur' => traduction('messages.php.budget_insight.paiement_bordereau_deja_rapproche',null,[$paiement->paiement_id])));
			}
			
		}
		
		foreach($bordereaux as $id_bordereau) {
			
			$bordereau_management = management('bordereau', $id_bordereau);
			
			$paiements = modele('paiement')->where('bordereau_id', $id_bordereau)->get();
			
			foreach($paiements as $paiement) {
				
				$paiement_management = management('paiement', $paiement->id);
				
				$modifications = array(
					
					'rapproche' => 1,
					'transaction_id' => request()->transaction_id,
					// 'date' => $transaction->modele->date,
					'compte_bancaire_id' => $compte_bancaire_reel->compte_bancaire_id,
				);
				
				$erreur = $paiement_management->enregistre_modele($modifications);
				
				// @todo : traiter l'erreur correctement
			}
			
			// on change le statut du bordereau
			$bordereau_management->enregistre_modele(array('statut' => 10));
		}
		
		$transaction->maj_statut_rapprochement();
		
			
		return response()->json(array('resultat' => true));
	}
	
	/**
	 * 
	 * Reporter les transactions
	 * 
	 */
	public function reporter() {
		
		// on va saisir les paiements
		$transactions = request()->transactions;
		$commentaire = request()->commentaire;
		foreach($transactions as $transaction) {
			
			$transaction = management('budget_insight_transaction', $transaction);
			
			$transaction->enregistre(array('reporte_eden' => 1));
			$transaction->enregistre(array('commentaire' => $commentaire));
		}
		
		return response()->json(true);
	}
	
	/**
	 * 
	 * Paramétrage des synchro bancaires
	 * 
	 */
	public function parametrage_synchro() {
		
		$comptes_bancaires_budget_insight = modele('budget_insight_comptes')->get();
		$comptes_bancaires_eden = modele('compte_bancaire')->get();
		
		return view('eden::budget_insight_parametrage_comptes_bancaires', array(
			
			'comptes_bancaires_budget_insight' => $comptes_bancaires_budget_insight,
			'comptes_bancaires_eden' => $comptes_bancaires_eden,
		));
	}
	
	/**
	 * 
	 * Retourne les informations pour un fournisseur choisi (quel article, quelle TVA)
	 * 
	 * On retourne également les 5 dernières factures du fournisseur, ce n'est pas pour budget insight,
	 * Mais pour la saisie des factures fournisseurs via la bannette
	 * 
	 */
	public function informations_pour_fournisseur($id) {
		
		$dernieres_factures = modele('facture_achat')->where('fournisseur_id', $id)->orderBy('id', 'DESC')->take(5)->get();
		
		foreach($dernieres_factures as $derniere_facture) {
			
			$derniere_facture->affiche_lien = management('facture_achat', $derniere_facture->id)->formate_derniere_facture_saisie();
		}
		
		$retour = array(
			
			'automatisation' => modele('automatisation_fournisseur')->where('fournisseur_id', $id)->first(),
			'dernieres_factures' => $dernieres_factures,
		);
		
		return response()->json($retour);
	}
    
}