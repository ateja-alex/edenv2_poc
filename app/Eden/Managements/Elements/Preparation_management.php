<?php

namespace App\Eden\Managements\Elements;

class Preparation_management extends Element_management {


	/**
	 * 
	 * 
	 * Retourne les préparations par date selon un document_id
	 * 
	 * 
	 */
	public function retourne_preparations($document_id) {
        
        $preparation_trie_par_date = collect();

        $preparations =  modele('preparation')->where('document_id', $document_id)->get()->sortBy('date');

        foreach($preparations as $preparation) 
            $preparation_trie_par_date->push($preparation);
        

        return $preparation_trie_par_date;
    }
	
}
