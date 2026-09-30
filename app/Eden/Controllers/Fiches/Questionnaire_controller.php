<?php

namespace App\Eden\Controllers\Fiches;
use App\Eden\Controllers\Fiche_controller;

use Illuminate\Http\Request;


class Questionnaire_controller extends Fiche_controller {

    /**
     *
     * Permet de récupérer les questions
     *
     */
    public function questions(){

        $management_questionnaire = management('questionnaire', $this->id_element);

        $questions = $management_questionnaire->questions();

        return response()->json($questions);
    }

	/**
	 *
	 * Ordonne les questions
	 *
	 **/
	public function ordre_questions() {

        $formulaire = request()->all();

        $ordres = $formulaire['ordres'];

        $questions = modele('questionnaire_question')->whereIn('id',$ordres)->get()->keyBy('id');

        foreach($ordres as $ordre => $id_question){

            $ordre = $ordre+1;

            if($questions[$id_question]->ordre != $ordre){
                $management = management('questionnaire_question', $id_question,$questions[$id_question]);
			    $management->enregistre_modele(['ordre'=>$ordre]);
            }

        }
	}

    /**
	 *
	 * Enregistre les options
	 *
	 **/
	public function enregistrer_options() {

		$formulaire = request()->all();

        $options = $formulaire['options'];
        $question_id = $formulaire['question_id'];

        $nouvels_id = [];

        $options_a_supprimer = modele('questionnaire_question_option')
            ->where('question_id',$question_id)->get()->keyBy('id');

        foreach($options as $index => $option){

            $ordre = $index+1;

            if(!empty($option['id']))
                $management = management('questionnaire_question_option',$option['id'],$options_a_supprimer[$option['id']]);
            else
                $management = management('questionnaire_question_option');

            $management->enregistre(array(
                'question_id' => $option['question_id'],
                'option' => $option['option'],
                'ordre' => $ordre,
            ));

            if(empty($option['id']))
                $nouvels_id[$index] = $management->modele->id;
            else
                unset($options_a_supprimer[$option['id']]);
        }

        foreach($options_a_supprimer as $option){

            management('questionnaire_question_option',$option['id'])->supprime();
        }

        return response()->json(array('retour' => true,'nouvels_id'=> $nouvels_id));
	}
	
	/**
	 *
	 * Retourne la liste des options d'une question
	 *
	 **/
	public function liste_options($id_question) {
		$options = modele('questionnaire_question_option')
						->where('question_id', $id_question)
						->orderBy('ordre')
						->get()
						->toArray();
					
		return response()->json($options) ;
	}

	/**
	 *
	 * Ajoute une option
	 *
	 **/
	public function ajout_option($id_question, Request $formulaire) {

		$management = management('questionnaire_question_option');

        $ordre = modele('questionnaire_question_option')->where('question_id',$id_question)->count();

		$management->enregistre([
            'option' => $formulaire->post('option'),
            'question_id' => $id_question,
            'ordre' => $ordre+1
        ]);

		return $this->liste_options($id_question);
	}

	/**
	 *
	 * Supprime une option
	 *
	 **/
	public function supprime_option($id_question, Request $formulaire) {

		$management = management('questionnaire_question_option', $formulaire->get('option_id'));
		$management->supprime();

		return $this->liste_options($id_question);
	}

	/**
	 *
	 * Édite une option
	 *
	 **/
	public function edite_option($id_question, Request $formulaire) {

		$management = management('questionnaire_question_option', $formulaire->get('id'));
		$management->enregistre(['option'=>$formulaire->get('option')]);

		return $this->liste_options($id_question);
	}

}
