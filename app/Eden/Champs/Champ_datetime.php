<?php

namespace App\Eden\Champs;

use App\Eden\Tools\DatesUtils;
use App\Eden\Variables;

class Champ_datetime extends Champ_date {

    public string $nom_composant = 'champ-datetime';

    public function cree(){

        $interval = 1;

        if(!empty($this->modele->contenu))
            $interval = $this->modele->contenu;

        $this->attr('interval', $interval, 1);

        $this->attr('ref', $this->modele->nom_sql);

        return $this->cree_champ();
    }

	public function formate_valeur_initiale($valeur) {
		
		if(!empty($valeur) && $valeur != '0000-00-00 00:00:00' && $valeur != '00/00/0000 00:00:00')
			$this->valeur = formate_date('d/m/Y H:i:s', $valeur);
		else
			$this->valeur = '';
	}
	
	public function value($valeur) {
		
		if($valeur == '#MAINTENANT#')
			$valeur = date('Y-m-d H:i:s');
		
		if(!empty($valeur) && $valeur != '0000-00-00 00:00:00' && $valeur != '00/00/0000 00:00:00')
			$this->valeur = formate_date('d/m/Y H:i:s', $valeur);
		else
			$this->valeur = '';
		
		return $this;
	}
	
	public function affiche($valeur = false) {

	    $format = 'd/m/Y H:i:s';

	    if($this->modele->format_champ != ""){
            $format = $this->modele->format_champ ;
        }
		
		if($valeur === false)
			return formate_date($format, $this->valeur);
		else
			return formate_date($format, $valeur);
	}

    public function cree_web($valeur_par_defaut = false){

        $paterne_champ = $this->paterne_champ_web();

        $obligatoire = '';

        if(!empty($this->modele->obligatoire) || !empty($this->champ_formulaire->condition_obligatoire))
            $obligatoire = "required";

        $input = '<input '.$obligatoire.' '.($valeur_par_defaut!==false ? 'value="'.$valeur_par_defaut.'"' : '').' type="datetime-local" name="'.$this->attributs['name']['valeur'].'" />';

        return str_replace('[eden_champ]',$input,$paterne_champ);
    }
}