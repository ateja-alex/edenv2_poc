<?php

namespace App\Eden\Champs;

class Champ_time extends Champ {
	
	public string $type_filtre = 'filtre-time';

	public string $nom_composant = 'champ-time';

    public function cree(){

		$this->attr('format', $this->modele->format_champ);

		$this->attr('ref', $this->modele->nom_sql);

		if(!empty($this->modele->contenu))
            $this->attr('interval', $this->modele->contenu, 1);

        return $this->cree_champ();
    }

	/**
	 *
	 * Applique les filtres sur les listes (les listes d'éléments génériques)
	 * 
	 */
	public function applique_filtre_sur_requete($filtre, $requete) {

		$alias_champ = $this->alias_champ_requete();

		if(isset($filtre['debut']))
			$requete = $requete->where($alias_champ, '>=', $filtre['debut']);

		if(isset($filtre['fin']))
			$requete = $requete->where($alias_champ, '<=', $filtre['fin']);

		return $requete;
	}

    public function cree_web($valeur_par_defaut = false){

        $paterne_champ = $this->paterne_champ_web();

        $obligatoire = '';

        if(!empty($this->modele->obligatoire) || !empty($this->champ_formulaire->condition_obligatoire))
            $obligatoire = "required";

        $input = '<input '.$obligatoire.' '.($valeur_par_defaut!==false ? 'value="'.$valeur_par_defaut.'"' : '').' type="time" name="'.$this->attributs['name']['valeur'].'" />';

        return str_replace('[eden_champ]',$input,$paterne_champ);
    }

	/**
     *
     * Permet de créer un champ de création spécifique pour les colonnes sur les listes libres de type champ
     *
     */
    public function cree_pour_colonne_champ($valeur = null,$parametres) {

        return '<input type="time" value="'.$valeur.'" step="' . ($this->modele->format_champ === 'H:i:s' ? '1' : '60') . '" onChange="'.$parametres['lien_liste'].'.enregistre_modification_depuis_liste('.$parametres['element_id'].', \''.$parametres['type_element'].'\', \''.$parametres['champ'].'\', $(this).val(),\'\','.(isset($parametres['parametres_creation']) ? htmlspecialchars(json_encode($parametres['parametres_creation'])) : 'false').')" />';
    }
}