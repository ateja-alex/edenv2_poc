var composant = {
    name: 'actions-'+vue_instance.id_liste,
    template:`<div class="dropdown dropdown_hover bouton_actions_liste">
        <div class="css_action_icon secondaire" :title="$root.traduction('interface.listes.actions')">
            <i class="fas fa-grip-horizontal"></i>
        </div>
        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            @foreach($actions as $vue => $action)
                {!! $action !!}
            @endforeach
        </div>
        <div ref="modales">
            @foreach($actions as $vue => $action)
                @includeIf('eden::listes.includes.actions.'.$vue)
            @endforeach
            @stack('modales')
        </div>
    </div>`,
    data:function(){
        return Object.assign({}, vue_instance.$data, vue_instance.$props, {
            @stack('donnees_pour_vuejs_data')
        });
    },
    methods: Object.assign(
        Object.keys(vue_instance.$options.methods).reduce((methodes, nom) => {
            methodes[nom] = vue_instance.$options.methods[nom].bind(vue_instance);
            return methodes;
        }, {}),
        {
            @stack('donnees_pour_vuejs_methods')
        }
    ),
    mounted: function(){
        @stack('donnees_pour_vuejs_mounted')

        $('#stack_modales_composants').append($(this.$refs.modales));
    },
};
