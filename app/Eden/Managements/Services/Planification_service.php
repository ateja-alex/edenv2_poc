<?php

namespace App\Eden\Managements\Services;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Planification_service {

    public function imprimer($semaine_voulue, $parametres_initiaux) {

        $parametres_initiaux = $this->prepare_donnees_impression($semaine_voulue, $parametres_initiaux);

        list($chemins_pdfs,$nom_du_pdf) = $this->impression($semaine_voulue, $parametres_initiaux);

        $merger = \PDFMerger::init();

        foreach($chemins_pdfs as $chemin_pdf){
            $merger->addPDF($chemin_pdf, 'all', 'L');
        }

        $merger->merge("L");

        $merger->save(storage_path('/app/public/planning/'.$nom_du_pdf));

        foreach($chemins_pdfs as $pdf){

            Storage::delete(strchr($pdf,'tmp'));
        }

        return asset('/storage/planning/'.$nom_du_pdf);
    }

    protected function prepare_donnees_impression($semaine_voulue, $parametres_initiaux){

        $parametres_initiaux['logo_application'] = asset('storage/'.maquette('logo_application'));
        $parametres_initiaux['filtres_calendrier'] = $this->filtres();

        if(empty($parametres_initiaux['filtres_avec_valeurs'])) {
            $parametres_initiaux['filtres_avec_valeurs'] = array();

            foreach ($parametres_initiaux['filtres'] as $infos_filtre) {

                if (!isset($infos_filtre['id'], $infos_filtre['valeurs']))
                    continue;

                foreach ($parametres_initiaux['filtres_calendrier'] as $filtre_calendrier) {

                    if ($filtre_calendrier['id'] == $infos_filtre['id'])
                        $parametres_initiaux['filtres_avec_valeurs'][$filtre_calendrier['nom_sql']] = $infos_filtre['valeurs'];
                }
            }
        }

        $parametres_initiaux['semaines_voulues'] = [
            [
                'debut_semaine' => lundi($semaine_voulue),
                'fin_semaine' => dimanche_prochain($semaine_voulue),
            ]
        ];

        if(isset($parametres_initiaux['nombre_de_semaines']) && $parametres_initiaux['nombre_de_semaines'] > 1){

            for($i=0;$i<$parametres_initiaux['nombre_de_semaines'] - 1;$i++){

                $debut_semaine_actuelle = date('Y-m-d', strtotime(Arr::last($parametres_initiaux['semaines_voulues'])['debut_semaine'] .' next monday'));

                $parametres_initiaux['semaines_voulues'][] = [
                    'debut_semaine' => $debut_semaine_actuelle,
                    'fin_semaine' => dimanche_prochain($debut_semaine_actuelle),
                ];
            }
        }

        if(empty($parametres_initiaux['type_taches_affichees']))
            $parametres_initiaux['type_taches_affichees'] = 'les_deux';

        return $parametres_initiaux;
    }

    /**
     *
     * Renvoie les utilisateurs pour lesquels il faut imprimer le calendrier
     *
     */
    public function selection_utilisateurs_impression($semaine_voulue, $parametres_initiaux) {

        $debut_periode = Arr::first($parametres_initiaux['semaines_voulues'])['debut_semaine'];
        $fin_periode = Arr::last($parametres_initiaux['semaines_voulues'])['fin_semaine'];

        $selects = ['utilisateur.*'];

        $utilisateurs = modele('utilisateur');

        if($parametres_initiaux['type_taches_affichees'] != 'conges') {
            $selects[] = DB::raw('COUNT(tache.id) as nb_taches');

            $utilisateurs = $utilisateurs->leftJoin('tache', 'utilisateur.id', 'tache.affectation');
        }

        if($parametres_initiaux['type_taches_affichees'] != 'tache')
            $utilisateurs = $utilisateurs->leftJoin('employe_demande_conge', 'utilisateur.id', 'employe_demande_conge.employe_id');

        $filtres = $this->filtres();

        $utilisateurs->where(function($condition) use ($parametres_initiaux,$debut_periode,$fin_periode,$filtres){
            if($parametres_initiaux['type_taches_affichees'] != 'conges')
                $condition->orWhere(function($condition) use($debut_periode,$fin_periode,$filtres,$parametres_initiaux){
                    $condition->where('tache.date_de_debut', '>=', $debut_periode)
                        ->where('tache.date_de_fin', '<=', $fin_periode);

                    foreach($filtres as $filtre) {

                        if(!isset($parametres_initiaux['filtres_avec_valeurs'][$filtre['nom_sql']])
                            || empty($filtre['valeurs_dans_champs']['tache']))
                            continue;

                        $champ = champ_libre($filtre['type_element'], $filtre['nom_sql']);

                        $champ->champ->modele->alias_champ = 'tache.'.$filtre['valeurs_dans_champs']['tache'];

                        $condition = $champ->champ->applique_filtre_sur_requete($parametres_initiaux['filtres_avec_valeurs'][$filtre['nom_sql']], $condition);
                    }
                });

            if($parametres_initiaux['type_taches_affichees'] != 'tache')
                $condition->orWhere(function($condition) use($debut_periode,$fin_periode,$filtres,$parametres_initiaux){
                    $condition->where('employe_demande_conge.date_de_debut', '>=', $debut_periode)
                        ->where('employe_demande_conge.date_de_fin', '<=', $fin_periode);

                    foreach($filtres as $filtre) {

                        if(!isset($parametres_initiaux['filtres_avec_valeurs'][$filtre['nom_sql']])
                            || empty($filtre['valeurs_dans_champs']['employe_demande_conge']))
                            continue;

                        $champ = champ_libre($filtre['type_element'], $filtre['nom_sql']);

                        $champ->champ->modele->alias_champ = 'employe_demande_conge.'.$filtre['valeurs_dans_champs']['employe_demande_conge'];

                        $condition = $champ->champ->applique_filtre_sur_requete($parametres_initiaux['filtres_avec_valeurs'][$filtre['nom_sql']], $condition);
                    }
                });
        });

        $utilisateurs->select($selects);

        $utilisateurs = $utilisateurs->groupBy('utilisateur.id')->get();

        return $utilisateurs;
    }
}
