<?php

namespace App\Eden\Managements\Elements;

class Bon_preparation_vente_lignes_management extends Document_lignes_management {

    /**
     *
     * Pour les bons de préparation au moment où l'on met à jour le transforme et reliquat il faut mettre à jour la commande parent
     *
     */
    public function mise_a_jour_lignes_document_origine(){

        $mise_a_jour = parent::mise_a_jour_lignes_document_origine();

        if($mise_a_jour)
            return;

        if(empty($this->modele->type_element_source) || empty($this->modele->id_element_source) || empty($this->modele->id_ligne_source) || $this->modele->type_element_source != 'commande_vente')
			return;

        $id_element_source = $this->modele->id_element_source;

        $management_ligne_origine = $this->management_ligne_origine($id_element_source);

        if(!$management_ligne_origine->existe())
			return;

        $management_ligne_origine->management_principal = $this->management_principal ?? $this->management_entete();

        $management_ligne_origine->mise_a_jour_stocks();
    }
}