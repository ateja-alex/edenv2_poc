<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Cache_management;
use App\Eden\Models\Champs_liste_formatee;
use App\Eden\Variables;
use App\Eden\Models\Champ_libre;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;

class Tache_management extends Element_management {

    protected $utilisateurs = null;
    
    protected $dates_a_creer;
    protected $utilisateurs_id;
    protected $modifications_origine;
    protected $tache_deja_cree;
    protected $dates;
    protected $valeurs_liste_504 = null;
    protected $cases_sql = array();
    protected $champs_emails_par_type = null;

    protected $recurrence = array();
    public $tache_enfant = null;
    public $modifier_recurrence = false;
    public $occurrences_personnalisees = null;

    protected $createur_tache = null;
    protected $ancienne_affectation = null;
    protected $service_microsoft_calendrier = null;
    protected $service_google_calendrier = null;
    
    protected $participants = array();
    protected $participants_avant = array();
    public $taches_organisateur = null;
    protected $management_participants = null;
    protected $management_tache = null;
    public $annulation_organisateur = false;
    protected $taches_participant_creees = array();

	/**
	 *
	 * @cf description sur Element_management
	 *
	 * On ajoute le champ entite_id
	 *
	 */
	protected function retraite_modifications($modifications) {

		if(!empty($modifications['client_id'])) {

			$client = modele('client', $modifications['client_id']);

            if(!empty($client->entite_id))
			    $modifications['entite_id'] = $client->entite_id;
		}


		return parent::retraite_modifications($modifications);
	}

	/**
	 *
	 * @cf description sur Element_management
	 *
	 * On vérifie que les notifications sont bien présentes, sinon on les rajoute
	 *
	 */
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {

		parent::methodes_post_modification($modele, $modele_avant, $modifications);

		$this->enregistre_notifications_pour_tache($modele, $modifications);

        if(!empty($modele->type_tache_todo) && $modele->type_tache_todo > 0)
		    $this->enregistrer_tache_sur_liste_taches_microsoft($modele);

		// On crée/modifie une feuille de temps
		if(fonctionnalite('creation_feuille_de_temps_pour_tache')) {

			// on crée la feuille de temps
			$donnees = [
				'type_element' => 'projet',
				'element_id' => $this->modele->projet_id,
				'utilisateur_id' => $this->modele->affectation,
				'date' => $this->modele->date_de_debut,
				'duree' => nombre_d_heures_entre_deux_dates($this->modele->date_de_fin, $this->modele->date_de_debut)
			];

			// on vérifie si la tache est déjà associée une feuille de temps
			if(empty($this->modele->id_feuille_de_temps)) {

				$management_feuille_de_temps = management('feuille_de_temps');

				$management_feuille_de_temps->enregistre($donnees);

				$this->enregistre_modele(['id_feuille_de_temps' => $management_feuille_de_temps->modele->id]);
			}
			else {

				$management_feuille_de_temps = management('feuille_de_temps', $this->modele->id_feuille_de_temps);
				$management_feuille_de_temps->enregistre($donnees);
			}
		}
        
        // on crée un échange automatiquement
        $texte = table_libre($this->_type_element)->fiche == 1 ? "#lien/tache/" . $this->modele->id . "#" : $this->affiche();

        $alimentation_timeline_creation_tache = fonctionnalite('alimentation_timeline_creation_tache');

        if (empty($modele_avant->id) && $alimentation_timeline_creation_tache != 'desactive' ) {

            $type = null;
            
            if ($modele->type_tache_todo > 0 && ($alimentation_timeline_creation_tache == 'tache_todo' || $alimentation_timeline_creation_tache == 'les_2_taches'))
                $type = 'tache';
            else if ($modele->type_tache_rdv > 0 && ($alimentation_timeline_creation_tache == 'tache_rdv' || $alimentation_timeline_creation_tache == 'les_2_taches'))
                $type = 'rdv';

            if ($type != null) {
                
                $message = traduction('module_sur_fiche.client.timeline.' . $type) . ' ' . $texte;

                service('alimentation_timeline')->alimentation_timeline('alimentation_timeline_creation_tache', 'client', $this->modele->client_id, $message);
            }
        }

        $modifications_par_element = $modifications;

        $valeurs_a_eviter = array(
            'am' => [],
            'pm' => [],
        );

        if(isset($this->dates_a_creer)) {

            $debut_am = fonctionnalite('planning_affichage_demi_journee_debut_am');
            $fin_am = fonctionnalite('planning_affichage_demi_journee_fin_am');
            $debut_pm = fonctionnalite('planning_affichage_demi_journee_debut_pm');
            $fin_pm = fonctionnalite('planning_affichage_demi_journee_fin_pm');

            foreach($this->dates_a_creer as $info_date => $dates){

                foreach($dates as $date){

                    if(in_array($date,$valeurs_a_eviter[$info_date]))
                        continue;

                    if ($info_date == 'am') {

                        $modifications_par_element['date_de_debut'] = formate_date('Y-m-d '.$debut_am.':00', $date);

                        $date_fin = formate_date('Y-m-d '.$fin_pm.':00', $date);

                        if(isset($this->dates_a_creer['pm']) && in_array($date,$this->dates_a_creer['pm'])) {
                            $modifications_par_element['date_de_fin'] = $date_fin;
                            $valeurs_a_eviter['pm'][] = $date;
                        }
                        else
                            $modifications_par_element['date_de_fin'] = formate_date('Y-m-d '.$fin_am.':00', $date);
                    }
                    else {
                        $modifications_par_element['date_de_fin'] = formate_date('Y-m-d '.$fin_pm.':00', $date);

                        $date_debut = formate_date('Y-m-d '.$debut_am.':00', $date);

                        if(isset($this->dates_a_creer['am']) && in_array($date,$this->dates_a_creer['am'])) {
                            $modifications_par_element['date_de_debut'] = $date_debut;
                            $valeurs_a_eviter['am'][] = $date;
                        }
                        else
                            $modifications_par_element['date_de_debut'] = formate_date('Y-m-d '.$debut_pm.':00', $date);

                    }

                    $retour = management('tache')->enregistre($modifications_par_element);
                }
            }
        }

        //Lorsqu'on a encore des tâches à créer, on les crée
        if(isset($this->utilisateurs_id) && isset($this->tache_deja_cree) && sizeof($this->utilisateurs_id) > sizeof($this->tache_deja_cree)) {

            $this->modifications_origine['utilisateurs_id'] = $this->utilisateurs_id;
            $this->modifications_origine['tache_deja_cree'] = $this->tache_deja_cree;

            if(isset($this->dates))
                $this->modifications_origine['dates'] = $this->dates;

            $retour = management('tache')->enregistre($this->modifications_origine);
        }
    }

    /**
	 *
	 * Supprime un élément
	 *
	 * @param $modele le modèle que l'on veut supprimer (si non fourni, on se base sur le modèle lié au management)
	 *
	 * @return true si tout va bien, une erreur (string) si il y a une erreur (impossible de supprimer)
	 *
	 */
	public function supprime($modele = false) {

        $this->instancie_services_synchro_externes();

        $resultat_test = $this->test_droits_api();

        if($resultat_test !== true)
            return $resultat_test;

        if($this->annulation_organisateur === true)
            return $this->enregistre_modele(['annulee' => 1, 'titre' => traduction('messages.php.tache.annulee') . $this->modele->titre, 'chaine_affichage' => null]);
        else
            return parent::supprime();
    }
	/**
	 *
	 * On supprime la feuille de temps liée à la tâche
	 *
	 */
	public function methodes_post_suppression($modele) {

		parent::methodes_post_suppression($modele);

        if(!empty($this->service_microsoft_calendrier) && $this->service_microsoft_calendrier->condition_synchro_rdv($modele) && !isset($this->tache_enfant))
		    $this->supprimer_evenement_sur_calendrier_microsoft($modele);
        else if(!empty($this->service_google_calendrier) && $this->service_google_calendrier->condition_synchro_rdv($modele) && !isset($this->tache_enfant))
            $this->supprimer_evenement_sur_calendrier_google($modele);

        if(!empty($modele->type_tache_todo) && $modele->type_tache_todo > 0)
		    $this->supprimer_tache_sur_liste_taches_microsoft($modele);

        // Si la tâche est parente d'une récurrence, il faut supprimer tous les enfants, et la tache_recurrence qui correspond
        // La tâche parente ne peut être supprimée que via la synchro Office, car elle n'est pas accessible autrement
        if($modele->tache_parent && !$this->tache_enfant)
            service('recurrence')->supprimer_recurrence($modele->id, $modele);
        else if(!$modele->tache_parent && !$modele->participant && $this->modifier_recurrence !== 1) {

            $participants_existants = modele('tache_participants')
                ->where('id_tache_organisateur', $modele->id)
                ->get();

            $taches_participants = modele('tache')
                ->avec_parents()
                ->where('id_tache_organisateur', $modele->id)
                ->get()
                ->keyBy('affectation');
            
            $management_tache = management('tache');
            $management_tache->annulation_organisateur = true;

            $management_participants = management('tache_participants');

            foreach($participants_existants as $participant_existant){

                $management_participants->modele = $participant_existant;
                $management_participants->supprime();
            }

            foreach ($taches_participants as $tache_participant) {

                $management_tache->modele = $tache_participant;
                $management_tache->supprime();
            }
        }

		if(fonctionnalite('creation_feuille_de_temps_pour_tache')) {

			$management = management('feuille_de_temps', $modele->id_feuille_de_temps);
			$management->supprime();
		}
	}

	/**
	 *
	 * Gère les notifications associées aux tâches
	 *
	 */
	protected function enregistre_notifications_pour_tache($modele, $modifications) {

		$types_notifications = ['visuelle' => 'modal_notifications', 'email' => 'email'];

		foreach ($types_notifications as $type => $zone) {

			$active = $modele->{'notification_'.$type.'_active'} ;

            // Si ce type de notification n'est pas activé, on passe au suivant
            if($active != 1)
                continue;

			$combien = $modele->{'notification_'.$type.'_combien'} ;
			$unite = $modele->{'notification_'.$type.'_unite'} ;

            // On vérifie si la notification visuelle est bien présente
            $notification = modele('notification')
                                ->where('type_element', 'tache')
                                ->where('element_id', $modele->id)
                                ->where('zone', $zone)
                                ->first();
            if (!empty($notification))
                $management = management('notification', $notification->id, $notification);
            else
                $management = management('notification');

            if ($unite == 'J')
                $modificateur = ' -'.$combien.' day' ;
            elseif ($unite == 'H')
                $modificateur = ' -'.$combien.' hour' ;
            elseif ($unite == 'M')
                $modificateur = ' -'.$combien.' minutes' ;
            else
                $modificateur = '' ;


            $date = $modele->date_de_debut;
            $date = date('Y-m-d H:i:s', strtotime($date.$modificateur));

            $modifications = [

                'date' 				=> $date,
                'utilisateur_id' 	=> $modele->affectation,
                'zone' 				=> $zone,
                'contenu_html' 		=> (!empty($modele->type_tache_rdv) ? traduction('messages.php.notification.rdv') : traduction('messages.php.notification.tache')) . ' : ' . $this->affiche_lien() . $this->affichage_date($modele->date_de_debut, $modele->date_de_fin),
                'icone' 			=> 'fa-tasks',
                'type_element' 		=> 'tache',
                'element_id' 		=> $modele->id,
             ];

            if ($modele->terminee == 1)
                $modifications['vue'] = 1 ;
            else
                $modifications['vue'] = null;

            $management->enregistre($modifications);
		}
	}

	/**
	 *
	 *
	 *
	 */
	private function affichage_date($date_de_debut, $date_de_fin) {

		if ($date_de_debut == $date_de_fin)
			return formate_date('d/m/Y à H:i', $date_de_debut);

		if ( substr($date_de_debut, 0, 10) == substr($date_de_fin, 0,10) ) {

			return formate_date('d/m/Y', $date_de_debut).' de '.formate_date('H:i', $date_de_debut).' à '.formate_date('H:i', $date_de_fin);
		}

		return formate_date('d/m/Y H:i', $date_de_debut) . ' jusqu\'au ' . formate_date('d/m/Y H:i', $date_de_fin);
	}

	/**
	 *
	 * Définit comment une tâche doit être affichée sur le planning
	 *
	 */
	public function affiche_sur_planning() {

		return $this->affichage_pour_planning();
	}

    public function affiche_sur_calendrier() { 
    
        return $this->affichage_pour_calendrier();
    }

	/**
	 *
	 * Définit comment le title d'une tâche doit être affichée sur le calendrier
	 *
	 */
	public function affiche_title_sur_calendrier() {

		return 'tache.titre';
	}

	/**
	 *
	 * Retourne du vuejs pour afficher le title de la tache sur le caledrier (appelé dans computed)
	 *
	 */
	public function methode_vuejs_affichage_title_tache() {

		return "";
	}

	/**
	 *
	 * Définit comment un client doit être affiché sur le calendrier
	 *
	 */
	public function affiche_client_sur_calendrier($client_id) {

		return management('client', $client_id)->affiche();
	}

	/**
	 *
	 * Prépare les infos nécessaires à la création ou la modification d'un événement et décide s'il faut créer un nouvel événement ou en modifier un si un id_microsoft existe pour celui-ci
	 *
	 */
	public function enregistrer_evenement_sur_calendrier_microsoft($modele){

        if(empty($this->createur_tache) || $this->createur_tache->id !== $modele->affectation)
            $this->createur_tache = modele('utilisateur')
                ->select('email', 'id_microsoft', 'id')
                ->where('id', $modele->affectation)
                ->where('synchronisation_calendrier_outlook', 1)
                ->first();

        if(empty($this->createur_tache) || !$this->modification_evenement_synchro_externe()) {

            if(isset($this->ancienne_affectation->id) && $modele->affectation != $this->ancienne_affectation->id && !empty($modele->id_microsoft)) {

                $reponse = $this->service_microsoft_calendrier->supprimer_evenement($modele->id_microsoft, $this->ancienne_affectation);

                if(!empty($reponse) && is_string($reponse))
                    return $reponse;
                
                return $this->enregistre_modele(['id_microsoft' => null]);
            }

            return true;
        }

        $infos = $this->infos_evenement_microsoft($modele);

        if(!empty($modele->id_microsoft)){

            $infos['id'] = $modele->id_microsoft;

            if(isset($this->ancienne_affectation->id) && $modele->affectation != $this->ancienne_affectation->id)
                $reponse = $this->service_microsoft_calendrier->modifier_affectation_evenement($infos, $this->createur_tache, $this->ancienne_affectation);
            else if($this->modele_avant->statut_participant !== $this->modele->statut_participant)
                $reponse = $this->service_microsoft_calendrier->changer_statut_evenement($this->modele->statut_participant, $modele->id_microsoft, $this->createur_tache);
            else if(!$this->modele->participant)
                $reponse = $this->service_microsoft_calendrier->modifier_evenement($infos, $modele->id_microsoft, $this->createur_tache);
        }
        else if(!$this->modele->participant)
            $reponse = $this->service_microsoft_calendrier->creer_evenement($infos, $this->createur_tache);

        if(!empty($reponse) && is_string($reponse))
            return $reponse;
        else if(!empty($reponse) && is_object($reponse)){

            $modifications_communes = array();

            if(!empty($this->participants) && $this->modele->id_commun_taches_participants !== $reponse->getICalUId())
                $modifications_communes['id_commun_taches_participants'] = $reponse->getICalUId();

            if(!empty($reponse->getBody()->getContent()) && $this->modele->commentaire !== $reponse->getBody()->getContent())
                $modifications_communes['commentaire'] = $reponse->getBody()->getContent();

            if(!empty($modifications_communes) || $this->modele->id_microsoft !== $reponse->getId()){

                $this->enregistre_modele(array_merge(['id_microsoft' => $reponse->getId()], $modifications_communes));

                foreach($this->taches_participant_creees as $tache){

                    management('tache', $tache->id, $tache)->enregistre_modele($modifications_communes);
                }
            }
        }

        return true;
	}

    public function infos_evenement_microsoft($modele, $createur_tache = null){

        if(isset($createur_tache) && (!isset($this->createur_tache) || $createur_tache->id !== $this->createur_tache->id))
            $this->createur_tache = $createur_tache;

        $infos = array(
            'date_debut' => $modele->date_de_debut,
            'date_fin' => $modele->date_de_fin,
            'sujet' => $modele->titre,
            'commentaire' => $modele->commentaire,
            'categorie' => $this->champ('type_tache_rdv')->recuperer_valeur(),
            'prive' => $modele->prive,
            'organisateur' => [
                'emailAddress' => [
                    'address' => $this->createur_tache->email,
                ]
            ],
            'journee_entiere' => $modele->journee_entiere,
            'visioconference' => $modele->visioconference
        );

        if(!empty($this->participants))
            $infos['participants'] = $this->participants;

        $infos = $this->ajout_infos_evenement_microsoft($infos, $modele);

        return $infos;
    }

    public function infos_evenement_google($modele, $createur_tache = null){

        if(isset($createur_tache) && (!isset($this->createur_tache) || $createur_tache->id !== $this->createur_tache->id))
            $this->createur_tache = $createur_tache;

        $infos = array(
            'date_de_debut' => $modele->date_de_debut,
            'date_de_fin' => $modele->date_de_fin,
            'titre' => $modele->titre,
            'commentaire' => $modele->commentaire,
            'prive' => $modele->prive,
            'journee_entiere' => $modele->journee_entiere,
            'utilisateur' => $this->createur_tache,
            'categorie' => $modele->type_tache_rdv,
        );

        if(!empty($this->participants))
            $infos['participants'] = $this->participants;

        $infos = $this->ajout_infos_evenement_google($infos, $modele);

        return $infos;
    }

    /*
     *
     * Retourne true si l'un des champs synchronisés dans Office a été modifié
     *
     */
    private function modification_evenement_synchro_externe() {

        $champs_synchronises = Variables::$champs_tache_synchronises;

        $champs_modifies = array_keys($this->changement_enregistrement);
        
        foreach($champs_modifies as $champ){

            if(in_array($champ, $champs_synchronises))
                return true;
        }

        if(!empty($this->participants)) {

            $participants_avant = [
                'participants_externes' => array_filter($this->participants_avant, function($pa) {return isset($pa['id_participant']);}),
                'participants_internes' => array_filter($this->participants_avant, function($pa) {return isset($pa['id_tache']);}),
                'nouveaux_participants' => array()
            ];

            $participants = [
                'participants_externes' => array_filter($this->participants, function($p) {return isset($p['id_participant']);}),
                'participants_internes' => array_filter($this->participants, function($p) {return isset($p['id_tache']);}),
                'nouveaux_participants' => array_filter($this->participants, function($p) {return !isset($p['id_tache']) && !isset($p['id_participant']);})
            ];

            if($participants != $participants_avant)
                return true;
        }
        else if(!empty($this->participants_avant))
            return true;

        return false;
    }

    /*
     *
     * Fonction à surcharger pour ajouter des infos au rdv à envoyer à Office
     *
     */
    protected function ajout_infos_evenement_microsoft($infos, $modele){

        return $infos;
    }

	/**
	 *
	 *
	 *
	 */
	public function supprimer_evenement_sur_calendrier_microsoft($modele){

        if(empty($this->createur_tache) || $this->createur_tache->id !== $modele->affectation)
            $this->createur_tache = modele('utilisateur')
                ->select('email', 'id_microsoft', 'id')
                ->where('id', $modele->affectation)
                ->where('synchronisation_calendrier_outlook', 1)
                ->first();

        $reponse = $this->service_microsoft_calendrier->supprimer_evenement($modele->id_microsoft, $this->createur_tache);

        if(!empty($reponse) && is_string($reponse))
            return $reponse;

        return true;
	}

    /**
     *
     * Prépare les infos nécessaires à la création ou la modification d'un événement et décide s'il faut créer un nouvel événement ou en modifier un si un id_microsoft existe pour celui-ci
     *
     */
    public function enregistrer_evenement_sur_calendrier_google($modele){

        if(empty($this->createur_tache) || $this->createur_tache->id !== $modele->affectation)
            $this->createur_tache = modele('utilisateur')
                ->select('email', 'access_token_google', 'id')
                ->where('id', $modele->affectation)
                ->where('synchronisation_calendrier_google', 1)
                ->first();

        if(empty($this->createur_tache) || !$this->modification_evenement_synchro_externe()) {

            if(isset($this->ancienne_affectation->id) && $modele->affectation != $this->ancienne_affectation->id && !empty($modele->id_google)) {

                $reponse = $this->service_google_calendrier->supprimer_evenement($modele->id_google, $this->ancienne_affectation);

                if(!empty($reponse) && is_string($reponse))
                    return $reponse;
                
                return $this->enregistre_modele(['id_google' => null]);
            }

            return true;
        }

        $infos = $this->infos_evenement_google($modele);

        if(!empty($modele->id_google)){

            $infos['id'] = $modele->id_google;

            if($modele->affectation != $this->modele_avant->affectation)
                $reponse = $this->service_google_calendrier->modifier_affectation_evenement($infos, $this->createur_tache, $this->ancienne_affectation);
            else
                $reponse = $this->service_google_calendrier->modifier_evenement($infos, $modele->id_google, $this->createur_tache);
        }
        else if(!$this->modele->participant)
            $reponse = $this->service_google_calendrier->creer_evenement($infos, $this->createur_tache);

        if(!empty($reponse) && is_string($reponse))
            return $reponse;
        else if(!empty($reponse)){

            $modifications_communes = array();

            if(!empty($this->participants))
                $modifications_communes['id_commun_taches_participants'] = $reponse->iCalUID;

            $this->enregistre_modele(array_merge(['id_google' => $reponse->id], $modifications_communes));

            foreach($this->taches_participant_creees as $tache){

                management('tache', $tache->id, $tache)->enregistre_modele($modifications_communes);
            }
        }

        return true;
    }

    /*
     *
     * Fonction à surcharger pour ajouter des infos au rdv à envoyer à Office
     *
     */
    protected function ajout_infos_evenement_google($infos, $modele){

        return $infos;
    }

    public function supprimer_evenement_sur_calendrier_google($modele){

        if(empty($this->createur_tache) || $this->createur_tache->id !== $modele->affectation)
            $this->createur_tache = modele('utilisateur')
                ->select('email', 'id_microsoft', 'id')
                ->where('id', $modele->affectation)
                ->where('synchronisation_calendrier_google', 1)
                ->first();

        if(empty($this->createur_tache) || empty($modele->id_google))
            return true;

        $reponse = $this->service_google_calendrier->supprimer_evenement($modele->id_google, $this->createur_tache);

        if(!empty($reponse) && is_string($reponse))
            return $reponse;

        return true;
    }

	/**
	 *
	 * Prépare les infos nécessaires à la création ou la modification d'une tâche et décide s'il faut créer une nouvelle tâche ou en modifier un si un id_microsoft existe pour celui-ci
	 *
	 */
	public function enregistrer_tache_sur_liste_taches_microsoft($modele){

		if(fonctionnalite('microsoft_utiliser_connexion')){

            $titre = '';

            if(!empty($modele->client_id)){
                $client_management = management('client',$modele->client_id);

                if($client_management != null)
                    $titre .= '['.$client_management->affiche().'] ';
            }

            $titre .= $modele->titre;

            $commentaire = $modele->commentaire;

            if(!empty($modele->commentaire))
                $commentaire .= "\n";

            $commentaire .= URL::to('eden/fiche/tache/' . $modele->id);

			$infos = array(

				'date_debut' => $modele->date_de_debut,
				'date_fin' => $modele->date_de_fin,
				'sujet' => $titre,
				'commentaire' => $commentaire,
                'urgent' => $modele->urgent,
				'terminee' => $modele->terminee,
			);

            if($modele->notification_visuelle_active == "1"){

                $notification = modele('notification')->where('element_id',$modele->id)->where('type_element','tache')->where('zone','modal_notifications')->first();

                if($notification != null && !empty($notification->date))
                    $infos['notification'] = $notification->date;
            }

			if(!empty($modele->id_microsoft))
				service('microsoft_taches')->modifier_tache($infos, $modele->id_microsoft, $modele->affectation);
			else {

				$reponse = service('microsoft_taches')->creer_tache($infos, $modele->affectation);

				if(!empty($reponse))
					$this->enregistre_modele(array('id_microsoft' => $reponse->getId()));
			}
		}
	}

	/**
	 *
	 *
	 *
	 */
	public function supprimer_tache_sur_liste_taches_microsoft($modele){

		if(fonctionnalite('microsoft_utiliser_connexion')){

			service('microsoft_taches')->supprimer_tache($modele->id_microsoft, $modele->affectation);
		}
	}

    /**
     *
     * On vérifie les dates, la multi-affectation, si une récurrence est définie et le multi-datage dans le cas du planning
     *
     */
    public function enregistre($modifications = array(), $modele = false) {

        if(isset($modifications['utilisateurs_id'],$modifications['participants']))
            return traduction('messages.php.tache.erreur_multi_affectations_participants');

        if(isset($this->modele) && $this->modele->participant && $this->modele->annulee)
            return 'Impossible de modifier une tâche annulée.';

        $utilisateur_connecte = moi();

        //Gestion de la multi affectation
        if(isset($modifications['utilisateurs_id'])) {

            if(!isset($modifications['groupe_affectations']))
                $modifications['groupe_affectations'] = modele('tache')->avec_inactifs()->avec_parents()
                    ->select(DB::raw('COALESCE(MAX(groupe_affectations), 0) + 1 AS id_groupe'))
                    ->first()
                    ->id_groupe;

            $this->utilisateurs_id = $modifications['utilisateurs_id'];
            $this->modifications_origine = $modifications;
            

            if(!isset($modifications['tache_deja_cree']))
                $this->tache_deja_cree = array();
            else
                $this->tache_deja_cree = $modifications['tache_deja_cree'];

            $affectation_trouve = false;

            //On parcourt les utilisateurs affectés et on vérifie que la tâche n'a pas été créée pour cet utilisateur
            foreach($this->utilisateurs_id as $affectation){

                if(!in_array($affectation,$this->tache_deja_cree) && $affectation_trouve == false) {
                    $modifications['affectation'] = $affectation;
                    $affectation_trouve = true;
                }
            }

            $this->tache_deja_cree[] = $modifications['affectation'];
        }

        //Gestion du multi datage
        if(isset($modifications['dates'])) {

            $this->dates = $modifications['dates'];

            $date_trouve = false;

            $this->dates_a_creer = $this->dates;

            //On parcourt les dates et on vérifie que la tache n'a pas été créée pour cette date
            foreach($this->dates_a_creer as $info_date => $dates){

                foreach($dates as $index => $date) {

                    if ($date_trouve === false) {

                        if ($info_date == 'am') {

                            $modifications['date_de_debut'] = formate_date('Y-m-d 08:30:00', $date);

                            $date_fin = formate_date('Y-m-d 17:30:00', $date);

                            if(isset($this->dates_a_creer['pm']) && in_array($date,$this->dates_a_creer['pm'])) {
                                $modifications['date_de_fin'] = $date_fin;
                                unset($this->dates_a_creer['pm'][array_search($date,$this->dates_a_creer['pm'])]);
                            }
                            else
                                $modifications['date_de_fin'] = formate_date('Y-m-d 12:30:00', $date);
                        }
                        else {
                            $modifications['date_de_fin'] = formate_date('Y-m-d 17:30:00', $date);

                            $date_debut = formate_date('Y-m-d 8:30:00', $date);

                            if(isset($this->dates_a_creer['am']) && in_array($date,$this->dates_a_creer['am'])) {
                                $modifications['date_de_debut'] = $date_debut;
                                unset($this->dates_a_creer['am'][array_search($date,$this->dates_a_creer['am'])]);
                            }
                            else
                                $modifications['date_de_debut'] = formate_date('Y-m-d 14:30:00', $date);

                        }

                        unset($this->dates_a_creer[$info_date][$index]);

                        $date_trouve = true;
                    }
                }
            }
        }

        //On vérifie si l'utilisateur a les droits d'administrateur ou si c'est le créateur de la tâche
        if(!empty($utilisateur_connecte) && ($utilisateur_connecte->type_utilisateur == 1 || $utilisateur_connecte->type_utilisateur == 2 || (!empty($this->modele) && $utilisateur_connecte->id == $this->modele->cree_par)))
            $administrateur_ou_editeur = true;
        else
            $administrateur_ou_editeur = false;

        //Si l'utilisateur possède les droits d'administrateur, il peut modifier la tâche dans tous les cas
        if(!$administrateur_ou_editeur && !fonctionnalite('modifier_tache_terminee') && isset($this->modele->terminee) && $this->modele->terminee == 1)
            return traduction('messages.php.tache.modification_tache_terminee');

       // on regarde si la date de fin était remplie et elle est vidée alors qu'on est en RDV
		if(!empty($this->modele->type_tache_rdv) && (!isset($modifications['type_tache_rdv']) || !empty($modifications['type_tache_rdv']))) {
			
			if(isset($modifications['date_de_fin']) && empty($modifications['date_de_fin']))
				return traduction('messages.php.champ_obligatoire').' '.champ_libre('tache','date_de_fin')->modele->nom;
		}
		
		// si on change le statut pour "devenir un rdv" 
		if(empty($modifications['date_de_fin']) && empty($this->modele->date_de_fin) && !empty($modifications['type_tache_rdv']))
			return traduction('messages.php.champ_obligatoire').' '.champ_libre('tache','date_de_fin')->modele->nom;
		

        if(isset($modifications['date_de_debut']) && strpos($modifications['date_de_debut'],'autre') !== false)
            return traduction('messages.php.champ_obligatoire').' '.champ_libre('tache','date_de_debut')->modele->nom;

        $date_debut = $modifications['date_de_debut'] ?? $this->modele->date_de_debut ?? null;
        $date_fin = $modifications['date_de_fin'] ?? $this->modele->date_de_fin ?? null;

        if((isset($modifications['date_de_debut']) || isset($modifications['date_de_fin'])) 
            && !empty($date_debut) && !empty($date_fin)
            && strtotime($date_debut) > strtotime($date_fin))
                return traduction('messages.php.tache.date_fin_inferieur_egale_date_debut');

        if(!(((isset($modifications['type_tache_rdv']) && !empty($modifications['type_tache_rdv'])) || (!isset($modifications['type_tache_rdv']) && isset($this->modele->type_tache_rdv))) || ((isset($modifications['type_tache_todo']) && !empty($modifications['type_tache_todo'])) || (!isset($modifications['type_tache_todo']) && isset($this->modele->type_tache_todo)))))
            return traduction('messages.php.champ_obligatoire').' '.champ_libre('tache','type_tache_rdv')->modele->nom;

        // Si on est en journée entière, on change les heures de début et de fin
        if(isset($modifications['journee_entiere']) && $modifications['journee_entiere'] == 1){

            $modifications['date_de_debut'] = substr($modifications['date_de_debut'], 0, 10) . ' 00:00:00';
            $modifications['date_de_fin'] = substr($modifications['date_de_fin'], 0, 10) . ' 23:59:59';
        }

        if(!empty($this->modele->parent_id) && !$this->modele->tache_parent && isset($modifications['date_de_debut']) && 
            $modifications['date_de_debut'] !== $this->modele->date_de_debut){

            //On vérifie qu'on ne déplace pas l'occurrence à la même date qu'une autre occurrence
            $verification = modele('tache')
                ->where('parent_id', $this->modele->parent_id)
                ->where('id', '!=', $this->modele->id)
                ->whereDate('date_de_debut', date_create($modifications['date_de_debut'])->format('Y-m-d'))
                ->count();
            
            if($verification > 0)
                return traduction('messages.php.tache.erreur_deplacement_occurrence');
        }
        // Si une récurrence est définie, on la traite
        if(empty($this->tache_enfant) && (!empty($modifications['infos_recurrence']) || !empty($modifications['tache_creation_tache_recurrence']) || 
            !empty($modifications['parent_id']) || !empty($this->modele->parent_id) || !empty($this->modele->tache_parent))) {

            $recurrence = null;

            //On récupère la récurrence pour vérifier s'il y a des modifications
            if(!empty($modifications['parent_id']))
                $recurrence = modele('tache_recurrence')->where('tache_parent_id', $modifications['parent_id'])->first();
            else if(!empty($this->modele->parent_id))
                $recurrence = modele('tache_recurrence')->where('tache_parent_id', $this->modele->parent_id)->first();
            else if (isset($this->modele->tache_parent) && $this->modele->tache_parent)
                $recurrence = modele('tache_recurrence')->where('tache_parent_id', $this->modele->id)->first();

            if(!empty($recurrence)){

                $management_recurrence = management('tache_recurrence', $recurrence->id, $recurrence);
                $management_recurrence->charge_valeurs_champs_multiselection();
                $recurrence = $management_recurrence->modele;
            }

            if(!empty($modifications['infos_recurrence']))
                $modifications['tache_creation_tache_recurrence'] = $modifications['infos_recurrence'];

            if(empty($recurrence))
                $modifications['tache_parent'] = true;

            $this->recurrence = [
                'infos_recurrence' => $modifications['tache_creation_tache_recurrence'] ?? array(),
                'modele_recurrence' => $recurrence
            ];
            
            if(!empty($this->modele->tache_parent) || !empty($this->modele->parent_id))
                unset($modifications['tache_creation_tache_recurrence']);
        }
        
        if(isset($this->modele->id) && empty($this->modele->participant))
            $this->participants_avant = isset($this->modele->id) ? $this->participants(true)['participants'] : array();
        
        if((!empty($this->participants_avant) || array_key_exists('participants', $modifications)) && empty($this->modele->participant)){
        
            if(isset($modifications['participants']))
                $this->participants = gettype($modifications['participants']) === 'string' ? 
                    json_decode($modifications['participants'], true) : $modifications['participants'];
            else if(array_key_exists('participants', $modifications))
                $this->participants = array();
            else
                $this->participants = $this->participants(true)['participants'];
        }

        //Si modifier_recurrence est dans les modifications, on vérifie la valeur pour savoir si on ne modifie qu'une occurrence ou plusieurs
        if(isset($modifications['modifier_recurrence']) && $modifications['modifier_recurrence'] > 0)
            $this->modifier_recurrence = $modifications['modifier_recurrence'];
        else if($this->condition_exception_recurrence($modifications))
            $modifications['exception_recurrence'] = 1;
        
        $this->instancie_services_synchro_externes();

        $resultat_test = $this->test_droits_api($modifications);

        if($resultat_test !== true)
            return $resultat_test;

        $retour_enregistrement = parent::enregistre($modifications, $modele);

        if($retour_enregistrement !== true)
            return $retour_enregistrement;

        $retour = $this->gestion_recurrences($modifications);

        if($retour !== true)
            return $retour;

        if((!empty($this->participants_avant) || !empty($this->participants)) && empty($this->modele->participant) && (empty($this->tache_enfant) || !empty($this->modele->exception_recurrence)))
            $retour = $this->gestion_participants($modifications);

        if(isset($retour) && $retour !== true)
            return $retour;

        if(!empty($this->service_microsoft_calendrier) && $this->service_microsoft_calendrier->condition_synchro_rdv($this->modele, $modifications) && 
            !isset($this->tache_enfant) && (!isset($this->modifier_recurrence) || $this->modifier_recurrence == false) && empty($this->modele->tache_parent))
            $retour = $this->enregistrer_evenement_sur_calendrier_microsoft($this->modele, $this->createur_tache, $this->ancienne_affectation);
        else if(!empty($this->service_google_calendrier) && $this->service_google_calendrier->condition_synchro_rdv($this->modele, $modifications) && !isset($this->tache_enfant) &&
            (!isset($this->modifier_recurrence) || $this->modifier_recurrence == false) && empty($this->modele->tache_parent))
            $retour = $this->enregistrer_evenement_sur_calendrier_google($this->modele);

        return $retour;
    }

    /**
     *
     * On affiche une ligne rouge si urgent
     *
     */
    public function recupere_tache_urgente($modele){

        if($modele['urgent'] == 1)
            return "<span style='display: flex;background-color: #bd2727; height: 40px; width: 10px;border-radius: 8px;'></span>";

    }

    /**
     *
     * On affiche l'icone associé au type de tache
     *
     */
    public function icone_type_de_tache($modele){

        $types_tache = Champs_liste_formatee::where('id_liste_choix',503)->get()->keyBy('id_valeur')->toArray();

        if(isset($types_tache[$modele['type_tache_todo']]) && $types_tache[$modele['type_tache_todo']]['icone'] != null) {
            return "<div style='width: 35px; height: 35px;border-radius: 50%;overflow: hidden;background-color: black; color: white;display: flex;align-items: center;justify-content: center;margin-left: auto;margin-right: auto;'>
                        <span style='font-size: 20px;' class='" . $types_tache[$modele['type_tache_todo']]['icone'] . "'></span>
                    </div>";
        }
        else{
            return "<div style='width: 35px; height: 35px;border-radius: 50%;overflow: hidden;background-color: black; color: white;display: flex;align-items: center;justify-content: center;margin-left: auto;margin-right: auto;'>
                        <span style='font-size: 20px;' class='fas fa-stream'></span>
                    </div>";
        }


    }

    /**
     *
     * On retourne le titre avec la date de début et de fin
     *
     */
    public function titre_formate_dates($modele){

        return "<span style='font-weight: bold'>" . $modele['titre'] . "</span><br><span style='font-style: italic'>Début : " . date('d/m/Y H:i', strtotime($modele['date_de_debut'])) . "</span><br><span style='font-style: italic'>Fin : " . date('d/m/Y H:i', strtotime($modele['date_de_fin'])) . "</span>";


    }

	/**
     *
     * On retourne le titre avec la date de début et de fin
     *
     */
    public function avatar_utilisateur_en_charge($modele){

        $utilisateur = modele('utilisateur')->where('id',$modele['affectation'])->first();
        if(!empty($utilisateur) && $utilisateur['avatar'] != null)
            return "
            <div style='width: 35px; height: 35px;border-radius: 50%;overflow: hidden;margin-left: auto;margin-right: auto;'>
                <img style='width: 100%;' src='" . asset('storage') . "/" . $utilisateur['avatar'] . "'>
            </div>";
        else
            return "
            <div style='width: 35px; height: 35px;border-radius: 50%;overflow: hidden;margin-left: auto;margin-right: auto;'>
                <img style='width: 100%' src='" . asset('eden/images') . "/no_avatar.jpg'>
            </div>
            ";

    }

    public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();

        $liste_options[] = 'valider';

        if(in_array('dupliquer',$liste_options))
            unset($liste_options[array_search('dupliquer',$liste_options)]);

        return $liste_options;
    }

    /**
     *
     * On retourne le nom du client
     *
     */
    public function client_associe_tache($modele){

        if(isset($modele->client_id)){

            $client = modele('client')->where('id', $modele->client_id)->first();

            return "<span style='font-weight: bold;'>" . $client['chaine_affichage'] . "</span>";

        }

    }

    /**
     *
     * On retourne le titre de la tache
     *
     */
    public function affichage_titre_tableau_bord($modele){

        return "<span style='font-weight: bold;'>" . $modele->titre . "</span>";

    }

    /**
     *
     * On retourne la date de début formatée
     *
     */
    public function format_date_debut($modele){

        if($modele->type_tache_rdv != null && $modele->type_tache_rdv != '')
            return "<div style='text-align: center;'><span>" . date('d/m/Y', strtotime($modele['date_de_debut'])) . "</span><br><span style='font-style: italic;'>" . date('H:i', strtotime($modele['date_de_debut'])) . "</span></div>";

        return "<div style='text-align: center;'><span>" . date('d/m/Y', strtotime($modele['date_de_debut'])) . "</span></div>";

    }

    /**
     *
     * On retourne la date de fin formatée
     *
     */
    public function format_date_fin($modele){

        if($modele->type_tache_rdv != null && $modele->type_tache_rdv != '')
            return "<div style='text-align: center;'><span>" . date('d/m/Y', strtotime($modele['date_de_fin'])) . "</span><br><span style='font-style: italic;'>" . date('H:i', strtotime($modele['date_de_fin'])) . "</span></div>";

        return '';
    }

    /**
	 *
	 * On rajoute la date début à aujourd'hui
	 *
	 */
	public function modele_par_defaut() {

		$modele = parent::modele_par_defaut();

		$moi = moi();

		if($moi !== null && property_exists($moi, "equipe") && !empty($moi->equipe)){

            $equipe = modele('equipe',moi()->equipe);

            if($equipe != null && $equipe->type_tache_rdv_par_defaut != null){

                $modele->type_tache_rdv = $equipe->type_tache_rdv_par_defaut;
            }
        }

        if(fonctionnalite('planning_affichage_demi_journee'))
            $modele->dates = array(
                'am' => [],
                'pm' => []
            );

        $modele->affectations = array();

		return $modele;
	}

    public function recuperer_couleur_tache() {

        $fonctionnalite_couleur_tache = fonctionnalite('choix_couleur_tache');

        if(!isset($this->utilisateurs) && in_array($fonctionnalite_couleur_tache, ['equipe','utilisateur']))
            $this->utilisateurs = modele('utilisateur')->get()->keyBy('id');
        else if(!isset($this->valeurs_liste_504) && $fonctionnalite_couleur_tache === 'type_tache')
            $this->valeurs_liste_504 = Cache_management::valeurs_liste_formatee(504);

        $tache = $this->modele;
        $style['background'] = maquette('background_tache');
        $style['color'] = maquette('couleur_police_tache');

        if($fonctionnalite_couleur_tache == 'equipe' && !empty($tache->affectation)) {

            $utilisateur = $this->utilisateurs[$tache->affectation] ?? null;

            if ($utilisateur!= null && !empty($utilisateur->equipe)) {

                $equipe = modele('equipe', $utilisateur->equipe);

                if (!empty($equipe->couleur_fond))
                    $style['background'] = $equipe->couleur_fond;

                if (!empty($equipe->couleur_police))
                    $style['color'] = $equipe->couleur_police ;
            }
        }

        if($fonctionnalite_couleur_tache == 'utilisateur' && !empty($tache->affectation)) {

            $utilisateur = $this->utilisateurs[$tache->affectation] ?? null;

            if (!empty($utilisateur->couleur_fond_tache))
                $style['background'] = $utilisateur->couleur_fond_tache;

            if (!empty($utilisateur->couleur_police_tache))
                $style['color'] = $utilisateur->couleur_police_tache;
        }

        $type_tache_rdv = $tache->type_tache_rdv;

        if(fonctionnalite('choix_couleur_tache') == 'type_tache' && !empty($type_tache_rdv)) {

            if(!empty($this->valeurs_liste_504) && isset($this->valeurs_liste_504[$type_tache_rdv])){

                $style['background'] = maquette('background_tache');
                $style['color'] = maquette('couleur_police_tache');

                if(!empty($this->valeurs_liste_504[$type_tache_rdv]['couleur_fond']))
                    $style['background'] = $this->valeurs_liste_504[$type_tache_rdv]['couleur_fond'];

                if(!empty($this->valeurs_liste_504[$type_tache_rdv]['couleur_police']))
                    $style['color'] = $this->valeurs_liste_504[$type_tache_rdv]['couleur_police'];
            }
        }

        return $style;
    }

    /**
	 *
	 * Si le champ type_tache_rdv ou type_tache_todoest rempli et qu'il y a une valeur par défaut sur l'autre type on le rempli pas
	 *
	 */
	public function affecte_valeurs_par_defaut_en_creation($modifications, $champs) {

		if(isset($this->modele) && $this->modele->exists === true)
            return $modifications;

		foreach($champs as $champ) {

			if(!empty($modifications[$champ->nom_sql]))
				continue;

			if(empty($champ->valeur_defaut))
				continue;

            if(($champ->nom_sql == 'type_tache_rdv' && !empty($modifications['type_tache_todo'])) ||
                ($champ->nom_sql == 'type_tache_todo' && !empty($modifications['type_tache_rdv']))
            )
                continue;

			$modifications[$champ->nom_sql] = $this->remplace_variables_modele_par_defaut($champ);
		}

		return $modifications;
	}

    public function charge_donnees_rdv_pour_pdf_pour_fiche_client($id_client){

        // On récupère les tâches pour récupérer les 5 dernières tâches RDV + faire la stat
        $taches_5_plus_recents = modele('tache')->where('client_id',$id_client)->where('type_tache_rdv', '>', 0)->orderBy('date_de_debut', 'DESC')->take(5)->get();

        $nombre_tache = modele('tache')->where('client_id',$id_client)->where('type_tache_rdv', '>', 0)->count();

        // On a besoin de la premiere tache pour savoir quelle est la date du premier RDV de ce client.
        // Dans le but de créer l'array contenant le nombre de RDV / mois pour faire la moyenne
        $premiere_tache = modele('tache')->select('date_de_debut')->where('client_id',$id_client)->where('type_tache_rdv', '>', 0)->orderBy('date_de_debut', 'ASC')->first();

        $donnees = array();

        if($premiere_tache !== null) {

            $date_actuelle_datetime = new \DateTime();
            $date_actuelle = $date_actuelle_datetime->format('Y-m');

            $date_premiere_tache_datetime = new \DateTime($premiere_tache->date_de_debut);
            $date_comparaison = $date_premiere_tache_datetime->format('Y-m');

            $nombre_mois = 0;

            // On compte le nombre de mois pour faire la statistique
            if ($date_comparaison != $date_actuelle)
                $nombre_mois = $date_actuelle_datetime->diff($date_premiere_tache_datetime)->format('%m');

            $moyenne = 0;

            if($nombre_mois > 0)
                $moyenne = round($nombre_tache / $nombre_mois, 2);

            $donnees['moyenne'] = $moyenne;
        }
        else
            $donnees['moyenne'] = 0;

        $donnees['taches'] = $taches_5_plus_recents;

        return $donnees;
    }

    /*
     *
     * On retourne les infos du sous-formulaire tache_recurrence
     *
     */
    public function retourne_sous_formulaire(){

        $retour = parent::retourne_sous_formulaire();

        $retour[] = [
            'type_element_enfant' => 'tache_recurrence',
            'champ_liaison' => 'tache_parent_id',
            'optionnel' => 0,
            'unique' => 1,
        ];

        return $retour;

    }

    /**
     *
     * On gére différent les triggers pour les récurrences
     *
     */
    public function trigger_applicatif($remplacements = array()) {

        if(empty($remplacements) && !empty($this->tache_enfant))
            return;

        return parent::trigger_applicatif($remplacements);
    }

    /*
     *
     * Récupère les autres affectations sous le format de la chaîne d'affichage d'une tâche
     *
     */
    public function autres_affectations(){

        if(empty($this->modele->groupe_affectations))
            return array();
        
        $utilisateurs = modele('tache')
            ->join('utilisateur', 'utilisateur.id', '=', 'tache.affectation')
            ->select('utilisateur.*')
            ->where('groupe_affectations', $this->modele->groupe_affectations)
            ->where('affectation', '!=', $this->modele->affectation)
            ->groupBy('affectation')
            ->get();

        $tableau_chaines_utilisateurs = [];

        foreach ($utilisateurs as $utilisateur){

            $tableau_chaines_utilisateurs[$utilisateur->id] = $utilisateur->nom . ' ' . $utilisateur->prenom;
        }

        return $tableau_chaines_utilisateurs;
    }

    public function autres_affectations_pour_liste($element){
        
        $this->reload_modele($element, $this->modele);

        $autres_affectations = $this->autres_affectations();

        $affichage = implode(', ',array_values($autres_affectations));

        return $affichage;
    }
        
    private function gestion_recurrences(&$modifications) {

        //Si une récurrence est définie, on la traite
        if(!empty($this->recurrence) && empty($this->tache_enfant)){

            $modifications['infos_recurrence'] = $this->recurrence['infos_recurrence'];
            $modifications['modele_recurrence'] = $this->recurrence['modele_recurrence'];
            $modifications['modifier_recurrence'] = $this->modifier_recurrence ?? false;
            
            $service = service('recurrence');

            if(isset($this->occurrences_personnalisees))
                $service->occurrences_personnalisees = $this->occurrences_personnalisees;

            if(!empty($this->taches_organisateur) && $this->modele->participant)
                $service->taches_organisateur = $this->taches_organisateur;
            else if(!empty($this->participants) && !$this->modele->participant){

                $service->organisateur = true;
                $service->participants = $this->participants;
            }

            if(empty($modifications['modele_recurrence']))
                $retour = $service->enregistre_recurrence($modifications, $this->modele);
            else
                $retour = $service->modifier_recurrence($modifications, $this->modele, $this->modele_avant);

            if(!empty($service->taches_organisateur))
                $this->taches_organisateur = $service->taches_organisateur;

            if(!empty($this->modele->id_commun_taches_participants))
                $modifications['id_commun_taches_participants'] = $this->modele->id_commun_taches_participants;

            // Si modification_infos_recurrence ou modification_dates_recurrence, cela veut dire que la récurrence a été modifiée, on l'indique donc dans la variable changement_enregistrement
            if(!empty($service->modification_infos_recurrence) || !empty($service->modification_dates_recurrence) || !empty($service->modification_recurrence)){

                $this->changement_enregistrement['infos_recurrence'] = $modifications['infos_recurrence'];
                $this->reload_modele();

                // Cas de la modif "Cet évènement et les suivants, on coupe la récurrence et on en recrée une, 
                // il faut donc vider participants_avant car il n'y en a plus, et indiquer les participants actuels comme étant à créer
                if(!empty($service->modification_recurrence) && !empty($this->participants) && !empty($this->participants_avant)){

                    array_walk($this->participants, fn(&$p) => $p['id_participant'] = null);
                    $this->participants_avant = array_filter($this->participants_avant, fn($p) => isset($p['id_tache']));
                }

                // Si modification_infos_recurrence, cela veut dire qu'on a totalement recréé la récurrence et les participants,
                // On doit donc vider les tableaux participants_avant et participants
                if(!empty($service->modification_infos_recurrence) && empty($service->modification_recurrence) && !empty($this->participants) && !empty($this->participants_avant))
                    $this->participants = $this->participants_avant  = array();
            }

            if(!empty($this->modele->commentaire) && !empty($modifications['commentaire']) && $this->modele->commentaire !== $modifications['commentaire'])
                $modifications['commentaire'] = $this->modele->commentaire;
        }

        return $retour ?? true;
    }

    private function instancie_services_synchro_externes() {

        if(fonctionnalite('microsoft_utiliser_connexion') && empty($this->service_microsoft_calendrier))
            $this->service_microsoft_calendrier = service('microsoft_calendrier');
        if(fonctionnalite('google_utiliser_connexion') && empty($this->service_google_calendrier))
            $this->service_google_calendrier = service('google_calendrier');
    }

    public function test_droits_api($modifications = array()) {

        if(!empty($this->service_microsoft_calendrier) || $this->service_google_calendrier) {

            $type_synchro = !empty($this->service_microsoft_calendrier) ? 'outlook' : 'google';

            if(empty($this->createur_tache) || (isset($modifications['affectation']) && $this->createur_tache->id !== $modifications['affectation']))
                $this->createur_tache = modele('utilisateur')
                    ->select('email', 'id_microsoft', 'access_token_google', 'id')
                    ->where('id', $modifications['affectation'] ?? $this->modele->affectation)
                    ->where('synchronisation_calendrier_' . $type_synchro, 1)
                    ->first();

            if(isset($this->modele, $modifications['affectation']) && $modifications['affectation'] != $this->modele->affectation && 
                (empty($this->ancienne_affectation) || $this->ancienne_affectation->id !== $this->modele->affectation))
                $this->ancienne_affectation = modele('utilisateur')
                    ->select('email', 'id_microsoft', 'access_token_google', 'id')
                    ->where('id', $this->modele->affectation)
                    ->where('synchronisation_calendrier_' . $type_synchro, 1)
                    ->first();
        }
        
        // Si la tâche existe et qu'elle est synchronisée, on teste si on a les droits sur les événements. 
        // Si on ne les a pas, on bloque la modification
        if(!defined('microsoft_test_api_calendrier_ok') && !empty($this->service_microsoft_calendrier) && 
            $this->service_microsoft_calendrier->condition_synchro_rdv($this->modele, $modifications) && !isset($this->tache_enfant)) {
            
            //Si l'utilisateur n'a pas d'id, ça veut dire qu'il ne s'est jamais connecté via Office, il n'y aura donc pas de test de droits à effectuer car aucune synchro
            if(empty($this->createur_tache->id_microsoft) && empty($this->ancienne_affectation->id_microsoft))
                return true;

            $test_droits = $this->service_microsoft_calendrier->test_droits_api($this->createur_tache ?? $this->ancienne_affectation);
            
            // Si la fonction renvoie autre chose que true, c'est qu'il manque des droits
            if($test_droits !== true)
                return $test_droits;

            define('microsoft_test_api_calendrier_ok', true);
        }
        else if(!defined('google_test_api_calendrier_ok') && !empty($this->service_google_calendrier) && 
            $this->service_google_calendrier->condition_synchro_rdv($this->modele) && !isset($this->tache_enfant)){

            //Si l'utilisateur n'a pas d'id, ça veut dire qu'il ne s'est jamais connecté via Google, il n'y aura donc pas de test de droits à effectuer car aucune synchro
            if(empty($this->createur_tache->access_token_google) && empty($this->ancienne_affectation->access_token_google))
                return true;

            // On teste si on a les droits sur les événements
            $test_droits = $this->service_google_calendrier->test_droits_api($this->createur_tache ?? $this->ancienne_affectation);
            
            // Si la fonction renvoie autre chose que true, c'est qu'il manque des droits
            if($test_droits !== true)
                return $test_droits;

            define('google_test_api_calendrier_ok', true);
        }

        return true;
    }

    /**
     * Récupère les autres affectations sous le format de la chaîne d'affichage d'une tâche
     * @return array{organisateur: mixed, participants: array}
     */
    public function participants($sans_chaine_affichage = false){

        $id_tache_organisateur = $this->modele->participant ? $this->modele->id_tache_organisateur : $this->modele->id;
        $participants_utilisateurs = collect();
        $organisateur = null;

        if(empty($id_tache_organisateur))
            return ['organisateur' => null, 'participants' => array()];
        
        $organisateur = modele('tache')
            ->avec_parents()
            ->select('utilisateur.*')
            ->join('utilisateur', 'tache.affectation', 'utilisateur.id')
            ->where('tache.id', $id_tache_organisateur)
            ->first();

        $participants_utilisateurs = modele('tache')
            ->avec_parents()
            ->select('tache.id as id_tache', DB::raw("'utilisateur' as type_element"), 'tache.affectation as element_id', 'tache.statut_participant', 'utilisateur.email as adresse_email', 'id_tache_organisateur')
            ->join('utilisateur', 'utilisateur.id', '=', 'tache.affectation')
            ->whereNot('tache.id', $id_tache_organisateur)
            ->where('id_tache_organisateur', $id_tache_organisateur)
            ->where(DB::raw('COALESCE(annulee, 0)'), 0)
            ->get()
            ->toArray();

        $id_tache_organisateur_externes = empty($this->modele->participant) ? 
            (empty($this->modele->parent_id) ? $this->modele->id : ($this->modele->exception_recurrence ? $this->modele->id : $this->modele->parent_id)) : 
            (empty($this->modele->parent_id) || $this->modele->exception_recurrence ? $this->modele->id_tache_organisateur : null);

        // Si on est sur la tâche du participant, il faut l'id organisateur de la tâche parente, donc faire une sous-requête pour récupérer l'id tache organisateur de la parente
        // Sinon, on utilise directement le parent_id 
        $participants_externes = modele('tache_participants')
            ->select('id as id_participant', 'type_element', 'element_id', 'statut_participant', 'adresse_email', 'champ_email', 'id_tache_organisateur')
            ->where('id_tache_organisateur', $id_tache_organisateur_externes ?? function($sous_requete){
                
                $sous_requete->select('id_tache_organisateur')->from('tache')->where('id', $this->modele->parent_id);
            })
            ->get()
            ->toArray();
        
        $participants = array_merge($participants_utilisateurs, $participants_externes);

        foreach($participants as &$participant){

            if(isset($participant['type_element'], $participant['element_id'])){
            
                $management = management($participant['type_element'], $participant['element_id']);

                if(isset($participant['champ_email'], $management->modele->{$participant['champ_email']}))
                    $participant['adresse_email'] = $management->modele->{$participant['champ_email']};

                if($sans_chaine_affichage === false)
                    $participant['affichage_pour_recherche'] = $management->affichage_pour_select();
            }
            else if($sans_chaine_affichage === false)
                $participant['affichage_pour_recherche'] = $participant['adresse_email'];
        }

        if(!empty($organisateur)){

            $management_organisateur = management('utilisateur', $organisateur->id, $organisateur);
            $organisateur->affichage_pour_recherche = $management_organisateur->affichage_pour_select();
        }

        return ['organisateur' => $organisateur, 'participants' => $participants];
    }

    /**
     * Permet d'ajouter/modifier les participants d'une tâche.
     * @param array $modifications
     * @return void
     */
    private function gestion_participants(array $modifications){
        
        $modifications_participants = $this->recuperer_modifications_participants();

        if(empty($this->taches_organisateur))
            $this->taches_organisateur = modele('tache')
                ->avec_parents()
                ->where(function($requete) {

                    $id_parent = $this->modele->tache_parent || empty($this->modele->tache_parent) || empty($this->modele->parent_id) ? 
                        $this->modele->id : $this->modele->parent_id;
                    $requete->where('parent_id', $id_parent)->orWhere('id', $id_parent);
                })
                ->get()
                ->keyBy(fn($t) => $t->tache_parent ? 'parent' : date_create($t->date_de_debut)->format('Y-m-d'));
        
        $this->management_participants = management('tache_participants');
        $this->management_participants->tache_maitre_organisateur = $this->modele;

        $this->management_tache = management('tache');
        $this->management_tache->taches_organisateur = $this->taches_organisateur;

        $champs_a_supprimer = ['id_google', 'id_microsoft','parent_id'];

        if((!empty($this->modele_avant->parent_id) || !empty($this->modele_avant->tache_parent)) && !$this->modifier_recurrence && !empty($this->recurrence))
            $champs_a_supprimer = array_merge($champs_a_supprimer, ['infos_recurrence', 'type_recurrence', 'modele_recurrence', 'modifier_recurrence']);
        
        foreach($champs_a_supprimer as $nom_champ) {
        
            if(isset($modifications[$nom_champ]))
                unset($modifications[$nom_champ]);
        }

        if(empty($this->tache_enfant))
            $this->creation_participants($modifications, $modifications_participants['creation']);

        if(!empty($this->modele_avant->id))
            $this->suppression_participants($modifications_participants['suppression']);

        $taches_participants = modele('tache')
            ->avec_parents()
            ->where('id_tache_organisateur', $this->modele->id)
            ->get()
            ->keyBy('affectation');

        $participants_externes = modele('tache_participants')
            ->where('id_tache_organisateur', $this->modele->id)
            ->get();
        
        foreach($modifications_participants['modification']['tache'] as $participant){

            if(empty($this->changement_enregistrement) && $participant['statut_participant'] == $taches_participants[$participant['element_id']]->statut_participant)
                continue;
            
            $modifications_tache = array_filter($modifications, fn($nom_champ) => !in_array($nom_champ, ['affectation', 'participants', 'id_commun_taches_participants']), ARRAY_FILTER_USE_KEY);
            $modifications_tache['statut_participant'] = $participant['statut_participant'];
            
            $this->management_tache->modele = $taches_participants[$participant['element_id']];

            $this->management_tache->enregistre($modifications_tache);
        }
        
        if(!empty($this->modele->parent_id) && empty($this->modele->exception_recurrence))
            return true;

        foreach($modifications_participants['modification']['tache_participants'] as $participant){

            $participant_actuel = $participants_externes->first(function($element) use ($participant){
                return (isset($participant['type_element'], $participant['element_id']) && 
                    $element->element_id == $participant['element_id'] && $element->type_element == $participant['type_element']) 
                    ||
                    (isset($participant['adresse_email']) && $element->adresse_email == $participant['adresse_email']);
            });
            
            if(!isset($participant_actuel) || $participant_actuel->statut_participant == $participant['statut_participant'] || 
                ($participant_actuel->statut_participant != $participant['statut_participant'] && empty($participant['statut_participant'])))
                continue;

            $this->management_participants->modele = $participant_actuel;
            $this->management_participants->enregistre(['statut_participant' => $participant['statut_participant']]);
        }

        return true;
    }

    private function recuperer_modifications_participants() {
        
        $participants = $this->participants;

        if(isset($this->modele->exception_recurrence) && $this->modele->exception_recurrence){

            $participants_exception = collect($this->participants(true)['participants']);

            $participants = array_map(function($p) use ($participants_exception){
                
                if(!empty($p['id_tache']) || !empty($p['id_participant']))
                    $participant_exception = $participants_exception->first(fn($pe) => 
                        $pe['type_element'] == $p['type_element'] && $pe['element_id'] == $p['element_id'] && 
                        $pe['adresse_email'] == $p['adresse_email'] && (!empty($p['id_tache']) || !empty($p['id_participant']))
                    );
                
                if(!empty($participant_exception)){
                    
                    if($p['statut_participant'] != $participant_exception['statut_participant'])
                        $participant_exception['statut_participant'] = $p['statut_participant'];

                    return $participant_exception;
                }

                return $p;
            }, $participants);
        }

        $modifications_participants = [
            'creation' => array_filter($participants, function($p) {return empty($p['id_tache']) && empty($p['id_participant']);}),
            'suppression' => [
                'tache' => [],
                'tache_participants' => [],
            ],
            'modification' => [
                'tache' => [],
                'tache_participants' => [],
            ]
        ];

        $participants_avant = [
            'tache_participants' => array_column(array_filter($this->participants_avant, function($pa) {return isset($pa['id_participant']);}), null, 'id_participant'),
            'tache' => array_column(array_filter($this->participants_avant, function($pa) {return isset($pa['id_tache']);}), null, 'id_tache')
        ];
        
        foreach($participants as $participant){

            if(isset($participant['id_tache'], $participants_avant['tache'][$participant['id_tache']]) ){

                $modifications_participants['modification']['tache'][$participant['id_tache']] = $participant;
                unset($participants_avant['tache'][$participant['id_tache']]);
            }
            else if(isset($participant['id_participant']) && 
                (
                    (isset($participants_avant['tache_participants'][$participant['id_participant']]) && !empty($this->modele->exception_recurrence) && 
                        ($participant['id_tache_organisateur'] != $this->modele->id)) 
                    || 
                    (!isset($participants_avant['tache_participants'][$participant['id_participant']]) && empty($this->modele->exception_recurrence))
                )
            ){
                
                unset($participants_avant['tache_participants'][$participant['id_participant']]);
                unset($participant['id_participant']);
                $modifications_participants['creation'][] = $participant;
            }
            else if(isset($participant['id_participant'], $participants_avant['tache_participants'][$participant['id_participant']])){

                $modifications_participants['modification']['tache_participants'][$participant['id_participant']] = $participant;
                unset($participants_avant['tache_participants'][$participant['id_participant']]);
            }
            
        }

        $modifications_participants['suppression']['tache'] = $participants_avant['tache'];
        $modifications_participants['suppression']['tache_participants'] = $participants_avant['tache_participants'];
        
        return $modifications_participants;
    }

    /**
     * Crée les nouveaux participants sur une tâche. Si elle est récurrente, crée les participants sur les occurrences également
     * @param array $modifications
     * @return array[]
     */
    private function creation_participants(array $modifications, array $participants_a_creer){

        $modifications_tache = $modifications;
        $modifications_tache['id_tache_organisateur'] = $this->modele->parent_id && isset($modifications['infos_recurrence']) ? $this->modele->parent_id : $this->modele->id;
        $modifications_tache['participant'] = true;

        foreach($participants_a_creer as $participant){

            if(isset($participant['type_element'], $participant['element_id']) && $participant['type_element'] === 'utilisateur') {

                $modifications_tache['affectation'] = $participant['element_id'];
                $modifications_tache['statut_participant'] = $participant['statut_participant'] ?? null;
                
                if(!empty($this->modele->parent_id) && !empty($this->taches_organisateur))
                    $modifications_tache['id_commun_taches_participants'] = $this->taches_organisateur['parent']?->id_commun_taches_participants;
                else
                    $modifications_tache['id_commun_taches_participants'] = $this->modele->id_commun_taches_participants;

                $this->management_tache->modele = null;
                $this->management_tache->enregistre($modifications_tache);
                $this->taches_participant_creees[] = $this->management_tache->modele;
            }
            else if(empty($this->modele->parent_id) || !empty($this->modele->exception_recurrence) || !empty($this->modifier_recurrence)){

                $this->management_participants->modele = null;

                $modifications_participants = [
                    'id_tache_organisateur' => empty($this->modele->parent_id) || !empty($this->modele->exception_recurrence) ? $this->modele->id : $this->modele->parent_id,
                    'statut_participant' => $participant['statut_participant'] ?? null,
                ];

                if(isset($participant['type_element'], $participant['element_id'])){

                    $modifications_participants['element_id'] = $participant['element_id'];
                    $modifications_participants['champ_email'] = $participant['champ_email'];
                    $modifications_participants['type_element'] = $participant['type_element'];
                }
                else
                    $modifications_participants['adresse_email'] = $participant['adresse_email'];

                $this->management_participants->enregistre($modifications_participants);
            }
        }

        return true;
    }

    /**
     * 
     * Supprime les anciens participants d'une tâche. Si elle est récurrente, supprime les participants sur les occurrences.
     * @return array
     */
    private function suppression_participants($participants_a_supprimer){

        $participants_externes_actuels = modele('tache_participants')
            ->whereIn('id', array_map(fn($p) => $p['id_participant'], $participants_a_supprimer['tache_participants']))
            ->get()
            ->keyBy('id');

        $participants_internes_actuels = modele('tache')
            ->avec_parents()
            ->whereIn('id', array_map(fn($p) => $p['id_tache'], $participants_a_supprimer['tache']))
            ->get()
            ->keyBy('id');

        if(!empty($participants_a_supprimer['tache_participants']))
            $this->management_participants->annulation_organisateur = true;

        if(!empty($participants_a_supprimer['tache']))
            $this->management_tache->annulation_organisateur = true;

        foreach($participants_a_supprimer['tache_participants'] as $participant){

            //Si on est sur une exception et qu'on enregistre seulement pour celle-ci, on enregistre un participant puis on le supprime pour indiquer la suppression de ce participant sur l'exception
            if(empty($this->modifier_recurrence) && $this->modele->exception_recurrence && $participant['id_tache_organisateur'] !== $this->modele->id) {

                $modifications_participant = clone $participants_externes_actuels[$participant['id_participant']];
                $modifications_participant = $modifications_participant->toArray();
                $modifications_participant['id_tache_organisateur'] = $this->modele->id;

                $this->management_participants->modele = null;
                $this->management_participants->enregistre($modifications_participant);
                $this->management_participants->supprime();

                continue;
            }

            $this->management_participants->modele = $participants_externes_actuels[$participant['id_participant']];
            $this->management_participants->supprime();
        }

        foreach($participants_a_supprimer['tache'] as $tache_participant){

            $this->management_tache->modele = $participants_internes_actuels[$tache_participant['id_tache']];

            if(!empty($this->modifier_recurrence))
                service('recurrence')->supprimer_recurrence($this->management_tache->modele->id, $this->management_tache->modele, $this->modifier_recurrence == 2);
            else
                $this->management_tache->supprime();
        }

        if(!empty($participants_a_supprimer['tache_participants']))
            $this->management_participants->annulation_organisateur = false;

        if(!empty($participants_a_supprimer['tache']))
            $this->management_tache->annulation_organisateur = false;

        return true;
    }

    /**
     * Recherche les participants liés à la tâche passée en paramètre
     * @param mixed $tache
     * @param $recherche
     * @return array
     */
    public function rechercher_participants_possibles(string $recherche) {
        
        $types_elements = management('tache_participants')->champ('type_element')->modele->contenu;
        $types_elements = array_unique(array_merge(['utilisateur'], array_map(fn($type) => $type->type_element, json_decode($types_elements))));
        
        $this->champs_emails_par_type = Champ_libre::select('nom_sql', 'type_element')
            ->whereIn('type_element', $types_elements)
            ->where('format_champ','email')
            ->where('type', 0)
            ->get()
            ->groupBy('type_element');

        $participants_possibles = array();

        foreach($types_elements as $type_element) {

            if (!isset($this->champs_emails_par_type[$type_element]))
                continue;
            
            $participants_possibles = array_merge(
                $participants_possibles,
                $this->recuperer_participants_possibles($recherche, $type_element)
            );
        }

        return $participants_possibles;
    }

    /**
     * Génère et exécute la requête SQL qui récupère les résultats de la recherche rechercher_participants_possibles
     * @param array $tache
     * @param string $recherche
     * @param string $type_element
     * @return array{affichage_pour_recherche: string, champ_email: string, element_id: int, type_element: string}
     */
    public function recuperer_participants_possibles(string $recherche, string $type_element){

        $participants_possibles = array();
        $champs_emails_type_element = $this->champs_emails_par_type[$type_element];

        if (empty($this->cases_sql[$type_element])) {

            $this->cases_sql[$type_element] = "CASE ";

            foreach ($champs_emails_type_element as $champ) {

                $this->cases_sql[$type_element] .= "WHEN " . $champ->nom_sql . " LIKE '%$recherche%' THEN '" . $champ->nom_sql . "' ";
            }

            $this->cases_sql[$type_element] .= "END as champ_email";
        }

        $elements = modele($type_element)->selectRaw('*, ' . $this->cases_sql[$type_element]);
        $recherche_fulltext = str_replace('@', ' ', $recherche);
        $recherches = explode(' ', $recherche_fulltext);
        $recherche_booleenne = implode(' ', array_map(fn($r) => '+' . $r . '*', $recherches));

        $elements = $elements
            ->where(function ($where) use ($champs_emails_type_element, $recherche, $recherche_booleenne) {

                foreach ($champs_emails_type_element as $champ) {

                    $where->orWhere($champ->nom_sql, 'LIKE', '%' . $recherche . '%');
                    
                }
                
                $where->orWhereRaw("MATCH(chaine_tags_recherche) AGAINST (? IN BOOLEAN MODE)", [$recherche_booleenne]);
            });

        $elements = $elements->get();

        foreach ($elements as $element) {

            if(!isset($element->champ_email))
                $element->champ_email = $champs_emails_type_element->first()->nom_sql;

            $participants_possibles[] = [
                'type_element' => $type_element,
                'element_id' => $element->id,
                'champ_email' => $element->champ_email,
                'adresse_email' => $element->{$element->champ_email},
                'statut_participant' => $element->statut_participant,
                'affichage_pour_recherche' => management($type_element, $element->id, $element)->affichage_pour_select(),
            ];
        }

        return $participants_possibles;
    }

    protected function condition_exception_recurrence($modifications) {

        $nombre_modifications_champs_synchronises = count(array_filter($modifications, function($valeur, $nom_champ) use ($modifications) {

            if((!empty($modifications['participant']) || !empty($this->modele->participant) || empty($valeur)) && $nom_champ == 'participants') 
                return false;
            else if($nom_champ == 'participants') {

                $modifications_participants = $this->recuperer_modifications_participants();
                return !empty($modifications_participants['creation']) || 
                        !empty($modifications_participants['suppression']['tache']) || 
                        !empty($modifications_participants['suppression']['tache_participants']);
            }

            return in_array($nom_champ, Variables::$champs_tache_synchronises) && (empty($this->modele->$nom_champ) || $valeur != $this->modele->$nom_champ);
        }, ARRAY_FILTER_USE_BOTH));

        if(!isset($modifications['modifier_recurrence']) && !isset($this->modele->tache_parent) && !isset($this->tache_enfant) && isset($this->modele->parent_id) && 
            $nombre_modifications_champs_synchronises > 0)
            return true;

        return false;
    }
}
