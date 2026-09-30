<?php

/*
*
* Fonction qui sert à formater une date dans le format voulu
*
* @param $format string similaire au format de date() standard de php
* @param $date string la date en question
*
*/
function formate_date($format, $date) {

	if($date == '' || is_array($date) || is_object($date)) {
		
		return null;
	}

	$date = str_replace('.', '-', $date);

	if(strlen($date) < 10) {

		return "";
	}

	// on repère le format initial
	if(strlen($date) == 10) {
		
		// date sans heure
		if(strpos($date, '-')) {
		
			list($annee, $mois, $jour) = explode('-', $date);
		}
		else {

			list($jour, $mois, $annee) = explode('/', $date);
		}
	}
	else {
		
		$date = str_replace('T', ' ', $date);
		
		list($date, $heure) = explode(' ', str_replace('  ', ' ', $date));

		// on traite la partie 1
		if(strpos($date, '-')) {
		
			list($annee, $mois, $jour) = explode('-', $date);
		}
		else {
			
			list($jour, $mois, $annee) = explode('/', $date);
		}

		// on récupère l'heure
		if(strlen($heure) == 5)
			$heure .= ':00';
			
		list($heure, $minute, $seconde) = explode(':', $heure);
	}

	// sans les 0
	$jour_ = (int) $jour;
	$mois_ = (int) $mois;
	$annee_ = (int) $annee;

	// numéro du jour de la semaine
	$numero_jour_semaine = date('N', mktime(0, 0, 0, $mois, $jour, $annee));
	
	// numéro de semaine
	$numero_semaine = date('W', mktime(0, 0, 0, $mois, $jour, $annee));
	
	$dernier_jour = date('t', mktime(0, 0, 0, $mois, $jour, $annee));
	
	// on crée le format
	$remplacements = array(
		
		'Y' => $annee,
		'y' => substr($annee, -2),
		'm' => $mois,
		'n' => $mois_,
		'd' => $jour,
		'j' => $jour_,
		't' => $dernier_jour,
		'H' => isset($heure) ? $heure : null,
		'i' => isset($minute) ? $minute : null,
		's' => isset($seconde) ? $seconde : null,
		'N' => $numero_jour_semaine,
		'W' => $numero_semaine,
		' ::' => '', // gestion des erreurs
	);

	return str_replace(array_keys($remplacements), $remplacements, $format);
}

/**
 * 
 * Affiche une date relative avec l'heure (par exemple "Aujourd'hui à 12h30")
 * 
 */
function heure_relative($date) {
	
	$date = formate_date("Y-m-d H:i:s", $date);
	
	$jour = formate_date("Y-m-d", $date);
	
	if($jour == date('Y-m-d')) {
		
		$texte = "Aujourd'hui";
	}
	
	elseif($jour == date('Y-m-d', strtotime('tomorrow'))) {
		
		$texte = "Demain";
	}
	
	elseif($jour == date('Y-m-d', strtotime('today +2 days'))) {
		
		$texte = "Après demain";
	}
	
	
	elseif($jour == date('Y-m-d', strtotime('yesterday'))) {
		
		$texte = "Hier";
	}
	
	elseif($jour == date('Y-m-d', strtotime('today -2 days'))) {
		
		$texte = "Avant hier";
	}
	
	else {
		
		$texte = "Le ".formate_date("d/m/Y", $date);
		
	}
	
	$texte .= " à ".formate_date('H:i', $date);
	
	
	return $texte;
}

/**
 * 
 * Retourne la date du dernier lundi pour une date donnée
 * 
 */
function lundi($date = false) {
	
	if($date === false)
		$date = date('Y-m-d');
	
	$date = formate_date('Y-m-d', $date);
	
	return date('Y-m-d', strtotime(date('Y-m-d', strtotime($date.' +1 day')).' last monday'));
}

/**
 * 
 * Retourne la date du prochain lundi pour une date donnée
 * 
 */
function lundi_prochain($date = false) {
	
	if($date === false)
		$date = date('Y-m-d');
	
	$date = formate_date('Y-m-d', $date);
	
	return date('Y-m-d', strtotime(date('Y-m-d', strtotime($date.' -1 day')).' next monday'));
}

/**
 * 
 * Retourne la date du dernier dimanche pour une date donnée
 * 
 */
function dimanche($date = false) {
	
	if($date === false)
		$date = date('Y-m-d');
	
	$date = formate_date('Y-m-d', $date);
	
	return date('Y-m-d', strtotime(date('Y-m-d', strtotime($date.' +1 day')).' last sunday'));
}

/**
 * 
 * Retourne la date du prochain dimanche pour une date donnée
 * 
 */
function dimanche_prochain($date = false) {
	
	if($date === false)
		$date = date('Y-m-d');
	
	$date = formate_date('Y-m-d', $date);
	
	return date('Y-m-d', strtotime(date('Y-m-d', strtotime($date.' -1 day')).' next sunday'));
}

/**
 * 
 * Retourne le nombre jours entre deux dates
 * 
 */
function nombre_de_jours_entre_deux_dates($date1, $date2) {

	return round(retourne_difference_date($date1, $date2) / (60 * 60 * 24));

}

/**
 * 
 * 
 * Retourne le nombre d'heures entre deux dates
 * 
 */
function nombre_d_heures_entre_deux_dates($date1, $date2) {

	return round(retourne_difference_date($date1, $date2) / (60 * 60));
}

function retourne_difference_date($date1, $date2) {

	$date1 = strtotime($date1); 
	$date2 = strtotime($date2); 

	return $date1 - $date2;
}

function retourne_jours_ouvres($date_debut, $nombre_jours) {

	$i = 0;

	while($i <= $nombre_jours) {

		$jour = date('N', strtotime($date_debut));

		if($jour == 6 || $jour == 7) {

			$date_debut = date('Y-m-d', strtotime( $date_debut . " +1 days"));
		}
		else {

			$date_debut = date('Y-m-d', strtotime( $date_debut . " +1 days"));

			$i++;
		}
	}

	return $i;
}

function retourne_prochaine_date_ouvrable($jours, $sens = 1, $date = null) {

	if(!$date) {

		$date = date('Y-m-d'); 
	}

	while ($jours != 0) {
		$sens == 1 ? $jour = strtotime($date.' +1 day') : $jour = strtotime($date.' -1 day');
		
		$date = date('Y-m-d',$jour);

		if( date('N', strtotime($date)) <= 5) {

			$jours--;
		}
	}

	return $date;
}



/**
 * 
 * 
 * Retourne les dates qui se trouvent entre deux dates
 * 
 * 
 */
function recupere_dates_entre_deux_dates($debut, $fin, $interval = 1, $type ="month", $format="m/Y", $plus_un_interval = true) {

	$debut = formate_date('Y-m-d', $debut);
	$fin = formate_date('Y-m-d', $fin);

    if($debut > $fin)
        return false;
    
    $sdate = strtotime($debut.($plus_un_interval ? " +$interval $type" : ''));
    $edate = strtotime($fin);
    
    $dates = array();
    
    for($i = $sdate; $i < $edate; $i += strtotime("+$interval $type", 0)) {

        $dates[] = date($format, $i);
    }
    
    return $dates;
}


function retourne_date_debut_et_date_fin($filtre) {
	
	$variable = null;
	
	if(!empty($filtre['variable']))
		$variable = $filtre['variable'];
	
	if(!empty($filtre['date_quotidienne_variable']))
		$variable = $filtre['date_quotidienne_variable'];

	// est ce qu'il y a une variable ?
	if(!empty($variable)) {
		
		if($variable == 'cette_annee') {
						
			return array('date_debut' => date('Y-01-01'), 'date_fin' => date('Y-12-31 23:59:59'));
		}
		
		if($variable == 'annee_derniere') {
						
			return array('date_debut' => date('Y-01-01', strtotime('last year')), 'date_fin' => date('Y-12-31 23:59:59', strtotime('last year')));
		}
		
		if($variable == 'annee_prochaine') {
						
			return array('date_debut' => date('Y-01-01', strtotime('next year')), 'date_fin' => date('Y-12-31 23:59:59', strtotime('next year')));
		}
		
		if($variable == 'depuis_janvier') {
						
			return array('date_debut' => date('Y-01-01'), 'date_fin' => date('Y-m-t 23:59:59'));
		}
		
		if($variable == 'ce_mois_ci') {
						
			return array('date_debut' => date('Y-m-01'), 'date_fin' => date('Y-m-t 23:59:59'));
		}
		
		if($variable == 'le_mois_dernier') {
			
			return array('date_debut' => date('Y-m-01', strtotime('last month')), 'date_fin' => date('Y-m-t 23:59:59', strtotime('last month')));
		}
		

		if($variable == 'le_mois_prochain') {
			
			return array('date_debut' => date('Y-m-01', strtotime('next month')), 'date_fin' => date('Y-m-t 23:59:59', strtotime('next month')));
		}
		
		if($variable == 'aujourdhui') {
			
			return array('date_debut' => date('Y-m-d'), 'date_fin' => date('Y-m-d 23:59:59'));
		}

		if($variable == 'hier') {

			return array('date_debut' => date('Y-m-d', strtotime('yesterday')), 'date_fin' => date('Y-m-d 23:59:59', strtotime('yesterday')));
		}

		if($variable == 'demain') {

			return array('date_debut' => date('Y-m-d', strtotime('tomorrow')), 'date_fin' => date('Y-m-d 23:59:59', strtotime('tomorrow')));
		}

		if($variable == '7_derniers_jours') {

			return array('date_debut' => date('Y-m-d', strtotime('now -7 d')), 'date_fin' => date('Y-m-d 23:59:59'));
		}

		if($variable == '7_prochains_jours') {

			return array('date_debut' => date('Y-m-d'), 'date_fin' => date('Y-m-d 23:59:59', strtotime("now +7 days")));
		}

		if($variable == '6_prochains_jours') {


			return array('date_debut' => date('Y-m-d'), 'date_fin' => date('Y-m-d 23:59:59', strtotime("now +6 days")));
		}

		if($variable == 'cette_semaine') {


			return array('date_debut' => lundi(), 'date_fin' => dimanche_prochain().' 23:59:59');
		}

		if($variable == 'jusqua_dimanche') {

			return array('date_debut' => '', 'date_fin' => dimanche_prochain().' 23:59:59');
		}
		
		

		if($variable == 'pas_renseigne') {

			return array('date_debut' => '', 'date_fin' => '');

		}

		if($variable == 'renseigne') {

			return array('date_debut' => '', 'date_fin' => '');

		}

		if($variable == 'passe') {
			
			return array('date_debut' => '', 'date_fin' => date('Y-m-d'));

		}

		if($variable == 'pas_passe') {

			return array('date_debut' => date('Y-m-d') , 'date_fin' => '');

		}

		if($variable == '30_derniers_jours') {


			return array('date_debut' => date('Y-m-d', strtotime('now -30 days')), 'date_fin' => date('Y-m-d').' 23:59:59');
		}
	}
	

	$date = array('date_debut' => '', 'date_fin' => '');
	
	// pas de variable, on traite le cas classique
	if(!empty($filtre['debut'])) {
		
		$date['date_debut'] = $filtre['debut'];
	}
	
	// pas de variable, on traite le cas classique
	if(!empty($filtre['date_quotidienne_debut'])) {
		
		$date['date_debut'] = $filtre['date_quotidienne_debut'];
	}
	
	if(!empty($filtre['fin'])) {
		
		$date['date_fin'] = $filtre['fin'];
	}
	
	if(!empty($filtre['date_quotidienne_fin'])) {
		
		$date['date_fin'] = $filtre['date_quotidienne_fin'];
	}
	
	return $date;

}

function extraite_format_date($date) {

    $patterns = array(
        '/\b\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}.\d{3,8}Z\b/' => 'Y-m-d\TH:i:s.u\Z',
        '/\b\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])\b/' => 'Y-m-d',
        '/\b\d{4}-(0[1-9]|[1-2][0-9]|3[0-1])-(0[1-9]|1[0-2])\b/' => 'Y-d-m',
        '/\b(0[1-9]|[1-2][0-9]|3[0-1])-(0[1-9]|1[0-2])-\d{4}\b/' => 'd-m-Y',
        '/\b(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])-\d{4}\b/' => 'm-d-Y',

        '/\b\d{4}\/(0[1-9]|[1-2][0-9]|3[0-1])\/(0[1-9]|1[0-2])\b/' => 'Y/d/m',
        '/\b\d{4}\/(0[1-9]|1[0-2])\/(0[1-9]|[1-2][0-9]|3[0-1])\b/' => 'Y/m/d',
        '/\b(0[1-9]|[1-2][0-9]|3[0-1])\/(0[1-9]|1[0-2])\/\d{4}\b/' => 'd/m/Y',
        '/\b(0[1-9]|1[0-2])\/(0[1-9]|[1-2][0-9]|3[0-1])\/\d{4}\b/' => 'm/d/Y',

        '/\b\d{4}\.(0[1-9]|1[0-2])\.(0[1-9]|[1-2][0-9]|3[0-1])\b/' => 'Y.m.d',
        '/\b\d{4}\.(0[1-9]|[1-2][0-9]|3[0-1])\.(0[1-9]|1[0-2])\b/' => 'Y.d.m',
        '/\b(0[1-9]|[1-2][0-9]|3[0-1])\.(0[1-9]|1[0-2])\.\d{4}\b/' => 'd.m.Y',
        '/\b(0[1-9]|1[0-2])\.(0[1-9]|[1-2][0-9]|3[0-1])\.\d{4}\b/' => 'm.d.Y',

        '/\b(?:2[0-3]|[01][0-9]):[0-5][0-9](:[0-5][0-9])\.\d{3,6}\b/' => 'H:i:s.u',
        '/\b(?:2[0-3]|[01][0-9]):[0-5][0-9](:[0-5][0-9])\b/' => 'H:i:s',
        '/\b(?:2[0-3]|[01][0-9]):[0-5][0-9]\b/' => 'H:i',

        '/\b(?:1[012]|0[0-9]):[0-5][0-9](:[0-5][0-9])\.\d{3,6}\b/' => 'h:i:s.u',
        '/\b(?:1[012]|0[0-9]):[0-5][0-9](:[0-5][0-9])\b/' => 'h:i:s',
        '/\b(?:1[012]|0[0-9]):[0-5][0-9]\b/' => 'h:i',

        '/\.\d{3}\b/' => '.v'
    );

    $date = preg_replace( array_keys( $patterns ), array_values( $patterns ), $date );

    return preg_match( '/\d/', $date ) ? '' : $date;
}

