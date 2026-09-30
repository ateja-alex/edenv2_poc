<?php

namespace App\Eden\Managements\Rapports\Rapports;

use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_liste_management;

class Client_carte_gmap_management extends Rapports_management {

    /**
     *
     * On initialise les données du rapport
     *
     */
    function __construct() {
        $this->rapport = new Rapport_liste_management('client_carte_gmap', 'Carte Google Maps');
    }
    public function genere($ajax = false) {
        
        $adresses_par_type = array();
        $adresses_par_type['client'] = modele('adresse')->whereNotNull('latitude')->whereNotNull('longitude')->whereNotNull('client_id')->get();
        $adresses_par_type['fournisseur'] = modele('adresse')->whereNotNull('latitude')->whereNotNull('longitude')->whereNotNull('fournisseur_id')->get();
        $adresses_par_type['projet'] = modele('adresse')->whereNotNull('latitude')->whereNotNull('longitude')->whereNotNull('projet_id')->get();

        foreach($adresses_par_type as $type_element => $adresses){

            foreach($adresses as $adresse){

                $adresse->affichage_carte_google_map = management($type_element, $adresse->{$type_element . '_id'})->affiche_lien();
                $adresse->icone_google_map = management($type_element, $adresse->{$type_element . '_id'})->retourne_icone_gmap();
            }
        }

        $this->rapport->parametres_pour_vue['adresses_par_type'] = $adresses_par_type;
        return $this->rapport->genere($ajax);
    }
}