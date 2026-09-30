<?php

namespace App\Eden\Managements\Rapports\Rapports;


use App\Eden\Managements\Calcul_gescom_management;
use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_liste_management;

use Illuminate\Http\Request;

class Balance_agee_management extends Rapports_management {
    
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
	 * On calcule la balance agée
	 * 
	 */	
    public function genere($ajax = false) {

        $titres = array('clients', 'non_echu', 'inferieur_30_jours', 'entre_30_45', 'entre_46_60', '61_et_plus', 'totaux');

        $filtre_fournisseur = $this->fournisseur($this->rapport);
        $filtre_client = $this->client($this->rapport);
 		$this->export_excel($this->rapport);

        foreach($titres as &$titre){
            if($titre != 'clients')
                $titre = array(traduction('rapport.balance_agee.colonnes.'.$titre),'css_montant');
            else
                $titre = traduction('rapport.balance_agee.colonnes.'.$titre);
        }

		$this->rapport->titres($titres);

		$totaux = ['fv' => ['non echu' => 0, 'inf 30' => 0, '30-45' => 0, '46-60' => 0, '61+' => 0, 'total' => 0], 'fa' => ['non echu' => 0, 'inf 30' => 0, '30-45' => 0, '46-60' => 0, '61+' => 0, 'total' => 0]];
		 
		// Clients
			// On insère les données

				$documents = [];
				$types_documents = ['facture_vente' => 1, 'avoir_vente' => -1, 'acompte_vente' => 1];

				foreach($types_documents as $type_document => $osef) {

					$documents[$type_document] = modele($type_document)
													->zero_ou_null('regle')
													->where('valide', 1)
													->select('id', 'client_id', 'date_de_reglement', 'solde_document_ttc')
													->whereNotNull('client_id')
													->get()
													->groupBy('client_id');
				}

				$clients = modele('client');

				if(!empty($filtre_client)) {
					$clients = $clients->where('id', $filtre_client);
				}

				$clients = $clients->get();

				$lignes_temp = [];

				foreach($clients as $client) {

					$client = management('client', $client->id, $client);

					$ligne = array($client->affiche_lien());
				
					$montants = ['non echu' => 0, 'inf 30' => 0, '30-45' => 0, '46-60' => 0, '61+' => 0, 'total' => 0];

					foreach($types_documents as $type_document => $sens) {

						if(!isset($documents[$type_document][$client->modele->id]))
							continue;

						foreach($documents[$type_document][$client->modele->id] as $document) {

							// non échus
							if($document->date_de_reglement >= date('Y-m-d'))
								$montants['non echu'] += $document->solde_document_ttc * $sens;

							// < 30 jours
							elseif($document->date_de_reglement >= date('Y-m-d', strtotime('now -29 days')))
								$montants['inf 30'] += $document->solde_document_ttc * $sens;

							// entre 30 et 45 jours
							elseif($document->date_de_reglement >= date('Y-m-d', strtotime('now -45 days')))
								$montants['30-45'] += $document->solde_document_ttc * $sens;

							// entre 46 et 60 jours
							elseif($document->date_de_reglement >= date('Y-m-d', strtotime('now -60 days')))
								$montants['46-60'] += $document->solde_document_ttc * $sens;

							// entre 46 et 60 jours
							else
								$montants['61+'] += $document->solde_document_ttc * $sens;

							$montants['total'] += $document->solde_document_ttc * $sens;
						}
					}

					if($montants['total'] == 0)
						continue;

					$totaux['fv']['non echu'] 	+= $montants['non echu'];
					$totaux['fv']['inf 30']		+= $montants['inf 30'];
					$totaux['fv']['30-45'] 		+= $montants['30-45'];
					$totaux['fv']['46-60'] 		+= $montants['46-60'];
					$totaux['fv']['61+'] 		+= $montants['61+'];
					$totaux['fv']['total'] 		+= $montants['total'];

					
					$ligne[] = array(montant($montants['non echu']), 'css_montant');
					$ligne[] = array(montant($montants['inf 30']), 'css_montant');
					$ligne[] = array(montant($montants['30-45']), 'css_montant');
					$ligne[] = array(montant($montants['46-60']), 'css_montant');
					$ligne[] = array(montant($montants['61+']), 'css_montant');
					$ligne[] = array(montant($montants['total']), 'css_montant');
					
					$lignes_temp[] = $ligne;
				}

				usort($lignes_temp, [$this, "usort"]);

				foreach($lignes_temp as $ligne) {

					$this->rapport->ligne($ligne);
				}

			// On crée la ligne de total
				$ligne = array(traduction('rapport.balance_agee.total_du'));
				$ligne[] = array(montant($totaux['fv']['non echu']), 'css_montant');
				$ligne[] = array(montant($totaux['fv']['inf 30']), 'css_montant');
				$ligne[] = array(montant($totaux['fv']['30-45']), 'css_montant');
				$ligne[] = array(montant($totaux['fv']['46-60']), 'css_montant');
				$ligne[] = array(montant($totaux['fv']['61+']), 'css_montant');
				$ligne[] = array(montant($totaux['fv']['total']), 'css_montant');
				$this->rapport->titres($ligne);

		// Séparateur		
		$this->rapport->ligne(array(' '));

		// Fournisseurs
			// On crée les titres
                $titres = array('fournisseurs', 'non_echu', 'inferieur_30_jours', 'entre_30_45', 'entre_46_60', '61_et_plus', 'totaux');

                foreach($titres as &$titre){
                    if($titre != 'fournisseurs')
                        $titre = array(traduction('rapport.balance_agee.colonnes.'.$titre),'css_montant');
                    else
                        $titre = traduction('rapport.balance_agee.colonnes.'.$titre);
                }

                $this->rapport->titres($titres);

			// On insère les données

				$documents = [];
				$types_documents = ['facture_achat' => 1, 'avoir_achat' => -1, 'acompte_achat' => 1];
				foreach($types_documents as $type_document => $osef) {
					$documents[$type_document] = modele($type_document)
								->zero_ou_null('regle')
								->where('valide', 1)
								->select('fournisseur_id', 'date_de_reglement', 'solde_document_ttc')
								->whereNotNull('fournisseur_id')
								->get()
								->groupBy('fournisseur_id');
				}

				$fournisseurs = modele('fournisseur');

				if(!empty($filtre_fournisseur))
					$fournisseurs = $fournisseurs->where('id', $filtre_fournisseur);

				$fournisseurs = $fournisseurs->get();

				$lignes_temp = [];
				foreach($fournisseurs as $fournisseur) {

					$fournisseur = management('fournisseur', $fournisseur->id, $fournisseur);

					$ligne = array($fournisseur->affiche_lien());
				
					foreach($types_documents as $type_document => $sens) {

						if(!isset($documents[$type_document][$fournisseur->modele->id]))
							continue;

						$montants = ['non echu' => 0, 'inf 30' => 0, '30-45' => 0, '46-60' => 0, '61+' => 0, 'total' => 0];

						foreach($documents[$type_document][$fournisseur->modele->id] as $document) {

							// non échus
							if($document->date_de_reglement >= date('Y-m-d'))
								$montants['non echu'] += $document->solde_document_ttc * $sens;

							// < 30 jours
							elseif($document->date_de_reglement >= date('Y-m-d', strtotime('now -29 days')))
								$montants['inf 30'] += $document->solde_document_ttc * $sens;

							// entre 30 et 45 jours
							elseif($document->date_de_reglement >= date('Y-m-d', strtotime('now -45 days')))
								$montants['30-45'] += $document->solde_document_ttc * $sens;

							// entre 46 et 60 jours
							elseif($document->date_de_reglement >= date('Y-m-d', strtotime('now -60 days')))
								$montants['46-60'] += $document->solde_document_ttc * $sens;

							// entre 46 et 60 jours
							else
								$montants['61+'] += $document->solde_document_ttc * $sens;

							$montants['total'] += $document->solde_document_ttc * $sens;
						}
					}

					if($montants['total'] == 0)
						continue;


					$totaux['fa']['non echu'] 	+= $montants['non echu'];
					$totaux['fa']['inf 30']		+= $montants['inf 30'];
					$totaux['fa']['30-45'] 		+= $montants['30-45'];
					$totaux['fa']['46-60'] 		+= $montants['46-60'];
					$totaux['fa']['61+'] 		+= $montants['61+'];
					$totaux['fa']['total'] 		+= $montants['total'];
					
					$ligne[] = array(montant($montants['non echu']), 'css_montant');
					$ligne[] = array(montant($montants['inf 30']), 'css_montant');
					$ligne[] = array(montant($montants['30-45']), 'css_montant');
					$ligne[] = array(montant($montants['46-60']), 'css_montant');
					$ligne[] = array(montant($montants['61+']), 'css_montant');
					$ligne[] = array(montant($montants['total']), 'css_montant');
					
					$lignes_temp[] = $ligne;
				}

				usort($lignes_temp, [$this, "usort"]);

				foreach($lignes_temp as $ligne) {

					$this->rapport->ligne($ligne);
				}
			
			// On crée la ligne de total
				$ligne = array(traduction('rapport.balance_agee.total_a_payer'));
				$ligne[] = array(montant($totaux['fa']['non echu']), 'css_montant');
				$ligne[] = array(montant($totaux['fa']['inf 30']), 'css_montant');
				$ligne[] = array(montant($totaux['fa']['30-45']), 'css_montant');
				$ligne[] = array(montant($totaux['fa']['46-60']), 'css_montant');
				$ligne[] = array(montant($totaux['fa']['61+']), 'css_montant');
				$ligne[] = array(montant($totaux['fa']['total']), 'css_montant');
				$this->rapport->titres($ligne);

		// Séparateur		
		$this->rapport->ligne(array(' '));
		
		// On crée la ligne de total final
		$ligne = array(traduction('rapport.balance_agee.total'));
	
		$ligne[] = array(montant($totaux['fv']['non echu']	- $totaux['fa']['non echu']), 'css_montant');
		$ligne[] = array(montant($totaux['fv']['inf 30']	- $totaux['fa']['inf 30']), 'css_montant');
		$ligne[] = array(montant($totaux['fv']['30-45']		- $totaux['fa']['30-45']), 'css_montant');
		$ligne[] = array(montant($totaux['fv']['46-60']		- $totaux['fa']['46-60']), 'css_montant');
		$ligne[] = array(montant($totaux['fv']['61+']		- $totaux['fa']['61+']), 'css_montant');
		$ligne[] = array(montant($totaux['fv']['total']		- $totaux['fa']['total']), 'css_montant');
		
		$this->rapport->titres($ligne);

        $this->rapport->rapport_libre->type_rapport = 'tableau';
		return $this->rapport->genere($ajax);
    }

    private function usort($a, $b) {

    	if($a[6][0] == $b[6][0])
    		return 0;

    	return ($a[6][0] < $b[6][0] ? 1 : -1);
    }
}