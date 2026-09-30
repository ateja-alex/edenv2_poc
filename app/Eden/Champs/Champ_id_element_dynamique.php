<?php

namespace App\Eden\Champs;

use App\Eden\Variables;

class Champ_id_element_dynamique extends Champ {

    public string $nom_composant = 'champ-selection-element';

    public function cree(){

		$this->attr('type_element_origine', $this->modele->type_element);

        $this->attr('type_element', $this->modele->type_element.'.'.$this->modele->contenu, 1);

        if(!empty($this->modele->desactiver_creation_a_la_volee))
            $this->attr('desactiver_creation_a_la_volee', $this->modele->desactiver_creation_a_la_volee);
        
        if(!empty($this->modele->format_champ))
            $this->attr('format_champ', $this->modele->format_champ);

        $champ_type_element_dynamique = $this->champ_type_element_dynamique();

		return $this->cree_champ();
	}

    /**
     *
     * Crée le filtre pour les listes
     *
     */
    public function cree_filtre_pour_liste($valeurs = false, $filtre = false) {

        $champ_type_element_dynamique = $this->champ_type_element_dynamique();

        if(empty($champ_type_element_dynamique))
            return parent::cree_filtre_pour_liste($valeurs,$filtre);

        return $champ_type_element_dynamique->champ->cree_filtre_pour_liste($valeurs,$filtre);
    }

    public function champ_type_element_dynamique(){

        $contenu = $this->modele->contenu;

        if(empty($contenu))
            return null;

        return champ_libre($this->modele->type_element,$contenu);
    }

    /**
	*
	* Affiche proprement la valeur d'un champ
	*
	*/
	public function affiche_liste($element, $colonne){

        $colonne_valeur = $this->modele->alias_champ ?? $this->modele->nom_sql;
        $modele = $element->{"modele_".$colonne_valeur};

        if(empty($modele))
            return null;

        $this->modele->type_element_ajax = $modele->getTable();

        $champ_selection_dynamique = new Champ_recherche_element($this->modele, false);

        return $champ_selection_dynamique->affiche_liste($element, $colonne);
	}
}