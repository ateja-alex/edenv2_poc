<?php

namespace App\Eden\Managements\Rapports\Rapports;

use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_pdf_management;
use App\Eden\Models\Element_log;
use App\Eden\Variables;


use PDF;
use DB;


class Log_tentatives_connexion_management extends Rapports_management {

    /**
     *
     * On initialise les données du rapport
     *
     */
    function __construct() {
        $this->rapport = new Rapport_pdf_management();
    }
    function genere($ajax = false) {
        
        $donnees_pour_pdf = array();
        $donnees_pour_pdf['tableau_classement_ip'] = $this->tableau_classement_ip();

        $this->rapport->genere_pdf($donnees_pour_pdf);

        // enregistrement...
        return $this->rapport->genere($ajax);

    }


    /**
     *
     * Mise en forme de l'array + du HTML pour l'affichage
     *
     */
    public function tableau_classement_ip() {

        $ip_en_erreur = DB::table("log_tentative_connexion")
            ->select(DB::raw("ip, COUNT(*) as echec"))
            ->where('succes', 0)
            ->orderBy("echec",'DESC')
            ->groupBy("ip")
            ->get();

        $retour = array();
        $retour['total'] = array();

        foreach ($ip_en_erreur as $ip){

            $retour['total'][] = '<b>'.$ip->ip.'</b> : '.$ip->echec;
        }

        $ip_en_erreur_eden = DB::table("log_tentative_connexion")
            ->select(DB::raw("ip, COUNT(*) as echec"))
            ->where('succes', 0)
            ->where('source', 1)
            ->orderBy("echec",'DESC')
            ->groupBy("ip")
            ->get();

        $retour['eden'] = array();

        foreach ($ip_en_erreur_eden as $ip){

            $retour['eden'][] = '<b>'.$ip->ip.'</b> : '.$ip->echec;
        }

        $ip_en_erreur_extranet = DB::table("log_tentative_connexion")
            ->select(DB::raw("ip, COUNT(*) as echec"))
            ->where('succes', 0)
            ->where('source', 2)
            ->orderBy("echec",'DESC')
            ->groupBy("ip")
            ->get();

        $retour['extranet'] = array();

        foreach ($ip_en_erreur_extranet as $ip){

            $retour['extranet'][] = '<b>'.$ip->ip.'</b> : '.$ip->echec;
        }

        return $retour;
    }
}

?>