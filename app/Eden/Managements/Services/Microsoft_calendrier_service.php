<?php

namespace App\Eden\Managements\Services;

use App\Eden\Exceptions\Eden_exception;
use App\Eden\Variables;
use Illuminate\Support\Arr;
use Microsoft\Graph\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class Microsoft_calendrier_service {

    private $graph = null;
    private $rdvs_echoues_utilisateur = null;
    private $verifications_existence_rdv;
    private $date_debut_delta;
    private $occurrences_formatees;
    protected $utilisateurs_synchronises;
    protected $rdv_participants;
    protected $participants_avant;

    public function __construct(){

        $this->graph = service('microsoft_authentification')->instancie_graph_application();
    }

	/**
	 *
	 * Crée un événement dans le calendrier d'un autre utilisateur si son id est spécifié sinon il est créé dans le calendrier de l'utilisateur actuellement connecté
	 *
	 */
	public function creer_evenement($infos, $utilisateur){

		if(empty($utilisateur->id_microsoft))
			return false;

		// Crée un événement à partir du tableau $infos
		$nouvel_evenement = $this->prepare_donnees_pour_evenement($infos, $utilisateur);
        
        try {

            // Envoie la requête pour créer l'événement dans le calendrier
            $reponse = $this->graph->createRequest('POST', '/users/'.$utilisateur->id_microsoft.'/calendar/events')
                ->attachBody($nouvel_evenement)
                ->setReturnType(Model\Event::class)
                ->execute();
        } catch(\Throwable $e){

            Log::warning("Échec de la création de l'événement ". $infos['sujet'] ." pour l'utilisateur " . $utilisateur->id . "(id_microsoft : ". $utilisateur->id_microsoft . "). Message d'erreur : " . $e->getMessage() . " Stacktrace : " . $e->getTraceAsString() . " Données envoyées : " . json_encode($nouvel_evenement));
            
            return traduction('messages.php.microsoft_calendrier.erreur_creation');
        }

		return $reponse;
	}

	/**
	 *
	 *  Modifie un événement dans le calendrier d'un autre utilisateur si son id est spécifié sinon il est modifié dans le calendrier de l'utilisateur actuellement connecté
	 *
	 */
	public function modifier_evenement($infos, $id, $utilisateur){

        if(empty($utilisateur->id_microsoft))
            return false;

		//Crée un événement à partir du tableau $infos
		$nouvel_evenement = $this->prepare_donnees_pour_evenement($infos, $utilisateur);

        try {

            // Envoie la requête pour modifier l'événement dans le calendrier
            $reponse = $this->graph->createRequest('PATCH', '/users/'.$utilisateur->id_microsoft.'/calendar/events/'.$id)
                ->attachBody($nouvel_evenement)
                ->setReturnType(Model\Event::class)
                ->execute();
        } catch(\Throwable $e){

            Log::warning("Échec de la modification de l'événement ". $infos['sujet'] ." (id microsoft: '. $id .') pour l'utilisateur " . $utilisateur->id . "(id_microsoft : ". $utilisateur->id_microsoft . "). Message d'erreur : " . $e->getMessage() . " Stacktrace : " . $e->getTraceAsString());
            return traduction('messages.php.microsoft_calendrier.erreur_modification');
        }

        return $reponse;
	}

    /**
     *
     *  Modifie un événement dans le calendrier d'un autre utilisateur si son id est spécifié sinon il est modifié dans le calendrier de l'utilisateur actuellement connecté
     *
     */
    public function modifier_affectation_evenement($infos, $createur_tache, $ancienne_affectation){

        //On supprime la tâche du calendrier de l'ancien affecté
        $this->supprimer_evenement($infos['id'], $ancienne_affectation);

        //On crée la tâche dans le calendrier de l'utilisateur affecté
        $creation_nouvel_affecte = $this->creer_evenement($infos, $createur_tache);

        return $creation_nouvel_affecte;
    }

	/**
	*
	*  Supprime un événement dans le calendrier d'un autre utilisateur si son id est spécifié sinon il est supprimé dans le calendrier de l'utilisateur actuellement connecté
	*
	*/
	public function supprimer_evenement($id, $utilisateur){

        if(empty($utilisateur->id_microsoft))
            return false;

        try{

            // Envoie la requête pour supprimer l'événement dans le calendrier
            $reponse = $this->graph->createRequest('DELETE', '/users/'.$utilisateur->id_microsoft.'/calendar/events/'.$id)
                ->setReturnType(Model\Event::class)
                ->execute();
        } catch(\Throwable $e){

            Log::warning("Échec de la suppression de l'événement (id_microsoft : ". $id .") pour l'utilisateur " . $utilisateur->id . "(id_microsoft : ". $utilisateur->id_microsoft . "). Message d'erreur : " . $e->getMessage() . " Stacktrace : " . $e->getTraceAsString());
            return traduction('messages.php.microsoft_calendrier.erreur_suppression');
        }

        return $reponse;
	}

    /*
     *
     * Supprime les doublons entre les dates passées en paramètres.
     * Lors de la synchro des rendez-vous avec le delta, il y a parfois des doublons qui se créent (même contenu, sujet, dates, mais pas le même id microsoft).
     * Le seul moyen viable de les détecter est d'essayer de les récupérer via l'api, et s'il y a une erreur, on supprime la tâche dans Eden.
     *
     */
    public function supprimer_doublons($debut, $fin){

        $taches = modele('tache')
            ->join('utilisateur', 'tache.affectation', '=', 'utilisateur.id')
            ->select('tache.*', 'utilisateur.id_microsoft as id_microsoft_utilisateur')
            ->whereNotNull('tache.id_microsoft')
            ->whereNotNull('tache.type_tache_rdv')
            ->where('tache.date_de_debut', '>=', $debut)
            ->where('tache.date_de_fin', '<=', $fin)
            ->whereNotNull('utilisateur.id_microsoft')
            ->where('utilisateur.synchronisation_calendrier_outlook', 1)
            ->get();

        foreach($taches as $tache){

            try {

                $rdv = $this->graph->createRequest("GET", '/users/'.$tache->id_microsoft_utilisateur.'/calendar/events/'.$tache->id_microsoft)
                    ->setReturnType(Model\Event::class)
                    ->execute()
                    ->getBody();

            } catch(\Throwable $erreur){

                management('tache', $tache->id, $tache)->supprime();
                Log::notice('[Suppression doublons rdv Office] La tâche ' . $tache->id . ' (ID microsoft : ' . $tache->id_microsoft .
                    ') a été supprimée car elle n\'existe plus dans le calendrier de l\'utilisateur ' . $tache->affectation .
                    ' (ID microsoft : ' . $tache->id_microsoft_utilisateur . ').');
            }
        }
    }

    /**
     *
     *  Synchronise les événements du calendrier Office vers Eden en récupérant toutes les modifications pour les rendez-vous des derniers mois
     *
     */
    public function synchronise_evenements(){

        $this->utilisateurs_synchronises = modele('utilisateur')->whereNotNull('id_microsoft')->where('synchronisation_calendrier_outlook', 1)->orderBy('date_derniere_synchro_rdv_microsoft')->get()->keyBy('id');
        $this->rdv_participants = array();

        //On récupère le calendrier de chaque utilisateur
        foreach($this->utilisateurs_synchronises as $utilisateur){

            $reinitialisation_delta = strtotime(parametre_cron('date_reinitialisation_delta_office', id_cron, false, $utilisateur->id));
            $nombre_mois = fonctionnalite('microsoft_nombre_mois');

            //Si le delta n'est pas initialisé ou si la date à laquelle il faut le réinitialiser est passée, on l'initialise sinon on utilise le delta token
            if(empty(parametre_cron('delta_token_office', id_cron, false, $utilisateur->id)) ||
                (!empty($reinitialisation_delta) && strtotime('now') > $reinitialisation_delta)){

                $rafraichissement_token = fonctionnalite('microsoft_rafraichissement_delta_token_avant_fin');
                $this->date_debut_delta = date('Y-m-d', strtotime('- ' . $nombre_mois . ' months')) . ' 00:00:00';
                $date_de_fin_delta = date('Y-m-d', strtotime('+ ' . $nombre_mois . ' months')) . ' 23:59:59';
                $date_reinitialisation_delta = date('Y-m-d H:i:s', strtotime('now + ' . $nombre_mois . ' months - ' . $rafraichissement_token . ' days'));

                // Envoie la requête pour récupérer les modifications du calendrier dans l'intervalle de temps précisé
                try{

                    $delta = $this->graph->createRequest("GET", "/users/".$utilisateur->id_microsoft."/calendarView/delta?startDateTime=" . $this->date_debut_delta . "&endDateTime=" . $date_de_fin_delta)
                        ->addHeaders(['Prefer' => 'odata.maxpagesize=100'])
                        ->execute()
                        ->getBody();

                    parametre_cron('date_initialisation_delta_office', id_cron, date('Y-m-d H:i:s'), $utilisateur->id);
                    parametre_cron('date_reinitialisation_delta_office', id_cron, $date_reinitialisation_delta, $utilisateur->id);

                } catch(\Throwable $e){

                    Log::warning("Échec de la création du delta pour l'utilisateur " . $utilisateur->id . "(id_microsoft : ". $utilisateur->id_microsoft . "). Message d'erreur : " . $e->getMessage() . " Stacktrace : " . $e->getTraceAsString());
                    continue;
                }
            } else {

                $date_initialisation_delta = parametre_cron('date_initialisation_delta_office', id_cron, false, $utilisateur->id);

                $this->date_debut_delta = date_create_from_format('Y-m-d H:i:s', $date_initialisation_delta)
                    ->modify('- ' . $nombre_mois . ' months')
                    ->format('Y-m-d 00:00:00');

                // Envoie la requête pour récupérer les modifications du calendrier dans l'intervalle de temps précisé
                try{

                    $delta = $this->graph->createRequest("GET", "/users/".$utilisateur->id_microsoft."/calendarView/delta?\$deltatoken=". parametre_cron('delta_token_office', id_cron, false, $utilisateur->id))
                        ->addHeaders(['Prefer' => 'odata.maxpagesize=100'])
                        ->execute()
                        ->getBody();
                } catch(\Throwable $e){

                    Log::warning("Échec de la récupération des modifications avec le delta_token pour l'utilisateur " . $utilisateur->id . "(id_microsoft : ". $utilisateur->id_microsoft . "). Message d'erreur : " . $e->getMessage() . " Stacktrace : " . $e->getTraceAsString());
                    continue;
                }
            }

            $rdv_microsoft = $delta['value'];

            //S'il y a un nextlink dans le tableau, c'est qu'il reste des modifs à récupérer
            while(!empty($delta['@odata.nextLink'])) {

                $skip_token = explode('skiptoken=', $delta['@odata.nextLink'])[1];

                try{

                    $delta = $this->graph->createRequest("GET", "/users/" . $utilisateur->id_microsoft . "/calendarView/delta?\$skiptoken=" . $skip_token)
                        ->addHeaders(['Prefer' => 'odata.maxpagesize=100'])
                        ->execute()
                        ->getBody();

                    $rdv_microsoft = array_merge($rdv_microsoft,$delta['value']);
                } catch(\Throwable $e){
                    Log::warning("Échec de la récupération des modifications avec le skip_token pour l'utilisateur " . $utilisateur->id . "(id_microsoft : ". $utilisateur->id_microsoft . "). Message d'erreur : " . $e->getMessage() . " Stacktrace : " . $e->getTraceAsString());
                    continue 2;
                }
            }

            $retour = $this->enregistrer_evenements($rdv_microsoft, $utilisateur);
            
            if($retour === false)
                continue;
            
            $delta_token = explode('deltatoken=', $delta['@odata.deltaLink'])[1];

            parametre_cron('delta_token_office', id_cron, $delta_token, $utilisateur->id);
            management('utilisateur', $utilisateur->id, $utilisateur)->enregistre_modele(['date_derniere_synchro_rdv_microsoft' => date('Y-m-d H:i:s')]);
        }

        if(!empty($this->rdv_participants))
            $this->enregistrer_evenements_avec_participants();

        $this->synchronise_recurrences_sans_fin();

        return true;
    }

    /**
     *
     *  Synchronise les événements du calendrier Office vers Eden en récupérant toutes les modifications pour les rendez-vous des derniers mois
     *
     */
    public function synchronise_evenements_echoues(){

        $this->utilisateurs_synchronises = modele('utilisateur')->whereNotNull('id_microsoft')->where('synchronisation_calendrier_outlook', 1)->get()->keyBy('id');
        $rdv_echoues = modele('tache_erreurs_synchro_externe')->whereIn('utilisateur', $this->utilisateurs_synchronises->pluck('id')->toArray())->get();

        foreach($rdv_echoues->pluck('utilisateur')->toArray() as $id_utilisateur){

            $utilisateur = $this->utilisateurs_synchronises[$id_utilisateur];
            $rdvs_utilisateur = $rdv_echoues->where('utilisateur', $utilisateur->id);
            $this->rdvs_echoues_utilisateur = $rdvs_utilisateur->keyBy('id_externe');
            $rdvs_microsoft = array();

            foreach($rdvs_utilisateur as $rdv){

                $rdv_microsoft = $this->recuperer_evenement($rdv->id_externe, $utilisateur);

                if($rdv_microsoft instanceof \Throwable && $rdv_microsoft->getCode() === 404) {

                    management('tache_erreurs_synchro_externe', $rdv->id, $rdv)->supprime();
                    continue;
                }

                $rdvs_microsoft[] = $rdv_microsoft;
            }

            if(!empty($rdvs_microsoft))
                $this->enregistrer_evenements($rdvs_microsoft, $utilisateur);
        }

        return true;
    }

    private function synchronise_recurrences_sans_fin(){

        if(strtotime('now') <= strtotime(parametre_cron('prochaine_date_synchro_occurrences', id_cron, false)))
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

        $ids_microsoft_existants = modele('tache')
            ->select('parent_id', 'id_microsoft')
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
            $ids_microsoft_existants_recurrence = isset($ids_microsoft_existants[$recurrence->tache_parent_id]) ?
                $ids_microsoft_existants[$recurrence->tache_parent_id]->pluck('id_microsoft')->toArray() : array();
            $tache_parent = $taches_parent->where('id', $recurrence->tache_parent_id)->first();

            if(empty($tache_parent->id_microsoft))
                continue;

            $utilisateur = $this->utilisateurs_synchronises[$tache_parent->affectation];

            $date_de_debut = date_create_from_format('Y-m-d H:i:s', $derniere_tache->date_de_fin ?? $tache_parent->date_de_debut);
            $date_de_fin = date_create_from_format('Y-m-d H:i:s', $recurrence->date_de_fin . ' 23:59:59');
            $date_de_fin_max = date_create_from_format('Y-m-d H:i:s', date('Y-m-d 23:59:59', strtotime('now +' . fonctionnalite('duree_max_creation_recurrence') . ' months')));

            if(empty($date_de_fin) || $date_de_fin->getTimestamp() > $date_de_fin_max->getTimestamp())
                $date_de_fin = $date_de_fin_max;

            //Si la date de fin calculée est inférieure à la date de début de la dernière tâche, il n'y a rien à créer
            if($date_de_fin->getTimestamp() < $date_de_debut->getTimestamp())
                continue;

            $occurrences_microsoft = $this->recuperer_occurrences_recurrence($tache_parent->id_microsoft, $date_de_debut->format('Y-m-d H:i:s'), $date_de_fin->format('Y-m-d H:i:s'), $utilisateur);

            if(gettype($occurrences_microsoft) === 'string')
                continue;

            $modifications_triees = service('recurrence')->formate_donnees($tache_parent->toArray());
            $modifications_triees['parent_id'] = $recurrence->tache_parent_id;

            foreach($occurrences_microsoft as $occurrence){

                if(in_array($occurrence['id'], $ids_microsoft_existants_recurrence))
                    continue;

                try{

                    $management_tache->modele = null;
                    $this->enregistrer_occurrence($occurrence, $management_tache, $modifications_triees);
                }catch(\Throwable $e){

                    Log::warning("Échec de l'enregistrement de l'occurrence pour l'utilisateur " . $utilisateur->id . "(email : ". $utilisateur->email . "). RDV Microsoft : " . json_encode($occurrence) . " Message d'erreur : " . $e->getMessage() . " Stacktrace : " . $e->getTraceAsString());

                    $modifications = [
                        'message_erreur' => $e->getMessage(),
                        'stacktrace_erreur' => $e->getTraceAsString(),
                        'modele_rdv' => json_encode($occurrence),
                        'synchro_externe' => 1,
                    ];

                    $rdv_echoue = isset($this->rdvs_echoues_utilisateur) ? $this->rdvs_echoues_utilisateur->get($occurrence['id']) : null;

                    if(isset($rdv_echoue))
                        management('tache_erreurs_synchro_externe',$rdv_echoue->id, $rdv_echoue)->enregistre($modifications);
                    else
                        management('tache_erreurs_synchro_externe')->enregistre(array_merge($modifications,[
                            'id_externe' => $occurrence['id'],
                            'utilisateur' => $utilisateur->id,
                        ]));

                    continue;
                }
            }
        }

        parametre_cron('prochaine_date_synchro_occurrences', id_cron, date('Y-m-d', strtotime('now +' . fonctionnalite('frequence_creation_occurrences_recurrence') . 'months')));

        return true;
    }

    private function enregistrer_occurrence($occurrence, $management, $modifications){

        if (isset($occurrence['start']['dateTime'])) {

            $date_debut_utc = new \DateTime($occurrence['start']['dateTime'], new \DateTimeZone($occurrence['start']['timeZone']));
            $date_debut_locale = $date_debut_utc->setTimezone(new \DateTimeZone('Europe/Paris'))->format('Y-m-d H:i:s');

            $date_fin_utc = new \DateTime($occurrence['end']['dateTime'], new \DateTimeZone($occurrence['end']['timeZone']));
            $date_fin_locale = $date_fin_utc->setTimezone(new \DateTimeZone('Europe/Paris'))->format('Y-m-d H:i:s');

            if (isset($modifications['journee_entiere']))
                unset($modifications['journee_entiere']);
        } else {

            $date_debut_utc = new \DateTime($occurrence['start']['date']);
            $date_debut_locale = $date_debut_utc->format('Y-m-d') . ' 00:00:00';

            $date_fin_utc = new \DateTime($occurrence['end']['date']);
            $date_fin_locale = $date_fin_utc->modify('-1 day')->format('Y-m-d') . ' 23:59:59';

            $modifications['journee_entiere'] = 1;
        }

        $modifications['date_de_debut'] = $date_debut_locale;
        $modifications['date_de_fin'] = $date_fin_locale;
        $modifications['id_microsoft'] =  $occurrence['id'];

        $retour = $management->enregistre($modifications);

        if($retour !== true)
            throw new Eden_exception($retour, 500);

        $this->verifications_existence_rdv->put($occurrence['id'], $management->modele);

        return true;
    }

    /**
     *
     *  Synchronise les événements du calendrier Eden vers Office qui n'ont pas encore été synchronisés dans la période définie
     *
     */
    public function synchronise_evenements_eden_sans_id(){

        $nombre_mois = fonctionnalite('microsoft_nombre_mois');
        $date_debut = date('Y-m-d', strtotime('- ' . $nombre_mois . ' months')) . 'T00:00:00';
        $date_fin = date('Y-m-d', strtotime('+ ' . $nombre_mois . ' months')) . 'T23:59:59';

        //On récupère les utilisateurs qui ont potentiellement des tâches à synchroniser
        $taches = modele('tache')
            ->join('utilisateur', 'tache.affectation', '=', 'utilisateur.id')
            ->select('utilisateur.email as utilisateur_email',
                'utilisateur.nom as utilisateur_nom',
                'utilisateur.prenom as utilisateur_prenom',
                'utilisateur.id_microsoft as utilisateur_id_microsoft',
                'utilisateur.id as utilisateur_id',
                'utilisateur.access_token_microsoft',
                'utilisateur.refresh_token_microsoft',
                'utilisateur.expiration_token_microsoft',
                'tache.*'
            )
            ->where('utilisateur.synchronisation_calendrier_outlook', 1)
            ->whereNotNull('utilisateur.id_microsoft')
            ->where('date_de_debut', '>', $date_debut)
            ->where('date_de_fin', '<', $date_fin)
            ->whereNotNull('type_tache_rdv')
            ->whereNull('tache.id_microsoft')
            ->get();

        foreach ($taches as $tache){

            $utilisateur = (object) [
                'access_token_microsoft' => $tache->access_token_microsoft,
                'refresh_token_microsoft' => $tache->refresh_token_microsoft,
                'expiration_token_microsoft' => $tache->expiration_token_microsoft,
                'id_microsoft' => $tache->utilisateur_id_microsoft,
                'id' => $tache->utilisateur_id,
            ];

            $management = management('tache', $tache->id, $tache);

            $infos = array(

                'date_debut' => $tache->date_de_debut,
                'date_fin' => $tache->date_de_fin,
                'sujet' => $tache->titre,
                'commentaire' => $tache->commentaire,
                'categorie' => $management->champ('type_tache_rdv')->recuperer_valeur(),
                'prive' => $tache->prive,
                'organisateur' => [
                    'emailAddress' => [
                        'address' => $tache->utilisateur_email,
                        'name' => $tache->utilisateur_prenom.' '.$tache->utilisateur_nom,
                    ]
                ],
            );

            $reponse = $this->creer_evenement($infos, $utilisateur);

            if(!empty($reponse))
                $management->enregistre_modele(array('id_microsoft' => $reponse->getId()));
        }

        return true;
    }

    /*
     *
     * Traite les données avant de procéder à l'enregistrement lors de la synchro
     *
     */
    private function enregistrer_evenements($rdv_microsoft, $utilisateur, $enregistrement_participants = false){

        try {
            //On regarde s'il existe déjà dans la bdd
            $this->verifications_existence_rdv = modele('tache')
                ->avec_inactifs()
                ->avec_parents()
                ->whereIn('id_microsoft', array_map(fn($rdv) => $rdv['id'], $rdv_microsoft))
                ->get()->keyBy('id_microsoft');
        }
        catch(\Throwable $e){
            $this->verifications_existence_rdv = modele('tache')
                ->avec_inactifs()
                ->avec_parents()
                ->whereNotNull('id_microsoft')
                ->get()->keyBy('id_microsoft');
        }

        //On vérifie chaque rdv pour savoir si on doit l'enregistrer
        foreach ($rdv_microsoft as $rdv) {
            
            if(isset($rdv['type']) && $rdv['type'] === 'occurrence')
                continue;

            if (!empty($rdv['@removed']) && !empty($this->verifications_existence_rdv[$rdv['id']]) && $this->verifications_existence_rdv[$rdv['id']]->inactif !== 1){

                //On vérifie si le rdv n'existe vraiment plus, car l'api renvoie aussi les rdv créés ou modifiés hors de la plage de dates comme supprimés
                //S'il n'est pas supprimé, on continue le déroulement classique avec le rdv récupéré pour reporter les éventuelles modifications
                $rdv_eden = $this->verifications_existence_rdv[$rdv['id']];
                $rdv = $this->recuperer_evenement($rdv_eden->id_microsoft, $utilisateur);
                
                if($rdv instanceof \Throwable && $rdv->getCode() === 404) {

                    management('tache', $rdv_eden->id, $rdv_eden)->supprime();
                    continue;
                }
            }
            else if(!empty($this->verifications_existence_rdv[$rdv['id']]) && $this->verifications_existence_rdv[$rdv['id']]->inactif === 1){

                $this->supprimer_evenement($rdv['id'], $utilisateur);
                continue;
            }
            else if(!empty($rdv['@removed']))
                continue;
            
            if((!$rdv['isOrganizer'] || !empty($rdv['attendees'])) && !$enregistrement_participants){

                $this->rdv_participants[$rdv['iCalUId']][$utilisateur->id][$rdv['type'] == 'seriesMaster' ? 'parent' : date_create($rdv['start']['dateTime'])->format('Y-m-d')] = $rdv;
                
                if($rdv['isOrganizer'])
                    $this->rdv_participants[$rdv['iCalUId']]['organisateur_id'] = $utilisateur->id;

                continue;
            }

            try{
                
                if(!empty($this->verifications_existence_rdv[$rdv['id']]))
                    $this->enregistrer_evenement($rdv, $utilisateur, $this->verifications_existence_rdv[$rdv['id']]);
                else
                    $this->enregistrer_evenement($rdv, $utilisateur);

                $rdv_echoue = isset($this->rdvs_echoues_utilisateur) ? $this->rdvs_echoues_utilisateur->get($rdv['id']) : null;

                if(isset($rdv_echoue))
                    management('tache_erreurs_synchro_externe',$rdv_echoue->id, $rdv_echoue)->supprime();
            } catch(\Throwable $e){

                Log::warning("Échec de l'enregistrement du rdv pour l'utilisateur " . $utilisateur->id . "(id_microsoft : ". $utilisateur->id_microsoft . "). RDV Microsoft : " . json_encode($rdv) . " Message d'erreur : " . $e->getMessage() . " Stacktrace : " . $e->getTraceAsString());

                $modifications = [
                    'message_erreur' => $e->getMessage(),
                    'stacktrace_erreur' => $e->getTraceAsString(),
                    'modele_rdv' => json_encode($rdv),
                    'synchro_externe' => 1,
                ];

                $rdv_echoue = isset($this->rdvs_echoues_utilisateur) ? $this->rdvs_echoues_utilisateur->get($rdv['id']) : null;

                if(isset($rdv_echoue))
                    management('tache_erreurs_synchro_externe',$rdv_echoue->id, $rdv_echoue)->enregistre($modifications);
                else
                    management('tache_erreurs_synchro_externe')->enregistre(array_merge($modifications,[
                        'id_externe' => $rdv['id'],
                        'utilisateur' => $utilisateur->id,
                    ]));

                continue;
            }
        }

        return true;
    }

    /*
     *
     * Formate les données du rdv reçu par Outlook, puis l'enregistre dans la bdd
     *
     */
    private function enregistrer_evenement($rdv, $utilisateur, $modele = null){

        if((!empty($rdv['attendees']) || $rdv['isOrganizer'] === false) && empty($modele)){
            
            $verification = modele('tache')
                ->avec_parents()
                ->where('id_commun_taches_participants', $rdv['iCalUId'])
                ->where('participant', 1)
                ->where('affectation', $utilisateur->id)
                ->whereDate('date_de_debut', date_create($rdv['start']['dateTime'])->format('Y-m-d'))
                ->first();

            if(isset($verification))
                $modele = $verification;
        }

        $modifications = $this->prepare_donnees_enregistrement_eden($rdv, $utilisateur, $modele);

        $management_tache = !empty($modele) ? management('tache', $modele->id, $modele) : management('tache');
        $management_tache->fonction_a_eviter = ['verifie_champs_obligatoires'];
        
        if(isset($management_tache->modele) && !$this->tache_modifiee($modifications, $management_tache->modele) === true)
            return;

        //Si un tableau des occurrences existe pour le rdv, on le crée en attribut du management tache
        if(!empty($this->occurrences_formatees))
            $management_tache->occurrences_personnalisees = $this->occurrences_formatees;

        $retour = $management_tache->enregistre($modifications);

        if(!isset($modele) && $retour === true) {
            
            $taches_creees = collect([$rdv['id'] => $management_tache->modele]);

            if(isset($rdv['recurrence']))
                $taches_creees = $taches_creees->union(
                    modele('tache')
                        ->where('parent_id', $management_tache->modele->id)
                        ->get()
                        ->keyBy('id_microsoft')
                );
            
            $this->verifications_existence_rdv = $this->verifications_existence_rdv->union($taches_creees);
        }
        elseif($retour !== true) {

            //Si on enregistrait une récurrence, on ne sait pas si ça a planté sur l'enregistrement de la tâche parent ou
            // sur la génération des occurrences, on doit donc supprimer les tâches potentiellement créées pour éviter
            // les doublons lors de la synchro des rdv en erreurs
            if(isset($rdv['recurrence'])) {

                DB::delete("DELETE FROM tache_recurrence where tache_parent_id in (SELECT id FROM tache where id_microsoft = '" . $rdv['id'] . "')");
                DB::delete("DELETE FROM tache where parent_id in (SELECT id FROM tache where id_microsoft = '" . $rdv['id'] . "') or id_microsoft = '" . $rdv['id'] . "'");
            }

            throw new Eden_exception($retour, 500);
        }
    }

    private function recuperer_evenement($id, $utilisateur){

        try {

            $rdv = $this->graph->createRequest('GET', '/users/'.$utilisateur->id_microsoft.'/calendar/events/' . $id)
                ->setReturnType(Model\Event::class)
                ->execute();
        } catch(\Throwable $erreur){

            Log::error('[Récupération événément Office] La récupération de l\'événement ' . $id . ' a échoué. Message d\'erreur : ' . $erreur->getMessage());
            return $erreur;
        }

        return $rdv->jsonSerialize();
    }

    public function recuperer_occurrences_recurrence($id_rdv_microsoft, $date_debut, $date_fin, $utilisateur){

        if(empty($utilisateur->id_microsoft))
            return false;

        if(!empty($this->date_debut_delta) && ($date_debut) < strtotime($this->date_debut_delta))
            $date_debut = $this->date_debut_delta;

        try {

            $reponse = $this->graph->createRequest('GET', '/users/'.$utilisateur->id_microsoft.'/calendar/events/' . $id_rdv_microsoft . '/instances?startDateTime='. $date_debut .'&endDateTime=' . $date_fin . '&$top=1000')
                ->execute()
                ->getBody();

            $occurrences = $reponse['value'];
        } catch(\Throwable $erreur){

            Log::warning('[Récupération occurrences Office] La récupération des occurrences de l\'événement ' . $id_rdv_microsoft . ' a échoué. Message d\'erreur : ' . $erreur->getMessage());
            return traduction('messages.php.microsoft_calendrier.erreur_recuperation_occurrences');
        }

        //S'il y a un nextlink dans le tableau, c'est qu'il reste des occurrences à récupérer
        while(!empty($reponse['@odata.nextLink'])) {

            try{

                $reponse = $this->graph->createRequest('GET', $reponse['@odata.nextLink'])
                    ->execute()
                    ->getBody();
            } catch(\Throwable $erreur){
                Log::warning('[Récupération occurrences Office] La récupération des occurrences de l\'événement ' . $id_rdv_microsoft . ' a échoué. Message d\'erreur : ' . $erreur->getMessage());
                return traduction('messages.php.microsoft_calendrier.erreur_recuperation_occurrences');
            }

            $occurrences = array_merge($occurrences,$reponse['value']);
        }

        $occurrences_par_id = array();

        foreach($occurrences as $occurrence)
            $occurrences_par_id[$occurrence['id']] = $occurrence;

        return $occurrences_par_id;
    }

	/**
	 *
	 * Prépare un tableau formaté pour microsoft pour la création ou la modification d'un évènement
	 *
	 */
	protected function prepare_donnees_pour_evenement($infos, $utilisateur) {

		if(!isset($infos['commentaire']))
			$infos['commentaire'] = '';

        if(!isset($infos['categorie']))
            $infos['categorie'] = '';
        else
            $this->verifie_existence_categorie($infos['categorie'], $utilisateur);

		if(!isset($infos['sujet']))
			$infos['sujet'] = '';

        if(!isset($infos['prive']) || $infos['prive'] == 0)
            $infos['prive'] = 'normal';
        else if($infos['prive'] == 1)
            $infos['prive'] = 'private';

		if(!isset($infos['date_debut']))
			$infos['date_debut'] = date('Y-m-d H:i:00');

		if(!isset($infos['date_fin']))
			$infos['date_fin'] = date('Y-m-d H:i:59');

		if(strlen($infos['date_debut']) == 10)
			$infos['date_debut'] .= ' 09:00:00';

		if(strlen($infos['date_fin']) == 10)
			$infos['date_fin'] .= ' 18:00:00';

		if($infos['date_fin'] < $infos['date_debut'])
			$infos['date_fin'] = date('Y-m-d H:00:00', strtotime($infos['date_debut'].' +1 hour'));

        if(isset($infos['journee_entiere']) && $infos['journee_entiere'])
            $infos['date_fin'] = date('Y-m-d 00:00:00', strtotime($infos['date_fin'] . ' +1 day'));

		$nouvel_evenement = [

            'subject' => $infos['sujet'],
            'categories' => [ $infos['categorie']],
            'start' => [
                'dateTime' => formate_date(isset($infos['journee_entiere']) && $infos['journee_entiere'] ? 'Y-m-dT00:00' : 'Y-m-dTH:i', $infos['date_debut']),
                'timeZone' => 'Romance Standard Time'
            ],
            'end' => [
                'dateTime' => formate_date(isset($infos['journee_entiere']) && $infos['journee_entiere'] ? 'Y-m-dT00:00' : 'Y-m-dTH:i', $infos['date_fin']),
                'timeZone' => 'Romance Standard Time'
            ],
            'body' => [
                'content' => $infos['commentaire'],
                'contentType' => 'html'
            ],
            'sensitivity' => $infos['prive'],
		];

        if(isset($infos['journee_entiere']))
            $nouvel_evenement['isAllDay'] = $infos['journee_entiere'] == true;

        if(isset($infos['organisateur']))
            $nouvel_evenement['organizer'] = $infos['organisateur'];

        if(isset($infos['recurrence']))
            $nouvel_evenement['recurrence'] = $infos['recurrence'];

        $nouvel_evenement['attendees'] = array();

        if(!empty($infos['participants'])){

            $participants = array();
            $correspondance_statuts_microsoft = [
                0 => 'notResponded',
                1 => 'accepted',
                2 => 'declined',
                3 => 'tentativelyAccepted',
            ];

            foreach($infos['participants'] as $participant){

                
                $participants[] = [
                    'emailAddress' => [
                        'address' => $participant['adresse_email']
                    ],
                    'status' => [
                        'response' => $correspondance_statuts_microsoft[$participant['statut_participant'] ?? 0],
                    ]
                ];
            }

            $nouvel_evenement['attendees'] = $participants;
        }

        if(!empty($infos['visioconference']))
            $nouvel_evenement['isOnlineMeeting'] = true;
        
        return $nouvel_evenement;
	}

    /*
     *
     * Prépare les données nécessaires à l'enregistrement du rdv dans Eden à partir des données du rdv Outlook
     *
     */
    private function prepare_donnees_enregistrement_eden($rdv, $utilisateur, $modele = null, $dates_uniquement = false):array{

        $modifications = array();

        $correspondance_statut = [
            'none' => null,
            'organizer' => null,
            'notResponded' => null,
            'accepted' => 1,
            'declined' => 2,
            'tentativelyAccepted' => 3,
        ];

        //L'heure est retournée par l'api dans le fuseau horaire UTC et non Europe/Paris, il faut la convertir
        $date_debut_utc = new \DateTime($rdv['start']['dateTime'], new \DateTimeZone('UTC'));

        if(isset($rdv['isAllDay']) && $rdv['isAllDay'] === true)
            $date_debut_locale = $date_debut_utc->setTimezone(new \DateTimeZone('Europe/Paris'))->format('Y-m-d') . ' 00:00:00';
        else
            $date_debut_locale = $date_debut_utc->setTimezone(new \DateTimeZone('Europe/Paris'))->format('Y-m-d H:i:s');

        $date_fin_utc = new \DateTime($rdv['end']['dateTime'], new \DateTimeZone('UTC'));

        if(isset($rdv['isAllDay']) && $rdv['isAllDay'] === true)
            $date_fin_locale = $date_fin_utc->setTimezone(new \DateTimeZone('Europe/Paris'))->modify('-1 day')->format('Y-m-d') . ' 23:59:59';
        else
            $date_fin_locale = $date_fin_utc->setTimezone(new \DateTimeZone('Europe/Paris'))->format('Y-m-d H:i:s');

        $modifications['date_de_debut'] = $date_debut_locale;
        $modifications['date_de_fin'] = $date_fin_locale;

        if($dates_uniquement)
            return $modifications;

        if(isset($rdv['subject']) && (empty($modele) || !empty($modele) && $rdv['subject'] != $modele->titre))
            $modifications['titre'] = $rdv['subject'];
        else if(!empty($modele->titre))
            $modifications['titre'] = $modele->titre;
        else
            $modifications['titre'] = 'N/A';


        $modifications['affectation'] = $utilisateur->id;

        if(isset($rdv['body']['content'])){

            $commentaire = str_replace(["\n","\r"], "",$rdv['body']['content']);

            if(empty($modele) || ($rdv['body']['content'] != $modele->commentaire))
                $modifications['commentaire'] = $commentaire;
        }
        else
            $modifications['commentaire'] = '';

        if(isset($rdv['sensitivity']) && $rdv['sensitivity'] == 'private' && (empty($modele) || $rdv['sensitivity'] != $modele->prive))
            $modifications['prive'] = 1;

        //On récupère les catégories disponibles dans Eden
        $categories_possibles = management('tache')->champ('type_tache_rdv')->valeurs_possibles;

        //Si la catégorie provenant d'Office correspond à une catégorie du tableau, on récupère la valeur pour l'enregistrer
        //Sinon on récupère la valeur par défaut définie dans les fonctionnalités.
        if(isset($rdv['categories'][0]) && in_array($rdv['categories'][0], $categories_possibles))
            $modifications['type_tache_rdv'] = array_search($rdv['categories'][0], $categories_possibles);
        elseif(empty($verification_existence_rdv))
            $modifications['type_tache_rdv'] = fonctionnalite('microsoft_type_rdv_defaut');

        if(empty($modele) || $rdv['id'] != $modele->id_microsoft)
            $modifications['id_microsoft'] = $rdv['id'];

        if(isset($rdv['isAllDay']) && $rdv['isAllDay'] === true)
            $modifications['journee_entiere'] = 1;
        else
            $modifications['journee_entiere'] = null;

        if(isset($rdv['isOnlineMeeting']) && $rdv['isOnlineMeeting'] === true)
            $modifications['visioconference'] = true;
        else
            $modifications['visioconference'] = null;

        if(isset($rdv['recurrence']) && (empty($modele) || $rdv['id'] != $modele->parent_id)){

            $correspondance_jour_microsoft = Variables::$correspondance_jour_microsoft;
            $correspondance_frequence = Variables::$correspondance_frequence;
            $correspondance_frequence_relative = Variables::$correspondance_frequence_relative;

            $infos_recurrence = [
                'frequence' => $rdv['recurrence']['pattern']['interval'],
                'date_de_debut' => $rdv['recurrence']['range']['startDate'],
                'date_de_fin' => $rdv['recurrence']['range']['type'] == 'noEnd' ? null : $rdv['recurrence']['range']['endDate'],
                'type_frequence' => $correspondance_frequence[$rdv['recurrence']['pattern']['type']],
            ];

            if(!empty($rdv['recurrence']['pattern']['daysOfWeek'])){

                $infos_recurrence['jours_concernes'] = array();

                foreach($rdv['recurrence']['pattern']['daysOfWeek'] as $jour)
                    $infos_recurrence['jours_concernes'][] = $correspondance_jour_microsoft[$jour];
            }

            if(!empty($rdv['recurrence']['pattern']['index']) && in_array($infos_recurrence['type_frequence'], [3,4]))
                $infos_recurrence['frequence_jour_concerne'] = $correspondance_frequence_relative[$rdv['recurrence']['pattern']['index']];

            $modifications['tache_creation_tache_recurrence'] = $infos_recurrence;

            if(!empty($modele))
                $modifications['modifier_recurrence'] = 2;

            $this->occurrences_formatees($rdv, $utilisateur, $modifications);
        }
        else if(isset($rdv['seriesMasterId'])){

            if($rdv['type'] == 'exception')
                $modifications['exception_recurrence'] = 1;

            $parent_eden = modele('tache')->avec_parents()->where('id_microsoft', $rdv['seriesMasterId'])->first();

            if(isset($parent_eden))
                $modifications['parent_id'] = $parent_eden->id;
        }

        if(isset($rdv['attendees']) && !empty($rdv['isOrganizer'])){

            $modifications['id_commun_taches_participants'] = $rdv['iCalUId'];
            $modifications['participants'] = array();
            $emails_utilisateurs = $this->utilisateurs_synchronises->pluck('email');
            
            if(isset($modele))
                $this->participants_avant = management('tache', $modele->id, $modele)->participants(true)['participants'];

            foreach($rdv['attendees'] as $participant_microsoft){

                if($participant_microsoft['emailAddress']['address'] === $rdv['organizer']['emailAddress']['address'])
                    continue;

                $statut = $correspondance_statut[$participant_microsoft['status']['response']];

                if(isset($this->participants_avant)){

                    $participant = Arr::first($this->participants_avant, fn($pe) => $pe['adresse_email'] === $participant_microsoft['emailAddress']['address']);

                    if(!empty($participant)){
                        
                        $modifications['participants'][] = array_merge(
                            $participant, 
                            array('statut_participant' => $statut)
                        );
                        
                        continue;
                    }
                }

                $resultats_recherche = management('tache')->rechercher_participants_possibles($participant_microsoft['emailAddress']['address']);

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
            }
        }
        else if(isset($rdv['attendees'])){

            $modifications['participant'] = 1;
            $modifications['statut_participant'] = $correspondance_statut[$rdv['responseStatus']['response']];
            $modifications['adresse_email_organisateur'] = $rdv['organizer']['emailAddress']['address'];
            $modifications['id_commun_taches_participants'] = $rdv['iCalUId'];

            if($rdv['isCancelled'])
                $modifications['annulee'] = 1;
        }
        
        return $modifications;
    }

    private function occurrences_formatees($rdv, $utilisateur, &$modifications){

        if(!empty($this->occurrences_formatees))
            $this->occurrences_formatees = array();

        $date_de_debut = $rdv['recurrence']['range']['startDate'] . ' 00:00:00';
        $date_de_fin = $rdv['recurrence']['range']['endDate'] . ' 23:59:59';
        $date_de_fin_max = date_create_from_format('Y-m-d H:i:s', strtotime($date_de_debut) < strtotime($this->date_debut_delta) ? $this->date_debut_delta : $date_de_debut)
            ->modify('+ ' . fonctionnalite('duree_max_creation_recurrence') . 'months');

        if($rdv['recurrence']['range']['type'] == 'noEnd' || strtotime($date_de_fin) < strtotime($date_de_debut) || strtotime($date_de_fin) > $date_de_fin_max->getTimestamp())
            $date_de_fin = $date_de_fin_max->format('Y-m-d') . ' 23:59:59';

        $occurrences_microsoft = $this->recuperer_occurrences_recurrence($rdv['id'], $date_de_debut, $date_de_fin, $utilisateur);

        if(gettype($occurrences_microsoft) === 'string')
            throw new Eden_exception($occurrences_microsoft, 500);

        $modifications_triees = $modifications;
        $modifications_triees = service('recurrence')->formate_donnees($modifications_triees);

        foreach($occurrences_microsoft as $occurrence){

            $occurrence_formatee = ['id_microsoft' => $occurrence['id']];

            if($occurrence['type'] === 'exception') {

                $modifications_occurrence = $this->prepare_donnees_enregistrement_eden($occurrence, $utilisateur);

                foreach ($modifications_triees as $nom_champ => $valeur) {

                    if (isset($modifications_occurrence[$nom_champ]) && $valeur !== $modifications_occurrence[$nom_champ])
                        $occurrence_formatee[$nom_champ] = $modifications_occurrence[$nom_champ];
                }

                $occurrence_formatee['exception_recurrence'] = 1;
            }
            else
                $modifications_occurrence = $this->prepare_donnees_enregistrement_eden($occurrence, $utilisateur, null, true);

            $occurrence_formatee['date_de_debut'] = $modifications_occurrence['date_de_debut'];
            $occurrence_formatee['date_de_fin'] = $modifications_occurrence['date_de_fin'];
            $occurrence_formatee['id_commun_taches_participants'] = $occurrence['iCalUId'];

            $this->occurrences_formatees[$occurrence['id']] =  $occurrence_formatee;
        }

        if($rdv['recurrence']['range']['type'] !== 'noEnd' && strtotime($rdv['recurrence']['range']['endDate'] . ' 23:59:59') < strtotime($date_de_debut) && !empty($this->occurrences_formatees)){

            $date_de_fin_max = $rdv['recurrence']['range']['endDate'] . ' 23:59:59';

            foreach($this->occurrences_formatees as $occurrence_formatee){

                if(strtotime($occurrence_formatee['date_de_fin']) > strtotime($date_de_fin_max))
                    $date_de_fin_max = $occurrence_formatee['date_de_fin'];
            }

            $modifications['tache_creation_tache_recurrence']['date_de_fin'] = date_create_from_format('Y-m-d H:i:s', $date_de_fin_max)->format('Y-m-d');
        }
    }

    /**
     *
     * Vérifie si une catégorie d'événement existe sur le calendrier Outlook
     *
     */
    protected function verifie_existence_categorie($categorie, $utilisateur) {

        if(empty($utilisateur->id_microsoft))
            return false;

        $categorie_a_creer = [
            "displayName" => str_replace(["\n","\r"], "",strip_tags($categorie)),
        ];

        //On essaye de créer la catégorie, si ça échoue, c'est qu'elle existe déjà
        try {

            // Envoie la requête créer la catégorie dans le calendrier
            $requete = $this->graph->createRequest('POST', '/users/'.$utilisateur->id_microsoft.'/outlook/masterCategories')
                ->attachBody($categorie_a_creer)
                ->setReturnType(Model\OutlookCategory::class)
                ->execute();

        } catch(\Throwable $exception){

            return traduction('messages.php.microsoft_calendrier.categorie_existante');
        }

        return true;
    }

    /*
     *
     * Vérifie si on doit synchroniser la tâche lors d'un enregistrement dans Eden
     *
     */
    public function condition_synchro_rdv($modele, $modifications = null){

        if(empty($modele->type_tache_rdv) || defined('synchro_rdv_microsoft_vers_eden'))
            return false;

        //Dans le cas d'une simple validation, on ne resynchronise pas.
        if(isset($modifications) && count($modifications) === 1 && array_key_exists('terminee', $modifications))
            return false;

        if(!fonctionnalite('microsoft_utiliser_connexion') || empty($modele->affectation))
            return false;

        return true;
    }

    /*
     *
     * Fait un appel API pour essayer de récupérer des événements sur une très courte période pour vérifier 
     * si on a les droits suffisants pour récupérer, créer, modifier ou supprimer des événements
     * 
     */
    public function test_droits_api($utilisateur) {

        if(empty($utilisateur->id_microsoft))
            return false;

        try {

            $rdv = $this->graph->createRequest('GET', '/users/'.$utilisateur->id_microsoft.'/calendar/events?$select=subject,start,end&$top=1')
                ->setReturnType(Model\Event::class)
                ->execute();
        } catch(\Throwable $erreur){

            return traduction('messages.php.microsoft_calendrier.erreur_droits_api');
        }

        return true;
    }

    /*
     * 
     * Modifie les occurrences d'une nouvelle récurrence qui correspondent à des exceptions pour qu'elles soient identiques
     * Utilise le batch de l'API Graph, c'est à dire faire un appel API pour un groupe de requêtes (maximum 20 à la fois) pour éviter de faire trop d'appels (et donc éviter le délai entre chaque appel) 
     * 
     */
    public function recreer_exceptions($exceptions, $utilisateur){

        $requetes = array();

        // On génère les requêtes batch
        foreach($exceptions as $index => $exception){

            $requetes[] = [
                'id' => $index,
                'method' => 'PATCH',
                'url' => '/users/'.$utilisateur->id_microsoft.'/calendar/events/'.$exception['id_microsoft'],
                'body' => $this->prepare_donnees_pour_evenement($exception['infos'], $utilisateur),
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
            ];
        }

        //On chunk par groupes de 20
        $groupes_requetes = array_chunk($requetes, 20);

        //On exécute les requêtes batch
        foreach($groupes_requetes as $groupe_requetes){

            $batch = [
                'requests' => $groupe_requetes
            ];

            try {
                
                $reponse = $this->graph->createRequest('POST', '/$batch')
                    ->attachBody($batch)
                    ->execute();
            } catch(\Throwable $e){

                Log::warning("Échec de la requête batch permettant de recréer les exceptions pour l'utilisateur " . $utilisateur->id . "(id_microsoft : ". $utilisateur->id_microsoft . "). Message d'erreur : " . $e->getMessage() . " Stacktrace : " . $e->getTraceAsString());
                return traduction('messages.php.microsoft_calendrier.erreur_modification');
            }
        }

        return true;
    }

    protected function enregistrer_evenements_avec_participants(){

        //ajouter les tâches des participants déjà synchronisées et n'ayant pas d'id_tache_organisateur pour chaque groupe
        foreach($this->rdv_participants as $id_commun_taches_participants => &$groupe_participants){

            if(!empty($groupe_participants['organisateur_id'])) {

                $organisateur = $this->utilisateurs_synchronises[$groupe_participants['organisateur_id']];
                $this->enregistrer_evenements($groupe_participants[$organisateur->id], $organisateur, true);
            }

            $taches_organisateur = modele('tache')
                ->avec_parents()
                ->where('id_commun_taches_participants', $id_commun_taches_participants)
                ->whereNull('id_tache_organisateur')
                ->whereNull('participant')
                ->get()
                ->keyBy(function($tache){
                
                    if($tache->tache_parent)
                        return 'parent';

                    return date_create_from_format('Y-m-d H:i:s', $tache->date_de_debut)->format('Y-m-d');
                });

            // On continue même sans tâche organisateur, pour remplir l'id commun sur les tâches des participants au cas où la tâche de l'organisateur
            // sera synchronisée un jour (cela peut arriver si la synchro n'était pas activée pour l'organisateur et qu'on l'active)
            foreach($groupe_participants as $utilisateur_id => &$groupe_rdv){

                if($utilisateur_id == 'organisateur_id' || (isset($groupe_participants['organisateur_id']) && $utilisateur_id == $groupe_participants['organisateur_id']))
                    continue;

                $this->enregistrer_evenements($groupe_rdv, $this->utilisateurs_synchronises[$utilisateur_id], true);
            }

            $participants_a_lier = modele('tache')
                ->avec_parents()
                ->where('id_commun_taches_participants', $id_commun_taches_participants)
                ->whereNull('id_tache_organisateur')
                ->where('participant', 1)
                ->get()
                ->keyBy(function($tache){
                
                    if($tache->tache_parent)
                        return 'parent';

                    return date_create_from_format('Y-m-d H:i:s', $tache->date_de_debut)->format('Y-m-d');
                });
                
            foreach($participants_a_lier as $date_de_debut => $rdv_a_lier){

                if(isset($taches_organisateur[$date_de_debut]))
                    management('tache', $rdv_a_lier->id, $rdv_a_lier)->enregistre_modele(['id_tache_organisateur' => $taches_organisateur[$date_de_debut]->id]);
            }
        }
        return true;
    }

    public function changer_statut_evenement($statut, $id, $utilisateur) {

        if(empty($utilisateur->id_microsoft))
            return false;

        $route = [
            1 => 'accept',
            2 => 'decline',
            3 => 'tentativelyAccept'
        ];

        try {

            // Envoie la requête pour modifier l'événement dans le calendrier
            $reponse = $this->graph->createRequest('POST', '/users/'.$utilisateur->id_microsoft.'/calendar/events/'.$id.'/'.$route[$statut])
                ->setReturnType(Model\Event::class)
                ->execute();
        } catch(\Throwable $e){

            Log::warning("Échec de la modification du statut de l'événement (id microsoft: '. $id .') pour l'utilisateur " . $utilisateur->id . "(id_microsoft : ". $utilisateur->id_microsoft . "). Message d'erreur : " . $e->getMessage() . " Stacktrace : " . $e->getTraceAsString());
            return traduction('messages.php.microsoft_calendrier.erreur_modification');
        }

        return true;
    }

    /*
     *
     * Retourne un booléen indiquant si la tâche a été modifiée ou non
     * 
     */
    protected function tache_modifiee($modifications, $modele){

        foreach($modifications as $nom_champ => $valeur){

            if(!in_array($nom_champ, ['modifier_recurrence', 'tache_creation_tache_recurrence', 'participants']) && $valeur != $modele->$nom_champ)
                return true;
        }

        if($modele->tache_parent == 1 && isset($modifications['tache_creation_tache_recurrence'])) {

            $modele_recurrence = modele('tache_recurrence')->where('tache_parent_id', $modele->id)->first();

            if(!isset($modele_recurrence))
                return true;
            else {

                $management_recurrence = management('tache_recurrence', $modele_recurrence->id, $modele_recurrence);
                $management_recurrence->charge_valeurs_champs_multiselection();

                foreach ($modifications['tache_creation_tache_recurrence'] as $nom_champ => $valeur) {

                    if ($valeur !== $management_recurrence->modele->$nom_champ)
                        return true;
                }
            }
        }
        elseif(isset($modifications['tache_creation_tache_recurrence']))
            return true;

        if(isset($modifications['participants'], $this->participants_avant)){

            $participants_avant = [
                'participants_externes' => array_values(array_filter($this->participants_avant, function($pa) {return isset($pa['id_participant']);})),
                'participants_internes' => array_values(array_filter($this->participants_avant, function($pa) {return isset($pa['id_tache']);})),
                'nouveaux_participants' => array()
            ];

            $participants = [
                'participants_externes' => array_values(array_filter($modifications['participants'], function($p) {return isset($p['id_participant']);})),
                'participants_internes' => array_values(array_filter($modifications['participants'], function($p) {return isset($p['id_tache']);})),
                'nouveaux_participants' => array_values(array_filter($modifications['participants'], function($p) {return !isset($p['id_tache']) && !isset($p['id_participant']);}))
            ];

            if($participants != $participants_avant)
                return true;
        }

        return false;
    }
}
