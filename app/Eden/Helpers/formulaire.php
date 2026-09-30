<?php

/*
*
* Helper pour aller chercher un formulaire
*
* @param $type_element string
*
*/
function formulaire($nom_formulaire, $contexte = '', $sous_formulaire = '', $donnees_vue = [], $uniquement_champs_editables = false, $type_element_formulaire_parent = null, $options = []) {

    if($_SERVER['HTTP_HOST'] == fonctionnalite('url_extranet') && ($contexte == '' || $contexte == 'fiche_'))
        $contexte = 'extranet_';

	$formulaire_parametrable = App\Eden\Models\Formulaire::where('nom_formulaire', $contexte.$nom_formulaire)->first();

	if(empty($formulaire_parametrable) && $contexte !== '')
		$formulaire_parametrable = App\Eden\Models\Formulaire::where('nom_formulaire', $nom_formulaire)->first();

	if($formulaire_parametrable !== null && !empty($formulaire_parametrable->type_element))
		$type_element = $formulaire_parametrable->type_element;
	else
		$type_element = $nom_formulaire;

	$name = '';
	if(!empty($sous_formulaire)) {

		list($osef, $name) = explode('.', $sous_formulaire);
	}

    $nom_cache = "cache.formulaire.";
    $nom_cache_formulaire_editable = "cache.formulaire_uniquement_editables.";

    if($type_element_formulaire_parent == null && isset($options->type_element_formulaire_parent))
        $type_element_formulaire_parent = $options->type_element_formulaire_parent;

    if($type_element_formulaire_parent !== null){

        $nom_cache = "cache.sous_formulaire." . $type_element_formulaire_parent . "." ;
        $nom_cache_formulaire_editable = "cache.sous_formulaire_uniquement_editables." . $type_element_formulaire_parent . ".";

    }

	if(Session::has($nom_cache . $type_element.'.'.$contexte.$nom_formulaire.'.vue') && cache_actif() && $contexte != 'extranet_') {

        $vue = session($nom_cache . $type_element.'.'.$contexte.$nom_formulaire.'.vue');

        if(empty($options)) {
            if ($uniquement_champs_editables && Session::has($nom_cache_formulaire_editable . $contexte . $nom_formulaire . $vue . '.vue_render')) {
                return session($nom_cache_formulaire_editable . $contexte . $nom_formulaire . $vue . '.vue_render');
            } else if (!$uniquement_champs_editables && Session::has($nom_cache . $type_element . '.' . $contexte . $nom_formulaire . $vue . '.vue_render')) {
                return session($nom_cache . $type_element . '.' . $contexte . $nom_formulaire . $vue . '.vue_render');
            }
        }

        $formulaire_id = session($nom_cache . $type_element.'.'.$contexte.$nom_formulaire.'.formulaire_id');

		$vue_envoyee = view('eden::'.$vue, $donnees_vue + array(
			'type_element' => $type_element,
			'nom_formulaire' => $nom_formulaire,
			'formulaire_id' => $formulaire_id,
			'sous_formulaire' => $sous_formulaire,
			'name' => $name,
			'uniquement_champs_editables' => $uniquement_champs_editables,
            'type_element_formulaire_parent' => $type_element_formulaire_parent,
            'options' => $options,
		));

		// temps_execution('creation du formulaire '.$contexte.$nom_formulaire);
		$retour = $vue_envoyee->render();

		// temps_execution('fin creation du formulaire '.$contexte.$nom_formulaire);

		return $retour;
	}
	else {

		$formulaire = \App\Eden\Models\Formulaire::where('nom_formulaire',$contexte.$nom_formulaire)->first();

		$formulaire_id = null;
		$surcharger_la_vue = false;

		if($formulaire !== null) {

			$formulaire_id = $formulaire->nom_formulaire;
			$surcharger_la_vue = $formulaire->surcharger_la_vue;
		}
		else {

			if($contexte !== '') {

				$formulaire = \App\Eden\Models\Formulaire::where('nom_formulaire', $type_element)->first();

				if($formulaire !== null) {

					$formulaire_id = $formulaire->nom_formulaire;
					$surcharger_la_vue = $formulaire->surcharger_la_vue;
				}
			}
		}

		$cas = false;

		if(view()->exists('eden::formulaires.'.$contexte.$type_element) && empty($surcharger_la_vue)){

			$cas = 1;
			$vue = 'formulaires.'.$contexte.$type_element;
			$vue_envoyee = view('eden::formulaires.'.$contexte.$type_element, $donnees_vue + ['type_element_formulaire_parent' => $type_element_formulaire_parent]);
		}

		elseif(view()->exists('eden::formulaires.'.$type_element) && empty($surcharger_la_vue) && $nom_formulaire == $type_element){

			$cas = 2;
			$vue = "formulaires.$type_element";
			$vue_envoyee = view("eden::formulaires.$type_element", $donnees_vue + ['type_element_formulaire_parent' => $type_element_formulaire_parent]);
		}

		elseif($formulaire_id != null){

			$cas = 3;
			$vue = "formulaire";
			$vue_envoyee = view('eden::formulaire',[
				'type_element' => $type_element,
				'formulaire_id' => $formulaire_id,
				'sous_formulaire' => $sous_formulaire,
				'name' => $name,
				'uniquement_champs_editables' => $uniquement_champs_editables,
				'type_element_formulaire_parent' => $type_element_formulaire_parent,
                'options' => $options,
			]);

            //On enregistre en cache que s'il n'y a pas de champ vue et pas de data vuejs

            $champ_vue =\App\Eden\Models\Formulaires_champs::where('nom_formulaire', $formulaire_id)->where('type_champ',2)->first();

            $formulaire = \App\Eden\Models\Formulaire::where('nom_formulaire', $formulaire_id)->first();

            if($champ_vue == null && ($formulaire['vuejs_data'] == null || $formulaire['vuejs_data'] == '') && ($formulaire['vuejs_methods'] == null || $formulaire['vuejs_methods'] == '')){

                if($uniquement_champs_editables)
                    Session::put($nom_cache_formulaire_editable . $contexte.$nom_formulaire.$vue.'.vue_render', $vue_envoyee->render());
                else
                    Session::put($nom_cache . $type_element.'.'.$contexte.$nom_formulaire.$vue.'.vue_render', $vue_envoyee->render());
            }
        }

		else{

			$cas = 4;
			$vue = 'formulaires.formulaire_generique';
			$vue_envoyee = view('eden::formulaires.formulaire_generique',['type_element' => $type_element, 'uniquement_champs_editables' => $uniquement_champs_editables, 'type_element_formulaire_parent' => $type_element_formulaire_parent]);

            if($uniquement_champs_editables)
                Session::put($nom_cache_formulaire_editable . $contexte.$nom_formulaire.$vue.'.vue_render', $vue_envoyee->render());
            else
                Session::put($nom_cache . $type_element.'.'.$contexte.$nom_formulaire.$vue.'.vue_render', $vue_envoyee->render());
        }

        Session::put($nom_cache . $type_element . '.' . $contexte . $nom_formulaire . '.formulaire_id', $formulaire_id);
        Session::put($nom_cache . $type_element . '.' . $contexte . $nom_formulaire . '.vue', $vue);


		return $vue_envoyee->render();

	}
    
}
