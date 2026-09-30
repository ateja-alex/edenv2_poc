@php
    $parametres = ['action' => 'supprimer_documents'];
@endphp

@if(fonctionnalite('gescom_suppression_facture_valide') == 'empecher_suppression')
    @php
        $parametres['condition_affichage_bouton'] = 'verifie_facture_validee() == false';
    @endphp
    @section('texte_modale_supprimer_documents')
        <span v-if="verifie_facture_validee()" style="padding-left: 15px;">
            <h6> @traduction('interface.listes.supprimer_facture_impossible')</h6>
        </span>

        <br/><br/>
    @endsection
@endif

@include('eden::listes.includes.actions.js.js_supprimer', $parametres)

@push('donnees_pour_vuejs_methods')

    /**
    *
    * Vérifie que les factures ne soient pas validées
    *
    */
    verifie_facture_validee: function(){
        var ids = [];
        var lignes = [];
        var composant = this;

        var ids = composant.liste.lignes_selectionnees;
        var lignes = composant.liste.lignes;
        var tous_valide = false;

        lignes.forEach((facture) => {
            if (ids.includes(facture.id) && facture.element.valide) {
                tous_valide = true;
            }
        });

        return tous_valide;
    },

@endpush

