<?php

use App\Eden\Managements\Cache_management;

/*
*
* Le cache est il activé ? Par défaut oui
* 
* @return bool true = cache actif, false sinon
*
*/
function cache_actif() {
	
	return env('EDENPME_CACHE', true);
}

function cache_temporaire($nom, $valeur = null) {
	
	return Cache_management::temporaire($nom, $valeur);
}

function cache_eden($cle, $callback) {

	return Cache_management::partage($cle, $callback);
}

function oublie_cache_eden($cle) {

	return Cache_management::partage_oublie($cle);
}

function lit_cache_eden($cle) {

	return Cache_management::partage_lit($cle);
}

function ecrit_cache_eden($cle, $valeur) {

	return Cache_management::partage_ecrit($cle, $valeur);
}
