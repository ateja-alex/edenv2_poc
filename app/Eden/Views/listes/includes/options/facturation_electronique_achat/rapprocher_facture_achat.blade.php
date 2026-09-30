<a v-if="!ligne.element.facture_achat_id && !ligne.element.avoir_achat_id"
   @click="ouvrir_rapprochement_facture_achat(ligne.element.id)"
   style="cursor:pointer"> <span class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste">
        <span class="fas fa-link"
              data-toggle="tooltip"
              :title="$root.traduction('interface.listes.rapprocher_facture_achat')">
        </span>
    </span>
</a>
<a v-else
   @click="derapprocher_facture_achat(ligne.element.id)"
   style="cursor:pointer"> <span class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste">
        <span class="fas fa-unlink"
              data-toggle="tooltip"
              :title="$root.traduction('interface.listes.derapprocher_facture_achat')">
        </span>
    </span>
</a>

@push('modales')
<template v-if="modal_rapprochement_facture_achat">
    <transition name="modal" >
        <div class="modal-mask">
            <div class="modal-dialog modal-xl" style="max-width:90vw;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">@{{ $root.traduction('interface.listes.rapprocher_facture_achat') }}</h5>
                        <button type="button" class="close" @click="modal_rapprochement_facture_achat = false" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div v-if="chargement_candidats_facture_achat" style="text-align:center;padding:30px;">
                            <img style="width: 60px;" src="{{ 'eden/images/ajax_loader.gif' }}">
                        </div>
                        <component v-else-if="composant_candidats_facture_achat" :is="composant_candidats_facture_achat"></component>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="modal_rapprochement_facture_achat = false">@{{ $root.traduction('interface.listes.fermer') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>
@endpush

@push('donnees_pour_vuejs_data')
    modal_rapprochement_facture_achat: false,
    chargement_candidats_facture_achat: false,
    composant_candidats_facture_achat: null,
@endpush

@push('donnees_pour_vuejs_methods')

    ouvrir_rapprochement_facture_achat: function(id_element){

        this.$root.id_facturation_electronique_achat_a_rapprocher = id_element;
        this.composant_candidats_facture_achat = null;
        this.chargement_candidats_facture_achat = true;
        this.modal_rapprochement_facture_achat = true;

        $.get({
            url: "{{ route('base_eden.fiche.index', ['facturation_electronique_achat', '__ID__', 'candidats_facture_achat'], false) }}".replace('__ID__', id_element),
            dataType: 'json',
        }).done((donnees) => {

            eval(donnees.composant);

            this.composant_candidats_facture_achat = composant;
            this.chargement_candidats_facture_achat = false;
        }).fail(() => {

            this.chargement_candidats_facture_achat = false;
        });
    },

    derapprocher_facture_achat : async function(id_element){

        if(await confirm_eden(this.$root.traduction('interface.listes.confirmation_derapprocher_facture_achat')) !== true)
            return;

        loading(true);

        $.post({
            url: "{{ route('base_eden.element.enregistrer', ['facturation_electronique_achat', '__ID__'], false) }}".replace('__ID__', id_element),
            data: { facture_achat_id: null, avoir_achat_id: null },
            dataType: 'json',
        }).done(async (donnees) => {

            loading(false);

            if(donnees.retour !== true){

                await erreur(donnees.retour);
                return;
            }

            this.actualisation_filtres();
        }).fail(() => loading(false));
    },
@endpush

@push('donnees_pour_vuejs_mounted')

    this.$root.$on('rapprochement_facture_achat_effectue', () => {

        this.modal_rapprochement_facture_achat = false;
        this.actualisation_filtres();
    });
@endpush
