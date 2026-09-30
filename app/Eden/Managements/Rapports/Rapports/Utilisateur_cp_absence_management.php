<?php

namespace App\Eden\Managements\Rapports\Rapports;

use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_pdf_management;
use App\Eden\Models\Element_log;
use App\Eden\Variables;


use PDF;


class Utilisateur_cp_absence_management extends Rapports_management {

    /**
     *
     * On initialise les données du rapport
     *
     */
    function __construct() {
        $this->rapport = new Rapport_pdf_management();
    }
    
	function genere($ajax = false) {

        $this->rapport->parametrage_rapport_libre['filtres_rapport'][] = 'date_de_debut';
        $this->rapport->parametrage_rapport_libre['filtres_rapport'][] = 'employe_id';

        $management_element = management('employe_demande_conge');
        $this->rapport->applique_filtres($management_element);
        $this->rapport->recupere_valeurs_filtres($management_element);

        $this->rapport->parametrage_rapport_libre = [];
        $date_debut = null;
        $date_fin = null;
        $utilisateurs = [];

        foreach ($this->rapport->valeurs_filtre as $valeurs) {
            $id_filtre = $valeurs['id'];
            $valeurs = $valeurs['valeurs'];
            $filtre = $this->rapport->options[$id_filtre];
            if ($filtre['nom_sql'] == 'date_de_debut') {
                $date_debut = $valeurs['debut'];
                $date_fin = $valeurs['fin'];
                if(!empty($valeurs['variable'])) {
                    list($debut, $fin) = $management_element->champ('date_de_debut')->transforme_variable_date($valeurs['variable']);

                    if (!empty($debut))
                        $date_debut = date('Y-m-d', strtotime($debut));
                    if (!empty($fin))
                        $date_fin = date('Y-m-d', strtotime($fin));
                }
            }

            if ($filtre['nom_sql'] == 'employe_id') {

                $utilisateurs = $valeurs;

                foreach ($valeurs as $cle => $valeur) {
                    if($valeur == '#utilisateur_connecte#')
                        $utilisateurs[$cle] = moi()->id;
                }
            }
        }

        $dates = [
            'date_debut' => $date_debut ?? date('Y-01-01'),
            'date_fin' => $date_fin ?? date('Y-12-31 23:59:59'),
        ];

		$donnees_pour_pdf = array();
		$donnees_pour_pdf['tableau_cp_absences'] = $this->tableau_cp_absences($dates, $utilisateurs);
		$donnees_pour_pdf['dates'] = $dates;

        $this->rapport->rapport_libre->type_rapport = 'pdf';

		$this->rapport->genere_pdf($donnees_pour_pdf);

		// enregistrement...
		return $this->rapport->genere($ajax);
		
	}
	
	
	/**
	 * 
	 * Mise en forme de l'array + du HTML pour l'affichage
	 * 
	 */
	public function tableau_cp_absences($dates, $utilisateurs) {
		
		if(empty($utilisateurs))
			$utilisateurs = modele('utilisateur')->liste_utilisateurs_visibles()->toArray();

		$utilisateurs_formate = array();
		$valeurs_raison = champ_libre('employe_demande_conge','raison')->champ->valeurs_possibles;

		foreach($utilisateurs as $utilisateur) {

			if (isset($utilisateur['id']))
				$utilisateur_id = $utilisateur['id'];
			else
				$utilisateur_id = $utilisateur;

			$utilisateur_bdd = modele('utilisateur',$utilisateur_id);

			$html = "<b>".$utilisateur_bdd->prenom." ".$utilisateur_bdd->nom."</b>";

			$absences_et_cp_par_utilisateur = $this->recuperer_absences_globales($utilisateur_bdd->id, $valeurs_raison, $dates);

			if(!$absences_et_cp_par_utilisateur)
				continue;
			
			$html .= $absences_et_cp_par_utilisateur;

			$utilisateurs_formate[] = $html;
		}
		
		return $utilisateurs_formate;
	}

	/**
	 * 
	 * On récupère les absences de l'utilisateurs
	 * 
	 */
	public function recuperer_absences_globales($utilisateur_id,$valeurs_raison,$dates) {	

		// On prend les congé du mois en prenant en compte les demandes qui déborde sur le mois n-1 et n+1
		// sql : select * from employe_demande_conge where employer_id = ... 
		// and (
		// (date_de_debut between '..' and '..' or date_de_fin between '..' and '..') 
		// or 
		// (date_de_debut < '..' and date_de_fin > '...')
		// )
		$demandes_conges = modele('employe_demande_conge')
								->where('employe_id',$utilisateur_id)
								->where(function($r) use($dates) {
									
									$r->where(function($r2) use($dates) {
										
										$r2->whereBetween('employe_demande_conge.date_de_debut', [$dates['date_debut'], $dates['date_fin']])
										->orWhereBetween('employe_demande_conge.date_de_fin', [$dates['date_debut'], $dates['date_fin']]);
									})->orWhere(function($r2) use($dates) {
										
										$r2->where('employe_demande_conge.date_de_debut', '<', $dates['date_debut'])
										->where('employe_demande_conge.date_de_fin', '>', $dates['date_fin']);
									});
								})
								->where(function($q){
									$q->whereNull('statut')
									  ->orWhere('statut','!=',2);
								})
								->orderBy('raison')
								->get();

		if ($demandes_conges->isEmpty())
			return false;

		$demandes_conges = $demandes_conges->sortBy('date_de_debut');

		$html = "";
		$raison_precedente = 9999;

		$tableau_periodicite_journee = array();
		$tableau_periodicite_journee[0] = "matin";
		$tableau_periodicite_journee[1] = "après-midi";

		foreach ($demandes_conges as $demande) {
			
			if ($raison_precedente != $demande['raison']) {
				
				$raison_precedente = $demande['raison'];
				$html .= "<br>".$valeurs_raison[$demande['raison']]." : ";
			}

			// On met en forme les dates si date à cheval sur mois n-1 ou +1
			if (strtotime($demande['date_de_debut']) < strtotime($dates['date_debut'])) {
				$demande['date_de_debut'] = $dates['date_debut'];
				$demande['periode_de_debut'] = 0;
			}

			if (strtotime($demande['date_de_fin']) > strtotime($dates['date_fin'])) {
				$demande['date_de_fin'] = $dates['date_fin'];
				$demande['periode_de_fin'] = 1;
			}

			$html .= "
                <br>
                <span
                    style='margin-left: 20px;'
                >"
                .date('d/m/y',strtotime($demande['date_de_debut']))
                ." ".$tableau_periodicite_journee[$demande['periode_de_debut']]
                ." au ".date('d/m/y',strtotime($demande['date_de_fin']))
                ." ".$tableau_periodicite_journee[$demande['periode_de_fin']]
                ." </span>";
            
            if(!empty($demande['commentaire']))
                $html .= " : <span>".$demande['commentaire'] . "</span>";
            
		}


		return $html;
	}
}

?>
