@extends('eden::composants_vue.js.liste_libre')

@push('donnees_pour_vuejs_methods')

     enregistrer_stock_inventaire_reel : async function(event,stocks_sans_conditionnement = false){

        var lignes_mise_a_jour = {};

        var vue_composant = this;

        $(event.target).parents('table').find('.input_stock_inventaire_reel').each(function(){

            var valeur = $(this).val();
            var id_ligne = $(this).attr('id_ligne');

            if(!isNaN(parseFloat(valeur)))
                lignes_mise_a_jour[id_ligne] = valeur;
        });

        if(Object.values(lignes_mise_a_jour).length == 0)
            return false;

        if(!await confirm_eden())
            return false;

        loading(true);

        $.post({
            url: "{{route('stocks.ajustement_stock', [], false)}}",
            dataType:"json",
            data:{
                lignes: lignes_mise_a_jour,
                stocks_sans_conditionnement: stocks_sans_conditionnement,
            }
        }).done(function(){

            loading(false);

            $(event.target).parents('table').find('.input_stock_inventaire_reel').each(function(){
                $(this).val('');
            });

            vue_composant.$emit('actualisation');
        });
    },
@endpush
