<?php

namespace App\Eden\Models\Query_builder;

use Illuminate\Database\Eloquent\Builder;

class Eden_query_builder extends Builder {
	
    /**
	 * 
	 * Ajoute une méthode pour chercher un champ qui peut être 0 ou null
	 * 
	 */
	public function zero_ou_null($champ) {
        
		$this->where(function($requete) use ($champ) {
			
			$requete->where($champ, 0);
			$requete->orWhereNull($champ);
		});
		
		return $this;
    }
	
	/**	
	 * 	
	 * Ajoute une méthode pour chercher un champ qui peut être vide ou null	
	 * 	
	 */	
	public function vide_ou_null($champ) {	

		$this->where(function($requete) use ($champ) {	

			$requete->where($champ, '');	
			$requete->orWhereNull($champ);	
		});	

		return $this;	
    }

    public function get($columns = ['*']){

        $builder = $this->applyScopes();

        if(!editeur() && session()->has('cache.droits_licences.2')) {

            $table = $builder->getModel()->getTable();

            $types_elements = session()->get('cache.droits_licences.2');

            if(!in_array('tous',$types_elements)) {

                $table_libre = table_libre($table);

                if (!in_array($table, $types_elements) && empty($table_libre->table_systeme))
                    return $builder->getModel()->newCollection();
            }
        }

        return parent::get($columns);
    }

}