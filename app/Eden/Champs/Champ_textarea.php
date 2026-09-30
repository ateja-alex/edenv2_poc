<?php

namespace App\Eden\Champs;

use App\Eden\Variables;
use DB;
use Illuminate\Support\Str;


class Champ_textarea extends Champ_texte {

    public string $nom_composant = 'textarea';

    public function cree(){

        if(!empty($this->modele->nombre_max_caracteres))
            $this->attr('maxlength', $this->modele->nombre_max_caracteres);
        
        if($this->modele->format_champ == 'wysiwyg' && empty($this->colonne_champ)){
            $this->nom_composant = 'textarea-wysiwyg-vue';

        } else {
            $random_id =$this->modele->nom_sql.''.$this->modele->type_element.''.Str::random(6);
            $this->attr('id', $random_id);

            if(!empty($this->placeholder))
                $this->attr('placeholder', $this->placeholder); 
        }

        return $this->cree_champ();
    }

	/**
	 * 
	 * Gère l'affichage des mentions
	 * 
	 */
	public function affiche($valeur = false) {
		
		if($valeur === false)
			$valeur = $this->valeur;
		
		$utilisateurs = modele('utilisateur')->liste_utilisateurs_visibles();
		
		$mentions = array();
		
		foreach($utilisateurs as $utilisateur) {
			
			$mentions['@'.$utilisateur->prenom.' '.$utilisateur->nom] = '<span style="color: blue;">@'.$utilisateur->prenom.' '.$utilisateur->nom.'</span>';
		}

        $valeur = str_replace(array_keys($mentions), $mentions, $valeur);

        preg_match_all("/#lien\/.*#/", $valeur, $liens_a_remplacer);

        if(empty($liens_a_remplacer))
            return $valeur;

        $liens_a_remplacer = $liens_a_remplacer[0];

        $lien_remplace = [];

        foreach ($liens_a_remplacer as $lien){

            $tableau_lien = explode('/', str_replace("#", "", $lien));

            if(!isset($tableau_lien[2]))
                continue;

            $lien_remplace[$lien] = management($tableau_lien[1], $tableau_lien[2])->affiche_lien();

        }

        return str_replace(array_keys($lien_remplace), $lien_remplace, $valeur);
		
	}

    public function affiche_liste($element, $colonne) {

        $colonne_valeur = $this->modele->alias_champ ?? $this->modele->nom_sql;

        $affichage = strip_tags($this->affiche($element->{$colonne_valeur}));

        if(!empty($colonne->caracteres_max))
            $affichage = substr($affichage, 0, $colonne->caracteres_max). '...';

        return [
            'type' => 'contenu',
            'contenu' => $affichage,
        ];
    }

    public function cree_web($valeur_par_defaut = false){

        $paterne_champ = $this->paterne_champ_web();

        $obligatoire = '';

        if(!empty($this->modele->obligatoire) || !empty($this->champ_formulaire->condition_obligatoire))
            $obligatoire = "required";

        $input = '<textarea '.$obligatoire.' '.($valeur_par_defaut!==false ? 'value="'.$valeur_par_defaut.'"' : '').' name="'.$this->attributs['name']['valeur'].'" ></textarea>';

        return str_replace('[eden_champ]',$input,$paterne_champ);
    }
}