<?php

/**
 * 
 * Note : pour afficher le récapitulatif des temps d'execution, il faut ajoutr dans l'url ?temps_execution=1
 * 
 */

$temps_execution_eden = array();

/**
 *
 * Ajoute une entrée dans le tableau des temps d'execution
 *
 */
function temps_execution($nom, $rang = 0) {
	
	if(!request()->has('temps_execution'))
		return true;
	
	if(request()->get('temps_execution') == 'total' && $nom != 'fin template')
		return true;
	
	global $temps_execution_eden;
	
	if($rang != 0) {
		
		for($i=1; $i <= $rang; $i++) {
			
			$nom = '.........'.$nom;
		}
	}
	
	$temps_execution_eden[] = array('nom' => $nom, 'temps' => round(microtime(true) - LARAVEL_START, 4));
}

/**
 *
 * Affiche un récapitulatif des temps d'execution de la page
 *
 */
function temps_execution_recapitulatif($details = false) {
	
	global $temps_execution_eden;
	
	if(empty($temps_execution_eden))
		return;
	
	if($details === false) {
		
		$liste_temps = array();
		$dernier_temps = false;
		
		foreach($temps_execution_eden as $temps) {
			
			if(empty($dernier_temps)) {
				
				$liste_temps[$temps['nom']] = $temps['temps'];
			}
			else {
				
				$difference = round($temps['temps'] - $dernier_temps, 2);
				
				$index = $temps['nom'];
				$numero_index = 0;
				
				while(isset($liste_temps[$index])) {
					
					$index = $temps['nom'].'_'.$numero_index;
					$numero_index++;
				}
				
				if($difference <= request()->get('temps_execution')) {
					
					$liste_temps[$index] = $temps['temps'].' ('.$difference.')';
				}
				else {
					
					$liste_temps[$index] = $temps['temps'].' ('.$difference.') !!!!!!!!!!!!';
				}
			}
			
			$dernier_temps = $temps['temps'];
		}
		
		dd($liste_temps);
	}
	
	dd($temps_execution_eden);
}
