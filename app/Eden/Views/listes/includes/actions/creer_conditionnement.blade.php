@extends('eden::listes.includes.actions.action_en_masse_generique',['action' => 'creer_conditionnement'])

@section('texte_modale_creer_conditionnement')
    <formulaire ref="formulaire_creation_conditionnement" nom_formulaire="conditionnement" :options="{type_element_formulaire_parent : 'article'}"></formulaire>
    </br></br>
@endsection

@push('donnees_pour_vuejs_methods')

    creer_conditionnement_elements_selectionnes: function() {

        var composant = this;

        var type_element = composant.liste.type_element;

        // on va chercher les ID des éléments sélectionnés
        var ids = composant.liste.lignes_selectionnees;

        var conditionnement = composant.conditionnement;

        if(ids.length == 0)
            return;

        loading(true);

        $.post({

            url: "{{ route('article.creer_conditionnement_en_masse', [], false) }}",
            data: {

                type_element: type_element,
                ids: ids,
                conditionnement: this.$refs.formulaire_creation_conditionnement.element,
            }
        }).always(async (retour) =>  {

            // on cache le loader
            loading(false);

            this.modale_creer_conditionnement = false;

            composant.deselectionner_toutes_les_lignes();

            // on actualise
            composant.actualisation_filtres();

            if(retour.retour === false) {

                await erreur(retour.message);
                return;
            }

        });
    },

    creer_conditionnement_tous_elements: async function() {

        var composant = this;

        // on récupère les ids...
        loading(true);

        await composant.actualisation_filtres(true);

        var ids = composant.liste.ids;

        var type_element = composant.liste.type_element;

        var conditionnement = composant.conditionnement;

        $.post({

            url: "{{ route('article.creer_conditionnement_en_masse', [], false) }}",
            data: {

                type_element: type_element,
                ids: ids,
                conditionnement: this.$refs.formulaire_creation_conditionnement.element,
            }
        }).always(async (retour) => {

            loading(false);

            this.modale_creer_conditionnement = false;

            composant.deselectionner_toutes_les_lignes();

            // on actualise
            composant.actualisation_filtres();

            if(retour.retour === false) {

                await erreur(retour.message);
                return;
            }
        });

    },

@endpush
