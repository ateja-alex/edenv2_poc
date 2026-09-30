<?php

namespace App\Eden\Managements\Services;

use App\Eden\Variables;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class Recurrence_service {

    public $occurrences_personnalisees = null;
    public $taches_organisateur = null;
    public $organisateur = false;
    public $participants = null;
    
    protected $service_microsoft_calendrier = null;
    protected $service_google_calendrier = null;
    public $modification_infos_recurrence = false;
    public $modification_dates_recurrence = false;
    protected $modele_avant_tache_parent = null;
    protected $tache_parent = null;
    protected $date_debut = null;
    protected $intervalle = null;
    protected $exceptions_a_recreer = array();
    
    public $modification_recurrence = false;
    protected $ancien_parent = null;
    protected $ancienne_recurrence = null;
    public $date_fin_ancienne_recurrence = '';
    private $date_de_fin_utilisable_recurrence = null;

    /**
     *
     * Permet d'enregistrer toutes les occurrences d'une récurrence
     *
     */
    public function enregistre_recurrence(&$modifications, $modele, $passer_date_de_debut = false) {

        $recurrence_taches_a_creer = $this->occurrences_personnalisees;

        if(!isset($recurrence_taches_a_creer))
            $recurrence_taches_a_creer = $this->recurrence_dates_taches_a_creer($modifications['infos_recurrence'], $modele->getAttributes(), $passer_date_de_debut);

        $modifications_triees = $this->formate_donnees($modele->getAttributes());
        $this->modele_avant_tache_parent = clone $modele;

        $ids_taches = [];

        if($this->organisateur === true)
            $this->taches_organisateur = array('parent' => clone $modele);

        $modifications_triees['parent_id'] = $modele->id;
        
        $management_tache = management('tache');
        $management_tache->tache_enfant = true;

        // On crée les tâches de la récurrence
        foreach($recurrence_taches_a_creer as $index => $occurrence){

            $date_debut_occurrence = date_create_from_format('Y-m-d H:i:s', $occurrence['date_de_debut'])->format('Y-m-d');

            if(isset($this->taches_organisateur[$date_debut_occurrence]) && $this->taches_organisateur[$date_debut_occurrence]['exception_recurrence']){

                $this->exceptions_a_recreer[$date_debut_occurrence] = $this->taches_organisateur[$date_debut_occurrence];
                $this->exceptions_a_recreer[$date_debut_occurrence]->parent_id = $modele->id;
            }
            else if(isset($this->taches_organisateur[$date_debut_occurrence]))
                $occurrence = array_merge($occurrence, [
                    'id_tache_organisateur' => $this->taches_organisateur[$date_debut_occurrence]['id'],
                    'id_commun_taches_participants' => $this->taches_organisateur[$date_debut_occurrence]['id_commun_taches_participants'],
                ]);

            if(isset($this->exceptions_a_recreer[$date_debut_occurrence])){

                $exception = $this->exceptions_a_recreer[$date_debut_occurrence];

                $modifications_a_reprendre = array_filter(
                    $modifications_triees, 
                    fn($m) => in_array($m, Variables::$champs_tache_synchronises) || in_array($m, ['parent_id', 'affectation', 'participant']), 
                    ARRAY_FILTER_USE_KEY
                );

                $modifications_a_reprendre['id_tache_organisateur'] = $exception['id'];

                $this->retraite_modifications_exception($modifications_a_reprendre, $exception);

                $modifications_occurrence = array_merge(
                    $this->formate_donnees($exception->toArray(), true),
                    $modifications_a_reprendre
                );
            }
            else {

                $modifications_occurrence = $modifications_triees;

                foreach($occurrence as $nom_champ => $valeur){

                    $modifications_occurrence[$nom_champ] = $valeur;
                }
            }

            $management_tache->modele = null;
            $management_tache->enregistre($modifications_occurrence);

            if($this->organisateur === true && !empty($management_tache->modele))
                $this->taches_organisateur[$date_debut_occurrence] = $management_tache->modele;

            $ids_taches[] = $management_tache->modele->id;
        }

        if(!empty($ids_taches))
            management('tache')->trigger_applicatif_elements_multiples($ids_taches);

        $this->instancie_services_synchro_externes();

        if(!empty($this->service_microsoft_calendrier) && $this->service_microsoft_calendrier->condition_synchro_rdv($modele, $modifications) && empty($modele->participant))
            return $this->creer_recurrence_sur_calendrier_microsoft($modifications, $modele);
        else if(!empty($this->service_google_calendrier) && $this->service_google_calendrier->condition_synchro_rdv($modele, $modifications) && empty($modele->participant))
            return $this->creer_recurrence_sur_calendrier_google($modifications, $modele);

        return true;
    }

    /**
     *
     * Permet de modifier toutes les occurrences d'une récurrence
     *
     */
    public function modifier_recurrence(&$modifications, $modele, $modele_avant) {

        $this->instancie_services_synchro_externes();

        $this->modification_infos_recurrence = false;
        $this->modification_dates_recurrence = false;

        $taches_recurrence = $this->taches_recurrences_pour_modification($modele->tache_parent ? $modele->id : $modele->parent_id);
        $this->tache_parent = $taches_recurrence->first(function($tache){ return $tache->tache_parent == true;});
        $this->modele_avant_tache_parent = clone $this->tache_parent;
        
        if($this->organisateur === true)
            $this->taches_organisateur = array('parent' => clone $this->tache_parent);

        // Si la tâche modifiée est la parente ou la première tâche de la série, il faut considérer que modifier_recurrence = 2 (= modifier toute la série)
        if(!empty($modifications['modifier_recurrence']) && $modifications['modifier_recurrence'] !== 2 && ($this->tache_parent->id === $modele->id || 
            $taches_recurrence->first(function($tache){ return empty($tache->tache_parent);})->id === $modele->id))
            $modifications['modifier_recurrence'] = 2;

        // On vérifie si les champs de la récurrence ont été modifié
        foreach($modifications['infos_recurrence'] as $nom_champ => $valeur){

            if(!empty($modifications['modele_recurrence']) && $modifications['modele_recurrence']->$nom_champ != $valeur && !in_array($nom_champ, ['date_de_debut','date_de_fin']))
                $this->modification_infos_recurrence = true;
            else if(!empty($modifications['modele_recurrence']) && $modifications['modele_recurrence']->$nom_champ != $valeur)
                $this->modification_dates_recurrence = true;
        }

        if(isset($modele->affectation) && !isset($modifications['affectation']))
            $modifications['affectation'] = $modele->affectation;

        if($this->modification_infos_recurrence && $modifications['modifier_recurrence'] != 1){

            return $this->recreer_recurrence($modifications, $modele);
        }
        else if($this->modification_dates_recurrence && $modifications['modifier_recurrence'] != 1){

            $modifications_tache = sizeof(array_filter($modifications,function($modif,$index) use ($modele_avant){
                return !in_array($index,['modele_recurrence','infos_recurrence','modifier_recurrence'])
                    && ((in_array($index,['date_de_debut','date_de_fin']) && strtotime($modif) != strtotime($modele_avant->$index)) ||
                        (!in_array($index,['date_de_debut','date_de_fin']) && $modif != $modele_avant->$index));
            },ARRAY_FILTER_USE_BOTH)) > 0;

            $management_recurrence = management('tache_recurrence', $modifications['modele_recurrence']->id, $modifications['modele_recurrence']);

            if(isset($this->occurrences_personnalisees))
                $management_recurrence->occurrences_personnalisees = $this->occurrences_personnalisees;

            if($this->organisateur === true)
                $management_recurrence->organisateur = $this->organisateur;
            else if(!empty($this->taches_organisateur) && $modele->participant)
                $management_recurrence->taches_organisateur = $this->taches_organisateur;

            $management_recurrence->enregistre([
                'date_de_debut' => $modifications['infos_recurrence']['date_de_debut'],
                'date_de_fin' => $modifications['infos_recurrence']['date_de_fin']
            ]);

            if(!empty($management_recurrence->taches_organisateur))
                $this->taches_organisateur = $management_recurrence->taches_organisateur;

            // Si la date de début de la récurrence a changé, on met à jour le modèle de la tâche parente
            if(isset($management_recurrence->changement_enregistrement['date_de_debut']))
                $this->tache_parent = modele('tache')->avec_parents()->where('id', $this->tache_parent->id)->first();
            
            if(!$modifications_tache) {

                if(!empty($this->service_microsoft_calendrier) && $this->service_microsoft_calendrier->condition_synchro_rdv($this->tache_parent, $modifications) && empty($this->tache_parent->participant))
                    return $this->modifier_recurrence_sur_calendrier_microsoft($modifications, $this->tache_parent);
                else if(!empty($this->service_google_calendrier) && $this->service_google_calendrier->condition_synchro_rdv($this->tache_parent, $modifications) && empty($this->tache_parent->participant))
                    return $this->modifier_recurrence_sur_calendrier_google($modifications, $this->tache_parent);

                return true;
            }

            if(strtotime($modele->date_de_debut) < strtotime($modifications['infos_recurrence']['date_de_debut']))
                $modele = $modele->tache_parent ? $management_recurrence->tache_parent : $management_recurrence->premiere_occurrence;
            else if(strtotime($modele->date_de_debut) > strtotime($modifications['infos_recurrence']['date_de_fin']))
                $modele = $modele->tache_parent ? $management_recurrence->tache_parent : $management_recurrence->derniere_occurrence;
        }
        
        if(empty($modifications['modifier_recurrence']))
            return true;
        
        $this->date_debut = date_create_from_format('Y-m-d H:i:s', $modele->date_de_debut);
        $this->intervalle = date_diff($this->date_debut, date_create_from_format('Y-m-d H:i:s', $modele->date_de_fin));

        //Si la tâche n'est pas la parente, on modifie à partir de celle-ci
        if(!$modele->tache_parent && $modifications['modifier_recurrence'] == 1)
            $taches_recurrence = $taches_recurrence->where('date_de_debut', '>=', $modele->date_de_debut);

        if($modifications['modifier_recurrence'] == 2)
            $this->modifier_toute_recurrence($modifications, $taches_recurrence, $modele_avant);
        else
            $this->modifier_partie_recurrence($modifications, $modele, $modele_avant, $taches_recurrence);

        management('tache')->trigger_applicatif_elements_multiples($taches_recurrence->pluck('id')->toArray());

        if(!empty($this->service_microsoft_calendrier) && $this->service_microsoft_calendrier->condition_synchro_rdv($modele, $modifications) && empty($modele->participant))
            return $this->modifier_recurrence_sur_calendrier_microsoft($modifications, $this->tache_parent);
        else if(!empty($this->service_google_calendrier) && $this->service_google_calendrier->condition_synchro_rdv($modele, $modifications) && empty($modele->participant))
            return $this->modifier_recurrence_sur_calendrier_google($modifications, $this->tache_parent);

        return true;
    }

    /*
     *
     * Lorsqu'on modifie une récurrence, si les infos de la récurrence sont modifiées, cette fonction est appelée pour supprimer et récréer la récurrence
     * 
     */
    private function recreer_recurrence(&$modifications, $modele){

        $this->supprimer_recurrence($modele->id, $modele, true);

        //On récrée la tâche parent
        $management_tache_parent = management('tache');

        if(isset($this->occurrences_personnalisees))
            $management_tache_parent->occurrences_personnalisees = $this->occurrences_personnalisees;

        $modifications_tache_parent = $this->formate_donnees($this->tache_parent->getAttributes(), true, true);

        if(isset($modifications_tache_parent['id_microsoft']))
            unset($modifications_tache_parent['id_microsoft']);

        $modifications_tache_parent['infos_recurrence'] = $modifications['infos_recurrence'];
        $modifications_tache_parent['infos_recurrence']['date_de_debut'] = date('Y-m-d',strtotime($this->tache_parent->date_de_debut));
        
        if(isset($this->participants)){

            array_walk($this->participants, fn(&$p) => $p['id_tache'] = $p['id_participant'] = null);
            $modifications_tache_parent['participants'] = $this->participants;
        }

        return $management_tache_parent->enregistre($modifications_tache_parent);
    }

    /*
     *
     * Lorsqu'on modifie une récurrence, cette fonction est appelée si modifier_recurrence = 2 (modifier toute la série)
     * 
     */
    private function modifier_toute_recurrence(&$modifications, $taches_a_modifier, $modele_avant) {

        $management_tache = management('tache');
        $management_tache->tache_enfant = true;

        // On enlève les champs qu'on ne doit pas reprendre sur les autres occurrences
        $modifications_triees = $this->formate_donnees($modifications);
        
        $modification_heure_debut_ou_fin = $this->modification_heure_debut_ou_fin($modifications, $modele_avant);

        //On enregistre les modifications sur les tâches à traiter
        foreach($taches_a_modifier as $index => $tache_a_modifier){

            $management_tache->modele = $tache_a_modifier;
            $modifications_occurrence = $modifications_triees;

            if(isset($this->occurrences_personnalisees[$tache_a_modifier->id_microsoft]))
                $modifications_a_reprendre = $this->occurrences_personnalisees[$tache_a_modifier->id_microsoft];
            else if(isset($this->occurrences_personnalisees[$tache_a_modifier->id_google]))
                $modifications_a_reprendre = $this->occurrences_personnalisees[$tache_a_modifier->id_google];

            if(isset($modifications_a_reprendre)){

                foreach ($modifications_a_reprendre as $nom_champ => $modification_a_reprendre) {

                    $modifications_occurrence[$nom_champ] = $modification_a_reprendre;
                }
            }

            if(!isset($modifications_occurrence['date_de_debut']))
                $modifications_occurrence['date_de_debut'] = substr($tache_a_modifier->date_de_debut, 0,11) . $this->date_debut->format('H:i:s');

            if(!isset($modifications_occurrence['date_de_fin']))
                $modifications_occurrence['date_de_fin'] = date_create_from_format('Y-m-d H:i:s', $modifications_occurrence['date_de_debut'])->add($this->intervalle)->format('Y-m-d H:i:s');
            
            if(!$tache_a_modifier->tache_parent && $tache_a_modifier->exception_recurrence && !$modification_heure_debut_ou_fin)
                $this->retraite_modifications_exception($modifications_occurrence, $tache_a_modifier);
            else if(!$tache_a_modifier->tache_parent && $tache_a_modifier->exception_recurrence)
                $modifications_occurrence['exception_recurrence'] = null;

            $management_tache->enregistre($modifications_occurrence);

            if($this->organisateur === true)
                $this->taches_organisateur[date_create_from_format('Y-m-d H:i:s', $management_tache->modele->date_de_debut)->format('Y-m-d')] = $management_tache->modele;
        }
    }

    /*
     *
     * Lorsqu'on modifie une récurrence, cette fonction est appelée si modifier_recurrence = 2 (modifier toute la série)
     * 
     */
    private function modifier_partie_recurrence(&$modifications, $modele, $modele_avant, $taches_recurrence) {

        $this->modification_recurrence = true;
        $this->ancienne_recurrence = $modifications['modele_recurrence']->toArray();
        $this->ancien_parent = $this->tache_parent;

        //La tâche devient le parent
        $management_tache = management('tache');
        $management_tache->tache_enfant = true;
        $management_tache->modele = clone $modele;
        $donnees = ['tache_parent' => 1, 'parent_id' => null];
        $modification_heure_debut_ou_fin = $this->modification_heure_debut_ou_fin($modifications, $modele_avant);

        //On récupère et formate les exceptions pour pouvoir les recréer par la suite
        if(!$this->modification_infos_recurrence && !$modification_heure_debut_ou_fin)
            $this->exceptions_a_recreer = $taches_recurrence
                ->filter(fn($tache) => $tache->exception_recurrence)
                ->mapWithKeys(fn($tache) => [date_create_from_format('Y-m-d H:i:s', $tache->date_de_debut)->format('Y-m-d') => $tache]);
              
        if($modifications['infos_recurrence']['date_de_debut'] !== $this->ancienne_recurrence['date_de_debut']){

            $date_debut_tache = date_create_from_format('Y-m-d H:i:s', $modifications['infos_recurrence']['date_de_debut'] . ' ' .  substr($modele->date_de_debut, 11, 8));
            $donnees['date_de_debut'] = $date_debut_tache->format('Y-m-d H:i:s');
            $donnees['date_de_fin'] = $date_debut_tache->add($this->intervalle)->format('Y-m-d H:i:s');
        }
        
        $management_tache->enregistre_modele($donnees);
        $this->tache_parent = $management_tache->modele;
        $management_tache->modele = null;

        //On crée la nouvelle récurrence
        $management_recurrence = management('tache_recurrence');
        $nouvelle_recurrence = $modifications['modele_recurrence']->toArray();
        $nouvelle_recurrence = array_merge($nouvelle_recurrence, $modifications['infos_recurrence']);
        $nouvelle_recurrence = $this->formate_donnees($nouvelle_recurrence, true);
        $nouvelle_recurrence['date_de_debut'] = !empty($date_debut_tache) ? 
            $date_debut_tache->format('Y-m-d') : $this->date_debut->format('Y-m-d');

        if(isset($modifications['infos_recurrence']['date_de_fin']))
            $nouvelle_recurrence['date_de_fin'] = $modifications['infos_recurrence']['date_de_fin'];

        $nouvelle_recurrence['tache_parent_id'] = $this->tache_parent->id;
        $management_recurrence->enregistre($nouvelle_recurrence);

        $management_recurrence->charge_valeurs_champs_multiselection();
        $modifications['infos_recurrence'] = $management_recurrence->modele->toArray();
        $modifications['modele_recurrence'] = $management_recurrence->modele;

        //On génère les tâches de la nouvelle récurrence
        $this->enregistre_recurrence($modifications, $this->tache_parent);
        
        $this->date_fin_ancienne_recurrence = date_create($nouvelle_recurrence['date_de_debut'])
                ->modify("-1 day")
                ->format('Y-m-d');

        // On met fin à l'ancienne récurrence
        management('tache_recurrence', $this->ancienne_recurrence['id'])->enregistre([
            'date_de_fin' => $this->date_fin_ancienne_recurrence
        ]);
    }

    /*
     *
     * On supprime les événements postérieurs à celui passé en paramètre faisant partie de la même récurrence
     *
     */
    public function supprimer_recurrence($id_tache, $modele_tache = null, $toute_la_serie = false){

        if($modele_tache === null)
            $tache = modele('tache')->avec_parents()->where('id', $id_tache)->first();
        else
            $tache = $modele_tache;
        
        // On récupère toutes les tâches de la récurrence pour sélectionner celles qu'on veut supprimer par la suite
        $taches_recurrence = modele('tache')->avec_parents()
            ->where(function($r) use ($tache) {
                $r->where('parent_id', $tache->tache_parent ? $tache->id : $tache->parent_id)
                    ->orWhere('id', $tache->tache_parent ? $tache->id : $tache->parent_id);
            })
            ->orderBy('date_de_debut')
            ->get();

        $taches_a_supprimer = $taches_recurrence;

        $tache_parent = $tache->tache_parent ? $tache : $taches_recurrence->first(function($tache){ return $tache->tache_parent == true;});
        $recurrence = modele('tache_recurrence')->where('tache_parent_id', $tache_parent->id)->first();
        
        if($tache_parent->id === $tache->id || $taches_recurrence->first(function($tache){ return empty($tache->tache_parent);})->id === $tache->id)
            $toute_la_serie = true;

        if(!$toute_la_serie)
            $taches_a_supprimer = $taches_recurrence->where('date_de_debut', '>=', $tache->date_de_debut);
        
        $ids_taches_a_supprimer = $taches_a_supprimer->pluck('id')->toArray();

        if(!$tache->participant){

            $participants_internes = modele('tache')
                ->avec_parents()
                ->whereIn('id_tache_organisateur', $ids_taches_a_supprimer)
                ->get()
                ->groupBy('id_tache_organisateur');

            $participants_externes = modele('tache_participants')
                ->whereIn('id_tache_organisateur', $ids_taches_a_supprimer)
                ->get()
                ->groupBy('id_tache_organisateur');

            $management_participant = management('tache_participants');
        }

        $management_tache = management('tache');
        $management_tache->tache_enfant = true;
            
        foreach ($taches_a_supprimer as $tache_a_supprimer) {

            $management_tache->modele = $tache_a_supprimer;
            $management_tache->modele->exists = true;
            $management_tache->supprime();

            if(isset($participants_internes[$tache_a_supprimer->id])) {

                $management_tache->annulation_organisateur = true;

                foreach ($participants_internes[$tache_a_supprimer->id] as $participant_interne) {

                    $management_tache->modele = $participant_interne;
                    $management_tache->modele->exists = true;
                    $management_tache->supprime();
                }

                $management_tache->annulation_organisateur = false;
            }

            if(isset($participants_externes[$tache_a_supprimer->id])) {
                
                $management_participant->annulation_organisateur = true; 

                foreach ($participants_externes[$tache_a_supprimer->id] as $participant_externe) {

                    $management_participant->modele = $participant_externe;
                    $management_participant->modele->exists = true;
                    $management_participant->supprime();
                }
                
                $management_participant->annulation_organisateur = false;   
            }
        }

        $ids_taches_trigger = $taches_a_supprimer->pluck('id')->toArray();

        if(isset($participants_externes) && $participants_externes->isNotEmpty())
            $ids_taches_trigger = array_merge($ids_taches_trigger, $participants_internes->pluck('id')->toArray());

        $management_tache->trigger_applicatif_elements_multiples($ids_taches_trigger);

        if(isset($participants_externes) && $participants_externes->isNotEmpty())
            $management_participant->trigger_applicatif_elements_multiples($participants_externes->pluck('id')->toArray());

        if($toute_la_serie)
            management('tache_recurrence', $recurrence->id, $recurrence)->supprime();
        else
            management('tache_recurrence', $recurrence->id, $recurrence)->enregistre(['date_de_fin' => date('Y-m-d', strtotime($tache->date_de_debut))]);

        $this->instancie_services_synchro_externes();

        if(!empty($this->service_microsoft_calendrier) && $this->service_microsoft_calendrier->condition_synchro_rdv($tache_parent))
            return $this->supprimer_recurrence_sur_calendrier_externe($tache, $tache_parent, $recurrence, $toute_la_serie, 'microsoft');
        else if(!empty($this->service_google_calendrier) && $this->service_google_calendrier->condition_synchro_rdv($tache_parent))
            return $this->supprimer_recurrence_sur_calendrier_externe($tache, $tache_parent, $recurrence, $toute_la_serie, 'google');

        return true;
    }
    public function recurrence_dates_taches_a_creer($infos_recurrence, $modele, $passer_date_de_debut = false){

        $frequence = $infos_recurrence['frequence'];

        $correspondance_jours = [

            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday',
        ];

        switch($infos_recurrence['type_frequence']){

            case 1:
                $type_frequence = 'days';
                break;
            case 2:
                $type_frequence = 'weeks';
                break;
            case 3:
                $type_frequence = 'months';
                break;
            case 4:
                $type_frequence = 'years';
                break;
        }

        $this->date_de_fin_utilisable_recurrence($infos_recurrence);

        //On initialise les dates de la première occurrence
        $date_debut_recurrence = date_create_from_format('Y-m-d', formate_date('Y-m-d H:i:s', $infos_recurrence['date_de_debut']));
        $date_derniere_occurence = date_create_from_format('Y-m-d H:i:s', formate_date('Y-m-d H:i:s', $modele['date_de_debut']));
        $date_fin_occurence = date_create_from_format('Y-m-d H:i:s', formate_date('Y-m-d H:i:s', $modele['date_de_fin']));

        //On calcule la différence entre la date de début et la date de fin
        $intervalle_debut_fin = $date_derniere_occurence->diff($date_fin_occurence);

        if(!empty($modele['participant']) && $date_debut_recurrence->format('Y-m-d') !== $date_derniere_occurence->format('Y-m-d')){

            $date_derniere_occurence = date_create_from_format('Y-m-d H:i:s', $date_debut_recurrence->format('Y-m-d') . ' ' . $date_derniere_occurence->format('H:i:s'));
            $date_fin_occurence = clone $date_derniere_occurence;
            $date_fin_occurence->add($intervalle_debut_fin);
        }


        // S'il y a une fréquence associée au jour concerné quand on a une récurrence mensuelle ou annuelle (ex : le troisième jeudi),
        // on récupère le nombre ordinal associé
        if(isset($infos_recurrence['frequence_jour_concerne'])){

            switch($infos_recurrence['frequence_jour_concerne']){

                case 1:
                    $frequence_jour = 'first';
                    break;
                case 2:
                    $frequence_jour = 'second';
                    break;
                case 3:
                    $frequence_jour = 'third';
                    break;
                case 4:
                    $frequence_jour = 'fourth';
                    break;
                case 5:
                    $frequence_jour = 'last';
                    break;
                default:
                    $frequence_jour = null;
                    break;
            }

            if(!empty($infos_recurrence['jours_concernes']))
                $jour_autorise = $correspondance_jours[Arr::first($infos_recurrence['jours_concernes'])];
            else
                $jour_autorise = $correspondance_jours[$date_derniere_occurence->format('N')];
        } else
            $frequence_jour = null;

        

        //Si on a une récurrence hebdomadaire qui a une date de début dont le jour ne respecte pas les jours autorisés, on cherche le prochain jour autorisé
        if($type_frequence == 'weeks' && count($infos_recurrence['jours_concernes']) > 0 && !in_array($date_derniere_occurence->format('N'), $infos_recurrence['jours_concernes'])){

            $jours_choisis = $infos_recurrence['jours_concernes'];

            $jours_superieurs = array_filter($jours_choisis, function($jour) use ($date_debut_recurrence) {
                return $jour > $date_debut_recurrence->format('N');
            });

            if (!empty($jours_superieurs))
                $prochain_jour = min($jours_superieurs);
            else
                $prochain_jour = min($jours_choisis);
            
            $prochain_jour = $correspondance_jours[$prochain_jour];
            
            if(!in_array($date_debut_recurrence->format('N'), $infos_recurrence['jours_concernes']))
                $date_debut_recurrence->modify('next ' . $prochain_jour . ' +' . $date_derniere_occurence->format('H') .
                    ' hours' . $date_derniere_occurence->format('i') . ' minutes');

            $date_derniere_occurence = clone $date_debut_recurrence;
            $date_fin_occurence = clone $date_derniere_occurence;
            $date_fin_occurence->add($intervalle_debut_fin);
        }
        else if($type_frequence == 'weeks' && count($infos_recurrence['jours_concernes']) == 0)
            $infos_recurrence['jours_concernes'][0] = $date_derniere_occurence->format('N');

        if(in_array($type_frequence, ['months', 'years']) && isset($jour_autorise, $frequence_jour)){

            $date_comparaison_mois = clone($date_derniere_occurence);
            $date_comparaison_mois = $date_comparaison_mois->modify($frequence_jour . ' ' . $jour_autorise . ' of this month')->format('Y-m-d');

            //Si on a une récurrence mensuelle qui a une date de début dont le jour n'est pas un jour autorisé, on regarde s'il est possible de le trouver dans ce mois-ci
            if(strtotime($date_derniere_occurence->format('Y-m-d')) <= strtotime($date_comparaison_mois)){

                $date_derniere_occurence = $date_derniere_occurence->modify(
                    $frequence_jour . ' ' . $jour_autorise . ' of this month +' . $date_derniere_occurence->format('H')
                    . ' hours' . $date_derniere_occurence->format('i') . ' minutes'
                );
                $date_fin_occurence = $date_fin_occurence->modify(
                    $frequence_jour . ' ' . $jour_autorise . ' of this month +' . $date_fin_occurence->format('H')
                    . ' hours' . $date_fin_occurence->format('i') . ' minutes'
                );
            }
            elseif(strtotime($date_derniere_occurence->format('Y-m-d')) > strtotime($date_comparaison_mois))
                $passer_date_de_debut = true;
        }

        if(empty($passer_date_de_debut)){

            $dates = [
                [
                    'date_de_debut' => $date_derniere_occurence->format('Y-m-d H:i:s'),
                    'jour' => $date_derniere_occurence->format('l'),
                    'date_de_fin' => $date_fin_occurence->format('Y-m-d H:i:s'),
                ]
            ];
        }
        else
            $dates = array();

        // Tant que la date de la dernière occurrence ne dépasse pas celle de fin de la récurrence, on continue à calculer les prochaines dates
        // ATTENTION : une boucle = une occurrence, par exemple dans le cas d'une récurrence hebdomadaire, on ne calcule pas toutes les occurrences
        // d'une semaine par boucle, mais une occurrence par boucle
        while($date_derniere_occurence->getTimestamp() < $this->date_de_fin_utilisable_recurrence->getTimestamp()){

            if($type_frequence == 'weeks'){

                // On cherche l'index du jour actuel dans le tableau des jours autorisés
                $jour_actuel = array_search($date_derniere_occurence->format('N'), $infos_recurrence['jours_concernes']);

                //On prend le jour suivant dans le tableau, sinon on passe à la semaine suivante
                if(isset($infos_recurrence['jours_concernes'][$jour_actuel+1]))
                    $date_derniere_occurence->modify('next ' . $correspondance_jours[$infos_recurrence['jours_concernes'][$jour_actuel+1]] . ' +' .
                        $date_derniere_occurence->format('H') . ' hours' . $date_derniere_occurence->format('i') . ' minutes');
                else{

                    $date_derniere_occurence->modify('previous ' . $correspondance_jours[$infos_recurrence['jours_concernes'][0]] . ' +' . $frequence . ' ' .
                        $type_frequence . ' ' . $date_derniere_occurence->format('H') . ' hours ' . $date_derniere_occurence->format('i') . ' minutes');
                }
            }
            else if($type_frequence == 'months' && isset($frequence_jour)){

                $date_derniere_occurence->modify($frequence_jour . ' ' . $jour_autorise .
                    ' of +' . $frequence . ' ' . $type_frequence . ' ' . $date_derniere_occurence->format('H') .
                    ' hours' . $date_derniere_occurence->format('i') . ' minutes');
            }
            else if($type_frequence == 'years' && isset($frequence_jour)){

                // Je suis obligé de transformer le nombre d'années en nombre de mois, car le calcul d'un troisième ou quatrième jeudi d'un mois est faux quand on fait +1 year
                $date_derniere_occurence->modify($frequence_jour . ' ' . $jour_autorise .
                    ' of +' . ($frequence * 12) . ' months ' . $date_derniere_occurence->format('H') .
                    ' hours' . $date_derniere_occurence->format('i') . ' minutes');
            }
            else
                $date_derniere_occurence->modify('+' . $frequence . ' ' . $type_frequence);

            //Si l'occurrence dépasse la date de fin, on sort de la boucle sans l'ajouter au tableau
            if($date_derniere_occurence->getTimestamp() > $this->date_de_fin_utilisable_recurrence->getTimestamp())
                break;

            $date_prochaine_occurence = clone $date_derniere_occurence;

            $dates[] = [
                'date_de_debut' => $date_derniere_occurence->format('Y-m-d H:i:s'),
                'jour' => $date_derniere_occurence->format('l'),
                'date_de_fin' => $date_prochaine_occurence->add($intervalle_debut_fin)->format('Y-m-d H:i:s'),
            ];
        }

        return $dates;
    }

    /*
     *
     * Permet d'enlever des modifications les champs qui ne doivent pas être repris
     *
     */
    public function formate_donnees($modifications, $nouvelle_recurrence = false, $nouvelle_tache_parent = false){

        $champs_a_eviter = [
            'id',
            'inactif',
            'modifie_le',
            'modifie_par',
            'cree_le',
            'cree_par',
            'cle_externe',
            'chaine_tags_recherche',
            'chaine_affichage',
            'modele_recurrence',
            'modifier_recurrence',
            'tache_parent',
        ];

        if($nouvelle_tache_parent === false)
            $champs_a_eviter = array_merge($champs_a_eviter, ['infos_recurrence','id_microsoft', 'id_google']);
        else
            $champs_a_eviter = array_merge($champs_a_eviter, ['parent_id']);

        if($nouvelle_recurrence === false)
            $champs_a_eviter = array_merge($champs_a_eviter, ['date_de_debut','date_de_fin', 'id_commun_taches_participants'], array_keys(fonctionnalite('champs_non_repris_tache_parent')));

        foreach($modifications as $champ => $valeur){

            if(in_array($champ, $champs_a_eviter))
                unset($modifications[$champ]);
        }

        return $modifications;
    }

    public function creer_recurrence_sur_calendrier_microsoft($modifications, $modele){

        $management_tache_parent = management('tache', $modele->id, $modele);

        $createur_tache = modele('utilisateur')
            ->select('email', 'id_microsoft', 'id')
            ->where('id', $modele->affectation)
            ->where('synchronisation_calendrier_outlook', 1)
            ->first();

        if(empty($createur_tache))
            return true;

        $infos = $this->prepare_donnees_microsoft($modifications, $modele, $management_tache_parent, $createur_tache);

        if(!empty($modele->id_microsoft) && !empty($this->ancien_parent)){

            $this->service_microsoft_calendrier->supprimer_evenement($modele->id_microsoft, $createur_tache);
            $retour = $this->service_microsoft_calendrier->creer_evenement($infos, $createur_tache);
        }
        else if (!empty($modele->id_microsoft))
            $retour = $this->service_microsoft_calendrier->modifier_evenement($infos, $modele->id_microsoft, $createur_tache);
        else
            $retour = $this->service_microsoft_calendrier->creer_evenement($infos, $createur_tache);

        if($retour === false || is_string($retour))
            return $retour;

        if(empty($modele->id_microsoft) || !empty($this->ancien_parent)){

            $modifications_tache_parent = ['id_microsoft' => $retour->getId()];

            if(!empty($this->participants) && $modele->id_commun_taches_participants !== $retour->getICalUId())
                $modifications_tache_parent['id_commun_taches_participants'] = $retour->getICalUId();

            if(!empty($retour->getBody()->getContent()) && $modele->commentaire !== $retour->getBody()->getContent())
                $modifications_tache_parent['commentaire'] = $retour->getBody()->getContent();

            $management_tache_parent->enregistre_modele($modifications_tache_parent);
        }

        $this->date_de_fin_utilisable_recurrence($modifications['infos_recurrence']);

        return $this->lier_evenements_microsoft_eden($management_tache_parent->modele->id_microsoft, $modifications['infos_recurrence']['date_de_debut'] . ' 00:00:00', $this->date_de_fin_utilisable_recurrence->format('Y-m-d H:i:s'), $modele, $createur_tache);
    }

    public function creer_recurrence_sur_calendrier_google($modifications, $modele){

        $management_tache_parent = management('tache', $modele->id, $modele);
        $createur_tache = modele('utilisateur')->select('id', 'email')->where('id', $modele->affectation)->where('synchronisation_calendrier_google', 1)->first();

        if(empty($createur_tache))
            return true;

        $infos = $this->prepare_donnees_google($modifications, $modele, $management_tache_parent);

        if(!empty($modele->id_google) && empty($this->ancien_parent))
            $retour = $this->service_google_calendrier->modifier_evenement($infos, $modele->id_google, $createur_tache);
        else
            $retour = $this->service_google_calendrier->creer_evenement($infos, $createur_tache);

        if($retour === false || is_string($retour))
            return $retour;

        if(empty($modele->id_google) || !empty($this->ancien_parent))
            $management_tache_parent->enregistre_modele(['id_google' => $retour->id, 'id_commun_taches_participants' => $retour->iCalUId]);

        $this->date_de_fin_utilisable_recurrence($modifications['infos_recurrence']);

        if($modifications['infos_recurrence']['date_de_fin'] != $this->date_de_fin_utilisable_recurrence->format('Y-m-d'))
            $modifications['infos_recurrence']['date_de_fin'] = $this->date_de_fin_utilisable_recurrence->format('Y-m-d');

        return $this->service_google_calendrier->lier_evenements_eden($management_tache_parent->modele, $modifications['infos_recurrence'], $createur_tache, $this->exceptions_a_recreer);
    }

    private function lier_evenements_microsoft_eden($id_microsoft, $date_debut, $date_fin, $modele, $createur_tache){

        $occurrences_microsoft = $this->service_microsoft_calendrier->recuperer_occurrences_recurrence($id_microsoft, $date_debut, $date_fin, $createur_tache);

        if(is_string($occurrences_microsoft))
            return $occurrences_microsoft;
        
        $occurrences_eden = modele('tache')->where('parent_id', $modele->id)->get();
        $occurrences_microsoft_par_date = array();
        $exceptions_a_modifier = array();

        $management_tache = management('tache');
        
        foreach($occurrences_microsoft as $occurrence){

            $date_debut_utc = new \DateTime($occurrence['start']['dateTime'], new \DateTimeZone('UTC'));

            if(isset($occurrence['isAllDay']) && $occurrence['isAllDay'] === true)
                $date_debut_locale = $date_debut_utc->setTimezone(new \DateTimeZone('Europe/Paris'))->format('Y-m-d');
            else
                $date_debut_locale = $date_debut_utc->setTimezone(new \DateTimeZone('Europe/Paris'))->format('Y-m-d');

            $occurrences_microsoft_par_date[$date_debut_locale] = ['id_microsoft' => $occurrence['id'], 'id_commun_taches_participants' => $occurrence['iCalUId'], 'commentaire' => $occurrence['body']['content']];
        }

        foreach($occurrences_eden as $occurrence){

            $date_de_debut = date_create_from_format('Y-m-d H:i:s', $occurrence->date_de_debut)->format('Y-m-d');
            $occurrence_microsoft = $occurrences_microsoft_par_date[$date_de_debut];

            if(isset($this->exceptions_a_recreer[$date_de_debut])){

                $exceptions_a_modifier[] = [
                    'infos' => $management_tache->infos_evenement_microsoft($this->exceptions_a_recreer[$date_de_debut], $createur_tache),
                    'id_microsoft' => $occurrence_microsoft['id_microsoft'],
                ];
            }

            if($occurrence->commentaire === $occurrence_microsoft['commentaire'])
                unset($occurrence_microsoft['commentaire']);

            management('tache', $occurrence->id, $occurrence)->enregistre_modele($occurrence_microsoft);

            if($this->organisateur === true)
                $this->taches_organisateur[$date_de_debut] = $occurrence;

            unset($occurrences_microsoft_par_date[$date_de_debut]);
        }

        if(!empty($exceptions_a_modifier))
            $this->service_microsoft_calendrier->recreer_exceptions($exceptions_a_modifier, $createur_tache);

        foreach($occurrences_microsoft_par_date as $date => $infos)
            $this->service_microsoft_calendrier->supprimer_evenement($infos['id_microsoft'], $createur_tache);

        return true;
    }

    private function modifier_recurrence_sur_calendrier_microsoft($modifications, $modele){

        $management_tache_parent = management('tache', $modele->id, $modele);
        $createur_tache = modele('utilisateur')
                ->select('email', 'id_microsoft', 'id')
                ->where('id', $management_tache_parent->modele->affectation)
                ->where('synchronisation_calendrier_outlook', 1)
                ->first();

        if(empty($createur_tache))
            return true;

        //couper la récurrence actuelle et en créer une nouvelle dans le cas de la modification d'un et les suivants en milieu de récurrence
        if(isset($this->modification_recurrence, $this->ancienne_recurrence, $this->ancien_parent) && $this->modification_recurrence === true){

            //On reprend l'ancienne récurrence pour en modifier la date de fin
            $anciennes_infos = $this->ancien_parent->toArray();
            $anciennes_infos = $this->formate_donnees($anciennes_infos, true);
            $anciennes_infos['infos_recurrence'] = $this->ancienne_recurrence;
            $anciennes_infos['infos_recurrence']['date_de_fin'] = $this->date_fin_ancienne_recurrence;

            $infos = $this->prepare_donnees_microsoft($anciennes_infos, $this->ancien_parent, $management_tache_parent, $createur_tache);

            if(empty($infos) || empty($createur_tache))
                return true;

            return $this->service_microsoft_calendrier->modifier_evenement($infos, $this->ancien_parent->id_microsoft, $createur_tache);
        }
        else if(isset($this->modele_avant_tache_parent->statut_participant) && $this->modele_avant_tache_parent->statut_participant !== $modele->statut_participant){

            return $this->service_microsoft_calendrier->changer_statut_evenement($modele->statut_participant, $modele->id_microsoft, $createur_tache);
        }
        else {

            $modifications_parent = $management_tache_parent->modele->toArray();
            $modifications_parent = $this->formate_donnees($modifications_parent);
            $modifications_parent['infos_recurrence'] = $modifications['infos_recurrence'];

            $infos = $this->prepare_donnees_microsoft($modifications_parent, $management_tache_parent->modele, $management_tache_parent, $createur_tache);
            
            if(empty($infos) || empty($createur_tache))
                return true;

            $retour = $this->service_microsoft_calendrier->modifier_evenement($infos, $modele->id_microsoft, $createur_tache);

            if(!empty($retour) && is_string($retour))
                return $retour;
            else if(!empty($retour)){

                $modifications_tache_parent = array();

                if(!empty($retour->getId()) && $modele->id_microsoft !== $retour->getId())
                    $modifications_tache_parent['id_microsoft'] = $retour->getId();

                if(!empty($this->participants) && $modele->id_commun_taches_participants !== $retour->getICalUId())
                    $modifications_tache_parent['id_commun_taches_participants'] = $retour->getICalUId();

                if(!empty($retour->getBody()->getContent()) && $modele->commentaire !== $retour->getBody()->getContent())
                    $modifications_tache_parent['commentaire'] = $retour->getBody()->getContent();

                if(!empty($modifications_tache_parent))
                    $management_tache_parent->enregistre_modele($modifications_tache_parent);
            }

            $this->lier_evenements_microsoft_eden($management_tache_parent->modele->id_microsoft, $modifications['infos_recurrence']['date_de_debut'] . ' 00:00:00', $modifications['infos_recurrence']['date_de_fin'] . ' 23:59:59', $management_tache_parent->modele, $createur_tache);

            return true;
        }
    }
    
    private function prepare_donnees_microsoft($modifications, $modele, $management, $createur_tache){

        if(empty($createur_tache))
            return array();

        $correspondance_jour_microsoft = array_flip(Variables::$correspondance_jour_microsoft);

        $correspondance_frequence_relative = array_flip(Variables::$correspondance_frequence_relative);

        $infos = array(

            'date_debut' => $modele->date_de_debut,
            'date_fin' => $modele->date_de_fin,
            'sujet' => $modele->titre,
            'commentaire' => $modele->commentaire,
            'categorie' => $management->champ('type_tache_rdv')->recuperer_valeur(),
            'prive' => $modele->prive,
            'journee_entiere' => $modele->journee_entiere,
            'organisateur' => [
                'emailAddress' => [
                    'address' => $createur_tache->email,
                ]
            ],
            'recurrence' => [
                'pattern' => [
                    'interval' => $modifications['infos_recurrence']['frequence'],
                ],
                'range' => [
                    'type' => isset($modifications['infos_recurrence']['date_de_fin']) ? 'endDate' : 'noEnd',
                    'startDate' => formate_date('Y-m-d', $modifications['infos_recurrence']['date_de_debut']),
                ],
            ],
            'visioconference' => $modele->visioconference
        );

        if($modifications['infos_recurrence']['type_frequence'] == 1)
            $infos['recurrence']['pattern']['type'] = 'daily';
        else if($modifications['infos_recurrence']['type_frequence'] == 2){

            $infos['recurrence']['pattern']['type'] = 'weekly';
            $infos['recurrence']['pattern']['daysOfWeek'] = array();
            $infos['recurrence']['pattern']['firstDayOfWeek'] = $correspondance_jour_microsoft[1];

            foreach($modifications['infos_recurrence']['jours_concernes'] as $jour_concerne){
                $infos['recurrence']['pattern']['daysOfWeek'][] = $correspondance_jour_microsoft[$jour_concerne];
            }
        }
        else if($modifications['infos_recurrence']['type_frequence'] == 3)
            $infos['recurrence']['pattern']['type'] = $modifications['infos_recurrence']['frequence_jour_concerne'] == 0 ? 'absoluteMonthly' : 'relativeMonthly';
        else if($modifications['infos_recurrence']['type_frequence'] == 4)
            $infos['recurrence']['pattern']['type'] = !isset($modifications['infos_recurrence']['frequence_jour_concerne']) ||
                $modifications['infos_recurrence']['frequence_jour_concerne'] == 0 ?
                'absoluteYearly' : 'relativeYearly';

        if(in_array($infos['recurrence']['pattern']['type'], ['relativeMonthly', 'relativeYearly'])){

            $infos['recurrence']['pattern']['daysOfWeek'][] = $correspondance_jour_microsoft[date_create_from_format('Y-m-d', $modifications['infos_recurrence']['date_de_debut'])->format('N')];
            $infos['recurrence']['pattern']['index'] = $correspondance_frequence_relative[$modifications['infos_recurrence']['frequence_jour_concerne']];
        }

        if(in_array($infos['recurrence']['pattern']['type'], ['absoluteMonthly', 'absoluteYearly']))
            $infos['recurrence']['pattern']['dayOfMonth'] = date_create_from_format('Y-m-d', $modifications['infos_recurrence']['date_de_debut'])->format('j');

        if(in_array($infos['recurrence']['pattern']['type'], ['relativeYearly', 'absoluteYearly']))
            $infos['recurrence']['pattern']['month'] = date_create_from_format('Y-m-d', $modifications['infos_recurrence']['date_de_debut'])->format('n');

        if(isset($modifications['infos_recurrence']['date_de_fin']))
            $infos['recurrence']['range']['endDate'] = formate_date('Y-m-d', $modifications['infos_recurrence']['date_de_fin']);

        if(!empty($this->participants))
            $infos['participants'] = $this->participants;

        return $infos;
    }

    private function modifier_recurrence_sur_calendrier_google($modifications, $modele){

        $management_tache_parent = management('tache', $modele->id, $modele);
        $createur_tache = modele('utilisateur')
            ->select('id','email')
            ->where('id', $modele->affectation)
            ->where('synchronisation_calendrier_google', 1)
            ->first();

        if(empty($createur_tache))
            return true;

        //couper la récurrence actuelle et en créer une nouvelle dans le cas de la modification d'un et les suivants en milieu de récurrence
        if(isset($this->modification_recurrence, $this->ancienne_recurrence, $this->ancien_parent) && $this->modification_recurrence === true){

            //On reprend l'ancienne récurrence pour en modifier la date de fin
            $anciennes_infos = $this->ancien_parent->toArray();
            $anciennes_infos = $this->formate_donnees($anciennes_infos, true);
            $anciennes_infos['infos_recurrence'] = $this->ancienne_recurrence;
            $anciennes_infos['infos_recurrence']['date_de_fin'] = $this->date_fin_ancienne_recurrence;
            $infos = $this->prepare_donnees_google($anciennes_infos, $this->ancien_parent, $management_tache_parent);
            
            $this->service_google_calendrier->modifier_evenement($infos, $this->ancien_parent->id_google, $createur_tache);
            
            return true;
        }
        else {

            $infos = $this->prepare_donnees_google($modifications, $modele, $management_tache_parent);

            $retour = $this->service_google_calendrier->modifier_evenement($infos, $modele->id_google, $createur_tache);

            if(!empty($retour) && is_string($retour))
                return $retour;
            else if(!empty($retour)){

                $modifications_tache_parent = array();

                if(!empty($retour->id) && $modele->id_google !== $retour->id)
                    $modifications_tache_parent['id_google'] = $retour->id;

                if(!empty($this->participants) && $modele->id_commun_taches_participants !== $retour->iCalUId)
                    $modifications_tache_parent['id_commun_taches_participants'] = $retour->iCalUId;

                if(!empty($modifications_tache_parent))
                    $management_tache_parent->enregistre_modele($modifications_tache_parent);
            }

            return $this->service_google_calendrier->lier_evenements_eden($modele, $modifications['infos_recurrence'], $createur_tache, $this->exceptions_a_recreer);
        }
    }
    
    private function prepare_donnees_google($modifications, $modele, $management){

        //On récupère les catégories disponibles dans Eden
        $categories_possibles = $this->service_google_calendrier->recuperer_categories_possibles('id_couleur_google', 'id_valeur');
        $correspondance_jour_google = array_flip(Variables::$correspondance_jour_google);

        $infos = array(

            'date_de_debut' => $modele->date_de_debut,
            'date_de_fin' => $modele->date_de_fin,
            'titre' => $modele->titre,
            'commentaire' => $modele->commentaire,
            'categorie' => $modele->type_tache_rdv,
            'prive' => $modele->prive,
            'journee_entiere' => $modele->journee_entiere,
        );

        $rrule = "RRULE:";
        $regles = array();

        $regles['INTERVAL'] = $modifications['infos_recurrence']['frequence'];

        if($modifications['infos_recurrence']['type_frequence'] == 1)
            $regles['FREQ'] = 'DAILY';
        else if($modifications['infos_recurrence']['type_frequence'] == 2){

            $regles['FREQ'] = 'WEEKLY';
            $regles['BYDAY'] = array();

            foreach($modifications['infos_recurrence']['jours_concernes'] as $jour_concerne){
                $regles['BYDAY'][] = $correspondance_jour_google[$jour_concerne];
            }

            $regles['BYDAY'] = implode(',', $regles['BYDAY']);
        }
        else if($modifications['infos_recurrence']['type_frequence'] == 3)
            $regles['FREQ'] = 'MONTHLY';
        else if($modifications['infos_recurrence']['type_frequence'] == 4) {

            $regles['FREQ'] = 'YEARLY';
            $regles['BYMONTH'] = date_create_from_format('Y-m-d', $modifications['infos_recurrence']['date_de_debut'])->format('n');
        }

        if(in_array($modifications['infos_recurrence']['type_frequence'], [3, 4])){

            if(!empty($modifications['infos_recurrence']['frequence_jour_concerne'])) {

                $frequence_jour_concerne = $modifications['infos_recurrence']['frequence_jour_concerne'];

                if($frequence_jour_concerne === 5)
                    $frequence_jour_concerne = -1;

                $regles['BYDAY'] = $frequence_jour_concerne.$correspondance_jour_google[date_create_from_format('Y-m-d', $modifications['infos_recurrence']['date_de_debut'])->format('N')];
            }
            else
                $regles['BYMONTHDAY'] = date_create_from_format('Y-m-d H:i:s', $modele->date_de_debut)->format('j');
        }

        if(isset($modifications['infos_recurrence']['date_de_fin'])) {

            $regles['UNTIL'] = new \DateTime($modifications['infos_recurrence']['date_de_fin'] . '23:59:59', new \DateTimeZone('UTC'));
            $regles['UNTIL'] = $regles['UNTIL']->format('Ymd\THis\Z');
        }

        foreach($regles as $nom_regle => $valeur){

            $rrule .= $nom_regle . '=' . $valeur;

            if($nom_regle !== array_key_last($regles))
                $rrule .= ';';
        }

        $infos['recurrence'] = [$rrule];

        if(!empty($this->participants))
            $infos['participants'] = $this->participants;

        return $infos;
    }

    /*
     *
     * Récupère les tâches d'une récurrence que l'on doit modifier
     * Surtout utile pour la surcharge, dans certains projets, on veut modifier la requête pour ne sélectionner des tâches que selon certains statuts.
     * $modele n'est pas utilisé, mais il est utile en cas de surcharge
     *
     */
    private function taches_recurrences_pour_modification($parent_id){

        return modele('tache')->avec_parents()
            ->where(function($r) use ($parent_id) {
                $r->where('parent_id', $parent_id)
                    ->orWhere('id', $parent_id);
            })
            ->orderBy('date_de_debut')
            ->get();
    }

    private function instancie_services_synchro_externes() {

        if(fonctionnalite('microsoft_utiliser_connexion') && empty($this->service_microsoft_calendrier))
            $this->service_microsoft_calendrier = service('microsoft_calendrier');
        if(fonctionnalite('google_utiliser_connexion') && empty($this->service_google_calendrier))
            $this->service_google_calendrier = service('google_calendrier');
    }

    private function retraite_modifications_exception(&$modifications, $tache) {

        $champs_synchro_modifies = array_filter($modifications, function($valeur, $nom_champ) use($tache) {

            return in_array($nom_champ, Variables::$champs_tache_synchronises) && $valeur != $tache->$nom_champ;
        }, ARRAY_FILTER_USE_BOTH);

        if(count($champs_synchro_modifies) === 0)
            return;

        foreach($champs_synchro_modifies as $nom_champ => $valeur){

            if($this->modele_avant_tache_parent->$nom_champ != $tache->$nom_champ)
                unset($modifications[$nom_champ]);
        }
    }

    /*
     *
     * Retourne true si l'heure de début ou de fin de la tâche a été modifiée 
     * 
     */
    private function modification_heure_debut_ou_fin($modifications, $modele_avant) {

        $modification_heure_debut_ou_fin = false;

        if(isset($modifications['date_de_debut'],$modifications['date_de_fin'])){
            
            $heure_debut_modifications = date_create($modifications['date_de_debut'])->format('H:i:s');
            $heure_debut_modele_avant = date_create($modele_avant->date_de_debut)->format('H:i:s');

            $heure_fin_modifications = date_create($modifications['date_de_fin'])->format('H:i:s');
            $heure_fin_modele_avant = date_create($modele_avant->date_de_fin)->format('H:i:s');
            
            $modification_heure_debut_ou_fin = $heure_debut_modifications != $heure_debut_modele_avant;

            if($modification_heure_debut_ou_fin === true)
                return $modification_heure_debut_ou_fin;

            $modification_heure_debut_ou_fin = $heure_fin_modifications != $heure_fin_modele_avant;
        }

        return $modification_heure_debut_ou_fin;
    }

    private function date_de_fin_utilisable_recurrence($infos_recurrence){

        $this->date_de_fin_utilisable_recurrence = isset($infos_recurrence['date_de_fin']) ? 
            date_create_from_format('Y-m-d H:i:s', $infos_recurrence['date_de_fin'] . ' 23:59:59') : null;
        $date_de_fin_max = date_create_from_format('Y-m-d H:i:s', date('Y-m-d', strtotime('+' . fonctionnalite('duree_max_creation_recurrence') . ' months')) . ' 23:59:59');
        
        //On récupère la date de fin de la récurrence
        if(!isset($this->date_de_fin_utilisable_recurrence) || $this->date_de_fin_utilisable_recurrence->getTimestamp() > $date_de_fin_max->getTimestamp())
            $this->date_de_fin_utilisable_recurrence = $date_de_fin_max;
    }
    
    private function supprimer_recurrence_sur_calendrier_externe($tache, $tache_parent, $recurrence, $toute_la_serie, $contexte){

        $createur_tache = modele('utilisateur')
            ->select('email', 'id_microsoft', 'id')
            ->where('id', $tache_parent->affectation)
            ->where('synchronisation_calendrier_' . ($contexte == 'microsoft' ? 'outlook' : 'google'), 1)
            ->first();

        if(empty($createur_tache))
            return true;

        if($toute_la_serie)
            return $this->{'service_'.$contexte.'_calendrier'}->supprimer_evenement($tache_parent->{'id_'.$contexte}, $createur_tache);

        $management_tache_parent = management('tache', $tache_parent->id, $tache_parent);
        $management_tache_parent->charge_valeurs_champs_multiselection();

        $management_recurrence = management('tache_recurrence', $recurrence->id, $recurrence);
        $management_recurrence->charge_valeurs_champs_multiselection();

        $modifications = $tache_parent->toArray();
        $modifications['infos_recurrence'] = $recurrence->toArray();
        $modifications['infos_recurrence']['date_de_fin'] = date_create_from_format('Y-m-d H:i:s', $tache->date_de_fin)
            ->modify("-1 day")
            ->format('Y-m-d');

        return $this->{'modifier_recurrence_sur_calendrier_'.$contexte}($modifications, $tache_parent);
    }
}
