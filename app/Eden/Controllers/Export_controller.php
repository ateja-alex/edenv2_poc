<?php

namespace App\Eden\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Export_controller extends Controller {

	/**
	 * 
	 * Exporte la liste au format Excel (GET) 
	 * 
	 */
	public function exporter_reponses_questionnaire(Request $formulaire,$questionnaire_id) {

        $formulaire = $formulaire->all();

        $management_questionnaire = management('questionnaire',$questionnaire_id);

        $parametres = [];
        $parametres_liste = [];

        if(!empty($formulaire['parametres']))
            $parametres = json_decode($formulaire['parametres'],true);

        if(!empty($formulaire['parametres_liste']))
            $parametres_liste = json_decode($formulaire['parametres_liste'],true);

        $colonnes = $management_questionnaire->questions()['questions'];

        $parametres_liste['export'] = true;

        $groupe_reponses = $management_questionnaire->reponses($parametres,$parametres_liste)['reponses'];

        foreach($colonnes as $index => $question){

            if($question->type == 7)
                unset($colonnes[$index]);
        }

        $colonnes_sources = array(
            'cree_le',
            'type_element',
            'element_id',
            'element_id_affichage',
            'type_element_origine',
            'element_origine_id',
            'element_origine_id_affichage',
        );

        $en_tete = array();
        $lignes = array();

        $id = -1;

        foreach($colonnes_sources as $colonne_source){

            $en_tete[] = (object) [
                'id' => $id++,
                'nom' => traduction('champs_libres.questionnaire_element_repondant.'.$colonne_source.'.nom')
            ];
        }

        foreach($colonnes as $colonne) {

            $en_tete[] = (object) [
                'id' => $id++,
                'nom' => $colonne->question
            ];
		}

        foreach($groupe_reponses as $groupe_reponse){

            $ligne = array();

            foreach($colonnes_sources as $colonne){

                if($colonne == 'cree_le')
                    formate_date('d/m/Y H:i', $groupe_reponse[$colonne]);

                $ligne[] = $groupe_reponse[$colonne];
            }

            foreach($colonnes as $colonne){

                if(!empty($groupe_reponse['question_'.$colonne->id]))
                    $ligne[] = $groupe_reponse['question_'.$colonne->id];
                else
                    $ligne[] = null;
            }

            $lignes[] = $ligne;
        }

        $donnees = array(
            'colonnes' => collect($en_tete),
            'lignes' => $lignes
        );

        if(!file_exists(storage_path('app/public/exports/export_questionnaire.xlsx')))
			\Storage::delete('public/exports/export_questionnaire.xlsx');

        service('export')->exporter_xlsx_csv($donnees,'export_questionnaire.xlsx');

		return response()->download(storage_path('app/public/exports/export_questionnaire.xlsx'), 'export_questionnaire.xlsx');
	}

}