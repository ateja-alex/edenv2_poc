<?php

/*
*
* Retourne l'id local du compte système Eden (utilisateur cle_externe = 16).
*
* La résolution est mise en cache LV pour ne pas requêter à chaque
* enregistrement d'élément.
*
* @return int id de l'utilisateur système, ou NULL s'il n'est pas encore synchronisé
*
*/
function id_utilisateur_systeme() {

	// cle_externe = 16 : compte système Eden. avec_inactifs() : l'id reste valide pour la FK
	// même si le compte est désactivé. Fallback NULL si non encore synchronisé.
	// On ne met en cache que l'id résolu : tant que le compte n'est pas synchronisé
	// (NULL), on continue à requêter pour le récupérer dès qu'il apparaît.
	return cache_eden('utilisateur_systeme', function() {

		return modele('utilisateur')->avec_inactifs()->where('cle_externe', 16)->value('id') ?? null;
	});
}
