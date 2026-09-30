@extends('eden::fiches.fiche_generique')

@push('donnees_pour_vuejs_computed')
    formulaires_ndf: function() {

        var formulaires = [];

        for (const [ref, composant] of Object.entries(this.$refs)) {
			if (composant.formulaire != undefined)
				formulaires.push(ref);
		}
        return formulaires;
    },
@endpush
@push('donnees_pour_vuejs_methods')

    actualiser_montants_tva: async function(donnees) {

        let note_de_frais_tva = null;

        await $.ajax({

            url: "{{ URL::to('/eden/note_de_frais/') }}/"+this.note_de_frais.id+"/valeurs_tva",
            dataType: "json"
        }).done((donnees) => {

            note_de_frais_tva = donnees.taux_de_tva;
        });

        this.formulaires_ndf.forEach((nom_formulaire) => {

            this.$refs[nom_formulaire].$refs.formulaire.note_de_frais_tva = note_de_frais_tva;
        });

        this.note_de_frais.note_de_frais_tva = note_de_frais_tva;
    },
@endpush

@push('donnees_pour_vuejs_mounted')

    var ids_liste_ndf_lignes = [];

    for(const liste of Object.values(this.listes_sur_fiche.unitaire)){
        if(liste.liste_libre.type_element === 'note_de_frais_lignes')
            ids_liste_ndf_lignes.push(liste.liste_libre.id);
    }

    ids_liste_ndf_lignes.forEach((id_liste_ndf_lignes) => {

        this.$on('enregistrement_liste_' + id_liste_ndf_lignes, async (donnees) => {

            this.formulaires_ndf.forEach(async (nom_formulaire) => {

                await this.$refs[nom_formulaire].actualisation_modele();
                this.$set(this.$root, 'note_de_frais', this.$refs[nom_formulaire].element);
            });

            this.actualiser_montants_tva();
        });
    });

    this.$on('enregistrement_formulaire',(parametres) => {
        for(ref in this.$refs){
            if(ref.includes('liste_libre_'))
                this.$refs[ref].actualiser();
        }
    });

    this.$on('element_comptabilise', () => {
        this.note_de_frais.comptabilisee = 1;
    });
@endpush