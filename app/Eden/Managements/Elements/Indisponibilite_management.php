<?php

namespace App\Eden\Managements\Elements;

class Indisponibilite_management extends Element_management {

    /**
	 *
	 * On vérifie qu'il y pas d'indisponibilité sur cette plage horaire d'activé
	 *
	 */
	public function enregistre($modifications = array(), $modele = false) {

        if((!empty($modifications['date_debut']) || !empty($this->modele->date_debut))
            && (!empty($modifications['date_fin']) || !empty($this->modele->date_fin))
            && (!empty($modifications['type_element']) || !empty($this->modele->type_element))
            && (!empty($modifications['element_id']) || !empty($this->modele->element_id))){

            $date_debut = !empty($modifications['date_debut']) ? $modifications['date_debut'] : $this->modele->date_debut;

            if(strpos($date_debut,'/') !== false)
                $date_debut = \DateTime::createFromFormat('d/m/Y H:i:s',$date_debut);
            else
                $date_debut = \DateTime::createFromFormat('Y-m-d H:i:s',$date_debut);

            $date_fin = !empty($modifications['date_fin']) ? $modifications['date_fin'] : $this->modele->date_fin;

            if(strpos($date_fin,'/') !== false)
                $date_fin = \DateTime::createFromFormat('d/m/Y H:i:s',$date_fin);
            else
                $date_fin = \DateTime::createFromFormat('Y-m-d H:i:s',$date_fin);

            if($date_debut >= $date_fin)
                return traduction('messages.php.indisponibilite.erreur_dates');

            $type_element = !empty($modifications['type_element']) ? $modifications['type_element'] : $this->modele->type_element;
            $element_id = !empty($modifications['element_id']) ? $modifications['element_id'] : $this->modele->element_id;

            $indisponibilite = modele('indisponibilite')->where('type_element',$type_element)
                ->where('element_id', $element_id)
                ->where('date_debut','<=',$date_fin->format('Y-m-d H:i:s'))
                ->where('date_fin','>=',$date_debut->format('Y-m-d H:i:s'));

            if(!empty($this->modele->id))
                $indisponibilite->where('id','!=',$this->modele->id);

            $indisponibilite = $indisponibilite->first();

            if($indisponibilite !== null)
                return traduction('messages.php.indisponibilite.indisponibilite_deja_presente');
        }

        return parent::enregistre($modifications,$modele);
    }

	/**
	 *
	 * On recharge les chaines affichages si nécessaire
	 *
	 */
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {

		parent::methodes_post_modification($modele, $modele_avant, $modifications);

        $chaine_affichage_a_regenerer = [];

        if($modele_avant->exists !== false) {
            if ($modele_avant->type_element != $modele->type_element || $modele_avant->element_id != $modele->element_id)
                $chaine_affichage_a_regenerer = array($modele_avant,$modele);
            else if ($modele_avant->date_debut != $modele->date_debut || $modele_avant->date_fin != $modele->date_fin)
                $chaine_affichage_a_regenerer = array($modele);
        }
        else
            $chaine_affichage_a_regenerer = array($modele);

        foreach($chaine_affichage_a_regenerer as $modele_a_regenerer){

            $element = modele($modele_a_regenerer->type_element, $modele_a_regenerer->element_id);

            if($element !== null) {

                $element->chaine_affichage = null;
                $element->save();
            }
        }

	}

    /**
     *
     * On supprime dans la chaîne d'affichage l'indisponibilité
     *
     * @return void
     *
     */
    protected function methodes_post_suppression($modele)
    {
        parent::methodes_post_suppression($modele);

        $element = modele($modele->type_element, $modele->element_id);

        if($element !== null) {

            $element->chaine_affichage = null;
            $element->save();
        }
    }
}