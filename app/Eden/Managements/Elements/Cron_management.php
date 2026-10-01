<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Controllers\Cron_controller;
use Illuminate\Support\Facades\DB;
use Mail;
use Illuminate\Support\Facades\Log;

class Cron_management extends Element_management {


    /**
     *
     * Permet d'optimiser des tables
     *
     */
    public static function optimisation_tables()
    {
        try {
            // On récupère les tables de la bdd
            $type_elements = DB::select("SELECT table_name
                FROM INFORMATION_SCHEMA.TABLES
                WHERE
                TABLE_TYPE = 'BASE TABLE'
                AND TABLE_SCHEMA = '" . env('DB_DATABASE') . "'");

            foreach ($type_elements as $type_element) {
                try {
                    $retour_optimisation = DB::select('OPTIMIZE TABLE ' . $type_element->table_name);

                    if(count($retour_optimisation) == 2 && $retour_optimisation[1]->Msg_text != "OK")
                        throw new \Exception("Erreur lors de l'optimisation de la table {$type_element} : {$retour_optimisation}");
                }
                catch (\Exception $e){
                    Log::error("Erreur lors de l'execution du CRON dans la table {$type_element} : {$e}");
                }
            }
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'execution du CRON optimisation_tables : {$e}");
            return false;
        }
        return true;
    }

    /*
     *
     * Permet d'initialiser et de vérifier les paramètres du cron
     * $expiration est la durée en secondes après laquelle il faut réinitialiser le cron
     *
     */
    public static function initialisation_parametres($cron){

        $derniere_execution = new \DateTime($cron->derniere_execution);

        if($cron->en_cours == 1){

            //Si le cron est en cours depuis plus d'un jour, on réinitialise le paramètre et on envoie un mail au support
            if(!empty($derniere_execution) && $derniere_execution->getTimestamp() + 60*60*$cron->temps_avant_relance < time()){

                management('cron', $cron->id, $cron)->enregistre_modele(['en_cours' => 0]);

                try {

                    // on prépare les données pour envoyer le mail
                    $service_email = service('email');

                    $parametres_email = [
                        'type_configuration' => 1,
                        'destinataire' => [fonctionnalite('adresse_mail_support')],
                        'sujet' => 'Réinitialisation cron',
                    ];

                    $variables_email = [
                        'cron' => $cron->nom,
                        'projet' => maquette('nom_application')
                    ];

                    $retour = $service_email->envoyer('eden::mails.cron_reinitialise', $variables_email, $parametres_email);

                } catch(\Exception $e){
                    Log::warning('L\'envoi de mails de réinitialisation de cron n\'a pas fonctionné.');
                }

            }
            else
                return false;
        }

        // Verrou atomique : une seule exécution gagne, même lancée en même temps
        // depuis plusieurs pods (lecture puis écriture séparées = deux gagnants possibles)
        $verrou_pris = DB::table('cron')
            ->where('id', $cron->id)
            ->where(fn($requete) => $requete->where('en_cours', 0)->orWhereNull('en_cours'))
            ->update(['en_cours' => 1, 'derniere_execution' => date('Y-m-d H:i:s')]);

        if($verrou_pris !== 1)
            return false;

        $cron->en_cours = 1;

        return true;
    }

    /**
     *
     * Retourne les actions sur les listes
     *
     */
    public function actions_a_afficher($id_liste) {

        // on récupère les actions principales
        $actions = parent::actions_a_afficher($id_liste);

        $actions['maj_crons'] = '<span class="dropdown-item" @click="maj_crons()"><i class="fa fa-fw fa-check"></i><span v-html="$root.traduction(\'interface.listes.maj_crons\')"></span></span>';

        return $actions;
    }

    /*
     *
     * Récupère les crons dans le fichier std et le fichier spé
     *
     */
    public function recuperer_crons(){

        $controleur_std = Cron_controller::class;
        $crons = array_fill_keys(get_class_methods($controleur_std), 1);
        $fonctions_a_eviter = [
            '__construct', '__destruct', 'middleware', 'getMiddleware', 
            'callAction', '__call', 'authorize', 'authorizeForUser', 'authorizeResource', 
            'dispatchSync', 'validateWith', 'validate', 'validateWithBag'
        ];

        if(file_exists(app_path('Http/Controllers/Cron_controller.php'))){

            $controleur_spe = \App\Http\Controllers\Cron_controller::class;
            $crons = $crons + array_fill_keys(get_class_methods($controleur_spe), 0);
        }
        foreach($crons as $nom => $standard){

            if(in_array($nom,$fonctions_a_eviter))
                unset($crons[$nom]);
        }

        return $crons;
    }

    /**
     *
     * Crée les details d'une ligne
     *
     */
    public function recuperer_details_ligne_pour_liste($id_element,$type_element,$id_liste_parent) {

        // On va chercher les lignes qui nous interessent
        $parametres = modele('cron_parametres')->where('id_cron',$id_element)->get();

        // On appelle la vue qui affiche les infos que l'on veut via un render();
        $vue = "eden::listes.includes.details_ligne_cron";
        $vue_render = view($vue, array(

            'parametres' => $parametres
        ))->render();

        return $vue_render;
    }

    public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();

        if(in_array('apercu',$liste_options))
            unset($liste_options[array_search('apercu',$liste_options)]);

        if(in_array('supprimer',$liste_options))
            unset($liste_options[array_search('supprimer',$liste_options)]);

        if(in_array('dupliquer',$liste_options))
            unset($liste_options[array_search('dupliquer',$liste_options)]);

        $liste_options[] = 'supprimer_parametres_lies';
        $liste_options[] = 'zoom';
        $liste_options[] = 'executer_cron';

        return $liste_options;
    }
}
