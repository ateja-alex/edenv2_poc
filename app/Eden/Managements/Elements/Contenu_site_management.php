<?php

namespace App\Eden\Managements\Elements;

use Illuminate\Support\Facades\Log;
class Contenu_site_management extends Element_management {
	
	/**
	 * 
	 * Permet d'éditer le contenu du site
	 * 
	 */
	public function affiche_contenu($texte_id) {
		
		$langue_id = 1;
		
		if(session()->get('langue') !== null) {
			
			if(session()->get('langue') == 'en') {
				
				$langue_id = 2;
			}
		}
		
		$modele = modele('contenu_site')->where('langue_id', $langue_id)->where('texte_id', $texte_id)->first();
        
		if(session()->get('utilisateur_eden') !== null && isset($modele->texte))
			return '<span class="js_edition_contenu_site">'.$modele->texte.' <span onClick="return eden_edition_contenu_site('.$modele->id.');" style="background: #2d2d2d; padding: 3px; color: white; font-size: 10px;">éditer</span></span>';
		elseif(isset($modele->texte))
			return $modele->texte;
        else
            return '';
	}
	
	
}