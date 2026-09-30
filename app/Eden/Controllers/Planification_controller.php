<?php

namespace App\Eden\Controllers;

use App\Http\Controllers\Controller;

class Planification_controller extends Controller {

    protected $contexte = '';

    /**
     *
     * Génération du PDF du planning
     *
     */
    public function imprimer($semaine_voulue) {

        $parametres_initiaux = $this->recuperer_parametres();
        
        try{
            if(!empty(request()->type) && request()->type == 'global')
                $url_fichier = service('planning')->imprimer($semaine_voulue, $parametres_initiaux);
            else
                $url_fichier = service('calendrier')->imprimer($semaine_voulue, $parametres_initiaux);
        } catch(\Exception $e){

            return response()->json(['succes' => false, 'message' => $e->getMessage()]);
        }

        return response()->json(['succes' => true, 'url' => $url_fichier]) ;
    }

    /**
     *
     * Envoi par email du PDF du planning
     *
     */
    public function envoyer_par_mail($semaine_voulue) {

        $parametres_initiaux = $this->recuperer_parametres();

        return service('calendrier')->envoyer_par_mail($semaine_voulue, $parametres_initiaux);
    }

    /**
     *
     * Envoi par email du PDF du calendrier
     *
     */
    public function recuperer_parametres() {

        if($this->contexte == 'calendrier')
            $parametres_initiaux = parametre_utilisateur('parametres_calendrier_'.service('calendrier')->contexte, false, moi()->id);
        else if($this->contexte == 'planning')
            $parametres_initiaux = parametre_utilisateur('parametres_planning', false, moi()->id);

        if(!empty($parametres_initiaux))
            $parametres_initiaux = $this->contexte == 'calendrier' ? unserialize(base64_decode($parametres_initiaux)) : json_decode(base64_decode($parametres_initiaux), true);
        else
            $parametres_initiaux = ['filtres' => []];

        if($this->contexte == 'calendrier') {

            $requete = explode('?', $_SERVER['HTTP_REFERER'])[1] ?? null;

            if(!empty($requete)) {
                parse_str($requete, $parametres_requete);

                $parametres_requete = json_decode(base64_decode($parametres_requete['parametres']), true);

                $parametres_initiaux['filtres_avec_valeurs'] = $parametres_requete['filtres_valeurs'];
            }
        }

        if(empty($parametres_initiaux['filtres']))
            $parametres_initiaux['filtres'] = [];

        return $parametres_initiaux;
    }
}
