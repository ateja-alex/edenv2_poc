<?php

namespace App\Eden\Champs;

use App\Eden\Variables;

class Champ_texte_couleur extends Champ_texte {

    public string $nom_composant = 'champ-colorpicker';

	public function cree(){
        return $this->cree_champ();
    }

    /**
	*
	* Affiche proprement la valeur d'un champ
	* 
	*/
	public function affiche($valeur = false) {
		
		if($valeur === false)
			return '<span style="background: '.$this->valeur.'; color: white; padding: 5px;">'.$this->valeur.'</span>';
		else
			return '<span style="background: '.$valeur.'; color: white; padding: 5px;">'.$valeur.'</span>';
	}

    public function cree_web($valeur_par_defaut = false){

        $paterne_champ = $this->paterne_champ_web();

        $obligatoire = '';

        if(!empty($this->modele->obligatoire) || !empty($this->champ_formulaire->condition_obligatoire))
            $obligatoire = "required";

        $input = '<input '.$obligatoire.' '.($valeur_par_defaut!==false ? 'value="'.$valeur_par_defaut.'"' : '').' type="color" name="'.$this->attributs['name']['valeur'].'" />';

        return str_replace('[eden_champ]',$input,$paterne_champ);
    }
	
}