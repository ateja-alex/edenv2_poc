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

    changement_statut_possible : function(){

        var element = this.employe_demande_conge;

        if(element.statut !== 0 && element.statut != null)
            return false;

        if(this.moi.type_utilisateur == 2)
            return true;

        var utilisateur = this.utilisateurs.find(utilisateur => utilisateur.id == element.employe_id);

        if(!utilisateur)
            return false;

        if(utilisateur.validation_conges_n_plus_1.includes(this.$root.moi.id) && !element.valide_n1)
            return true

        return utilisateur == null ? false : utilisateur.validation_conges_n_plus_2.includes(this.$root.moi.id);
    },

    changement_statut_demande : async function(event, statut){

        event.stopPropagation();

        if(!await confirm_eden())
            return false;

        loading(true);

        var id = this.employe_demande_conge.id;

        $.post({

            url: "{{ route('employe_demande_conge.changement_statut_conge') }}",
            data: {

                ids: {id},
                statut: statut
            }
        }).done(async (retour) => {

            if(retour.retour === false) {

                retour.message = retour.message.replaceAll('<br>', '\n')
                await erreur(retour.message);
            }
            else{

                await this.$refs.formulaire_edition_element.$refs.formulaire.actualisation_modele();
                this.$set(this.$root, 'employe_demande_conge', this.$refs.formulaire_edition_element.$refs.formulaire.element);
                info(this.$root.traduction('messages.js.enregistrement_succes'));
            }

            loading(false);
        });
    },
@endpush