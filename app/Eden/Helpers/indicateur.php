<?php

/*
*
* Helper pour aller chercher un management pour un élément
*
* @param $type_element string
*
*/
function indicateur($indicateur) {
	

	
	$tests = array(
		
		"\\App\\Managements\\Indicateurs\\".ucfirst($indicateur),
		"\\App\\Eden\\Managements\\Indicateurs\\".ucfirst($indicateur),
	);

	$classe = classe_existante($tests);

	if(empty($classe))
		return null;

	return new $classe();
}