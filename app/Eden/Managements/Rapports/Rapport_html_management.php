<?php

namespace App\Eden\Managements\Rapports;

use App\Exports\Export;
use Mail;

/**
* Gestion des rapports
*/
class Rapport_html_management extends Rapport_base_management {
	
	/**
	 * 
	 * On initialise les données du rapport
	 * 
	 */
	function __construct($id_rapport = '', $titre = '', $sous_titre = '') {
		
		parent::__construct($id_rapport, $titre, $sous_titre);
		
		$this->vue_standard = 'rapport_html';
		$this->html = array();
	}
	
	/**
	 * 
	 * Ajoute du html au rapport
	 * 
	 */
	public function html($html) {
		
		$this->html[] = $html;
	}
	
	/**
	 * 
	 * Récupère les données pour la vue
	 * 
	 */
	public function parametres_pour_vue($ajax = false) {
		
		parent::parametres_pour_vue($ajax);
		
		$this->parametres_pour_vue['html'] = $this->html;
		$this->parametres_pour_vue['js'] = $this->js ?? null;
	}

    /**
     *
     * Génère le html du rapport
     *
     */
    public function genere($ajax = false) {

        $this->parametres_pour_vue($ajax);
        $this->html = parent::genere($ajax)->render();

        return true;

    }
}