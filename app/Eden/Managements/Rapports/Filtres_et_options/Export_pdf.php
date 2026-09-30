<?php

namespace App\Eden\Managements\Rapports\Filtres_et_options;

use App\Eden\Managements\Rapports\Rapports_management;

/**
 *
 * Gestion des exports excel
 *
 */
class Export_pdf {

    public static function applique($rapport, $orientation = 'portrait', $format_papier = 'a4') {

        $rapport->option('export_pdf', array(
			
            'id_rapport' => $rapport->id_rapport,
            'orientation' => $orientation,
            'format_papier' => $format_papier,
		));
    }
}