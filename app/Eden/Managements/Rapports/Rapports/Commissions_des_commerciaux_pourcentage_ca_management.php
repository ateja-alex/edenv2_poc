<?php	

namespace App\Eden\Managements\Rapports\Rapports;	


use App\Eden\Managements\Calcul_gescom_management;	
use App\Eden\Managements\Rapports\Rapports_management;	
use App\Eden\Managements\Rapports\Rapport_liste_management;	

use Illuminate\Http\Request;	

/**	
 *	
 * Gestion des rapports	
 *  	
 */	
class Commissions_des_commerciaux_pourcentage_ca_management extends Rapports_management {

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
	 * On affiche les X premiers mois d'activité	
	 * 	
	 */		
    public function genere($ajax = false) {	
        
		$dates = $this->dates_mensuelles($this->rapport);	
        $this->utilisateurs($this->rapport);	
        $utilisateurs = modele('utilisateur')->liste_utilisateurs_visibles();

        $calcul = new Calcul_gescom_management();	
		$taux_commission = 0.05;	

       $ca_par_utilisateur =  modele('facture_vente')	
            ->zero_ou_null('facture_vente.annulee_par_avoir')	
            ->whereBetween('facture_vente.date', [$dates['date_debut'], $dates['date_fin']])	
            ->select( \DB::raw('sum(montant_document_ht) as ca_total'), 'responsable_commercial_id') 	
            ->groupBy('responsable_commercial_id')	
            ->get()->pluck('ca_total', 'responsable_commercial_id');

        $titres = array(ucfirst(table_libre('utilisateur')->element), traduction('rapport.commissions_des_commerciaux_pourcentage_ca.colonnes.ca'), traduction('rapport.commissions_des_commerciaux_pourcentage_ca.colonnes.commission_calculee'));
        $this->rapport->titres($titres);	

        foreach($utilisateurs as $utilisateur) {	

            $ligne = [];	
            $ca = "N/A";	
            $commission = "N/A";	

            if(isset($ca_par_utilisateur[$utilisateur->id])) {	

                $ca = $ca_par_utilisateur[$utilisateur->id];	
                $commission = $ca * $taux_commission;	
            }	

            $ligne['utilisateur'] = $utilisateur->prenom.' '.$utilisateur->nom;	

            $ligne['ca'] = $ca;	
            $ligne['commission'] = $commission;	

            $this->rapport->ligne($ligne);	
        }	

        return $this->rapport->genere($ajax);	
    }	





} 