<?php

namespace App\Eden\Managements\Rapports\Rapports;


use App\Eden\Managements\Calcul_gescom_management;
use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_liste_management;

use Illuminate\Http\Request;

/**
* Gestion des rapports
*/
class Ca_par_responsable_commercial_management extends Rapports_management {

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
		
		// on récupère les différents filtres
		$dates = $this->dates_mensuelles($this->rapport);
		$entites = $this->entites($this->rapport);
		
        $this->rapport->titre = traduction('rapport.ca_par_responsable_commercial.titre_affichage',null,[formate_date('d/m/Y', $dates['date_debut']),formate_date('d/m/Y', $dates['date_fin'])]);

		// on fait le calcul
		// on va chercher le CA mensuel
		$calcul = new Calcul_gescom_management();
		
		$ca_par_commerciaux = $calcul->documents_valides_uniquement(false)
						->groupe_par('responsable_commercial_id')
						->filtre_entites($entites)
						->filtre_dates_mensuelles($dates)
						->plus('facture_vente')
						->moins('avoir_vente')
						->resultat();

						
		$this->rapport->titres(array(champ_libre('facture_vente','responsable_commercial_id')->modele->nom, array(traduction('rapport.ca_par_responsable_commercial.ca_ht_periode'), 'css_montant')));


        foreach($ca_par_commerciaux as $id_commercial => $ca) {

            $commercial = modele('utilisateur', $id_commercial);
            $nom_du_commercial = 'N/A';
            if($commercial->exists) 
                $nom_du_commercial = $commercial->nom.' '.$commercial->prenom;
            

            $ligne = [];
            $ligne[] = $nom_du_commercial;
            $ligne[] = [montant($ca).' ' . maquette('devise_application_symbole'), 'css_montant'];

            $this->rapport->ligne($ligne);
        }
		
	
		
		return $this->rapport->genere($ajax);
    }
	
}