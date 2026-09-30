<?php

/*
*
* Fonction qui sert à créer une erreur et l'afficher à la mode laravel
*
* @param $erreur string le texte de l'erreur
*
*/
function exception($erreur) {
		
	throw new \App\Eden\Exceptions\Eden_exception($erreur);
}