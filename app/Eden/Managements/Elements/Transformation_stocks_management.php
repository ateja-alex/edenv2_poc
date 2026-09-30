<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Variables;

use DB;

class Transformation_stocks_management extends Element_management {

    public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();

        $liste_options[] = 'zoom';

        if(in_array('apercu',$liste_options))
            unset($liste_options[array_search('apercu',$liste_options)]);

        return $liste_options;
    }

    /**
	 *
	 * Crée les details d'une ligne
	 *
	 */
	public function recuperer_details_ligne_pour_liste($id_element,$type_element,$id_liste_parent) {

		$management = management($type_element, $id_element);

		// On va chercher les lignes qui nous interessent
		$lignes = DB::table($type_element.'_lignes')->where('transformation_stocks_id',$id_element)->get();

		// On appelle la vue qui affiche les infos que l'on veux via un render()
		$vue = "eden::listes.includes.details_ligne";
		$vue_render = view($vue, array(

			'lignes' => $lignes,
            'pieces_jointes' => [],
			'management' => $management,
		))->render();

		return $vue_render;
	}

	/**
	 *
	 * Enregistre un élément
	 *
	 * @param $modele le modèle en question
	 * @param $modifications un tableau avec les champs à modifier (similaire à create() de laravel)
	 * 
	 * @return true si tout va bien, une erreur (string) sinon
	 * 
	 */
	public function enregistre($modifications = array(), $modele = false) {

		$retour_apres_modif = $this->tri_transformation($modifications);

		$modifications = $retour_apres_modif['modifications'];

        $modifications['reserve'] = 1;

		$transformations_a_enregistrer = $retour_apres_modif['transformations_a_enregistrer'];

		$retour = parent::enregistre($modifications, $modele);

		if($retour != true)
			return $retour;

		$post_traitement = $this->transformations_post_enregistrement($transformations_a_enregistrer);

		return $post_traitement;

	}

	/**
	 * 
	 * Enregistre les transformations de stocks
	 * 
	 */
	public function tri_transformation($modifications){

		foreach($modifications as $cle => $valeur){

            if(explode('_',$cle)[0] == 'quantite' && explode('_',$cle)[1] == 'depart')
                $transformations_a_enregistrer[explode('_',$cle)[2]]['quantite_depart'] = $valeur;

			if(strpos($cle, '_arrivee_') !== false){


                if(explode('_',$cle)[0] == 'quantite')
                    $transformations_a_enregistrer[explode('_',$cle)[2]]['quantite_arrive'] = $valeur;
				else if(explode('_',$cle)[0] == 'conditionnement')
					$transformations_a_enregistrer[explode('_',$cle)[2]]['conditionnement_id'] = $valeur;
				else{
					$transformations_a_enregistrer[explode('_',$cle)[2]][explode('_',$cle)[0]] = $valeur;
				}

				unset($modifications[$cle]);

			}

		}

		return ['modifications' => $modifications , 'transformations_a_enregistrer' => $transformations_a_enregistrer];

	}

	/**
	 * 
	 * On enregistre les transformations
	 * 
	 */
	public function transformations_post_enregistrement($transformations_a_enregistrer){

		$total_transforme = 0;

		$conditionnement_depart = modele('conditionnement')->where('id', $this->modele->conditionnement_depart_id)->first();

		foreach($transformations_a_enregistrer as $transformation){

			$transformation['transformation_stocks_id'] = $this->modele->id;

            $conditionnement_transformation = !empty($transformation['conditionnement_id']) ? modele('conditionnement')->where('id', $transformation['conditionnement_id'])->first() : null;

			if(isset($transformation['id'])){

				$mouvement_de_stock = modele('mouvement_de_stock')->where('transformation_stocks_id', $transformation['id'])->first();

				if($transformation['conditionnement_id'] != $mouvement_de_stock['conditionnement_id']){

					$modifications_mouvement_de_stock = true;

					$quantite_arrivee = $transformation['quantite_depart'] * $conditionnement_depart['quantite'];

					$mouvement_de_stock['quantite'] = $quantite_arrivee;

                    if($conditionnement_transformation !== null)
					    $mouvement_de_stock['quantite_conditionnement'] = $quantite_arrivee / $conditionnement_transformation['quantite'];
                    else
                        $mouvement_de_stock['quantite_conditionnement'] = $quantite_arrivee;

					$conditionnement_id = $transformation['conditionnement_id'];

				}
				if($conditionnement_transformation !== null && $transformation['quantite_depart'] * $conditionnement_depart['quantite'] / $conditionnement_transformation['quantite'] != $mouvement_de_stock['quantite_conditionnement']){

					$modifications_mouvement_de_stock = true;

					$quantite_arrivee = $transformation['quantite_depart'] * $conditionnement_depart['quantite'];

					$mouvement_de_stock['quantite'] = $quantite_arrivee;

					$mouvement_de_stock['quantite_conditionnement'] = $quantite_arrivee / $conditionnement_transformation['quantite'];

					$conditionnement_id = $transformation['conditionnement_id'];

				}
				if($this->modele->entrepot_id != $mouvement_de_stock['entrepot_id']){

					$modifications_mouvement_de_stock = true;

					$mouvement_de_stock['entrepot_id'] = $this->modele->entrepot_id;

				}

				if($this->modele->date_transformation != $mouvement_de_stock['date']){

					$modifications_mouvement_de_stock = true;

					$mouvement_de_stock['date'] = $this->modele->date_transformation;

				}

                $mouvement_de_stock['reserve'] = 0;

				if(isset($modifications_mouvement_de_stock) && $modifications_mouvement_de_stock == true){

					$retour = management('transformation_stocks_lignes',$transformation['id'])->enregistre($transformation);

					if(isset($conditionnement_id))
						$mouvement_de_stock['conditionnement_id'] = $conditionnement_id;

					$retour_mouvement_de_stock = management('mouvement_de_stock',$mouvement_de_stock['id'])->enregistre($mouvement_de_stock);

				}

				$total_transforme += $mouvement_de_stock['quantite'];
			}
			else{

				$management = management('transformation_stocks_lignes');

				$retour = $management->enregistre($transformation);

				$mouvement_de_stock = $transformation;

				$mouvement_de_stock['transformation_stocks_id'] = $management->modele->id;

				unset($mouvement_de_stock['id']);

				$mouvement_de_stock['entrepot_id'] = $this->modele->entrepot_id;

				$quantite_arrivee = $mouvement_de_stock['quantite_depart'] * ($conditionnement_depart['quantite'] ?? 1);

                if($conditionnement_transformation !== null)
				    $mouvement_de_stock['quantite_conditionnement'] = $quantite_arrivee / ($conditionnement_transformation['quantite'] ?? 1);
                else
                    $mouvement_de_stock['quantite_conditionnement'] = $quantite_arrivee;

				$mouvement_de_stock['quantite'] =  $quantite_arrivee;

				$mouvement_de_stock['date'] = $this->modele->date_transformation;

				$mouvement_de_stock['article_id'] = $this->modele->article_id;

				$mouvement_de_stock['reserve'] = 0;

				$retour_mouvement_de_stock = management('mouvement_de_stock')->enregistre($mouvement_de_stock);

				$total_transforme += $mouvement_de_stock['quantite'];
			}

			if(isset($retour) && $retour != true)
				return $retour;

			if(isset($retour_mouvement_de_stock) && $retour_mouvement_de_stock != true)
				return $retour_mouvement_de_stock;

		}

		if(isset($retour) && $retour != true)
			return $retour;

		if(isset($retour_mouvement_de_stock) && $retour_mouvement_de_stock != true)
			return $retour_mouvement_de_stock;

		$mouvement_de_stock_apres_transformation = modele('mouvement_de_stock')->where('transformation_stocks_id',$this->modele->id)->where('transformation_stock_total',1)->first();
		
		if($mouvement_de_stock_apres_transformation != null)
			$mouvement_de_stock_apres_transformation = $mouvement_de_stock_apres_transformation->toArray();

		$conditionnement_id = $this->modele->conditionnement_depart_id;

		if($mouvement_de_stock_apres_transformation == null){

			$mouvement_de_stock_apres_transformation = [];

			$mouvement_de_stock_apres_transformation['entrepot_id'] = $this->modele->entrepot_id;

			$mouvement_de_stock_apres_transformation['article_id'] = $this->modele->article_id;

			$mouvement_de_stock_apres_transformation['quantite'] = -$total_transforme;

			$mouvement_de_stock_apres_transformation['conditionnement_id'] = $conditionnement_id;

			$mouvement_de_stock_apres_transformation['quantite_conditionnement'] = -($total_transforme / ($conditionnement_depart['quantite'] ?? 1));

			$mouvement_de_stock_apres_transformation['date'] = $this->modele->date_transformation;

			$mouvement_de_stock_apres_transformation['transformation_stocks_id'] = $this->modele->id;

			$mouvement_de_stock_apres_transformation['transformation_stock_total'] = 1;

            $mouvement_de_stock_apres_transformation['reserve'] = 0;


            $retour_apres_transformation = management('mouvement_de_stock')->enregistre($mouvement_de_stock_apres_transformation);

		}
		else{

			if($mouvement_de_stock_apres_transformation['entrepot_id'] != $this->modele->entrepot_id){

				$modifications_apres_transformation = true;

				$mouvement_de_stock_apres_transformation['entrepot_id'] = $this->modele->entrepot_id;

			}

			if($mouvement_de_stock_apres_transformation['article_id'] != $this->modele->article_id){

				$modifications_apres_transformation = true;

				$mouvement_de_stock_apres_transformation['article_id'] = $this->modele->article_id;

			}

			if($mouvement_de_stock_apres_transformation['quantite'] != -$total_transforme){

				$modifications_apres_transformation = true;

				$mouvement_de_stock_apres_transformation['quantite'] = -$total_transforme;

				$mouvement_de_stock_apres_transformation['quantite_conditionnement'] = -($total_transforme / ($conditionnement_depart['quantite'] ?? 1));

			}

			if($mouvement_de_stock_apres_transformation['quantite_conditionnement'] != -$total_transforme / ($conditionnement_depart['quantite'] ?? 1)){

				$modifications_apres_transformation = true;

				$mouvement_de_stock_apres_transformation['quantite_conditionnement'] = -($total_transforme / ($conditionnement_depart['quantite'] ?? 1));

			}

			if($mouvement_de_stock_apres_transformation['date'] != $this->modele->date_transformation){

				$modifications_apres_transformation = true;

				$mouvement_de_stock_apres_transformation['date'] = $this->modele->date_transformation;

			}

			if($mouvement_de_stock_apres_transformation['conditionnement_id'] != $conditionnement_id){

				$modifications_apres_transformation = true;

				$mouvement_de_stock_apres_transformation['conditionnement_id'] = $conditionnement_id;

			}

            $mouvement_de_stock_apres_transformation['reserve'] = 0;

            if(isset($modifications_apres_transformation) && $modifications_apres_transformation == true)
				$retour_apres_transformation = management('mouvement_de_stock',$mouvement_de_stock_apres_transformation['id'])->enregistre($mouvement_de_stock_apres_transformation);

		}

		if(isset($retour_apres_transformation) && $retour_apres_transformation != true)
			return $retour_apres_transformation;
		
		if(isset($retour_apres_transformation)){

			$transformation_mere = modele('transformation_stocks')->where('id', $this->modele->id)->first();

			if($transformation_mere != null)
				$transformation_mere = $transformation_mere->toArray();

			$transformation_mere['quantite_conditionnement_depart'] = $total_transforme / ($conditionnement_depart['quantite'] ?? 1);

			$transformation_mere['quantite_colisee'] = $total_transforme;

			$retour_transformation_mere = parent::enregistre($transformation_mere);

			if($retour_transformation_mere != true)
				return $retour_transformation_mere;

		}

		return true;

	}

}