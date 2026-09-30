<?php

namespace App\Eden\Managements\Elements;

class Questionnaire_question_management extends Element_management {

    /**
     *
     * On vérifie que des réponses n'ont pas été déjà remplis sur ce questionnaire
     *
     */
    public function enregistre($modifications = array(), $modele = false) {

        if(!empty($modifications['checkbox']))
            $modifications['checkbox'] = json_encode($modifications['checkbox']);

        if(!empty($modifications['affichage_conditionnel']))
            $modifications['affichage_conditionnel'] = json_encode($modifications['affichage_conditionnel']);

        $a_verifier = false;

        if($this->existe())
            $a_verifier = (!empty($modifications['type']) && $modifications['type'] != $this->modele->type)
                || (!empty($modifications['checkbox']) && $modifications['checkbox'] != $this->modele->checkbox);

        if($a_verifier) {
            // on va chercher toutes les réponses du questionnaire pour savoir si on a le droit d'ajouter une question
            $reponses = modele('questionnaire_reponse')
                ->join('questionnaire_question', 'questionnaire_reponse.question_id', 'questionnaire_question.id')
                ->where('questionnaire_id', $modifications['questionnaire_id'])->first();

            if (!empty($reponses))
                return traduction('messages.php.questionnaire.creer_modifier_question_avec_reponses');
        }

        // ok on laisse le standard faire
        return parent::enregistre($modifications, $modele);
    }
    
    /**
     *
     * On change le modèle par défaut
     *
     */
    public function modele_par_defaut() {

        $modele = parent::modele_par_defaut();

        $modele->checkbox = [];

        return $modele;
    }
    
}