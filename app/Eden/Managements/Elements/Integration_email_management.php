<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Element_piece_jointe;
use Storage;

class Integration_email_management extends Element_management {

    private $correspondances = false;

    public function synchronisation_mail(){

        $comptes_emails = modele('integration_email_comptes_emails')
            ->select('compte_email.id as compte_email_id','compte_email.type_de_compte','boite_mail','boite_deplacement_mail','utilisateur.email as utilisateur_email','configuration_email.*')
            ->join('compte_email','compte_email.id','integration_email_comptes_emails.valeur')
            ->leftJoin('utilisateur','utilisateur.id','compte_email.utilisateur_id')
            ->leftJoin('configuration_email','configuration_email.id','compte_email.configuration_email')
            ->where('cle_locale',$this->modele->id)->get();

        $correspondances = $this->getCorrespondances();

        $elements = [];

        $champs_libres = [
            'classique' => [],
            'reponse' => []
        ];

        foreach($correspondances as $correspondance){

            $champ_libre = champ_libre_modele($correspondance->mail_reponse == 1 ? $this->modele->table_gestion_reponse:
                $this->modele->type_element, $correspondance->champ_eden);

            if(!empty($champ_libre)) {

                if($champ_libre->type == 42 && !empty($correspondance->champ_correspondance_type_42) && !isset($elements[$champ_libre->type_element_ajax]))
                    $elements[$champ_libre->type_element_ajax] = modele($champ_libre->type_element_ajax)->get();

                $champs_libres[$correspondance->mail_reponse == 1 ? 'reponse' : 'classique'][$champ_libre->nom_sql] = $champ_libre;
            }
        }

        list($elements_existants,$mails_ids) = $this->elements_existants();

        foreach($comptes_emails as $compte_email){

            if($compte_email->type_de_compte == 1)
                $emails = $this->emails_microsoft($mails_ids,$compte_email);
            else
                $emails = $this->emails($mails_ids,$compte_email);

            foreach($emails as $email) {

                $email['compte_email_id'] = $compte_email->compte_email_id;

                $contenu = $email['contenu'];

                $id_element_parent = null;

                if(!empty($this->modele->balise_gestion_reponse) && !empty($elements_existants[$this->modele->type_element]['elements'])){

                    preg_match_all('/#(.*?)#/', $this->modele->balise_gestion_reponse, $matches);

                    $elements_a_remplacer = $matches[1] ?? [];

                    foreach($elements_existants[$this->modele->type_element]['elements'] as $element_existant){

                        $balise = $this->modele->balise_gestion_reponse;

                        foreach($elements_a_remplacer as $element_a_remplacer){

                            $balise = str_replace('#'.$element_a_remplacer.'#', $element_existant->{$element_a_remplacer}, $balise);
                        }

                        if (preg_match('/'.$balise.'/', $contenu, $match) == 1) {
                            $id_element_parent = $element_existant->id;
                            break;
                        }
                    }
                }

                $type_element = $id_element_parent == null ? $this->modele->type_element : $this->modele->table_gestion_reponse;

                $champs = $correspondances->where('mail_reponse',$id_element_parent == null ? null : 1);

                $modifications = [];

                if(!empty($id_element_parent))
                    $modifications[$this->modele->champ_gestion_reponse] = $id_element_parent;

                $email['contenu'] = $this->supprimer_apercu_mail_reponse($email['contenu']);

                foreach($champs as $champ){

                    $valeur = $champ['valeur'];

                    if($valeur == '#pieces_jointes#') {

                        if(in_array($champ->champ_eden,['bloc_piece_jointe','bloc_piece_jointe_parent'])) {

                            if(empty($pjs_blocs_pieces_jointes[$champ->champ_eden]))
                                $pjs_blocs_pieces_jointes[$champ->champ_eden] = [];

                            $pjs_blocs_pieces_jointes[$champ->champ_eden] = $email['pieces_jointes'];

                            continue;
                        }
                        else
                            $valeur = json_encode(array_map(function($piece_jointe) use ($type_element){

                                $nouveau_localisation = str_replace('integration_email/',$type_element.'/', $piece_jointe['fichier']);
                                Storage::move('public/'.$piece_jointe['fichier'], 'public/'.$nouveau_localisation);
                                return [
                                    'chemin' => $nouveau_localisation,
                                    'nom_original' => $piece_jointe['nom'],
                                ];
                            },$email['pieces_jointes']));
                    }
                    else {

                        $champ_libre_modele = $champs_libres[$champ->mail_reponse == 1 ? 'reponse' : 'classique'][$champ['champ_eden']];

                        if (preg_match_all('/#(.*?)#/', $valeur, $match) == 1) {

                            foreach ($match[1] as $champ_email) {

                                if ($champ_email == 'pieces_jointes')
                                    continue;

                                $valeur_email = $email[$champ_email];

                                if(in_array($champ_email,['from','to','cc','bcc']))
                                    $valeur_email = json_encode($valeur_email);

                                $valeur = str_replace('#' . $champ_email . '#', $valeur_email, $valeur);
                            }

                            if ($champ_libre_modele->type == 42 && !empty($champ->champ_correspondance_type_42)) {
                                $element_correspondant = $elements[$champ_libre_modele->type_element_ajax]
                                    ->where($champ->champ_correspondance_type_42, $valeur)->first();

                                $valeur = !empty($element_correspondant) ? $element_correspondant->id : null;
                            }
                        }
                    }

                    $modifications[$champ->champ_eden] = $valeur;
                }

                $element = management($type_element);

                $element->enregistre($modifications);

                if($compte_email->type_de_compte == 1 && !empty($compte_email->boite_deplacement_mail)) {

                    $retour = service('microsoft_email')->deplacement_mail_dossier($compte_email->utilisateur_email, $email['message_id'], $compte_email->boite_deplacement_mail);

                    if($retour['retour'] === true){

                        $champ_message_id = $champs->where('valeur','#message_id#')->first()->champ_eden ?? null;

                        $element->enregistre_modele(array(
                            $champ_message_id => $retour['id_nouveau_mail']
                        ));
                    }
                }

                if(!empty($pjs_blocs_pieces_jointes)){

                    foreach($pjs_blocs_pieces_jointes as $champ_eden => $pieces_jointes){

                        foreach($pieces_jointes as $piece_jointe){
                            $fichier = new Element_piece_jointe();
                            $fichier->nom = $piece_jointe['nom'];
                            $fichier->titre = $piece_jointe['nom'];
                            $fichier->type_element = $champ_eden == 'bloc_piece_jointe' && !empty($id_element_parent) ? $this->modele->table_gestion_reponse:
                                $this->modele->type_element;
                            $fichier->element_id =  $champ_eden == 'bloc_piece_jointe_parent' && !empty($id_element_parent) ?
                                $id_element_parent : $element->modele->id;

                            $nouveau_localisation = str_replace('integration_email/',$fichier->type_element.'/', $piece_jointe['fichier']);
                            Storage::move('public/'.$piece_jointe['fichier'], 'public/'.$nouveau_localisation);

                            $fichier->chemin = $nouveau_localisation;

                            if(!empty(moi())) {
                                $fichier->type_element_createur = 'utilisateur';
                                $fichier->element_id_createur = moi()->id;
                            }

                            $fichier->save();
                        }
                    }
                }
            }
        }

        Storage::deleteDirectory('public/integration_email');

        return true;
    }

    public function getCorrespondances(){

        if($this->correspondances !== false)
            return $this->correspondances;

        $this->correspondances = modele('integration_email_correspondance')
            ->where('integration_email_id',$this->modele->id)->get();

        return $this->correspondances;
    }

    public function elements_existants(){

        $correspondances = $this->getCorrespondances();

        $correspondances_mails_id = $correspondances->where('valeur','#message_id#');

        $elements = [];
        $mails_ids = [];

        foreach($correspondances_mails_id as $correspondance_mail_id){

            $type_element = $correspondance_mail_id->mail_reponse == 1 ? $this->modele->table_gestion_reponse :
                $this->modele->type_element;

            $elements[$type_element] = [];

            $elements[$type_element]['champ_eden'] = $correspondance_mail_id->champ_eden;

            $modeles = modele($type_element)->whereNotNull($correspondance_mail_id->champ_eden)->get();

            $elements[$type_element]['elements'] = $modeles;

            $mails_ids = array_merge($mails_ids,$modeles->pluck($correspondance_mail_id->champ_eden)->toArray());
        }

        return [$elements,$mails_ids];
    }

    public function emails_microsoft($mails_ids,$compte_email){

        $emails = service('microsoft_email')->emails($compte_email->utilisateur_email,'integration_email',$compte_email->boite_mail);

        if(empty($emails))
            return array();

        $emails = service('microsoft_email')->formate_mails($emails,$mails_ids);

        return $emails;
    }

     public function emails($mails_ids,$compte_email){

        if(empty($compte_email->id))
            return array();

        $management_configuration_email = management('configuration_email',$compte_email->id,$compte_email);

        $emails = service('email')->recupere_mails(
            $compte_email->login,
            $compte_email->mot_de_passe_par_defaut,
            $compte_email->adresse_host,
            false,
            array(
                'protocol' => $management_configuration_email->champ('protocole')->affiche(),
                'port' => $compte_email->port,
                'encryption' => $compte_email->cryptage_utilise
            )
        );

        if(empty($emails))
            return array();

        $emails = service('email')->formate_mails($emails,$mails_ids,'integration_email');

        return $emails;
    }

    /*
     *
     * Permet de supprimer le mail auquel on a répondu qui apparaît à la fin de l'échange s'il existe
     *
     */
    private function supprimer_apercu_mail_reponse($contenu){

        // Pour Microsoft, on fait une exception car ils modifient les classes des balises HTML, donc on se fie à la div qu'ils ajoutent, sinon on utilise la balise ajoutée dans le mail
        if (strpos($contenu, '<div id="appendonsend"></div>') !== false) {

            $contenu_tmp = explode('<div id="appendonsend"></div>', $contenu);

            $contenu = $contenu_tmp[0];
        }
        else if (strpos($contenu, '<div class="separation_mail"></div>') !== false) {

            $contenu_tmp = explode('<div class="separation_mail"></div>', $contenu);

            $contenu = $contenu_tmp[0];
        }

        return $contenu;
    }
}