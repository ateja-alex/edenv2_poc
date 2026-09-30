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

        var element = this.note_de_frais;

        if(element.accepte !== 0 && element.accepte != null)
            return false;

        if(this.moi.type_utilisateur == 2)
            return true;

        var utilisateur = this.utilisateurs.find(utilisateur => utilisateur.id == element.utilisateur_id);

        if(!utilisateur)
            return false;

        if(utilisateur.validation_ndf_n_plus_1.includes(this.$root.moi.id) && !element.valide_n1)
            return true

        return utilisateur == null ? false : utilisateur.validation_ndf_n_plus_2.includes(this.$root.moi.id);
    },

    changement_statut_note_de_frais : async function(event, statut){

        event.stopPropagation();

        if(!await confirm_eden())
            return false;

        loading(true);

        var id = this.note_de_frais.id;

        $.post({

        url: "{{ route('note_de_frais.changement_statut') }}",
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

                this.formulaires_ndf.forEach(async (nom_formulaire) => {

                    await this.$refs[nom_formulaire].actualisation_modele();
                    this.$set(this.$root, 'note_de_frais', this.$refs[nom_formulaire].element);
                });

                info(this.$root.traduction('messages.js.enregistrement_succes'));

                this.$emit(statut == 1 ? 'validation_note_de_frais' : 'refus_note_de_frais', {
                    element : this.note_de_frais
                });
            }

            loading(false);
        });
    },
@endpush