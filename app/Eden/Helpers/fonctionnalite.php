<?php

/*
*
* Retourne la config d'un fonctionnalité
*
*/

$fonctionnalites_eden_helper = false;

function fonctionnalite($nom) {
	
	global $fonctionnalites_eden_helper;

	if(empty($fonctionnalites_eden_helper)) {
		
		$fonctionnalites_eden_helper = array_merge(config('fonctionnalites'),config('fonctionnalites_integrations'));
	}

    if(!empty(moi()->profil_id) && isset($fonctionnalites_eden_helper['fonctionnalites_par_profil'][$nom][moi()->profil_id]))
        return $fonctionnalites_eden_helper['fonctionnalites_par_profil'][$nom][moi()->profil_id];
	else if(isset($fonctionnalites_eden_helper[$nom]))
        return $fonctionnalites_eden_helper[$nom];

	return null;
}
