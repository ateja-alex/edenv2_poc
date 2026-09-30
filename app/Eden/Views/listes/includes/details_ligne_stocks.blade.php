@include('eden::listes.js')
var composant = {
    template:`<div :id="'detail_'+id_random">
        <input name="id_liste_parent" value="{{$id_liste_parent}}" type="hidden">
        @include('eden::listes.includes.liste_tableau_standard')
        @yield('informations_supplementaire')
        @stack('informations_supplementaire')
        </div>`,
    name: 'detail-ligne-{{$id_element}}',
    data:function(){
        return{
            liste : {!! collect($donnees_liste) !!},
            mode_parametrage : {!! mode_parametrage() ? 'true' : 'false' !!},
            filtres_pour_fiche:{!! collect($filtres_pour_fiche) !!},
            id_liste:{{$donnees_liste['liste_id']}},
            detail_liste_element_id:{{$id_element}},
        }
    },
    methods:{
        @stack('donnees_pour_vuejs_methods')
        @yield('donnees_pour_vuejs_methods')
        enregistrer_stock_inventaire_reel : async function(event){

            var lignes_mise_a_jour = {};

            var vue_composant = this;

            $(event.target).parents('#detail_'+this.id_random).find('.input_stock_inventaire_reel').each(function(){

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
                }
            }).done(function(){

                loading(false);

                $(event.target).parents('#detail_'+vue_composant.id_random).find('.input_stock_inventaire_reel').each(function(){
                    $(this).val('');
                });

                vue_composant.$parent.$emit('actualisation');
            });
        },
    },
    mounted:function(){
        @stack('donnees_pour_vuejs_mounted')
        @yield('donnees_pour_vuejs_mounted')
        this.$root.$on('enregistrer_stock_inventaire_reel_dans_detail',(event) => {
            this.enregistrer_stock_inventaire_reel(event);
        });
    },
    computed:{
        id_random: function() {
			length = 15;
			var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz'.split('');
			if (! length) {
				length = Math.floor(Math.random() * chars.length);
			}
			var str = '';
			for (var i = 0; i < length; i++) {
				str += chars[Math.floor(Math.random() * chars.length)];
			}
			return str;
		},
        @stack('donnees_pour_vuejs_computed')
        @yield('donnees_pour_vuejs_computed')
    }
};
