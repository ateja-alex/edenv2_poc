@push('modales')
    <template v-if="$root.modale_mfiles_jeton_authentification">
        <transition name="modal" >
            <div class="modal-mask">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">@traduction('interface.mfiles.modale_connexion.titre')</h5>
                            <button type="button" @click="modale_mfiles_jeton_authentification = false" aria-label="Close"
                                    class="close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body css_form">
                            <div class="col-sm-2">
                                @traduction('interface.mfiles.modale_connexion.nom_utilisateur')
                            </div>
                            <div class="col-sm-4">
                                <input type="text" v-model="mfiles['nom_utilisateur_mfiles']">
                            </div>
                            <div class="col-sm-2">
                                @traduction('interface.mfiles.modale_connexion.mot_de_passe')
                            </div>
                            <div class="col-sm-4">
                                <input type="password" v-model="mfiles['mot_de_passe_mfiles']">
                            </div>
                            <div class="col-sm-2">
                                @traduction('interface.mfiles.modale_connexion.vaultguid')
                            </div>
                            <div class="col-sm-4">
                                <input type="text" v-model="mfiles['vault_guid']">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="modale_mfiles_jeton_authentification = false">@traduction('interface.modales.fermer')</button>
                            <div class="btn btn-primary" @click="connexion_mfiles()">@traduction('interface.mfiles.modale_connexion.connexion')</div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </template>
@endpush

@push('donnees_pour_vuejs_data')

    mfiles: {

        nom_utilisateur_mfiles : '',
        mot_de_passe_mfiles : '',
        vault_guid : '',
    },
@endpush
@push('donnees_pour_vuejs_methods')
    connexion_mfiles: function(){

        var vue_instance = this;
        loading(true);

        $.ajax({

            url: "{{ route('mfiles.recuperer_jeton_authentification') }}",
            dataType: "json",
            method: 'post',
            data: {
                donnees_mfiles: vue_instance.mfiles
            }
        }).done(function(donnees) {

            if(donnees.succes === true){

                toastr.success(vue_instance.traduction('messages.js.mfiles.connexion_reussie'));
                vue_instance.parametres_fonctionnalites.mfiles_jeton_authentification = true;
                vue_instance.modale_mfiles_jeton_authentification = false;
            }
            else {

                toastr.error(donnees.erreur);
                vue_instance.parametres_fonctionnalites.mfiles_jeton_authentification = false;
            }

            loading(false);
        });

    },
@endpush