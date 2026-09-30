<?php

namespace App\Eden\Champs;

use App\Eden\Variables;

class Champ_piece_jointe extends Champ
{

    public string $nom_composant = 'champ-file';

    public string $type_filtre = 'filtre-piece-jointe';

    public function cree(){

        if($this->modele->format_champ == 'signature'){

            $this->nom_composant = 'champ-signature';

            $this->attr('type_element', $this->modele->type_element);

            return $this->cree_champ();
        }

        // on récupère le modèle par défaut...
        if (in_array($this->modele->type_element, Variables::$documents_gescom))
            $vue_model = 'document.' . $this->modele->nom_sql;
        else
            $vue_model = $this->modele->type_element . '.' . $this->modele->nom_sql;
        
        $this->attr('accept', $this->modele->type_fichier);

        $this->attr('type_element', $this->modele->type_element);

        $this->attr('valeur', $vue_model, 1);

        $this->attr('type', 'file');

        return $this->cree_champ();
    }

    public function applique_filtre_sur_requete($filtre, $requete) {

        $alias_champ = $this->alias_champ_requete();

        if($filtre == 'sans')
            return $requete->whereNull($alias_champ);

        elseif($filtre == 'avec')
            return $requete->whereNotNull($alias_champ);

        return $requete;
    }

    public function valeurs_pour_filtre(){

        $valeurs = array('sans' => 'Sans', 'avec' => 'Avec');

        return $valeurs;
    }

    /**
	 * 
	 * Affiche proprement la valeur d'un champ
	 * 
	 */
	public function affiche($valeur = false) {
		
		if($valeur === false)
			return $this->valeur;
		else if (!empty($valeur)){

            if($this->modele->format_champ == 'signature'){

                if(filter_var($valeur, FILTER_VALIDATE_URL))
                    return "<img src='".$valeur."' class='image_signature image_signature_".$this->modele->type_element."_".$this->modele->nom_sql."' />";

                return "<img src='".asset('storage/'.$valeur)."' class='image_signature image_signature_".$this->modele->type_element."_".$this->modele->nom_sql."' />";
            }

            if(filter_var($valeur, FILTER_VALIDATE_URL))
                return "<span data-toggle='tooltip' title='".$valeur."'><i class='fas fa-file-pdf'></i> <a href='" . $valeur ."' target='_blank'>" . traduction('champs_libres.piece_jointe.telecharger') . "</a></span>";

            return "<span data-toggle='tooltip' title='".$valeur."'><i class='fas fa-file-pdf'></i> <a href='" . url('storage/' . $valeur) ."' target='_blank'>" . traduction('champs_libres.piece_jointe.telecharger') . "</a></span>";
        }
        else
            return '';
	}

    /**
     * 
     * Affiche l'image
     * 
     */
    public function affiche_liste($element, $colonne){

        $colonne_valeur = $this->modele->alias_champ ?? $this->modele->nom_sql;

        $valeur = $element->{$colonne_valeur};

        if(empty($valeur))
            return null;

        if($colonne->type != 'concatenation' && in_array($this->modele->format_champ, array('logo', 'signature'))){

            $classes = [$this->modele->type_element, $this->modele->nom_sql];

            $les_classes = 'image_liste ';

            if(is_array($classes))
                $les_classes .= 'image_liste_'.implode(' image_liste_', $classes);

            elseif($classes !== false)
                $les_classes .= 'image_liste_'.$classes;

            if(!empty($valeur)){

                $image = '';
                if(file_exists(storage_path('app/public/'.$valeur)))
                    $image = '<img src="'.asset('storage/'.$valeur).'" class="'.$les_classes.'" />';
                else if(filter_var($valeur, FILTER_VALIDATE_URL))
                    $image = "<img src='{$valeur}' class='{$les_classes}' />";

                return [
                    'type' => 'contenu',
                    'contenu' => $image,
                ];
            }
        }

        return parent::affiche_liste($element, $colonne);
    }

    public function affiche_export($element){

        $colonne_valeur = $this->modele->alias_champ ?? $this->modele->nom_sql;
        
        return $element->{$colonne_valeur};
    }

    public function cree_web($valeur_par_defaut = false){

        $paterne_champ = $this->paterne_champ_web();

        $obligatoire = '';

        if(!empty($this->modele->obligatoire) || !empty($this->champ_formulaire->condition_obligatoire))
            $obligatoire = "required";

        $input = '<input accept="' . $this->modele->type_fichier.'" type="file" '.$obligatoire.' name="'.$this->attributs['name']['valeur'].'" />';

        return str_replace('[eden_champ]',$input,$paterne_champ);
    }

}