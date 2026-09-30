<?php

namespace App\Eden\Managements\Services;

use Illuminate\Support\Facades\Log;

class Jour_indisponibilite_service {

    public function indisponibilites_calendrier(&$liste_dates,$debut,$fin,$format_calendrier){

        $date_debut = $debut['format_us'];
        $date_fin = $fin['format_us'];

        $this->verification_synchronisation_indisponibilite($date_debut,$date_fin);

        $jours_indisponibilites = modele('jour_indisponibilite')
            ->where(function($condition) use ($date_debut,$date_fin){
                $condition->whereBetween('date_fin',[$date_debut,$date_fin])
                    ->orWhere('date_fin','>=',$date_fin);
            })
            ->where(function($condition) use ($date_debut,$date_fin){
                $condition->whereBetween('date_debut',[$date_debut,$date_fin])
                    ->orWhere('date_debut','<=',$date_debut);
            })
            ->get();

        $champ = management('jour_indisponibilite')->champ('type_indisponibilite');

        foreach($jours_indisponibilites as $indisponibilite) {

            $date_debut = $indisponibilite->date_debut;
            $date_fin = $indisponibilite->date_fin;

            if($format_calendrier == 'mois'){

                foreach ($liste_dates as &$dates) {
                    $this->charge_indisponibilite($dates,$date_debut,$date_fin, $indisponibilite,$champ);
                }
            }
            else
                $this->charge_indisponibilite($liste_dates,$date_debut,$date_fin, $indisponibilite, $champ);
        }
    }

    /**
     * @param $donnees
     *
     * Ajoute les indisponibilités des jours comme les jours fériés
     *
     */
    public function indisponibilites_planning($semaines,$date_debut,$date_fin){

        $this->verification_synchronisation_indisponibilite($date_debut,$date_fin);

        $jours_indisponibilites = modele('jour_indisponibilite')
            ->where(function($condition) use ($date_debut,$date_fin){
                $condition->whereBetween('date_fin',[$date_debut,$date_fin])
                    ->orWhere('date_fin','>=',$date_fin);
            })
            ->where(function($condition) use ($date_debut,$date_fin){
                $condition->whereBetween('date_debut',[$date_debut,$date_fin])
                    ->orWhere('date_debut','<=',$date_debut);
            })
            ->get();

        $indisponibilites = [];

        $champ = management('jour_indisponibilite')->champ('type_indisponibilite');

        foreach($jours_indisponibilites as $indisponibilite) {

            $date_debut = $indisponibilite->date_debut;
            $date_fin = $indisponibilite->date_fin;

            foreach($semaines as $dates) {

                foreach ($dates['dates_format'] as $date) {

                    if ($date >= $date_debut && $date <= $date_fin) {

                        if (empty($indisponibilites[$date]))
                            $indisponibilites[$date] = [];

                        $management = management('jour_indisponibilite', $indisponibilite->id, $indisponibilite);

                        $indisponibilite->chaine_affichage = $management->affiche();
                        $indisponibilite->couleur = $champ->couleurs[$indisponibilite->type_indisponibilite] ?? null;
                        $indisponibilite->couleur_police = $champ->couleurs_polices[$indisponibilite->type_indisponibilite] ?? null;

                        $indisponibilites[$date][] = $indisponibilite;
                    }
                }
            }
        }

        return $indisponibilites;
    }

    /**
     * @param $donnees
     *
     * Ajoute les indisponibilités des jours comme les jours fériés
     *
     */
    public function indisponibilites($date_debut,$date_fin){

        $this->verification_synchronisation_indisponibilite($date_debut,$date_fin);

        $jours_indisponibilites = modele('jour_indisponibilite')
            ->where(function($condition) use ($date_debut,$date_fin){
                $condition->whereBetween('date_fin',[$date_debut,$date_fin])
                    ->orWhere('date_fin','>=',$date_fin);
            })
            ->where(function($condition) use ($date_debut,$date_fin){
                $condition->whereBetween('date_debut',[$date_debut,$date_fin])
                    ->orWhere('date_debut','<=',$date_debut);
            })
            ->get();

        $champ = management('jour_indisponibilite')->champ('type_indisponibilite');

        foreach($jours_indisponibilites as $indisponibilite){

            $management = management('jour_indisponibilite', $indisponibilite->id, $indisponibilite);

            $indisponibilite->chaine_affichage = $management->affiche();
            $indisponibilite->couleur = $champ->couleurs[$indisponibilite->type_indisponibilite] ?? null;
            $indisponibilite->couleur_police = $champ->couleurs_polices[$indisponibilite->type_indisponibilite] ?? null;

        }

        return $jours_indisponibilites;
    }

    /**
     *
     * Permet de vérifier que toutes les indisponibilités on était chargés pour la tranche horaire
     *
     */
    public function verification_synchronisation_indisponibilite($date_debut,$date_fin,$pays = 'FR'){

        $parametre_synchro_jour_indisponible = parametre('parametre_synchro_jour_indisponible_'.$pays);

        $besoin_synchronisation = true;

        $groupe_dates = [];

        if(!empty($parametre_synchro_jour_indisponible)){

            $groupe_dates = json_decode($parametre_synchro_jour_indisponible,true);

            foreach($groupe_dates as $dates){

                 if($date_debut >= $dates[0] && $date_fin <= $dates[1])
                    $besoin_synchronisation = false;
            }
        }

        if(!$besoin_synchronisation)
            return;

        $date_debut_synchro = date('Y-m-d',strtotime($date_debut.' -1 year'));
        $date_fin_synchro = date('Y-m-d',strtotime($date_fin.' +1 year'));

        if(env('BASE_LICENCE'))
            $retour = $this->synchronisation_avec_api($date_debut_synchro,$date_fin_synchro,$pays);
        else
            $retour = $this->synchronisation_via_reference($date_debut,$date_fin);

        if($retour === true) {

            function gestion_groupes_dates(&$groupe_dates){

                $changement_effectue = false;

                foreach($groupe_dates as $index => &$dates) {

                    if($changement_effectue)
                        continue;

                    foreach($groupe_dates as $index_boucle => $dates_boucle) {

                        if($index_boucle == $index || $changement_effectue)
                            continue;

                        if((($dates_boucle[1] >= $dates[0] && $dates_boucle[1] <= $dates[1]) || $dates_boucle[1] > $dates[1]) &&
                            (($dates_boucle[0] >= $dates[0] && $dates_boucle[0] <= $dates[1]) || $dates_boucle[0] < $dates[0])){

                            if($dates_boucle[0] < $dates[0])
                                $dates[0] = $dates_boucle[0];

                            if($dates_boucle[1] > $dates[1])
                                $dates[1] = $dates_boucle[1];

                            unset($groupe_dates[$index_boucle]);

                            $changement_effectue = true;
                        }

                    }
                }

                if($changement_effectue && sizeof($groupe_dates) > 1)
                    gestion_groupes_dates($groupe_dates);
            }

            $ajout_borne = true;

            foreach($groupe_dates as &$dates) {

                if($ajout_borne === false)
                    continue;

                if ($date_debut_synchro < $dates[0] && (($date_fin_synchro >= $dates[0] && $date_fin_synchro <= $dates[1]) || $date_fin_synchro > $dates[1]) ||
                    ($date_debut_synchro >= $dates[0] && $date_debut_synchro <= $dates[1]) && $date_fin_synchro > $dates[1]){

                    if($date_debut_synchro < $dates[0])
                        $dates[0] = $date_debut_synchro;

                    if($date_fin_synchro > $dates[1])
                        $dates[1] = $date_fin_synchro;

                    $ajout_borne = false;
                }
            }

            if($ajout_borne){

                $groupe_dates[] = array(
                    $date_debut_synchro,
                    $date_fin_synchro
                );
            }
            elseif(sizeof($groupe_dates) > 1)
                gestion_groupes_dates($groupe_dates);

            parametre('parametre_synchro_jour_indisponible_' . $pays, json_encode($groupe_dates));

        }

        return true;
    }

    /**
     * @param $date_de_debut
     * @param $date_de_fin
     * @param $pays
     * @return true
     *
     * Permet de récupérer les jours fériés via l'API openholidaysapi
     *
     */
    public function synchronisation_avec_api($date_de_debut = null,$date_de_fin = null,$pays = "FR"){

        if($date_de_debut == null)
            $date_de_debut = date('Y-m-d',strtotime('now -1 year'));

        if($date_de_fin == null)
            $date_de_fin = date('Y-m-d',strtotime('now +1 year'));

        try {
            $jours_feries = json_decode(file_get_contents('https://openholidaysapi.org/PublicHolidays?countryIsoCode=' . $pays . '&languageIsoCode=' . $pays . '&validFrom=' . $date_de_debut . '&validTo=' . $date_de_fin), true);

        }
        catch (\Exception | \Throwable $e){

            Log::alert('Erreur lors de la synchronisation openholidaysapi : '.$e);
            return $e;
        }

        $jour_indisponibilite_deja_synchronise = modele('jour_indisponibilite')->whereNotNull('cle_externe')->get()->keyBy('cle_externe');

        foreach($jours_feries as $jour_ferie){

            if(isset($jour_indisponibilite_deja_synchronise[$jour_ferie['id']]) || $jour_ferie['regionalScope'] != 'National')
                continue;
            try{
                management('jour_indisponibilite')->enregistre([
                    'nom' => $jour_ferie['name'][0]['text'],
                    'date_debut' => $jour_ferie['startDate'],
                    'date_fin' => $jour_ferie['endDate'],
                    'type_indisponibilite' => 1,
                    'cle_externe' => $jour_ferie['id']
                ]);
            }
            catch(\Exception | \Throwable $e){
                Log::alert("Erreur lors de l'enregistrement d'un jour indisponible : ".$e);
                continue;
            }
        }

        return true;
    }

    public function synchronisation_via_reference($date_debut,$date_fin){

        $url = env('EDEN_MODEL_API_URL') . 'api/maj_jour_indisponibilites';

        $postdata = http_build_query(
            array(
                'date_debut' => $date_debut,
                'date_fin' => $date_fin
            )
        );

        $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded\r\nx-client-id: " . env('EDEN_MODEL_API_KEY') . "\r\n",
                'method' => 'POST',
                'content' => $postdata
            )
        );

        $contexte = stream_context_create($options);

        try {
            $elements = file_get_contents($url, false, $contexte);
        }
        catch (\Exception | \Throwable $e){
            Log::alert("Erreur lors de l'appel de référence pour les jours indisponibilités : ".$e);
            return $e;
        }

        $jours_feries = json_decode($elements, true);

        $jour_indisponibilite_deja_synchronise = modele('jour_indisponibilite')->whereNotNull('cle_externe')->get()->keyBy('cle_externe');

        foreach($jours_feries as $jour_ferie){

            if(isset($jour_indisponibilite_deja_synchronise[$jour_ferie['cle_externe']]))
                continue;
            try{
                management('jour_indisponibilite')->enregistre($jour_ferie);
            }catch(\Exception | \Throwable $e){
                Log::alert("Erreur lors de l'enregistrement d'un jour indisponible : ".$e);
                continue;
            }
        }

        return true;
    }

    private function charge_indisponibilite(&$liste_dates,$date_debut,$date_fin, $indisponibilite,$champ){

        foreach ($liste_dates as &$date) {

            if(empty($date))
                continue;

            $date_format_us = $date['format_us'];

            if($date_format_us >= $date_debut && $date_format_us <= $date_fin){

                if(empty($date['indisponibilites']))
                    $date['indisponibilites'] = [];

                $management = management('jour_indisponibilite',$indisponibilite->id,$indisponibilite);

                $indisponibilite->chaine_affichage = $management->affiche();
                $indisponibilite->couleur = $champ->couleurs[$indisponibilite->type_indisponibilite] ?? null;
                $indisponibilite->couleur_police = $champ->couleurs_polices[$indisponibilite->type_indisponibilite] ?? null;

                $date['indisponibilites'][] = $indisponibilite;
            }
        }
    }
}