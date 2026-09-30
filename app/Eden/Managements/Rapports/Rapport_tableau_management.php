<?php

namespace App\Eden\Managements\Rapports;

use App\Eden\Managements\Rapports\Rapport_base_management;
use App\Eden\Managements\Rapports\Serie_management;

use App\Eden\Models\Rapport_parametre;

use App\Exports\Export;
use Mail;

/**
* Gestion des rapports
*/
class Rapport_tableau_management extends Rapport_base_management {
	
	/**
	 * 
	 * On initialise les données du rapport
	 * 
	 */
	function __construct($id_rapport = '', $titre = '', $sous_titre = '') {
		
		parent::__construct($id_rapport, $titre, $sous_titre);
		
		$this->vue_standard = 'rapport_tableau';
        $this->type_rapport = 'tableau';
	}
	
	/**
	 * 
	 * Récupère les données pour la vue
	 * 
	 */
	public function parametres_pour_vue($ajax = false) {
		
		parent::parametres_pour_vue($ajax);

		$this->parametres_pour_vue['props_composant'] = [
            'id_rapport' => $this->id_rapport,
            'titres' => $this->titres,
			'resultats' => $this->resultats,
			'type_element' => $this->rapport_libre->type_element,
			'cacher_colonnes_vides' => !empty($this->parametrage_rapport_libre['cacher_colonnes_vides']) ? true : false,
			'champ_x' => $this->parametrage_rapport_libre['axe_x'],
			'champ_y' => $this->parametrage_rapport_libre['axe_y'] ?? 'serie',
			'export_excel' => !empty($this->parametrage_rapport_libre['export_excel']) ? true : false,
        ];
	}
	
	/**
	 * 
	 * On génère un rapport libre paramétré via l'interface
	 * 
	 */
	public function genere($ajax = false, $filtres_tableau = false) {

        $type_element = $this->rapport_libre->type_element;
        $management_element = management($type_element);

		// les filtres génériques du rapport

		if(!empty($filtres_tableau)){
			$this->options = $filtres_tableau['filtres'];
			$this->valeurs_filtre = $filtres_tableau['valeurs_filtres'];
		} else {
			$this->applique_filtres($management_element);
        	$this->recupere_valeurs_filtres($management_element);
		}
        
		$champ_axe_x = $this->parametrage_rapport_libre['axe_x'];
		$champ_axe_y = $this->parametrage_rapport_libre['axe_y'] ?? 'serie';
		
		$resultats_globaux = collect();
		$champs_managements = [];
		$titres_par_colonne = array(
			$champ_axe_x => [],
			$champ_axe_y => []
		);

		$tableau_multidimensionnel = $champ_axe_x != 'serie' && $champ_axe_y != 'serie';
		
		if(!$tableau_multidimensionnel){

			$series = collect($this->parametrage_rapport_libre['series'])->keyBy('id')->toArray();
		
			$filtres = modele('recherche_avancee')
				->where('type',$this->rapport_libre->id_rapport.'.serie')
				->get()->keyBy('id_cible');

			foreach($filtres as $serie_index => $filtre){
				if(isset($series[$serie_index]))
					$series[$serie_index]['filtre'] = management('recherche_avancee',$filtre->id,$filtre)->structure();
			}

			$champ_valeur = $champ_axe_x == 'serie' ? $champ_axe_y : $champ_axe_x;

			foreach($series as $id => $serie) {
				$serie_management = new Serie_management($serie, $this);
				$resultats = $serie_management->recupere_resultats();
				$titres_par_colonne['serie'][$id] = traduction($serie['index_traduction'].'.nom'); 

				if($serie['type_calcul'] != 'count' && !empty($serie['champ_calcul']))
					$champs_managements[$id] = $management_element->champ($serie['champ_calcul']);

				foreach($resultats as $resultat){
					$resultats_globaux->push([
						$champ_valeur => $resultat->{$champ_valeur},
						'serie' => $id,
						'total_1' => $resultat->total_1,
					]);
				}
			}

			$titres_par_colonne[$champ_valeur] = $serie_management->legende($type_element, $champ_valeur, $resultats_globaux);
		}
		else{
			$serie = $this->parametrage_rapport_libre['serie'];

			$recherche_avancee = modele('recherche_avancee')
				->where('type','rapport')
				->where('id_cible',$this->rapport_libre->id_rapport)
				->first();

			if(!empty($recherche_avancee))
				$serie['filtre'] = management('recherche_avancee', $recherche_avancee->id, $recherche_avancee)->structure();

			$serie_management = new Serie_management($serie, $this);
			$resultats_globaux = $serie_management->recupere_resultats();
			$titres_par_colonne[$champ_axe_x] = $serie_management->legende($type_element, $champ_axe_x, $resultats_globaux);
			$titres_par_colonne[$champ_axe_y] = $serie_management->legende($type_element, $champ_axe_y, $resultats_globaux);

			if($serie['type_calcul'] != 'count' && !empty($serie['champ_calcul']))
				$champ_management = $management_element->champ($serie['champ_calcul']);
		}

		$resultats_globaux = $resultats_globaux->toArray();

		foreach($resultats_globaux as &$resultat) {
			if($tableau_multidimensionnel)
				$resultat['total_affichage'] = !empty($champ_management) ? $champ_management->affiche($resultat['total_1'] ?? 0) : $resultat['total_1'];
			else
				$resultat['total_affichage'] = !empty($champs_managements[$resultat['serie']]) ? $champs_managements[$resultat['serie']]->affiche($resultat['total_1'] ?? 0) : $resultat['total_1'];
		}

		foreach($titres_par_colonne as &$titres) {
			$titres = array_map(function($cle) use ($titres) {
				return [
					'id' => $cle,
					'nom' => $titres[$cle]
				];
			},array_keys($titres));
		}
		
		$this->titres = $titres_par_colonne;
		$this->resultats = $resultats_globaux;
		$this->parametres_pour_vue($ajax);
	}
	
}