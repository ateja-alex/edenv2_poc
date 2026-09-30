<?php

namespace App\Eden\Champs;

use App\Eden\Models\Colonne;
use App\Eden\Variables;

class Champ_recherche_element extends Champ {

    public string $type_filtre = 'filtre-recherche-element';

    public string $nom_composant = 'champ-selection-element';

    public bool $affichage_complet = false;

    public array $types_sans_creation = [
        'utilisateur',
        'entite',
        'profil'
    ];

	private $filtrage = null;

	public function __construct($champs_libre, $valeur) {

		parent::__construct($champs_libre, $valeur);

        if($this->modele->type_element_ajax == 'utilisateur')
            $this->type_filtre = 'filtre-utilisateur';
        else if($this->modele->type_element_ajax == 'famille')
            $this->type_filtre = 'filtre-famille';

        $this->affichage_complet = isset($this->attributs['affichage_complet']) ? 1 : 0;
	}

    public function cree(){

        $this->attr('type_element', $this->modele->type_element_ajax);
        $this->attr('ref', $this->modele->nom_sql);
        $this->attr('type_element_origine', $this->modele->type_element);
        
        $desactiver_creation_a_la_volee = false;
        if (!empty($this->modele->desactiver_creation_a_la_volee) || in_array($this->modele->type_element_ajax,$this->types_sans_creation) || !profil_creation($this->modele->type_element_ajax))
            $desactiver_creation_a_la_volee = true;
        $this->attr('desactiver_creation_a_la_volee',$desactiver_creation_a_la_volee);

        if($this->filtrage !== null)
            $this->attr('filtrage', $this->filtrage, 1);
        
        $this->attr('format_champ', $this->modele->format_champ);

        return $this->cree_champ();
    }

	/**
	*
	* Affiche proprement la valeur d'un champ
	*
	*/
	public function affiche($valeur = false) {

        if($valeur === false)
            $valeur = $this->valeur;

		if(empty($valeur))
			return '';

        $id_element = $valeur;

		// cas spécifique des adresses
		if($this->modele->type_element_ajax == 'adresse' && !empty($id_element)) {

			$adresse = modele('adresse', $id_element);

			$return = $adresse->prenom.' '.$adresse->nom.', ';
			$return .= $adresse->numero.' '.$adresse->adresse.', ';
			if(!empty($adresse->adresse_complement))
				$return .= $adresse->adresse_complement.', ';
			$return .= $adresse->code_postal.' '.$adresse->ville;

            if(fonctionnalite('documents_adresses_pays') === true && !empty($adresse->pays))
                $return .= ', ' . $adresse->pays;

			return $return;
		}
		else {

			return management($this->modele->type_element_ajax, $id_element)->affiche();
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

		$management = management($this->modele->type_element_ajax, $this->valeur);

		return $management->affiche_lien();
	}

	/**
	 *
	 * Retourne le html pour le champ pour les workflows
	 *
	 */
	public function cree_pour_workflow($nom = 'champ') {

		return '';
	}

	public function applique_filtre_sur_requete($filtre, $requete) {

		if(empty($filtre))
			return $requete;

        $alias_champ = $this->alias_champ_requete();

        if(
            ($this->modele->type_element == "article" || $this->modele->type_element_origine == "article")
            && $this->modele->type_element_ajax == "fournisseur"
            && $this->modele->nom_sql == "fournisseur_id"
        ) {

            $champ_a_verifier = 'id';

            if($this->modele->type_element_origine == "article")
                $champ_a_verifier = 'article_id';

            if(is_array($filtre)){
                $articles_fournisseur = modele('article_fournisseur')->whereIn('fournisseur_id', $filtre)->get();

                $tableau_filtre = [];

                foreach ($articles_fournisseur as $article_fournisseur) {

                    if (!in_array($article_fournisseur->article_id, $tableau_filtre))
                        $tableau_filtre[] = $article_fournisseur->article_id;

                }

                $requete = $requete->whereIn($champ_a_verifier, $tableau_filtre);
            }
            elseif($filtre == 'non_vide'){
                $articles = modele('article')->whereIn('article.id', modele('article_fournisseur')->pluck('article_id'))->pluck('id');

                $requete = $requete->whereIn($champ_a_verifier, $articles);
            }
            else if($filtre == 'vide'){
                $articles = modele('article')->whereNotIn('article.id', modele('article_fournisseur')->pluck('article_id'))->pluck('id');

                $requete = $requete->whereIn($champ_a_verifier, $articles);
            }

            return $requete;
        }

        if(is_array($filtre)){

            if(in_array('#utilisateur_connecte#',$filtre,true)){
                $cle = array_search('#utilisateur_connecte#',$filtre,true);
                $filtre[$cle] = moi()->id;
            }
            
            $requete = $requete->where(function($sous_requete) use ($filtre,$alias_champ) {

                if (in_array('0', $filtre) || in_array(false, $filtre)) {
                    $sous_requete->where($alias_champ, 0)
                        ->orWhereNull($alias_champ);
                }

                $sous_requete->orWhereIn($alias_champ, $filtre);
            });
        }
        elseif($filtre == 'non_vide'){
            $requete = $requete
                ->where($alias_champ,'!=',0)
                ->whereNotNull($alias_champ);
        }
        else if($filtre == 'vide'){
            $requete->where(function($query) use ($alias_champ){
                $query->where($alias_champ, 0)
                    ->orWhereNull($alias_champ);
            });
        }

        return $requete;
	}

	public function filtrage($filtrage) {
		$this->filtrage = $filtrage;
	}

	/**
	 *
	 * Retourne les valeurs possibles d'un type element
	 *
	 */
	public function retourne_valeurs_possibles() {

		return  modele($this->modele->type_element)->distinct($this->modele->nom_sql)->get([$this->modele->nom_sql])->pluck($this->modele->nom_sql)->toArray();
	}

    /**
     *
     * Permet de récupérer les options des selects formatées
     *
     */
    public function recuperation_options_select(){

        $options = array();

        $requete = request();

        $donnees = $requete->all();

        $filtrage = false;

        if(isset($donnees['filtrage']))
            $filtrage = json_decode($donnees['filtrage'],true);

        $valeurs_possibles = modele($this->modele->type_element_ajax);

        $valeurs_possibles = management($this->modele->type_element_ajax)->conditions_specifiques_recherche($valeurs_possibles, $filtrage);

        $recherche_avancee = modele('recherche_avancee')
				->where('type', 'champs_libres.'.$this->modele->type_element.'.'.$this->modele->nom_sql)
				->where('id_cible',$this->modele->type_element_ajax)
				->first();

        if($recherche_avancee != null){

            $management_recherche_avancee = management('recherche_avancee', $recherche_avancee->id, $recherche_avancee);

            $structure = $management_recherche_avancee->structure();

            $structure = $management_recherche_avancee->remplacement_lien_champ(
                $structure,
                []
            );

            $management_recherche_avancee->applique_filtrage(
                $structure,
                $valeurs_possibles,
                $this->modele->type_element_ajax
            );
        }

        $valeurs_possibles = $valeurs_possibles->get();

        if ($this->modele->obligatoire)
            $options[0] = 'Sans valeur';

        foreach ($valeurs_possibles as $modele) {

            $options[$modele->id] = management($this->modele->type_element_ajax, $modele->id, $modele)->affichage_pour_select();
        }

        return $options;
    }

    public function affiche_liste($element, $colonne){

        $colonne_valeur = $this->modele->alias_champ ?? $this->modele->nom_sql;

        $valeur = $element->{$colonne_valeur};
        $modele = $element->{"modele_".$colonne_valeur};

		if(empty($modele))
			return null;

		if($colonne->type != 'concatenation' && !empty($colonne->afficher_avatars_utilisateurs) && $this->modele->type_element_ajax == 'utilisateur'){

            if(empty($modele->avatar))
                $image = '<img src="'.asset('eden/images/no_avatar.jpg').'" title="'.$modele->chaine_affichage.'">';
            else
                $image = '<img src="storage/'.$modele ->avatar.'" title="'.$modele ->chaine_affichage.'">';

            return [
                'type' => 'avatar_utilisateur',
                'contenu' => $image,
            ];
        }
		else if($this->modele->type_element_ajax == 'adresse' && !empty($id_element)) {
			$affichage = $modele->prenom.' '.$modele->nom.', ';
			$affichage .= $modele->numero.' '.$modele->adresse.', ';
			if(!empty($modele->adresse_complement))
				$affichage .= $modele->adresse_complement.', ';
			$affichage .= $modele->code_postal.' '.$modele->ville;

            if(fonctionnalite('documents_adresses_pays') === true && !empty($modele->pays))
                $affichage .= ', ' . $modele->pays;
		}
		else
			$affichage = management($this->modele->type_element_ajax, $valeur, $modele)->affiche();

        return [
            'type' => 'contenu',
            'contenu' => $affichage,
        ];
    }

    public function affiche_export($element){
        return $this->affiche_liste($element, new Colonne())['contenu'] ?? '';
    }

    public function application_tri_requete($requete, $sens, &$joins){

        $alias_table = $this->modele->alias_table ?? $this->modele->type_element;

        if(empty($joins['jointures'][$this->modele->type_element_ajax]['champs_de_liaison'][$this->modele->nom_sql])) {

            $joins['alias_requete_compte']++;

            $table_liaison = 'liaison_'.$joins['alias_requete_compte'];
            // on doit lier la table dans un premier temps
            $requete = $requete->leftJoin($this->modele->type_element_ajax. ' AS '.$table_liaison, $table_liaison.'.id', $alias_table.'.'.$this->modele->nom_sql);
        
            $joins[$this->modele->type_element_ajax]['champs_de_liaison'][$this->modele->nom_sql] = array(
                'alias' => $table_liaison,
                'modele_champ_liaison' => $this->modele,
            );
        }
        else
            $table_liaison = $joins['jointures'][$this->modele->type_element_ajax]['champs_de_liaison'][$this->modele->nom_sql]['alias'];

        $chaine_affichage = table_libre($this->modele->type_element_ajax)->affichage_dans_liste;

        $matches = array();
        $regex = "/#([a-zA-Z0-9_]*)#/";
        preg_match_all($regex, $chaine_affichage, $matches);

        foreach ($matches[1] as $variable) {
            $requete = $requete->orderBy($table_liaison . '.' . $variable,$sens);
        }

        return $requete;
    }
}
