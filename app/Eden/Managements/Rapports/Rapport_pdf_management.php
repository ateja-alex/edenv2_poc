<?php

namespace App\Eden\Managements\Rapports;

use App\Eden\Models\Champ_libre;
use PDF;
use Mail;

/**
* Gestion des rapports
*/
class Rapport_pdf_management extends Rapport_base_management {
	
	/**
	 * 
	 * On initialise les données du rapport
	 * 
	 */
	function __construct($id_rapport = '', $titre = '', $sous_titre = '') {
		
		parent::__construct($id_rapport, $titre, $sous_titre);
		$this->lignes = array();
	}
	
	/**
	 * 
	 * Génère le PDF
	 * 
	 */
	public function genere_pdf($donnees_pour_pdf) {
		
		if(empty($this->parametrage_rapport_libre))
			$pdf = PDF::loadView('eden::rapports.pdf.'.$this->id_rapport, $donnees_pour_pdf);
		else
			$pdf = PDF::loadView('eden::rapports.pdf.rapport_pdf_standard', $donnees_pour_pdf);
		
		\Storage::put('public/'.$this->id_rapport.'_'.id_utilisateur.'.pdf', $pdf->output());
	}
	
	/**
	 * 
	 * Récupère les données pour la vue
	 * 
	 */
	public function parametres_pour_vue($ajax = false) {
		
		parent::parametres_pour_vue($ajax);

        $this->parametres_pour_vue['props_composant']['url_pdf'] = 'storage/'.$this->id_rapport.'_'.id_utilisateur.'.pdf';
		
		$this->parametres_pour_vue['url_pdf'] = 'storage/'.$this->id_rapport.'_'.id_utilisateur.'.pdf';
	}

	/**
	 * 
	 * Affichage du planning des salles de réunion par semaine
	 * 
	 */
    public function genere($ajax = false) {
		
		if(empty($this->parametrage_rapport_libre))
			return parent::genere($ajax);

        $type_element = $this->rapport_libre->type_element;
		$management_element = management($type_element);
		// les filtres génériques du rapport
        $this->applique_filtres($management_element);
        $this->recupere_valeurs_filtres($management_element);

    	$donnees_pour_pdf = array('rapport_libre' => $this->rapport_libre);
		
		// on va chercher les données
		$requete = modele($type_element);

        if(empty(moi()) && !empty(moi_extranet()))
			$requete = $requete->avec_filtre_extranet();

        $serie_management = new Serie_management([],$this);
		
		$requete = $serie_management->applique_filtres_du_rapport($requete, $management_element);

        $recherche_avancee = modele('recherche_avancee')
            ->where('type','rapport')
            ->where('id_cible',$this->rapport_libre->id_rapport)
            ->first();

        if(!empty($recherche_avancee)) {
            $filtre = management('recherche_avancee', $recherche_avancee->id, $recherche_avancee)->structure();
            management('recherche_avancee')->applique_filtrage($filtre,$requete,$type_element);
        }
		
		// on applique les filtres
		$champs_libres = Champ_libre::where('type_element', $type_element)->get();
		
		foreach($champs_libres as $champ_libre) {
			
			if(empty($this->parametrage_rapport_libre['filtre_applique_'.$champ_libre->nom_sql]))
				continue;
			
			// on applique le filtre
			$filtre = $this->parametrage_rapport_libre['filtre_applique_'.$champ_libre->nom_sql];
			
			$requete = $management_element->champ($champ_libre->nom_sql)->applique_filtre_sur_requete($filtre, $requete);
		}
		
		
		$resultats = $requete->get();
		
		$champs_libres_utilises = array();

		
		foreach($champs_libres as $champ_libre) {
			
			if(strpos($this->parametrage_rapport_libre['affichage_resultats'], '#'.$champ_libre->nom_sql.'#') !== false)
				$champs_libres_utilises[] = $champ_libre;
			
			// c'est un type 42 ? (recherche d'éléments)
			// on regarde s'il y a des champs libres d'un élément lié
			if($champ_libre->type == 42) {
				
				$champs_libres_bis = Champ_libre::where('type_element', $champ_libre->type_element_ajax)->get();
				
				foreach($champs_libres_bis as $champ_libre_bis) {
					
					if(strpos($this->parametrage_rapport_libre['affichage_resultats'], '#'.$champ_libre->nom_sql.'.'.$champ_libre_bis->nom_sql.'#') !== false) {
						
						$champ_libre_bis->colonne_source = $champ_libre->nom_sql;
						$champs_libres_utilises[] = $champ_libre_bis;
					}
				}
			}
		}
		
		foreach($resultats as $resultat) {
			
			$management = management($type_element, $resultat->id, $resultat);
			
			$affichage = $this->parametrage_rapport_libre['affichage_resultats'];
			
			foreach($champs_libres_utilises as $champ_libre) {
				
				if(empty($management->modele->{$champ_libre->nom_sql})) {

					$affichage = preg_replace('/#si.'.$champ_libre->nom_sql.'#([\s\S]*?)#si.'.$champ_libre->nom_sql.'#/i', '', $affichage);
				}
				else {

					$affichage = preg_replace('/#si.'.$champ_libre->nom_sql.'#([\s\S]*?)#si.'.$champ_libre->nom_sql.'#/i', '$1', $affichage);
				}
				
				if($champ_libre->type_element == $type_element)
					$affichage = str_replace('#'.$champ_libre->nom_sql.'#', $management->champ($champ_libre->nom_sql)->affiche(), $affichage);
				else {
					
					// on va chercher l'élément lié
					if(empty($resultat->{$champ_libre->colonne_source}))
						continue;
					
					$element_lie = management($champ_libre->type_element, $resultat->{$champ_libre->colonne_source});
					
					$affichage = str_replace('#'.$champ_libre->colonne_source.'.'.$champ_libre->nom_sql.'#', $element_lie->champ($champ_libre->nom_sql)->affiche(), $affichage);
				}
			}
			
			$resultat->affichage_element_dans_rapport_pdf = $affichage;
		}
		
		$donnees_pour_pdf['resultats'] = $resultats;
		
		$this->genere_pdf($donnees_pour_pdf);
		
		return parent::genere($ajax);
    }
	
	/**
	 * 
	 * Envoie le rapport par email (via abonnement des rapports)
	 * 
	 */
	public function envoi_par_email($destinataire, $langue = 'en') {
		
		$this->genere();
		
		$titre_rapport = $this->titre;

        // on prépare les données pour envoyer le mail
        $service_email = service('email');

        $parametres_email = [
            'type_configuration' => 1,
            'destinataire' => [$destinataire],
            'pieces_jointes' => [public_path('storage/'.$this->id_rapport.'_'.id_utilisateur.'.pdf')],
            'sujet' => "Envoi rapport automatique : ".$titre_rapport,
        ];

        $variables_email = [
            'titre' => $this->titre,
            'langue_destinataire' => $langue,
        ];

        $retour = $service_email->envoyer('eden::mails.template_standard', $variables_email, $parametres_email);
		
		dd($retour);
	}
}