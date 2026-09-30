<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Elements\Element_management;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Table_libre;

use DB;

class Echange_management extends Element_management {

    /**
     *
     * Permet de récupérer le client lié à l'échange
     *
     */
    public function recuperer_client($modele) {

        $type_element = $modele->type_element;
        $element_id = $modele->element_id;

        $champ_libre = Champ_libre::where('type_element', $type_element)->where('nom_sql','client_id')->first();

        $client = null;

        if($type_element == 'client'){

            $client = management($type_element,$element_id)->affiche();
        }

        elseif($champ_libre){

            $client = management($type_element,$element_id)->champ('client_id')->affiche();
        }

        elseif($modele->contact_id){

            $client = management('contact',$modele->contact_id)->champ('client_id')->affiche();
        }

        if($client){

            return $client;
        }

        return null;
    }

    /**
     *
     * Permet de récupérer le destinataire de l'échange
     *
     */
    public function recuperer_destinataire($modele) {

        $type_element = $modele->type_element;
        $element_id = $modele->element_id;

        $table_libre = Table_libre::where('type_element',$type_element)->first();

        if($table_libre == null)
            return '';

        $affichage = $table_libre->element .' : '. management($type_element,$element_id)->affiche_lien();

        return $affichage;
    }
	
}