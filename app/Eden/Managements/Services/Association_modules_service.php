<?php

namespace App\Eden\Managements\Services;

use App\Eden\Models\Liste_libre;
use App\Eden\Managements\Cache_management;

class Association_modules_service {

    /**
     *
     * Permet de récupérer le modèle par défaut d'un élémént
     *
     */
    public function recupere($type, $element = null){

        $table = [
            'projet' => [
                'modules' => [
                    'client_projets',
                ],
                'fonctionnalites' => [

                ],
            ],
            'adresse' => [
                'modules' => [
                    'adresse_sur_documents',
                    'liste_adresses',
                ],
                'fonctionnalites' => [

                ],
            ],
            'credit' => [
                'modules' => [
                    'client_credits',
                ],
                'fonctionnalites' => [

                ],
            ],
            'echange' => [
                'modules' => [
                    'section_timeline',
                ],
                'fonctionnalites' => [

                ],
            ],
            'calendrier_evenement' => [
                'modules' => [
                    'calendrier',
                ],
                'fonctionnalites' => [

                ],
            ],
            'tache' => [
                'modules' => [
                    'composant_calendrier',
                    'affichage_calendrier',
                    'taches',
                    'calendrier2'
                ],
                'fonctionnalites' => [

                ],
            ],
            'contact' => [
                'modules' => [
                    'liste_contacts',
                ],
                'fonctionnalites' => [

                ],
            ],
            'article_fournisseur' => [
                'modules' => [
                    'liste_fournisseur_js',
                ],
                'fonctionnalites' => [

                ],
            ],
            'compte_email' => [
                'modules' => [

                ],
                'fonctionnalites' => [
                    'email_utiliser_identifiants_comptes_emails',
                ],
            ],
            'planning' => [
                'modules' => [
                    'planning',
                ],
                'fonctionnalites' => [
                    'planning_hauteur_ligne',
                    'planning_afficher_avatar_utilisateur',
                ],
            ]
        ];

        $retour = [
            'type_element' => [],
            'modules' => [],
        ];

        foreach ($table as $type_element_tmp => $donnees){

            if($type == 'type_element' && $type_element_tmp == $element){

                $retour['type_element'] = [];
                $retour['modules'] = $donnees['modules'];

            }
            if($type == 'fonctionnalites' && in_array($element,$donnees['fonctionnalites'])){

                $retour['type_element'][] = $type_element_tmp;
                $retour['modules'] = array_merge($donnees['modules'],$retour['modules']);

            }

        }

        return $retour;

    }

    /**
     *
     * Regénère les modules
     *
     */
    public function generation_composant($type, $element, $composants = false){

        Cache_management::vider();

        if($composants === false)
            $composants = $this->recupere($type, $element);

        foreach ($composants['modules'] as $module){
            Cache_management::generation_module($module);
        }

        foreach ($composants['type_element'] as $type_element){

            $liste_libres = Liste_libre::where('type_element', $type_element)->get();

            foreach ($liste_libres as $liste_libre) {

                Cache_management::generation_liste_libre($liste_libre->id);
            }

        }

        Cache_management::vider();

        return true;

    }
}