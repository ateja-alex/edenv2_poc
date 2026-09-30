<?php

namespace App\Eden\Managements\Elements;


class Bordereau_management extends Element_management {
	
	/**
	*
	* On calcule le nouveau montant du bordereau
	*
	*/
	public function calcul_somme_montant_bordereau() {

	    $nouveau_montant = round(modele('paiement')
            ->where('bordereau_id',$this->modele->id)
            ->sum('paiement.montant'),2);

	    $nombre_de_cheque = modele('paiement')
            ->where('bordereau_id',$this->modele->id)
            ->count();

        $this->enregistre_modele(['montant' =>  $nouveau_montant,'nombre_de_cheques' => $nombre_de_cheque]);
		
	}

    /**
     *
     * On vérifie si le bordereau ne contient pas de paiement
     *
     */
    public function supprime($modele = false) {

        $first = modele('paiement')->where('bordereau_id', $this->modele->id)->first();

        if($first !== null)
            return traduction('messages.php.bordereau.suppresion_impossible_paiement');

        return parent::supprime($modele);
    }

	
}
