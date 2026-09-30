<?php

namespace App\Eden\Managements\Rapports\Rapports;


use App\Eden\Managements\Calcul_gescom_management;
use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_html_management;

use App\Eden\Models\Liste_libre;
use Illuminate\Http\Request;

/**
* Gestion des rapports
*/
class Eco_contribution_management extends Rapports_management {

    /**
     *
     * On initialise les données du rapport
     *
     */
    function __construct() {
        $this->rapport = new Rapport_html_management();
    }

	/**
	 * 
	 * On calcule le tableau de TVA par mois
	 * 
	 */	
    public function genere($ajax = false) {

        $rapport_facture_vente_lignes = Liste_libre::where('type_element','facture_vente_lignes_eco_contribution')
            ->first();

        $rapport_eco_contribution = Liste_libre::where('type_element','eco_contribution_mensuel')
            ->first();

        if(empty($rapport_facture_vente_lignes) || empty($rapport_eco_contribution))
            abort(404);

        $this->rapport->parametres_pour_vue['rapports'] = array(
            'rapport_facture_vente_lignes' => $rapport_facture_vente_lignes,
            'rapport_eco_contribution' => $rapport_eco_contribution
        );

        $this->rapport->parametres_pour_vue['listes_id'] = array(
            $rapport_facture_vente_lignes->id,
            $rapport_eco_contribution->id
        );

        $this->rapport->parametres_pour_vue['titre_du_rapport'] = traduction('rapport.eco_contribution.titre_affichage');

        $this->rapport->js = file_get_contents(storage_path("app/public/composants/liste_libre_{$rapport_facture_vente_lignes->id}.js"));
        $this->rapport->js .= " " . file_get_contents(storage_path("app/public/composants/liste_libre_{$rapport_eco_contribution->id}.js"));

		return $this->rapport->genere($ajax);
    }
	
	
}