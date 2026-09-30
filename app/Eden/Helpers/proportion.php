<?php

/**
 * 
 * Retourne la proportion entre $a et $b, $b étant la valeur dénominateur
 * 
 */
function proportion($a, $b, $mise_en_forme = true) {
	
	if(empty($b))
		return 'n/a';
	
	return round($a / $b * 100, 2).' %';
}