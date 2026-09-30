<?php

namespace App\Eden\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Tache_controller extends Controller {


    /**
     *
     * Récupère les autres affectations sous le format de la chaîne d'affichage d'une tâche via le management
     *
     */
    public function autres_affectations($element_id) {

        $management = management('tache', $element_id);

        $retour = $management->autres_affectations();

        return response()->json($retour);
    }

    /**
     * Récupère les infos supplémentaires de la tâche pour les afficher dans le tooltip
     * @param int $element_id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function recuperer_details(int $element_id) {

        $management = management('tache', $element_id);

        $autres_affectations_groupe = $management->autres_affectations();
        $infos_participants = $management->participants();

        if(isset($management->modele->client_id))
            $client_id_formate = management('tache')->affiche_client_sur_calendrier($management->modele->client_id);

        return response()->json([
            'organisateur' => $infos_participants['organisateur'],
            'participants' => $infos_participants['participants'],
            'autres_affectations_groupe' => $autres_affectations_groupe,
            'client_id_formate' => $client_id_formate ?? null,
        ]);
    }

    /**
     * Retourne les participants possibles correspondant à la recherche et la tâche 
     * @param \Illuminate\Http\Request $requete
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function rechercher_participants_possibles(Request $requete) {

        $tache = $requete->get('tache');
        $recherche = $requete->get('recherche');
        
        $resultat_recherche = management('tache')->rechercher_participants_possibles($recherche);

        return response()->json($resultat_recherche);
    }
}
