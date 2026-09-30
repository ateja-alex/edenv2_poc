<?php

namespace App\Eden\Managements\Fiches;

use App\Eden\Managements\Fiche_management;

use PDF;

/**
 *
 * Gestion des fiches de proposition commerciale
 *
 */
class Fiche_proposition_commerciale_management extends Fiche_management {
	
	/**
	 *
	 * Prépare les données pour la fiche
	 *
	 * @attention si vous utilisez cette méthode dans les classes filles, il faut faire appelle à parent::prepare_donnees_pour_fiche($donnees)
	 *
	 */
	public function prepare_donnees_pour_fiche($donnees = array()) {
		
		// on va chercher les données de base
		$donnees = parent::prepare_donnees_pour_fiche($donnees);

		return $donnees;
	}
	
	/**
	 * 
	 * Retourne les données pour la génération du PDF
	 * 
	 */
	public function donnees_pour_pdf_fiche() {
		
		$proposition_commerciale = modele('proposition_commerciale', $this->id_element);

		$donnees_pour_pdf = array(
		
			'proposition_commerciale' => $proposition_commerciale,
			'articles' => modele('article_sur_proposition_commerciale')->select('article.*')->join('article', 'article.id', 'article_sur_proposition_commerciale.article_id')->where('proposition_commerciale_id', $this->id_element)->get(),
		);
		
		return $donnees_pour_pdf;
	}
	
	/**
	 * 
	 * Génère le PDF pour la fiche
	 * 
	 */
	public function genere_pdf_pour_fiche() {
		
		$donnees = $this->donnees_pour_pdf_fiche();
		
		$proposition_commerciale = modele('proposition_commerciale', $this->id_element);
		
		$nom_du_pdf = 'proposition_commerciale_'.$this->id_element.'.pdf';
		
		$pdf = PDF::loadView('eden::pdf.proposition_commerciale', $donnees)->setPaper('a4', 'landscape');
		
		$pdf->getDomPDF()->set_option("enable_php", true);

		\Storage::put('propositions_commerciales/'.$nom_du_pdf, $pdf->output());
		
		$merger = \PDFMerger::init();
		$merger->addPDF(public_path('eden/pdf/exemple_proposition_commerciale.pdf'));
		$merger->addPDF(storage_path('app/propositions_commerciales/'.$nom_du_pdf));
		$merger->merge();
		
		$nom_du_pdf = str_replace('.pdf', '_'.date('YmdHis').'.pdf', $nom_du_pdf);

		$merger->save(storage_path('/app/propositions_commerciales/'.$nom_du_pdf));

		return response()->file(storage_path('/app/propositions_commerciales/'.$nom_du_pdf));
	}

    /**
     *
     * Retourne les actions sur les listes
     * Peut être surchargé pour ajouter des actions sur mesure en fonction des éléments ou du spécifique
     *
     */
    public function options_fil_ariane($donnees) {

        $options_fil_ariane = parent::options_fil_ariane($donnees);

        $presence_envoi_mail = false;
        $modele = modele('proposition_commerciale', $this->id_element);
        
        foreach ($options_fil_ariane as $option) {
            if($option['id'] == 'envoi_mail')
                $presence_envoi_mail = true;
        }
        
        if(!$presence_envoi_mail && !empty($modele->lead_id)) {
            $options_fil_ariane[] = [
                'id' => 'envoi_mail',
                'ordre' => 0
            ];
        }

        $options_fil_ariane[] = [
            'id' => 'genere_pdf',
            'ordre' => 1
        ];
        
        return $options_fil_ariane;
        
    }
	
	
}
