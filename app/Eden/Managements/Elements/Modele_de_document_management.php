<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Cache_management;
use App\Eden\Models\Liste_libre;

class Modele_de_document_management extends Element_management {

	/**
	 * @cf description sur Element_management
	 * 
	 * On traite le cas particulier des coefficients
	 */
	public function enregistre($modifications = array(), $modele = false) {

		if((empty($this->modele) || empty($this->modele->id)) && empty($modifications['css'])) {
			
			$modifications['css'] = 
				"@page {
					margin: 200px 25px 100px 25px;
				}

				header {
					position: fixed;
					top: -150px;
					left: 0px;
					right: 0px;
					height: 450px;
				}

				footer {
					position: fixed; 
					bottom: -60px; 
					left: 0px; 
					right: 0px;
					height: 50px; 
				}

				#articles {

				}

				#articles thead tr {
					background:#ccc;
					font-weight: bold;
				}" ;
		}

		if(!empty($modifications['annexes'])) {
			$modifications['annexes'] = json_encode($modifications['annexes']);
		}
		
		return parent::enregistre($modifications, $modele);
	}

	public function types_de_document() {

		$types_de_document = \App\Eden\Champs\Champ::recuperer_valeur_listes_preenregistrees(71)['liste'];
		$array = \DB::table('modele_de_document_type_element')->where('cle_locale', $this->modele->id)->get()->pluck('valeur')->toArray();

		foreach ($array as $clef => $valeur) {
			$array[$clef] = $types_de_document[$valeur];
		}

		return $array;
	}

    public function methodes_post_modification($modele, $modele_avant, $modifications){
        parent::methodes_post_modification($modele, $modele_avant, $modifications);

        if(!empty($this->modele->type_element_autres)) {

            oublie_cache_eden('modeles_de_documents.'.$this->modele->type_element_autres);

            $liste_libres = Liste_libre::where('type_element', $this->modele->type_element_autres)->get();

            foreach ($liste_libres as $liste_libre) {

                Cache_management::generation_liste_libre($liste_libre->id);
            }
        }
    }

    public function methodes_post_suppression($modele){
        parent::methodes_post_suppression($modele);

        if(!empty($modele->type_element_autres)) {

            oublie_cache_eden('modeles_de_documents.'.$this->modele->type_element_autres);

            $liste_libres = Liste_libre::where('type_element', $modele->type_element_autres)->get();

            foreach ($liste_libres as $liste_libre) {

                Cache_management::generation_liste_libre($liste_libre->id);
            }
        }
    }

}