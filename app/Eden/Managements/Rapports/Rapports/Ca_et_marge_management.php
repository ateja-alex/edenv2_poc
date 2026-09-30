<?php

namespace App\Eden\Managements\Rapports\Rapports;


use App\Eden\Managements\Calcul_gescom_management;
use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_courbe_management;

use Illuminate\Http\Request;

/**
 *
 * Gestion des rapports
 *  
 */
class Ca_et_marge_management extends Rapports_management {

    /**
     *
     * On initialise les données du rapport
     *
     */
    function __construct() {
        $this->rapport = new Rapport_courbe_management();
    }
    
	/**
	 * 
	 * On affiche les CA sur les 12 derniers mois
	 * 
	 */	
    public function genere($ajax = false) {

		// on récupère les différents filtres
		$dates = $this->dates_mensuelles($this->rapport);
		$periodicite = $this->periodicite($this->rapport, $dates);
		$entites = $this->entites($this->rapport);
		// $fournisseur = $this->fournisseur($this->rapport);
		$champs = $this->champs($this->rapport, [management('facture_vente')->champ('client_id')]);

		// On génère le titre
        $this->rapport->titre = traduction('rapport.ca_et_marge.titre_affichage',null,[formate_date('d/m/Y', $dates['date_debut']),formate_date('d/m/Y', $dates['date_fin'])]);

		if(!empty($champs['client_id']))
			$this->rapport->titre .= " pour le client ".modele('client', $champs['client_id'])->nom;

		if(empty($entites)) {

			$entites = modele('entite')->get()->pluck('id');
		}

		// on va chercher les résultats N et N-1
		$date_debut_NMoins1 = date('Y-m-d', strtotime($dates['date_debut'].' -1 year'));
		$calcul = new Calcul_gescom_management();
		$calcul->filtre_dates_quotidiennes(array('date_debut' => $date_debut_NMoins1, 'date_fin' => $dates['date_fin']));	
		$calcul->groupe_par(in_array($periodicite['periodicite'], array('mensuelle', 'trimestrielle', 'semestrielle', 'annuelle')) ? 'date_mensuelle' : 'date');
		
		/**
		@note frédéric 28/04/2021 je commente cette partie du code, car elle pose problème dans la requete 
		étant donné que la colonne fournisseur_id n'existe pas sur les tables ?!!
		*/
		// if($fournisseur != "null")
			// $calcul->where('fournisseur_id', $fournisseur);

		if(isset($champs['client_id']))
			$calcul->where('client_id', $champs['client_id']);

		$ca = $calcul->plus('facture_vente')
					 ->moins('avoir_vente')
					 ->resultat();

		$this->rapport->serie('N');		
		$this->rapport->serie('N-1');
		$this->rapport->serie(traduction('rapport.ca_et_marge.marge_brute'));
		$this->rapport->serie(traduction('rapport.ca_et_marge.marge_nette'));
		foreach($periodicite['periodes'] as $periode) {

			$montant_total = 0;
			$montant_total_annee_precedente = 0;

			foreach($ca as $date => $montant) {

				if($date >= $periode['date_debut'] && $date <= $periode['date_fin'])
					$montant_total += $montant;
				
				if($date >= date('Y-m-d', strtotime($periode['date_debut_n_moins_1'])) && $date <= date('Y-m-d', strtotime($periode['date_fin_n_moins_1']))) {
					$montant_total_annee_precedente += $montant;
				}
			}

			$this->rapport->valeur('N', $montant_total);
			$this->rapport->valeur('N-1', $montant_total_annee_precedente);

			$marges = modele('projet')
							->where('cree_le', '>=', $periode['date_debut'])
							->where('cree_le', '<=', $periode['date_fin'])
							->select(\DB::raw('SUM(marge_brute) as somme_marge_brute, SUM(marge_nette) as somme_marge_nette'))
							->first();

			$this->rapport->valeur(traduction('rapport.ca_et_marge.marge_brute'), (!empty($marges->somme_marge_brute) ? $marges->somme_marge_brute : 0));
			$this->rapport->valeur(traduction('rapport.ca_et_marge.marge_nette'), (!empty($marges->somme_marge_nette) ? $marges->somme_marge_nette : 0));
		}		
		
		return $this->rapport->genere($ajax);
    }
	
	
	
	
	
}