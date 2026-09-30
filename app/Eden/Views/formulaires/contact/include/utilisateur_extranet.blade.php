<template v-if="modale_utilisateur_extranet">
    <transition name="modal">
        <div class="modal-mask">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">@traduction('tables_libres.utilisateur_extranet.element')</h5>
                        <button type="button" class="close" @click="modale_utilisateur_extranet = false" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <formulaire ref="formulaire_utilisateur_extranet" 
                            nom_formulaire="utilisateur_extranet" :options="{contact_id_source : contact.id}">
                        </formulaire>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="modale_utilisateur_extranet = false">@traduction('interface.modales.fermer')</button>
                        <button type="button" class="btn btn-primary" @click="enregistrement_utilisateur_extranet">@traduction('interface.modales.enregistrer')</button>    
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

@push('donnees_pour_vuejs_data')
    modale_utilisateur_extranet : false,
    utilisateur_extranet : {},
@endpush

@push('donnees_pour_vuejs_methods')

    gestion_utilisateur_extranet : function(){

        var filtrage = {};
    
        if(this.contact.utilisateur_extranet_id > 0)
            filtrage = {
                champ : 'id',
                condition : 'where',
                valeur : this.contact.utilisateur_extranet_id
            };
        else
            filtrage = {
                champ : 'email',
                condition : 'where',
                valeur : this.contact.adresse_email
            };

        $.post({
            url : 'eden/elements/utilisateur_extranet',
            dataType : 'json',
            data:{
                filtrage:[filtrage]
            }
        }).done((elements) => {

            this.$once('formulaire_charger', () => {
                if(elements.length > 0){
                    this.utilisateur_extranet = elements[0];
                    this.$refs.formulaire_utilisateur_extranet.element = this.utilisateur_extranet;
                }
                else{
                    this.$refs.formulaire_utilisateur_extranet.element.email = this.contact.adresse_email;
                    this.$refs.formulaire_utilisateur_extranet.element.nom = this.contact.nom;
                    this.$refs.formulaire_utilisateur_extranet.element.prenom = this.contact.prenom;
                }
            });

            this.modale_utilisateur_extranet = true;
        });
    },

    enregistrement_utilisateur_extranet : async function(){

        loading(true);

		var donnees = await this.$refs.formulaire_utilisateur_extranet.enregistrer();

		loading(false);

		if(donnees.retour !== true) {
			await erreur(donnees.retour);
			return;
		}

        if(this.contact.utilisateur_extranet_id != donnees.element.id)
            this.contact.utilisateur_extranet_id = donnees.element.id;

		this.modale_utilisateur_extranet = false;
    },

@endpush