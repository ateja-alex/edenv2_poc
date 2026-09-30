<?php

/**
 * 
 * Helper pour initialiser les tableau (dans les boucles par exemple)
 * 
 * Exemple d'utilisation : 
 * 
 * initialise_tableau($tableau, 0, '2019-01-01', 'quantite');
 * 
 * est équivalent à :
 * 
 * if(!isset($tableau['2019-01-01']))
 * 		$tableau['2019-01-01'] = array();
 * 
 * if(!isset($tableau['2019-01-01']['quantite']))
 * 		$tableau['2019-01-01']['quantite'] = 0;
 * 
 * à noter que $valeur_defaut sera en général un array vide, ou 0 (généralement)
 * et que celle valeur par défaut est utilisé que pour la dernière dimension. 
 * Par exemple si le tableau a 2 dimensions, la valeur de la dimension 1 lorsqu'elle sera initialisée, sera forcément array() 
 * (puisqu'il y aura une deuxième dimension ajoutée par la suite)
 * 
 * Cela sous entend donc qu'il faut forcément mettre toutes les dimensions dès le 1er appel, et ne surtout pas faire :
 * 
 * initialise_tableau($tableau, 0, '2019-01-01');
 * initialise_tableau($tableau, 0, '2019-01-01', 'quantite');
 * 
 * Sinon la 1ere dimension sera initialisée telle que : $tableau['2019-01-01'] = 0;
 * 
 */
function initialise_tableau(&$tableau, $valeur_defaut, $dimension_1, $dimension_2 = false, $dimension_3 = false, $dimension_4 = false, $dimension_5 = false) {
	
	if(!isset($tableau[$dimension_1])) {
		
		if($dimension_2 !== false)
			$tableau[$dimension_1] = array();
		else
			$tableau[$dimension_1] = $valeur_defaut;
	}
	
	if(!isset($tableau[$dimension_1][$dimension_2]) && $dimension_2 !== false) {
		
		if($dimension_3 !== false)
			$tableau[$dimension_1][$dimension_2] = array();
		else
			$tableau[$dimension_1][$dimension_2] = $valeur_defaut;
	}
	
	if(!isset($tableau[$dimension_1][$dimension_2][$dimension_3]) && $dimension_3 !== false) {
		
		if($dimension_4 !== false)
			$tableau[$dimension_1][$dimension_2][$dimension_3] = array();
		else
			$tableau[$dimension_1][$dimension_2][$dimension_3] = $valeur_defaut;
	}
	
	if(!isset($tableau[$dimension_1][$dimension_2][$dimension_3][$dimension_4]) && $dimension_4 !== false) {
		
		if($dimension_5 !== false)
			$tableau[$dimension_1][$dimension_2][$dimension_3][$dimension_4] = array();
		else
			$tableau[$dimension_1][$dimension_2][$dimension_3][$dimension_4] = $valeur_defaut;
	}
	
	// note : le helper n'est pas prévu pour avoir plus de 5 dimensions, 
	// si cela arrive, il faut donc modifier cette fonction pour ajouter des dimensions
	if(!isset($tableau[$dimension_1][$dimension_2][$dimension_3][$dimension_4][$dimension_5]) && $dimension_5 !== false) {
		
		$tableau[$dimension_1][$dimension_2][$dimension_3][$dimension_4][$dimension_5] = $valeur_defaut;
	}
	
	
}