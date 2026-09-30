<?php

namespace App\Eden\Champs;

use App\Eden\Variables;

class Champ_dropzone extends Champ {

    public string $nom_composant = 'champ-dropzone';

	public function cree(){

		if(!empty($this->modele->format_champ))
			$this->attr('accept', $this->modele->format_champ);
		
        $this->attr('type_element',$this->modele->type_element);

		if (in_array($this->modele->type_element, Variables::$documents_gescom))
            $vue_model = 'document.' . $this->modele->nom_sql;
        else
            $vue_model = $this->modele->type_element . '.' . $this->modele->nom_sql;

		$this->attr('valeur', $vue_model, 1);

		return $this->cree_champ();
	}

    public function cree_web($valeur_par_defaut = false){

        $paterne_champ = $this->paterne_champ_web();

        $obligatoire = '';

        if(!empty($this->modele->obligatoire) || !empty($this->champ_formulaire->condition_obligatoire))
            $obligatoire = "required";

        $input = '<input accept="' . $this->modele->type_fichier.'" type="file" '.$obligatoire.' name="'.$this->attributs['name']['valeur'].'[]" multiple />';

        return str_replace('[eden_champ]',$input,$paterne_champ);
    }

	public function affiche($valeur = false){

		if(empty($valeur) && empty($this->valeur))
			return '';

		$fichiers = json_decode(empty($valeur) ? $this->valeur : $valeur, true);
		$chaine_affichage = '';
		
		foreach($fichiers as $index => $fichier){

			$chaine_affichage .= '<a href="' . $fichier['url_public'] . '" target="_blank">' . $fichier['nom_original'] . '</a>';

			if($index !== array_key_last($fichiers))
				$chaine_affichage .= ', ';
		}

		return $chaine_affichage;
	}
	
}