<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Champs_liste_formatee;
use App\Eden\Models\Champ_libre;

class Lead_management extends Element_management {

    public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();

        $liste_options[] = 'echange';
        $liste_options[] = 'mail';

        return $liste_options;
    }
	
	
	/**
	 * 
	 * C'est une méthode de l'interface lead, quand ils cliquent sur "oui" (a priori pour demander s'ils sont toujours interessés)
	 * 
	 * Cette méthode doit être surchargée pour gérer la logique métier
	 * 
	 */
	public function rsvp_oui() {
		
		return array('texte' => traduction('messages.php.lead.rsvp_oui'));
	}
	
	/**
	 * 
	 * C'est une méthode de l'interface lead, quand ils cliquent sur "non" (a priori pour demander s'ils sont toujours interessés)
	 * 
	 * Cette méthode doit être surchargée pour gérer la logique métier
	 * 
	 */
	public function rsvp_non() {
		
		return array('texte' => traduction('messages.php.lead.rsvp_non'));
	}
	
	/**
	 * 
	 * C'est une méthode de l'interface lead, quand ils cliquent sur "plustard" (a priori pour demander s'ils sont toujours interessés)
	 * 
	 * Cette méthode doit être surchargée pour gérer la logique métier
	 * 
	 */
	public function rsvp_plustard() {
		
		return array('texte' => traduction('messages.php.lead.rsvp_plustard'));
	}
	
	/**
	 * 
	 * C'est une méthode de l'interface lead, quand ils remplissent + d'informations suite à avoir cliqué sur le lien
	 * 
	 * Cette méthode doit être surchargée pour gérer la logique métier
	 * 
	 */
	public function maj_reponse_rsvp($formulaire) {
		
		return array('texte' => traduction('messages.php.lead.maj_reponse_rsvp'));
	}
}
