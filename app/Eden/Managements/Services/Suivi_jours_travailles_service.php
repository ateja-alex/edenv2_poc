<?php

namespace App\Eden\Managements\Services;

use App\Eden\Variables;

class Suivi_jours_travailles_service {

    public function initialisation(){

        $parametres = parametre_utilisateur('suivi_jours_travailles',false,moi()->id);

        if(empty($parametres))
            $parametres = $this->parametres_par_defaut();
        else
            $parametres = json_decode($parametres,true);

        $parametres['utilisateur_id'] = moi()->id;

        return $this->recuperation_donnees($parametres);
    }

    public function actualisation($parametres){

        $parametres_eden = $parametres;
        unset($parametres_eden['utilisateur_id']);

        parametre_utilisateur('suivi_jours_travailles',json_encode($parametres_eden),moi()->id);

        return $this->recuperation_donnees($parametres);
    }

    public function parametres_par_defaut(){

        return [
            'date_debut' => date("Y-m-d", strtotime('monday this week')),
            'date_fin' => date("Y-m-d", strtotime('sunday this week')),
            'type_affichage' => 'hebdomadaire'
        ];
    }

    public function recuperation_donnees($parametres){

        $jours_travailles = modele('jour_travaille')
            ->whereBetween('date',[$parametres['date_debut'],$parametres['date_fin']])
            ->where('utilisateur_id',$parametres['utilisateur_id'])
            ->get()->keyBy('date');

        if($parametres['type_affichage'] == 'mensuel') {
            $date_debut = date('N', strtotime($parametres['date_debut'])) == 1 ? $parametres['date_debut'] : date('Y-m-d', strtotime('last monday', strtotime($parametres['date_debut'])));
            $date_fin = date('N', strtotime($parametres['date_fin'])) == 7 ? $parametres['date_fin'] : date('Y-m-d', strtotime('next sunday', strtotime($parametres['date_fin'])));
        }
        else{
            $date_debut = $parametres['date_debut'];
            $date_fin = $parametres['date_fin'];
        }

        $jours = recupere_dates_entre_deux_dates($date_debut,$date_fin,1,'day','Y-m-d');

        array_unshift($jours,$date_debut);
        $jours[] = $date_fin;

        $donnees = [];

        $indisponibilites = service('jour_indisponibilite')->indisponibilites($date_debut,$date_fin);

        $statuts_par_semaine = [];

        foreach($jours as $jour) {

            $numero_semaine = date('W',strtotime($jour));

            if(empty($statuts_par_semaine))
                $statuts_par_semaine[$numero_semaine] = null;

            $statut_saisie = &$statuts_par_semaine[$numero_semaine];

            $hors_borne = $parametres['type_affichage'] == 'mensuel' && date('m',strtotime($jour)) != date('m',strtotime($parametres['date_debut']));

            $jour_indisponibilite = $indisponibilites
                ->where('date_debut', '<=' ,$jour)
                ->where('date_fin', '>=' ,$jour)->values();

            $non_travaille = date('N',strtotime($jour)) >= 6 || $jour_indisponibilite->isNotEmpty();

            if (!$hors_borne) {
                if (empty($jours_travailles[$jour]) && !$non_travaille)
                    $statut_saisie = 0;
                else if (!empty($jours_travailles[$jour]) && ($statut_saisie === null || $statut_saisie > $jours_travailles[$jour]->statut))
                    $statut_saisie = $jours_travailles[$jour]->statut;
            }
        }

        foreach($jours as $jour){

            $jour_indisponibilite = $indisponibilites
                ->where('date_debut', '<=' ,$jour)
                ->where('date_fin', '>=' ,$jour)->values();

            $numero_semaine = date('W',strtotime($jour));

            $donnee = [
                'date' => $jour,
                'affichage' => Variables::jours(date('N',strtotime($jour))).' '.date('d',strtotime($jour)),
                'jour_travaille' => $jours_travailles[$jour] ?? modele_par_defaut('jour_travaille'),
                'non_travaille' => date('N',strtotime($jour)) >= 6 || $jour_indisponibilite->isNotEmpty(),
                'hors_borne' => $parametres['type_affichage'] == 'mensuel' && date('m',strtotime($jour)) != date('m',strtotime($parametres['date_debut'])),
                'statut' => $statuts_par_semaine[$numero_semaine] !== null ? $statuts_par_semaine[$numero_semaine] : 0,
                'jour_ferie' => $jour_indisponibilite->isNotEmpty() ? $jour_indisponibilite : false,
            ];

            $donnees[] = $donnee;
        }

        $affichage_date = $parametres['type_affichage'] == 'hebdomadaire' ?
            traduction('composant.suivi_jours_travailles.semaine_du').' '.date('d/m/Y',strtotime($parametres['date_debut'])) :
            Variables::mois_de_lannee_format_complet(date('m',strtotime($parametres['date_debut']))).' '.date('Y',strtotime($parametres['date_debut']));

        $validateurs = \DB::table('utilisateur_validation_jours_travailles')
            ->where('cle_locale',$parametres['utilisateur_id'])
            ->get()->pluck('valeur')->toArray();

        $utilisateurs_disponibles = array_merge(
            [moi()->id],
            \DB::table('utilisateur_validation_jours_travailles')
                ->where('valeur',$parametres['utilisateur_id'])
                ->get()->pluck('cle_locale')->toArray()
        );

        $statuts = array_filter($statuts_par_semaine,function($statut){
            return $statut !== null;
        });

        return [
            'parametres' => $parametres,
            'jours_travailles' => $donnees,
            'affichage_date' => $affichage_date,
            'validateurs' => $validateurs,
            'utilisateurs_disponibles' => admin() ? false : $utilisateurs_disponibles,
            'statut_saisie' => empty($statuts) ? 0 : min($statuts),
        ];
    }

    public function changer_statut_saisie($nouveau_statut, $parametres){

        $donnees = $this->recuperation_donnees($parametres);

        foreach($donnees['jours_travailles'] as &$jour){

            $jour['statut'] = $nouveau_statut;

            if($jour['hors_borne'])
                continue;

            $modele = $jour['jour_travaille'];

            $management = management('jour_travaille',
                $modele->id ?? false,
                !empty($modele->id) ? $modele : false);

            $modifications = [];

            if(empty($modele->id)){

                if($jour['non_travaille'])
                    continue;

                $modifications = $modele->toArray();
                $modifications['utilisateur_id'] = $parametres['utilisateur_id'];
                $modifications['date'] = $jour['date'];
                $modifications['total_jours'] = (($modifications['matin'] ?? 0) + ($modifications['apres_midi'] ?? 0)) / 2;

            }
            else if($modele->statut == 2 && ($nouveau_statut == 0 || $donnees['statut_saisie'] == 0))
                continue;

            $modifications['statut'] = $nouveau_statut;

            if($nouveau_statut == 0)
                $modifications['date_terminee'] = null;
            else if($nouveau_statut == 1){
                $modifications['date_terminee'] = date('Y-m-d');
                $modifications['date_validation'] = null;
            }
            else
                $modifications['date_validation'] = date('Y-m-d');

            $management->enregistre($modifications);

            $jour['jour_travaille'] = $management->modele;
        }

        return [
            'jours_travailles' => $donnees['jours_travailles'],
        ];
    }
}