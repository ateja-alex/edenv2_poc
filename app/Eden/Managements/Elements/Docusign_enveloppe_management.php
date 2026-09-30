<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Champ_libre;
use Illuminate\Support\Facades\DB;

class Docusign_enveloppe_management extends Element_management{

    public $management_element = null;

    /**
     *
     * Crée les details d'une ligne
     *
     */
    public function recuperer_details_ligne_pour_liste($id_element,$type_element,$id_liste_parent) {

        $modele = modele('docusign_enveloppe')->where('id',$id_element)->first();

        // On va chercher les lignes qui nous interessent
        $signataires = modele('docusign_signataire')
            ->where('enveloppe_id',$id_element)
            ->get()->toArray();

        $champ_statut = management('docusign_signataire')->champ('statut');

        foreach($signataires as &$signataire){

            $signataire['statut'] = $champ_statut->affiche($signataire['statut']);
        }

        $documents = modele('docusign_document')
            ->where('enveloppe_id',$id_element)
            ->select(DB::raw('COALESCE(fichier,lien_fichier) as chemin_original'),
                DB::raw('COALESCE(nom_fichier,COALESCE(fichier,lien_fichier)) as nom_original'),
                'nom_fichier_signe','lien_fichier_signe')
            ->get()->toArray();

        foreach($documents as &$document){

            if(strpos($document['chemin_original'],'gescom/') !== false)
                $document['chemin_original'] = route('base_eden.element.afficher_pdf',array($modele->type_element,$modele->element_id));
            else
                $document['chemin_original'] = '/storage/'.$document['chemin_original'];

            $document['lien_fichier_signe'] = '/storage/'.$document['lien_fichier_signe'];

            $document['fichier_original'] = array(
                'nom' => $document['nom_original'],
                'chemin' => $document['chemin_original'],
            );

            $document['fichier_signe'] = array(
                'nom' => $document['nom_fichier_signe'],
                'chemin' => $document['lien_fichier_signe'],
            );
        }

        $colonnes_signataires = array(
            'email',
            'statut'
        );

        $colonnes_documents = array(
            'fichier_original',
            'fichier_signe',
        );

        // On appelle la vue qui affiche les infos que l'on veut via un render();
        $vue = "eden::listes.includes.details_ligne_docusign_enveloppe";
        $vue_render = view($vue, array(

            'signataires' => $signataires,
            'documents' => $documents,
            'colonnes_signataires' => $colonnes_signataires,
            'colonnes_documents' => $colonnes_documents
        ))->render();

        return $vue_render;
    }

    protected function creation_elements_sous_formulaires($sous_formulaires){

        parent::creation_elements_sous_formulaires($sous_formulaires);

        $this->envoie_signature();
    }

    /**
     *
     * Fonction qui envoie le signature via docusign
     *
     */
    public function envoie_signature(){

        if(!empty($this->modele->statut))
            return;

        $parametres["message_signature"] = "Signature document";

        $documents = modele('docusign_document')
            ->select(DB::raw("CONCAT(IF(COALESCE(fichier,lien_fichier) LIKE 'gescom/%','app/','app/public/'),COALESCE(fichier,lien_fichier)) as chemin"),DB::raw('COALESCE(nom_fichier,fichier,lien_fichier) as nom'),'id')
            ->where('enveloppe_id',$this->modele->id)
            ->get()->keyBy('id')->toArray();

        $parametres["documents"] = $documents;

        $signataires = modele('docusign_signataire')
            ->select('email as adresse_email','id')
            ->where('enveloppe_id',$this->modele->id)
            ->get()->keyBy('id')->toArray();

        $parametres['placements_signatures'] = [];

        $nom_placements = array(
            'sign_here_tabs' => 'signature',
            'date_signed_tabs' => 'date_signe',
            'full_name_tabs' => 'nom',
            'company_tabs' => 'entreprise',
            'title_tabs' => 'titre',
            'text_tabs' => 'texte',
            'initial_here_tabs' => 'initial',
        );

        foreach(array_values($signataires) as $index_signataire => $signataire){

            $index_signataire = $index_signataire+1;

            foreach($nom_placements as $nom_docusign => $indicateur) {

                $parametres['placements_signatures'][] = array(
                    'signataire_id' => $signataire['id'],
                    'indicateur' => '##docusign_'.$indicateur.'_' . $index_signataire . '##',
                    'type' => $nom_docusign,
                );

            }
        }

        $parametres["signataires"] = $signataires;

        $parametres["statut"] = "sent";

	    $retour = service('docusign')->envoyer_enveloppe($parametres);

        if($retour['retour'] !== true) {
            $this->enregistre_modele(array('statut' => 0, 'erreur_docusign' => $retour['erreur']->getResponseObject()->getErrorCode()));
            return $retour['erreur']->getResponseObject()->getErrorCode();
        }

        $this->enregistre_modele(array('statut' => 7,'erreur_docusign'=> null,'docusign_enveloppe_id'=> $retour['envelope_id']));

        management($this->modele->type_element,$this->modele->element_id)->log_demande_signature();

        return true;
    }

    /**
     *
     * Permet de récupérer le modele de l'élement
     *
     */
    public function management_element(){

        if($this->management_element !== null)
            return $this->management_element;

        if(empty($this->modele))
            return null;

        $this->management_element = management($this->modele->type_element,$this->modele->element_id);

        return $this->management_element;
    }

    public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();

        $liste_options[] = 'zoom';
        $liste_options[] = 'relance';

        if(in_array('apercu',$liste_options))
            unset($liste_options[array_search('apercu',$liste_options)]);

        return $liste_options;
    }

    public function relance_signature(){

        if(empty($this->modele->docusign_enveloppe_id))
            return false;

        $retour = service('docusign')->relancer_enveloppe($this->modele->docusign_enveloppe_id);

        if($retour !== true)
            return false;

        return true;
    }
}
