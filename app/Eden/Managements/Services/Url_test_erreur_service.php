<?php

namespace App\Eden\Managements\Services;

use App\Eden\Managements\Parametrage\Liste_libre_management;

use App\Eden\Managements\Parametrage\Table_libre_management;
use App\Eden\Models\Table_libre;
use App\Eden\Models\Liste_libre;

/**
 *
 * Retourne les URL a tester
 *
 */
class Url_test_erreur_service {

    /**
     *
     * Retourne la liste des urls à tester pour vérifier qu'elle s'affiche correctement (pas d'erreur 500)
     *
     */
	public function retourne_url_a_tester(){

		$urls = array(
			// les listes
			'eden/liste/client',
			'eden/liste/fournisseur',
			'eden/liste/article',
			'eden/liste/famille',
			'eden/liste/echange',

			// pages divers
			'eden/accueil',
			'eden/parametrage/utilisateurs',
		);

		// on ajoute les fiches
		$fiches = array_unique(Table_libre::where('fiche',1)->get()->pluck('type_element')->toArray());

		foreach($fiches as $fiche_type_element) {

			$modele = modele($fiche_type_element);

            if(in_array($fiche_type_element,['ticket','wiki_eden']))
                continue;

            if($modele !== null)
                $id_element = $modele->first();

			if($id_element !== null)
				$urls[] = 'eden/fiche/'.$fiche_type_element.'/'.$id_element->id;
		}

        // Schema et hote vus par le client (derriere l'ingress : X-Forwarded-*)
        $a_supprimer = request()->getSchemeAndHttpHost().'/';

		// on ajoute les documents de gestion commerciale
		foreach(\App\Eden\Variables::$documents_gescom as $type_element) {

			$urls[] = 'eden/liste/'.$type_element;
			$urls[] = 'eden/document/'.$type_element;


			$element = modele($type_element)->first();

			if($element !== null) {
                $urls[] = 'eden/document/' . $type_element . '/' . $element->id;

                $transformations_possibles = management($type_element,$element->id)->transformations_possibles();

                if(isset($transformations_possibles['Ventes'])) {
                    foreach ($transformations_possibles['Ventes'] as $transformation_possible) {
                        if(strpos($transformation_possible,'supprimer') == 0 && substr($transformation_possible, 0, 1) != '#')
                            $urls[] = str_replace($a_supprimer, '', $transformation_possible);
                    }
                }

            }
        }

        $listes_libres_dynamique = Liste_libre::get()->toArray();

        foreach ($listes_libres_dynamique as $liste_libre){

            if(empty($liste_libre['id_rapport']))
                $urls[] = 'eden/liste/'.$liste_libre['type_element'];
//            else
//                $urls[] = 'eden/liste_libre_rapport/'.$liste_libre['id_rapport'];


        }

        // On ajoute les modules de trésorerie
        foreach(['charge_recurrente','creance_client','revenu_recurrent','rapprochement','mouvement_exceptionnel','visualisation_tresorerie'] as $element_treso) {
        	$urls[] = 'eden/tresorerie/'.$element_treso;
        }

		return collect($urls);
	}

	/**
     *
     * Retourne la liste des elements à tester, pour vérifier des fonctions basiques comme enregistrer, supprimer... (pas d'erreur 500)
     *
     */
	public function retourne_elements_a_tester(){

		$elements = ['client', 'fournisseur', 'article', 'projet', 'devis_vente', 'devis_achat', 'facture_vente', 'facture_achat', 'commande_vente', 'commande_achat', 'bl_vente', 'bl_achat', 'avoir_vente', 'avoir_achat'];

		$urls = [];
		foreach($elements as $element) {
			$urls[] = route('maintenance.test_url.element', [$element]);
		}

		return collect($urls);
	}

	/**
     *
     * Retourne la liste des features à tester,
     * Ce type de test est un test "logique", c'est à dire que l'on va faire des enregistrements avec certains paramètres,
     * et vérifier les résultats en bdd ensuite.
     *
     */
	public function retourne_feature_a_tester(){

		$elements = ['gestion_des_stocks',];

		$urls = [];
		foreach($elements as $element) {
			$urls[] = route('maintenance.test_url.feature', [$element]);
		}

		return collect($urls);
	}
}