<?php

namespace App\Eden\Champs;

use App\Eden\Variables;

class Champ_telephone extends Champ_texte {

    public string $nom_composant = 'champ-telephone-indicateur';

    public function cree(){

		if(!empty($this->modele->nom))
            $this->attr('nom', $this->modele->nom);

        if(!empty($this->colonne_champ)){
            $this->nom_composant = 'input';
            $this->attr('type', 'tel');
        }

		return $this->cree_champ();
	}

    public function affiche_liste($element, $colonne){

        $colonne_valeur = $this->modele->alias_champ ?? $this->modele->nom_sql;

        if(empty($element->{$colonne_valeur}))
            return null;

        if($colonne->type != 'concatenation')
            return [
                'type' => 'composant',
                'contenu' => [
                    'composant' => $this->nom_composant,
                    'props' => [
                        'modele' => $element,
                        'nom_sql' => $colonne_valeur,
                        'lecture_seule' => true,
                    ]   
                ]
            ];

        return parent::affiche_liste($element, $colonne);
    }
}
