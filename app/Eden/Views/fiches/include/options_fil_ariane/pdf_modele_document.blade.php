<div class="btn-group" v-if="modeles_de_document_a_afficher.length > 0">
    <i class="css_action_icon primaire fa fa-fw fa-print" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
    <div class="dropdown-menu">
        <a class="dropdown-item" v-for="modele_de_document in modeles_de_document_a_afficher"
           :href="'/eden/fiche/'+$root.type_element+'/'+$root.element_id+'/generer_pdf_depuis_modele/'+modele_de_document.id" target="_blank" v-html="modele_de_document.nom"></a>
    </div>
</div>

@push('donnees_pour_vuejs_data')
    modeles_de_document: {!! collect($modeles_de_document) !!},
@endpush

@push('donnees_pour_vuejs_computed')
    modeles_de_document_a_afficher : function(){
        return this.modeles_de_document.filter(
            (modele_de_document) => {
                var a_afficher = true;

                if(modele_de_document.condition_affichage != null){
                    with(this) { 
                        a_afficher = eval(modele_de_document.condition_affichage); 
                    }
                }

                return a_afficher;
            }
        );
    },
@endpush