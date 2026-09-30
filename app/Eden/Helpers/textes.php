<?php

/**
 *
 * Ajoute des "..." si le texte est trop long
 *
 */
function tronque($str, $nb = 150) {

	if (strlen($str) > $nb) {

		$str             = substr($str, 0, $nb);
		$position_espace = strrpos($str, " ");
		$texte           = substr($str, 0, $position_espace);
		$str             = $texte."...";
	}

	return $str;
}

/**
 * 
 * Retourne la valeur d'une string entre deux mots/caractères
 * 
 */
function valeur_entre_deux_mots($string, $debut, $fin) {

	$string = ' ' . $string;
	$ini = strpos($string, $debut);

	if ($ini == 0) return '';

	$ini += strlen($debut);
	$len = strpos($string, $fin, $ini) - $ini;

	return substr($string, $ini, $len);
}