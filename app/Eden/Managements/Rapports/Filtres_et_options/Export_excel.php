<?php

namespace App\Eden\Managements\Rapports\Filtres_et_options;

use App\Eden\Managements\Rapports\Rapports_management;

/**
 *
 * Gestion des exports excel
 *
 */
class Export_excel {

    public static function applique($rapport) { 


        // on récupère les paramètres
        //$parametres = Rapports_management::recupere_parametres($rapport->id_rapport);
        
        $rapport->option('export_excel', array(
			
            'id_rapport' => $rapport->id_rapport
		));
    }
}