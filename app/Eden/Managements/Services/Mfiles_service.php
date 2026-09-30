<?php

namespace App\Eden\Managements\Services;

use App\Eden\Exceptions\Eden_exception;
use App\Eden\Models\Element_piece_jointe;
use App\Eden\Models\Elements\Element;
use App\Eden\Variables;
use Log;

class Mfiles_service {

    /*
     *
     * On initialise les données nécessaires aux requêtes
     * Données préparées tout le temps : jeton d'authentification, la ligne de mappage du type_element, les attributs à synchro sur le type_element.
     * Si on passe un id_element, on récupère l'élément correspondant
     * Si recuperer_element ==0 true, on récupère tous les éléments du type_element. (utile pour le cron de création en masse)
     *
     */
    private function initialisation_donnees($type_element, $id_element = null, $recuperer_elements = false){

        $donnees['jeton_authentification'] = parametre('mfiles_jeton_authentification');
        $donnees['parametrage_mappage_mfiles'] = modele('parametrage_mappage_mfiles')->where('table_libre', $type_element)->first();

        if(empty($donnees['parametrage_mappage_mfiles']))
            throw new Eden_exception('Le type élément n\'a pas été trouvé dans le mappage');

        $donnees['id_type_objet_mfiles'] = $donnees['parametrage_mappage_mfiles']->objet_mfiles;

        $donnees['attributs_a_synchro'] = modele('parametrage_mappage_mfiles_attributs')->where('parametrage_mappage_mfiles_id', $donnees['parametrage_mappage_mfiles']->id)->get();

        if(!empty($id_element))
            $donnees = $this->recuperer_objet($donnees, $type_element, $id_element);

        if($recuperer_elements === true)
            $donnees = $this->recuperer_elements($donnees, $type_element);

        return $donnees;
    }

    /*
     *
     * On récupère la ligne bibliotheque_synchro_elements qui correspond à l'élément Eden.
     * Si on n'en trouve pas, on crée l'objet dans M-Files puis on rappelle cette méthode pour le récupérer
     *
     */
    private function recuperer_objet($donnees, $type_element, $id_element){

        $donnees['objet_mfiles'] = modele('bibliotheque_synchro_elements')
            ->where('type_element', $type_element)
            ->where('element_id', $id_element)
            ->where('stockage_externe', 2)
            ->first();

        if(empty($donnees['objet_mfiles'])){

            $modele_element = modele($type_element)->where('id', $id_element)->first();

            if(empty($modele_element)){

                $donnees['id_objet_mfiles'] = null;
                return $donnees;
            }

            $retour = $this->creer_element($donnees, $type_element, $id_element, $modele_element);

            if((isset($retour->Exception) || isset($retour->ErrorCode)) && isset($retour->Message))
                throw new Eden_exception('Erreur lors de la création de l\'élément : ' . $type_element . ' ' . $id_element . ' Message : ' . $retour->Message);

            return $this->recuperer_objet($donnees, $type_element, $id_element);
        }
        else
            $donnees['id_objet_mfiles'] = $donnees['objet_mfiles']->id_dossier;

        return $donnees;
    }

    /*
     *
     * On récupère les éléments du type_element
     *
     */
    private function recuperer_elements($donnees, $type_element){

        $champs_a_recuperer = array('id', 'modifie_le');

        foreach($donnees['attributs_a_synchro'] as $attribut){

            $champs_a_recuperer[] = $attribut->champ_libre_eden;
        }

        $donnees['elements'] = modele($type_element)->select($champs_a_recuperer)->get();

        return $donnees;
    }

    /*
     *
     * On crée l'objet stream context qui servira à la requête
     * Les headers doivent être passés sous forme de tableau, la clé correspond au nom du header.
     *
     */
    private function initialisation_donnees_requete($methode_requete = 'GET', $contenu = false, $headers = array()){

        $opts = array(
            'http'=>array(
                'method'=> $methode_requete,
                'header'=>"X-Authentication: " . parametre('mfiles_jeton_authentification'),
                'ignore_errors' => true,
            )
        );

        foreach($headers as $nom_header => $header) {

            $opts['http']['header'] .= "\r\n" . $nom_header . ": " . $header;
        }

        if($contenu !== false)
            $opts['http']['content'] = $contenu;

        $context = stream_context_create($opts);

        return $context;
    }

    /*
     *
     * On prépare les données nécessaires aux requêtes de création ou de modification d'élément.
     * On utilise ces données comme contenu de la requête.
     *
     */
    private function preparation_donnees_objet($donnees, $type_element, $id_element, $element = false, $modification = null){

        // Si le modèle de l'élément n'a pas été passé, on va le chercher en bdd avec les attributs à synchroniser.
        if($element === false){

            $champs_a_recuperer = array('id');

            foreach($donnees['attributs_a_synchro'] as $attribut){

                $champs_a_recuperer[] = $attribut->champ_libre_eden;
            }

            $element = modele($type_element)->select($champs_a_recuperer)->where('id', $id_element)->first();
        }

        //On indique la classe M-Files
        if($modification === null){

            $donnees_objet = [
                [
                    'PropertyDef' => 100,
                    'TypedValue' => [
                        'DataType' => 9,
                        'Lookup' => ['Item' => $donnees['parametrage_mappage_mfiles']->classe_mfiles],
                    ]
                ],
            ];
        }
        else
            $donnees_objet = array();


        // On ajoute l'id Eden
        if(!empty(fonctionnalite('mfiles_attribut_eden_id')))
            $donnees_objet[] = [
                'PropertyDef' => fonctionnalite('mfiles_attribut_eden_id'),
                'TypedValue' => [
                    'DataType' => 2,
                    'Value' => $element->id,
                ],
            ];

        foreach($donnees['attributs_a_synchro'] as $attribut){

            // Les attributs de type 9 (Lookup) et 10 (MultiSelectLookup) correspondent aux champs type 42 de Eden,
            // il faut donc d'abord vérifier si le type_element est à synchroniser.
            if($attribut->type == 9 || $attribut->type == 10){

                $champ = management($type_element)->champ($attribut->champ_libre_eden);
                $verification_mappage = modele('parametrage_mappage_mfiles')->where('table_libre', $champ->modele->type_element_ajax)->first();

                if(empty($verification_mappage))
                    throw new Eden_exception('Le type d\'élément lié au champ ' . $attribut->champ_libre_eden . ' n\'est pas paramétré dans le mappage.');

                $element_a_synchro = $this->initialisation_donnees($champ->modele->type_element_ajax, $element->{$attribut->champ_libre_eden});

                if(empty($element_a_synchro['id_objet_mfiles']))
                    continue;

                $donnees_attribut_en_cours = [
                    'PropertyDef' => $attribut->attribut_mfiles,
                    'TypedValue' => [
                        'DataType' => $attribut->type,
                    ],
                ];

                // Le formatage des données est différent entre le type 9 et le type 10
                if($attribut->type == 9)
                    $donnees_attribut_en_cours['TypedValue']['Lookup'] = ['Item' => $element_a_synchro['id_objet_mfiles']];
                else
                    $donnees_attribut_en_cours['TypedValue']['Lookups'] = [['Item' => $element_a_synchro['id_objet_mfiles']]];

                $donnees_objet[] = $donnees_attribut_en_cours;
            }
            else
                $donnees_objet[] = [
                    'PropertyDef' => $attribut->attribut_mfiles,
                    'TypedValue' => [
                        'DataType' => $attribut->type,
                        'Value' => $element->{$attribut->champ_libre_eden},
                    ],
                ];
        }

        if($modification === null){

            $a_retourner = ['PropertyValues' => $donnees_objet];
            $a_retourner['Files'] = array();
        }
        else
            $a_retourner = $donnees_objet;

        return json_encode($a_retourner);
    }

    /*
     *
     * On prépare les données nécessaires aux requêtes de création de document.
     * On utilise ces données comme contenu de la requête.
     *
     */
    private function preparation_donnees_document($donnees, $nom_fichier, $fichier){

        $liaison = [
            'PropertyDef' => $donnees['parametrage_mappage_mfiles']->attribut_liaison_document_mfiles,
            'TypedValue' => [
                'DataType' => $donnees['parametrage_mappage_mfiles']->type_attribut_liaison,
            ]
        ];

        if($donnees['parametrage_mappage_mfiles']->type_attribut_liaison == 9)
            $liaison['TypedValue']['Lookup'] = ['Item' => $donnees['id_objet_mfiles']];
        else
            $liaison['TypedValue']['Lookups'] = [['Item' => $donnees['id_objet_mfiles']]];

        $donnees_document = json_encode([
            'PropertyValues' => [
                [
                    'PropertyDef' => 100,
                    'TypedValue' => [
                        'DataType' => 9,
                        'Lookup' => ['Item' => $donnees['parametrage_mappage_mfiles']->classe_document_mfiles],
                    ]
                ],
                [
                    'PropertyDef' => 0,
                    'TypedValue' => [
                        'DataType' => 1,
                        'Value' => $nom_fichier[0],
                    ]
                ],
                [
                    'PropertyDef' => 1040,
                    'TypedValue' => [
                        'DataType' => 1,
                        'Value' => $nom_fichier[0],
                    ]
                ],
                $liaison
            ],
            'Files' => array($fichier),
        ]);

        return $donnees_document;
    }

    /*
     *
     * Création des éléments d'un type_element dans M-Files
     *
     */
    public function creer_elements_en_masse($type_element, $forcer_modification){

        $donnees = $this->initialisation_donnees($type_element, null, true);

        foreach($donnees['elements'] as $element){

            $verification_existence_element = modele('bibliotheque_synchro_elements')->where('type_element', $type_element)->where('element_id', $element->id)->first();

            if($forcer_modification || (!empty($verification_existence_element) && (strtotime($verification_existence_element->derniere_synchro) < strtotime($element->modifie_le) || $verification_existence_element->derniere_synchro === null))){

                try {
                    $this->modifier_element($donnees, $type_element, $element->id, $element);
                } catch(Eden_exception $e){

                    Log::warning('[Synchronisation M-Files] La modification a échoué pour l\'élément ' . $element->id . ' de type ' . $type_element . ' dans M-Files. Message d\'erreur : ' . $e->getMessage());
                    continue;
                }

                Log::info('[Synchronisation M-Files] L\'élément ' . $element->id . ' de type ' . $type_element . ' a bien été modifié dans M-Files.');
                continue;
            }
            elseif(!empty($verification_existence_element)){

                Log::info('[Synchronisation M-Files] L\'élément ' . $element->id . ' de type ' . $type_element . ' n\'a pas été modifié dans M-Files car il n\'a pas été modifié récemment dans Eden.');
                continue;
            }

            try {
                $this->creer_element($donnees, $type_element, $element->id, $element);
            } catch(Eden_exception $e){

                Log::warning('[Synchronisation M-Files] La création a échoué pour l\'élément ' . $element->id . ' de type ' . $type_element . ' dans M-Files. Message d\'erreur : ' . $e->getMessage());
                continue;
            }

            Log::info('[Synchronisation M-Files] L\'élément ' . $element->id . ' de type ' . $type_element . ' a bien été créé dans M-Files.');
        }

        return true;
    }

    /*
     *
     * Crée un élément dans M-Files
     *
     */
    public function creer_element($donnees, $type_element, $id_element, $element = false) {

        $donnees_objet = $this->preparation_donnees_objet($donnees, $type_element, $id_element, $element);

        $context = $this->initialisation_donnees_requete('POST',$donnees_objet, ['Content-Length' => strlen($donnees_objet), 'Content-Type' => 'application/json']);

        $retour = file_get_contents(fonctionnalite('mfiles_url') . '/REST/objects/'. $donnees['id_type_objet_mfiles'],false,$context);

        $retour = json_decode($retour);

        if((isset($retour->Exception) || isset($retour->ErrorCode)) && isset($retour->Message))
            throw new Eden_exception('Erreur lors de la création de l\'élément : ' . $retour->Message);

        management('bibliotheque_synchro_elements')->enregistre([
            'type_element' => $type_element,
            'element_id' => $id_element,
            'stockage_externe' => 2,
            'id_dossier' => $retour->DisplayID,
            'derniere_synchro' => date('Y-m-d H:i:s')
        ]);

        return $retour;
    }

    /*
     *
     * Crée le document s'il n'y a aucun Element_piece_jointe correspondant
     * S'il y en a, on supprime le fichier existant dans M-files, on le recrée, puis on met à jour les infos de la pj
     *
     */
    public function creer_document($nom_fichier, $fichier, $type_element, $id_element){

        $piece_jointe = Element_piece_jointe::where('type_element', $type_element)->where('element_id', $id_element)->where('document_commercial', 1)->first();

        if(!empty($piece_jointe)){

            $this->supprimer_piece_jointe($piece_jointe->dossier_parent);
            $retour = $this->creer_fichier($nom_fichier, $fichier, $type_element, $id_element, true, true);

            $piece_jointe->dossier_parent = $retour->DisplayID;
            $piece_jointe->stockage_externe_id = $retour->Files[0]->ID;

            $piece_jointe->save();
        }
        else
            $retour = $this->creer_fichier($nom_fichier, $fichier, $type_element, $id_element, true);

        return $retour;
    }

    /*
     *
     * Modifie les propriétés d'un élément M-Files existant
     *
     */
    public function modifier_element($donnees, $type_element, $element_id, $element){

        $donnees = $this->recuperer_objet($donnees, $type_element, $element_id);

        $donnees_objet = $this->preparation_donnees_objet($donnees, $type_element, $element_id, $element, true);

        $context = $this->initialisation_donnees_requete('POST',$donnees_objet, ['Content-Length' => strlen($donnees_objet), 'Content-Type' => 'application/json']);

        $retour = file_get_contents(fonctionnalite('mfiles_url') . '/REST/objects/'. $donnees['id_type_objet_mfiles'] . '/' . $donnees['id_objet_mfiles'] . '/latest/properties',false,$context);

        $retour = json_decode($retour);

        if((isset($retour->Exception) || isset($retour->ErrorCode)) && isset($retour->Message))
            throw new Eden_exception('Erreur lors de la modification de l\'élément : ' . $retour->Message);

        management('bibliotheque_synchro_elements', $donnees['id_objet_mfiles'], $donnees['objet_mfiles'])
            ->enregistre_modele(['derniere_synchro' => date('Y-m-d H:i:s')]);

        return $retour;
    }

    /*
     *
     * Permet d'upload le fichier. Dans M-Files, il est nécessaire d'envoyer le fichier à l'api avant de l'associer à un élément
     * car il faut utiliser le retour de l'api comme étant le fichier dans la requête de création.
     *
     */
    public function upload_fichier($fichier){

        $opts = array(
            'http'=>array(
                'method'=>"POST",
                'header'=> "X-Authentication: ".parametre('mfiles_jeton_authentification')."\r\n".
                    "Content-Type: application/pdf\r\n",
                'content' => $fichier
            )
        );

        $context = stream_context_create($opts);

        $retour = file_get_contents(fonctionnalite('mfiles_url') . '/REST/files',false,$context);

        return $retour;
    }

    /*
     *
     * Permet de créer l'objet de type "document" dans M-files auquel sera associé le fichier de la pièce jointe Eden.
     *
     */
    public function creer_fichier($nom_fichier, $fichier, $type_element, $id_element, $document = false, $remplacement = false){

        $donnees = $this->initialisation_donnees($type_element, $id_element);

        $nom_fichier = explode('.', $nom_fichier);
        $fichier_uploade = $this->upload_fichier($fichier);
        $fichier_uploade = json_decode($fichier_uploade);
        $fichier_uploade->Extension = $nom_fichier[1];

        $donnees_document = $this->preparation_donnees_document($donnees, $nom_fichier, $fichier_uploade);

        $context = $this->initialisation_donnees_requete('POST',$donnees_document, ['Content-Length' => strlen($donnees_document), 'Content-Type' => 'application/json']);

        $retour = file_get_contents(fonctionnalite('mfiles_url') . '/REST/objects/0',false,$context);

        $piece_jointe = json_decode($retour);

        if((isset($piece_jointe->Exception) || isset($piece_jointe->ErrorCode)) && isset($piece_jointe->Message))
            throw new Eden_exception('Erreur lors de la création du document : ' . $piece_jointe->Message);

        if(!$remplacement)
            $piece_jointe = $this->sauvegarde_fichier_eden($type_element, $id_element, $piece_jointe, $document);

        return $piece_jointe;
    }

    /*
     *
     * Supprime un élément de M-Files.
     * Pas utilisée pour le moment, mais elle le sera si on gère la suppression des éléments dans une v2
     */
    public function supprimer_element($type_element, $id_element) {

        $donnees = $this->initialisation_donnees($type_element, $id_element);

        $context = $this->initialisation_donnees_requete('DELETE');

        $retour = file_get_contents(fonctionnalite('mfiles_url') . '/REST/objects/'. $donnees['id_type_objet_mfiles'] . '/' . $donnees['id_objet_mfiles'] . '/latest?allVersions=true',false,$context);

        $retour = json_decode($retour);

        if((isset($retour->Exception) || isset($retour->ErrorCode)) && isset($retour->Message))
            throw new Eden_exception('Erreur lors de la suppression de l\'ancienne version du document : ' . $retour->Message);

        management('bibliotheque_synchro_elements', $donnees['objet_mfiles']->id, $donnees['objet_mfiles'])->supprime();

        return $retour;
    }

    /*
     *
     * Supprime le document lié au fichier correspondant à la pièce jointe Eden dans M-Files
     *
     */
    public function supprimer_piece_jointe($id_document_mfiles) {

        $context = $this->initialisation_donnees_requete('DELETE');

        $retour = file_get_contents(fonctionnalite('mfiles_url') . '/REST/objects/0/'. $id_document_mfiles . '/latest?allVersions=true',false,$context);

        $retour = json_decode($retour);

        if((isset($retour->Exception) || isset($retour->ErrorCode)) && isset($retour->Message))
            throw new Eden_exception('Erreur lors de la suppression du fichier : ' . $retour->Message);

        return $retour;
    }

    /*
     *
     * Retourne le fichier
     *
     */
    public function telecharger_fichier($piece_jointe){

        $context = $this->initialisation_donnees_requete();

        if(empty($piece_jointe))
            throw new Eden_exception('La pièce jointe n\'a pas été trouvé dans Eden');

        //On récupère le contenu du fichier
        $retour = file_get_contents(fonctionnalite('mfiles_url') . '/REST/objects/0/' . $piece_jointe->dossier_parent . '/latest/files/' . $piece_jointe->stockage_externe_id . '/content',false,$context);

        return response()->streamDownload(function () use ($retour) {
            echo $retour;
        }, $piece_jointe->nom);
    }

    /*
     *
     * Initialise un nouvel objet Element_piece_jointe et ses données, puis le sauvegarde.
     *
     */
    private function sauvegarde_fichier_eden($type_element, $id_element, $modele, $document = false) {

        $fichier = new Element_piece_jointe();
        $fichier->nom = $modele->Files[0]->Name . '.' . $modele->Files[0]->Extension;
        $fichier->titre = $modele->Title;
        $fichier->type_element = $type_element;
        $fichier->element_id = $id_element;
        $fichier->stockage_externe = 2;
        $fichier->dossier_parent = $modele->DisplayID;
        $fichier->stockage_externe_id = $modele->Files[0]->ID;
        $fichier->document_commercial = $document;

        $fichier->save();

        return $fichier;
    }

    /*
     *
     * Vérifie si le type_element fait partie des types synchronisés et si la synchro est activée.
     * Utilisée lors du chargement des pièces jointes pour savoir si on utilise la gestion classique ou M-Files
     *
     */
    public function verifier_synchronisation_mfiles($type_element){

        $verification_mappage_type_element = modele('parametrage_mappage_mfiles')->where('table_libre', $type_element)->first();

        return !empty($verification_mappage_type_element) && fonctionnalite('mfiles_utiliser_synchronisation') && fonctionnalite('mfiles_jeton_authentification');
    }
}
