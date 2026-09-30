<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Elements\Element_management;
use App\Eden\Managements\Elements\Utilisateur_management;

class Email_recus_management extends Element_management {
	
	/**
	 * 
	 * Affiche les pièces jointes de l'email sur la liste libre
	 * 
	 */
	public function colonne_affichage_pieces_jointes($modele) {
		
		return $this->affichage_pieces_jointes($modele);
	}

	/**
	 * 
	 * 
	 * On retourne les pièces jointes
	 * 
	 */
	public function affichage_pieces_jointes($modele) {

		$chaine = '' ;

        $pieces_jointes = json_decode($modele->pieces_jointes) ;

		if(!is_array($pieces_jointes) || empty($pieces_jointes))
			return '';

        if($modele->pieces_jointes_charges != 1)
            return '<span class="css_pointer" onClick="vue_instance.$refs.liste_libre_'.$this->liste_id.'.charger_pieces_jointes('.$modele->id.')">
					<i class="fas fa-sync"></i>
					'.traduction('interface.email_recus.charger_pieces_jointes').'
				</span>';

		$compteur_piece_jointe = 1;



		foreach ($pieces_jointes as $piece_jointe) {
		    if($compteur_piece_jointe)
			$chaine .= ($chaine != '' ? '<br>' : '').'<a href="storage/email_recus/'.$piece_jointe->fichier.'" target="_blank"><i class="fa fa-fw fa-download"></i>'.$piece_jointe->nom.'</a>';
		}

		return $chaine;
	}

    /**
     *
     * Affiche les destinataires
     *
     */
    public function colonne_affichage_destinataires($modele) {

        $chaine = '<span style="font-size:12px">' ;

        $tableu_destinatiare = array();
        $from = $modele->from;
        if($from != '') {
            $tableu_destinatiare['from'] = array(
                'nom' => 'De',
                'destinataires' => explode(',', $from)
            );
        }
        $to = $modele->to ;
        if($to != '') {
            $tableu_destinatiare['to'] = array(
                'nom' => 'À',
                'destinataires' => explode(',', $to)
            );
        }
        $cc = $modele->cc ;
        if($cc != '') {
            $tableu_destinatiare['cc'] = array(
                'nom' => 'Copie',
                'destinataires' => explode(',', $cc)
            );
        }

        foreach($tableu_destinatiare as $index=>$valeur){

            $chaine.= "<b>".$valeur['nom']." : </b>";

            $compteur_limite_affichage = 1 ;

            foreach($valeur['destinataires'] as $destinataire){

                if($compteur_limite_affichage == 3 ){

                    $chaine .= '<br><span class="badge" id="badge_afficher_destinataire_'.$index.'_email_'.$modele->id.'"
                        onclick="document.getElementById(\'affichage_plus_destinataire_'.$index.'_email_'.$modele->id.'\').style.display= \'\';
                        event.target.style.display= \'none\';">...</span>';

                    $chaine .= "<div id='affichage_plus_destinataire_".$index."_email_".$modele->id."' style='display:none'>";
                    $chaine.= $destinataire.";";
                }
                elseif($compteur_limite_affichage == 1){

                    $chaine.= $destinataire.";";
                }
                else{

                    $chaine.= "<br>".$destinataire.";";

                }

                $compteur_limite_affichage++;
            }

            if($compteur_limite_affichage >3){
                $chaine .= '<br><span class="badge"
                        onclick="document.getElementById(\'affichage_plus_destinataire_'.$index.'_email_'.$modele->id.'\').style.display= \'none\';
                        document.getElementById(\'badge_afficher_destinataire_'.$index.'_email_'.$modele->id.'\').style.display= \'\';">X</span>';
                $chaine.="</div>";
            }

            $chaine.= "<br>";

        }

        $chaine.= "</span>";


        return $chaine;
    }

    /**
     *
     * Affiche le texte
     *
     */
    public function colonne_affichage_texte($modele) {

        $chaine = "<div style=' overflow: hidden;
   text-overflow: ellipsis;
   display: -webkit-box;
   -webkit-line-clamp: 4;
   -webkit-box-orient: vertical;word-wrap: break-word;
    width: 400px;'>".$modele->texte."</div>";

        return $chaine;
    }

    /**
     *
     * Affiche le sujet
     *
     */
    public function colonne_affichage_sujet($modele) {

        $chaine = "<div style=' word-wrap: break-word;overflow: hidden;
    width: 100px;'>".$modele->sujet."</div>";

        return $chaine;
    }
}