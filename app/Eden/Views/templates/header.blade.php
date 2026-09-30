<!-- Header content -->
<nav class="eden_navbar navbar-dark  fixed-top" id="mainNav" style="background-color: var(--background_navbar);border-bottom: solid var(--background_navbar) 1px">

    @include('eden::templates.navbar_haut')

    <?php temps_execution('template après navbar_haut'); ?>

    <menus :type_menu="type_menu" @if(super_admin()) :superadmin="'1'" @endif :droits_non_acces='{!! collect(session()->get('cache.droits_profils.divers_non_acces') ?? []) !!}'></menus>

    <?php temps_execution('template après composant menu'); ?>

    @include('eden::templates.info_user_header')

    <?php temps_execution('template après info_user_header'); ?>

</nav>

@push('donnees_pour_vuejs_data')

    @if(parametre_utilisateur('type_menu') !== null)
        type_menu: {!! parametre_utilisateur('type_menu') !!},
    @else
        type_menu: 0,
    @endif
@endpush

@push('donnees_pour_vuejs_methods')

	modifier_type_menu(){

        var vue_instance = this;

        var type_menu = vue_instance.type_menu;

        // on enregistre les infos du champ libre
        $.post({

            url: 'eden/modifier_type_menu',
            dataType: "json",
            data:{ type_menu : type_menu }
        }).done(function(donnees) {

             jQuery('.content-wrapper').animate({ "margin-left": donnees.nouvelle_taille_contenu });

            vue_instance.type_menu = donnees.nouveau_type_menu;
        });
    },

@endpush


