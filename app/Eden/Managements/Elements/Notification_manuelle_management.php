<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Champ_libre;
use App\Eden\Models\Table_libre;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class Notification_manuelle_management extends Element_management {

    /**
     * @return bool
     *
     * Génére les lignes de notification_manuelle_element pour ensuite gérer l'envoi des notifications
     *
     */
    public function genere_donnees_notification_manuelle_element(){

        if(empty($this->modele))
            return false;

        $type_element = Table_libre::where('id',$this->modele->type_element_id)->first()->type_element;

        // Récupération des lignes déjà présentes
        $element_ids_deja_inclus = modele('notification_manuelle_element')
            ->where('notification_manuelle_id',$this->modele->id)->get()->pluck('element_id')->toArray();

        $elements_concernes_non_crees = [];

        // Gestion de l'envoi de notifications à la création d'un élément
        if($this->modele->condition == 1) {

            $elements_concernes_non_crees = modele($type_element)
                ->whereNotIn('id', $element_ids_deja_inclus)
                ->where('cree_le', '>', $this->modele->cree_le)
                ->get()->pluck('id')->toArray();

        }

        // Gestion de l'envoi de notifications à la modification d'un élément
        else if($this->modele->condition == 2) {

            $elements_concernes_non_crees = modele($type_element)
                ->whereNotIn('id', $element_ids_deja_inclus)
                ->where('modifie_le', '>', $this->modele->cree_le)
                ->get()->pluck('id')->toArray();

            $elements_concernes_deja_crees = modele($type_element)
                ->join('notification_manuelle_element','notification_manuelle_element.element_id',$type_element.'.id')
                ->whereIn($type_element.'.id', $element_ids_deja_inclus)
                ->where('notification_manuelle_id',$this->modele->id)
                ->where('notification_manuelle_element.envoyee','!=',0)
                ->where($type_element.'.modifie_le', '>', 'notification_manuelle_element.modifie_le')
                ->get()->pluck('notification_manuelle_element.id')->toArray();

            foreach($elements_concernes_deja_crees as $notification_manuelle_element_id){

                $donnees = array(
                    'envoyee' => 0,
                );

                management('notification_manuelle_element',$notification_manuelle_element_id)->enregistre($donnees);
            }
        }

        // Gestion de l'envoi de notifications selon une condition indiqué
        else if($this->modele->condition == 3){

             $elements_concernes_non_crees = modele($type_element)
                ->whereRaw($this->modele->condition_sql)
                ->where('modifie_le', '>', $this->modele->modifie_le)
                ->whereNotIn('id', $element_ids_deja_inclus)
                ->get()->pluck('id')->toArray();

             $elements_concernes = modele($type_element)
                ->whereRaw($this->modele->condition_sql)
                ->get()->pluck('id')->toArray();

             //On supprime les lignes qui ne sont plus concernés par les notifications
             $elements_plus_concernes = modele('notification_manuelle_element')
                 ->whereNotIn('element_id',$elements_concernes)
                 ->where('notification_manuelle_id',$this->modele->id)
                 ->get()->pluck('id')->toArray();

             foreach($elements_plus_concernes as $notification_manuelle_element_id){

                management('notification_manuelle_element',$notification_manuelle_element_id)->supprime();
             }
        }

        foreach($elements_concernes_non_crees as $element_concerne_id){

            $donnees = array(
                'element_id' => $element_concerne_id,
                'notification_manuelle_id' => $this->modele->id,
                'envoyee' => 0,
            );

            management('notification_manuelle_element')->enregistre($donnees);

        }

        return true;

    }

    public function envoie_des_notifications(){
        $notifications_manuelles_elements = modele('notification_manuelle_element')
            ->where('envoyee',0)
            ->where('notification_manuelle_id',$this->modele->id)
            ->where(function($requete)  {
                // jamais essayé (aucune erreur) ou délai de retentative écoulé
                $requete->whereNull('erreur_prochain_essai')
                    ->orWhere(function($sous_requete) {
                        $sous_requete->where('erreur_prochain_essai', '<=', date('Y-m-d H:i:s'))
                        ->where('erreur_nombre_essai', '<', 20);
                    });
            })
            ->get();

        $type_element = Table_libre::where('id',$this->modele->type_element_id)->first()->type_element;

        $service_publipostage = service('publipostage');

        foreach($notifications_manuelles_elements as $notification_manuelle_element){

            $management_origine = management($type_element,$notification_manuelle_element->element_id);

            if($management_origine == null)
                continue;

            $management_notification_element = management('notification_manuelle_element',$notification_manuelle_element->id,$notification_manuelle_element);

            $management_origine->charge_valeurs_champs_multiselection();

            // notification ERP
            if($this->modele->type_notification == 1) {

                $message_notification = $service_publipostage->publipostage_texte($this->modele->message_notification, $type_element, [$notification_manuelle_element->element_id]);

                $destinataires_erp = $management_notification_element->destinataires('erp');

                // Aucun destinataire
                if(empty($destinataires_erp)) {
                    management('notification_manuelle_element', $notification_manuelle_element->id)->enregistre(
                        $this->donnees_erreur($notification_manuelle_element, "#erreur_01#")
                    );

                    continue;
                }

                foreach($destinataires_erp as $destinataire_id) {

                    $notification = management('notification');

                    $infos = array(

                        'date' => date('Y-m-d H:i:s'),
                        'utilisateur_id' => $destinataire_id,
                        'zone' => 'navbar_notifications',
                        'contenu_html' => $message_notification,
                    );

                    $notification->enregistre($infos);
                }
            }
            // notification MAIL
            elseif($this->modele->type_notification == 2) {

                $sujet = $service_publipostage->publipostage_texte($this->modele->sujet, $type_element, [$notification_manuelle_element->element_id]);
                $contenu_email = $service_publipostage->publipostage_texte($this->modele->contenu_email, $type_element, [$notification_manuelle_element->element_id]);

				$modele_email_id = $this->modele->modele_email;
				$modele_email = modele('modele_email',$modele_email_id);

                $destinataires_par_types = $management_notification_element->destinataires('email');

                // Aucun destinataire
                if(empty($destinataires_par_types[1])) {
                    management('notification_manuelle_element', $notification_manuelle_element->id)->enregistre(
                        $this->donnees_erreur($notification_manuelle_element, "#erreur_01#")
                    );

                    continue;
                }

                if (fonctionnalite('enregistrer_systematique_email_en_echange') === true || ((!empty($modele_email) && $modele_email->enregistrer_echange == 1))) {

					$echange = service('email')->prepare_tableau_donnees_echange($type_element, $management_origine->modele->id);

                    $echange['description'] = strip_tags(str_replace('<br/>', "\n", $contenu_email));

                    $destinataires_mail = "";

                    foreach($destinataires_par_types as $type => $destinataires_par_type){
                        if(!empty($destinataires_par_type)){
                            $affichage_destinataires = implode(', ',$destinataires_par_type);

                            $destinataires_mail .= management('parametrage_destinataire_email')->champ('type')->affiche($type)." : ".$affichage_destinataires."\n";
                        }
                    }

                    $echange['destinataires_email'] = $destinataires_mail;

                    $echange_management = management('echange');
                    $echange_management->enregistre($echange);
                }

                try {
                    $to = $destinataires_par_types[1] ?? [];
                    $cc = $destinataires_par_types[2] ?? [];
                    $bcc = $destinataires_par_types[3] ?? [];

                    $template_email = "template_standard";

                    if (!empty($modele_email) && $modele_email->utilisation_template_vide == 1)
                        $template_email = 'template_vide';

                    $pieces_jointes = $management_notification_element->pieces_jointes_mail($type_element,$management_origine);

                    // on prépare les données pour envoyer le mail
                    $service_email = service('email');

                    $compte_email_id = $this->compte_email($type_element, $management_origine);

                    if(empty($compte_email_id)){
                        management('notification_manuelle_element',$notification_manuelle_element->id)->enregistre(
                            $this->donnees_erreur($notification_manuelle_element, "#erreur_02#")
                        );

                        continue;
                    }

                    $parametres_email = [
                        'type_configuration' => 2,
                        'id_compte_email' => $compte_email_id,
                        'cc' => $cc,
                        'bcc' => $bcc,
                        'pieces_jointes' => $pieces_jointes,
                        'destinataire' => $to,
                        'sujet' => $sujet,
                    ];

                    $variables_email = [
                        'contenu_email' => $contenu_email,
                        'titre' => $sujet
                    ];

                    $retour = $service_email->envoyer('eden::mails.'.$template_email, $variables_email, $parametres_email);

                    if($retour !== true) {

                        management('notification_manuelle_element',$notification_manuelle_element->id)->enregistre(
                            $this->donnees_erreur($notification_manuelle_element, "#erreur_03# => ".$retour)
                        );

                        continue;
                    }
                }
                catch(Exception $e){

                    management('notification_manuelle_element',$notification_manuelle_element->id)->enregistre(
                        $this->donnees_erreur($notification_manuelle_element, "#erreur_03# => ".$e)
                    );

                    continue;
                }
            }

            management('notification_manuelle_element',$notification_manuelle_element->id)->enregistre(
                array(
                    'envoyee' => 1,
                    'erreur_envoi' => null
                )
            );

        }

        return true;
    }

    /**
     * Construit les données d'erreur à enregistrer pour un élément de notification.
     * Incrémente le compteur d'essais et calcule la date du prochain essai avec un
     * délai exponentiel : 2^erreur_nombre_essai secondes ajoutées à maintenant.
     */
    private function donnees_erreur($notification_manuelle_element, $message_erreur){

        $nombre_essai = (int) ($notification_manuelle_element->erreur_nombre_essai ?? 0) + 1;

        $delai_secondes = pow(2, $nombre_essai);
        $prochain_essai = date('Y-m-d H:i:s', time() + $delai_secondes);

        return array(
            'erreur_envoi' => $message_erreur,
            'erreur_nombre_essai' => $nombre_essai,
            'erreur_prochain_essai' => $prochain_essai,
        );
    }

    public function compte_email($type_element, $management_origine){

        if($this->modele->type_expediteur == 'champ' && !empty($this->modele->lien_champ_expediteur)){
            
            $valeur = service('lien_champ')->valeurs($this->modele->lien_champ_expediteur, [
                'type_element' => $type_element,
                'elements_ids' => [$management_origine->modele->id],
            ])[0] ?? null;

            if($valeur['modele_champ_libre']->type_element_ajax == 'utilisateur'){
                $compte_email_id = modele('compte_email')
                    ->where('utilisateur_id',$valeur['valeur'])
                    ->orderBy('par_defaut','DESC')
                    ->first()->id ?? null;
            }
            else
                $compte_email_id = $valeur['valeur'];
        }
        elseif($this->modele->type_expediteur == 'utilisateur_expediteur_id'){
            $compte_email_id = modele('compte_email')
                ->where('utilisateur_id',$this->modele->utilisateur_expediteur_id)
                ->orderBy('par_defaut','DESC')
                ->first()->id ?? null;
        }
        else
            $compte_email_id = $this->modele->compte_email_id;

        return $compte_email_id;
    }
}
