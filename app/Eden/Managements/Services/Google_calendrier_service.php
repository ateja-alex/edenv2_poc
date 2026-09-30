<?php

namespace App\Eden\Managements\Services;

use Google\Model;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;

use App\Eden\Exceptions\Eden_exception;
use App\Eden\Models\Champs_liste_formatee;
use App\Eden\Variables;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class Google_calendrier_service {

    private $google_client = null;
    private $calendar_service = null;
    private $correspondance_frequence_google = [

        'DAILY'=> 1,
        'WEEKLY'=> 2,
        'MONTHLY'=> 3,
        'YEARLY'=> 4,
    ];
    private $exceptions_recurrence = array();
    private $verifications_existence_rdv = null;
    private $rdvs_echoues_utilisateur = null;
    private $occurrences_formatees;
    private $date_debut_synchro;
    protected $utilisateurs_synchronises;
    protected $rdv_participants;
    protected $participants_avant;

    public function __construct(){

        $this->google_client = service('google_authentification')->instancie_google_client_application();
    }

    private function instancie_service_calendrier($utilisateur){

        if(!empty($this->calendar_service) && $this->google_client->getConfig('subject') === $utilisateur->email)
            return;

        if(!empty($utilisateur))
            $this->google_client->setSubject($utilisateur->email);

        $this->calendar_service = new \Google_Service_Calendar($this->google_client);
    }
    
	/**
	 *
	 * Crée un événement dans le calendrier principal de l'utilisateur passé en paramètre
	 *
	 */
	public function creer_evenement($infos, $utilisateur){

        $this->instancie_service_calendrier($utilisateur);

		$nouvel_evenement = $this->prepare_donnees_pour_evenement($infos);

        try{

            $retour = $this->calendar_service->events->insert('primary', $nouvel_evenement);
        } catch(\Throwable $e){

            Log::warning("Échec de la création de l'événement pour l'utilisateur " . $utilisateur->id . "(email : ". $utilisateur->email . "). Message d'erreur : " . $e->getMessage() . " Stacktrace : " . $e->getTraceAsString());
            return traduction('messages.php.google_calendrier.erreur_creation');
        }

		return $retour;
	}

    /**
     *
     * Crée un événement dans le calendrier principal de l'utilisateur passé en paramètre
     *
     */
    public function recuperer_evenement($id_google, $utilisateur){

        $this->instancie_service_calendrier($utilisateur);

        try{

            $retour = $this->calendar_service->events->get('primary', $id_google);
        } catch(\Throwable $e){

            Log::warning("Échec de la récupération de l'événement ". $id_google ." pour l'utilisateur " . $utilisateur->id . "(email : ". $utilisateur->email . "). Message d'erreur : " . $e->getMessage() . " Stacktrace : " . $e->getTraceAsString());
            return traduction('messages.php.google_calendrier.erreur_recuperation_evenement');
        }

        return $retour;
    }
	 
	/**
	 *
	 *  Modifie un événement dans le calendrier principal de l'utilisateur passé en paramètre
	 *
	 */
	public function modifier_evenement($infos, $id_evenement, $utilisateur){

        $this->instancie_service_calendrier($utilisateur);

		$nouvel_evenement = $this->prepare_donnees_pour_evenement($infos);

        try{

            $retour = $this->calendar_service->events->update('primary', $id_evenement, $nouvel_evenement);
        } catch(\Throwable $e){

            Log::warning("Échec de la modification de l'événement (id_google : ". $id_evenement .") pour l'utilisateur " . $utilisateur->id . "(email : ". $utilisateur->email . "). Message d'erreur : " . $e->getMessage() . " Stacktrace : " . $e->getTraceAsString());

            return traduction('messages.php.google_calendrier.erreur_modification');
        }

		return $retour;
	}
	 
	/**
	 *
	 *  Supprime un événement dans le calendrier principal de l'utilisateur passé en paramètre
	 *
	 */
	public function supprimer_evenement($id_evenement, $utilisateur){

        $this->instancie_service_calendrier($utilisateur);

        try{

            $this->calendar_service->events->delete('primary', $id_evenement);
        } catch(\Throwable $e){

            Log::warning("Échec de la suppression de l'événement (id_google : ". $id_evenement .") pour l'utilisateur " . $utilisateur->id . "(email : ". $utilisateur->email . "). Message d'erreur : " . $e->getMessage() . " Stacktrace : " . $e->getTraceAsString());
            return traduction('messages.php.google_calendrier.erreur_suppression');
        }

        return true;
	}

	/**
	 * 
	 * Prépare un tableau formaté pour Google pour la création ou la modification d'un évènement
	 * 
	 */
	protected function prepare_donnees_pour_evenement($infos) {

        //On récupère les catégories disponibles dans Eden
        $categories_possibles = $this->recuperer_categories_possibles('id_couleur_google', 'id_valeur');

        $nouvel_evenement = new Event();

        if(isset($infos['titre']))
            $nouvel_evenement->setSummary($infos['titre']);
        
        if(isset($infos['commentaire']))
            $nouvel_evenement->setDescription($infos['commentaire']);
        
        if(isset($infos['prive']))
            $nouvel_evenement->setVisibility($infos['prive']);
        
        $start = new EventDateTime();
        $start->setTimeZone('Europe/Paris');

        $end = new EventDateTime();
        $end->setTimeZone('Europe/Paris');

        if($infos['journee_entiere'] == 1){

            $start->setDate(formate_date('Y-m-d', $infos['date_de_debut']));
            $start->setDateTime(Model::NULL_VALUE);

            $end->setDate(formate_date('Y-m-d', $infos['date_de_fin']));
            $end->setDateTime(Model::NULL_VALUE);
        }
        else{

            $start->setDateTime(formate_date('Y-m-dTH:i:s', $infos['date_de_debut']));
            $start->setDate(Model::NULL_VALUE);

            $end->setDateTime(formate_date('Y-m-dTH:i:s', $infos['date_de_fin']));
            $end->setDate(Model::NULL_VALUE);
        }
        
        $nouvel_evenement->setStart($start);
        $nouvel_evenement->setEnd($end);
        
        if(isset($infos['recurrence']))
            $nouvel_evenement->setRecurrence($infos['recurrence']);

        if(isset($categories_possibles[$infos['categorie']]))
            $nouvel_evenement->setColorId($categories_possibles[$infos['categorie']]);

        if(!empty($infos['participants'])){

            $participants = array();
            $correspondance_statuts_google = [
                0 => 'needsAction',
                1 => 'accepted',
                2 => 'declined',
                3 => 'tentative',
            ];

            foreach($infos['participants'] as $participant){

                $participants[] = [
                    'email' => $participant['adresse_email'],
                    'response' => $correspondance_statuts_google[$participant['statut_participant']] ?? 0
                ];
            }

            $nouvel_evenement->setAttendees($participants);
        }

		return $nouvel_evenement;
	}

    /*
     *
     * Vérifie si on doit synchroniser la tâche lors d'un enregistrement dans Eden
     *
     */
    public function condition_synchro_rdv($modele, $modifications = null){

        if(empty($modele->type_tache_rdv) || defined('synchro_rdv_google_vers_eden'))
            return false;

        //Dans le cas d'une simple validation, on ne resynchronise pas.
        if(isset($modifications) && count($modifications) === 1 && array_key_exists('terminee', $modifications))
            return false;

        if(!fonctionnalite('google_utiliser_connexion') || empty($modele->affectation))
            return false;

        return true;
    }

    /**
     *
     *
     *
     */
    public function synchronise_evenements(){

        $this->utilisateurs_synchronises = modele('utilisateur')->whereNotNull('access_token_google')->where('synchronisation_calendrier_google', 1)->orderBy('date_derniere_synchro_rdv_google')->get()->keyBy('id');

        //On récupère le calendrier de chaque utilisateur
        foreach($this->utilisateurs_synchronises as $utilisateur){

            $this->instancie_service_calendrier($utilisateur);

            $token_google = parametre_cron('sync_token_google', id_cron, false, $utilisateur->id);

            if(empty($token_google)){

                $date_debut_synchro = new \DateTime(date('Y-m-d', strtotime('-' . fonctionnalite('google_nombre_mois') . ' months')), new \DateTimeZone('Europe/Paris'));
                $this->date_debut_synchro = $date_debut_synchro->format('Y-m-d\TH:i:sP');

                try{

                    $delta = $this->calendar_service->events->listEvents('primary', ['timeMin' => $this->date_debut_synchro]);
                    parametre_cron('debut_synchro_google', id_cron, $this->date_debut_synchro, $utilisateur->id);
                } catch(\Throwable $e){

                    Log::warning("Échec de la synchronisation globale des événements pour l'utilisateur " . $utilisateur->id . "(email : ". $utilisateur->email . "). Message d'erreur : " . $e->getMessage() . " Stacktrace : " . $e->getTraceAsString());
                    continue;
                }
            } else {

                try{

                    $delta = $this->calendar_service->events->listEvents('primary', ['syncToken' => $token_google]);
                    $this->date_debut_synchro = parametre_cron('debut_synchro_google', id_cron, false, $utilisateur->id);
                } catch(\Throwable $e){

                    Log::warning("Échec de la récupération des événements avec le syncToken pour l'utilisateur " . $utilisateur->id . "(email : ". $utilisateur->email . "). Message d'erreur : " . $e->getMessage() . " Stacktrace : " . $e->getTraceAsString());

                    //Si on a une 410, on vide le token de synchro, car cela veut dire qu'il n'est plus valide
                    if($e->getCode() === 410)
                        DB::delete('DELETE FROM cron_parametres WHERE id_cron = ' . id_cron . ' AND id_utilisateur = ' . $utilisateur->id);

                    continue;
                }
            }

            $rdv_google = $delta->items;

            //S'il y a un nextlink dans le tableau, c'est qu'il reste des modifs à récupérer
            while(!empty($delta->nextPageToken)) {

                try{

                    $delta = $this->calendar_service->events->listEvents('primary', ['pageToken' => $delta->nextPageToken]);

                    $rdv_google = array_merge($rdv_google, $delta->items);
                } catch(\Throwable $e){

                    Log::warning("Échec de la récupération des événements avec le pageToken pour l'utilisateur " . $utilisateur->id . "(email : ". $utilisateur->email . "). Message d'erreur : " . $e->getMessage() . " Stacktrace : " . $e->getTraceAsString());
                    continue 2;
                }
            }
            
            $retour = $this->enregistrer_evenements($rdv_google, $utilisateur);

            if($retour !== true)
                continue;

            parametre_cron('sync_token_google', id_cron, $delta->nextSyncToken, $utilisateur->id);
            management('utilisateur', $utilisateur->id, $utilisateur)->enregistre_modele(['date_derniere_synchro_rdv_google' => date('Y-m-d H:i:s')]);
        }

        if(!empty($this->rdv_participants))
            $this->lier_evenements_participants();

        $this->synchronise_recurrences_sans_fin();

        return true;
    }

    /*
     *
     * Traite les données avant de procéder à l'enregistrement lors de la synchro
     *
     */
    private function enregistrer_evenements($rdv_google, $utilisateur){

        try {
            //On regarde s'il existe déjà dans la bdd
            $this->verifications_existence_rdv = modele('tache')
                ->avec_parents()
                ->avec_inactifs()
                ->whereIn('id_google', array_map(fn($rdv) => $rdv->id, $rdv_google))
                ->get()->keyBy('id_google');

        } catch (\Throwable $e) {

            Log::warning("Échec de la récupération des évènements dans la bdd pour l'utilisateur " . $utilisateur->id . "(email : ". $utilisateur->email . "). Message d'erreur : " . $e->getMessage() . " Stacktrace : " . $e->getTraceAsString());

            $this->verifications_existence_rdv = modele('tache')
                ->avec_parents()
                ->avec_inactifs()
                ->whereNotNull('id_google')
                ->get()->keyBy('id_google');
        }

        //On vérifie chaque rdv pour savoir si on doit l'enregistrer
        foreach ($rdv_google as $rdv) {

            if(!empty($rdv->status) && $rdv->status === "cancelled"){

                if(!empty($this->verifications_existence_rdv[$rdv->id]) && $this->verifications_existence_rdv[$rdv['id']]->inactif !== 1)
                    $retour = management('tache', $this->verifications_existence_rdv[$rdv->id]->id, $this->verifications_existence_rdv[$rdv->id])->supprime();

                continue;
            }
            else if(!empty($this->verifications_existence_rdv[$rdv->id]) && $this->verifications_existence_rdv[$rdv->id]->inactif === 1){

                $this->supprimer_evenement($rdv->id, $utilisateur);
                continue;
            }

            try{

                if(!empty($this->verifications_existence_rdv[$rdv->id]))
                    $this->enregistrer_evenement($rdv, $utilisateur, $this->verifications_existence_rdv[$rdv->id]);
                else
                    $this->enregistrer_evenement($rdv, $utilisateur);

                $rdv_echoue = isset($this->rdvs_echoues_utilisateur) ? $this->rdvs_echoues_utilisateur->get($rdv->id) : null;

                if(isset($rdv_echoue))
                    management('tache_erreurs_synchro_externe',$rdv_echoue->id, $rdv_echoue)->supprime();
            } catch(\Throwable $e){

                Log::warning("Échec de l'enregistrement du rdv pour l'utilisateur " . $utilisateur->id . "(email : ". $utilisateur->email . "). RDV Google : " . json_encode($rdv) . " Message d'erreur : " . $e->getMessage() . " Stacktrace : " . $e->getTraceAsString());

                $modifications = [
                    'message_erreur' => $e->getMessage(),
                    'stacktrace_erreur' => $e->getTraceAsString(),
                    'modele_rdv' => json_encode($rdv),
                    'synchro_externe' => 2,
                ];

                $rdv_echoue = isset($this->rdvs_echoues_utilisateur) ? $this->rdvs_echoues_utilisateur->get($rdv->id) : null;

                if(isset($rdv_echoue))
                    management('tache_erreurs_synchro_externe',$rdv_echoue->id, $rdv_echoue)->enregistre($modifications);
                else
                    management('tache_erreurs_synchro_externe')->enregistre(array_merge($modifications,[
                        'id_externe' => $rdv->id,
                        'utilisateur' => $utilisateur->id,
                    ]));

                continue;
            }
        }

        return true;
    }

    /*
     *
     * Formate les données du rdv reçu par Google, puis l'enregistre dans la bdd
     *
     */
    private function enregistrer_evenement($rdv, $utilisateur, $modele = null){

        $modifications = $this->prepare_donnees_enregistrement_eden($rdv, $utilisateur, $modele);

        if(isset($rdv->attendees)){
            
            $verification = modele('tache')
                ->where('id_commun_taches_participants', $rdv->iCalUID)
                ->where('participant', 1)
                ->where('affectation', $utilisateur->id)
                ->whereDate('date_de_debut', date_create($modifications['date_de_debut'])->format('Y-m-d'))
                ->first();

            if(isset($verification))
                $modele = $verification;
        }

        $management_tache = !empty($modele) ? management('tache', $modele->id, $modele) : management('tache');
        $management_tache->fonction_a_eviter = ['verifie_champs_obligatoires'];

        // Si on est en modification, on vérifie que certains champs ont bien été modifié, sinon on n'enregistre pas
        if(isset($management_tache->modele)){

            $modification = false;

            foreach($modifications as $nom_champ => $valeur){

                if(!in_array($nom_champ, ['modifier_recurrence', 'tache_creation_tache_recurrence', 'participants']) && $valeur != $management_tache->modele->$nom_champ)
                    $modification = true;
            }

            if($management_tache->modele->tache_parent == 1 && isset($modifications['tache_creation_tache_recurrence'])) {

                $modele_recurrence = modele('tache_recurrence')->where('tache_parent_id', $management_tache->modele->id)->first();

                if(!isset($modele_recurrence))
                    $modification = true;
                else {
                    foreach ($modifications['tache_creation_tache_recurrence'] as $nom_champ => $valeur) {

                        if ($valeur !== $modele_recurrence->$nom_champ)
                            $modification = true;
                    }
                }
            }
            elseif(isset($modifications['tache_creation_tache_recurrence']))
                $modification = true;

            if(isset($modifications['participants'], $this->participants_avant)){

                $participants_avant = [
                    'participants_externes' => array_filter($this->participants_avant, function($pa) {return isset($pa['id_participant']);}),
                    'participants_internes' => array_filter($this->participants_avant, function($pa) {return isset($pa['id_tache']);}),
                    'nouveaux_participants' => array()
                ];

                $participants = [
                    'participants_externes' => array_filter($modifications['participants'], function($p) {return isset($p['id_participant']);}),
                    'participants_internes' => array_filter($modifications['participants'], function($p) {return isset($p['id_tache']);}),
                    'nouveaux_participants' => array_filter($modifications['participants'], function($p) {return !isset($p['id_tache']) && !isset($p['id_participant']);})
                ];

                if($participants != $participants_avant)
                    $modification = true;
            }

            if(!$modification)
                return;
        }

        //Si un tableau des occurrences existe pour le rdv, on le crée en attribut du management tache
        if(!empty($this->occurrences_formatees))
            $management_tache->occurrences_personnalisees = $this->occurrences_formatees;

        $retour = $management_tache->enregistre($modifications);

        if(!isset($modele) && $retour === true) {
            
            $taches_creees = collect([$rdv->id => $management_tache->modele]);

            if(isset($rdv->recurrence))
                $taches_creees = $taches_creees->union(
                    modele('tache')
                        ->where('parent_id', $management_tache->modele->id)
                        ->get()
                        ->keyBy('id_google')
                );
            
            $this->verifications_existence_rdv = $this->verifications_existence_rdv->union($taches_creees);
            
            if(isset($modifications['participants']) || isset($modifications['participant']))
                $this->rdv_participants[$rdv->iCalUID][$rdv->organizer->self === true ? 'organisateur' : $management_tache->modele->affectation] = $taches_creees->keyBy(function($tache){
                    
                    if($tache->tache_parent)
                        return 'parent';

                    return date_create($tache->date_de_debut)->format('Y-m-d');
                })->all();
        }
        elseif($retour !== true) {

            //Si on enregistrait une récurrence, on ne sait pas si ça a planté sur l'enregistrement de la tâche parent ou
            // sur la génération des occurrences, on doit donc supprimer les tâches potentiellement créées pour éviter
            // les doublons lors de la synchro des rdv en erreurs
            if(isset($rdv->recurrence)) {

                DB::delete("DELETE FROM tache_recurrence where tache_parent_id in (SELECT id FROM tache where id_google = '" . $rdv->id . "')");
                DB::delete("DELETE FROM tache where parent_id in (SELECT id FROM tache where id_google = '" . $rdv->id . "') or id_google = '" . $rdv->id . "')");
            }
            
            throw new Eden_exception($retour, 500);
        }
    }
    
    private function enregistrer_occurrence($occurrence, $management, $modifications){
        
        if(isset($occurrence->start->dateTime)) {

            $date_debut_utc = new \DateTime($occurrence->start->dateTime, new \DateTimeZone($occurrence->start->timeZone));
            $date_debut_locale = $date_debut_utc->setTimezone(new \DateTimeZone('Europe/Paris'))->format('Y-m-d H:i:s');

            $date_fin_utc = new \DateTime($occurrence->end->dateTime, new \DateTimeZone($occurrence->end->timeZone));
            $date_fin_locale = $date_fin_utc->setTimezone(new \DateTimeZone('Europe/Paris'))->format('Y-m-d H:i:s');

            if(isset($modifications['journee_entiere']))
                unset($modifications['journee_entiere']);
        } else {

            $date_debut_utc = new \DateTime($occurrence->start->date);
            $date_debut_locale = $date_debut_utc->format('Y-m-d') . ' 00:00:00';

            $date_fin_utc = new \DateTime($occurrence->end->date);
            $date_fin_locale = $date_fin_utc->modify('-1 day')->format('Y-m-d') . ' 23:59:59';

            $modifications['journee_entiere'] = 1;
        }

        $modifications['date_de_debut'] = $date_debut_locale;
        $modifications['date_de_fin'] = $date_fin_locale;
        $modifications['id_google'] =  $occurrence->id;

        $retour = $management->enregistre($modifications);

        if($retour !== true)
            throw new Eden_exception($retour, 500);

        $this->verifications_existence_rdv->put($occurrence->id, $management->modele);

        return true;
    }

    /*
     *
     * Prépare les données nécessaires à l'enregistrement du rdv dans Eden à partir des données du rdv Google
     *
     */
    private function prepare_donnees_enregistrement_eden($rdv, $utilisateur, $modele = null):array{

        $modifications = array();
        $correspondance_statut = [
            'needsAction' => null,
            'accepted' => 1,
            'declined' => 2,
            'tentative' => 3,
        ];

        //L'heure est retournée par l'api dans le fuseau horaire UTC et non Europe/Paris, il faut la convertir
        if(isset($rdv->start->dateTime)) {

            $date_debut_utc = new \DateTime($rdv->start->dateTime, new \DateTimeZone($rdv->start->timeZone));
            $date_debut_locale = $date_debut_utc->setTimezone(new \DateTimeZone('Europe/Paris'))->format('Y-m-d H:i:s');

            $date_fin_utc = new \DateTime($rdv->end->dateTime, new \DateTimeZone($rdv->end->timeZone));
            $date_fin_locale = $date_fin_utc->setTimezone(new \DateTimeZone('Europe/Paris'))->format('Y-m-d H:i:s');

            $modifications['journee_entiere'] = null;
        } else {

            $date_debut_utc = new \DateTime($rdv->start->date);
            $date_debut_locale = $date_debut_utc->format('Y-m-d') . ' 00:00:00';

            $date_fin_utc = new \DateTime($rdv->end->date);
            $date_fin_locale = $date_fin_utc->modify('-1 day')->format('Y-m-d') . ' 23:59:59';

            $modifications['journee_entiere'] = 1;
        }

        if(isset($rdv->summary) && (empty($modele) || !empty($modele) && $rdv->summary != $modele->titre))
            $modifications['titre'] = $rdv->summary;
        else if(!empty($modele->titre))
            $modifications['titre'] = $modele->titre;
        else
            $modifications['titre'] = 'N/A';

        $modifications['date_de_debut'] = $date_debut_locale;
        $modifications['date_de_fin'] = $date_fin_locale;
        $modifications['affectation'] = $utilisateur->id;

        if(isset($rdv->description)){

            $commentaire = str_replace(["\n","\r"], "",$rdv->description);

            if(empty($modele) || ($rdv->description != $modele->commentaire))
                $modifications['commentaire'] = $commentaire;
        }
        else
            $modifications['commentaire'] = '';

        if(isset($rdv->visibility) && $rdv->visibility == 'private' && (empty($modele) || $rdv->visibility != $modele->prive))
            $modifications['prive'] = 1;

        //On récupère les catégories disponibles dans Eden
        $categories_possibles = $this->recuperer_categories_possibles('id_valeur', 'id_couleur_google');

        if(isset($rdv->colorId) && isset($categories_possibles[$rdv->colorId]))
            $modifications['type_tache_rdv'] = $categories_possibles[$rdv->colorId];
        elseif(empty($verification_existence_rdv))
            $modifications['type_tache_rdv'] = fonctionnalite('google_type_rdv_defaut');

        if(empty($modele) || $rdv->id != $modele->id_google)
            $modifications['id_google'] = $rdv->id;

        if(isset($rdv->recurrence)) {

            $modifications['tache_creation_tache_recurrence'] = $this->traitement_donnees_recurrence($rdv);

            if(!empty($modele))
                $modifications['modifier_recurrence'] = 2;

            $this->occurrences_formatees($rdv, $utilisateur, $modifications, $modele);
        }
        else if(isset($rdv->recurringEventId)){

            $parent_eden = modele('tache')->avec_parents()->where('id_google', $rdv->recurringEventId)->first();

            if(isset($parent_eden->id))
                $modifications['parent_id'] = $parent_eden->id;
        }

        if(isset($rdv->attendees) && !empty($rdv->organizer->self)){

            $modifications['participants'] = array();
            $emails_utilisateurs = $this->utilisateurs_synchronises->pluck('email');
            
            if(isset($modele))
                $this->participants_avant = management('tache', $modele->id, $modele)->participants(true)['participants'];

            foreach($rdv->attendees as $participant_google){

                if($participant_google->organizer === true)
                    continue;

                $statut = $correspondance_statut[$participant_google->responseStatus];

                if(isset($this->participants_avant)){

                    $participant = Arr::first($this->participants_avant, fn($pe) => $pe['adresse_email'] === $participant_google->email);

                    if(!empty($participant)){
                        
                        $modifications['participants'][] = array_merge(
                            $participant, 
                            array('statut_participant' => $statut)
                        );
                        
                        continue;
                    }
                }

                $resultats_recherche = management('tache')->rechercher_participants_possibles($participant_google->email);

                if(!empty($resultats_recherche)) {

                    $premier_resultat = $resultats_recherche[array_key_first($resultats_recherche)];

                    // On ignore les tâches des participants correspondant à des utilisateurs synchronisés, le reste de la synchro s'occupera de leurs tâches
                    if($emails_utilisateurs->contains($premier_resultat['adresse_email']))
                        continue;

                    $modifications['participants'][] = array_merge(
                        $resultats_recherche[array_key_first($resultats_recherche)], 
                        array('statut_participant' => $statut)
                    );
                }
                else {

                    $modifications['participants'][] = [
                        'adresse_email' => $participant_google->email,
                        'statut_participant' => $statut
                    ];
                }
            }
        }
        else if(isset($rdv->attendees)){

            $modifications['participant'] = 1;
            $participant_google = $rdv->attendees[array_find($rdv->attendees, fn($p) => $p->email === $utilisateur->email)];
            $modifications['statut_participant'] = $correspondance_statut[$participant_google->responseStatus];
            $modifications['adresse_email_organisateur'] = $rdv->organizer->email;

            if($rdv->status == 'cancelled')
                $modifications['annulee'] = 1;
        }

        return $modifications;
    }
    
    private function occurrences_formatees($rdv, $utilisateur, $modifications, $modele){

        if(!empty($this->occurrences_formatees))
            $this->occurrences_formatees = array();

        $date_de_debut = new \DateTime($modifications['tache_creation_tache_recurrence']['date_de_debut'], new \DateTimeZone('Europe/Paris'));
        $date_de_debut = $date_de_debut->format('Y-m-d\TH:i:sP');

        if(isset($modifications['tache_creation_tache_recurrence']['date_de_fin'])) {

            $date_de_fin = new \DateTime($modifications['tache_creation_tache_recurrence']['date_de_fin'] . ' 23:59:59', new \DateTimeZone('Europe/Paris'));
            $date_de_fin = $date_de_fin->format('Y-m-d\TH:i:sP');
        }
        else{

            if(strtotime($modifications['tache_creation_tache_recurrence']['date_de_debut']) < strtotime($this->date_debut_synchro))
                $date_de_fin = date_create_from_format('Y-m-d\TH:i:sP', $this->date_debut_synchro);
            else
                $date_de_fin = date_create_from_format('Y-m-d H:i:s', $modifications['tache_creation_tache_recurrence']['date_de_debut'] . ' 23:59:59');

            $date_de_fin = $date_de_fin->modify('+ ' . fonctionnalite('duree_max_creation_recurrence') . 'months')
                ->format('Y-m-d\TH:i:sP');
        }
        

        $occurrences_google = $this->recuperer_occurrences_recurrence($rdv->id, $date_de_debut, $date_de_fin, $utilisateur);

        if(gettype($occurrences_google) === 'string')
            throw new Eden_exception($occurrences_google, 500);

        $modifications_triees = service('recurrence')->formate_donnees($modifications);

        foreach($occurrences_google as $occurrence){

            if(!empty($occurrence->status) && $occurrence->status === "cancelled")
                continue;

            $occurrence_formatee = [
                'id_google' => $occurrence->id,
            ];

            $modifications_occurrence = $this->prepare_donnees_enregistrement_eden($occurrence, $utilisateur, $modele);

            foreach ($modifications_triees as $nom_champ => $valeur) {

                if (isset($modifications_occurrence[$nom_champ]) && $valeur !== $modifications_occurrence[$nom_champ])
                    $occurrence_formatee[$nom_champ] = $modifications_occurrence[$nom_champ];
            }

            $occurrence_formatee['date_de_debut'] = $modifications_occurrence['date_de_debut'];
            $occurrence_formatee['date_de_fin'] = $modifications_occurrence['date_de_fin'];

            $this->occurrences_formatees[$occurrence->id] =  $occurrence_formatee;
        }
    }

    private function traitement_donnees_recurrence($rdv){

        $regles = array();
        $exceptions = array();
        $infos_recurrence = array();

        foreach($rdv->recurrence as $index => $regle_recurrence){

            if(strpos($regle_recurrence, 'RRULE') !== false) {

                list($type_regle, $regle) = explode(':', $regle_recurrence);
                $parties = explode(';', $regle);

                foreach ($parties as $partie) {

                    list($nom_parametre, $valeur) = explode('=', $partie, 2);
                    $regles[$type_regle][$nom_parametre] = $valeur;
                }
            }
            else if(strpos($regle_recurrence, 'EXDATE') !== false) {

                $valeur = explode('=', $regle_recurrence)[1];
                list($type_valeur, $date) = explode(':', $valeur);

                $this->exceptions_recurrence[$rdv->id][] = date('Y-m-d H:i:s', strtotime($date));
            }
        }

        $infos_recurrence['date_de_debut'] = date('Y-m-d', strtotime($rdv->start->dateTime ?? $rdv->start->date));

        if(isset($regles['RRULE']['UNTIL']))
            $infos_recurrence['date_de_fin'] = date('Y-m-d', strtotime($regles['RRULE']['UNTIL']));

        if(isset($regles['RRULE']['INTERVAL']))
            $infos_recurrence['frequence'] = $regles['RRULE']['INTERVAL'];
        else
            $infos_recurrence['frequence'] = 1;

        $infos_recurrence['type_frequence'] = $this->correspondance_frequence_google[$regles['RRULE']['FREQ']];

        if($regles['RRULE']['FREQ'] == 'WEEKLY'){

            if(isset($regles['RRULE']['BYDAY']))
                $jours_concernes = explode(',', $regles['RRULE']['BYDAY']);
            else
                $jours_concernes = [array_search(date_create_from_format('Y-m-d', $infos_recurrence['date_de_debut'])->format('N'), Variables::$correspondance_jour_google)];

            $infos_recurrence['jours_concernes'] = array();
            $correspondance_jour_google = Variables::$correspondance_jour_google;

            foreach($jours_concernes as $jour){

                $infos_recurrence['jours_concernes'][] = $correspondance_jour_google[$jour];
            }

            sort($infos_recurrence['jours_concernes']);
        }
        else if(in_array($regles['RRULE']['FREQ'], ['MONTHLY', 'YEARLY'])) {

            if(isset($regles['RRULE']['BYDAY'])) {

                preg_match('/-?\d+/', $regles['RRULE']['BYDAY'], $correspondances);
                $infos_recurrence['frequence_jour_concerne'] = $correspondances[0] == -1 ? 5 : $correspondances[0];
            }
            else
                $infos_recurrence['frequence_jour_concerne'] = 0;
        }

        return $infos_recurrence;
    }

    public function recuperer_occurrences_recurrence($id_google, $date_debut, $date_fin, $utilisateur){

        $this->instancie_service_calendrier($utilisateur);

        if(strtotime($date_debut) < strtotime($this->date_debut_synchro))
            $date_debut = $this->date_debut_synchro;

        try{

            $retour = $this->calendar_service->events->instances('primary', $id_google, ['timeMin' => $date_debut, 'timeMax' => $date_fin]);
        } catch(\Throwable $e){

            Log::warning("Échec de la récupération des occurrences de l'événement ".$id_google." pour l'utilisateur " . $utilisateur->id . "(email : ". $utilisateur->email . "). Message d'erreur : " . $e->getMessage() . " Stacktrace : " . $e->getTraceAsString());
            return traduction('messages.php.google_calendrier.erreur_recuperation_occurrences');
        }

        $occurrences_google = $retour->items;

        //S'il y a un nextlink dans le tableau, c'est qu'il reste des modifs à récupérer
        while(!empty($retour->nextPageToken)) {

            try{

                $retour = $this->calendar_service->events->listEvents('primary', ['pageToken' => $retour->nextPageToken]);

                $occurrences_google = array_merge($occurrences_google, $retour->items);
            } catch(\Throwable $e){

                Log::warning("Échec de la récupération des occurrences avec le pageToken pour l'utilisateur " . $utilisateur->id . "(email : ". $utilisateur->email . "). Message d'erreur : " . $e->getMessage() . " Stacktrace : " . $e->getTraceAsString());
                return traduction('messages.php.google_calendrier.erreur_recuperation_occurrences');
            }
        }

        return $occurrences_google;
    }

    public function recuperer_categories_possibles($nom_valeur, $nom_cle){

        return $categories_possibles = Champs_liste_formatee::select($nom_valeur, $nom_cle)
            ->where('id_liste_choix', 504)
            ->where(function($where){
                $where->where('desactivee', 0)
                    ->orWhereNull('desactivee');
            })
            ->get()
            ->pluck($nom_valeur, $nom_cle)
            ->toArray();
    }

    /**
     *
     *  Modifie un événement dans le calendrier d'un autre utilisateur si son id est spécifié sinon il est modifié dans le calendrier de l'utilisateur actuellement connecté
     *
     */
    public function modifier_affectation_evenement($infos, $utilisateur, $utilisateur_avant){

        //On supprime la tâche du calendrier de l'ancien affecté
        $retour = $this->supprimer_evenement($infos['id'], $utilisateur_avant);

        if($retour !== true)
            return $retour;

        //On crée la tâche dans le calendrier de l'utilisateur affecté
        return $this->creer_evenement($infos, $utilisateur);
    }

    private function synchronise_recurrences_sans_fin(){

        $prochaine_date_synchro = parametre_cron('prochaine_date_synchro_occurrences', id_cron, false);

        if(isset($prochaine_date_synchro) && strtotime('now') <= strtotime($prochaine_date_synchro))
            return true;

        $recurrences = modele('tache_recurrence')
            ->where(function($r){ 
                $r->where('date_de_fin', '>', date('Y-m-d'))->orWhereNull('date_de_fin'); 
            })
            ->get();

        $ids_taches_parent = $recurrences->pluck('tache_parent_id')->toArray();
        $ids_utilisateurs = $this->utilisateurs_synchronises->pluck('id')->toArray();

        $dernieres_taches = modele('tache')
            ->select('parent_id', DB::raw('MAX(date_de_fin) as date_de_fin'))
            ->whereIn('parent_id', $ids_taches_parent)
            ->whereIn('affectation', $ids_utilisateurs)
            ->groupBy('parent_id')
            ->get();

        $ids_google_existants = modele('tache')
            ->select('parent_id', 'id_google', 'id')
            ->whereIn('parent_id', $ids_taches_parent)
            ->whereIn('affectation', $ids_utilisateurs)
            ->get()
            ->groupBy('parent_id');

        $taches_parent = modele('tache')
            ->avec_parents()
            ->whereIn('id', $ids_taches_parent)
            ->whereIn('affectation', $ids_utilisateurs)
            ->get();

        $management_tache = management('tache');
        $management_tache->fonction_a_eviter = ['verifie_champs_obligatoires'];

        foreach($recurrences as $recurrence){

            $derniere_tache = $dernieres_taches->where('parent_id', $recurrence->tache_parent_id)->first();
            
            $ids_google_existants_recurrence = isset($ids_google_existants[$recurrence->tache_parent_id]) ? 
                $ids_google_existants[$recurrence->tache_parent_id]->pluck('id', 'id_google')->toArray() : array();

            $tache_parent = $taches_parent->where('id', $recurrence->tache_parent_id)->first();

            if(empty($tache_parent->id_google))
                continue;

            $utilisateur = $this->utilisateurs_synchronises[$tache_parent->affectation];

            $date_de_debut_recurrence = new \DateTime($derniere_tache->date_de_fin ?? $tache_parent->date_de_debut, new \DateTimeZone('Europe/Paris'));
            $date_de_fin_recurrence = new \DateTime(date('Y-m-d', $recurrence->date_de_fin) . ' 23:59:59', new \DateTimeZone('Europe/Paris'));
            $date_de_fin_max = date_create_from_format('Y-m-d H:i:s', date('Y-m-d 23:59:59', strtotime('now +' . fonctionnalite('duree_max_creation_recurrence') . ' months')));
            
            if(empty($date_de_fin_recurrence) || $date_de_fin_recurrence->getTimestamp() > $date_de_fin_max->getTimestamp())
                $date_de_fin_recurrence = $date_de_fin_max;

            //Si la date de fin calculée est inférieure à la date de début de la dernière tâche, il n'y a rien à créer
            if($date_de_fin_recurrence->getTimestamp() < $date_de_debut_recurrence->getTimestamp())
                continue;

            $date_de_debut_recurrence = $date_de_debut_recurrence->format('Y-m-d\TH:i:sP');
            $date_de_fin_recurrence = $date_de_fin_recurrence->format('Y-m-d\TH:i:sP');

            $occurrences_google = $this->recuperer_occurrences_recurrence($tache_parent->id_google, $date_de_debut_recurrence, $date_de_fin_recurrence, $utilisateur);

            if(gettype($occurrences_google) === 'string')
                throw new Eden_exception($occurrences_google, 500);

            $modifications_triees = service('recurrence')->formate_donnees($tache_parent->toArray());
            $modifications_triees['parent_id'] = $recurrence->tache_parent_id;

            foreach($occurrences_google as $occurrence){

                if(!empty($occurrence->status) && $occurrence->status === "cancelled"){

                    if(isset($ids_google_existants_recurrence[$occurrence->id]))
                        $retour = management('tache', $ids_google_existants_recurrence[$occurrence->id])->supprime();

                    continue;
                }
                else if(isset($ids_google_existants_recurrence[$occurrence->id]))
                    continue;

                try{

                    $management_tache->modele = null;
                    $this->enregistrer_occurrence($occurrence, $management_tache, $modifications_triees);
                }catch(\Throwable $e){

                    Log::warning("Échec de l'enregistrement de l'occurrence pour l'utilisateur " . $utilisateur->id . "(email : ". $utilisateur->email . "). RDV Google : " . json_encode($occurrence) . " Message d'erreur : " . $e->getMessage() . " Stacktrace : " . $e->getTraceAsString());

                    $modifications = [
                        'message_erreur' => $e->getMessage(),
                        'stacktrace_erreur' => $e->getTraceAsString(),
                        'modele_rdv' => json_encode($occurrence),
                        'synchro_externe' => 2,
                    ];

                    $rdv_echoue = isset($this->rdvs_echoues_utilisateur) ? $this->rdvs_echoues_utilisateur->get($occurrence->id) : null;

                    if(isset($rdv_echoue))
                        management('tache_erreurs_synchro_externe',$rdv_echoue->id, $rdv_echoue)->enregistre($modifications);
                    else
                        management('tache_erreurs_synchro_externe')->enregistre(array_merge($modifications,[
                            'id_externe' => $occurrence->id,
                            'utilisateur' => $utilisateur->id,
                        ]));

                    continue;
                }
            }
        }

        parametre_cron('prochaine_date_synchro_occurrences', id_cron, date('Y-m-d', strtotime('now +' . fonctionnalite('frequence_creation_occurrences_recurrence') . 'months')));

        return true;
    }

    /**
     *
     *  Synchronise les événements du calendrier Google vers Eden en récupérant toutes les modifications pour les rendez-vous des derniers mois
     *
     */
    public function synchronise_evenements_echoues(){

        $this->utilisateurs_synchronises = modele('utilisateur')->whereNotNull('access_token_google')->where('synchronisation_calendrier_google', 1)->get()->keyBy('id');
        $id_cron_synchro = modele('cron')->where('nom', 'synchroniser_rdv_google')->value('id');
        $rdv_echoues = modele('tache_erreurs_synchro_externe')
            ->where('synchro_externe', 2)
            ->whereIn('utilisateur', $this->utilisateurs_synchronises->pluck('id')->toArray())
            ->get();

        foreach($rdv_echoues->pluck('utilisateur')->toArray() as $id_utilisateur){

            $utilisateur = $this->utilisateurs_synchronises[$id_utilisateur];
            $rdvs_utilisateur = $rdv_echoues->where('utilisateur', $utilisateur->id);
            $this->rdvs_echoues_utilisateur = $rdvs_utilisateur->keyBy('id_externe');
            $rdvs_google = array();

            foreach($rdvs_utilisateur as $rdv){

                try {

                    $rdv_google = $this->recuperer_evenement($rdv->id_externe, $utilisateur);
                    $rdvs_google[] = $rdv_google;
                } catch(\Throwable $e){

                    //Si c'est une 404, le rdv n'existe plus, on supprime la ligne
                    if($e->getCode() == 404)
                        management('tache_erreurs_synchro_externe', $rdv->id, $rdv)->supprime();

                    continue;
                }
            }

            if(!empty($rdvs_google)) {

                $this->date_debut_synchro = parametre_cron('debut_synchro_google', $id_cron_synchro, false, $utilisateur->id);
                $this->enregistrer_evenements($rdvs_google, $utilisateur);
            }
        }

        return true;
    }

    public function lier_evenements_eden($modele_eden, $infos_recurrence, $utilisateur, $exceptions_a_recreer){

        $date_de_debut = new \DateTime($infos_recurrence['date_de_debut'], new \DateTimeZone('Europe/Paris'));
        $date_de_debut = $date_de_debut->format('Y-m-d\TH:i:sP');

        if(isset($infos_recurrence['date_de_fin'])) {

            $date_de_fin = new \DateTime($infos_recurrence['date_de_fin'] . ' 23:59:59', new \DateTimeZone('Europe/Paris'));
            $date_de_fin = $date_de_fin->format('Y-m-d\TH:i:sP');
        }
        else
            $date_de_fin = date_create_from_format(
        'Y-m-d H:i:s',
        strtotime('+ ' . fonctionnalite('duree_max_creation_recurrence') . 'months') . ' 23:59:59',
        new \DateTimeZone('Europe/Paris')
            )->format('Y-m-d\TH:i:sP');
        
        $occurrences_google = $this->recuperer_occurrences_recurrence($modele_eden->id_google, $date_de_debut, $date_de_fin, $utilisateur);

        if(is_string($occurrences_google))
            return $occurrences_google;
        
        $occurrences_eden = modele('tache')->where('parent_id', $modele_eden->id)->get();
        $occurrences_google_par_date = array();
        $exceptions_a_modifier = array();

        $management_tache = management('tache');
        
        foreach($occurrences_google as $occurrence){

            if(isset($occurrence->start->dateTime)) {

                $date_debut_utc = new \DateTime($occurrence->start->dateTime, new \DateTimeZone($occurrence->start->timeZone));
                $date_debut_locale = $date_debut_utc->setTimezone(new \DateTimeZone('Europe/Paris'))->format('Y-m-d');
            } 
            else {

                $date_debut_utc = new \DateTime($occurrence->start->date);
                $date_debut_locale = $date_debut_utc->format('Y-m-d');
            }

            $occurrences_google_par_date[$date_debut_locale] = ['id_google' => $occurrence->id, 'id_commun_taches_participants' => $occurrence->iCalUID];
        }

        foreach($occurrences_eden as $occurrence){

            $date_de_debut = date_create_from_format('Y-m-d H:i:s', $occurrence->date_de_debut)->format('Y-m-d');
            $occurrence_google = $occurrences_google_par_date[$date_de_debut];

            if(isset($exceptions_a_recreer[$date_de_debut])){

                $exceptions_a_modifier[] = [
                    'infos' => $management_tache->infos_evenement_google($exceptions_a_recreer[$date_de_debut], $utilisateur),
                    'id_google' => $occurrence_google['id_google'],
                ];
            }

            management('tache', $occurrence->id, $occurrence)->enregistre_modele($occurrence_google);
            unset($occurrences_google_par_date[$date_de_debut]);
        }

        if(!empty($exceptions_a_modifier)){

            foreach($exceptions_a_modifier as $exception){

                $this->modifier_evenement($exception['infos'], $exception['id_google'], $utilisateur);
            }
        }

        foreach($occurrences_google_par_date as $date => $infos)
            $this->supprimer_evenement($infos['id_google'], $utilisateur);

        return true;
    }

    /*
     *
     * Fait un appel API pour essayer de récupérer des événements sur une très courte période pour vérifier 
     * si on a les droits suffisants pour récupérer, créer, modifier ou supprimer des événements
     * 
     */
    public function test_droits_api($utilisateur) {

        $this->instancie_service_calendrier($utilisateur);

        try{

            $this->calendar_service->events->listEvents('primary', ['maxResults' => 1]);
        } catch(\Throwable $e){

            Log::warning("Échec de la création de l'événement pour l'utilisateur " . $utilisateur->id . "(email : ". $utilisateur->email . "). Message d'erreur : " . $e->getMessage() . " Stacktrace : " . $e->getTraceAsString());
            return traduction('messages.php.google_calendrier.erreur_droits_api');
        }

        return true;
    }

    protected function lier_evenements_participants(){

        $management_tache = management('tache');
        
        //ajouter les tâches des participants déjà synchronisées et n'ayant pas d'id_tache_organisateur pour chaque groupe
        foreach($this->rdv_participants as $id_commun_taches_participants => &$groupe_participants){

            $taches_organisateur = $groupe_participants['organisateur'] ?? array();
            $modifications = ['id_commun_taches_participants' => $id_commun_taches_participants];
            $participants_supplémentaires = modele('tache')
                ->where('id_commun_taches_participants', $id_commun_taches_participants)
                ->whereNotIn('affectation', array_keys($groupe_participants))
                ->whereNull('id_tache_organisateur')
                ->where('participant', 1)
                ->get()
                ->groupBy('affectation')
                ->map(fn($g) => $g->all())
                ->all();

            if(!empty($participants_supplémentaires))
                $groupe_participants = array_merge($groupe_participants, $participants_supplémentaires);

            if(empty($taches_organisateur)){
                $taches_organisateur = modele('tache')
                    ->where('id_commun_taches_participants', $id_commun_taches_participants)
                    ->whereNull('id_tache_organisateur')
                    ->whereNull('participant')
                    ->get()
                    ->keyBy(function($tache){
                    
                        if($tache->tache_parent)
                            return 'parent';

                        return date_create_from_format('Y-m-d H:i:s', $tache->date_de_debut)->format('Y-m-d');
                    })->all();
                
                $groupe_participants['organisateur'] = $taches_organisateur;
            }

            // On continue même sans tâche organisateur, pour remplir l'id commun sur les tâches des participants au cas où la tâche de l'organisateur
            // sera synchronisée un jour (cela peut arriver si la synchro n'était pas activée pour l'organisateur et qu'on l'active)
            foreach($groupe_participants as $groupe_rdv){

                foreach($groupe_rdv as $date_de_debut => $rdv_a_lier){

                    if($rdv_a_lier->participant && isset($groupe_participants['organisateur'][$date_de_debut]))
                        $modifications['id_tache_organisateur'] = $groupe_participants['organisateur'][$date_de_debut]->id;
                    else if(!isset($groupe_participants['organisateur'][$date_de_debut]) && isset($modifications['id_tache_organisateur']))
                        unset($modifications['id_tache_organisateur']);

                    $management_tache->modele = $rdv_a_lier;
                    $management_tache->enregistre_modele($modifications);
                }
            }
        }
        return true;
    }
}

	
	