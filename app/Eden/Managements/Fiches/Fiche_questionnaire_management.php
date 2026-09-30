<?php

namespace App\Eden\Managements\Fiches;

use App\Eden\Managements\Fiche_management;
use App\Eden\Managements\Listes_management;

use App\Eden\Models\Recurrence;
use App\Eden\Models\Liste_libre;
use DB;
use App\Eden\Variables;


/**
 * Gestion des fiches leads
 */
class Fiche_questionnaire_management extends Fiche_management {

	/**
	 *
	 * Prépare les données pour la fiche
	 *
	 * @attention si vous utilisez cette méthode dans les classes filles, il faut faire appelle à parent::prepare_donnees_pour_fiche($donnees)
	 *
	 */
	public function prepare_donnees_pour_fiche($donnees = array()) {

		$management_questionnaire = management('questionnaire', $this->id_element);

		// on va chercher les données de base
		$donnees = parent::prepare_donnees_pour_fiche($donnees);

        $questions = $management_questionnaire->questions();

		// on va ajouter les intérêts
		$donnees['questions'] = $questions['questions'];
		$donnees['options'] = $questions['options'];

		$donnees['graph_reponses'] = $this->graph_reponses();

		return $donnees;
	}

	/**
	 *
	 * Retourne les info pour le graph réponses
	 *
	 * @return collection
	 *
	 */
	public function graph_reponses() {
		
		// on précharge les questions
		$questions = modele('questionnaire_question')
            ->where('questionnaire_id', $this->id_element)
            ->where('type', 1)->get()->pluck('question', 'id');

		$graph_reponses = array();
		
		// pour chaque réponse, on va chercher le détail
		foreach($questions as $id => $question) {
			
			$moyenne = modele('questionnaire_reponse')->select(\DB::raw('AVG(reponse) as moyenne'))->where('question_id', $id)->first();
			
			$graph_reponses["'".str_replace("'", "\\'", $question)."'"] = $moyenne->moyenne;
		}

		return $graph_reponses;
	}

}
