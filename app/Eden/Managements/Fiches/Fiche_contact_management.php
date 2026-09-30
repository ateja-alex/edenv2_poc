<?php

namespace App\Eden\Managements\Fiches;

use App\Eden\Managements\Fiche_management;


/**
 * Gestion des fiches clients
 */
class Fiche_contact_management extends Fiche_management {
		
	/**
	 *
	 * Prépare les données pour la fiche
	 *
	 * @attention si vous utilisez cette méthode dans les classes filles, il faut faire appelle à parent::prepare_donnees_pour_fiche($donnees)
	 *
	 */
	public function prepare_donnees_pour_fiche($donnees = array()) {
		
		$donnees = parent::prepare_donnees_pour_fiche($donnees);
		
		$contact = modele($this->type_element, $this->id_element);

        if(isset($contact->client_id) && !empty($contact->client_id))
		    $donnees['adresses_du_contact'] = modele('adresse')->where('client_id', $contact->client_id)->get();

        elseif(isset($contact->fournisseur_id) && !empty($contact->fournisseur_id))
            $donnees['adresses_du_contact'] = modele('adresse')->where('fournisseur_id', $contact->fournisseur_id)->get();

        elseif(isset($contact->id) && !empty($contact->id))
            $donnees['adresses_du_contact'] = modele('adresse')->where('contact_id', $contact->id)->get();
        else
        	$donnees['adresses_du_contact'] = collect(array());

		return $donnees;
	}

    /**
     * @return Array
     *
     * Permet de récupérer les options des fils arianes
     *
     */
    public function options_fil_ariane($donnees){

        $options_fil_ariane = parent::options_fil_ariane($donnees);

        $options_fil_ariane[] = [
            'id' => 'adresses',
            'ordre' => -5
        ];

        $options_fil_ariane[] = [
            'id' => 'echange',
            'ordre' => -4
        ];

        $options_fil_ariane[] = [
            'id' => 'creation_devis',
            'ordre' => -3
        ];

        $options_fil_ariane[] = [
            'id' => 'telephone',
            'ordre' => -1
        ];

        return $options_fil_ariane;
    }

}
