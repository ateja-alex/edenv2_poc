<?php

namespace App\Eden\Managements\Rapports;

use App\Eden\Managements\Listes_management;
use App\Eden\Managements\Rapports\Rapport_base_management;
use App\Eden\Managements\Rapports\Serie_management;

use App\Eden\Models\Rapport_parametre;

use App\Exports\Export;
use Mail;

/**
* Gestion des rapports
*/
class Rapport_liste_management extends Rapport_base_management {
	
	/**
	 * 
	 * On initialise les données du rapport
	 * 
	 */
	function __construct($id_rapport = '', $titre = '', $sous_titre = '') {
		
		parent::__construct($id_rapport, $titre, $sous_titre);
		
		$this->vue_standard = 'rapport_liste';
		$this->datatable = true;
		$this->lignes = array();
        $this->type_rapport = 'liste';
	}
	
	public function titres($titres) {
		
		$this->lignes[] = array(
			'donnees' => $titres,
			'classes_css' => 'css_tableau_titre',
			'style' => '',
		);
	}
	
	/**
	 * 
	 * @param string $sous_titre le sous titre (c'est une ligne qui fera toute la largeur)
	 * 
	 */
	public function sous_titre($sous_titre) {
		
		$this->lignes[] = array(
		
			'donnees' => $sous_titre,
			'classes_css' => 'css_tableau_sous_titre',
			'sous_titre' => true,
			'style' => '',
		);
	}
	
	public function ligne($ligne, $parametres = array()) {
		
		$classes_css = '';
		
		if(!empty($parametres['classes_css']))
			$classes_css = $parametres['classes_css'];
		
		$style = '';
		
		if(!empty($parametres['style']))
			$style = $parametres['style'];
		
		$this->lignes[] = array(
		
			'donnees' => $ligne,
			'classes_css' => $classes_css,
			'style' => $style,
		);
	}
	
	/**
	 * 
	 * Ajoute une ligne classique, mais avec une classe css en plus (la même que les titres)
	 * 
	 */
	public function ligne_total($ligne) {
		
		$this->lignes[] = array(
			'donnees' => $ligne,
			'classes_css' => 'css_tableau_titre',
			'style' => '',
		);
	}
	
	/**
	 * 
	 * Désactive datatable pour ce rapport
	 * 
	 */
	public function sans_datatable() {
		
		$this->datatable = false;
	}
	
	/**
	 * 
	 * Récupère les données pour la vue
	 * 
	 */
	public function parametres_pour_vue($ajax = false) {
		
		parent::parametres_pour_vue($ajax);
		
		$this->parametres_pour_vue['lignes'] = $this->lignes;

        if(!empty($this->parametrage_rapport_libre['cacher_colonnes_vides'])) {

            // On récupère la liste des colonnes vides
            $colonnes_vides = [];
            foreach($this->parametres_pour_vue['lignes'] as $numero_ligne => $ligne) {

                // on ne traite pas la première ligne, les titres
                if($numero_ligne == 0)
                    continue;

                foreach($ligne['donnees'] as $numero_colonne => $cellule) {
                    if(!isset($colonnes_vides[$numero_colonne]))
                        $colonnes_vides[$numero_colonne] = true;

                    // On marque la colonne comme non vide
                    if(!empty($cellule)) {
                        $colonnes_vides[$numero_colonne] = false;
                    }
                }
            }

            // On efface les colonnes vides
            foreach($this->parametres_pour_vue['lignes'] as $index_ligne => $ligne) {
                foreach($colonnes_vides as $numero_colonne => $effacer) {
                    if($effacer)
                        unset($this->parametres_pour_vue['lignes'][$index_ligne]['donnees'][$numero_colonne]);
                }
            }
        }
	}
	
	/**
	 * 
	 * On génère un rapport libre paramétré via l'interface
	 * 
	 */
	public function genere($ajax = false) {

		if(empty($this->parametrage_rapport_libre) || !empty($this->forcer_generation)) {
            $render = parent::genere($ajax)->render();
            $this->html = $render;
            return $render;
        }

        $type_element = $this->rapport_libre->type_element;
        $management_element = management($type_element);
		// les filtres génériques du rapport
        $this->applique_filtres($management_element);
        $this->recupere_valeurs_filtres($management_element);

		$champ_axe_x = $this->parametrage_rapport_libre['axe_x'];
		
		// c'est un champ type liste

		$series = $this->parametrage_rapport_libre['series'];
		
		$titres = array('');
		$lignes = array();

        $filtres = modele('recherche_avancee')
            ->where('type',$this->rapport_libre->id_rapport.'.serie')
            ->get()->keyBy('id_cible');

        foreach($filtres as $serie_index => $filtre){
            if(isset($series[$serie_index]))
                $series[$serie_index]['filtre'] = management('recherche_avancee',$filtre->id,$filtre)->structure();
        }
		
		foreach($series as $id => $serie) {

            $serie_management = new Serie_management($serie, $this);
			
			$ligne = array(traduction($serie['index_traduction'].'.nom'));
			
			// on va chercher les données
			$resultats = $serie_management->recupere_resultats();
            $valeurs_champ_x = $serie_management->legende($type_element, $champ_axe_x, $resultats);

            try {
                $champ_management = $management_element->champ($serie['champ_calcul']);
            } catch (\Throwable | \Exception $e) {
                $champ_management = false;
            }

            foreach($valeurs_champ_x as $id_valeur => $valeur) {

                $titres[] = $valeur;
            }
			
			$resultats_definitifs = array();
			
			foreach($resultats as $resultat) {
				
				if(empty($resultat[$champ_axe_x])) {
					
					$resultat[$champ_axe_x] = 0;
				}
				
				if(!isset($resultats_definitifs[$resultat[$champ_axe_x]]))
					$resultats_definitifs[$resultat[$champ_axe_x]] = 0;
				
				$resultats_definitifs[$resultat[$champ_axe_x]] += $resultat['total_1'];
			}
			
			foreach($valeurs_champ_x as $id_valeur => $valeur) {
				
				if(!isset($resultats_definitifs[$id_valeur]))
					$resultats_definitifs[$id_valeur] = 0;

                if($champ_management !== false)
                    $ligne[] = $champ_management->affiche($resultats_definitifs[$id_valeur]);
                else
				    $ligne[] = $resultats_definitifs[$id_valeur];
			}

            $lignes[] = $ligne;

		}

        $titres = array_unique($titres);

        $this->titres($titres);

        foreach ($lignes as $ligne) {
            $this->ligne($ligne);
        }
		
		
		return parent::genere($ajax)->render();
	}
	
}