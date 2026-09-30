<?php

namespace App\Eden\Managements\Services;

use App\Eden\Managements\Cache_management;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Elements\Filtre;
use Carbon\Carbon;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PDF;
use Illuminate\Support\Facades\Mail;

class Calendrier_service extends Planification_service {

    protected $id_utilisateur_actuel = null;
    public $contexte = 'calendrier';

    public function __construct(){

        $this->id_utilisateur_actuel = moi()->id;
        $url = $_SERVER['HTTP_REFERER'];

        $contexte = collect(\Route::getRoutes())->first(function($route) use($url){
            return $route->matches(request()->create($url));
        });

        $this->contexte = $contexte->getName() ?? 'calendrier';
    }

    public function recuperation_donnees($formulaire){

        $initialisation = isset($formulaire['initialisation']);

        $filtres_pour_fiche = array();

        if(!empty($formulaire['filtres_pour_fiche']))
            $filtres_pour_fiche = $formulaire['filtres_pour_fiche'];

        if($initialisation) {

            $parametres = $this->recuperation_donnees_pour_initialisation();

            if(!isset($parametres['type_taches_affichees']))
                $parametres['type_taches_affichees'] = $formulaire['type_taches_affichees'];

            $modification_periode = false;

            $filtres_valeurs = $parametres['filtres'] ?? [];

            if(!empty($formulaire['parametres_requete']['parametres'])) {

                $parametres_requete = json_decode(base64_decode($formulaire['parametres_requete']['parametres']), true);

                if (isset($parametres_requete['filtres_valeurs'])) {

                    $filtres_a_afficher = $this->filtres();

                    foreach ($parametres_requete['filtres_valeurs'] as $cle => $valeur){

                        $champ_libre = champ_libre_modele('tache',$cle);

                        if(in_array($champ_libre->type,[1,20,42]) && !is_array($valeur))
                            $valeur = [$valeur];

                        $filtre = array_values(array_filter($filtres_a_afficher,function($filtre) use ($cle){
                            return $filtre['nom_sql'] == $cle;
                        }))[0] ?? null;

                        if(!empty($filtre)){

                            $correspondance = null;

                            foreach($filtres_valeurs as $id_filtre_valeur => $filtre_valeur){
                                if($filtre_valeur['id'] == $filtre['id'])
                                    $correspondance = $id_filtre_valeur;
                            }

                            if($correspondance !== null)
                                $filtres_valeurs[$correspondance]['valeurs'] = $valeur;
                            else
                                $filtres_valeurs[] = array(
                                    'id' => $filtre['id'],
                                    'valeurs' => $valeur
                                );

                        }
                    }

                    $parametres['filtres'] = $filtres_valeurs;
                }

                if (isset($parametres_requete['parametres'])) {
                    foreach ($parametres_requete['parametres'] as $cle => $valeur){
                        $parametres[$cle] = $valeur;
                    }
                }
            }
            else
                parametre_utilisateur('parametres_calendrier_'.$this->contexte, base64_encode(serialize($parametres)), $this->id_utilisateur_actuel);

            if(!empty($filtres_pour_fiche)) {
                list($taches, $demandes_cp) = $this->recuperation_taches($initialisation, $filtres_pour_fiche, $filtres_valeurs, $parametres);


                $prochaine_tache = clone $taches;
                $prochaine_demande_cp = clone $demandes_cp;

                $prochaine_tache = $prochaine_tache->select('date_de_debut')->where( function($tache) {
                    return $tache->where('date_de_debut', '>=', date('Y-m-d'))
                                ->orWhere('date_de_fin', '>=', date('Y-m-d'));
                })->orderBy('date_de_debut')->first();

                $prochaine_demande_cp = $prochaine_demande_cp->select('date_de_debut')->where(function($demande_cp) {
                    return $demande_cp->where('date_de_debut', '>=', date('Y-m-d'))
                        ->orWhere('date_de_fin', '>=', date('Y-m-d'));
                })->orderBy('date_de_debut')->first();

                if(!empty($prochaine_tache))
                    $prochaine_tache = $prochaine_tache->date_de_debut;

                if(!empty($prochaine_demande_cp))
                    $prochaine_demande_cp = $prochaine_demande_cp->date_de_debut;

                $date = false;

                if(!empty($prochaine_tache))
                    $date = $prochaine_tache;
                if(!empty($prochaine_demande_cp)) {
                    if($date === false)
                        $date = $prochaine_demande_cp;
                    else if(abs(strtotime($prochaine_demande_cp) - strtotime("now")) < abs(strtotime($date) - strtotime("now")))
                        $date = $prochaine_demande_cp;
                }

                if($date === false) {
                    $precedente_tache = clone $taches;
                    $precedente_demande_cp = clone $demandes_cp;

                    $precedente_tache = $precedente_tache->select('date_de_debut')->where( function($tache) {
                        return $tache->where('date_de_debut', '<=', date('Y-m-d'))
                            ->orWhere('date_de_fin', '<=', date('Y-m-d'));
                    })->orderBy('date_de_debut', 'desc')->first();

                    $precedente_demande_cp = $precedente_demande_cp->select('date_de_debut')->where(function($demande_cp) {
                        return $demande_cp->where('date_de_debut', '<=', date('Y-m-d'))
                            ->orWhere('date_de_fin', '<=', date('Y-m-d'));
                    })->orderBy('date_de_debut', 'desc')->first();

                    if(!empty($precedente_tache))
                        $precedente_tache = $precedente_tache->date_de_debut;

                    if(!empty($precedente_demande_cp))
                        $precedente_demande_cp = $precedente_demande_cp->date_de_debut;

                    if(!empty($precedente_tache))
                        $date = $precedente_tache;
                    if(!empty($precedente_demande_cp)) {
                        if($date === false)
                            $date = $precedente_demande_cp;
                        else if(abs(strtotime($precedente_demande_cp) - strtotime("now")) < abs(strtotime($date) - strtotime("now")))
                            $date = $precedente_demande_cp;
                    }
                }

                $parametres['date'] = $date;
            }

            if(empty($parametres['date']))
                $parametres['date'] = date('Y-m-d');
        }

        else {

            $parametres = $formulaire;

            $parametres_initiaux = parametre_utilisateur('parametres_calendrier_'.$this->contexte, false, $this->id_utilisateur_actuel);

            if($parametres_initiaux !== null)
                $parametres_initiaux = unserialize(base64_decode($parametres_initiaux));

            if(empty($parametres['tranche_horaire'])){

                if(!empty($parametres_initiaux['tranche_horaire']))
                    $parametres['tranche_horaire'] = $parametres_initiaux['tranche_horaire'];
                else
                    $parametres['tranche_horaire'] = array(
                        'heure_debut' => '08:00',
                        'heure_fin' => '20:00',
                    );
            }

            if(empty($parametres['granularite']))
                $parametres['granularite'] = !empty($parametres_initiaux['granularite']) ? $parametres_initiaux['granularite'] : '30';

            $filtres_valeurs = isset($parametres['filtres']) && is_array($parametres['filtres']) ? $parametres['filtres'] : [];

            $modification_periode = $parametres['modification_periode'];
            unset($parametres['modification_periode']);

            $date =  $parametres['date'];
            unset($parametres['date']);

            parametre_utilisateur('parametres_calendrier_'.$this->contexte, base64_encode(serialize($parametres)), $this->id_utilisateur_actuel);

            if(is_array($date)) {
                $date = $date[array_key_first($date)];
            }

            $parametres['date'] = $date;
        }

        if(empty($parametres['format_calendrier']))
            $parametres['format_calendrier'] = 'semaine_5j';

        // Traitement si option 1 jour
        if($parametres['format_calendrier'] == 'jour' )
            $informations = $this->traitement_jour($parametres,$modification_periode);

        // Traitement si option "Semaine 5j"
        else if($parametres['format_calendrier'] == 'semaine_5j' )
            $informations = $this->traitement_semaine(5,$parametres,$modification_periode);

        else if($parametres['format_calendrier'] == 'semaine_6j' )
            $informations = $this->traitement_semaine(6,$parametres,$modification_periode);

        // Traitement si option "Semaine 7j"
        else if($parametres['format_calendrier'] == 'semaine_7j' )
            $informations = $this->traitement_semaine(7,$parametres,$modification_periode);

        // Traitement si option "Mois"
        else
            $informations = $this->traitement_mois($parametres,$modification_periode);

        $debut = $informations['debut'];
        $fin = $informations['fin'];
        $liste_dates = $informations['liste_dates'];
        $date = $informations['date'];

        if(!isset($taches))
            list($taches, $demandes_cp) = $this->recuperation_taches($initialisation, $filtres_pour_fiche, $filtres_valeurs, $parametres, $informations);
        else {
            $taches = $taches->where(function ($r) use ($debut, $fin) {
                $r->where(function ($r) use ($debut, $fin) {
                    $r->where('date_de_debut', '>=', $debut['format_us'] . ' 00:00:00')
                        ->where('date_de_debut', '<=', $fin['format_us'] . ' 23:59:59');
                });
                $r->orWhere(function ($r) use ($debut, $fin) {
                    $r->where('date_de_fin', '>=', $debut['format_us'] . ' 00:00:00')
                        ->where('date_de_fin', '<=', $fin['format_us'] . ' 23:59:59');
                });
                $r->orWhere(function ($r) use ($debut, $fin) {
                    $r->where('date_de_debut', '<=', $debut['format_us'] . ' 00:00:00')
                        ->where('date_de_fin', '>=', $fin['format_us'] . ' 23:59:59');
                });
            });

            $demandes_cp = $demandes_cp->where(function ($r) use ($debut, $fin) {
                $r->where(function ($r) use ($debut, $fin) {
                    $r->where('date_de_debut', '>=', $debut['format_us'] . ' 00:00:00')
                        ->where('date_de_debut', '<=', $fin['format_us'] . ' 23:59:59');
                });
                $r->orWhere(function ($r) use ($debut, $fin) {
                    $r->where('date_de_fin', '>=', $debut['format_us'] . ' 00:00:00')
                        ->where('date_de_fin', '<=', $fin['format_us'] . ' 23:59:59');
                });
                $r->orWhere(function ($r) use ($debut, $fin) {
                    $r->where('date_de_debut', '<=', $debut['format_us'] . ' 00:00:00')
                        ->where('date_de_fin', '>=', $fin['format_us'] . ' 23:59:59');
                });
            });

        }


        $taches = $taches->get();
        $taches = $this->formatage_taches($taches);

        $demandes_cp = $demandes_cp->get();
        $demandes_cp = $this->formatage_demandes_cp($demandes_cp);

        $taches = $taches->merge($demandes_cp);

        $this->taches = $taches;

        service('jour_indisponibilite')->indisponibilites_calendrier($liste_dates,$debut,$fin,$parametres['format_calendrier']);

        if($parametres['format_calendrier'] == 'mois') {
            $agenda = $this->formate_agenda_mois($liste_dates, $taches);

            $donnees_retour = array(
                'dates' => $liste_dates,
                'date' => $date,
                'date_debut' => $debut,
                'date_fin' => $fin,
                'agenda' => $agenda,
            );
        }

        else{

            $tableau_des_heures = $this->recuperer_tranche_horaires($parametres);

            $agenda = $this->formate_agenda($liste_dates, $taches,$parametres,$tableau_des_heures);

            $this->definit_largeur_tache($agenda);

            $donnees_retour = array(
                'dates' => $liste_dates,
                'date' => $date,
                'aujourdhui' => date('Y-m-d'),
                'date_debut' => $debut,
                'date_fin' => $fin,
                'heures' => $tableau_des_heures,
                'agenda' => $agenda
            );
        }

        unset($parametres['date']);

        $valeurs_par_defaut_tache = array();

        if(request()->has('valeurs_par_defaut_tache'))
            $valeurs_par_defaut_tache = request()->get('valeurs_par_defaut_tache');

        $donnees_retour['modele_par_defaut_tache'] = $this->modele_par_defaut_tache($valeurs_par_defaut_tache,$filtres_pour_fiche);

        return array_merge($donnees_retour,$parametres);
    }

    /**
     * @return array|mixed
     *
     * Gére le paramétrage par défaut ainsi que le retour des données à l'initialisation
     *
     */
    public function recuperation_donnees_pour_initialisation(){

        $parametres = parametre_utilisateur('parametres_calendrier_'.$this->contexte, false, $this->id_utilisateur_actuel);

        $parametres_par_defaut = array(
            'format_calendrier' => 'semaine_5j',
            'tranche_horaire' => array(
                'heure_debut' => '08:00',
                'heure_fin' => '20:00',
            ),
            'type_taches_affichees' => 'les_deux',
            'granularite' => '30',
        );

        if($parametres == null){

            parametre_utilisateur('parametres_calendrier_'.$this->contexte, base64_encode(serialize($parametres_par_defaut)), $this->id_utilisateur_actuel);

            return $parametres_par_defaut;
        }

        $parametres = unserialize(base64_decode($parametres));

        $parametres['date'] = date('Y-m-d');

        foreach($parametres_par_defaut as $cle => $valeur){

            if(empty($parametres[$cle]))
                $parametres[$cle] = $valeur;
            else if($cle == 'tranche_horaire') {
                if(empty($parametres[$cle]['heure_debut']))
                    $parametres[$cle]['heure_debut'] = $valeur['heure_debut'];
                if(empty($parametres[$cle]['heure_fin']))
                    $parametres[$cle]['heure_fin'] = $valeur['heure_fin'];
            }
        }

        return $parametres;

    }


    /**
     * Chargement des données du calendrier sur 1 jour
     * @param $parametres
     * @param $modification_periode
     * @return array
     */
    public function traitement_jour(&$parametres, $modification_periode){

        $date = $parametres['date'];
        // Si l'utilisateur a cliqué sur un des boutons
        if($modification_periode !== false && $modification_periode !== 'false') {

            switch($modification_periode) {

                //Si l'utilisateur a cliqué sur suivant, la date de base avance de 7 jours
                case 'suivant':
                    $date = date('Y-m-d', strtotime("$date +1 day"));
                    break;

                //Si l'utilisateur a cliqué sur precedent, la date de base recule de 7 jours
                case 'precedent':
                    $date = date('Y-m-d', strtotime("$date -1 day"));
                    break;

                default:
                    $date = $modification_periode;

            }

        }

        //Déclaration des variables
        $liste_dates = array();

        array_push($liste_dates, $this->formate_date_pour_tableau($date));

        //On récupère la première date de la semaine et la dernière
        $debut['format_us'] = date('Y-m-d', strtotime($date));
        $debut['format_fr'] = date('d/m/Y', strtotime($date));
        $fin = end($liste_dates);

        return array(
            'debut' => $debut,
            'fin' => $fin,
            'liste_dates' => $liste_dates,
            'date' => $date,
        );
    }

    /**
     *
     * Permet de traiter le cas de la semaine de xj
     *
     */
    public function traitement_semaine($nombre_jours,&$parametres,$modification_periode){

        $date = $parametres['date'];
        // Si l'utilisateur a cliqué sur un des boutons
        if($modification_periode !== false && $modification_periode !== 'false') {

            switch($modification_periode) {

                //Si l'utilisateur a cliqué sur suivant, la date de base avance de 7 jours
                case 'suivant':
                    $date = date('Y-m-d', strtotime("$date +7 days"));
                    break;

                //Si l'utilisateur a cliqué sur precedent, la date de base recule de 7 jours
                case 'precedent':
                    $date = date('Y-m-d', strtotime("$date -7 days"));
                    break;

                default:
                    $date = $modification_periode;

            }

        }

        //Déclaration des variables
        $liste_dates = array();
        $jour_suivant =  '';
        $semaine_en_cours = lundi($date);

        // Tant qu'on parcourt la semaine en cours
        for($i = 0 ; $i < $nombre_jours; $i++) {

            //Le lundi + 1 jour de plus à chaque boucle (le format de date est celui accepté par strtotime: d-m-Y ou m/d/Y)
            $jour_suivant =  date("Y-m-d", strtotime($semaine_en_cours."+".$i." day"));

            //on enregistre la date dans le tableau
            array_push($liste_dates, $this->formate_date_pour_tableau($jour_suivant));

        }

        //On récupère la première date de la semaine et la dernière
        $debut['format_us'] = date('Y-m-d', strtotime($semaine_en_cours));
        $debut['format_fr'] = date('d/m/Y', strtotime($semaine_en_cours));
        $debut['numero_semaine'] = date('W', strtotime($semaine_en_cours));
        $fin = end($liste_dates);

        return array(
            'debut' => $debut,
            'fin' => $fin,
            'liste_dates' => $liste_dates,
            'date' => $date,
        );
    }

    /**
     *
     * Permet de traiter le cas du mois
     *
     */
    public function traitement_mois(&$parametres,$modification_periode){

        $date = $parametres['date'];

        // Si l'utilisateur a cliqué sur un des boutons
        if($modification_periode !== false && $modification_periode !== 'false') {

            $date_pour_calcul = formate_date('Y-m-01', $date);

            switch($modification_periode) {
                //Si l'utilisateur a cliqué sur suivant, la date de base avance de 7 jours
                case 'suivant':
                    $date = date("Y-m-d", strtotime("$date_pour_calcul +1 month"));
                    break;
                //Si l'utilisateur a cliqué sur precedent, la date de base recule de 7 jours
                case 'precedent':
                    $date = date("Y-m-d", strtotime("$date_pour_calcul -1 month"));
                    break;

                default:
                    $date = $modification_periode;

            }
        }


        // Déclaration des variables
        $liste_dates = array();
        $i = 0;
        $numero_semaine = 0;
        $liste_dates[$numero_semaine] = array();

        // Numéro du mois en cours
        $mois_en_cours = date("m", strtotime($date));

        //On récupère le premier jour du mois et le suivant
        $premier_jour = date("Y-m-01", strtotime($date));

        $jour_suivant = date("Y-m-02", strtotime($date));

        // Définition du premier jour du mois : quel jour est-ce dans la semaine?
        $jour_de_la_semaine = date("N",strtotime($premier_jour));

        // on ajoute des null dans le cas ou le premier jour du mois n'est pas un lundi
        for($i=1; $i<$jour_de_la_semaine; $i++) {

            array_push($liste_dates[$numero_semaine], null);
        }

        array_push($liste_dates[$numero_semaine], $this->formate_date_pour_tableau($premier_jour));

        // Traitement du second jour du mois
        // Si c'est un lundi
        if(date("N",strtotime($jour_suivant))== 1) {

            //On passe à une nouvelle semaine
            $numero_semaine ++;
            $liste_dates[$numero_semaine] = array();
        }


        //On insère le 2nd jour
        array_push($liste_dates[$numero_semaine], $this->formate_date_pour_tableau($jour_suivant));

        // Tant que le jour suivant est dans le mois à traiter
        while($mois_en_cours == date("m", strtotime($jour_suivant."+1 day"))) {


            //Le jour suivant à chaque tour de boucle
            $jour_suivant = date("Y-m-d", strtotime($jour_suivant."+1 day"));

            //Si c'est un lundi
            if(date("N",strtotime($jour_suivant))== 1) {

                //On passe à une nouvelle semaine
                $numero_semaine ++;
                $liste_dates[$numero_semaine] = array();
            }

            // On enregistre la date dans le tableau, dans la bonne semaine
            array_push($liste_dates[$numero_semaine], $this->formate_date_pour_tableau($jour_suivant));


        }

        //On récupère la première date du mois et la dernière
        $debut['format_us'] = date('Y-m-d', strtotime($premier_jour));
        $debut['format_fr'] = date('d/m/Y', strtotime($premier_jour));
        $derniere_semaine = end($liste_dates);
        $fin = end($derniere_semaine);

        if(max(array_keys($derniere_semaine))!= 6){
            for($i = max(array_keys($derniere_semaine)) +1 ;$i<=6;$i++){
                $liste_dates[max(array_keys($liste_dates))][$i] = null;
            }
        }

        return array(
            'debut' => $debut,
            'fin' => $fin,
            'liste_dates' => $liste_dates,
            'date' => $date,
        );
    }

    /**
     *
     * Permet de formater les demandes de CP
     *
     */
    public function formatage_taches($taches){

        $management_tache = management('tache');
        $utilisateurs = modele('utilisateur')->get()->keyBy('id');

        foreach($taches as &$tache){

            $management_tache->modele = $tache;
            $management_tache->charge_valeurs_champs_multiselection($tache);

            $tache->label = $tache->prive == 1 && $tache->affectation != moi()->id ? traduction('interface.calendrier.rdv_prive') : $management_tache->affichage_pour_calendrier();
        }

        $this->gestion_couleur_taches($taches);

        return $taches;
    }

    /**
     *
     * Permet de formater les demandes de CP
     *
     */
    public function formatage_demandes_cp($demandes_cp){

        $management = management('employe_demande_conge');

        foreach($demandes_cp as &$demande_cp){

            $management->charge_valeurs_champs_multiselection($demande_cp);
        }

        // Un congé peut être sur plusieurs jours donc on va multiplier les demandes de CP par le nombre de jours de congé
        foreach ($demandes_cp as $demande) {

            $management->modele = $demande;

            // On ajoute un affichage pour le calendrier
            $demande->label = $management->affiche();

            $demande->type_element="demande_cp";

            $demande->date_de_debut = date('Y-m-d H:i:s', strtotime(
                $demande->date_de_debut . ($demande->periode_de_debut === 0 ? ' +8 hours' : ' +13 hours')
            ));
            $demande->date_de_fin = date('Y-m-d H:i:s', strtotime(
                $demande->date_de_fin  . ($demande->periode_de_fin === 0 ? ' +13 hours' : ' +18 hours')
            ));

            if(isset($demande->client_id))
                $demande->client_id_formate = management('tache')->affiche_client_sur_calendrier($demande->client_id);
        }

        return $demandes_cp;
    }

    /**
     *
     *
     * Fonction qui enregistre une date sous plusieurs formats et la retourne sous forme d'objet. Participe à l'affichage basique du calendrier.
     *
     */
    public function formate_date_pour_tableau($date){

        // On enregistre les versions us et fr
        $tab_a_retourner['format_us'] = date('Y-m-d',strtotime($date));
        $tab_a_retourner['format_fr'] = date('d/m/Y', strtotime($date));
        $tab_a_retourner['format_date_du_jour'] = date('d',strtotime($date));

        $date_formatee = date('d/m', strtotime($date));

        // En fonction de l'indice du jour de la semaine
        switch(date('N',strtotime($date))) {
            case 1 :
                $tab_a_retourner['date'] = $date_formatee;
                $tab_a_retourner['index_traduction'] = 'interface.jours.lundi';
                break;

            case 2 :
                $tab_a_retourner['date'] = $date_formatee;
                $tab_a_retourner['index_traduction'] = 'interface.jours.mardi';
                break;

            case 3 :
                $tab_a_retourner['date'] = $date_formatee;
                $tab_a_retourner['index_traduction'] = 'interface.jours.mercredi';
                break;

            case 4 :
                $tab_a_retourner['date'] = $date_formatee;
                $tab_a_retourner['index_traduction'] = 'interface.jours.jeudi';
                break;

            case 5 :
                $tab_a_retourner['date'] = $date_formatee;
                $tab_a_retourner['index_traduction'] = 'interface.jours.vendredi';
                break;

            case 6 :
                $tab_a_retourner['date'] = $date_formatee;
                $tab_a_retourner['index_traduction'] = 'interface.jours.samedi';
                break;

            case 7 :
                $tab_a_retourner['date'] = $date_formatee;
                $tab_a_retourner['index_traduction'] = 'interface.jours.dimanche';
                break;
        }

        return $tab_a_retourner;

    }

	/**
	 *
	 *  Retourne un tableau contenant un array de dates et d'événements correspondants pour l'affichage aux formats semaine_5j et semaine_7j. Participe à l'affichage de l'agenda.
	 *
	 */
    public function formate_agenda($dates, $taches,$parametres, $tableau_des_heures){

        $minimum_tranche_horaire = '00:00';
        $fin_tranche = '24:00';
        $granularite = $parametres['granularite'];
        $fonctionnalite_affichage_journee_entiere = fonctionnalite('affichage_taches_journee_entiere');

        //Pour chaque jour et chaque créneau, on associe la tache qui correspond
        $agenda = array();

        // on boucle sur chaque jour de la semaine
        foreach($dates as $jour){

            $date_format_us = $jour['format_us'];

            $agenda[$date_format_us] = array(
                'taches' => array('journee_entiere' => array())
            );

        }
        foreach($taches as $evenement) {

            $date_debut = date('Y-m-d', strtotime($evenement['date_de_debut']));

            // on vérifie le créneau horaire
            $debut_heure_minutes_tache = date('H:i', strtotime($evenement['date_de_debut']));

            $heure_minute_fin_tache = date('H:i', strtotime($evenement['date_de_fin']));

            $evenement->draggable = true;
            $evenement->affichage_nom_tache = '';
            $evenement->pourcentage_avant_debut = 0;
            $evenement->commentaire_title = strip_tags($evenement->commentaire);

            if($evenement->prive == 1 && $evenement->affectation != moi()->id){

                $evenement->titre = traduction('interface.calendrier.rdv_prive');
                $evenement->commentaire = '';
            }

            if (date('Y-m-d', strtotime($evenement['date_de_debut'])) != date('Y-m-d', strtotime($evenement['date_de_fin'])) ) {

                $date_time_debut = new \DateTime($evenement['date_de_debut']);
                $date_time_fin = $date_time_debut->diff(new \DateTime($evenement['date_de_fin']));

                $date_time_fin_jours = $date_time_fin->days;

                if(date('H:i', strtotime($evenement['date_de_debut'])) > date('H:i', strtotime($evenement['date_de_fin'])))
                    $date_time_fin_jours++;

                $date_time_fin_jours_avant = $date_time_fin_jours;

                if(date('H:i', strtotime($evenement['date_de_fin'])) == '00:00')
                    $date_time_fin_jours--;

                if($date_time_fin_jours > 0){

                    $evenement->affichage_nom_tache = '(1/'.($date_time_fin_jours+1).')';

                    for($i = 1; $i <= $date_time_fin_jours; $i++) {

                        $date_time_debut = $date_time_debut->add(new \DateInterval('P1D'));

                        if (isset($agenda[$date_time_debut->format('Y-m-d')])) {

                            if($i == $date_time_fin_jours)
                                $difference_debut_fin = strtotime($heure_minute_fin_tache) - strtotime($minimum_tranche_horaire);
                            else
                                $difference_debut_fin = strtotime($fin_tranche) - strtotime($minimum_tranche_horaire);

                            $difference_par_60 = $difference_debut_fin / 60;
                            $difference_par_30 = $difference_par_60 / $granularite;

                            $evenement->nombre_demies_heures = $difference_par_30;
                            $evenement_clone = clone $evenement;
                            $evenement_clone->id_groupe = strval($evenement->id . '.' . $i);
                            $evenement_clone->affichage_nom_tache = '(' . ($i + 1) . '/' . ($date_time_fin_jours + 1) . ')';

                            if($fonctionnalite_affichage_journee_entiere === 'bandeau' && isset($evenement['journee_entiere']) && $evenement['journee_entiere'] == 1)
                                $agenda[$date_time_debut->format('Y-m-d')]['taches']['journee_entiere'][] = $evenement_clone;
                            else
                                $agenda[$date_time_debut->format('Y-m-d')]['taches'][$minimum_tranche_horaire][] = $evenement_clone;
                        }
                    }
                }

                $heure_minute_fin_tache = $fin_tranche;
            }

            if(isset($agenda[$date_debut])) {

                $difference_debut_fin = strtotime($heure_minute_fin_tache) - strtotime($debut_heure_minutes_tache);
                $difference_par_60 = $difference_debut_fin / 60;
                $difference_par_30 = $difference_par_60 / $granularite;

                $evenement->nombre_demies_heures = $difference_par_30;
                $evenement->id_groupe = $evenement->id;

                $debut_minutes_tache_int = intval(date('i', strtotime($debut_heure_minutes_tache)));
                $reste_minutes_tache = $debut_minutes_tache_int % $granularite;

                if($reste_minutes_tache != 0){

                    $minute_du_creneau = $debut_minutes_tache_int - $reste_minutes_tache;
                    $debut_heure_minutes_tache = date('H', strtotime($debut_heure_minutes_tache)).':'.sprintf('%02d', $minute_du_creneau);

                    $pourcentage_avant_debut = $reste_minutes_tache * 100 / $granularite;

                    $evenement->pourcentage_avant_debut = $pourcentage_avant_debut;
                }

                if($fonctionnalite_affichage_journee_entiere === 'bandeau' && isset($evenement['journee_entiere']) && $evenement['journee_entiere'] == 1)
                    $agenda[$date_debut]['taches']['journee_entiere'][] = $evenement;
                else
                    $agenda[$date_debut]['taches'][$debut_heure_minutes_tache][] = $evenement;
            }
        }

		return $agenda;
    }

    /**
     *
     *
     * Retourne un tableau contenant un array de dates et d'événements correspondants pour l'affichage au format mois complet. Participe à l'affichage de l'agenda.
     *
     */
    public function formate_agenda_mois($dates, $taches) {

        $agenda = array();

		foreach($dates as $id_semaine => $semaine) {

			$agenda[$id_semaine] = array();

			foreach($semaine as $date) {

				$taches_de_la_journee = array();

                if($date === null){
                    $agenda[$id_semaine][] = array(

                        'date' => $date,
                        'taches' => array_values($taches_de_la_journee),
                    );
                    continue;
                }

				foreach($taches as $tache) {

                    $debut_de_la_tache = formate_date('Ymd', $tache->date_de_debut);
                    $debut_de_la_tache_organisation = formate_date('YmdHms', $tache->date_de_debut);
                    $fin_de_la_tache = formate_date('Ymd', $tache->date_de_fin);
                    $tache->commentaire_title = strip_tags($tache->commentaire);

                    if($tache->prive == 1 && $tache->affectation != moi()->id){

                        $tache->titre = traduction('interface.calendrier.rdv_prive');
                        $tache->commentaire = '';
                    }

                    if(formate_date('Y-m-d', $tache->date_de_debut) == $date['format_us'] ||
                        formate_date('Y-m-d', $tache->date_de_fin) == $date['format_us'] ||
                        ($date['format_us'] > formate_date('Y-m-d', $tache->date_de_debut) &&
                            $date['format_us'] < formate_date('Y-m-d', $tache->date_de_fin)
                        )
                    ){
                        $date_time_debut = new \DateTime($tache->date_de_debut);
                        $date_time_difference = $date_time_debut->diff(new \DateTime($tache->date_de_fin));

                        $nombre_total = $date_time_difference->days;

                        $tache_clone = clone $tache;
                        $tache_clone->draggable = true;

                        if($nombre_total > 0) {

                            if(formate_date('Y-m-d', $tache->date_de_debut) == $date['format_us'])
                                $jour = 0;

                            elseif( formate_date('Y-m-d', $tache->date_de_fin) == $date['format_us'])
                                $jour = $nombre_total ;

                            else
                                $jour = $date_time_debut->diff(new \DateTime($date['format_us']))->days +1 ;

                            $tache_clone->affichage_nom_tache = '('.($jour+1).'/'.($nombre_total+1).')';
                        }

                        $taches_de_la_journee["$debut_de_la_tache_organisation$fin_de_la_tache$tache->id"] = $tache_clone;

                    }

				}

                ksort($taches_de_la_journee);

				$agenda[$id_semaine][] = array(

					'date' => $date,
					'taches' => array_values($taches_de_la_journee),
				);
			}
		}

		return $agenda;
    }

    /**
     * @param $agenda
     * @param $creneaux
     * @return void
     *
     * Permet de gérer le placement de chaque tâche dans le calendrier
     *
     */
    public function definit_largeur_tache(&$agenda){

        $tableau_des_heures = [];

        for ($heure = 0; $heure < 24; $heure++) {
            for ($minute = 0; $minute < 60; $minute++) {
                $tableau_des_heures[] = sprintf('%02d:%02d', $heure, $minute);
            }
        }

        //On effectue une répartition par jour
		foreach($agenda as $date => &$creneaux_de_la_journee) {

            $tache_repartition_heure = [];
            $date_jour = new \DateTime($date);

            // On crée un tableau avec des celulles vides pour remplir progressivement les tâches dans ce tableau et combler le vide si besoin
            foreach($tableau_des_heures as $index => $heure){
                $tache_repartition_heure[$heure][] = 'vide';
            }

            ksort($creneaux_de_la_journee['taches']);

            foreach($creneaux_de_la_journee['taches'] as $heure => $taches) {

                if($heure == 'journee_entiere')
                    continue;

                //Pour chaque tache, on récupére la duree et on remplit $tache_repartition_heure
                foreach ($taches as $evenement) {

                    $date_debut = new \DateTime($evenement->date_de_debut);
                    $date_fin = new \DateTime($evenement->date_de_fin);

                    if($date_debut->format('Y-m-d') < $date_jour->format('Y-m-d'))
                        $date_debut = new \DateTime($date_jour->format('Y-m-d 00:00:00'));

                    if($date_fin->format('Y-m-d') > $date_jour->format('Y-m-d'))
                        $date_fin = new \DateTime($date_jour->format('Y-m-d 23:59:59')); 

                    // Calcul en minutes entières à partir des timestamps complets (pas de re-parsing H:i:s)
                    $duree = (int) round(($date_fin->getTimestamp() - $date_debut->getTimestamp()) / 60);

                    $informations_taches[$evenement->id_groupe]['duree'] = $duree;

                    $heure_minustes_debut = $date_debut->format('H:i');
                    $index_heure = array_flip($tableau_des_heures)[$heure_minustes_debut];

                    $heures_impactes = [];

                    // Sécurité : on ne dépasse jamais le dernier index du tableau
                    $index_fin = min($index_heure + ($duree - 1), count($tableau_des_heures) - 1);

                    if($index_heure + 1 <= $index_fin) {
                        foreach (range($index_heure + 1, $index_fin) as $index) {
                            $heures_impactes[] = $tableau_des_heures[$index];
                        }
                    }

                    $index_disponible = null;

                    // On cherche un index dans $tache_repartition_heure vide pour pouvoir y ajouter la tâche
                    foreach($tache_repartition_heure[$heure_minustes_debut] as $index => $valeur){

                        if($index_disponible !== null)
                            continue;

                        // Si on a un index vide on regarde s'il est vide pour toutes les heures impactés égalemen,t
                        if($valeur == 'vide'){

                            $index_disponible = $index;

                            foreach($heures_impactes as $heure_impacte){

                                if($tache_repartition_heure[$heure_impacte][$index] != 'vide')
                                    $index_disponible = null;
                            }
                        }
                    }

                    $heures_impactes[] = $heure_minustes_debut;

                    //Si on a pas de place disponible on crée un nouvel index
                    if($index_disponible === null){

                        foreach($tache_repartition_heure as &$valeurs){
                            $valeurs[] = 'vide';
                        }

                        $index_disponible = array_key_last($tache_repartition_heure[$heure]);
                    }

                    // On remplit le tableau
                    foreach($heures_impactes as $heure_impacte){
                        $tache_repartition_heure[$heure_impacte][$index_disponible] = $evenement->id_groupe;
                    }

                    // On enregistre l'ordre de la tâche
                    $evenement['ordre'] = $index_disponible;
                }
            }

            // On enregistre la taille des tâches
            $creneaux_de_la_journee['pourcentage_taille_evenement'] = 100 / sizeof(array_values($tache_repartition_heure)[0]);
        }
    }

    /**
     *
     * Permet de récupérer la tranche horaire adéquate
     *
     */
    public function recuperer_tranche_horaires(&$parametres, $respecter_tranche = false){

        if(!isset($parametres['tranche_horaire'], $parametres['tranche_horaire']['heure_debut'], $parametres['tranche_horaire']['heure_fin']))
            $parametres['tranche_horaire'] = [
                'heure_debut' => '08:00',
                'heure_fin' => '20:00',
            ];

        $granularite = $parametres['granularite'];

        // On va chercher la tâche avec l'heure minimal de début
        if($respecter_tranche){

            $tranche_actuel = $parametres['tranche_horaire']['heure_debut'];
            $tranche_fin = $parametres['tranche_horaire']['heure_fin'];
        }else{

            $tranche_actuel = '00:00';
            $tranche_fin = date('H:i', strtotime('24:00 -'.$granularite.' minutes'));
        }

        $heures = [$tranche_actuel];

        // Permet de récupérer les heures / créneaux entre tranches, selon la granularité choisie (15min / 30min / 1h)
        while($tranche_actuel < $tranche_fin){

            $tranche_actuel = date('H:i', strtotime($tranche_actuel.' +'.$granularite.' minutes'));
            $heures[] = $tranche_actuel;
        }

        return $heures;

    }

    /**
     * @param $taches
     *
     * Permet de gérer la couleur des tâches
     *
     */
    public function gestion_couleur_taches(&$taches){

        $management = management('tache');

        foreach($taches as &$tache) {

            $management->modele = $tache;

            $style = $management->recuperer_couleur_tache();

            foreach($style as $attribut => $valeur){

                $style[$attribut] = $attribut . ':' . $valeur;
            }

            $tache['style'] = implode(';',$style);
        }
    }

    /**
     *
     * Permet de gérer les filtres
     *
     */
    public function gestion_des_filtres(&$filtres,&$taches,&$demandes_cp){

        $filtres_a_afficher = $this->filtres();

        //Pour chaque filtre gérer on va appliquer le filtrage pour le champ de tache et le champ d'employe_demande_conge
        foreach($filtres as $filtre){

            $modele_filtre = null;

            foreach($filtres_a_afficher as $filtre_a_afficher){

                if($filtre_a_afficher['id'] == $filtre['id'])
                    $modele_filtre = $filtre_a_afficher;
            }

            if(empty($modele_filtre))
                continue;

            if(!empty($filtre['valeurs'])) {
                foreach ($modele_filtre['valeurs_dans_champs'] as $type_element => $nom_sql) {

                    $champ = champ_libre($type_element, $nom_sql);

                    if ($type_element == 'tache')
                        $taches = $champ->champ->applique_filtre_sur_requete($filtre['valeurs'], $taches);

                    if ($type_element == 'employe_demande_conge')
                        $demandes_cp = $champ->champ->applique_filtre_sur_requete($filtre['valeurs'], $demandes_cp);
                }
            }
        }

    }

    /**
     *
     * Permet de récupérer les filtres utilisés pour le calendrier
     *
     */
    public function filtres(){

        $filtres = array(
            array(
                'id' => 1,
                'nom_sql' => 'affectation',
                'type_element' => 'tache',
                'valeurs_dans_champs' => array(
                    'tache' => 'affectation',
                    'employe_demande_conge' => 'employe_id',
                ),
                'index_traduction' => 'champs_libres.tache.affectation.nom',
                'type_filtre' => management('tache')->champ('affectation')->type_filtre
            ),
            array(
                'id' => 2,
                'nom_sql' => 'type_tache_rdv',
                'type_element' => 'tache',
                'valeurs_dans_champs' => array(
                    'tache' => 'type_tache_rdv',
                ),
                'index_traduction' => 'champs_libres.tache.type_tache_rdv.nom',
                'type_filtre' => management('tache')->champ('type_tache_rdv')->type_filtre
            ),
            array(
                'id' => 3,
                'nom_sql' => 'client_id',
                'type_element' => 'tache',
                'valeurs_dans_champs' => array(
                    'tache' => 'client_id',
                ),
                'index_traduction' => 'champs_libres.tache.client_id.nom',
                'type_filtre' => management('tache')->champ('client_id')->type_filtre
            )
        );

        // On regarde si au moins une des taches à un projet pour afficher le filtre
        $count_tache_avec_projet = modele('tache')->where('projet_id','>',0)->zero_ou_null('inactif')->count();

        if ($count_tache_avec_projet > 0)
            $filtres[] = array(
                'id' => 4,
                'nom_sql' => 'projet_id',
                'type_element' => 'tache',
                'index_traduction' => 'champs_libres.tache.projet_id.nom',
                'valeurs_dans_champs' => array(
                    'tache' => 'projet_id',
                ),
                'type_filtre' => management('tache')->champ('projet_id')->type_filtre
            );

        foreach($filtres as $cle_filtre => $filtre){

            $filtres[$cle_filtre]['modele'] = management($filtre['type_element'])->champ($filtre['nom_sql'])->modele;
        }

        if(!empty(request()->filtres_pour_fiche)){

            $types_filtres_fiche = array_keys(request()->filtres_pour_fiche);

            foreach($filtres as $cle_filtre => $filtre){

                if(in_array($filtre['modele']->type_element_ajax, $types_filtres_fiche))
                    unset($filtres[$cle_filtre]);
            }
        }

        return $filtres;
    }

    /**
     *
     * Permet de gérer les filtres pour fiche
     *
     */
    public function gestion_des_filtres_pour_fiche($filtres,&$taches,&$demandes_cp){

        $tables_a_filtrer = array('tache','employe_demande_conge');

        foreach($tables_a_filtrer as $table){

            foreach($filtres as $type_element_ajax => $valeur){

                $champ = Champ_libre::where('type_element',$table)
                    ->whereNotIn('nom_sql',['cree_par','modifie_par'])
                    ->where('type_element_ajax',$type_element_ajax)->first();

                //Si le champ n'existe pas, on n'affiche aucune des données en mettant un where id = 0 ce qui est impossible
                if($champ == null){
                    $nom_sql = $table . '.id';
                    $valeur = 0;
                }
                else
                    $nom_sql = $champ->nom_sql;

                if($table == 'tache')
                    $taches->where($nom_sql, $valeur);

                else
                    $demandes_cp->where($nom_sql, $valeur);

            }
        }
    }

    /**
     *
     * Permet de récupérer le modèle par défaut des tâches en fonction des valeurs par defaut et des filtres pour fiche
     *
     */
    public function modele_par_defaut_tache($valeurs_par_defaut_tache,$filtres_pour_fiche){

        $modele_par_defaut = service('modele_par_defaut')->recupere('tache');

        if(is_array($valeurs_par_defaut_tache)) {

            foreach($valeurs_par_defaut_tache as $champ => $valeur) {

                $modele_par_defaut->{$champ} = $valeur;
            }
        }

        foreach($filtres_pour_fiche as $type_element_ajax => $valeur){

            $champ = Champ_libre::where('type_element','tache')->where('type_element_ajax',$type_element_ajax)->first();

            if($champ == null)
                continue;

            $modele_par_defaut->{$champ->nom_sql} = $valeur;
        }

        return $modele_par_defaut;
    }

    /**
     *
     * Renvoie les utilisateurs pour lesquels il faut imprimer le calendrier
     *
     */
    public function taches_par_utilisateurs_impression($semaine_voulue, $parametres_initiaux, $utilisateurs) {

        $utilisateurs_ids = $utilisateurs->pluck('id');
        $debut_periode = Arr::first($parametres_initiaux['semaines_voulues'])['debut_semaine'];
        $fin_periode = Arr::last($parametres_initiaux['semaines_voulues'])['fin_semaine'];

        $taches_par_utilisateurs = modele('tache')
            ->select('date_de_debut', 'date_de_fin', 'affectation',DB::raw("COALESCE(journee_entiere,0) as journee_entiere"))
            ->whereIn('affectation', $utilisateurs_ids)
            ->where('tache.date_de_debut', '>=', $debut_periode . ' 00:00:00')
            ->where('tache.date_de_fin', '<=', $fin_periode . ' 23:59:59');

        $management_tache = management('tache');

        foreach($parametres_initiaux['filtres_avec_valeurs'] as $nom_filtre => $valeurs){

            if($nom_filtre == 'affectation')
                continue;

            $taches_par_utilisateurs = $management_tache->champ($nom_filtre)->applique_filtre_sur_requete($valeurs, $taches_par_utilisateurs);
        }

        return $taches_par_utilisateurs->get()->groupBy('affectation');
    }

    /**
     *
     * Génère le PDF du calendrier lié à un seul utilisateur
     * Retourne le chemin du pdf généré
     *
     */
    public function generation_pdf_utilisateur($semaine_voulue, $parametres_initiaux) {

        $merger = \PDFMerger::init();
        $pdfs_a_supprimer = array();

        foreach($parametres_initiaux['filtres'] as $index => $filtre){

            if($filtre['id'] == 1)
                unset($parametres_initiaux['filtres'][$index]);
        }

        $parametres = [

            'format_calendrier' => $parametres_initiaux['format_calendrier'] ?? 'semaine_5j',
            'type_taches_affichees' => $parametres_initiaux['type_taches_affichees'],
            'modification_periode' => false,
            'filtres_pour_fiche'=> [],
            'filtres' => $parametres_initiaux['filtres'][] = [['id' => 1, 'valeurs' => [$parametres_initiaux['utilisateur']->id]]],
            'valeurs_par_defaut_tache' => [],
            'granularite' => '30',
        ];

        $prenom = str_replace([" ", "\""], ["_", ""], $parametres_initiaux['utilisateur']->prenom);
        $nom = str_replace([" ", "\""], ["_", ""], $parametres_initiaux['utilisateur']->nom);

        foreach($parametres_initiaux['semaines_voulues'] as $semaine){

            $parametres['date'] = $semaine['debut_semaine'];
            $parametres['tranche_horaire'] = $this->recuperer_tranche_horaires_impression($semaine, $parametres_initiaux);

            $donnees_calendrier = $this->recuperation_donnees($parametres);

            $donnees_calendrier = array_merge($donnees_calendrier, [
                'logo_application' => $parametres_initiaux['logo_application'],
                'utilisateur' => $parametres_initiaux['utilisateur'],
                'numero_semaine' => date_create_from_format('Y-m-d', $semaine['debut_semaine'])->format('W')
            ]);

            $donnees_calendrier['heures'] = $this->recuperer_tranche_horaires($parametres, true);

            $pdf = PDF::loadView('eden::pdf.calendrier', $donnees_calendrier)->setPaper('a4', 'landscape');

            $options = $pdf->getDomPDF()->getOptions();
            $options->set('isRemoteEnabled', true);
            $options->set('isPhpEnabled', true);
            $options->set('isHtml5ParserEnabled', true);
            $pdf->getDomPDF()->setOptions($options);

            $chemin_pdf = 'public/planning/calendrier_' . $prenom . '_' . $nom . '_semaine_' . $donnees_calendrier['numero_semaine'] . '.pdf';

            Storage::put($chemin_pdf, $pdf->output());

            $pdfs_a_supprimer[] = $chemin_pdf;
            $merger->addPDF(storage_path('app/' . $chemin_pdf), 'all', 'L');
        }

        $chemin_pdf = 'public/planning/calendrier_' . $prenom . '_' . $nom . '.pdf';

        $merger->merge();
        $merger->save(storage_path('app/'.$chemin_pdf));

        foreach($pdfs_a_supprimer as $pdf){

            Storage::delete(strchr($pdf,'tmp'));
        }

        return storage_path('app/' . $chemin_pdf);
    }

    public function recuperer_tranche_horaires_impression($semaine, $parametres_initiaux) {

        if(!isset($this->taches_par_utilisateur[$parametres_initiaux['utilisateur']->id]))
            return array(
                'heure_debut' => '05:00',
                'heure_fin' => '20:00',
            );

        $taches_semaine = $this->taches_par_utilisateur[$parametres_initiaux['utilisateur']->id]
            ->where('journee_entiere',0)
            ->where('date_de_debut', '>=',$semaine['debut_semaine'])
            ->where('date_de_fin', '<=', $semaine['fin_semaine']);
        
        $tranche_horaire = [];

        $heure_limite_debut = date_create_from_format('Y-m-d H:i:s', date('Y-m-d 00:30:s'))->getTimestamp();
        $heure_limite_fin = date_create_from_format('Y-m-d H:i:s', date('Y-m-d 23:30:s'))->getTimestamp();

        foreach($taches_semaine as $tache){

            $heure_debut = date_create_from_format('Y-m-d H:i:s', date('Y-m-d' . date_create_from_format('Y-m-d H:i:s', $tache->date_de_debut)->format('H:i') . ':s'));

            if($heure_debut->getTimestamp() > $heure_limite_debut)
                $heure_debut = $heure_debut->modify('-30 minutes');

            $heure_fin = date_create_from_format('Y-m-d H:i:s', date('Y-m-d' . date_create_from_format('Y-m-d H:i:s', $tache->date_de_fin)->format('H:i') . ':s'));

            if($heure_fin->getTimestamp() < $heure_limite_fin && $heure_fin->format('H:i') != '00:00')
                $heure_fin = $heure_fin->modify('+30 minutes');

            if(empty($tranche_horaire)){

                $tranche_horaire = ['heure_debut' => $heure_debut->format('H:i'),'heure_fin' => $heure_fin->format('H:i'),];
                continue;
            }

            $debut_reference = date_create_from_format('Y-m-d H:i:s', date('Y-m-d'. $tranche_horaire['heure_debut'] . ':s'));

            if($debut_reference->getTimestamp() > $heure_limite_debut)
                $debut_reference = $debut_reference->modify('-30 minutes');

            $fin_reference = date_create_from_format('Y-m-d H:i:s', date('Y-m-d'. $tranche_horaire['heure_fin'] . ':s'));

            if($fin_reference->getTimestamp() < $heure_limite_fin)
                $fin_reference = $fin_reference->modify('+30 minutes');

            if($heure_debut->getTimestamp() < $debut_reference->getTimestamp())
                $tranche_horaire['heure_debut'] = $heure_debut->format('H:i');

            if($heure_fin->getTimestamp() > $fin_reference->getTimestamp())
                $tranche_horaire['heure_fin'] = $heure_fin->format('H:i');
            elseif($heure_fin->format('H:i') == '00:00')
                $tranche_horaire['heure_fin'] = '23:59';
        }

        return array(
            'heure_debut' => $tranche_horaire['heure_debut'] ?? '09:00',
            'heure_fin' => $tranche_horaire['heure_fin'] ?? '18:00',
        );
    }

    /**
     *
     * Envoi par email du PDF du calendrier/planning
     *
     */
    public function envoyer_par_mail($semaine_voulue, $parametres_initiaux) {

        $parametres_initiaux = $this->prepare_donnees_impression($semaine_voulue, $parametres_initiaux);

        $utilisateurs = $this->selection_utilisateurs_impression($semaine_voulue, $parametres_initiaux);

        $this->taches_par_utilisateur = $this->taches_par_utilisateurs_impression($semaine_voulue, $parametres_initiaux, $utilisateurs);

        foreach ($utilisateurs as $utilisateur){

            $parametres_initiaux['utilisateur'] = $utilisateur;

            $chemin_pdf = $this->generation_pdf_utilisateur($semaine_voulue, $parametres_initiaux);

            $parametres_email = [
                'type_configuration' => 1,
                'destinataire' => [$utilisateur->email],
                'sujet' => traduction('mails.calendrier.sujet', $utilisateur->langue),
                'pieces_jointes' => [$chemin_pdf],
            ];

            $variables_email = [
                'utilisateur' => $utilisateur,
                'semaine_voulue' => $semaine_voulue,
                'langue_destinataire' => $utilisateur->langue
            ];

            $retour = service('email')->envoyer('eden::mails.calendrier', $variables_email, $parametres_email);
        }

        return response()->json(['retour' => true]);
    }

    public function recuperation_taches($initialisation, $filtres_pour_fiche, $filtres_valeurs, &$parametres, $dates = false) {

        // Récupération des événements de la table "tache"
        $taches = modele('tache')
            ->select('tache.*')
            ->leftJoin('utilisateur', 'tache.affectation', 'utilisateur.id')
            ->leftJoin('utilisateur_restrictions_erp', function($query) {
                $query->on('utilisateur_restrictions_erp.cle_locale', '=', 'utilisateur.id')
                    ->where('utilisateur_restrictions_erp.valeur', 1);
            })
            ->whereNull('valeur')
            ->whereNotNull('type_tache_rdv');

        // On récupère les demandes de CP
        $demandes_cp = modele('employe_demande_conge')
            ->select('employe_demande_conge.*')
            ->leftJoin('utilisateur', 'employe_demande_conge.employe_id', 'utilisateur.id')
            ->leftJoin('utilisateur_restrictions_erp', function($query) {
                $query->on('utilisateur_restrictions_erp.cle_locale', '=', 'utilisateur.id')
                    ->where('utilisateur_restrictions_erp.valeur', 1);
            })
            ->whereNull('valeur')
            ->where(function($where){
                $where->whereNull('statut')
                    ->orWhere('statut', '<', 2);
            });

        if($dates !== false) {

            $debut = $dates['debut'];
            $fin = $dates['fin'];
            $this->applique_dates_requete($taches, $debut, $fin);
            $this->applique_dates_requete($demandes_cp, $debut, $fin);
        }

        $this->gestion_des_filtres($filtres_valeurs,$taches,$demandes_cp);

        if($initialisation) {
            $parametres['valeurs_filtres'] = $filtres_valeurs;
            $parametres['filtres'] = $this->filtres();
        }

        $this->gestion_des_filtres_pour_fiche($filtres_pour_fiche,$taches,$demandes_cp);

        $taches = $parametres['type_taches_affichees'] === 'conges' ? modele('tache')->whereRaw('false') : $taches;

        $demandes_cp = $parametres['type_taches_affichees'] === 'tache' ? modele('employe_demande_conge')->whereRaw('false') : $demandes_cp;

        return [$taches, $demandes_cp];
    }

    private function applique_dates_requete(&$requete, $debut, $fin){

        $requete->where(function ($r) use ($debut, $fin) {
            $r->where(function ($r) use ($debut, $fin) {
                $r->where('date_de_debut', '>=', $debut['format_us'] . ' 00:00:00')
                    ->where('date_de_debut', '<=', $fin['format_us'] . ' 23:59:59');
            });
            $r->orWhere(function ($r) use ($debut, $fin) {
                $r->where('date_de_fin', '>=', $debut['format_us'] . ' 00:00:00')
                    ->where('date_de_fin', '<=', $fin['format_us'] . ' 23:59:59');
            });
            $r->orWhere(function ($r) use ($debut, $fin) {
                $r->where('date_de_debut', '<=', $debut['format_us'] . ' 00:00:00')
                    ->where('date_de_fin', '>=', $fin['format_us'] . ' 23:59:59');
            });
        });
    }

    public function impression($semaine_voulue, $parametres_initiaux){

        $chemins_pdf = [];

        $utilisateurs = $this->selection_utilisateurs_impression($semaine_voulue, $parametres_initiaux);

        $this->taches_par_utilisateur = $this->taches_par_utilisateurs_impression($semaine_voulue, $parametres_initiaux, $utilisateurs);

        if($utilisateurs->isEmpty())
            throw new \Exception(traduction('interface.planning.imprimer.pas_de_taches'));

        foreach ($utilisateurs as $utilisateur){

            $parametres_initiaux['utilisateur'] = $utilisateur;

            $chemins_pdf[] = $this->generation_pdf_utilisateur($semaine_voulue, $parametres_initiaux);
        }

        $nom_du_pdf = 'calendriers_' . $semaine_voulue . '.pdf';

        return [$chemins_pdf,$nom_du_pdf];
    }
}
