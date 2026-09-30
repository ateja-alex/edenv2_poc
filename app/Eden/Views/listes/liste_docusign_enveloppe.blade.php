@extends('eden::composants_vue.js.liste_libre')

@section('options_modale_edition')

    <button type="button" class="btn btn-secondary css_btn_responsive" @click="retour_a_la_liste();">
        @traduction('interface.listes.fermer')
    </button>
    <button type="button" class="btn btn-primary css_btn_responsive" @click="enregistrer_dans_liste()">
        Envoyer pour signature
    </button>
@endsection