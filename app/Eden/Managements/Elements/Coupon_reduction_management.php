<?php

namespace App\Eden\Managements\Elements;

use PDF;
use Illuminate\Support\Str;

class Coupon_reduction_management extends Element_management {
	
	/**
	 * @cf description sur Element_management
	 * 
	 * On traite le cas particulier des codes réservés à certaines familles d'articles seulement
	 */
	public function enregistre($modifications = array(), $modele = false) {
		
		if(isset($modifications['liste_familles_coupon'])) {
			
			$modifications['liste_familles_coupon'] = json_encode($modifications['liste_familles_coupon']);
		}
		else {
			
			$modifications['liste_familles_coupon'] = json_encode(array());
		}

		return parent::enregistre($modifications, $modele);
	}

	/**
	 * 
	 * On ajoute un code généré automatiquement si c'est un chèque cadeau
	 * 
	 */
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {
		
		if(empty($this->modele->code) && $this->modele->type_coupon == 2) {
			
			while(true) {
				
				$code = Str::random(8);
				
				if(modele('coupon_reduction')->sans_profils()->avec_inactifs()->where('code', $code)->count() == 0)
					break;
			}
			
			$this->enregistre_modele(array('code' => $code, 'type_de_reduction' => 1));
		}
		
		// @note frédéric : je commente ça, je ne sais pas pourquoi c'était forcé mais ça pose pb
		// $this->enregistre_modele(array('type_de_reduction' => 1));
		
		// on génère le PDF

	//	if($this->modele->type_coupon == 2 && !empty($this->modele->client_id)) {
		if(!empty($this->modele->type_coupon)) {

			$destinataire = "";

			if(!empty($this->modele->client_id)) {

				$client = management('client', $this->modele->client_id);
				$destinataire = $client->champ('civilite')->affiche().' '.$client->modele->prenom.' . '.$client->modele->nom;
			}
			
			
			$centimes = substr($this->modele->valeur - floor($this->modele->valeur), 2, 2);
			$centimes = (string) $centimes;
			$centimes = substr($centimes.'00', 0, 2);
			
			
			$donnees_pour_pdf = array(
			
				'destinataire' => $destinataire,
				'montant' => floor($this->modele->valeur).",<span id='bonMontantSup'>$centimes". maquette('devise_application_symbole') ."</span>",
				'code' => $this->modele->code,
				'expiration' => formate_date('d/m/Y', $this->modele->fin_validite),
			);
			
			
			$nom_du_pdf = 'coupon_reduction_'.$this->modele->id.'.pdf';
			
			$pdf = PDF::loadView('eden::pdf.coupon_reduction', $donnees_pour_pdf);
			$pdf->setPaper([0, 0, 600,290], 'portrait');
			
			\Storage::put($nom_du_pdf, $pdf->output());
		}
		
		parent::methodes_post_modification($modele, $modele_avant, $modifications);
	}

    public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();

        $liste_options[] = 'pdf';

        return $liste_options;
    }

	/**
	 *
	 * On ajoute deux champs obligatoires si le type_coupon est à 3
	 *
	 */
	protected function verifie_champs_obligatoires_avec_champs($modele, $modifications, $champs_libres) {

		if ( (isset($modifications['type_coupon']) && $modifications['type_coupon'] == '3') || (isset($modele['type_coupon']) && $modele['type_coupon'] == '3') ) {

			$code = champ_libre($this->_type_element, 'code');
			$type_de_reduction = champ_libre($this->_type_element, 'type_de_reduction');

			$champs_libres->push($code->modele);
			$champs_libres->push($type_de_reduction->modele);

		}

		return parent::verifie_champs_obligatoires_avec_champs($modele, $modifications, $champs_libres);

	}

	/**
	 * 
	 * Retourne un modèle avec des données par défaut
	 * 
	 */
	public function modele_par_defaut() {
		
		$modele = parent::modele_par_defaut();
		
		$modele->liste_familles_coupon = [];
		
		return $modele;
	}
}