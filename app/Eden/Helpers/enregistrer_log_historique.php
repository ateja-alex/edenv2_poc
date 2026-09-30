<?php

/*
*
* Helper pour enregistrer les logs des historique
*
*
*/
function enregistrer_log_historique($url,$nom_page) {
	
	if(empty(moi())) 
		return;
		
	$log_historique = modele('log_historique');
	$log_historique->date = date('Y-m-d H:i:s');
	$log_historique->url=$url;
	$log_historique->utilisateur_id = moi()->id;
	$log_historique->nom_page = $nom_page;
	$log_historique->save();
	
}