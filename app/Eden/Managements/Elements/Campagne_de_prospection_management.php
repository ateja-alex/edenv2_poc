<?php

namespace App\Eden\Managements\Elements;

use Illuminate\Http\Request;


class Campagne_de_prospection_management extends Element_management {
    
	/**
	 * 
	 * Affiche l'avancement dans les listes
	 * 
	 */
	public function affiche_avancement($modele) {
		
		$total = modele('campagne_de_prospection_client')->where('campagne_de_prospection_id', $modele->id)->count();
		
		if(!empty($total)) {
			
			$traites = modele('campagne_de_prospection_client')->where('campagne_de_prospection_id', $modele->id)->where('statut', 2)->count();
			
			$avancement = $traites / $total * 100;
			
			return '<div class="progress" style="height: 1.2rem;">
						<div class="progress-bar bg-success css_background_couleur_primaire" role="progressbar" style="width: '.$avancement.'%; font-size: 1rem;">'.round($avancement).'%</div>
					</div>';
		}
		
		return '';
		
	}

    public function ajouter_un_des_clients(Request $formulaire) {

        // on va chercher tous les liens actuels
        $campagne = modele('campagne_de_prospection', $formulaire->campagne_de_prospection_id);

        $id_clients_deja_dans_la_campagne = $campagne->clients()->get()->keyBy('id');

        $ids_a_traiter = $formulaire->ids;

        $ids_a_traiter = array_unique($ids_a_traiter);
        
        foreach($ids_a_traiter as $id) {

            // le client est déjà dans la campagne
            if(isset($id_clients_deja_dans_la_campagne[$id]))
                continue;

            // on l'ajoute
            $management = management('campagne_de_prospection_client');

            $donnees = array(
                'campagne_de_prospection_id' => $formulaire->campagne_de_prospection_id,
                'client_id' => $id,
                'statut' => 0,
            );

            $donnees['utilisateur_id'] = moi()->id;

            $management->enregistre($donnees);
        }

        return true;
    }

    /**
     *
     * Calcule le détail des heures réalisées pour la campagne
     *
     */
    public function heures_details(){

        $donnees = ['temps_realise' => 0, 'temps_par_utilisateur' => array()];

        $feuilles_de_temps = modele('feuille_de_temps')
            ->where('type_element','campagne_de_prospection')
            ->where('element_id', $this->modele->id)->get();

        if($feuilles_de_temps->count() > 0) {

            $donnees['temps_realise'] = $feuilles_de_temps->sum('duree');

            $utilisateurs = $feuilles_de_temps->pluck('utilisateur_id')->unique();

            foreach($utilisateurs as $utilisateur_id) {

                $donnees['temps_par_utilisateur'][$utilisateur_id] = $feuilles_de_temps->where('utilisateur_id', $utilisateur_id)->sum('duree');
            }
        }

        return $donnees;
    }

    /**
     *
     * Récapitulatif d'avancement de la campagne de prospection
     *
     */
	public function recapitulatif(){

        $recapitulatif = array(
            "tableau_header" => array(
                '',
                traduction('composant.recapitulatif_campagne_de_prospection.non_statue'),
                traduction('composant.recapitulatif_campagne_de_prospection.affecte'),
                traduction('composant.recapitulatif_campagne_de_prospection.traite'),
                traduction('composant.recapitulatif_campagne_de_prospection.total'),
            )
        );

        $valeurs = array(
            'TOTAL' => array(
                0 => 0,
                1 => 0,
                2 => 0,
                3 => 0
            )
        );

        $campagne_de_prospection_clients = modele('campagne_de_prospection_client')->where('campagne_de_prospection_id', $this->modele->id)
            ->get()->groupBy(['utilisateur_id','statut']);

        $utilisateurs = modele('utilisateur')->get()->keyBy('id');

        foreach($campagne_de_prospection_clients as $utilisateur_id => $campagne_de_prospection_client){

            if(!isset($utilisateurs[$utilisateur_id]))
                $nom_affiche = 'Non affecté';
            else
                $nom_affiche = $utilisateurs[$utilisateur_id]->nom." ".$utilisateurs[$utilisateur_id]->prenom;

            $valeurs[$nom_affiche] = array(
                0 => 0,
                1 => 0,
                2 => 0
            );

            $total_utilisateur = 0;

            foreach($campagne_de_prospection_client as $statut => $affectes){

                if(empty($statut))
                    $statut = 0;

                $total = $affectes->count();

                $valeurs[$nom_affiche][$statut] = $total;

                $valeurs['TOTAL'][$statut] += $total;

                $total_utilisateur += $total;
            }

            $valeurs[$nom_affiche][3] = $total_utilisateur;

            $valeurs['TOTAL'][3] += $total_utilisateur;
        }

        $valeurs['TOTAL'] = array_shift($valeurs);

        $recapitulatif['valeurs'] = $valeurs;

        if($valeurs['TOTAL'][3] > 0)
            $recapitulatif['score_avancement'] = intval(( $valeurs['TOTAL'][2] * 100 ) / $valeurs['TOTAL'][3]);
        else
            $recapitulatif['score_avancement'] = 0;

        return $recapitulatif;
    }
}