<?php

namespace App\Eden\Managements\Elements;

class Note_de_frais_lignes_management extends Element_management {

    /**
     *
     * On calcule le montant remboursé de la ligne, puis on déclenche la mise à jour des informations de la note de frais
     *
     */
    protected function methodes_post_modification($modele, $modele_avant, $modifications){

        parent::methodes_post_modification($modele, $modele_avant, $modifications);

        // Si on a une modification sur le montant ttc ou l'article on doit mettre à jour le montant rembourse
        if(!empty($modifications['montant_ttc']) || !empty($modifications['article_id'])){

            $article = modele('article_note_de_frais',$this->modele->article_id);

            $montant_ttc = $this->modele->montant_ttc;

            if(!empty($article)){

                if ($montant_ttc < $article->plafond || empty($article->remboursement_plafonne))
                    $montant_rembourse = $montant_ttc;
                else
                    $montant_rembourse = $article->plafond;

                if($montant_rembourse != $this->modele->montant_rembourse)
                    $this->enregistre_modele(['montant_rembourse' => $montant_rembourse]);
            }
        }

        $this->mise_a_jour_note_de_frais();
    }

    /**
     *
     * Bloque l'enregistrement si la note de frais est déjà validée
     *
     */
    public function enregistre($modifications = array(), $modele = false) {

        if($modifications['montant_ttc'] == 0)
            return traduction('messages.php.note_de_frais_ligne.erreur_ligne_a_0');

        $note_de_frais = modele('note_de_frais')->where('id', $modifications['note_de_frais_id'] ?? $this->modele->note_de_frais_id ?? 0)->first();


        if(isset($note_de_frais->accepte) && $note_de_frais->accepte == 1){
            if(isset($note_de_frais->comptabilisee) && $note_de_frais->comptabilisee == 1){
                return traduction('messages.php.note_de_frais_lignes.erreur_note_de_frais_comptabilisee');
            }
            $employe = modele('utilisateur', $note_de_frais->utilisateur_id);
            $management_employe = management('utilisateur', $employe->id, $employe);
            $management_employe->charge_valeurs_champs_multiselection();

            $utilisateur_connecte = moi();

            if((!in_array($utilisateur_connecte->id,$employe->validation_ndf_n_plus_1) && !in_array($utilisateur_connecte->id,$employe->validation_ndf_n_plus_2)) || (isset($note_de_frais->valide_n2) && in_array($utilisateur_connecte->id,$employe->validation_ndf_n_plus_1)))
                return traduction('messages.php.note_de_frais_lignes.erreur_note_de_frais_acceptee');
        }
            
        else if(isset($note_de_frais->accepte) && $note_de_frais->accepte == 2 && moi()->id !== $note_de_frais->utilisateur_id)
            return traduction('messages.php.note_de_frais_lignes.erreur_note_de_frais_refusee');

        return parent::enregistre($modifications,$modele);
    }

    /**
     *
     * Bloque la suppression si la note de frais est déjà validée
     *
     */
    public function supprime($modele = false) {

        $note_de_frais = modele('note_de_frais')->where('id', $this->modele->note_de_frais_id)->first();

        if(isset($note_de_frais->accepte) && $note_de_frais->accepte == 1)
            return traduction('messages.php.note_de_frais_lignes.erreur_note_de_frais_acceptee');

        return parent::supprime($modele);
    }

    /**
     *
     * On déclenche la mise à jour des informations de la note de frais
     *
     */
    protected function methodes_post_suppression($modele) {

        parent::methodes_post_suppression($modele);
        $this->mise_a_jour_note_de_frais();
    }

    /*
     *
     * Déclenche la mise à jour des montants et de la catégorie de dépenses principales de la note de frais liée à la ligne
     *
     */
    private function mise_a_jour_note_de_frais() {

        $note_de_frais = modele('note_de_frais')->where('id', $this->modele->note_de_frais_id)->first();

        if (!empty($note_de_frais))
            $management_note_de_frais = management('note_de_frais', $note_de_frais->id, $note_de_frais)->maj_montants();
    }

    public function montant_ht_devise($modele){

        $note_de_frais = modele('note_de_frais')->where('id', $modele->note_de_frais_id)->first();

        if(!empty($note_de_frais) && $note_de_frais->devise > 0){

            $devise = modele('devise', $note_de_frais->devise);

            return round($modele->montant_ht / $note_de_frais->taux_de_change,2) .' '.$devise->code;
        }

        return round($modele->montant_ht,2) . ' ' . maquette('devise_application_symbole');
    }
}
