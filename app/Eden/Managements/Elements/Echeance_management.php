<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Elements\Element_management;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Table_libre;

use DB;

class Echeance_management extends Element_management {

    public function enregistre($modifications = array(), $modele = false){

        if(isset($modifications['montants_dates_echeances'])){

            $montants_date_echeances = $modifications['montants_dates_echeances'];

            unset($modifications['montants_dates_echeances']);

            $modifications['montant'] = $montants_date_echeances[0]['montant'];

            unset($montants_date_echeances[0]);

            foreach($montants_date_echeances as $montant_date){

                management('echeance')->enregistre($modifications);

                $modifications['date'] = $montant_date['date'];
                $modifications['montant'] = $montant_date['montant'];
            }
        }

        if(isset($modifications['pourcentage']) && $modifications['pourcentage'] == 1 && !empty($modifications['montant_pourcentage'])) {
            $document = modele($modifications['type_element'])->where('id', $modifications['element_id'])->first();

            if($document !== null)
                $modifications['montant'] = $document->montant_document_ttc * $modifications['montant_pourcentage'] / 100;
        }

        if(!isset($modifications['pourcentage']) || empty($modifications['pourcentage']))
            $modifications['montant_pourcentage'] = 0;

        return parent::enregistre($modifications, $modele);

    }

}