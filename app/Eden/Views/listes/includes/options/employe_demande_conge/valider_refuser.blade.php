<template v-if="changement_statut_possible(ligne.element)">
    <span @click="changement_statut_demande($event,ligne.element.id, 1)"
          class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste css_btn_action_theme">
        <i class="fa fa-fw fa-check"></i>
    </span>
    <span @click="changement_statut_demande($event,ligne.element.id, 2)"
          class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste css_btn_action_theme">
        <i class="fa fa-fw fa-times"></i>
    </span>
</template>

@push('donnees_pour_vuejs_data')
    utilisateurs : [],
@endpush

@push('donnees_pour_vuejs_mounted')

    $.post({
        url : 'eden/elements/utilisateur',
        dataType : 'json'
    }).done((utilisateurs) => {
        
        this.utilisateurs = utilisateurs;
    });
@endpush

@push('donnees_pour_vuejs_methods')

    changement_statut_possible : function(element){

        if(element.statut !== 0 && element.statut != null)
            return false;

        if(this.$root.moi.type_utilisateur == 2)
            return true;
    
        var utilisateur = this.utilisateurs.find(utilisateur => utilisateur.id == element.employe_id);

        if(!utilisateur)
            return false;

        if(utilisateur.validation_conges_n_plus_1.includes(this.$root.moi.id) && !element.valide_n1)
            return true

        return utilisateur == null ? false : utilisateur.validation_conges_n_plus_2.includes(this.$root.moi.id);
    },

    changement_statut_demande : async function(event,id, statut){

        event.stopPropagation();
    
        if(!await confirm_eden())
            return false;
    
        loading(true);
    
        $.post({
    
            url: "{{ route('employe_demande_conge.changement_statut_conge', [], false) }}",
            data: {

                ids: {id},
                statut: statut
            }
        }).done(async (retour) => {
    
            loading(false);
        
            if(retour.retour === false) {
            
                retour.message = retour.message.replaceAll('<br>', '\n')
                await erreur(retour.message);
            }
        
            info(this.$root.traduction('messages.js.enregistrement_succes'));
        
            this.actualisation_filtres();
        });
    },
@endpush
