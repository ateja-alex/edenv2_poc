<a href="javascript:;" @click="nouvel_echange()" class="css_action_icon primaire far fa-comments" title="{{ traduction('module_sur_fiche.fiche.contact.nouvel_echange') }}" data-toggle="tooltip"></a>

@push('modales')
    <!-- Modale ajouter un échange 2 -->
    <div class="modal fade" id="modal_ajout_echange" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@traduction('module_sur_fiche.fiche.contact.titre_modal')</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body css_form">
                    <form id="js_edite_echange">
                        @include('eden::formulaires.echange')
                        <input type="hidden" name="client_id" v-model="echange.client_id" />
                        <input type="hidden" name="contact_id" v-model="echange.contact_id" />
                        <input type="hidden" name="lead_id" v-model="echange.lead_id" />
                        <input type="hidden" name="fournisseur_id" v-model="echange.fournisseur_id" />
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">@traduction('module_sur_fiche.fiche.contact.fermer')</button>
                    <div class="btn btn-primary" @click="enregistrer_nouvel_echange()">@traduction('module_sur_fiche.fiche.contact.enregistrer')</div>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('donnees_pour_vuejs_data')
    echange: {},
@endpush

@push('donnees_pour_vuejs_methods')

    nouvel_echange: function(){

        $('#modal_ajout_echange').css("z-index", 1500).modal('show');

        <?php
        $id_valeur_echange = App\Eden\Models\Champs_liste_formatee::where('id_liste_choix',33)->where('desactivee','!=',1)->first();

        if($id_valeur_echange !== null) {
            $id_valeur_echange = $id_valeur_echange->id_valeur;
        }
        else{
            $id_valeur_echange=false;
        }
        ?>
        this.echange = {

            type_element: 'contact',
            element_id: {{ $contact->id }},
            date: '{{ date('d/m/Y H:i:s') }}',
            type: {{intval($id_valeur_echange)}},
            utilisateur_id: {{moi()->id}},
        };
    },


    enregistrer_nouvel_echange: function() {

        // on enregistre toutes les nouvelles infos
        $.post({

            url: "{{ route('base_eden.element.creer', ['echange']) }}",
            dataType: "json",
            method: "post",
            data: $('#js_edite_echange').serialize()
        }).done(async function(donnees) {

            if(donnees.retour !== true) {

                await erreur(donnees.retour);
                return;
            }

            $('#modal_ajout_echange').modal('hide');
        });
    },

@endpush