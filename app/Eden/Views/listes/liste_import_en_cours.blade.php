@extends('eden::composants_vue.js.liste_libre')

@push('donnees_pour_vuejs_mounted')

    $('body').on('dblclick', '#affichage_liste_{{$id_liste}} .js_liste_ligne_selectionnable', function() {

        var import_en_cours_id = $(this).attr('element_id');

        window.location.href = '/eden/import_sur_mesure/'+import_en_cours_id;
    });

@endpush
