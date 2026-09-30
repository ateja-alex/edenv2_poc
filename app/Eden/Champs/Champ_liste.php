<?php

namespace App\Eden\Champs;

use App\Eden\Models\Champ_libre_liste;
use App\Eden\Variables;
use Illuminate\Support\Facades\Session;
use DB;

class Champ_liste extends Champ {

	public $valeurs_possibles = array();

    public function cree_web($valeur_par_defaut = false){

        $paterne_champ = $this->paterne_champ_web();

        $obligatoire = '';

        if(!empty($this->modele->obligatoire) || !empty($this->champ_formulaire->condition_obligatoire))
            $obligatoire = "required";

        if($this->modele->liste_choix == 14)
            $input = '<input type="checkbox" name="' . $this->attributs['name']['valeur'] . '" ' . ($valeur_par_defaut == 1 ? 'checked' : '') . '/>';
        else {

            $input = '<select ' . $obligatoire . ' name="' . $this->attributs['name']['valeur'] . '" />';

            foreach ($this->valeurs_possibles as $id => $element) {
                $input .= '<option ' . ($valeur_par_defaut == $id ? 'selected' : '') . ' value="' . $id . '">' . $element . '</option>';
            }

            $input .= '</select>';
        }

        return str_replace('[eden_champ]',$input,$paterne_champ);
    }

    public function application_tri_requete($requete, $sens, &$joins){

        $alias_table = $this->modele->alias_table ?? $this->modele->type_element;

		$valeurs_possibles = $this->valeurs_possibles;

		if(empty($valeurs_possibles))
			return $requete;

		$ordre_cles = array_keys($valeurs_possibles);

		$ordre_cles = implode(",", $ordre_cles);

		return $requete->orderBy(DB::raw('FIELD('.$alias_table.'.'.$this->modele->nom_sql.', ' . $ordre_cles . ')'),$sens);
    }

}
