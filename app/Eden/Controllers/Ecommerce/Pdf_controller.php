<?php

namespace App\Eden\Controllers\Ecommerce;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Pdf_controller extends Controller {

    /**
     *
     * On récupère le pdf pour les clients coté e-commerce
     *
     * 
     */
    public function Recuperation_pdf_pour_client($type_element, $id_element, $token) {
		
		$cryptage = md5("eden.$id_element.$type_element");

        if($cryptage == $token) {

            $document = modele($type_element, $id_element);

            //On génère le pdf s'il n'existe pas et on actualise le modèle
            if(empty($document->pdf)){

                management($type_element, $id_element)->creation_pdf();
                $document = modele($type_element, $id_element);
            }

            return response()->file(storage_path('app/'.$document->pdf));
        }

        return 'error #1';
    }


}
