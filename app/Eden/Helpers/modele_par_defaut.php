<?php

/**
 *
 * Helper pour enlever les accents et caractères spéciaux d'une chaine
 *
 * @param $chaine string
 * @param $separateur string
 *
 */
function modele_par_defaut($type_element) {

	if(session()->has('cache.modeles_par_defaut.'.$type_element) && cache_actif()) {

        $modele_par_defaut = clone session()->get('cache.modeles_par_defaut.'.$type_element);

		traitement_des_variables_a_remplacer($modele_par_defaut,$type_element);

        return $modele_par_defaut;
	}

	$modele_par_defaut = management($type_element)->modele_par_defaut();

	session()->put('cache.modeles_par_defaut.'.$type_element, clone $modele_par_defaut);

    return $modele_par_defaut;
}

/**
 *
 * Mise en place d'un traitement des valeurs par défaut pour les dates
 *
 *
 */
function traitement_des_variables_a_remplacer(&$modele_par_defaut,$type_element){

	if(session()->has('cache.modeles_par_defaut.champs_date.'.$type_element) && cache_actif()) {

		$champs_libres_a_remplacer = session()->get('cache.modeles_par_defaut.champs_date.'.$type_element);
	}
	else {

		$champs_libres_a_remplacer = \App\Eden\Models\Champ_libre::where('type_element',$type_element)
			->where(function($requete){
				$requete->orWhereIn('type',array(4,5,'4','5'));
			})
			->whereNotNull('valeur_defaut')
			->where('valeur_defaut', '!=', '')
			->get();

		session()->put('cache.modeles_par_defaut.champs_date.'.$type_element, $champs_libres_a_remplacer);
	}

	$management_element = false;

    foreach($champs_libres_a_remplacer as $champ_libre) {

		if($management_element === false)
			$management_element = management($type_element);

        $modele_par_defaut->{$champ_libre->nom_sql} = $management_element->remplace_variables_modele_par_defaut($champ_libre);
	}

    return $modele_par_defaut;
}
