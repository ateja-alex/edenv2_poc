<span v-if="modeles_de_document_disponibles(ligne.element).length > 0" class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste css_btn_action_theme"
      @click="element_a_imprimer = ligne.element;modale_imprimer_modele_document_options = true;"
      :title="$root.traduction('interface.modele_de_document.impression')"
      data-toggle="tooltip"
        >
    <i class="fas fa-print"></i>
</span>

@push('donnees_pour_vuejs_data')
    modale_imprimer_modele_document_options : false,
    element_a_imprimer:false,
    modeles_de_document:[],
@endpush

@push('donnees_pour_vuejs_mounted')

    $.post({
        url : '{{route('base_eden.element.rechercher_avec_requete',['modele_de_document'], false)}}',
        dataType : 'json',
        data : {
            donnees : {
                nom_sql : 'type_element_autres',
                valeur : this.liste.type_element
            }
        }
    }).done((donnees) => {
        this.modeles_de_document = donnees.retour;
    });
@endpush

@push('donnees_pour_vuejs_methods')
    modeles_de_document_disponibles : function(element){

        return this.modeles_de_document.filter(
            (modele_de_document) => {
                var a_afficher = true;

                if(modele_de_document.condition_affichage != null){

                    var condition = modele_de_document.condition_affichage.replace(new RegExp(this.type_element+"\\.", "g"),'element.');

                    with(this) { 
                        a_afficher = eval(condition); 
                    }
                }

                return a_afficher;
            }
        );
    },
@endpush

@push('modales')
    <!-- OPTION -->
    <template v-if="modale_imprimer_modele_document_options">
        <transition name="modal" >
            <div class="modal-mask">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" style="color:black;">@traduction('interface.listes.titre_imprimer_modele_document')</h5>
                            <button type="button" class="close" @click="modale_imprimer_modele_document_options = false" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body css_form" style="color:#363636;">
                            @traduction('interface.listes.imprimer_modele_document')
                            <br/><br/>
                            <a v-for="modele_de_document in modeles_de_document_disponibles(element_a_imprimer)" class="btn btn-secondary" :href="'eden/fiche/'+type_element+'/'+element_a_imprimer.id+'/generer_pdf_depuis_modele/'+modele_de_document.id" target="_blank">
                                <i class="fa fa-file-pdf"></i> @{{modele_de_document.nom}}
                            </a>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="modale_imprimer_modele_document_options = false">Fermer</button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </template>
@endpush
