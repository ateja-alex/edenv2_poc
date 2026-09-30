<?php

namespace App\Eden\Champs;

use DB;

class Champ_montant extends Champ {

    public string $type_filtre = 'filtre-montant';

    public string $nom_composant = 'champ-montant';

    public function cree(){

        $this->attr('ref', $this->modele->nom_sql);
        
        if(!empty($this->placeholder))
            $this->attr('placeholder', $this->placeholder);
        
        $nombre_decimale = !empty($this->modele->nombre_decimale) ? $this->modele->nombre_decimale : 2;
        $this->attr('nombre_decimale',$nombre_decimale,1);

        return $this->cree_champ();
    }

	/**
	 *
	 * Affiche proprement la valeur d'un champ
	 *
	 */
	public function affiche($valeur = false) {

        if($valeur === false && empty($this->valeur) && $this->valeur != 0)
            return "";

        if($valeur === false)
            $valeur = $this->valeur;

        $nombre_decimales = ($this->modele->type == 2) ? 0 : ($this->modele->nombre_decimale === null ? 2 : $this->modele->nombre_decimale);

        $this->modele->decimal_separateur_milliers = !isset($this->modele->decimal_separateur_milliers) ? ' ' : $this->modele->decimal_separateur_milliers;

        // Monétaire / sans séparateur de milliers
        if($this->modele->format_champ == 'monetaire')
            return montant($valeur,  $nombre_decimales, ',', '', false, false);

        // Entier / sans séparateur de milliers
        else if($this->modele->format_champ == 'sans_separateur')
            return montant($valeur, 0, ',', '');

        // Décimal / sans séparateur de milliers
		else if($this->modele->format_champ == 'montant' || (empty($this->modele->format_champ) && $this->modele->type == 3))
            return montant($valeur,  $nombre_decimales, ',', '');

        // Décimal / avec séparateur de milliers
		else if($this->modele->format_champ == 'decimal_separateur_milliers')
            return montant($valeur,  $nombre_decimales, ',', $this->modele->decimal_separateur_milliers);

        // Monétaire / avec séparateur de milliers
		else if($this->modele->format_champ == 'monetaire_separateur_milliers')
            return montant($valeur,  $nombre_decimales, ',', $this->modele->decimal_separateur_milliers, false, false);

        return montant($valeur, 0, ',', ' ', false, false);
	}

	/**
	 *
	 * Crée le champ pour la création des workflows
	 *
	 */
	public function cree_pour_workflow($nom = 'champ') {

		$this->attr('name', 'parametrage['.$nom.'_'.$this->modele->nom_sql.']');
		$this->vmodel(true,'workflow.parametrage',$nom.'_'.$this->modele->nom_sql);

		$champ = '<select '.$this->attributs().'>';
		$champ .= '<option value=""></option>';
		$champ .= '<option value="egal_a_0">Egal à 0</option>';
		$champ .= '<option value="superieur_a_0">Supérieur à 0</option>';
		$champ .= '</select>';

		return $champ;
	}

	/**
	 *
	 * Applique les filtres sur les listes (les listes d'éléments génériques)
	 *
	 */
	public function applique_filtre_sur_requete($filtre, $requete) {

        if(isset($filtre['texte']))
            $filtre['montant'] = $filtre['texte'];

		// pas de variable, on traite le cas classique
		if(empty($filtre['montant']) && (!isset($filtre['montant']) || $filtre['montant'] != "0"))
			return $requete;

        $alias_champ = $this->alias_champ_requete();

        $variable_dynamique = false;

        if (preg_match('/#(.*?)#/', $filtre['montant'], $match) == 1) {
            $filtre['montant'] = $match[1];
            $variable_dynamique = true;
        }

        if(!empty($filtre['variable']) && $filtre['variable'] == 'superieur') {

            if($variable_dynamique)
                $requete = $requete->where($alias_champ, '>', DB::raw('CONCAT('.$filtre['montant'].')'));
            else
                $requete = $requete->where($alias_champ, '>',$filtre['montant']);
        }
        elseif(!empty($filtre['variable']) && $filtre['variable'] == 'superieur_egal') {

            if($variable_dynamique)
                $requete = $requete->where($alias_champ, '>=', DB::raw('CONCAT('.$filtre['montant'].')'));
            else
                $requete = $requete->where($alias_champ, '>=',$filtre['montant']);
        }
        elseif(!empty($filtre['variable']) && $filtre['variable'] == 'inferieur') {

            if($variable_dynamique)
                $requete = $requete->where($alias_champ, '<', DB::raw('CONCAT('.$filtre['montant'].')'));
            else
                $requete = $requete->where($alias_champ, '<',$filtre['montant']);
        }
        elseif(!empty($filtre['variable']) && $filtre['variable'] == 'inferieur_egal') {

            if($variable_dynamique)
                $requete = $requete->where($alias_champ, '<=', DB::raw('CONCAT('.$filtre['montant'].')'));
            else
                $requete = $requete->where($alias_champ, '<=',$filtre['montant']);
        }
        else {
            if($variable_dynamique)
                $requete = $requete->where($alias_champ, DB::raw('CONCAT('.$filtre['montant'].')'));
            else
                $requete = $requete->where($alias_champ, $filtre['montant']);

        }

		return $requete;
	}

    public function cree_web($valeur_par_defaut = false){

        $paterne_champ = $this->paterne_champ_web();

        $obligatoire = '';

        if(!empty($this->modele->obligatoire) || !empty($this->champ_formulaire->condition_obligatoire))
            $obligatoire = "required";

        $input = '<input '.$obligatoire.' '.($valeur_par_defaut!==false ? 'value="'.$valeur_par_defaut.'"' : '').' type="number" name="'.$this->attributs['name']['valeur'].'" />';

        return str_replace('[eden_champ]',$input,$paterne_champ);
    }

    public function affiche_export($element){

        $colonne_valeur = $this->modele->alias_champ ?? $this->modele->nom_sql;
        
        return $element->{$colonne_valeur};
    }
}
