@php
$filtres = service('echange')->filtres_timeline();
@endphp

@foreach($filtres as $nom_filtre => $informations)
    <span style="display:inline;font-size:12px;" class="css_conteneur_popover_filtre conteneur_filtres">
        <span
            class="css_dropdown_filtres js_dropdown_filtres_calendrier"
            :class="[retourne_filtre_actif('{{ $nom_filtre }}') ? 'bg-dark text-white' : 'bg-white text-dark']"
                    style="border:solid 1px" @click="filtre_actif = ('$nom_filtre' == filtre_actif) ? null : '$nom_filtre'">

                    @php $index_traduction_champ = $informations['champ']->index_traduction.'.nom' @endphp
                    @traduction('{{$index_traduction_champ}}') <i class="fas fa-angle-down"></i>
        </span>
        <div class="css_block_popover_filtre js_block_popover_filtre_timeline" v-show="filtre_actif == '$nom_filtre'">
            <component ref="filtre_{{$nom_filtre}}" :is="affichage_filtre_{{$nom_filtre}}" />
        </div>
        <i class="fas fa-times" style="cursor: pointer;" v-if="retourne_filtre_actif('{{$nom_filtre}}')" @click="effacer_filtre($event)"></i>
    </span>
@endforeach

<form id="filtres_calendrier" style="display:none">
</form>

@include('eden::includes.fonctionnement_filtres')

@push('donnees_pour_vuejs_data')
    filtres: {},
    filtres_erp: {},
    filtre_actif: '',
@endpush

@push('donnees_pour_vuejs_methods')

    retourne_filtre_actif: function(nom_sql) {

        var valeurs = this.filtres_erp[nom_sql];

        if(valeurs === undefined)
            return false;

        else if(typeof valeurs === 'array')
            return valeurs.length > 0;

        else if(typeof valeurs === "object"){

            var retour = false;
            $.each(valeurs,function(index,valeur_filtre){
                if(valeur_filtre !== '')
                    retour = true;
            });

            return retour;
        }

        else
            return false;

    },

    methode_actualisation : function(){

        @foreach($filtres as $nom_filtre => $informations)
            this.filtres_erp['{{$nom_filtre}}'] = this.$refs.filtre_{{$nom_filtre}}.valeur_filtre;
        @endforeach

        this.charge_donnees();

    },

@endpush

@push('donnees_pour_vuejs_computed')

    @foreach($filtres as $nom_filtre => $osef)

        affichage_filtre_{{$nom_filtre}}(){

            var vue_calendrier = this;

            if(this.filtres.{{$nom_filtre}} != undefined && this.filtres.{{$nom_filtre}}.affichage !== undefined){

                var filtre = this.filtres.{{$nom_filtre}};
                var nom_filtre = 'echange_'+vue_calendrier.filtres.{{$nom_filtre}}.nom_sql;
                var type_element_filtre = 'echange';

                return {
                    template:this.filtres.{{$nom_filtre}}.affichage,
                    name: 'filtre_'+nom_filtre,
                    methods:this.$options.methods,
                    props : {
                        @stack('filtres_props')
                        @yield('filtres_props')
                    },
                    data: function () {
                        return {
                            type_element_filtre : type_element_filtre,
                            nom_filtre : nom_filtre,
                            filtre : filtre,
                            @stack('filtres_data')
                            @yield('filtres_data')
                        }
                    },
                    created:function(){

                        @stack('filtres_created')
                        @yield('filtres_created')
                    },
                    mounted : function(){

                        @stack('filtres_mounted')
                        @yield('filtres_mounted')

                    },
                }
            }
            else{
                return {
                    template:'<div></div>',
                    methods:this.$options.methods,
                }
            }
        },

    @endforeach

@endpush

@push('action_a_executer_suite_actualisation')

    if(initialisation === true)
        composant.filtres = retour.filtres_a_envoyer.informations;

@endpush

@push('donnees_pour_vuejs_mounted')

    var vue_composant = this;

    $('.js_datepicker').datepicker({
        format: "dd/mm/yyyy",
        language: 'fr'
    });

     $('body').on('click', '.js_dropdown_filtres_calendrier', function () {
         var this_popover = $(this).next('.js_block_popover_filtre_timeline');
         $('.js_block_popover_filtre_timeline').not(this_popover).removeClass('deploy').hide('fast');
         this_popover.toggleClass('deploy').show('fast');
     });

     // Ferme le popover dès que l'on clique ailleurs
     $(document).on('click', function (e) {
         var target = $(e.target);
         var datepicker_class = ".day, .dow, .prev, .next, .month, .datepicker-months, .today, .datepicker-years, .year, .new, .clear, .datepicker-switch, .datepicker-days";
         if (!target.is($('.js_block_popover_filtre_timeline, .js_dropdown_filtres_calendrier').find('*').addBack()) && !target.is(datepicker_class)) {
             $('.js_block_popover_filtre_timeline').removeClass('deploy').hide('fast');
         }
     });

     $('body').on('click', '.js_block_popover_filtre_timeline span.js_filtre_timeline', function(){
         vue_composant.methode_actualisation();
     });

     $('body').on('click', '.js_block_popover_filtre_timeline div.js_filtre_timeline', function(){
         vue_composant.methode_actualisation();
     });

      $('body').on('change', '.js_block_popover_filtre_timeline select.js_filtre_timeline', function(){
         vue_composant.methode_actualisation();
     });

      $('body').on('change', '.js_block_popover_filtre_timeline input.js_filtre_timeline', function(){
         if($(this).hasClass('filtre_date_checkbox') || ($(this).hasClass('js_filtre_timeline') && $(this).hasClass('js_datepicker'))) {
            $(this).closest('.css_block_datepicker_popover').find('.filtre_date_checkbox').not(this).prop('checked', false);

            if($(this).hasClass('filtre_date_checkbox')){

                var tous_deselectionne = true;

                $(this).closest('.css_block_datepicker_popover').find('.filtre_date_checkbox').each(function() {
                      if(this.checked == true)
                        tous_deselectionne = false;
                });

                if(tous_deselectionne)
                    $(this).closest('.css_block_datepicker_popover').find('.filtre_date_checkbox').first().prop('checked', true);

            }
         }

         vue_composant.methode_actualisation();
     });

@endpush