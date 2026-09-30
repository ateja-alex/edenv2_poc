@extends('eden::composants_vue.js.liste_libre')

@push('vue_liste_actions')

<div class="modal fade" id="modal_rattacher_paiement_{{$liste_id}}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@traduction('interface.listes.rattacher_les_paiements')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @traduction('interface.listes.que_souhaitez_vous_faire')<br/><br/>
                <span class="btn btn-xs btn-primary js_action_lignes_selectionnees" :class="this.liste.lignes_selectionnees.length == 0 ? 'disabled ' : ''" @click="eden_rattacher_paiements_selectionnes({{$liste_id}})">@traduction('interface.listes.rattacher_paiements_selectionnes') (<span class="js_nombre_lignes_selectionnees" v-html="this.liste.lignes_selectionnees.length"></span>)</span><br/><br/>
                <span class="btn btn-xs btn-primary" @click="eden_rattacher_tous_les_paiements({{$liste_id}})">@traduction('interface.listes.rattacher_tous_les_paiements')</span><br/>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@traduction('interface.listes.fermer')</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_detacher_paiement_{{$liste_id}}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@traduction('interface.listes.detacher_paiements')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @traduction('interface.listes.que_souhaitez_vous_faire')<br/><br/>
                <span class="btn btn-xs btn-primary js_action_lignes_selectionnees" :class="this.liste.lignes_selectionnees.length == 0 ? 'disabled ' : ''" @click="eden_detacher_paiements_selectionnes({{$liste_id}})">@traduction('interface.listes.detacher_paiements_selectionnes') (<span class="js_nombre_lignes_selectionnees" v-html="this.liste.lignes_selectionnees.length"></span>)</span><br/><br/>
                <span class="btn btn-xs btn-primary" @click="eden_detacher_tous_les_paiements({{$liste_id}})">@traduction('interface.listes.detacher_paiements_de_cette_liste')</span><br/>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@traduction('interface.listes.fermer')</button>
            </div>
        </div>
    </div>
</div>

@endpush

@push('donnees_pour_vuejs_methods')

    eden_rattacher_paiements_selectionnes: function(id_liste) {

        var vue_instance = this;

        $('#modal_rattacher_paiement_'+id_liste).modal('hide');

        var type_element = vue_instance.liste.type_element;

        // on va chercher les ID des éléments sélectionnés
        var ids = vue_instance.liste.lignes_selectionnees;

        if(ids.length == 0)
            return;

        // on récupère les ids...
        loading(true);

        $.post({
            url: "/eden/fiche/bordereau/"+ vue_instance.$root.bordereau.id+"/post/rattacher_paiement",
            data: {
                ids: ids,
            }
        }).always(async function(retour) {

            // on cache le loader
            loading(false);

            if(retour.retour === false) {

                await erreur(retour.message);
                return;
            }

            vue_instance.$root.bordereau = retour.bordereau;

            // on actualise
            vue_instance.actualisation_filtres();

        });
    },

    eden_rattacher_tous_les_paiements: async function(id_liste) {

        var vue_instance = this;
        $('#modal_rattacher_paiement_'+id_liste).modal('hide');

        // on récupère les ids...
        loading(true);

        await vue_instance.actualisation_filtres(true);

        var ids = vue_instance.liste.ids;

        var type_element = vue_instance.liste.type_element;

        $.post({

            url: "/eden/fiche/bordereau/"+ vue_instance.$root.bordereau.id+"/post/rattacher_paiement",
            data: {
                ids: ids,
            }
        }).always(async function(retour) {

            loading(false);

            if(retour.retour === false) {

                await erreur(retour.message);
                return;
            }

            vue_instance.$root.bordereau = retour.bordereau;

            // on actualise
            vue_instance.actualisation_filtres();
        });

    },

    eden_detacher_paiements_selectionnes: function(id_liste) {

        var vue_instance = this;
        $('#modal_detacher_paiement_'+id_liste).modal('hide');

        var type_element = vue_instance.liste.type_element;

        // on va chercher les ID des éléments sélectionnés
        var ids = vue_instance.liste.lignes_selectionnees;

        if(ids.length == 0)
            return;

        // on récupère les ids...
        loading(true);

        $.post({

        url: "/eden/fiche/bordereau/"+ vue_instance.$root.bordereau.id+"/post/detacher_paiement",
        data: {
            ids: ids,
        }
        }).always(async function(retour) {

        // on cache le loader
        loading(false);

        if(retour.retour === false) {

            await erreur(retour.message);
            return;
        }

        vue_instance.$root.bordereau = retour.bordereau;

        // on actualise
        vue_instance.actualisation_filtres();

        });
    },

    eden_detacher_tous_les_paiements: async function(id_liste) {

        var vue_instance = this;
        $('#modal_rattacher_paiement_'+id_liste).modal('hide');

        // on récupère les ids...
        loading(true);

        await vue_instance.actualisation_filtres(true);

        var ids = vue_instance.liste.ids;

        var type_element = vue_instance.liste.type_element;

        $.post({

            url: "/eden/fiche/bordereau/"+ vue_instance.$root.bordereau.id+"/post/detacher_paiement",
            data: {
                ids: ids,
            }
        }).always(async function(retour) {

            loading(false);

            if(retour.retour === false) {

                await erreur(retour.message);
                return;
            }

            vue_instance.$root.bordereau = retour.bordereau;

            // on actualise
            vue_instance.actualisation_filtres();
        });
    },

@endpush

@push('donnees_pour_vuejs_mounted')

    var vue_instance = this;
    setTimeout(function(){
        if(vue_instance.$root.bordereau.mode_de_paiement != 0 && vue_instance.$root.bordereau.mode_de_paiement != null){

            var filtres = $('input[nom_sql="mode_paiement_id"]');

            filtres.each(function(){

                $(this).prop('checked',false);

            });

            var input_filtre = $('input[nom_sql="mode_paiement_id"][value="'+vue_instance.$root.bordereau.mode_de_paiement+'"');

            input_filtre.click();

        }
    },500);

@endpush

@push('scripts')
    <script>

        $('select[name="mode_de_paiement"]').on('change',function(){

            var valeur = $(this).val();

            var filtres = $('input[nom_sql="mode_paiement_id"]');

            filtres.each(function(){

                $(this).prop('checked',false);

            });

            var input_filtre = $('input[nom_sql="mode_paiement_id"][value="'+valeur+'"');

            input_filtre.click();
        });

    </script>
@endpush
