<?php

namespace App\Eden\Controllers;

use App\Eden\Champs\Champ;
use App\Http\Controllers\Controller;
use App\Eden\Controllers\Planification_controller;
use Illuminate\Http\Request;
use App\Eden\Models\Elements;


class Calendrier_controller extends Planification_controller {

    public function __construct() {

        $this->contexte = 'calendrier';
    }

    public function afficher() {

        // Affichage du calendrier
        return view('eden::calendrier.calendrier');

    }

    public function mon_calendrier() {

        return view('eden::calendrier.calendrier',[
            'mon_calendrier' => true
        ]);
    }


    /**
     *
     * Permet de récupérer les données nécessaires à l'affichage du calendrier
     *
     */
    public function recuperation_donnees(Request $formulaire){

        $formulaire = $formulaire->all();

        if(isset($formulaire['initialisation'])){

            $requete = explode('?', $_SERVER['HTTP_REFERER'])[1] ?? null;
            parse_str($requete, $formulaire['parametres_requete']);
        }

        $donnees_retour = service('calendrier')->recuperation_donnees($formulaire);

        return response()->json($donnees_retour);
    }

    /*
     *
     * On supprime les événements postérieurs à celui passé en paramètre faisant partie de la même récurrence
     *
     */
    public function supprimer_recurrence(Request $request, $id_tache){

        $retour = service('recurrence')->supprimer_recurrence($id_tache, null, $request->get('toute_la_serie'));

        return response()->json(['retour' => true]);
    }

    /*
     *
     * On récupère la récurrence associée à la tâche et le modèle de récurrence s'il y en a un
     *
     */
    public function recuperer_recurrence($id_tache){

        $id_tache_parent = modele('tache')->where('id', $id_tache)->value('parent_id');

        if(empty($id_tache_parent))
            return response()->json(['succes' => true, 'recurrence' => null]);

        $recurrence = modele('tache_recurrence')->where('tache_parent_id', $id_tache_parent)->first();

        // Si on trouve une récurrence et qu'elle est hebdomadaire, on charge les données des champs multiselect, pour charger les jours concernés de la semaine
        if(!empty($recurrence) && $recurrence->type_frequence == 2){

            $management = management('tache_recurrence', $recurrence->id, $recurrence);
            $management->charge_valeurs_champs_multiselection();
        }

        return response()->json(['succes' => true, 'recurrence' => $management->modele ?? $recurrence]);
    }
}
?>