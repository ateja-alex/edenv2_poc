<template v-if="modale_generer_modele_de_relance">
    <transition name="modal">
        <div class="modal-mask">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">@traduction('document.actions.transformations_possibles.modele_relance_numero') @{{ numero_relance }}</h5>
                        <button type="button" class="close" @click="modale_generer_modele_de_relance = false" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="" method="post" class="css_form">
                        <div class="modal-body" style="font-size: 13px;">
                            <div class="row">
                                <div class="col-md-12" v-if="etape_relance == 1">
                                    <div class="form-group">
                                        <label for="">@traduction('document.actions.transformations_possibles.modele_relance')</label>
                                        <textarea style="height:100%" name="contenu_email_relance" v-model="contenu_email_relance" rows="3" cols="30"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-12" v-if="etape_relance == 2">
                                    <span style="white-space: pre-line;word-wrap: break-word;" v-html="contenu_email_relance_html"></span>
                                </div>
                                <div class="col-md-12" v-if="etape_relance == 3">
                                    <span>@traduction('document.actions.transformations_possibles.telechargement_relance')</span>
                                </div>
                            </div>


                        </div>
                        <div class="modal-footer">

                            <button type="button" class="btn btn-danger" @click.prevent="annuler_modele_relance">@traduction('interface.modales.annuler')</button>

                            <button v-if="etape_relance == 1" class="btn btn-secondary" @click.prevent="transformer_modele_relance">@traduction('document.actions.transformations_possibles.transformer')</button>
                            <button v-if="etape_relance == 2" class="btn btn-secondary" @click.prevent="generer_modele_relance">@traduction('document.actions.transformations_possibles.generer')</button>
                            <a :href="chemin_email_relance" type="button"  v-if="etape_relance == 3" class="btn btn-secondary">@traduction('document.actions.transformations_possibles.telecharger')</a>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </transition>
</template>

@push('donnees_pour_vuejs_data')

    modale_generer_modele_de_relance : false,
    numero_relance : 1,
    contenu_email_relance : '',
    contenu_email_relance_html : '',
    chemin_email_relance : '',
    etape_relance : 1,
@endpush

@push('donnees_pour_vuejs_methods')

    ouvrir_modele_relance : function(numero_relance) {

        var contenus_modeles_de_relances = {

            1 : `{!! fonctionnalite('modele_relance_1') !!}`,
            2 : `{!! fonctionnalite('modele_relance_2') !!}`,
            3 : `{!! fonctionnalite('modele_relance_3') !!}`,
            4 : `{!! fonctionnalite('modele_relance_4') !!}`,
        };

        this.numero_relance = numero_relance;
        this.etape_relance = 1;
        this.contenu_email_relance = contenus_modeles_de_relances[numero_relance] ?? '';

        this.modale_generer_modele_de_relance = true;
    },

    transformer_modele_relance : function() {

        loading(true);

        var donnees = {
            modele : this.contenu_email_relance
        };

        $.post({

            url: '{{ route('document.transformer_modele_relance', ['type_element' => $management->_type_element, 'id' => $management->modele->id]) }}',
            data: donnees,
            success: function(data) {

                loading(false);

                if(data.retour == true) {

                    vue_instance.$data.etape_relance = 2;
                    vue_instance.$data.contenu_email_relance_html = data.contenu;
                }
            }
        });

    },

    generer_modele_relance : function() {

        loading(true);

        var donnees = {
            modele : this.contenu_email_relance_html
        };

        $.post({

            url: '{{ route('document.generer_modele_relance', ['type_element' => $management->_type_element, 'id' => $management->modele->id]) }}',
            data: donnees,
            success: function(data) {

                loading(false);

                if(data.retour == true) {

                    vue_instance.$data.etape_relance = 3;
                    vue_instance.$data.chemin_email_relance = data.chemin;
                }
            }
        });

    },

    annuler_modele_relance : function() {

        if(this.etape_relance == 1) {

            this.modale_generer_modele_de_relance = false;
        }
        else {
            this.etape_relance = this.etape_relance - 1;
        }
    },
@endpush
