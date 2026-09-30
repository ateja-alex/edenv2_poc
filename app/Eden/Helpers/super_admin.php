<?php
function super_admin($utilisateur = false) {

	if($utilisateur == false)
		$utilisateur = moi();

	if($utilisateur !== null)
		return ($utilisateur->super_admin == 1);
	else
		return 0;
}

function editeur($utilisateur = false) {

	if($utilisateur == false) {
        $utilisateur = moi();

        if(session()->has('eden_usurpation_origine') && session()->has('activer_recuperer_droits_compte_initial'))
            $utilisateur = session()->get('eden_usurpation_origine');
    }

	if($utilisateur === null)
		return false;

	if(super_admin($utilisateur) === true)
		return true;

	if($utilisateur->type_utilisateur == 2)
		return true;

	return false;
}

function admin($utilisateur = false) {

	if($utilisateur == false) {

        $utilisateur = moi();

         if(session()->has('eden_usurpation_origine') && session()->has('activer_recuperer_droits_compte_initial'))
            $utilisateur = session()->get('eden_usurpation_origine');
    }

    if($utilisateur === null)
        return false;

    if(editeur($utilisateur) === true)
        return true;

    if($utilisateur->type_utilisateur == 1)
        return true;

    return false;
}