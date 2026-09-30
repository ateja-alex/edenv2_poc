<?php

namespace App\Eden\Controllers\Parametrage;

use App\Eden\Managements\Cache_management;
use App\Http\Controllers\Controller;
use App\Eden\Managements\Parametrage\Menus_management;
use Illuminate\Http\Request;

class Menus_controller extends Controller {

    /**
	 *
	 * Gère les menus de l'ERP
	 *
	 */
	public function menus($extranet = null) {

        $menu_extranet = $extranet === 'extranet';

        $management_menus = new Menus_management();

        $menus = $management_menus->recuperation_menus($menu_extranet);

        return view('eden::parametrage.menus',['menus' => $menus, 'extranet' => $menu_extranet]);
	}

	public function donnees_selection_routes(){

		$management_menus = new Menus_management();

        return $management_menus->donnees_selection_routes();
	}

    public function changement_ordre_menus(Request $requete){

        $donnees = $requete->get('ordre_menus');

		if(isset($donnees['menus_categories'])){

			$menus_categories = modele('menus_categories')->whereIn('id', array_keys($donnees['menus_categories']))->get()->keyBy('id');
			$management_menus_categories = management('menus_categories');
		}

		if(isset($donnees['menus_liens'])){

			$menus_liens = modele('menus_liens')->whereIn('id', array_keys($donnees['menus_liens']))->get()->keyBy('id');
			$management_menus_liens = management('menus_liens');
		}

        foreach($donnees as $type_element => $elements){

			foreach($elements as $id_element => $modifications_element){

				${'management_' . $type_element}->modele = $$type_element[$id_element];
				${'management_' . $type_element}->enregistre_modele($modifications_element);
			}
        }

		Cache_management::genere_menus();

		return response()->json(['succes' => true]);
	}

    public function desactivation_menu(Request $requete){

        $donnees = $requete->all();
		
		$valeur = $donnees['valeur'] === 'true' || $donnees['valeur'] === true;

		modele($donnees['type_element'])->where('id', $donnees['id_menu'])->update(['desactive' => $valeur]);

		if($donnees['type_element'] === 'menus_categories')
			modele('menus_liens')->where('id_categorie_parent', $donnees['id_menu'])->update(['desactive' => $valeur]);

		Cache_management::genere_menus();

        return response()->json(['succes' => true]);
	}
}
