<?php

namespace App\Eden\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use ZipArchive;
use Illuminate\Support\Facades\URL;

class Affacturage_controller extends Controller {

	/**
	 *
	 * Affacturer des documents
	 *
	 */
	public function affacturer_documents(Request $formulaire) {

        $type_element= 'facture_vente';

		$liste_des_factures = [];
		$nom_du_zip = 'affacturage_'.time().'.zip';

		// on crée le fichier ZIP
        $zip = new ZipArchive();
		$zip->open(storage_path('app/public/'.$nom_du_zip), ZIPARCHIVE::CREATE | \ZIPARCHIVE::OVERWRITE );

		// Pour chaque id, on instancie le management (ce qui nous permet par la suite, d'appeler les méthodes du management)
        foreach($formulaire->ids as $id) 
            $liste_des_factures[] = management($type_element, $id);

		// on récupère les documents qu'on doit zipper pour les retourner
        $documents_par_factures = service('affacturage')->prepare_liste_documents($liste_des_factures);

        foreach($documents_par_factures as $documents_par_facture){

            $nom_dossier = $documents_par_facture['facture']->modele->reference_document.'_'.$documents_par_facture['facture']->modele->id;

            foreach($documents_par_facture['documents'] as $nom_document => $document){

                $nom_document = is_int($nom_document) ? basename($document) : $nom_document;
                $zip->addFromString($nom_dossier.'/'.$nom_document,file_get_contents($document));
            }
        }
		
		$zip->close();

		return response()->json(['retour' => true, 'chemin' => URL::to('storage/'.$nom_du_zip)]);
    }
}
