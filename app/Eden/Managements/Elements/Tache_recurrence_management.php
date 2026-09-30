<?php

namespace App\Eden\Managements\Elements;

class Tache_recurrence_management extends Element_management {

    public $occurrences_personnalisees = null;
    public $premiere_occurrence = null;
    public $derniere_occurrence = null;
    public $tache_parent = null;

    public $organisateur = false;
    public $taches_organisateur = array();

    /**
     *
     * On actualise les valeurs de la liste formatée reprenant les modèles de récurrence
     *
     */
    protected function methodes_post_modification($modele, $modele_avant, $modifications) {

        parent::methodes_post_modification($modele, $modele_avant, $modifications);

        // Si la date de début ou la date de fin a été modifiée, on doit vérifier que les occurrences ne sortent pas de l'intervalle
        // Celles qui sortent de l'intervalle sont supprimées
        if((isset($modifications['date_de_debut'],$modele_avant->date_de_debut) && $modifications['date_de_debut'] != $modele_avant->date_de_debut) ||
            (isset($modifications['date_de_fin'],$modele_avant->date_de_fin) && $modifications['date_de_fin'] != $modele_avant->date_de_fin)){

            $occurrences = modele('tache')->where('parent_id', $this->modele->tache_parent_id)->orderBy('date_de_debut')->get();
            $this->tache_parent = modele('tache')->avec_parents()->where('id', $this->modele->tache_parent_id)->first();
            $this->premiere_occurrence = $occurrences->first();
            $this->derniere_occurrence = $occurrences->last();
            
            $date_derniere_occurence = date_create_from_format('Y-m-d H:i:s', formate_date('Y-m-d H:i:s', $this->tache_parent->date_de_debut));
            $date_fin_occurence = date_create_from_format('Y-m-d H:i:s', formate_date('Y-m-d H:i:s', $this->tache_parent->date_de_fin));
            $intervalle_debut_fin = $date_derniere_occurence->diff($date_fin_occurence);

            $management_tache = management('tache');
            $management_tache->tache_enfant = true;
            $management_tache->modifier_recurrence = 1;

            $dates_a_creer = $this->occurrences_personnalisees ?? array();

            //S'il n'y a plus de tâches associées, on supprime la récurrence
            if($occurrences->isEmpty() && empty($this->tache_parent)){

                $this->supprime();
                return;
            }

            if(defined('synchro_rdv_microsoft_vers_eden') && !empty($this->tache_parent->id_microsoft))
                $occurrences = $occurrences->keyBy('id_microsoft');
            else if(defined('synchro_rdv_google_vers_eden') && !empty($this->tache_parent->id_google))
                $occurrences = $occurrences->keyBy('id_google');

            $this->charge_valeurs_champs_multiselection();

            // Si la date de début a été modifiée et est antérieure à l'ancienne date de début,
            // il faut calculer les dates entre ces deux dates et les ajouter au tableau $dates_a_creer
            if(!isset($this->occurrences_personnalisees) && strtotime($modele_avant->date_de_debut) > strtotime($modele->date_de_debut)) {

                $infos_recurrence = clone $this->modele;
                $infos_recurrence = $infos_recurrence->toArray();

                $infos_recurrence['date_de_fin'] = strtotime($modele_avant->date_de_debut) > strtotime($infos_recurrence['date_de_fin']) ?
                    $infos_recurrence['date_de_fin'] : $modele_avant->date_de_debut;

                $date_debut_recuperation_taches = date_create_from_format('Y-m-d H:i:s', formate_date('Y-m-d H:i:s', $infos_recurrence['date_de_debut'] . ' ' .  substr($this->tache_parent->date_de_debut, 11, 8)));

                $date_fin_recuperation_taches = clone $date_debut_recuperation_taches;
                $date_fin_recuperation_taches->add($intervalle_debut_fin);

                $dates_a_creer = array_merge($dates_a_creer, service('recurrence')->recurrence_dates_taches_a_creer($infos_recurrence, [
                    'date_de_debut' => $date_debut_recuperation_taches->format('Y-m-d H:i:s'),
                    'date_de_fin' => $date_fin_recuperation_taches->format('Y-m-d H:i:s')
                ]));
            }

            //Si la date de fin de la dernière occurrence créée est inférieure à la date de fin de la récurrence,
            // il faut calculer les dates des prochaines occurrences et les ajouter au tableau $dates_a_creer
            if(!isset($this->occurrences_personnalisees) && (strtotime($modele_avant->date_de_fin) < strtotime($modele->date_de_fin) ||
                    ((empty($modele_avant->id) || !empty($modele->date_de_debut)) && empty($modele->date_de_fin)))){

                $infos_recurrence = clone $this->modele;
                $infos_recurrence = $infos_recurrence->toArray();

                if($modele_avant->date_de_debut != $modele->date_de_debut && strtotime($modele->date_de_debut) > strtotime($modele_avant->date_de_fin))
                    $date_debut_recuperation_taches = date_create_from_format('Y-m-d H:i:s', formate_date('Y-m-d H:i:s', $modele->date_de_debut . ' ' .  substr($this->tache_parent->date_de_debut, 11, 8)));
                else
                    $date_debut_recuperation_taches = date_create_from_format('Y-m-d H:i:s', formate_date('Y-m-d H:i:s', $this->derniere_occurrence->date_de_debut));

                $date_fin_recuperation_taches = clone $date_debut_recuperation_taches;
                $date_fin_recuperation_taches->add($intervalle_debut_fin);

                $dates_a_creer = array_merge($dates_a_creer, service('recurrence')->recurrence_dates_taches_a_creer($infos_recurrence, [
                    'date_de_debut' => $date_debut_recuperation_taches->format('Y-m-d H:i:s'),
                    'date_de_fin' => $date_fin_recuperation_taches->format('Y-m-d H:i:s')
                ]));
            }
            
            $this->dechargement_valeurs_multiselection();

            if(!empty($dates_a_creer)){

                $modifications_triees = service('recurrence')->formate_donnees($this->tache_parent->toArray(), true);
                $modifications_triees['parent_id'] = $this->tache_parent->id;

                $ids_taches = [];

                // On crée les tâches de la récurrence
                foreach($dates_a_creer as $occurrence){

                    if($occurrence['date_de_debut'] == $this->derniere_occurrence->date_de_debut || $occurrence['date_de_debut'] == $this->premiere_occurrence->date_de_debut ||
                        (defined('synchro_rdv_microsoft_vers_eden') && isset($occurrence['id_microsoft']) && !empty($occurrences[$occurrence['id_microsoft']])) ||
                        (defined('synchro_rdv_google_vers_eden') && isset($occurrence['id_google']) && !empty($occurrences[$occurrence['id_google']])))
                        continue;

                    $date_debut_occurrence = date_create($occurrence['date_de_debut'])->format('Y-m-d');
                    $modifications_occurrence = $modifications_triees;

                    foreach($occurrence as $nom_champ => $valeur){

                        $modifications_occurrence[$nom_champ] = $valeur;
                    }

                    if(isset($this->taches_organisateur[$date_debut_occurrence]))
                        $modifications_occurrence['id_tache_organisateur'] = $this->taches_organisateur[$date_debut_occurrence]['id'];

                    $management_tache->enregistre($modifications_occurrence);

                    if($this->organisateur === true && !empty($management_tache->modele))
                        $this->taches_organisateur[$date_debut_occurrence] = $management_tache->modele;

                    $ids_taches[] = $management_tache->modele->id;
                    
                    $management_tache->modele = null;
                }

                if(!empty($ids_taches))
                    $management_tache->trigger_applicatif_elements_multiples($ids_taches);
            }

            //On regarde s'il y a des tâches à supprimer si elles sortent des bornes de la récurrence
            foreach($occurrences as $occurrence) {

                if (
                    strtotime($occurrence->date_de_debut) < strtotime($this->modele->date_de_debut . ' 00:00:00') ||
                    (!empty($this->modele->date_de_fin) && strtotime($occurrence->date_de_fin) > strtotime($this->modele->date_de_fin . ' 23:59:59'))
                ){

                    $management_tache->modele = $occurrence;
                    $management_tache->supprime();
                }
            }
            
            $occurrences = modele('tache')->where('parent_id', $this->modele->tache_parent_id)->orderBy('date_de_debut')->get();
            $this->premiere_occurrence = $occurrences->first();
            $this->derniere_occurrence = $occurrences->last();

            if(!empty($this->premiere_occurrence)){

                $management_tache = management('tache', $this->tache_parent->id, $this->tache_parent);
                $management_tache->enregistre_modele([
                    'date_de_debut' => $this->premiere_occurrence->date_de_debut,
                    'date_de_fin' => $this->premiere_occurrence->date_de_fin,
                ]);
                $this->tache_parent = $management_tache->modele;
            }
        }
    }
}