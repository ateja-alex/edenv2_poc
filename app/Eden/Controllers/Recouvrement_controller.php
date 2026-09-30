<?php

namespace App\Eden\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;


class Recouvrement_controller extends Controller {


	private $recouvrement_management;

	public function __construct() {

		$this->recouvrement_management = app('App\Eden\Managements\Recouvrement_management');
	}

	/**
	 * 
	 * Page d'accueil du recouvrement / tableau de bord
	 * 
	 */
    public function index() {
		
		
		// groupes de clients pour le recouvrement
		$groupes_recouvrement = modele('groupe_recouvrement')->orderBy('nom')->get();
		
		$sans_groupe = new \StdClass;
		$sans_groupe->id = 0;
		$sans_groupe->nom = "Sans groupe";
		
		// les clients sans groupe de recouvrement
		$groupes_recouvrement->prepend($sans_groupe);
		
		// les clients avec un groupe de recouvrement
		foreach($groupes_recouvrement as $groupe_recouvrement) {
			
			// on ajoute les null
			if($groupe_recouvrement->id == 0) {

				$groupe_recouvrement->documents = modele('facture_vente')
													->where('solde_document_ttc', '>', 0)
													->where('valide', '=', 1)
													->join('client', 'facture_vente.client_id', 'client.id')
													->where(function($query) use ($groupe_recouvrement) {

														$query->where('groupe_recouvrement_id', $groupe_recouvrement->id);
														$query->orWhereNull('groupe_recouvrement_id');
													})
													->select('facture_vente.*')
													->get();

			}
			else {

				$groupe_recouvrement->documents = modele('facture_vente')
													->where('solde_document_ttc', '>', 0)
													->where('valide', '=', 1)
													->join('client', 'facture_vente.client_id', 'client.id')
													->where('groupe_recouvrement_id', $groupe_recouvrement->id)
													->select('facture_vente.*')
													->get();
			}
			
			foreach($groupe_recouvrement->documents as $document) {
				
				$client_management = management('client', $document->client_id);
				$facture_management = management('facture_vente', $document->id);

				$document->client = $client_management->affiche_lien();
				$document->modalite_paiement_id = $facture_management->champ('modalite_paiement_id')->affiche();
			}
		}

		// on va calculer les indicateurs
		
		// montant dû & nombre de documents
		$indicateurs = modele('facture_vente')
							->select(DB::raw("SUM(solde_document_ttc) as montant_du, COUNT(facture_vente.id) as nombre_documents"))
							->where('solde_document_ttc', '>', 0)
							->first();
							
		$montant_du = $indicateurs->montant_du;
		$nombre_documents = $indicateurs->nombre_documents;
		
		// nombre de relances à effectuer
		$relances_a_effectuer = modele('facture_vente')
							->where('solde_document_ttc', '>', 0)
							->where(function($requete) {

								for($i=1; $i<=4; $i++) {

									$requete->orWhere(function($requete_tmp) use ($i) {

										$requete_tmp->where('relance_'.$i.'_ok', '!=', 1);
										$requete_tmp->where('relance_'.$i, '<=', date('Y-m-d'));
									});
								}
							})
							->count();
		// On affiche la vue
		return view('eden::recouvrement', [
			'groupes_recouvrement' => $groupes_recouvrement,
			'montant_du' => $montant_du,
			'nombre_documents' => $nombre_documents,
			'relances_a_effectuer' => $relances_a_effectuer,
		]);
	}
	
	/**
	 * 
	 * 
	 * On retourne les factures qui ne sont pas encore payées par entité
	 * 
	 */
	public function recouvrement_sans_groupe() {

		$type_relance_recouvrement = modele('type_relance_recouvrement')->get();
		$entites = modele('entite')->get();
		$filtre_recouvrement_entite = parametre_utilisateur('filtre_recouvrement_entite');

		
		if($filtre_recouvrement_entite == null) {

			parametre_utilisateur('filtre_recouvrement_entite', $entites->first()->id);
			$filtre_recouvrement_entite = $entites->first()->id;
		}
		
		$groupes_recouvrement = $this->recouvrement_management->retourne_factures_avec_dates_de_relance($filtre_recouvrement_entite);
		$indicateurs = $this->recouvrement_management->indicateurs_recouvrement($filtre_recouvrement_entite);
		$creances_retard = $this->recouvrement_management->creances_retard($indicateurs['montant_du'], $filtre_recouvrement_entite);
		$ca_des_30_derniers_jours = $this->recouvrement_management->ca_des_30_derniers_jours($indicateurs['montant_du'], $filtre_recouvrement_entite);

		
		return view('eden::recouvrement_sans_groupe', [
			'groupes_recouvrement' => $groupes_recouvrement,
			'type_relance_recouvrement' => $type_relance_recouvrement,
			'entites' => $entites,
			'entite_par_defaut' => $filtre_recouvrement_entite,
			'indicateurs' => $indicateurs,
			'creances_retard' => $creances_retard,
			'ca_des_30_derniers_jours' => $ca_des_30_derniers_jours,

		]);
	}


	/**
	 * 
	 * 
	 * On ajoute une relance à une facture
	 * 
	 */
	public function ajoute_relance(Request $formulaire) {


		$type_element = $formulaire->type_element;
		$document = modele($type_element, $formulaire->id);
		$client_id = modele('client', $document->client_id)->id;

		$donnees = [
			'date' => date('Y-m-d'),
			'client_id' => $client_id,
			$type_element.'_id' => $formulaire->id,
			'type_relance' => $formulaire->type,
			'type_element' => $type_element, 
		];

		$retour = management('relance_recouvrement')->enregistre($donnees);

		if($retour === true) {

			$filtre_recouvrement_entite = parametre_utilisateur('filtre_recouvrement_entite');
			$groupes_recouvrement = $this->recouvrement_management->retourne_factures_avec_dates_de_relance($filtre_recouvrement_entite);

			return response()->json(['success' => true, 'groupes_recouvrement' => $groupes_recouvrement]);
		}

		return response()->json(['success' => false]);
	}

	/**
	 * 
	 * 
	 * Appel ajax pour supprimer relance
	 * 
	 */
	public function supprimer_relance(Request $formulaire) {

		modele('relance_recouvrement')->where('id', $formulaire->id)->delete();

		$filtre_recouvrement_entite = parametre_utilisateur('filtre_recouvrement_entite');
		$groupes_recouvrement = $this->recouvrement_management->retourne_factures_avec_dates_de_relance($filtre_recouvrement_entite);

		return response()->json(['success' => true, 'groupes_recouvrement' => $groupes_recouvrement]);
	}

	/**
	 * 
	 *  
	 * Appel ajax lorsqu'on change d'entité, on charge les bonnes données
	 * 
	 */
	public function changement_entite_pour_recouvrement(Request $formulaire) {

		parametre_utilisateur('filtre_recouvrement_entite', $formulaire->entite);

		$groupes_recouvrement = $this->recouvrement_management->retourne_factures_avec_dates_de_relance($formulaire->entite);
		$indicateurs = $this->recouvrement_management->indicateurs_recouvrement($formulaire->entite);
		$creances_retard = $this->recouvrement_management->creances_retard($indicateurs['montant_du'], $formulaire->entite);
		$ca_des_30_derniers_jours = $this->recouvrement_management->ca_des_30_derniers_jours($indicateurs['montant_du'], $formulaire->entite);

		return response()->json(['success' => true, 'groupes_recouvrement' => $groupes_recouvrement, 'indicateurs' => $indicateurs, 'creances_retard' => $creances_retard, 'ca_des_30_derniers_jours' => $ca_des_30_derniers_jours]);
	}

	public function calcule_indicateurs_par_periode(Request $request) {


		$entite = parametre_utilisateur('filtre_recouvrement_entite');

		$creances_retard = $this->recouvrement_management->creances_retard($request->montant_du, $entite);
		$ca_des_30_derniers_jours = $this->recouvrement_management->ca_des_30_derniers_jours($request->montant_du, $entite);

		return response()->json(['success' => true, 'creances_retard' => $creances_retard, 'ca_des_30_derniers_jours' => $ca_des_30_derniers_jours]);
	}

}
