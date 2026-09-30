<?php

namespace App\Eden\Controllers;

use App\Http\Controllers\Controller;

use App\Eden\Variables;
use PDF;


class Bordereau_controller extends Controller {

	/**
	 *
	 * Imprime le bordereau au format pdf
	 *
	 */

	public function imprimer_pdf($id_bordereau){

        $donnees['bordereau']= modele('bordereau', $id_bordereau);

        $donnees['bordereau']->date =date ('d/m/Y',strtotime($donnees['bordereau']->date) );

        $donnees['banque'] = modele('compte_bancaire',$donnees['bordereau']->banque);

        $donnees['paiements'] = modele('paiement')
            ->join('client','client.id','paiement.client_id')
            ->where('bordereau_id',$id_bordereau)
            ->get();

        $donnees['entite'] = modele('entite',$donnees['bordereau']->entite_id);

        foreach($donnees['paiements'] as $paiement){
            if($paiement->type_element !="" && $paiement->type_element !=null && $paiement->id_document!=null){
                $document = modele($paiement->type_element,$paiement->id_document);
                $paiement->document = $document->reference_document;
            }
        }

        $pdf = PDF::loadView('eden::pdf.bordereau',$donnees);

        $pdf->getDomPDF()->set_option("enable_php", true);

        return $pdf->download('bordereau_n_'.$donnees['bordereau']->numero.'.pdf');
    }

}
