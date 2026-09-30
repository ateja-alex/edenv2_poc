<?php

namespace App\Eden\Managements;

use DB;
use App\Eden\Managements\Calcul_gescom_management;

class Recouvrement_management { 


    /**
     * 
     * Retourne les factures par entite qui ne sont pas encore payées avec les relances
     * 
     */
	public function retourne_factures_avec_dates_de_relance($entite_id) {

        $date_du_jour = date('Y-m-d');
		$date_du_jour_date_time = new \DateTime($date_du_jour);
		
		$date_a_utiliser_pour_recouvrement = management('facture_vente')->date_a_utiliser_pour_recouvrement();
		$date_a_utiliser_pour_recouvrement_acompte_vente = management('acompte_vente')->date_a_utiliser_pour_recouvrement();

		// on va chercher toutes les factures relancées récemment
		$parametre_masquer_les_factures = fonctionnalite('recouvrement_ne_pas_afficher_les_factures_relancees_depuis_x_jours');
		
		$factures_relancees_recemment = [];
		
		if(!empty($parametre_masquer_les_factures)) {
			
			$factures_relancees_recemment = modele('relance_recouvrement')->where('date', '>=', date('Y-m-d', strtotime("now -$parametre_masquer_les_factures days")))->get()->pluck('facture_vente_id')->toArray();
		}

		$factures_recouvrement = modele('facture_vente')
					->join('client', 'client.id', 'facture_vente.client_id')
					->where('solde_document_ttc', '>', 0)
					->where($date_a_utiliser_pour_recouvrement, '<', $date_du_jour)
					->where('valide', '=', 1)
					->whereNotIn('facture_vente.id', $factures_relancees_recemment)
					->where(function($requete) {
						$requete->where('regle', 0)->orWhereNull('regle');
					})
					->where(function($requete) {
						$requete->where('annulee_par_avoir', 0)->orWhereNull('annulee_par_avoir');
					})
					->select('facture_vente.*', 'client.id as client_id','client.nom as client_nom', 'client.prenom as client_prenom');

		$acompte_recouvrement = modele('acompte_vente')
					->join('client', 'client.id', 'acompte_vente.client_id')
					->where('solde_document_ttc', '>', 0)
					->where($date_a_utiliser_pour_recouvrement_acompte_vente, '<', $date_du_jour)
					->where('valide', '=', 1)
					->where(function($requete) {
						$requete->where('regle', 0)->orWhereNull('regle');
					})

					->select('acompte_vente.*', 'client.id as client_id','client.nom as client_nom', 'client.prenom as client_prenom');

		if(!empty($entite_id))
			$factures_recouvrement = $factures_recouvrement->where('facture_vente.entite_id', $entite_id)->get();
		else
			$factures_recouvrement = $factures_recouvrement->get();
					
		if(!empty($entite_id))
			$acompte_recouvrement = $acompte_recouvrement->where('acompte_vente.entite_id', $entite_id)->get();
		else
			$acompte_recouvrement = $acompte_recouvrement->get();

		$groupes_recouvrement = $factures_recouvrement->merge($acompte_recouvrement);

		foreach($groupes_recouvrement as $facture) {
			
			$client = management('client', $facture->client_id);
			
			$tags = '';
			
			if(!empty($client->modele->portefeuille_payline) || !empty($client->modele->portefeuille_stripe) || !empty($client->modele->portefeuille_payzen_cb)) {
				
				$tags .= '<img src="'.asset('eden/images/cb.png').'" style="width: 16px" /> ';
			}
			
			if(!empty($client->modele->portefeuille_payzen_prelevement)) {
				
				$tags .= '<img src="'.asset('eden/images/sepa-euro.jpg').'" style="width: 16px" /> ';
			}
			
			$facture->client_tags = $tags;
		}
		
		// on récupère tous les ID de facture en recouvrement
		$ids_factures = $groupes_recouvrement->pluck('id')->toArray();
		
		// on va chercher les relances
		$relance_recouvrement = modele('relance_recouvrement')->whereIn('facture_vente_id', $ids_factures)->get();
		
		$groupes_recouvrement->each(function ($item, $key) use($relance_recouvrement, $date_du_jour_date_time, $date_a_utiliser_pour_recouvrement, $groupes_recouvrement) {

			$date_de_reglement = new \DateTime($item->$date_a_utiliser_pour_recouvrement);
			$jour_de_retard = $date_du_jour_date_time->diff($date_de_reglement)->days;
			$item->jours_de_retard = $jour_de_retard;
			$item->relances =  $relance_recouvrement->whereIn('facture_vente_id', [$item->id]);

		});
		
		$groupes_recouvrement = collect($groupes_recouvrement->sortBy('date_de_reglement')->values()->all());

		foreach ($groupes_recouvrement as $index => &$recouvrement) {
			
			$recouvrement->type_document = $recouvrement->getTable();
		}

		return $groupes_recouvrement;
    }

	/**
     * 
     * Retourne les indicateurs (nombre de document + somme total) des factures qui ne sont pas encore payées par entité
     * 
     */
    public function indicateurs_recouvrement($entite_id) {
		
		$date_du_jour = date('Y-m-d');
		
		$date_a_utiliser_pour_recouvrement = management('facture_vente')->date_a_utiliser_pour_recouvrement();

		$retour =  modele('facture_vente')
				->select(DB::raw("SUM(solde_document_ttc) as montant_du, COUNT(facture_vente.id) as nombre_documents"))
				->where('solde_document_ttc', '>', 0)
				->where($date_a_utiliser_pour_recouvrement, '<=', $date_du_jour)
				->where('valide', '=', 1)
				->zero_ou_null('regle')
				->zero_ou_null('annulee_par_avoir');
				
		if(!empty($entite_id))
			$retour = $retour->where('facture_vente.entite_id', $entite_id)->first();
		else
			$retour = $retour->first();
		
		
		if($retour->montant_du == null) 
			$retour->montant_du = 0;
		
			
		return $retour;
	}

	/**
     * 
     * Créance retards 
     * 
     */
	public function creances_retard($montant_du, $entite) {

		$date_du_jour = date('Y-m-d');
		$date_n_moins_1 = date('Y-m-d', strtotime('now -1year'));

		$dates = ['date_debut' => $date_du_jour, 'date_fin' => $date_n_moins_1];
		$calcul = new Calcul_gescom_management();

		if(!empty($entite_id))
			$calcul = $calcul->filtre_entites([$entite]);
		
						
		$ca_anneee = $calcul->a_partir_de($date_n_moins_1)
			->jusqu_a($date_du_jour)
			->plus('facture_vente')
			->moins('avoir_vente')
			->resultat();
			
		$creances_retard = $montant_du  * 365;

		if($ca_anneee > 0) 
			$creances_retard = ($montant_du / $ca_anneee) * 365;

		return floatval($creances_retard);
	}

	/**
     * 
     * Créance retards 
     * 
     */
	public function ca_des_30_derniers_jours($montant_du, $entite) {

		$date_du_jour = date('Y-m-d');
		$date_j_moins_30 = date('Y-m-d', strtotime('now -1month'));

		$calcul = new Calcul_gescom_management();
		
		if(!empty($entite_id))
			$calcul = $calcul->filtre_entites([$entite]);

		$ca_30_jours = $calcul->a_partir_de($date_j_moins_30)
			->jusqu_a($date_du_jour)
			->plus('facture_vente')
			->moins('avoir_vente')
			->resultat();

		$ca_des_30_derniers_jours = $montant_du * 100;

		if($ca_30_jours > 0) 
			$ca_des_30_derniers_jours = ($montant_du / $ca_30_jours) * 100;

		return floatval($ca_des_30_derniers_jours);
	}
}
