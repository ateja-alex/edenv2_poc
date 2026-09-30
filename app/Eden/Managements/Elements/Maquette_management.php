<?php

namespace App\Eden\Managements\Elements;

class Maquette_management extends Element_management {

    /**
	 *
	 * @cf Element_management::enregistre()
	 *
	 * A l'enregistrement d'une maquette on vérifie si elle est définie comme par défaut, si oui on vérifie si une autre maquette est déjà par défaut
	 *
	 */
	public function enregistre($modifications = array(), $modele = false) {
    
        if(isset($modifications['par_defaut']) && $modifications['par_defaut'] == 1){

            $ancien_par_defaut = modele('maquette')->where('par_defaut',1)->first();

            if($ancien_par_defaut != null && ($this->existe() && $this->modele->id != $ancien_par_defaut->id))
                management('maquette',$ancien_par_defaut->id, $ancien_par_defaut)->enregistre(['par_defaut' => 0]);
        }

        return parent::enregistre($modifications, $modele);
    }
}