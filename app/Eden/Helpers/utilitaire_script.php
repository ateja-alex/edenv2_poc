<?php

/*
*
* Helper pour aller chercher les calendriers synchro d'une table libre
*
* @param $type_element string
*
*/
function calendrier_synchro($type_element) {

	return cache_eden('calendrier_synchro.'.$type_element, function() use ($type_element) {

		return modele('calendrier_synchro')
                            ->where('type_element_synchro', $type_element)
                            ->get();
	});
}

/*
*
* Helper pour aller chercher les workflow d'une table libre
*
* @param $type_element string
*
*/
function workflows($type_element) {

	return cache_eden('workflows.'.$type_element, function() use ($type_element) {

		return modele('workflow')
            ->where('type_element', $type_element)
            ->get();
	});
}