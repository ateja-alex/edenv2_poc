<?php

namespace App\Eden\Champs;

use App\Eden\Variables;

class Champ_tableau extends Champ {

	public string $nom_composant = 'champ-tableau';

	public function cree(){

		if(!empty($this->attributs['disabled']))
            $this->attr('champ_disabled', $this->attributs['disabled'],true);

		$this->attr('type_element', $this->modele->type_element);

		return $this->cree_champ();
	}
}