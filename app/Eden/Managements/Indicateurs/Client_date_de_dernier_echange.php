<?php

namespace App\Eden\Managements\Indicateurs;

class Client_date_de_dernier_echange extends Indicateur {

	/**
	 *
	 * On va calculer la date du dernier échange du client
	 *
	 */
	public function calcule($management) {

		if(in_array($management->_type_element, array('echange')) && $management->modele->type_element == "client")
			$management = management('client', $management->modele->element_id);
		else if(in_array($management->_type_element, array('echange')))
            $management = null;

        if($management !== null && $management->_type_element == "client") {

            $dernier_echange = modele('echange')->where('type_element','client')->where('element_id', $management->modele->id)->orderBy('date', 'DESC')->first();

            if ($dernier_echange !== null)
                $management->enregistre_modele(array('date_de_dernier_echange' => $dernier_echange->date));
            else
                $management->enregistre_modele(array('date_de_dernier_echange' => '0000-00-00'));
        }
		
		return true;
	}

	/**
	 *
	 * On va calculer le CA de tous les clients
	 *
	 */
	public function calcule_pour_tous() {

        $requete = \DB::select(\DB::raw("UPDATE client SET date_de_dernier_echange =
           (select max(date) from echange
           where (inactif is null or inactif = 0) and echange.type_element='client'
             and echange.element_id= client.id) WHERE (inactif is null or inactif = 0)"));
		
		return true;
	}

	
}
