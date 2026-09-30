<?php

/*
*
* Helper pour aller chercher le management pour envoyer les mails
*
* @param $type_element string
*
*/
function email() {

	return new \App\Eden\Managements\Email_management;
}

function verifie_email_valide($email){
	
	return filter_var($email, FILTER_VALIDATE_EMAIL);
}