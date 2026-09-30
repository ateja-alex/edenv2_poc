<?php

namespace App\Eden\Managements\Rapports\Rapports;

use App\Eden\Managements\Calcul_gescom_management;

use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_liste_management;

use App\Eden\Managements\Rapports\Serie_management;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
* Gestion des rapports
*/
class Temps_saisi_par_utilisateur_management extends Rapports_management {

    /**
     *
     * On initialise les données du rapport
     *
     */
    function __construct() {
        $this->rapport = new Rapport_liste_management();
        $this->rapport->parametrage_rapport_libre['filtres_rapport'] = [
            'feuille_de_temps.utilisateur_id',
            'feuille_de_temps.date',
        ];
    }

	/**
	 *
	 * On calcule le CA par mois
	 *
	 */
    public function genere($ajax = false) {

		// on récupère les différents filtres
//		$dates = $this->dates_quotidiennes($this->rapport);
//		$utilisateurs = $this->utilisateurs($this->rapport);
		// $entites = $this->entites($this->rapport);
        $this->rapport->html = '';

        $management_element = management('feuille_de_temps');
        $this->rapport->applique_filtres($management_element);
        $this->rapport->recupere_valeurs_filtres($management_element);

		$titres = array(ucfirst(table_libre('utilisateur')->element));

		// on va chercher les utilisateurs et les temps saisis
        $feuilles_de_temps_utilisateur = modele('feuille_de_temps')->select(\DB::raw('SUM(duree) as temps_total, CONCAT(type_element,"_",element_id) as type_element_element_id,type_element,element_id,utilisateur_id,date'));

        $serie_management = new Serie_management([],$this->rapport);

        $feuilles_de_temps_utilisateur = $serie_management->applique_filtres_du_rapport($feuilles_de_temps_utilisateur, $management_element);

        $feuilles_de_temps_utilisateur = $feuilles_de_temps_utilisateur->groupBy('type_element_element_id', 'date', 'utilisateur_id')->get();

        $utilisateurs = modele('utilisateur')->whereIn('id', $feuilles_de_temps_utilisateur->pluck('utilisateur_id'))->orderBy('prenom')->get()->keyBy('id');

        $dates = $feuilles_de_temps_utilisateur->pluck('date')->toArray();
        $dates = array_unique($dates);

        $feuilles_de_temps_utilisateur = $feuilles_de_temps_utilisateur->groupBy(['utilisateur_id', 'date']);

        $titres = array_merge($titres, $dates);

        $titres[] = traduction('rapport.temps_saisi_par_utilisateur.colonnes.total');

        $this->rapport->titres($titres);

        foreach ($feuilles_de_temps_utilisateur as $utilisateur_id => $feuilles_de_temps_date) {

            if(empty($utilisateurs[$utilisateur_id]))
                continue;

            $utilisateur = $utilisateurs[$utilisateur_id];
            $total_utilisateur = 0;

            $ligne = array($utilisateur->prenom.' '.$utilisateur->nom);

            foreach ($dates as $date) {

                if(empty($feuilles_de_temps_date[$date])) {
                    $ligne[] = '<b>Total </b> : 0h';
                    continue;
                }

                $feuilles_de_temps = $feuilles_de_temps_date[$date];

                $temps_par_element = array();

                $total = 0;

                foreach($feuilles_de_temps as $feuille_de_temps) {

                    $texte = '';

                    if(!empty($feuille_de_temps->type_element) && !empty($feuille_de_temps->element_id))
                        $texte = management($feuille_de_temps->type_element, $feuille_de_temps->element_id)->affiche_lien();
                        
                    $total += $feuille_de_temps->temps_total;
                    $total_utilisateur += $feuille_de_temps->temps_total;
                    $temps_par_element[] = $texte.' : '.$feuille_de_temps->temps_total.'h';
                }

                if(!empty($total)) {

                    $temps_par_element = Arr::prepend($temps_par_element, '');
                    $temps_par_element = Arr::prepend($temps_par_element, '<b>Total </b> : '.$total.'h');
                }

                $ligne[] = implode('<br/>', $temps_par_element);

            }

            $ligne[] = $total_utilisateur.'h';

            $this->rapport->ligne($ligne);
        }

        $this->rapport->forcer_generation = true;
        return $this->rapport->genere($ajax);
    }





}
