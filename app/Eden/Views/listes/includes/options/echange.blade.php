<a
   @click="nouvel_echange([
            {champ: 'type_element', valeur: type_element},
			{champ: 'element_id', valeur: ligne.element.id},
			{champ: 'utilisateur_id', valeur: $root.moi.id}
        ])"
   style="cursor:pointer"> <span class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste">
        <span class="fas fa-comment"
              data-toggle="tooltip"
              :title="$root.traduction('interface.listes.ajout_echange')">
        </span>
    </span>
</a>

@push('modales')
<template v-if="modal_ajout_echange">
    <transition name="modal" >
        <div class="modal-mask">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">@traduction('interface.listes.gestion_des_echanges')</h5>
                        <button type="button" class="close" @click="modal_ajout_echange = false"  aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body css_form">
                        <formulaire ref="formulaire_echange" nom_formulaire="echange"></formulaire>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="modal_ajout_echange = false">@traduction('interface.listes.fermer')</button>
                        <div class="btn btn-primary" @click="enregistrer_nouvel_echange()">@traduction('interface.listes.enregistrer')</div>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>
@endpush

@push('donnees_pour_vuejs_data')
    modal_ajout_echange: false,
@endpush

@push('donnees_pour_vuejs_methods')

    nouvel_echange: function(liste_donnees){

        this.$once('formulaire_charger',() => {

            for(association_champ_valeur of liste_donnees){
                this.$refs.formulaire_echange.element[association_champ_valeur.champ] = association_champ_valeur.valeur;
            }

            this.$refs.formulaire_echange.element.type = this.$root.valeurs_listes_formatees[33].filter(valeur => valeur.id_valeur > 0)[0].id_valeur;
        });

        this.modal_ajout_echange = true;
    },
    enregistrer_nouvel_echange: async function() {

        var component = this;

        // On afficher le loader
        loading(true);

        var donnees = await this.$refs.formulaire_echange.enregistrer();

        if(donnees.retour === true)
            this.modal_ajout_echange = false;

        loading(false);
    },
@endpush