<?php

namespace App\Eden\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Eden\Managements\Recherche_management;

class Recherche_controller extends Controller
{
    
    /**
	 *
     * Récupère les résultats de la recherche sur index_recherche
     *
     */
    public function resultats_recherche(Request $requete) {

        $management =  new Recherche_management();

        $recherche = $requete->input('recherche_globale_sur_appli');

        list($resultats, $types_elements) =  $management->recuperer_resultats_recherche($recherche);
		
		$affichage = array();
		
		$affichage_trouve = false;
		
		foreach($types_elements as $type_element => $osef) {
			
			if(fonctionnalite('recherche_par_defaut') != '' && fonctionnalite('recherche_par_defaut') != 'tout') {
				
				if(fonctionnalite('recherche_par_defaut') == $type_element) {
					
					$affichage[$type_element] = true;
					$affichage_trouve = true;
				}
				else
					$affichage[$type_element] = false;
			} 
			else {
				
				$affichage[$type_element] = true;
			}
		}
		
		if($affichage_trouve === false) {
			
			foreach($affichage as $type_element => $osef) {
				
				$affichage[$type_element] = true;
				break;
			}
		}

		// Cas spécial des articles, si nomenclature, on modifie légèrement le lien
		foreach ($resultats as &$resultat) {
			
			if ($resultat['type_element'] == "article") {
				
				$modele_article = modele('article',$resultat['id_element']);

				if($modele_article->type_article === 1)
					$resultat['lien'] = $resultat['lien'].' <span class="badge badge-warning">Nomenclature (non stockable)</span>';
			}
		}
		
        return response()->json(array('resultats' => $resultats, 'types_elements' => $types_elements, 'affichage' => $affichage));
    }

	 /**
	 *
     * Récupère les résultats similaires pour le dédoublonnage
     *
     */
	public function dedoublonnage($type_element){

		$valeurs_recherches = request()->valeurs_recherches;

		$management =  new Recherche_management();

        $resultats =  $management->dedoublonnage($valeurs_recherches, $type_element);

		return response()->json($resultats);
	}
}
