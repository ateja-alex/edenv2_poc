<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

class S20230208_taille_question_campagne implements Script {

    public function execute(){

        $questionnaire_questions = modele('questionnaire_question')->get();

        foreach($questionnaire_questions as $question){

            if(!empty($question->taille))
                continue;

            if($question->type == 7)
                $question->taille = 12;
            else
                $question->taille = 6;

            $question->save();
        }

        return true;
    }
}