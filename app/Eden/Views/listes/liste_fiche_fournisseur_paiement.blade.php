@extends('eden::listes.liste_paiement')

@push('js_a_inserer')

    if(vue_instance[type_element].montant_en_negatif == undefined){
        vue_instance[type_element].montant = vue_instance[type_element].montant * -1;
        vue_instance[type_element].montant_en_negatif = true;
    }

@endpush

