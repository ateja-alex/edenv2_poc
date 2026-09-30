@if(editeur())

    @php
        $rappels_version = parametre('rappels_version');

        if($rappels_version != null)
            $rappels_version = json_decode($rappels_version);
        else
            $rappels_version = [];
    @endphp

    @push('modales')
    <template v-if="rappel_version.afficher_modale_rappel_version">
        <transition name="modal" >
            <div class="modal-mask">
                <div class="modal-dialog modal-lg" style="height:90%">
                    <div class="modal-content" style="height:100%">

                        <div class="modal-header">
                            <h5 class="modal-title">@traduction('interface.modales_alertes_rappels_versions.titre')</h5>
                        </div>

                        <div class="modal-body" style="min-height: 75vh;overflow-y: auto;" >
                            <template v-for="rappels_par_version in rappels_par_versions">
                                <div class="row">
                                    <div class="col-sm-12 css_form_ligne_titre">
                                        <h6>@traduction('interface.modales_alertes_rappels_versions.version') @{{ rappels_par_version.version }}</h6>
                                    </div>
                                </div>
                                <div v-for="rappel in rappels_par_version.rappels" style="border-bottom: 0.5px solid lightgrey;">
                                    <div class="row" style="padding-top: 15px;padding-bottom: 25px;">
                                        <div class="col-sm-12">
                                            <span><b>@traduction('interface.modales_alertes_rappels_versions.ticket') @{{ rappel.ticket.id }} : </b> @{{ rappel.ticket.titre }}</span>
                                        </div>
                                        <div class="col-sm-12" v-if="rappel.ticket.description != ''">
                                            <span v-html="rappel.ticket.description"></span>
                                        </div>
                                    </div>
                                    <div class="row" style="padding-bottom: 15px;">
                                        <div class="col-sm-12" style="padding-bottom: 25px;">
                                            <span> @traduction('interface.modales_alertes_rappels_versions.note') : </span><span v-html="rappel.description"></span>
                                        </div>
                                        <div class="col-sm-12 text-right" v-if="rappel.referent != ''">
                                            <span style="font-style: italic;">@traduction('interface.modales_alertes_rappels_versions.referent') : @{{ rappel.referent }}</span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="rappel_version.afficher_modale_rappel_version = false">@traduction('interface.modales.fermer')</button>
                            <button type="button" class="btn btn-primary" @click="confirmation_lecture_rappel">@traduction('interface.modales_alertes_rappels_versions.lu')</button>
                        </div>

                    </div>
                </div>
            </div>
        </transition>
    </template>
    @endpush

    @push('donnees_pour_vuejs_data')
        rappel_version : {
            afficher_modale_rappel_version : {{ $rappels_version != null && editeur() ? 'true' : 'false' }},
            rappels : {!! collect($rappels_version) !!},
        },
    @endpush

    @push('donnees_pour_vuejs_methods')
        confirmation_lecture_rappel: function(){

            var instance = this;
            $.ajax({
                url: '{{URL::to('eden/maintenance/rappel_version/confirmation_lecture')}}',
            }).done(function(){
                instance.rappel_version.afficher_modale_rappel_version = false;
            });
        },

        recuperer_rappels_version: async function(){

            var rappels = await $.ajax({
                url: '{{URL::to('eden/maintenance/rappel_version')}}',
                dataType: 'json',
            });

            if(rappels != false && {{editeur() ? 'true' : 'false'}}){
                this.rappel_version.afficher_modale_rappel_version = true;
                this.rappel_version.rappels = rappels;
            }

            return true;
        },
    @endpush

    @push('donnees_pour_vuejs_computed')

        rappels_par_versions: function(){
            return this.rappel_version.rappels.sort(
                function ( a, b ) {
                    return b.version - a.version;
                }
            );
        },
    @endpush

@endif
