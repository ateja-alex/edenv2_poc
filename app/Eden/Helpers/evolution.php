<?php

/**
 * 
 * Retourne l'évolution entre $a et $b, $b étant la valeur la plus ancienne
 * 
 */
function evolution($a, $b, $mise_en_forme = true) {
	
	if(empty($b))
		return 'n/a';
	
	$difference = $a - $b;
	
	return round($difference / $b * 100, 2).' %';
}