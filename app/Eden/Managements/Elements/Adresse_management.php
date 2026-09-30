<?php

namespace App\Eden\Managements\Elements;

use Illuminate\Support\Facades\Log;

class Adresse_management extends Element_management {

	/**
	 *
	 * On affiche proprement une adresse
	 *
	 */
	public function affiche() {

		$affichage = array();

		if(empty($this->modele))
			return '';

		$adresse = array();

		if(!empty($this->modele->societe)) {

			$adresse[] = $this->modele->societe;
		}

		if(!empty($this->modele->nom) || !empty($this->modele->prenom) ) {

			$adresse[] = $this->modele->prenom.' '.$this->modele->nom;
		}

		if(!empty($this->modele->adresse)) {

			$adresse[] = $this->modele->adresse;
		}

		if(!empty($this->modele->adresse_complement)) {

			$adresse[] = $this->modele->adresse_complement;
		}

		if(!empty($this->modele->code_postal)) {

			$adresse[] = $this->modele->code_postal.' '.$this->modele->ville;
		}

		return implode(',', $adresse);
	}

	/**
	 *
	 * On ajoute les données de longitude et latitude / on met à jour les tags de recherche du client lié
	 *
	 */
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {

		parent::methodes_post_modification($modele, $modele_avant, $modifications);

		$adresse_par_defaut = $modele['adresse_par_defaut'];
        $champs_adresse = ['adresse', 'adresse_complement', 'code_postal', 'ville'];
        $champs_element = [
            ['type_element' => 'client', 'champ' => 'client_id'],
            ['type_element' => 'fournisseur', 'champ' => 'fournisseur_id'],
            ['champ' => 'type_element', 'champ_secondaire' => 'element_id']
        ];

		// On vérifie si l'adresse a été modifiée avant de géocoder
		if(!empty(array_intersect_key($this->changement_enregistrement, array_flip($champs_adresse))))
			$this->maj_latitude_longitude();
		
		// On vérifie qu'il n'y ait pas d'autre adresse par défaut, sinon on les passe à 0
		if ($adresse_par_defaut == 1) {

            foreach($champs_element as $informations_champ) {

                if(empty($modele[$informations_champ['champ']]))
                    continue;

                // On supprime l'ancienne adresse par défaut si elle existe
                $adresses_par_defaut_element = modele('adresse')
                    ->where('id', '!=', $modele['id'])
                    ->where($informations_champ['champ'], $modele[$informations_champ['champ']])
                    ->where('adresse_par_defaut',1);

                if(isset($informations_champ['champ_secondaire']))
                    $adresses_par_defaut_element = $adresses_par_defaut_element
                        ->where($informations_champ['champ_secondaire'], $modele[$informations_champ['champ_secondaire']]);

                if(in_array($modele->type_adresse, array(1,2)))
                    $adresses_par_defaut_element = $adresses_par_defaut_element
                        ->where('type_adresse', $modele['type_adresse']);

                $adresses_par_defaut_element = $adresses_par_defaut_element->get();

                foreach ($adresses_par_defaut_element as $adresse) {

                    management('adresse',$adresse->id, $adresse)->enregistre(['adresse_par_defaut' => 0]);
                }

                if(!isset($informations_champ['type_element']))
                    continue;

                // On change l'adresse du client
                $management = management($informations_champ['type_element'], $modele[$informations_champ['champ']]);
                $informations_adresse = array();

                foreach($champs_adresse as $champ) {

                    if(empty($modele[$champ]) && !empty($management->modele->$champ))
                        $informations_adresse[$champ] = null;
                    else if(isset($modele[$champ]))
                        $informations_adresse[$champ] = $modele[$champ];

                }
                
                $management->enregistre($informations_adresse);
            }
		}
	}


	/**
	 *
	 * Met à jour la latitude et la longitude de l'adresse
	 *
	 */
	public function maj_latitude_longitude() {

        $cle_api = config('services_eden.cle_api_google_places');
        $adresse = array();
        $champs = ['adresse', 'adresse_complement', 'code_postal', 'ville','pays_id'];

        foreach($champs as $champ){

            $champ_en_cours = str_replace(['&', ' '], ['et', '+'], $this->champ($champ)->affiche());
            $adresse[] = $champ_en_cours;
        }

        $adresse = implode('+', $adresse);

        $data = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.$adresse.'&sensor=false&key='.$cle_api);
        $data = json_decode($data, true);

		// on sauvegarde les coordonnées de géolocalisation
		if(isset($data['results'][0]['geometry']['location'])) {

			$infos = array();
			$infos['latitude'] = $data['results'][0]['geometry']['location']['lat'];
			$infos['longitude'] = $data['results'][0]['geometry']['location']['lng'];

			$this->enregistre_modele($infos);
		}
        elseif(isset($data['status'], $data['error_message']) && $data['status'] == 'REQUEST_DENIED')
            Log::warning('Erreur lors du géocodage de l\'adresse : ' . $data['error_message']);

		return true;
	}

}
