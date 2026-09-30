<?php

namespace App\Eden\Managements\Rapports\Rapports;


use App\Eden\Managements\Calcul_gescom_management;
use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_liste_management;

use Illuminate\Http\Request;

class Erreur_rapprochement_management extends Rapports_management {

    /**
     *
     * On initialise les données du rapport
     *
     */
    function __construct() {
        $this->rapport = new Rapport_liste_management();
    }
    
	/**
	 * 
	 * On compare les objectifs avec le réalisé pour chaque entité
	 * 
	 */
    public function genere($ajax = false) {
        
		// on récupère les différents filtres
		$dates = $this->dates_mensuelles($this->rapport);
		$this->export_excel($this->rapport);

        $titres = array('#','erreur','objet', 'date');

        foreach($titres as &$titre){
            if($titre != '#')
                $titre = traduction('rapport.erreur_rapprochement.colonnes.'.$titre);
        }

		$this->rapport->titres($titres);
		
		// lien compte bancaire réel / eden
		$comptes_bancaires = modele('budget_insight_comptes')->get()->pluck('compte_bancaire_id', 'id')->toArray();
		
		// les paiements rapprochés, sans compte bancaire
		$paiements_sans_compte_bancaire = modele('paiement')
			->select(['paiement.*', 'id_account'])
			->join('budget_insight_transaction', 'paiement.transaction_id', 'budget_insight_transaction.id')
			->where('rapproche', 1)
			->zero_ou_null('compte_bancaire_id')
			->where('paiement.date', '>=', $dates['date_debut'])
			->where('paiement.date', '<=', $dates['date_fin'])
			->orderBy('paiement.date')
			->get();
		
		foreach($paiements_sans_compte_bancaire as $paiement) {
			
			$compte_budget_insight = modele('budget_insight_comptes', $paiement->id_account);
			$compte_bancaire = modele('compte_bancaire', $compte_budget_insight->compte_bancaire_id);
			
			$this->rapport->ligne(array(
				
				$paiement->id,
                traduction('rapport.erreur_rapprochement.paiement_rapproche_non_lie',null,[$compte_bancaire->nom.', #'.$compte_bancaire->id]),
				management('paiement', $paiement->id)->affiche(),
				formate_date('d/m/Y', $paiement->date),
			));
		}
		
		// les paiements rapprochés, avec le mauvais compte bancaire
	
		
		foreach($comptes_bancaires as $budget_insight_id => $compte_bancaire_id) {
			
			if(empty($compte_bancaire_id))
				continue;
			
			$compte_budget_insight = modele('budget_insight_comptes', $budget_insight_id);
			$compte_bancaire = modele('compte_bancaire', $compte_bancaire_id);
			
			$paiements_sans_compte_bancaire = modele('paiement')
				->select('paiement.*')
				->join('budget_insight_transaction', 'paiement.transaction_id', 'budget_insight_transaction.id')
				->where('rapproche', 1)
				->where('paiement.compte_bancaire_id', $compte_bancaire_id)
				->where('budget_insight_transaction.id_account', '!=', $budget_insight_id)
				->where('paiement.date', '>=', $dates['date_debut'])
				->where('paiement.date', '<=', $dates['date_fin'])
				->orderBy('paiement.date')
				->get();
			
			foreach($paiements_sans_compte_bancaire as $paiement) {
				
				$this->rapport->ligne(array(
					
					$paiement->id,
					traduction('rapport.erreur_rapprochement.paiement_rapproche_mauvais_compte_bancaire').' : '.$compte_budget_insight->original_name.', Eden : '.$compte_bancaire->nom,
					management('paiement', $paiement->id)->affiche(),
					formate_date('d/m/Y', $paiement->date),
				));
			}
		}
		
		
		
		return $this->rapport->genere($ajax);
    }
	
	
	
}