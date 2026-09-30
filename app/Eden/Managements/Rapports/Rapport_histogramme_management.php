<?php

namespace App\Eden\Managements\Rapports;

use App\Eden\Models\Rapport_parametre;
use App\Eden\Managements\Rapports\Rapport_base_management;




/**
* Gestion des rapports
*/
class Rapport_histogramme_management extends Rapport_courbe_et_histogramme_management {
	
	/**
	 * 
	 * On initialise les données du rapport
	 * 
	 */
	function __construct($id_rapport = '', $titre = '', $sous_titre = '') {
		
		parent::__construct($id_rapport, $titre, $sous_titre);
		
		$this->vue_standard_js = 'rapport_histogramme';
		$this->legende = array();
		$this->series = array();
	}
	
	/**
	 * 
	 * Ajoute une valeur à la légende
	 * 
	 */
	public function legende($valeur) {
		
		$this->legende[] = $valeur;
		
		return true;		
	}
	
	/**
	 * 
	 * Crée une nouvelle série de données
	 * 
	 */
	public function serie($nom) {
		
		$this->series[$nom] = array();
		
		return true;		
	}
	
	
	/**
	 * 
	 * Ajoute une valeur à la série
	 * 
	 */
	public function valeur($nom, $valeur, $nom_cumule = false) {

        if($nom_cumule === false)
		    $this->series[$nom][] = $valeur;
        else
            $this->series[$nom][$nom_cumule] = $valeur;

        return true;
	}
	
	/**
	 * 
	 * Récupère les données pour la vue
	 * 
	 */
	public function parametres_pour_vue($ajax = false) {
		
		parent::parametres_pour_vue($ajax);
        
        if(isset($this->parametrage_rapport_libre['groupe_par']))
            $this->parametres_pour_vue['props_composant']['groupe_par'] = $this->parametrage_rapport_libre['groupe_par'];
	}
	
	
}