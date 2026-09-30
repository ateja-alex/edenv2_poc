<?php

namespace App\Eden\Controllers;

use App\Eden\Models\Colonne;
use App\Eden\Variables;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Eden\Models\Liste_libre_filtre;
use App\Eden\Models\Liste_libre_filtre_enregistre;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Liste_libre;
use App\Eden\Managements\Listes_management;

class Note_de_frais_controller extends Controller {

    public function changement_statut(Request $request) {

        $donnees = $request->all();
        $retours = [];

        foreach ($request->ids as $note_de_frais_id) {

            $note_de_frais = management('note_de_frais', $note_de_frais_id);

            $retour_validation = $donnees['statut'] == 1 ? $note_de_frais->valide() : $note_de_frais->refuse();

            if($retour_validation !== true)
                $retours[] = ucfirst(table_libre('note_de_frais')->element).' '.$note_de_frais_id.' : '.$retour_validation;
        }

        return response()->json(['retour'  => empty($retours), 'message' => implode('<br>',$retours)]);
    }

    /**
     *
     *
     * Fonction qu'on appelle suite à l'action d'accpeter ou de refuser la note de frais via mail
     *
     */
    public function validation_note($id_demande, $reponse) {

        $management = management('note_de_frais', $id_demande);
        $type_message = 'message';
        $message = traduction('messages.php.intranet.changement_statut_valide');

        $retour = $reponse == 1 ? $management->valide() : $management->refuse();

        if($retour !== true){

            $type_message = 'erreur';
            $message = $retour;
        }

        return redirect()->route('base_eden.liste.index', ['type_element' => 'note_de_frais'])->with($type_message, $message);
    }

	/**
     *
     * Exportation des notes de frais au format pdf
     *
     */
	public function export_pdf(Request $request){

        $formulaire = $request->all();

        //Gestion des filtres
        $filtres = [];

        $id_liste = $formulaire['id_liste'];

        $filtres_liste = Liste_libre_filtre::where('liste_libre_id', $id_liste)->get();

        $filtres_actifs = [];
        if(isset($formulaire['filtres'])) {
            $filtres_actifs = json_decode($formulaire['filtres']);
        }

        $nom_variable_date = array(
                'cette_annee' => "Tout ".date('Y'),
				'annee_derniere' => "Tout ".date('Y', strtotime('last year')),
				'annee_prochaine' => "Tout ".date('Y', strtotime('next year')),
				'depuis_janvier' => "De janvier à ". Variables::mois_de_lannee_format_complet(date('m'))." ".date('Y'),
                'ce_mois_ci' => Variables::mois_de_lannee_format_complet(date('m'))." ".date('Y'),
                'le_mois_dernier' => Variables::mois_de_lannee_format_complet(date('m',strtotime('last month')))." ".date('Y'),
                'le_mois_prochain' => Variables::mois_de_lannee_format_complet(date('m',strtotime('next month')))." ".date('Y'),
                'aujourdhui' => "Aujourd'hui",
                'hier' => "Hier",
                'demain' => "Demain",
                '7_derniers_jours' => "7 derniers jours",
                '7_prochains_jours' => "7 prochains jours",
                '6_prochains_jours' => "6 prochains jours",
                'cette_semaine' => "Cette semaine",
                'jusqua_dimanche' => "Jusqu'à dimanche",
                'passe' => "Est passé",
                'pas_passe' => "N'est pas passé",
                'pas_renseigne' => "N'est pas renseigné",
                'renseigne'=> "Est renseigné"
            );

        foreach($filtres_liste as $filtre){

            $valeurs_possibles = [];

            $champ = management('note_de_frais')->champ($filtre->nom_sql);

            $nom_sql = $filtre->nom_sql;

            $nom = $champ->modele->nom;

            $filtres[$nom_sql]['nom'] = $nom;

            if(property_exists($champ,'valeurs_possibles')){

                $valeurs_possibles = $champ->valeurs_possibles;
            }

            elseif(method_exists($champ,'valeurs_pour_filtre')){

                $valeurs_possibles = $champ->valeurs_pour_filtre();

            }

            foreach($filtres_actifs as $filtre_actif){

                if($filtre->id == $filtre_actif->id){

                    foreach($filtre_actif->valeurs as $cle => $valeur){

                        if(isset($valeurs_possibles[$valeur])){

                            $valeur = $valeurs_possibles[$valeur];
                        }

                        elseif($champ->modele->type_element_ajax != null){

                            $valeur = management($champ->modele->type_element_ajax,$valeur)->affiche();

                        }

                        elseif($cle == 'debut' && $valeur){

                            $valeur = 'A partir du '.$valeur;
                        }

                        elseif($cle == 'fin' && $valeur){

                            $valeur = "Jusqu'au ".$valeur;
                        }
                        
                        elseif($cle == 'variable' && $valeur){
                            
                            $valeur = $nom_variable_date[$valeur];
                        }

                        if($valeur) {

                            if (isset($filtres[$nom_sql]['valeur'])) {

                                $filtres[$nom_sql]['valeur'] .= ' - ' . $valeur;
                            } else {

                                $filtres[$nom_sql]['valeur'] = $valeur;
                            }
                        }
                    }
                }

            }

            if(!isset($filtres[$nom_sql]['valeur'])){

                $filtres[$nom_sql]['valeur'] = 'Tous';
            }

        }

        //Récupération des colonnes pour le tableau du pdf

        $colonnes = Champ_libre::where('type_element', 'note_de_frais')->orderByRaw('case when ordre is null then 0 else ordre end, id_cl')->get();

        $management = new Listes_management();

        $liste = Liste_libre::where('id_rapport', 'export_pdf_note_de_frais')->first();
        $colonnes_listes = $management->obtenir_colonnes($liste->id, table_libre('note_de_frais'));
		
		$colonnes_pdf = array();
		
		foreach($colonnes_listes as $cle => $colonne_liste){

            $methode = false;

            if ($colonne_liste->methode){

                $methode=$colonne_liste->methode;
            }

                foreach ($colonnes as $colonne) {

                    if ($colonne_liste->valeur == $colonne->nom_sql) {

                        $colonne_liste->nom_sql = $colonne->nom_sql;

                    }

                }

                if (!empty($colonne_liste->nom_sql) || $methode) {
                    $colonnes_pdf[] = array(
                        'nom' => $colonne_liste->nom,
                        'nom_sql' => $colonne_liste->nom_sql,
                        'methode' => $methode,
                    );
                }

        }


        // Récupération des données des notes de frais ainsi que des annexes
        $annexes = array();

        $categories = array();

        $totaux = array('tva' => 0 , 'ttc' => 0);

        if(isset($formulaire['ids_note_de_frais']) ){

            $ids=json_decode($formulaire['ids_note_de_frais']);

            $numero_page = 1 ;

            management('note_de_frais')->traitement_notes_de_frais_pour_export_pdf($ids, $numero_page, $totaux, $annexes, $categories);

        }

        foreach($annexes as $cle => $annexe) {

            if($annexe['image'] && is_file(storage_path('app/public/'.$annexe['image'])) && $annexe['extension'] == 'pdf'){

                $numero_page ++;
                $annexe['page']  = "Page n° " . $numero_page;

                $annexes[$cle] = $annexe;
            }
        }

        // Génération du pdf
        $html =  view('eden::pdf.note_de_frais', [
            'ids_note_de_frais' => $ids,
            'filtres' => $filtres,
            'colonnes' => $colonnes_pdf,
            'annexes' => $annexes,
            'categories' => $categories,
            'totaux' => $totaux,
        ]);
        
        $pdf = \PDF::loadhtml($html)->setPaper('A4');

        $pdf->getDomPDF()->set_option("enable_php", true);

        $merger = \PDFMerger::init();

        $merger->addString($pdf->output());
        
        $fichier_supprimer = [];

        //Ajout via merge des pdfs présents en annexe
        foreach($annexes as $annexe) {

            if($annexe['image'] && is_file(storage_path('app/public/'.$annexe['image'])) && $annexe['extension'] == 'pdf'){

                $fichier = storage_path('app/public/'.$annexe['image']);
                
                $fichier_utilisable = service('pdf')->pdf_utilisable($fichier);

                $numero_page ++;
                $annexe['page'] = "Page n° " . $numero_page;
                $merger->addPDF(
                    $fichier_utilisable
                );
                
                if($fichier_utilisable != $fichier && file_exists($fichier_utilisable))
                    $fichier_supprimer[] = $fichier_utilisable;
                
            }
        }

        $merger->setFileName('Export_note_de_frais_du_'.date('d_m_Y').'.pdf');

        $merger->merge();
        
        foreach ($fichier_supprimer as $fichier) {
            if(file_exists($fichier))
                unlink($fichier);
        }

        return $merger->download();
    }

    /**
     * @param $id
     *
     * Permet de récupérer les tvas avec leur montant
     *
     */
    public function recuperer_valeur_tva($id){

        $lignes = modele('note_de_frais_lignes')->where('note_de_frais_id',$id)->get();

        $taux_de_tva_nom = champ_libre('note_de_frais_lignes','taux_tva')->champ->recuperation_options_select();

        $taux_de_tva = array();

        foreach($lignes as $ligne){

            if($ligne->taux_tva > 0 ){

                if(!isset($taux_de_tva[$ligne->taux_tva]['montant']))
                    $taux_de_tva[$ligne->taux_tva]['montant'] = 0;

                $taux_de_tva[$ligne->taux_tva]['montant'] += round($ligne->montant_ttc - $ligne->montant_ht,2);

            }
        }

        foreach($taux_de_tva as $tva_id => $informations){

            $taux_de_tva[$tva_id]['nom'] = $taux_de_tva_nom[$tva_id] ?? '';
        }

        return json_encode(array('taux_de_tva'=>$taux_de_tva));

    }

    /**
     *
     * Récupére les informations nécessaires à la création d'une note de frais
     *
     */
    public function informations(){

        $articles_pour_note_de_frais = modele('article_note_de_frais')->get()->keyBy('id');

        $taux_de_tva = champ_libre('note_de_frais_lignes','taux_tva')->champ->recuperation_options_select();

        $taux_de_tva = modele('code_tva')->whereIn('id', array_keys($taux_de_tva))->get()->keyBy('id');

        return response()->json(
            array(
                'articles_pour_note_de_frais' => $articles_pour_note_de_frais,
                'taux_de_tva' => $taux_de_tva,
            )
        );
    }
}
