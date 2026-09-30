<?php

/**
 * 
 * Retourne la liste des colonnes sur une table
 * 
 */
function bdd_colonnes($table) {
	
	return cache_eden('colonnes.'.$table, fn() => Schema::getColumnListing($table));
}
