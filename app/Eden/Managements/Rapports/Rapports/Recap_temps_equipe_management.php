<?php

namespace App\Eden\Managements\Rapports\Rapports;

use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_liste_management;


/**
* Gestion des rapports
*/
class Recap_temps_equipe_management extends Rapports_management {

    /**
     *
     * On initialise les données du rapport
     *
     */
    function __construct() {
        $this->rapport = new Rapport_liste_management();
    }
    
	/**
	 * 
	 * On calcule le CA par mois
	 * 
	 */	
    public function genere($ajax = false) {
		
        $date = $this->dates_quotidiennes($this->rapport);
		$utilisateurs = $this->utilisateurs($this->rapport);
		
		// on boucle sur chaque utilisateur
		
		if(empty($utilisateurs))
			$utilisateurs = modele('utilisateur')->liste_utilisateurs_visibles()->pluck('id')->toArray();
			
		$this->rapport->titres(array('Projet', 'Tache', 'Durée', 'Montant'));

        foreach($utilisateurs as $utilisateur) {

            $total_utilisateur = 0;

            $utilisateur_modele = modele('utilisateur', $utilisateur);

            $this->rapport->sous_titre($utilisateur_modele->prenom.' '.$utilisateur_modele->nom);

            $feuilles_de_temps = modele('feuille_de_temps')
                ->whereBetween('date', [$date['date_debut'],$date['date_fin']])
                ->where('utilisateur_id', $utilisateur_modele->id)->get();

            foreach($feuilles_de_temps as $feuille_de_temps) {

                $this->rapport->ligne(array(
                    management($feuille_de_temps->type_element, $feuille_de_temps->element_id)->affiche_lien(),
                    $feuille_de_temps->tache,
                    $feuille_de_temps->duree.'h',
                    montant($feuille_de_temps->duree * 400 /7). maquette('devise_application_symbole'),
                ));

                $total_utilisateur += $feuille_de_temps->duree;
            }

            $this->rapport->ligne(array(
                'total '.$utilisateur_modele->prenom.' '.$utilisateur_modele->nom,
                '',
                $total_utilisateur.'h',
                montant($total_utilisateur * 400 /7). maquette('devise_application_symbole'),
            ));
        }
		
		
		return $this->rapport->genere($ajax);
    }
	
	
	
	
	
}