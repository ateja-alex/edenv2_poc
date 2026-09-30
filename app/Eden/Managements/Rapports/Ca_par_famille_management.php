<?php

namespace App\Eden\Managements\Rapports;


use App\Eden\Managements\Calcul_gescom_management;
use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_liste_management;
use App\Eden\Managements\Cache_management;

use Illuminate\Http\Request;

/**
* Gestion des rapports
*/
class Ca_par_famille_management extends Rapports_management {
	
	/**
	 * 
	 * On calcule le CA par mois
	 * 
	 */	
    public function genere($ajax = false) {
		
		// on stocke toutes les sous familles 
		$this->sous_familles = array();
		
		$familles = modele('famille')->get();
		
		foreach($familles as $famille) {
			
			if(!isset($this->sous_familles[$famille->parent_id]))
				$this->sous_familles[$famille->parent_id] = array();
			
			if(!isset($this->sous_familles[$famille->id]))
				$this->sous_familles[$famille->id] = array();
			
			$this->sous_familles[$famille->parent_id][$famille->id] = $famille;
		}
		
		$rapport = new Rapport_liste_management('ca_par_famille', 'CA par famille');
		
		// on récupère les différents filtres
		$dates = $this->dates_mensuelles($rapport);
		$entites = $this->entites($rapport);
		
		$rapport->titre = traduction('rapport.ca_par_famille.titre_affichage',null,[formate_date('d/m/Y', $dates['date_debut']),formate_date('d/m/Y', $dates['date_fin'])]);

		// on fait le calcul
		// on va chercher le CA mensuel
		$calcul = new Calcul_gescom_management();
		
		$ca_par_famille = $calcul->documents_valides_uniquement(false)
						->filtre_entites($entites)
						->filtre_dates_mensuelles($dates)
						->plus('facture_vente')
						->groupe_par('famille')
						->resultat();
						
		// on récupère toutes les familles (perf)
		
						
		// il faut recalculer le CA par famille en remontant aux familles parents
		$ca_par_famille_cumul = array();
		
		$famille_management = management('famille');
		
		foreach($ca_par_famille as $famille_id => $ca) {
			
			// la famille elle même
			if(!isset($ca_par_famille_cumul[$famille_id]))
				$ca_par_famille_cumul[$famille_id] = 0;
			
			$ca_par_famille_cumul[$famille_id] += $ca;
			
			$familles_parents = $famille_management->familles_parent(modele('famille', $famille_id));
			
			foreach($familles_parents as $famille_parent_id) {
				
				if(!isset($ca_par_famille_cumul[$famille_parent_id]))
					$ca_par_famille_cumul[$famille_parent_id] = 0;
				
				$ca_par_famille_cumul[$famille_parent_id] += $ca;
			}
		}
		
		// titre du rapport
		$rapport->titres(array('Famille', array(traduction('rapport.ca_par_famille.ca_ht_periode'), 'css_montant')));
		
		// on va enregistrer un CA par sous famille, pour le graphique
		$rapport->ca_pour_sous_familles = array();
		
		// puis finalement, on affiche les familles
		$familles = modele('famille')->where('parent_id', 0)->orderBy('nom')->get();
		
		foreach($familles as $famille) {
			
			$this->contenu_famille($famille, $famille_management, $ca_par_famille_cumul, $rapport);
		}
		
		//dd($rapport->ca_pour_sous_familles);
		
		return $rapport->genere($ajax);
    }
	
	protected function contenu_famille($famille, $famille_management, $ca_par_famille_cumul, $rapport, $rang = 0) {
		
		// on crée le sous titre
		$espaces = '';
		for($i=0; $i<= $rang; $i++) {
			
			$espaces .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		}
		
		if(isset($ca_par_famille_cumul[$famille->id]))
			$ca_famille = $ca_par_famille_cumul[$famille->id];
		else
			$ca_famille = 0;
		
		$rapport->ligne(array(
			$espaces.$famille->nom.' <span class="css__lien" onClick="$(\'#famille_id_ca_par_famille\').val('.$famille->id.'); actualise_rapport(\'ca_par_famille\')"><span class="fa fa-search-plus"></span></span>', 
			array(montant($ca_famille), 'css_montant'),
		));
			
		// $sous_familles = $famille_management->sous_familles($famille->id);
		$sous_familles = $this->sous_familles[$famille->id];
		
		// les sous familles
			
		foreach($sous_familles as $id_sous_famille => $sous_famille) {
			
			$rang++;
			
			// le modèle de la sous famille
			$modele_sous_famille = modele('famille', $id_sous_famille);
			
			if(isset($ca_par_famille_cumul[$id_sous_famille]))
				$ca_famille = $ca_par_famille_cumul[$id_sous_famille];
			else
				$ca_famille = 0;
			
			if(!empty(request()->famille_id)) {
				
				if(request()->famille_id == $famille->id) {
					
					if(!isset($rapport->ca_pour_sous_familles[$famille->id]))
						$rapport->ca_pour_sous_familles[$famille->id] = array();
					
					$rapport->ca_pour_sous_familles[$famille->id][$modele_sous_famille->nom] = $ca_famille;
				}
			}
			else {
				
				if(!isset($rapport->ca_pour_sous_familles[$famille->id]))
					$rapport->ca_pour_sous_familles[$famille->id] = array();
				
				$rapport->ca_pour_sous_familles[$famille->id][$modele_sous_famille->nom] = $ca_famille;
			}
			
			
			$this->contenu_famille($modele_sous_famille, $famille_management, $ca_par_famille_cumul, $rapport, $rang);
			
			$rang--;
		}
		
		
	}
	
	
}