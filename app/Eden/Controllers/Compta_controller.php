<?php

namespace App\Eden\Controllers;

use App\Eden\Managements\Parametres_erp_management;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;

class Compta_controller extends Controller {

	/**
	 * 
	 * Action comptabiliser des éléments
	 * 
	 */
	public function comptabiliser(Request $request, $type_element) {
	    //Gestion des vues
        $type_element = service('vue_sql')->recupere_type_element($type_element);
		
		if(!is_array($request->ids_element) || empty($request->ids_element)) {
			
			return response()->json(array('retour' => traduction('messages.php.comptabilisation.erreur_liste_id')));
		}

        if(parametre('comptabilisation_en_cours') == 1 ){

            return response()->json(array('retour' => traduction('messages.php.comptabilisation.deja_en_cours')));

        }

        parametre('comptabilisation_en_cours',1);
		
		$resultat = array('succes' => 0, 'erreurs' => array());

        foreach($request->ids_element as $id) {
			
			$retour = management($type_element, $id)->comptabilise();

			if($retour === true) {
				
				$resultat['succes']++;
				continue;
			}
			
			if(!isset($resultat['erreurs'][$retour]))
				$resultat['erreurs'][$retour] = 0;
			
			$resultat['erreurs'][$retour]++;
		}

        parametre('comptabilisation_en_cours',0);

        return response()->json(array('retour' => true, 'resultat' => $resultat));
	}

	/**
	 * 
	 * Formulaire d'export comptable
	 * 
	 */
	public function export() {
		
		$modeles_export = modele('export_compta_modele')
			->where('type_element','ecriture_comptable')
			->orderBy('par_defaut', 'DESC')->get()->groupBy('entite_id');
		
		return view('eden::compta.export', array('modeles_export' => collect($modeles_export)));
	}
	
	/**
	 * 
	 * Génère l'export comptable
	 * 
	 */
	public function export_post(Request $request) {
		
		// on va chercher les écritures comptables
        $requete = modele('ecriture_comptable')
            ->where('ecriture_comptable.date', '>=', formate_date('Y-m-d', $request->debut))
            ->where('ecriture_comptable.date', '<=', formate_date('Y-m-d', $request->fin));
		
		if(!empty($request->journal)) {

            $requete = $requete->where('ecriture_comptable.journal_id', $request->journal);
		}
		
		if(!empty($request->entite_id)) {

            $requete = $requete->where('ecriture_comptable.entite_id', $request->entite_id);
		}
		
		if(!empty($request->choix_ecritures)) {

			if($request->choix_ecritures == 1)
                $requete = $requete->zero_ou_null('ecriture_comptable.exporte');
			else
                $requete = $requete->where('ecriture_comptable.exporte', 1);
		}
		
		$requete = $requete->orderBy('ecriture_comptable.ecriture_id');

        if (isset($request->type_export))
            $format = $request->type_export;
        else
            $format = 'FEC';

        if ($format == 'CUSTOM') {

            //Check si c'est CSV ou Positionné
            $modele = modele('export_compta_modele', $request->export_compta_modele_id);

            if (!isset($modele))
                throw Exception("Impossible de trouver le modèle");

            return management('export_compta_modele',$modele->id,$modele)->export($requete, array(
                'date_debut' => $request->debut,
                'date_fin' => $request->fin,
                'type_export' => $request->type_export,
                'entite_id' => $request->entite_id,
                'export_compta_modele_id' => $request->export_compta_modele_id,
            ));
        }

        return management('ecriture_comptable')->exporte_ecritures_fec($requete->get(), array(
            'date_debut' => $request->debut,
            'date_fin' => $request->fin,
            'type_export' => $request->type_export,
            'entite_id' => $request->entite_id,
            'export_compta_modele_id' => $request->export_compta_modele_id,
        ));
	}
	
}
