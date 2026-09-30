<?php

namespace App\Eden\Controllers\Fiches;

use Illuminate\Http\Request;
use App\Eden\Controllers\Fiche_controller;

use DB;

class Notification_manuelle_controller extends Fiche_controller {

    /**
	 *
	 * Generer les notifications via une fiche
	 *
	 */
    public function generer_notifications_manuelles($type_element, $id_element) {

        $management_notification = management('notification_manuelle',$id_element);

        $management_notification->genere_donnees_notification_manuelle_element();

        return response()->json(array('retour' => true));

	}

    /**
	 *
	 * Envoyer les notifications via une fiche
	 *
	 */
    public function envoyer_notifications_manuelles($type_element, $id_element) {

        $management_notification = management('notification_manuelle',$id_element);

        $retour = $management_notification->envoie_des_notifications();

        return response()->json(array('retour' => $retour));

	}
}
