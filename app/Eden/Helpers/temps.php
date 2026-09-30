<?php

/*
*
* Retourne une nombre d'heures et de minutes, pour un nombre de minutes
*
*/
function temps($minutes) {
	
	$heures = floor($minutes / 60);
	
	$minutes = $minutes - ($heures * 60);
	
	if($heures == 0)
		$heures = '00';
	
	if($minutes == 0)
		$minutes = '00';
	
	return $heures.'h'.$minutes;
}

