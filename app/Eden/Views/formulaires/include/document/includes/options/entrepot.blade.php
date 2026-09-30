<span class="dropdown dropdown_hover">
    <div class="css_bouton_custom_document_vente entrepot" aria-haspopup="true" aria-expanded="false"
        :data-original-title="traduction('document.blocs.saisie_des_articles.aide.{{$nom_option_ligne_divers}}')">
        <i class="fas fa-warehouse"></i>
    </div>
    <div class="dropdown_elements">
        <champ-selection-element type_element="entrepot" nom_sql="id" :modele="entrepot_preselection"></champ-selection-element>
        <span class="css_pointer btn btn-action entrepot" @click="maj_lignes_entrepot" v-if="entrepot_preselection.id > 0">
            @traduction('document.blocs.saisie_des_articles.entrepot.mise_a_jour_lignes')
        </span>
    </div>
</span>

@push('donnees_pour_vuejs_data')
    entrepot_preselection : {
        id : 0,
    },
@endpush

@push('donnees_pour_vuejs_methods')

    maj_lignes_entrepot : async function(){

        var entrepot_selectionne = false;

        for(article of this.articles_du_document){

            if(article.entrepot_id != 0 && article.entrepot_id != null && article.entrepot_id != this.entrepot_preselection.id)
                entrepot_selectionne = this.entrepot_preselection.id;
        }

        if(entrepot_selectionne !== false){

            if(!await confirm_eden('{!! traduction('interface.alerte.attention') !!}',this.traduction('document.blocs.saisie_des_articles.entrepot.alerte_mise_a_jour_lignes'),'{!! traduction('interface.modales.oui') !!}','{!! traduction('interface.modales.non') !!}'))
                return;
        }

        for(article of this.articles_du_document){

            article.entrepot_id = this.entrepot_preselection.id;
        }
    },
@endpush