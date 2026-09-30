<?php

namespace App\Eden\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Eden\Managements\Cache_management;

use App\Eden\Models\Table_libre;
use App\Eden\Models\Liste_libre;

class Cache_controller extends Controller {

	/**
	 * 
	 * Permet de vider le cache Eden qui est en session
	 * 
	 */
    public function vider() {
		
		Cache_management::vider_tout();

		return response()->json(true);
    }

    /**
     *
     * Permet de générer les fichiers js des composants
     *
     */
    public function genere_fichiers_composants() {

        Cache_management::vider();

        Cache_management::genere_fichiers_composants();

        return response()->json(true);
    }

    /**
     *
     * Permet de générer les fichiers js des composants
     *
     */
    public function genere_fichiers_composants_liste($type_a_generer = false,$valeur_a_generer= false) {

        session()->forget('cache.formulaire');

        if($type_a_generer == false || $valeur_a_generer == false || $type_a_generer == 'id'){
            Cache_management::generation_liste_libre($valeur_a_generer);
        }
        elseif($type_a_generer == 'element') {

            $liste_libres = Liste_libre::where('type_element', $valeur_a_generer)->get();

            foreach ($liste_libres as $liste_libre) {

                Cache_management::generation_liste_libre($liste_libre->id);
            }
        }

        return response()->json(true);
    }

     /**
     *
     * Permet de générer les fichiers js des menus
     *
     */
    public function genere_menus($id_profil_menu = false) {

        Cache_management::vider();

        Cache_management::genere_menus($id_profil_menu);

        return response()->json(true);
    }

     /**
     *
     * Permet de générer le fichier css
     *
     */
    public function genere_css() {

        Cache_management::vider();

        Cache_management::genere_fichier_css();

        return response()->json(true);
    }

    /**
     *
     * Permet de générer les fichiers js des modules
     *
     */
    public function genere_fichiers_composants_module($nom_module = false) {

        Cache_management::vider();

        Cache_management::generation_module($nom_module);

        return response()->json(true);
    }

    /**
	 *
	 * Affiche les différentes options de génération de composants
	 *
	 */
    public function generer_module() {

		return view('eden::composants_vue.generer_module');
    }

    /**
	 *
	 *  Permet de générer les valeurs des listes libres et des listes formatées dans un fichier
	 *
	 */
    public function genere_valeurs_champs_listes() {

        Cache_management::genere_valeurs_champs_listes();

        return response()->json(true);
    }

    /**
     *
     * Permet de générer le fichier de traduction
     *
     */
    public function genere_traductions() {

        Cache_management::vider();

        Cache_management::genere_traductions();

        return response()->json(true);
    }
}
