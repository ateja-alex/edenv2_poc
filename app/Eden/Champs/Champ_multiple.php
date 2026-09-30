<?php

namespace App\Eden\Champs;
use DB;

class Champ_multiple extends Champ {

    protected $champ_enfant;

    public string $type_filtre = '';

    public string $nom_composant = '';

    public function __construct($champ_libre, $champ_enfant, $valeur = false) {

        parent::__construct($champ_libre, $valeur);
        $this->champ_enfant = $champ_enfant;
        $this->champ_libre = $champ_libre;

        $this->type_filtre = $this->champ_enfant->type_filtre;
    }

    public function cree(){

        if(in_array($this->modele->type_reference, array(1,20))) {

            $this->attr('type_element', $this->modele->type_element);

            if($this->modele->format_champ == 'typeahead'){

                $this->nom_composant = "champ-multi-selection";

                $info_champ_libre = [
                    'type' => $this->modele->type,
                    'liste_choix' => $this->modele->liste_choix,
                    'id_cl' => $this->modele->id_cl,
                ];

                $this->attr('champ_libre', json_encode($info_champ_libre),1);

            } else {

                if($this->modele->format_champ == 'select')
                    $this->nom_composant = "champ-multi-selection-select";

                else
                    $this->nom_composant = "champ-multi-selection-checkbox";

                $id_cl = $this->modele->id_cl;

                if(!empty($this->modele->liste_choix))
                    $id_cl = $this->modele->liste_choix;

                $this->attr('id_cl',$id_cl);

                $this->attr('liste_choix', $this->modele->liste_choix);

                $this->attr('type_reference', $this->modele->type_reference);
            }
        } else {
            $this->nom_composant = 'champ-multiple';

            $this->champ_enfant->cree();
            $composant_enfant_attributs = $this->champ_enfant->attributs;
            $this->nom_composant_enfant = $this->champ_enfant->nom_composant;

            $props_json = [];

            foreach($composant_enfant_attributs as $param){
                if(!empty($param['valeur'])){
                    $props_json[] = !empty($param['dynamique']) ? json_encode($param['nom']) . ': ' . $param['valeur'] : json_encode($param['nom']) . ': ' . json_encode($param['valeur']);
                }
            }

            $this->attr('composant_enfant', $this->nom_composant_enfant);
            $props_composant_enfant ='{'.implode(',',$props_json).'}';
            $this->attr('composant_enfant_props', $props_composant_enfant, 1);
        }

        return parent::cree();
    }

    public function applique_filtre_sur_requete($filtre, $requete) {

        $table_pivot = $this->modele->table_pivot;

		if(empty($filtre))
			$filtre = array();

        $champ_enfant = $this->champ_enfant;

        $this->champ_enfant->modele->alias_champ = $table_pivot. '.valeur';

        $alias_table = $this->alias_table_requete();

        $nom_colonne = 'id';

        $vue_sql = modele('vue_sql')->where('nom',$this->modele->type_element)->first();

        if($vue_sql && $this->modele->type_element_origine)
            $nom_colonne = service('vue_sql')->recuperer_nom_colonne_id($vue_sql,$this->modele->type_element_origine);

        $type_element = $this->modele->type_element;

		return $requete->whereIn($alias_table.'.'.$nom_colonne,function($query) use ($table_pivot,$filtre,$champ_enfant,$alias_table,$type_element) {

            $query->select($alias_table . '.id')
                ->from($type_element .' as '.$alias_table)
                ->whereRaw('COALESCE('.$alias_table .'.inactif,0) = 0')
                ->leftJoin($table_pivot,$table_pivot.'.cle_locale',$alias_table.'.id')
                ->groupBy($alias_table . '.id');

            if(table_libre_existe($table_pivot))
                $query->whereRaw('COALESCE('.$table_pivot .'.inactif,0) = 0');

            $champ_enfant->applique_filtre_sur_requete($filtre, $query);
        });
	}

	/**
	 *
	 * Retourne les valeurs du modèle, un tableau $array
	 *
	 */
	public function recupere_valeurs_du_modele($id_modele) {

        $modele_table_pivot = table_libre_existe($this->modele->table_pivot) ? modele($this->modele->table_pivot) : \DB::table($this->modele->table_pivot);

		$liens = $modele_table_pivot->where('cle_locale', $id_modele)->get()->pluck('valeur')->toArray();

		return $liens;
	}

    public function affiche_liste($element, $colonne){

        $colonne_valeur = $this->modele->alias_champ ?? $this->modele->nom_sql;

        $contenus = [];

        $valeurs = $element->{$colonne_valeur};

        $modeles = $element->{"modeles_".$colonne_valeur};

        if(empty($valeurs))
            return null;

        $type = null;

        foreach($valeurs as $valeur){

            $element_unitaire = clone $element;
            $element_unitaire->{$colonne_valeur} = $valeur;

            if($this->modele->type_reference == 42)
                $element_unitaire->{"modele_".$colonne_valeur} = $modeles[$valeur] ?? null;

            $affichage_liste = $this->champ_enfant->affiche_liste($element_unitaire, $colonne);

            if(empty($affichage_liste))
                continue;
            
            if($type === null)
                $type = $affichage_liste['type'];

            if($type == 'contenu')
                $contenus[] = '<span class="badge badge-default">'.$affichage_liste['contenu'].'</span>';
            else
                $contenus[] = $affichage_liste['contenu'];
        }

        return [
            'type' => $type,
            'contenus' => $contenus,
        ];
    }

    public function affiche_export($element){

        $colonne_valeur = $this->modele->alias_champ ?? $this->modele->nom_sql;

        $contenus = [];

        $valeurs = $element->{$colonne_valeur};

        $modeles = $element->{"modeles_".$colonne_valeur};

        if(empty($valeurs))
            return null;

        $type = null;

        foreach($valeurs as $valeur){

            $element_unitaire = clone $element;
            $element_unitaire->{$colonne_valeur} = $valeur;

            if($this->modele->type_reference == 42)
                $element_unitaire->{"modele_".$colonne_valeur} = $modeles[$valeur] ?? null;

            $contenus[] = $this->champ_enfant->affiche_export($element_unitaire);
        }

        return implode('|', $contenus);
    }

    public function affiche($valeur = false) {

        $retour = [];

        if($valeur === false)
            $valeur = $this->valeur;

        if(empty($valeur))
            return '';

        foreach($valeur as $element_a_afficher){
            $retour[] = '<span class="badge badge-default">'.$this->champ_enfant->affiche($element_a_afficher).'</span>';
        }

        return implode(' ', $retour);
    }

    public function cree_web($valeur_par_defaut = false){

        $paterne_champ = $this->paterne_champ_web();

        $this->liste_valeurs();

        $obligatoire = '';

        if(!empty($this->modele->obligatoire) || !empty($this->champ_formulaire->condition_obligatoire))
            $obligatoire = "required";

        $valeur_par_defaut = $valeur_par_defaut == false ? false : json_decode($valeur_par_defaut);

        $input = '<select '.$obligatoire.'  multiple name="'.$this->attributs['name']['valeur'].'[]" />';

        foreach($this->valeurs_possibles as $id => $element){
            $input.= '<option '.(!empty($valeur_par_defaut) && in_array($id,$valeur_par_defaut) ? 'selected' : '').' value="'.$id.'">'.$element.'</option>';
        }

        $input.= '</select>';

        return str_replace('[eden_champ]',$input,$paterne_champ);
    }

    public function liste_valeurs() {

        if($this->modele->type_reference == 1){

            $this->liste_libres();

		    return $this->valeurs_possibles;

        } else if($this->modele->type_reference == 20){

            $this->listes_preenregistrees();
		
		    return $this->valeurs_possibles;

        } else {
            return;
        }
    }

    public function liste_valeurs_ordre(){
        if($this->modele->type_reference == 1){

            $this->liste_libres_ordre();

		    return $this->valeurs_possibles_ordre;

        } else if($this->modele->type_reference == 20){
            return '';
        } else {
            return;
        }
    }

    /**
	 *
	 * Affiche la valeur du champ
	 *
	 */
	public function cree_affichage($valeur = null) {

		if($valeur !== null)
			$this->value($valeur);

		if(empty($this->valeur))
			return '';

		$retour = array();

		foreach($this->valeur as $valeur) {

			$retour[] = management($this->modele->type_element_ajax, $valeur)->affiche();
		}

		return implode(', ', $retour);
	}

    public function application_tri_requete($requete, $sens, &$joins){

        $table_pivot_nom = "table_pivot_champ_". $this->modele->id_cl;
        $alias_table = $this->modele->alias_table ?? $this->modele->type_element;

        $requete = $requete->leftJoin($this->modele->table_pivot . " AS ".$table_pivot_nom, $table_pivot_nom . '.cle_locale', $alias_table . '.id'); 

        $champ_table_pivot = clone $this->champ_enfant;
        $champ_table_pivot->modele = clone $champ_table_pivot->modele;
        $champ_table_pivot->modele->nom_sql = 'valeur';
        $champ_table_pivot->modele->alias_table = $table_pivot_nom;

        return $champ_table_pivot->application_tri_requete($requete, $sens, $joins);
    }
}
