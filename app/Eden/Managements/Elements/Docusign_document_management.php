<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Champ_libre;
use App\Eden\Models\Element_piece_jointe;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Docusign_document_management extends Element_management {

    public $docusign_enveloppe = null;

    /**
     *
     * Permet de récupérer l'enveloppe rattaché
     *
     */
    public function docusign_enveloppe(){

        if($this->docusign_enveloppe !== null)
            return $this->docusign_enveloppe;

        if(empty($this->modele))
            return null;

        $this->docusign_enveloppe = management('docusign_enveloppe',$this->modele->enveloppe_id);

        return $this->docusign_enveloppe;
    }

	/**
	 *
	 * On cherche les destinations du document signé possible au moment de la récupération des documents sur docusign
	 *
	 */
	public function destinations_possibles($management_element) {

		$champs_libres_fichiers = Champ_libre::where(function($where) {
                $where->where('type', 7)->orWhere('type', 15);
            })
            ->select('nom_sql as destination_document_signe','index_traduction','type')
            ->where('type_element',$management_element->_type_element)->get()->toArray();

        foreach($champs_libres_fichiers as &$champ_libre) {
            $champ_libre['nom'] = traduction($champ_libre['index_traduction'] . '.nom').' ('.$champ_libre['destination_document_signe'].')';
        }

        $destinations_possibles = array(
            'champs_libres' => array(
                'nom' => traduction('interface.champs_libres'),
                'valeurs' => $champs_libres_fichiers
            ),
            'tables' => array(
                'nom' => traduction('interface.tables'),
                'valeurs' => [
                    array(
                        'nom' => traduction('interface.bloc_piece_jointe'),
                        'destination_document_signe' => 'table.element_piece_jointe'
                    )
                ]
            )
        );

        return $destinations_possibles;
	}

    /**
     *
     * Enregistre le document signé une fois reçu par docusign
     *
     */
    public function enregistrer_fichier_signe($fichier){

        $informations = array();

        $docusign_enveloppe = $this->docusign_enveloppe()->modele;

        $nom_fichier = $this->modele->nom_fichier ?? pathinfo($this->modele->fichier, PATHINFO_FILENAME);

        $informations['nom_fichier_signe'] = $nom_fichier."_signed";

        if (fonctionnalite('pieces_jointes_garder_nom_originel') === true)
            $informations['lien_fichier_signe'] = $informations['nom_fichier_signe'].'.pdf';
        else
            $informations['lien_fichier_signe'] = Str::random(40) . ".pdf";

        if($this->modele->destination_document_signe == 'table.element_piece_jointe'){

            if(!is_dir(storage_path("app/public/".$docusign_enveloppe->type_element)))
                mkdir(storage_path("app/public/".$docusign_enveloppe->type_element));

            $informations['lien_fichier_signe'] = $docusign_enveloppe->type_element.'/'.$informations['lien_fichier_signe'];

            $piece_jointe_parent = Element_piece_jointe::where('type_element',$docusign_enveloppe->type_element)
                ->where('element_id',$docusign_enveloppe->element_id)
                ->where('chemin',$this->modele->lien_fichier)
                ->first();

            $piece_jointe = new Element_piece_jointe;
            $piece_jointe->type_element = $docusign_enveloppe->type_element;
            $piece_jointe->element_id = $docusign_enveloppe->element_id;
            $piece_jointe->nom = $informations['nom_fichier_signe'];
            $piece_jointe->chemin = $informations['lien_fichier_signe'];
            $piece_jointe->titre = $informations['nom_fichier_signe'];
            $piece_jointe->dossier_parent = !empty($piece_jointe_parent->dossier_parent) ? $piece_jointe_parent->dossier_parent : null;
            $piece_jointe->save();

            if(!empty($piece_jointe_parent) && $this->modele->remplacement_fichier == 1)
                $piece_jointe_parent->delete();
        }
        else{

            $nom_sql_champ = $this->modele->destination_document_signe;

            $champ_libre = champ_libre_modele($docusign_enveloppe->type_element,$nom_sql_champ);

            $valeur = $this->docusign_enveloppe()->management_element()->modele->{$nom_sql_champ};

            if($champ_libre->type == 15){

                try{
                    $pieces_jointes = json_decode($valeur,true);
                }
                catch(\Exception $e){
                    $pieces_jointes = [];
                }

                $remplacement_effectue = false;

                if($this->modele->remplacement_fichier == 1){

                    foreach($pieces_jointes as &$piece_jointe){

                        if($piece_jointe['url_storage'] == 'public/'.$this->modele->lien_fichier){

                            $remplacement_effectue = true;

                            $piece_jointe['url_storage'] = 'public/'.$informations['lien_fichier_signe'];
                            $piece_jointe['url_public'] = 'storage/'.$informations['lien_fichier_signe'];
                            $piece_jointe['nom_original'] = $informations['nom_fichier_signe'];
                        }
                    }
                }

                if($remplacement_effectue == false){

                    $pieces_jointes[] = array(
                        'url_storage' => 'public/'.$informations['lien_fichier_signe'],
                        'url_public' => 'storage/'.$informations['lien_fichier_signe'],
                        'type' => 'pdf',
                        'nom_original' => $informations['nom_fichier_signe'],
                        'image' => false
                    );
                }

                $valeur = json_encode($pieces_jointes);
            }
            else
                $valeur = $informations['lien_fichier_signe'];

            $this->docusign_enveloppe()->management_element()->enregistre(array(
                $nom_sql_champ=> $valeur
            ));
        }

        $this->docusign_enveloppe()->management_element()->methode_post_signature();

        file_put_contents(storage_path('app/public/'.$informations['lien_fichier_signe']), file_get_contents($fichier->getPathname()));

        $this->enregistre($informations);

        if(service('mfiles')->verifier_synchronisation_mfiles($docusign_enveloppe->type_element)) {

            try {
                service('mfiles')->creer_fichier($informations['nom_fichier_signe'].'.pdf', file_get_contents(storage_path('app/public/'.$informations['lien_fichier_signe'])), $docusign_enveloppe->type_element, $docusign_enveloppe->element_id,false,true);
            } catch (\App\Eden\Exceptions\Eden_exception $e) {

                Log::error("Le document signé ".$informations['nom_fichier_signe']." n'a pas pu être envoyé à m-files");
            }
        }
    }
}