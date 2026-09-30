<script>
const menus = Vue.component('menus', {
    template: `<div>
        <div v-show="type_menu == 1">
            @include('eden::templates.menus_deployer')
        </div>
        <div v-cloak v-show="type_menu == 2" class="fixed-sidebar-left "  style="background-color: var(--background_menus);top:3.5rem;">
            @include('eden::templates.menus_replier')
        </div>
        <div v-show="type_menu == 'app'">
            @include('eden::templates.menus_app')
        </div>
	</div>`,
   props: {
		type_menu: '',
        superadmin: false,
        maquette_nom_application : '',
        droits_non_acces : {
            type : Array,
            default : function(){
                return [];
            }
        }
	},
	data: function () {
		return {
            menus : {!! collect($menus_eden) !!},
            menu_deployer : null,
            replier_sous_menu: 1,
		}
	},
    methods: {
        @yield('donnees_pour_vuejs_methods')
        @stack('donnees_pour_vuejs_methods')

        collapse_sous_menu(cle_menu){

            this.replier_sous_menu = 0;

            if(this.menu_deployer == cle_menu) {
                this.menu_deployer = null;
            }
            else
                this.menu_deployer = cle_menu;
        },
    },

    computed : {

        menus_a_afficher : function(){

            var droits_non_acces = this.droits_non_acces;

            var menus_a_afficher = Object.values(this.menus).filter((menu) => {

                var droits_non_acces_menu = droits_non_acces[menu.type_element] ?? [];
                
                return !menu.desactive && !droits_non_acces_menu.includes(menu.id.toString());
            });

            var menus_filtrer = [];

            for(index_menu in menus_a_afficher){

                var menu = menus_a_afficher[index_menu];

                if(menu.route == null){
                    menu.sous_menus = Object.values(menu.sous_menus).filter((menu) => {

                        var droits_non_acces_sous_menu = droits_non_acces[menu.type_element] ?? [];

                        return !menu.desactive && !droits_non_acces_sous_menu.includes(menu.id.toString());
                    });

                    if(menu.sous_menus.length > 0)
                        menus_filtrer.push(menu);
                }
                else
                    menus_filtrer.push(menu);
            }

            return menus_filtrer;
        },

        url_actuel : function(){
            return window.location.pathname;
        }
    },
    mounted:function(){

        for(index_menu in this.menus_a_afficher){

            var menu = this.menus_a_afficher[index_menu];

            if(menu.sous_menus){
                var routes_utilises = menu.sous_menus.map((sous_menu) => {return sous_menu.route});

                if(routes_utilises.includes(this.url_actuel))
                    this.menu_deployer = index_menu;
            }
        }

        var vue_contexte = this;

        $(document).on("mouseleave"," .fixed-sidebar-left", function(e) {
            vue_contexte.replier_sous_menu = 1;
        });

         $(document).on("mouseenter"," .fixed-sidebar-left", function(e) {
            vue_contexte.replier_sous_menu = 0;
        });
    },
});

</script>