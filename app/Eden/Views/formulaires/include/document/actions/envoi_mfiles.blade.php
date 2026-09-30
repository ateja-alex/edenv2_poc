<a class="css_action_icon primaire fa fa-file-upload"
   @click="envoi_mfiles()"
   :title="traduction('document.actions.envoi_mfiles')"
   data-toggle="tooltip" >
</a>

@push('modales')

    <!-- Modale erreur envoi document M-Files -->
    <transition name="modal" v-if="modale_erreur_ajout_mfiles" hidden>
        <div class="modal-mask">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">M-Files : erreur envoi document</h5>
                    </div>

                    <div class="modal-body css_form js_selection_element" >
                        @{{ message_erreur_ajout_mfiles }}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="modale_erreur_ajout_mfiles = false">@traduction('interface.modales.fermer')</button>
                    </div>
                </div>
            </div>
        </div>

    </transition>
@endpush
@push('donnees_pour_vuejs_data')

    modale_erreur_ajout_mfiles : false,
    message_erreur_ajout_mfiles : '',
@endpush

@push('donnees_pour_vuejs_methods')

    envoi_mfiles : function(){

        var vue_instance = this;

        loading(true);

        $.ajax({

            url: "{{ URL::to('/eden/mfiles/document') }}/" + vue_instance.type_element + "/" + vue_instance.id_element + "/envoi_mfiles",
            dataType: "json"
        }).done(function(donnees) {

            loading(false);

            if(donnees.succes === false && donnees.mfiles === true){

                vue_instance.message_erreur_ajout_mfiles = donnees.message;
                vue_instance.modale_erreur_ajout_mfiles = true;
                return false;
            }

            toastr.success('Document envoyé.');
        });
    },
@endpush