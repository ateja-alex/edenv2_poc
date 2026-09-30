@include('eden::listes.js')
var composant = {
    template:`<div :id="'detail_'+id_random">
        @include('eden::listes.includes.liste_tableau_standard')
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
    },
    mounted:function(){
        @stack('donnees_pour_vuejs_mounted')
        @yield('donnees_pour_vuejs_mounted')
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
