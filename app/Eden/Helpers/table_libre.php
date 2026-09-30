<?php

use \App\Eden\Models\Table_libre;
use \App\Eden\Models\Champ_libre;

use \App\Eden\Managements\Parametrage\Champ_libre_management;

use \App\Eden\Champs\Champ;
use \App\Eden\Champs\Champ_texte;
use \App\Eden\Champs\Champ_recherche_element;
use \App\Eden\Champs\Champ_date;
use \App\Eden\Champs\Champ_datetime;
use \App\Eden\Champs\Champ_time;
use \App\Eden\Champs\Champ_liste_preenregistree;
use \App\Eden\Champs\Champ_liste_libre;
use \App\Eden\Champs\Champ_montant_decimal;
use \App\Eden\Champs\Champ_montant_entier;
use \App\Eden\Champs\Champ_piece_jointe;
use \App\Eden\Champs\Champ_textarea;
use \App\Eden\Champs\Champ_texte_couleur;
use \App\Eden\Champs\Champ_liste_preenregistree_checkbox;
use \App\Eden\Champs\Champ_liste_valeurs_libre_checkbox;
use \App\Eden\Champs\Champ_note;
use \App\Eden\Champs\Champ_html;
use \App\Eden\Champs\Champ_numero_automatique;
use \App\Eden\Champs\Champ_sous_formulaire;
use \App\Eden\Champs\Champ_dropzone;
use \App\Eden\Champs\Champ_tableau;
use \App\Eden\Champs\Champ_telephone;
use \App\Eden\Champs\Champ_pourcentage;
use \App\Eden\Champs\Champ_type_element_dynamique;
use \App\Eden\Champs\Champ_id_element_dynamique;
use \App\Eden\Champs\Champ_multiple;



/*
*
* Helper pour aller chercher les infos d'une table libre
*
* @param $type_element string
*
*/
function table_libre($type_element) {
	
	$table = cache_eden('table_libre.'.$type_element, fn() => Table_libre::where('type_element', $type_element)->first());

	if($table === null)
		exception("La table ".$type_element." n'existe pas");

	return clone $table;
}


/*
*
* Helper pour retourner la présence - ou non - d'une table libre
*
* @param $type_element string
*
*/
function table_libre_existe($type_element) {

    return !empty(cache_eden('table_libre.'.$type_element, fn() => Table_libre::where('type_element', $type_element)->first()));
}

/*
*
* Helper pour retourner le type element en focntion de l'id
*
*/
function type_element_depuis_id($id) {

    $tables = cache_eden('type_elements', fn() => Table_libre::get()->pluck('type_element','id')->toArray());

    if(!isset($tables[$id])) {

        oublie_cache_eden('type_elements');

        $tables = cache_eden('type_elements', fn() => Table_libre::get()->pluck('type_element','id')->toArray());
    }

    return $tables[$id];
}

/*
*
* Helper pour aller chercher les infos d'un champ libre 
*
* @param $type_element string
* @param $nom_champ string
*
* @return instance champ_libre_management
*
*/
function champ_libre($type_element, $nom_sql = false) {
	
	/**
	@todo gérer le cache
	*/
	
	if(is_object($type_element) && $nom_sql === false) {
		
		$nom_sql = $type_element->nom_sql;
		$type_element = $type_element->table;
	}
	
	$retour = new Champ_libre_management($type_element, $nom_sql);
	
	return $retour;
}

/*
*
* Helper pour aller chercher le modèle d'un champ libre
*
*/
function champ_libre_modele($type_element, $nom_sql) {
	
	$champ = cache_eden('champ_libre.'.$type_element.'.'.$nom_sql, function() use ($type_element, $nom_sql) {

		return Champ_libre::where('type_element', $type_element)->where('nom_sql', $nom_sql)->first();
	});

	if($champ == null)
		return null;

	return clone $champ;
}

/*
*
* Helper pour aller chercher le bon type de champ (input, select...)
*
* @param $champ_libre objet
*
* @return instance champ
*
*/
function champ($champ_libre, $valeur = '') {
	
	/**
	 * 
	 * @todo gérer le cache
	 * 
	 * @note Frédéric 08/06/2022 : le cache ne semble pas essentiel ici car a priori cela est très rapide
	 * 
	 */
	$tests = array(
		
		// on regarde dans les personnalisations
		"\\App\\Champs\\".ucfirst($champ_libre->type_element)."\\".ucfirst($champ_libre->nom_sql),
		
		// puis dans les champs eden spéciaux
		"\\App\\Eden\\Champs\\".ucfirst($champ_libre->type_element)."\\".ucfirst($champ_libre->nom_sql),
	);
	
	foreach($tests as $classe) {
		
		if(class_exists($classe)) {
			
			$champ = new $classe($champ_libre, $valeur);
			
			return $champ;
		}
	}
	
	// champ non trouvé, on regarde le type, et on renvoie le champ par défaut
	if($champ_libre->type == -5)
		return new Champ_sous_formulaire($champ_libre, $valeur);
	
	if($champ_libre->type == -4)
		return new Champ($champ_libre, $valeur);
	
	if($champ_libre->type == -3)
		return new Champ($champ_libre, $valeur);
	
	if($champ_libre->type == -2)
		return new Champ_html($champ_libre, $valeur);

	if($champ_libre->type == -1)
		return new Champ($champ_libre, $valeur);
	
	if($champ_libre->type == 0) {

	    if($champ_libre->format_champ == 'numero_telephone')
            return new Champ_telephone($champ_libre, $valeur);

        return new Champ_texte($champ_libre, $valeur);
    }
	
	if($champ_libre->type == 1) 
		return new Champ_liste_libre($champ_libre, $valeur);
	
	if($champ_libre->type == 2)
		return new Champ_montant_entier($champ_libre, $valeur);
	
	if($champ_libre->type == 3)
		return new Champ_montant_decimal($champ_libre, $valeur);
	
	if(in_array($champ_libre->type, [4, 18]))
		return new Champ_date($champ_libre, $valeur);
	
	if($champ_libre->type == 5)
		return new Champ_datetime($champ_libre, $valeur);
	
	if($champ_libre->type == 6)
		return new Champ_textarea($champ_libre, $valeur);
	
	if($champ_libre->type == 7)
		return new Champ_piece_jointe($champ_libre, $valeur);
	
	if($champ_libre->type == 8)
		return new Champ_time($champ_libre, $valeur);

	if($champ_libre->type == 9)
		return new Champ_texte_couleur($champ_libre, $valeur);
	
	if($champ_libre->type == 13)
		return new Champ_note($champ_libre, $valeur);

	if($champ_libre->type == 14)
		return new Champ_numero_automatique($champ_libre, $valeur);

	if($champ_libre->type == 15)
		return new Champ_dropzone($champ_libre, $valeur);

	if($champ_libre->type == 16)
		return new Champ_tableau($champ_libre, $valeur);

    if($champ_libre->type == 17)
        return new Champ_pourcentage($champ_libre, $valeur);
	
	if($champ_libre->type == 20) 
		return new Champ_liste_preenregistree($champ_libre, $valeur);

    if($champ_libre->type == 21)
        return new Champ_type_element_dynamique($champ_libre, $valeur);

    if($champ_libre->type == 22)
        return new Champ_id_element_dynamique($champ_libre, $valeur);
	
	if($champ_libre->type == 40 || $champ_libre->type == 42)
		return new Champ_recherche_element($champ_libre, $valeur);

    if($champ_libre->type == 10) {
		
		$champ_ref = clone $champ_libre;
		$champ_ref->type = $champ_libre->type_reference;
		$champ_enfant = champ($champ_ref, $valeur);
		return new Champ_multiple($champ_libre, $champ_enfant, $valeur);
    }
}
