<?php

namespace App\Eden\Managements\Rapports\Rapports;


use App\Eden\Managements\Calcul_gescom_management;
use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_histogramme_management;
use App\Eden\Managements\Rapports\Serie_management;

use Illuminate\Http\Request;

/**
* Gestion des rapports
*/
class Ca_mensuel_management extends Rapports_management {

    /**
     *
     * On initialise les données du rapport
     *
     */
    function __construct() {
        $this->rapport = new Rapport_histogramme_management();
    }
    
	/**
	 * 
	 * On calcule le CA par mois
	 * 
	 */	
    public function genere($ajax = false) {
		
		$this->rapport->serie(traduction('rapport.ca_mensuel.ca_ht_n1'));
		$this->rapport->serie(traduction('rapport.ca_mensuel.ca_ht_n'));

        // on récupère les différents filtres
        $this->rapport->parametrage_rapport_libre['filtres_rapport'][] = 'date';

        $management_element = management('facture_vente');

        $this->rapport->applique_filtres($management_element);
        $this->rapport->recupere_valeurs_filtres($management_element);

        // on fait le calcul
        // on va chercher le CA mensuel

        $ca_mensuel = $this->recupere_ca($management_element);
        $ca_mensuel_n_moins_1 = $this->recupere_ca($management_element, true);

        $this->rapport->parametrage_rapport_libre = [];

        if (empty($filtres)) {
            $date_debut = date('Y-01-01');
            $date_fin = date('Y-12-31 23:59:59');
        }
        else {
            foreach ($this->valeurs_filtre as $valeurs) {
                $id_filtre = $valeurs['id'];
                $valeurs = $valeurs['valeurs'];
                $filtre = $this->options[$id_filtre];
                if ($filtre['nom_sql'] != 'date')
                    continue;

                $date_debut = $valeurs['debut'];
                $date_fin = $valeurs['fin'];
                if(!empty($valeurs['variable'])) {
                    list($debut, $fin) = $management_element->champ('date')->transforme_variable_date($valeurs['variable']);

                    if (!empty($debut))
                        $date_debut = date('Y-m-d', strtotime($debut));
                    if (!empty($fin))
                        $date_fin = date('Y-m-d', strtotime($fin));
                }

            }
        }

        $serie_management = new Serie_management([],$this->rapport);

        $periodes = $serie_management->periodes('mensuelle', $date_debut, $date_fin);

        foreach($periodes as $periode) {

            $this->rapport->legende($periode['nom']);

            if(isset($ca_mensuel[$periode['periode']]))
                $this->rapport->valeur(traduction('rapport.ca_mensuel.ca_ht_n'), $ca_mensuel[$periode['periode']]);
            else
                $this->rapport->valeur(traduction('rapport.ca_mensuel.ca_ht_n'), 0);

            $date_n_moins_1 = date('Y-m', strtotime($periode['periode']." -1 year"));

            if(isset($ca_mensuel_n_moins_1[$date_n_moins_1]))
                $this->rapport->valeur(traduction('rapport.ca_mensuel.ca_ht_n1'), $ca_mensuel_n_moins_1[$date_n_moins_1]);
            else
                $this->rapport->valeur(traduction('rapport.ca_mensuel.ca_ht_n1'), 0);
        }
		
		return $this->rapport->genere($ajax);
    }

    public function recupere_ca($management_element, $n_mois_1 = false) {
        $ca_mensuel = modele('facture_vente')
            ->select(
                \DB::raw("
                    facture_vente.*,
                    CONCAT(YEAR(date),'-',RIGHT(CONCAT('0',MONTH(date)), 2)) as date_mensuelle
                ")
            )
            ->where('valide', 1);

        $serie_management = new Serie_management([],$this->rapport);

        $ca_mensuel = $serie_management->applique_filtres_du_rapport($ca_mensuel, $management_element, $n_mois_1);

        $ca_mensuel = $ca_mensuel->get()->groupBy('date_mensuelle');

        $tableau_ca = [];

        foreach($ca_mensuel as $cle => $valeurs) {
            $tableau_ca[$cle] = $valeurs->sum('montant_document_ttc');
        }

        return $tableau_ca;
    }

}