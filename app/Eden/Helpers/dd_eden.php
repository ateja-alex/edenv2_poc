<?php

/**
 *
 * Appelle la fonction dd, seulement si l'utilisateur est super_admin
 *
 */
function dd_eden(...$args) {
	
	if(moi() === null)
		return;
	
	if(moi()->super_admin == 1)
		ddd(...$args);
}

/**
 *
 * Appelle la fonction dump, seulement si l'utilisateur est super_admin
 *
 */
function dump_eden(...$args) {
	
	if(moi() === null)
		return;
	
	if(moi()->super_admin == 1)
		dump(...$args);
}