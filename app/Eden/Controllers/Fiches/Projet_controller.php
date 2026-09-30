<?php

namespace App\Eden\Controllers\Fiches;

use App\Eden\Controllers\Fiche_controller;
use Illuminate\Http\Request;

use App\Exports\Export;

class Projet_controller extends Fiche_controller {

	public function ajoute_participant(Request $formulaire) {
		
		$projet_management = management('projet', $this->id_element);
		
		$participants = array();

		if(!empty($projet_management->modele->participants))
			$participants = json_decode(base64_decode($projet_management->modele->participants), true);
		
		if(empty($participants))
			$participants = array();
		
		$participants[] = array(
			
			'nom' => $formulaire->nouveau_participant['nom'],
			'prenom' => $formulaire->nouveau_participant['prenom'],
			'date_naissance' => $formulaire->nouveau_participant['date_naissance'],
			'nationalite' => $formulaire->nouveau_participant['nationalite'],
			'commentaires' => $formulaire->nouveau_participant['commentaires'],
		);
		
		$projet_management->enregistre(array('participants' => base64_encode(json_encode($participants))));
		
		return response()->json($participants);
	}

	public function modifie_participant(Request $formulaire) {
		
		$projet_management = management('projet', $this->id_element);
		
		$participants = array();
		
		$participants = json_decode(base64_decode($projet_management->modele->participants), true);
		
		
		$participants[$formulaire->participant_id] = array(
			
			'nom' => $formulaire->participant['nom'],
			'prenom' => $formulaire->participant['prenom'],
			'date_naissance' => $formulaire->participant['date_naissance'],
			'nationalite' => $formulaire->participant['nationalite'],
			'commentaires' => $formulaire->participant['commentaires'],
		);
		
		$projet_management->enregistre(array('participants' => base64_encode(json_encode($participants))));
		
		return response()->json($participants);
	}

	public function supprimer_participant(Request $formulaire) {
		$projet_management = management('projet', $this->id_element);
		
		$participants = array();
		
		$participants = json_decode(base64_decode($projet_management->modele->participants), true);

		array_splice($participants, $formulaire->participant_id, 1);

		$projet_management->enregistre(array('participants' => base64_encode(json_encode($participants))));
		
		return response()->json($participants);
	}

	public function change_statut($type_element, $element_id, $id_statut) {

		$projet = management('projet', $element_id);
		
		$projet->enregistre(array('statut' => $id_statut));

		return redirect()->back();
	}

	public function consommation_de_stock($type_element, $id_element) {
		
		return response()->json(fiche('projet', $id_element)->consommation_de_stock());
	}

	
    public function enregistrer_ligne_categorie($formulaire, $type_element, $id){

        $test_temps_categorie = modele('categorie_projet_ligne')
                                ->where('projet_id', $id)
                                ->where('categorie_id',$formulaire->categorie_id)
                                ->first();

        if($test_temps_categorie !== null)
            $management = management('categorie_projet_ligne', $test_temps_categorie['id']);
        else
            $management = management('categorie_projet_ligne');

        return response()->json(['retour' => $management->enregistre($formulaire->all())]);

    }
}
