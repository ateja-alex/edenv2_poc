<?php

namespace App\Eden\Champs;

class Champ_numero_automatique extends Champ_texte{

	public string $nom_composant = 'input';

	public function cree(){

		$this->attr('type', 'text');
		
		$this->attr('disabled','true');

		if(empty($this->valeur))
			$this->attr('valeur',$this->valeur);

        return $this->cree_champ();
    }

}