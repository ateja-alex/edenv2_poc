<?php

namespace App\Eden\Controllers;

use Illuminate\Support\Facades\DB;
use Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class  Questionnaire_controller extends Controller {

    /**
     * 
     * Affichage du questionnaire
     * 
     */
    public function afficher($questionnaire_id,$repondant_id,$token) {
		
		if(!empty($questionnaire_id) && !empty($repondant_id) && !empty($token)){

			// On renvoie la vue
			if($token == md5('questionnaire_eden_'.$questionnaire_id . $repondant_id)) {

                $infos_questionnaire = management('questionnaire',$questionnaire_id)->questions();

                $infos_questionnaire['questionnaire_id'] = $questionnaire_id;
                $infos_questionnaire['repondant_id'] = $repondant_id;
                $infos_questionnaire['token'] = $token;

                 $suivi_envoi_questionnaire = modele('suivi_envoi_questionnaire')
                    ->where('repondant_id', $repondant_id)
                    ->where('id_questionnaire', $questionnaire_id)->first();

                if(!empty($suivi_envoi_questionnaire) && $suivi_envoi_questionnaire->repondu == 1)
                    return redirect('/eden/questionnaire/merci');

                return view('eden::questionnaire.questions', $infos_questionnaire);
            }
		}

        abort(404);
    }

    /**
     * 
     * On traite les réponses envoyés
     * 
     */
    public function traitement_reponse(Request $request){

        //On récupère les réponses
        $formulaire = $request->all();

        //De même pour l'id du questionnaire
        $questionnaire_id = $formulaire['questionnaire_id'];
        $repondant_id = $formulaire['repondant_id'];
        $token = $formulaire['token'];
        $reponses = $formulaire['reponse'];

        if($token == md5('questionnaire_eden_'.$questionnaire_id . $repondant_id)) {

            $suivi_envoi_questionnaire = modele('suivi_envoi_questionnaire')
                ->where('repondant_id', $repondant_id)
                ->where('id_questionnaire', $questionnaire_id)->first();

            if(!empty($suivi_envoi_questionnaire) && $suivi_envoi_questionnaire->repondu == 1)
                return redirect('/eden/questionnaire/merci');

            $retour = management('questionnaire',$questionnaire_id)->enregistrer_reponses($reponses, ['repondant_id' => $repondant_id]);

            if ($retour !== true)
                return back()->with('error',$retour);

            return redirect('/eden/questionnaire/merci');

        }
		
		return back()->with('error', traduction('messages.php.questionnaire.erreur_validation'));
    }

    /**
     * 
     * Retourne la validation du questionnaire
     * 
     */
    public function merci(){

        return view('eden::questionnaire.retour');

    }

	 /**
     * 
     * Enregistre dans la bdd à qui il faut envoyer le questionnaire pour qu'ils soient envoyés ensuite par la tâche cron
     * 
     */
    public function envoi_questionnaire(Request $request){

		if(empty($request->ids) || empty($request->questionnaire_id) || empty($request->type_element))
			return response()->json(array('retour' => false,'message' => 'Formulaire incomplet'));

        $ids = $request->ids;
        $questionnaire_id = $request->questionnaire_id;
        $type_element = $request->type_element;

        $parametres_repondant = array(
            'type_element' => $type_element
        );

        if(!empty($request->type_element_origine) && !empty($request->element_origine_id)){
            $parametres_repondant['type_element_origine'] = $request->type_element_origine;
            $parametres_repondant['element_origine_id'] = $request->element_origine_id;
        }

        $management_questionnaire = management('questionnaire',$questionnaire_id);

        $emails_par_element = modele($type_element)->whereIn('id',$ids)->get()->pluck('adresse_email','id')->toArray();

		//On recupere les adresses email, on crée le lien du questionnaire et on enregistre dans la bdd
		foreach($ids as $id_element){

            if(!isset($emails_par_element[$id_element]))
                continue;

			$destinataire = $emails_par_element[$id_element];

            $parametres_repondant['element_id'] = $id_element;

            $retour = $management_questionnaire->envoi_questionnaire($destinataire,$parametres_repondant);

            if($retour !== true)
                return response()->json($retour);
		}

        return response()->json(array('retour' => true));
    }

    /**
     *
     * Permet de récupérer les informations d'un questionnaire
     *
     */
    public function prepare_questionnaire(Request $formulaire,$questionnaire_id){

        $parametres = $formulaire->all();

        $management_questionnaire = management('questionnaire',$questionnaire_id);

        $donnees = $management_questionnaire->questions();

        $donnees['questionnaire'] = $management_questionnaire->modele;

        if(empty($management_questionnaire->modele->reponses_multiples))
            $donnees['reponse_questionnaire'] = $management_questionnaire->reponses($parametres);

        return response()->json($donnees);
    }

    /**
     *
     * Permet de récupérer les reponses d'un questionnaire
     *
     */
    public function reponses(Request $formulaire,$questionnaire_id){

        $formulaire = $formulaire->all();

        $management_questionnaire = management('questionnaire',$questionnaire_id);

        $parametres = [];
        $parametres_liste = [];

        if(!empty($formulaire['parametres']))
            $parametres = $formulaire['parametres'];

        if(!empty($formulaire['parametres_liste']))
            $parametres_liste = $formulaire['parametres_liste'];

        $donnees = $management_questionnaire->reponses($parametres,$parametres_liste);

        return response()->json($donnees);
    }

    /**
     *
     * Permet d'enregistrer les réponses à un questionnaire
     *
     */
    public function enregistrer(Request $formulaire,$questionnaire_id){

        $formulaire = $formulaire->all();

        $reponses = isset($formulaire['reponses']) ? $formulaire['reponses'] : [];
        $parametres = $formulaire['parametres'];

        $management_questionnaire = management('questionnaire',$questionnaire_id);

        $retour = $management_questionnaire->enregistrer_reponses($reponses,$parametres);

        if($retour !== true)
            return response()->json(array('retour' => false, 'erreur' => $retour));

        return response()->json(array('retour' => true));
    }
}