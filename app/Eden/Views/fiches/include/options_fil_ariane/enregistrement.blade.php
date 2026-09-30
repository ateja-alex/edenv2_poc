@if($modification_possible)
    <span @click="enregistrer_formulaires_fiche()" class="css_ajouter_element ml-auto" data-toggle="tooltip" data-placement="top" :title="traduction('interface.enregistrer')">
        <i class="css_action_icon secondaire fa fa-fw fa-save"></i>
    </span>
@endif

@push('donnees_pour_vuejs_methods')

    enregistrer_formulaires_fiche : async function() {

        loading(true);

        let modifications_element = [];
        let champs_non_remplis = [];
        let liste_formulaires = [];
        let url = '';
        
        const type_element = this.type_element;
        const element_id = this[type_element].id;
        const enregistrement = element_id != '' && element_id != 0 && element_id != null;

        if (enregistrement)
            url = "eden/element/" + type_element + "/" + element_id + "/enregistrer";
        else
            url = "eden/element/" + type_element + "/creer";
        
        for(nom_composant of Object.keys(this.$refs)) {

            if(nom_composant.includes('formulaire'))
                liste_formulaires.push(nom_composant);
        }

        if(liste_formulaires.length === 1){

            let composant = {};

            if(liste_formulaires[0] === 'formulaire_edition_element')
                composant = this.$refs[liste_formulaires[0]].$refs.formulaire;
            else
                composant = this.$refs[liste_formulaires[0]];
            
            await composant.enregistrer();
            loading(false);

            return;
        }

        liste_formulaires.forEach((nom_formulaire) => {

            let donnees_formulaire_composant = {};

            if (['formulaire_edition_element','formulaire_affichage_element'].includes(nom_formulaire))
                donnees_formulaire_composant = this.$refs[nom_formulaire].$refs.formulaire;
            else
                donnees_formulaire_composant = this.$refs[nom_formulaire];

            const modifications_formulaire = donnees_formulaire_composant.formulaire_donnees_renseignes({});

            champs_non_remplis_formulaire = donnees_formulaire_composant.verification_champs_obligatoires(modifications_formulaire)
            
            if(champs_non_remplis_formulaire.length > 0)
                champs_non_remplis[nom_formulaire] = champs_non_remplis_formulaire;

            modifications_element = modifications_element.concat(modifications_formulaire.filter(mf => !modifications_element.map(me => me.name).includes(mf.name)));
        })
        
        if(Object.keys(champs_non_remplis).length > 0){

            var nom_champs_non_remplis = Object.values(champs_non_remplis).flat().map(champ_obligatoire => champ_obligatoire.nom);
            var erreur_affichage = '';

            if(champs_non_remplis.length == 1)
                erreur_affichage = this.$root.traduction('messages.php.champ_obligatoire')+nom_champs_non_remplis.join(', ');
            else
                erreur_affichage = this.$root.traduction('messages.php.champs_obligatoires')+nom_champs_non_remplis.join(', ');

            await erreur(erreur_affichage);

            liste_formulaires.forEach((nom_formulaire) => {

                let donnees_formulaire_composant = {};

                if(nom_formulaire === 'formulaire_edition_element')
                    donnees_formulaire_composant = this.$refs[nom_formulaire].$refs.formulaire;
                else
                    donnees_formulaire_composant = this.$refs[nom_formulaire];

                donnees_formulaire_composant.indication_champs_obligatoires(champs_non_remplis[nom_formulaire] ?? []);
            })

            loading(false);

            return {
                retour : false,
            };
        }

        var retour = await $.post({
            url: url,
            data: modifications_element,
            dataType: "json",
        }).done(async (donnees) => {

            if (donnees.retour !== true) {

                await erreur(donnees.retour);
                return;
            }

            if(!enregistrement)
                this.$root.$emit('ajout_element', type_element);
            else {
                liste_formulaires.forEach((nom_formulaire) => {

                    this.$root.$emit('enregistrement_formulaire', {
                        nom_formulaire : nom_formulaire,
                        retour : donnees,
                    });
                });
                if(this.$refs.historique)
                    this.$refs.historique.charge_donnees();
            }

            _.extend(this[type_element], donnees.element);
			info(this.$root.traduction('messages.js.enregistrement_succes'));
        });

        loading(false);
    },
@endpush