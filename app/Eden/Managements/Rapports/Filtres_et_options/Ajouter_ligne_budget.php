<?php

namespace App\Eden\Managements\Rapports\Filtres_et_options;

use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Variables;

/**
 *
 * Filtres pour les dates mensuelles (choisir une période)
 *
 */
class Ajouter_ligne_budget {
	
	/**
	 * 
	 * Applique le filtre dates mensuelles
	 * 
	 */	
    public static function applique($rapport) {
		if(isset($rapport->parametres)) {
			
			$parametres = $rapport->parametres;
		}
		else {
			
			$parametres = Rapports_management::recupere_parametres($rapport->id_rapport);
		}

		if(!empty(request()->nom_ligne)){

			$nom_ligne = request()->nom_ligne;

		}
		else if(!empty(request()->nom_post)){

			$nom_ligne = request()->nom_post;
			$type_ligne = "poste";
			$rubrique_id = request()->post_rubrique_id;
			$article_id = request()->post_article_id;

		}
		else if(!empty(request()->new_nom_ligne)){

			$nom_ligne = request()->new_nom_ligne;
			$type_ligne = request()->type_ligne_edit;
			$rubrique_id = request()->edit_rubrique_id;
			$article_id = request()->edit_article_id;

		}
		else{

			$nom_ligne = "";

		}

		if(!empty(request()->type_ligne) && !isset($type_ligne)){

			$type_ligne = request()->type_ligne;

		}
		else if(!isset($type_ligne)){

			$type_ligne = "";

		}

		if(!empty(request()->rubrique_id) && !isset($rubrique_id)){

			$rubrique_id = request()->rubrique_id;

		}
		else if(!isset($rubrique_id)){

			$rubrique_id = "";

		}

		if(!empty(request()->article_id) && !isset($article_id)){

			$article_id = request()->article_id;

		}
		else if(!isset($article_id)){

			$article_id = "";

		}
		
		$familles = modele('famille')->get()->toArray();
		$articles = modele('article')->get()->toArray();
		$rubriques = modele('budget_rubrique')->get()->toArray();
		
		$rapport->option('ajouter_ligne_budget',['rubriques' => $rubriques , 'familles' => $familles , 'articles' => $articles]);
		
		if(!empty(request()->nom_ligne) || !empty(request()->nom_post) || !empty(request()->new_nom_ligne)){

			self::ajout_ligne($nom_ligne,$type_ligne,$article_id,$rubrique_id,$rapport);

		}
    }

	private static function ajout_ligne($nom , $type, $article_id = "" , $rubrique_id = "",$rapport){

        $id = request()->id;

        if($type == 'rubrique'){

			$type_rubrique = request()->type_de_rubrique;

            $rubrique = management('budget_rubrique',$id);
            $retour = $rubrique->enregistre(array('nom' => $nom , 'type_de_rubrique' => $type_rubrique));

        }
        else{

            $poste = management('budget_poste',$id);
            $retour = $poste->enregistre(array('nom' => $nom, 'rubrique_id' => $rubrique_id, 'article_id' => $article_id));

        }

		$familles = modele('famille')->get()->toArray();
		$articles = modele('article')->get()->toArray();
		$rubriques = modele('budget_rubrique')->get()->toArray();

		$rapport->option('ajouter_ligne_budget',['rubriques' => $rubriques , 'familles' => $familles , 'articles' => $articles]);
    }
	
}