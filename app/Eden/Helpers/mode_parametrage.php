<?php

/*
*
* Helper pour savoir si l'utilisateur connecté à le mode paramètrage d'activé ou non
*
* @param $type_element string
*
*/
function mode_parametrage() {

    if(empty(moi()))
        return false;

	if ((moi()->mode_parametrage === 1) && (moi()->type_utilisateur == 1 || moi()->type_utilisateur == 2))
		return true;
	else
		return false;

}
