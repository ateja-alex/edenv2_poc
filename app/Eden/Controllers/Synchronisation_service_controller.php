<?php

namespace App\Eden\Controllers;

use App\Http\Controllers\Controller;

use Log;

class Synchronisation_service_controller extends Controller {

    public function tables_externes($id_element){

        $synchronisation_service = modele('synchronisation_service')->find($id_element);

        if(empty($synchronisation_service))
            return response()->json([]);

        return response()->json(
            management('synchronisation_service',$id_element,$synchronisation_service)->tables_externes()
        );
    }

    public function parametres($id_element){

        $synchronisation_service = modele('synchronisation_service')->find($id_element);

        if(empty($synchronisation_service))
            return response()->json([]);

        return response()->json(
            management('synchronisation_service', $id_element, $synchronisation_service)->parametres()
        );
    }

    public function champs_externes($id_element){

        $synchronisation_service_element = modele('synchronisation_service_element')->find($id_element);

        if(empty($synchronisation_service_element))
            return response()->json([]);

        return response()->json(
            management('synchronisation_service_element',$id_element,$synchronisation_service_element)->champs_externes()
        );
    }

    public function table_externe($id_element, $nom_table){

        $synchronisation_service_element = modele('synchronisation_service_element')->find($id_element);

        if(empty($synchronisation_service_element))
            return response()->json([]);

        return response()->json(
            management('synchronisation_service_element',$id_element,$synchronisation_service_element)->table_externe($nom_table)
        );
    }

    public function lancer($id_element){

        $synchronisation_service_element = modele('synchronisation_service_element')->find($id_element);

        $donnees = request()->only(['type_element', 'element_id']);

        if(empty($synchronisation_service_element) || $synchronisation_service_element->type_synchronisation != 3 || !empty($synchronisation_service_element->desactive))
            return response()->json(['succes' => false, 'message' => traduction('messages.php.synchronisation_service_element.synchronisation_desactivee')]);

        if(empty($donnees['type_element']) || empty($donnees['element_id']))
            return response()->json(['succes' => false, 'message' => traduction('messages.php.synchronisation_service_element.parametres_manquants')]);

        $management_origine = management($donnees['type_element'], $donnees['element_id']);
        
        $retour = management('synchronisation_service_element', $id_element, $synchronisation_service_element)
            ->synchronisation_element($management_origine, true);

        if($retour === true)
            return response()->json(['succes' => true]);

        return response()->json(['succes' => false, 'message' => $retour]);
    }

}