<?php

namespace App\Eden\Managements\Fonctionnalites;

class Tache_management extends Fonctionnalite_management{

    public function fonctionnalites_avec_valeurs(){

        $valeurs['champs_tache'] = champs_libres('tache')->pluck('nom', 'nom_sql')->toArray();

        return $this->fonctionnalites($valeurs);
    }

    /*
     *
     * Retour les fonctionnalitées du module
     *
     */
    public function fonctionnalites($valeurs = array()){

        $fonctionnalites = array(

            'Paramètres généraux' => array(
                array(

                    'nom' => "Activer les tâches",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "taches",
                ),
                array(

                    'nom' => "Taches sur les fiches clients",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "fiche_client_taches",
                    'fonctionnalite_mere' => "taches",
                ),
                array(

                    'nom' => "Durée des taches par défaut",
                    'type' => "input",
                    'description' => "Défini les durées proposées par défaut pour la création d'une tache. Au format : 'heure,minute;heure.minute'",
                    'placeholder' => "1;2,5;3,5;4.5",
                    'fonctionnalite' => "duree_des_taches",
                    'fonctionnalite_mere' => "taches",
                ),
                array(

                    'nom' => "Affichage des tâches en journée entière",
                    'type' => "select",
                    'valeurs_select' => array(
                        'bandeau' => 'Dans un bandeau dans le header du calendrier',
                        'classique' => "Dans le calendrier directement",
                    ),
                    'description' => "Permet de paramétrer où se situent les tâches en journée entière dans le calendrier : soit dans un bandeau en haut, soit dans le calendrier directement.",
                    'fonctionnalite' => "affichage_taches_journee_entiere",
                    'fonctionnalite_mere' => "taches",
                ),
                array(

                    'nom' => "Couleur des tâches sur le calendrier en fonction",
                    'type' => "select",
                    'valeurs_select' => array(
                        'type_tache' => 'Du type de tâche',
                        'equipe' => "De l'équipe",
                        'utilisateur' => "De l'utilisateur",
                    ),
                    'description' => "Permet de paramétrer les couleurs des tâches en fonction d'une donnée associée sur le calendrier",
                    'fonctionnalite' => "choix_couleur_tache",
                    'fonctionnalite_mere' => "taches",
                ),
                array(

                    'nom' => "Couleur des tâches sur le planning en fonction",
                    'type' => "select",
                    'valeurs_select' => array(
                        'type_tache' => 'Du type de tâche',
                        'equipe' => "De l'équipe",
                        'utilisateur' => "De l'utilisateur"
                    ),
                    'description' => "Permet de paramétrer les couleurs des tâches en fonction d'une donnée associée sur le planning",
                    'fonctionnalite' => "choix_couleur_tache_planning",
                    'fonctionnalite_mere' => "taches",
                ),
                array(

                    'nom' => "Permettre la modification d'une tâche terminée",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "modifier_tache_terminee",
                    'fonctionnalite_mere' => "taches",
                ),
                array(

                    'nom' => "Durée jusqu'à laquelle sont créées les tâches d'une récurrence (en mois)",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "duree_max_creation_recurrence",
                    'fonctionnalite_mere' => "taches",
                ),
                array(

                    'nom' => "Fréquence à laquelle sont synchronisées les prochaines occurrences d'une récurrence (en mois)",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "frequence_creation_occurrences_recurrence",
                    'fonctionnalite_mere' => "taches",
                ),
                array(

                    'nom' => "Valeur de la durée ajoutée à la date de début pour calculer la date de fin",
                    'type' => "input",
                    'description' => "Valeur de la durée ajoutée à la date de début pour calculer la date de fin quand on est sur une récurrence de type personnalisée",
                    'fonctionnalite' => "valeur_duree_ajoutee_recurrence_personnalisee",
                    'fonctionnalite_mere' => "taches",
                ),
                array(

                    'nom' => "Unité de la durée ajoutée à la date de début pour calculer la date de fin",
                    'type' => "select",
                    'valeurs_select' => array(
                        'day' => 'Jour(s)',
                        'week' => 'Semaine(s)',
                        'month' => 'Mois',
                        'year' => 'Année(s)',
                    ),
                    'description' => "Unité de la durée ajoutée à la date de début pour calculer la date de fin quand on est sur une récurrence de type personnalisée",
                    'fonctionnalite' => "unite_duree_ajoutee_recurrence_personnalisee",
                    'fonctionnalite_mere' => "taches",
                ),
                array(

                    'nom' => "Champs à ne pas reprendre d'une tâche parent de récurrence",
                    'type' => "badge_multiselection",
                    'valeurs_badges' => $valeurs['champs_tache'] ?? array(),
                    'description' => "",
                    'fonctionnalite' => "champs_non_repris_tache_parent",
                    'fonctionnalite_mere' => "taches",
                ),
            ),
            'Planning' => array(

                array(

                    'nom' => "Afficher le planning du...",
                    'type' => "select",
                    'valeurs_select' => array(

                        '5' => 'Lundi au vendredi',
                        '6' => 'Lundi au samedi',
                        '7' => 'Lundi au dimanche',
                    ),
                    'description' => "",
                    'fonctionnalite' => "planning_nombre_jours",
                ),
                array(

                    'nom' => "Affichage en demi journée",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "planning_affichage_demi_journee",
                ),
                array(
                    'nom' => "Heure de début de la journée",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "planning_affichage_demi_journee_debut_am",
                    'fonctionnalite_mere' => "planning_affichage_demi_journee",
                ),
                array(
                    'nom' => "Heure de fin de la première moitié de journée",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "planning_affichage_demi_journee_fin_am",
                    'fonctionnalite_mere' => "planning_affichage_demi_journee",
                ),
                array(
                    'nom' => "Heure de début de la deuxiéme moitié de journée",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "planning_affichage_demi_journee_debut_pm",
                    'fonctionnalite_mere' => "planning_affichage_demi_journee",
                ),
                array(
                    'nom' => "Heure de fin de la journée",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "planning_affichage_demi_journee_fin_pm",
                    'fonctionnalite_mere' => "planning_affichage_demi_journee",
                ),
                array(

                    'nom' => "Hauteur des lignes du planning",
                    'type' => "select",
                    'valeurs_select' => array(

                        1 => '1',
                        2 => '2',
                        3 => '3',
                        4 => '4',
                        5 => '5',
                        "ajust" => 'Ajuster au texte',
                    ),
                    'description' => "",
                    'fonctionnalite' => "planning_hauteur_ligne",
                ),
                array(

                    'nom' => "Afficher les avatars",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "planning_afficher_avatar_utilisateur",
                ),
            ),
        );

        return $fonctionnalites;
    }

    public function obtenir_type_module(){

        return 'Tache';
    }

    public function obtenir_nom_module(){

        return 'Module tâche';
    }

    public function obtenir_icone_module(){

        return 'eden/images/pictos/project.png';
    }
}
