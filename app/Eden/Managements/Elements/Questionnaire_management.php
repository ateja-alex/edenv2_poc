<?php

namespace App\Eden\Managements\Elements;

use Illuminate\Support\Facades\DB;

class Questionnaire_management extends Element_management {

    private $questions_options = [];

    /**
	 *
	 * Prépare les questions d'un questionnaire de satisfaction
	 *
	 */
	public function questions() {

        if(!empty($this->questions_options))
            return $this->questions_options;

		//Récupération des questions
        $questions = modele('questionnaire_question')->where('questionnaire_id', $this->modele->id)->orderBy('ordre')->get();

        foreach($questions as $question) {
            $question->question_brut = strip_tags($question->question);
        }

        $options = modele('questionnaire_question_option')
            ->select('questionnaire_question_option.*')
            ->join('questionnaire_question','question_id','questionnaire_question.id')
            ->where('questionnaire_id', $this->modele->id)
            ->orderBy('questionnaire_question_option.ordre')->get()->groupBy('question_id')->toArray();

        foreach($questions as &$question){

            if($question['type'] == 8)
                $question['checkbox'] = json_decode($question['checkbox']);

            if(!empty($question['affichage_conditionnel']))
                $question['affichage_conditionnel'] = json_decode($question['affichage_conditionnel']);
        }

        $this->questions_options = array(
			'questions' => $questions,
			'options' => $options,
		);

        return $this->questions_options;
	}

	/**
     * @return array
     *
     * Permet de récupérer les réponses à un questionnaire
     *
     */
    public function reponses($parametres = [],$parametres_liste = []){

        $requete = modele('questionnaire_reponse')
            ->join('questionnaire_question', 'questionnaire_reponse.question_id', 'questionnaire_question.id')
            ->join('questionnaire_element_repondant', 'repondant_id', 'questionnaire_element_repondant.id')
            ->where('questionnaire_question.questionnaire_id',$this->modele->id);

        foreach($parametres as $champ => $valeur){
            $requete = $requete->where('questionnaire_element_repondant.'.$champ,$valeur);
        }

        if(empty($this->modele->reponses_multiples) && empty($parametres_liste['page']) && empty($parametres_liste['export']))
            return $requete->get()->pluck('reponse', 'question_id');

        $questions = modele('questionnaire_question')
            ->where('questionnaire_id',$this->modele->id)
            ->where('type','!=',7)
            ->get();

        $select = ['questionnaire_reponse.cree_le','questionnaire_element_repondant.type_element','questionnaire_element_repondant.element_id','type_element_origine','element_origine_id'];

        foreach($questions as $question){

            $valeur = 'questionnaire_reponse.reponse';

            if($question->type == 5){
                $requete = $requete->leftJoin('questionnaire_question_option as option_question_'.$question->id,function($join) use ($question){
                    $join->on('option_question_'.$question->id.'.question_id','questionnaire_reponse.question_id')
                        ->on('questionnaire_reponse.reponse','option_question_'.$question->id.'.id')
                        ->where('questionnaire_reponse.question_id',$question->id);
                });

                $valeur = 'option_question_'.$question->id.'.option';
            }
            else if($question->type == 9)
                $valeur = "IF(questionnaire_reponse.reponse = 1,'Oui','Non')";
            else if($question->type == 8){

                $possibilites = json_decode($question->checkbox,true);

                $concat = [];

                foreach($possibilites as $possibilite){
                    $concat[] = "IF(questionnaire_reponse.reponse LIKE '%%\"".$possibilite['valeur_checkbox']."\"%%','".str_replace("'","\\'",$possibilite['nom_checkbox'])."',NULL)";
                }

                $valeur = 'CONCAT_WS(" / ", '.implode(",",$concat).')';
            }

            $select[] = 'GROUP_CONCAT(IF(questionnaire_reponse.question_id = '.$question->id.' ,'.$valeur.',NULL)) as "question_'.$question->id.'"';
        }

        if(!empty($parametres_liste['recherche'])) {

            $types_elements = [];
            $types_elements_origine = [];

            // D'abord on recupére les types elements et les types elements d'origines présents pour faciliter la recherche éventuelle
            if (empty($parametres['type_element']))
                $types_elements = (clone $requete)->select('questionnaire_element_repondant.type_element')
                    ->whereNotNull('questionnaire_element_repondant.type_element')
                    ->groupBy('questionnaire_element_repondant.type_element')
                    ->get()->pluck('type_element')->toArray();

            if (empty($parametres['type_element_origine']))
                $types_elements_origine = (clone $requete)->select('questionnaire_element_repondant.type_element_origine')
                    ->whereNotNull('questionnaire_element_repondant.type_element_origine')
                    ->groupBy('questionnaire_element_repondant.type_element_origine')
                    ->get()->pluck('type_element_origine')->toArray();

            foreach ($types_elements as $type_element) {
                $requete = $requete->leftJoin($type_element . ' AS repondant_' . $type_element, function ($sous_requete) use ($type_element) {
                    $sous_requete->on('questionnaire_element_repondant.element_id', 'repondant_' . $type_element . '.id');
                    $sous_requete->where('questionnaire_element_repondant.type_element', $type_element);
                });
            }

            foreach ($types_elements_origine as $type_element) {
                $requete = $requete->leftJoin($type_element . ' AS origine_' . $type_element, function ($sous_requete) use ($type_element) {
                    $sous_requete->on('questionnaire_element_repondant.element_origine_id', 'origine_' . $type_element . '.id');
                    $sous_requete->where('questionnaire_element_repondant.type_element_origine', $type_element);
                });
            }

            $recherche = $parametres_liste['recherche'];

            $recherches = explode(' ',$recherche);

            foreach($recherches as $recherche) {

                $requete->where(function ($sous_requete) use ($recherche, $types_elements, $types_elements_origine) {

                    foreach ($types_elements as $type_element) {
                        $sous_requete = $sous_requete->orWhere('repondant_' . $type_element . '.chaine_tags_recherche', 'LIKE', '%' . $recherche . '%');
                    }

                    foreach ($types_elements_origine as $type_element) {
                        $sous_requete = $sous_requete->orWhere('origine_' . $type_element . '.chaine_tags_recherche', 'LIKE', '%' . $recherche . '%');
                    }

                });
            }
        }

        $groupeur = empty($this->modele->reponses_multiples) ? 'repondant_id' : 'groupe_reponses_id';

        $requete = $requete->groupBy($groupeur);

        $nombre_de_pages = null;

        $reponses = $requete->selectRaw(implode(',',$select))
            ->orderBy('questionnaire_reponse.cree_le','DESC');

        if(!empty($parametres_liste['page'])) {

            $nombres_elements = (clone $requete)->select($groupeur)->get()->count();

            $nombre_de_pages = ceil($nombres_elements / 5);

            $reponses = $reponses->skip(($parametres_liste['page'] - 1) * 5)->take(5);
        }

        $reponses = select($reponses);

        $reponses = modele('questionnaire_reponse')->hydrate($reponses);

        $elements_a_recuperer = [];

        foreach($reponses as $reponse){

            if(!empty($reponse->type_element) && !empty($reponse->element_id)) {

                if(!isset($elements_a_recuperer[$reponse->type_element]))
                    $elements_a_recuperer[$reponse->type_element] = [];

                $elements_a_recuperer[$reponse->type_element][] = $reponse->element_id;
            };

            if(!empty($reponse->type_element_origine) && !empty($reponse->element_origine_id)) {

                if(!isset($elements_a_recuperer[$reponse->type_element_origine]))
                    $elements_a_recuperer[$reponse->type_element_origine] = [];

                $elements_a_recuperer[$reponse->type_element_origine][] = $reponse->element_origine_id;
            };
        }

        $elements_recuperer = [];

        foreach($elements_a_recuperer as $type_element => $elements_id) {
            $elements_recuperer[$type_element] = [
                'traduction_table' => traduction('tables_libres.'.$type_element.'.nom_table'),
                'elements' => modele($type_element)->whereIn('id',array_unique($elements_id))->get()->keyBy('id')
            ];
        }

        foreach($reponses as $reponse){

            if(!empty($reponse->type_element) && !empty($reponse->element_id)) {

                if(isset($elements_recuperer[$reponse->type_element]['elements'][$reponse->element_id])) {

                    $modele = $elements_recuperer[$reponse->type_element]['elements'][$reponse->element_id];

                    $reponse->element_id_affichage = management($reponse->type_element, $reponse->element_id, $modele)->affiche_lien();
                }

                $reponse->type_element = $elements_recuperer[$reponse->type_element]['traduction_table'];
            }

            if(!empty($reponse->type_element_origine) && !empty($reponse->element_origine_id)) {

                if(isset($elements_recuperer[$reponse->type_element_origine]['elements'][$reponse->element_origine_id])) {

                    $modele = $elements_recuperer[$reponse->type_element_origine]['elements'][$reponse->element_origine_id];

                    $reponse->element_origine_id_affichage = management($reponse->type_element_origine, $reponse->element_origine_id, $modele)->affiche_lien();
                }

                $reponse->type_element_origine = $elements_recuperer[$reponse->type_element_origine]['traduction_table'];
            }
        }
        
        return array('reponses' => $reponses,'nombre_de_pages' => $nombre_de_pages);
    }

    /**
	 *
	 * Enregistre les questions d'un questionnaire de satisfaction
	 *
	 */
	public function enregistrer_reponses($reponses, $parametres) {

        if(isset($parametres['repondant_id']))
            $management_repondant = management('questionnaire_element_repondant',$parametres['repondant_id']);
        else
            $management_repondant = $this->repondant($parametres);

        $repondant = $management_repondant->modele;

        $groupe_reponses_id = $repondant->id.date('ymdHis');

        $retours_enregistrement_question = [];
        $reponses_a_enregistrer = [];
        $questionnaires_reponses = [];

        if (empty($questionnaire->reponses_multiples))
            $questionnaires_reponses = modele('questionnaire_reponse')
                ->where('repondant_id',$repondant->id)
                ->get()->keyBy('question_id');

        $questions_obligatoires = modele('questionnaire_question')
            ->where('questionnaire_id',$this->modele->id)
            ->where('obligatoire',1)
            ->get();

        $champs_obligatoires_non_remplis = [];

        foreach($questions_obligatoires as $question){

            if(empty($reponses[$question->id]))
                $champs_obligatoires_non_remplis[] = $question->question;
        }

        if(!empty($champs_obligatoires_non_remplis))
            return traduction('messages.php.champs_obligatoires').' '.implode(', ',$champs_obligatoires_non_remplis);

        foreach($reponses as $question_id => $reponse) {

            // Cas choix multiples
            if(is_array($reponse))
                $reponse = json_encode($reponse);

            $reponse_a_enregistrer = array(
                'reponse' => $reponse,
                'question_id' => $question_id,
                'repondant_id' => $repondant->id,
                'groupe_reponses_id' => $groupe_reponses_id
            );

            $management_reponse = management('questionnaire_reponse');

            if (empty($this->modele->reponses_multiples)) {

                if (isset($questionnaires_reponses[$question_id])) {
                    $management_reponse = management('questionnaire_reponse', $questionnaires_reponses[$question_id]);
                    $reponse_a_enregistrer['id'] = $questionnaires_reponses[$question_id];
                    unset($reponse_a_enregistrer['groupe_reponses_id']);
                }
            }

            $retour = $management_reponse->test_enregistre($reponse_a_enregistrer);

            if($retour !== 'test_ok')
                $retours_enregistrement_question[] = $question_id;
            else
                $reponses_a_enregistrer[] = $reponse_a_enregistrer;

        }

        if(!empty($retours_enregistrement_question))
            return "Erreur ! Les/la réponse(s) suivantes n'ont pas pu être enregistrés : ". implode(' / ',$retours_enregistrement_question);

        foreach($reponses_a_enregistrer as $reponses){

            if(isset($reponses['id']))
                $management_reponse = management('questionnaire_reponse',$reponses['id']);
            else
                $management_reponse = management('questionnaire_reponse');

            $management_reponse->enregistre($reponses);

        }

        $suivi_envoi_questionnaire = modele('suivi_envoi_questionnaire')
            ->where('repondant_id', $repondant->id)
            ->where('id_questionnaire', $this->modele->id)->first();

        if(!empty($suivi_envoi_questionnaire)) {

            $date = date('Y-m-d H:i:s');

            management('suivi_envoi_questionnaire', $suivi_envoi_questionnaire->id)->enregistre(['repondu' => 1, 'date_de_la_reponse' => $date]);
        }

        $management_repondant->activer_action_post_reponse_questionnaire();

        return true;
	}


    /**
     *
     * Permet de récupérer le répondant liés aux parametres et le crée s'il n'existe pas
     *
     */
    public function repondant($parametres){

        $repondant = modele('questionnaire_element_repondant');

        if(!isset($parametres['type_element_origine']) && !isset($parametres['element_origine_id']))
            $repondant = $repondant->whereNull('type_element_origine')->whereNull('element_origine_id');

        foreach($parametres as $champ => $valeur){

            $repondant = $repondant->where($champ,$valeur);
        }

        $repondant = $repondant->first();

        if(empty($repondant)) {
            $management_repondant = management('questionnaire_element_repondant');

            $retour = $management_repondant->enregistre($parametres);

            if($retour !== true)
                return array('retour' => false,'message' => $retour);
        }
        else
            $management_repondant = management('questionnaire_element_repondant',$repondant->id,$repondant);

        return $management_repondant;
    }

    /**
     *
     * Permet de préparer l'envoi d'un mail d'un questionnaire
     *
     */
    public function envoi_questionnaire($destinataire,$parametres){

        //On crée le répondant s'il n'existe pas ou sinon on le récupére
        $management_repondant = $this->repondant($parametres);

        if(is_array($management_repondant) && isset($management_repondant['retour']))
            return $management_repondant;

        $repondant = $management_repondant->modele;

        $modifications = [
            'repondant_id' => $repondant->id,
            'id_questionnaire' => $this->modele->id,
            'email_destinataire' => $destinataire,
        ];

        management('suivi_envoi_questionnaire')->enregistre($modifications);

        return true;
    }
	
	/**
	 *
	 *
	 *
	 */
	public function sous_elements_a_copier_avec_duplication($types_elements = array()){

		$types_elements = parent::sous_elements_a_copier_avec_duplication($types_elements);
		
		$types_elements[] = array(
            'type_element' => 'questionnaire_question',
			'clef' => 'questionnaire_id',
			'hierarchie' => '',
		);

		return $types_elements;
	}

	/**
	 *
	 *
	 *
	 */
	public function traitements_supplementaires_duplication($element_id, $id_a_dupliquer, $type_element){

		// On va chercher la question en base du questionnaire que l'on duplique
		$questions_listes = modele('questionnaire_question')
            ->where('questionnaire_id', $id_a_dupliquer)
            ->where('type', 5)
            ->get();

		if (!$questions_listes->isEmpty()) {
			
			foreach ($questions_listes as $question_liste) {
				
				$options_question = modele('questionnaire_question_option')->where('question_id', $question_liste->id)->get();
				
				$id_nouvelle_question = $this->recupere_nouvelle_question_id($element_id, $question_liste);
				
				foreach ($options_question as $option_question) {
					
					$management_table = management('questionnaire_question_option');
					
					$informations_a_dupliquer = array();
					
					foreach ($option_question->getAttributes() as $colonne => $valeur) {

						if ($colonne == "id" || $colonne == "modifie_par" || $colonne == "modifie_le" || $colonne == "cree_par" || $colonne == "cree_le" || $colonne == "inactif" || $colonne == "chaine_tags_recherche")
							continue;
						
						
						$informations_a_dupliquer[$colonne] = $valeur;
						
						if($colonne == 'question_id')
							$informations_a_dupliquer[$colonne] = $id_nouvelle_question;
						else
							$informations_a_dupliquer[$colonne] = $valeur;
					}
					$retour = $management_table->enregistre($informations_a_dupliquer);
				}
                
			}
		}
	}
	
	/**
	 *
	 *
	 *
	 */
	public function recupere_nouvelle_question_id($element_id, $question_liste){
		
		$nouvelles_questions_listes = modele('questionnaire_question')->where('questionnaire_id', $element_id)->where('type', 5)->get();
		
		$id_nouvelle_question = false;
		
		foreach($nouvelles_questions_listes as $nouvelle_question_liste){
			
			if($nouvelle_question_liste->question === $question_liste->question && $nouvelle_question_liste->question === $question_liste->question)
				$id_nouvelle_question = $nouvelle_question_liste->id;
			
		}
		
		return $id_nouvelle_question;
	}

    public function filtres_questionnaires_questions($champ_libre_liaison){

        $type_element = $champ_libre_liaison->type_element;

        // Gestion des filtres de questionnaire
        $questionnaires_questions = modele('questionnaire')
            ->select('qq_temp.*')
            ->join('questionnaire_question', 'questionnaire_question.questionnaire_id', 'questionnaire.id')
            ->join('questionnaire_reponse', 'questionnaire_reponse.question_id', 'questionnaire_question.id')
            ->join('questionnaire_element_repondant', 'questionnaire_reponse.repondant_id', 'questionnaire_element_repondant.id')
            ->join('questionnaire_question as qq_temp', 'qq_temp.questionnaire_id', 'questionnaire.id')
            ->join($type_element, $type_element . '.' . $champ_libre_liaison->nom_sql, 'questionnaire_element_repondant.id')
            ->where('qq_temp.type', '!=', 7)
            ->where(\DB::raw("coalesce(questionnaire_question.inactif, 0)"), 0)
            ->where(\DB::raw("coalesce(questionnaire_reponse.inactif, 0)"), 0)
            ->where(\DB::raw("coalesce(questionnaire_element_repondant.inactif, 0)"), 0)
            ->where(\DB::raw("coalesce(qq_temp.inactif, 0)"), 0)
            ->groupBy('questionnaire.id', 'qq_temp.id')
            ->get();

        $questionnaires = modele('questionnaire')
            ->whereIn('id', $questionnaires_questions->pluck('questionnaire_id')->toArray())
            ->get()->keyBy('id');

        $options = modele('questionnaire_question_option')
            ->select('questionnaire_question_option.id as id_valeur', 'option as valeur', 'question_id', 'questionnaire_question.questionnaire_id')
            ->join('questionnaire_question', 'question_id', 'questionnaire_question.id')
            ->whereIn('questionnaire_id', $questionnaires_questions->pluck('questionnaire_id')->toArray())
            ->orderBy('questionnaire_question_option.ordre')->get()->groupBy(['questionnaire_id', 'question_id']);

        $filtres_questionnaires_question = [];

        foreach ($questionnaires_questions->groupBy('questionnaire_id') as $questionnaire_id => $questions) {

            $questionnaire = $questionnaires[$questionnaire_id];

            foreach ($questions as $question) {
                $question->type_element = 'questionnaire_' . $questionnaire->id;
                $question->nom = $question->question;
                $question->nom_sql = 'question_' . $question->id;
                $question->index_traduction_type_element = ucfirst(traduction('tables_libres.questionnaire.element')).' : ' . $questionnaire->nom;
                $question->type_filtre = management('questionnaire')->type_filtre_par_type_questionnaire()[$question->type]['filtre'];
                $question->champ_questionnaire = true;

                if ($question->type == 5)
                    $question->options = !empty($options[$questionnaire->id][$question->id]) ?
                        $options[$questionnaire->id][$question->id]->map->only(['id_valeur', 'valeur']) : null;
                else if ($question->type == 8)
                    $question->options = array_map(function ($tag) {
                        return array(
                            'id_valeur' => is_numeric($tag['valeur_checkbox']) ? intval($tag['valeur_checkbox']) : $tag['valeur_checkbox'],
                            'valeur' => $tag['nom_checkbox']
                        );
                    }, json_decode($question->checkbox, true));
                else if ($question->type == 9)
                    $question->options = [
                        [
                            'id_valeur' => 'true',
                            'valeur' => traduction('valeurs_listes_formatees.3.valeur_1')
                        ],
                        [
                            'id_valeur' => 'false',
                            'valeur' => traduction('valeurs_listes_formatees.3.valeur_2')
                        ],
                    ];
            }

            $filtres_questionnaires_question[] = [
                'type_element' => 'questionnaire_' . $questionnaire->id,
                'champs_libres' => $questions,
                'index_traduction' => ucfirst(traduction('tables_libres.questionnaire.element')).' : ' . $questionnaire->nom,
                'champ_liaison' => $champ_libre_liaison->nom_sql,
                'questionnaire' => true
            ];
        }

        return $filtres_questionnaires_question;
    }

    public function applique_filtre_sur_requete($filtre,$where, $alias){

        preg_match('/question_(\d+)/',$filtre['nom_sql'],$match_question);
        $id_question = $match_question[1];

        $questions_options = $this->questions();

        $question = collect($questions_options['questions'])->where('id', $id_question)->first();

        $champ_libre_reponse = champ_libre_modele('questionnaire_reponse','reponse');

        $champ_libre_reponse->alias_table = $alias;
        $champ_libre_reponse->alias_champ = $alias.'.reponse';

        $type_filtre = $this->type_filtre_par_type_questionnaire()[$question['type']];

        if($question->type == 8) {

            $where->where(function($condition) use ($filtre,$alias) {
                foreach ($filtre['valeurs'] as $valeur) {
                    $condition->orWhere($alias . '.reponse', 'LIKE', '%"' . $valeur . '"%');
                }
            });

            return $where;
        }
        else
            return (new $type_filtre['classe']($champ_libre_reponse))->applique_filtre_sur_requete($filtre['valeurs'],$where);
    }

    public function type_filtre_par_type_questionnaire(){

        return [
            1 => [
                'filtre' => 'filtre-montant',
                'classe' => 'App\Eden\Champs\Champ_montant',
            ],
            2 => [
                'filtre' => 'filtre-texte',
                'classe' => 'App\Eden\Champs\Champ_texte',
            ],
            5 => [
                'filtre' => 'filtre-liste-questionnaire',
                'classe' => 'App\Eden\Champs\Champ_liste_preenregistree',
            ],
            6 => [
                'filtre' => 'filtre-date',
                'classe' => 'App\Eden\Champs\Champ_date',
            ],
            8 => [
                'filtre' => 'filtre-liste-questionnaire',
            ],
            9 => [
                'filtre' => 'filtre-liste-questionnaire',
                'classe' => 'App\Eden\Champs\Champ_liste_preenregistree',
            ],
        ];
    }
}


