<?php

namespace App\Eden\Managements\Rapports\Filtres_et_options;

use App\Eden\Managements\Rapports\Rapports_management;

/**
 *
 * 
 *
 */
class Pagination {
	
	/**
	 * 
	 * Applique le filtre par Fournisseur
	 * 
	 */	
    public static function applique($rapport, $nombre_total, $nombre_par_page) {
		
    	// on récupère les paramètres
		if(isset($rapport->parametres)) {

			$parametres = $rapport->parametres;
		}
		else {

			$parametres = Rapports_management::recupere_parametres($rapport->id_rapport);
		}

		if(!empty(request()->pagination)) {
            $parametres['pagination'] = request()->pagination;
        }
		
		if(isset($parametres['pagination']))
			$page = $parametres['pagination'];
		else
			$page = 1;
		
		if(empty($nombre_par_page))
			$nombre_par_page = 10;
		
		// on gère le cas ou il y a moins de page que la page actuelle (changement de filtre par exemple)
		if($page > ceil($nombre_total / $nombre_par_page)) {
			
			$parametres['pagination'] = 1;
			$page = 1;
		}
		
	
		Rapports_management::enregistre_parametres($rapport->id_rapport, $parametres, true);
		
		// Affiche la vue
		$rapport->option('pagination', ['page' => $page, 'pages' => ceil($nombre_total / $nombre_par_page)]);

		return array('take' => $nombre_par_page, 'skip' => ($page - 1) * $nombre_par_page);
    }	
}