<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Cache_management;

class Maquette_couleurs_management extends Element_management {

    /**
	 *
	 * @cf Element_management::enregistre()
	 *
	 * A l'enregistrement d'une couleur de maquette on vérifie si une valeur n'existe pas déjà pour celle-ci
	 *
	 */
	public function enregistre($modifications = array(), $modele = false) {
    
        $verification = modele('maquette_couleurs')->where('nom_couleur', $modifications['nom_couleur'])->where('maquette', $modifications['maquette']);

		if(!empty($this->modele->id))
			$verification = $verification->whereNot('id', $this->modele->id);

		$verification = $verification->count();

        if($verification > 0)
            return traduction('messages.php.maquette_couleurs.erreur_nom_couleur_deja_utilise');

        return parent::enregistre($modifications, $modele);
    }

	/**
	 *
	 * On régénére le composant css et on vide le cache
	 *
	 */
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {
		
		parent::methodes_post_modification($modele, $modele_avant, $modifications);
		
        Cache_management::invalide();
		Cache_management::genere_fichier_css();
	}
}