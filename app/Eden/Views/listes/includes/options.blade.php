var composant = {
    name: 'options-'+ligne.id,
    template:`<div>
        @foreach($options as $option)
            <template v-if="{!! $mobile ? "liste.modele_liste_libre.options_mobile != null && liste.modele_liste_libre.options_mobile.includes('".$option."')" : "true" !!} && 
                {!! in_array($option,['apercu','supprimer','dupliquer','retablir']) ? 
                    "(liste.vue_sql == null || (liste.vue_sql.table_par_defaut != null && ligne.element.modification_possible))" : "true" !!}">
                @includeFirst(
                    array_merge(
                        ['eden::listes.includes.options.'.$type_element_options.'.'.$option],
                        (
                            in_array($type_element_options,\App\Eden\Variables::$documents_gescom)
                            ?
                            ['eden::listes.includes.options.document.'.$option] : []
                        ),
                        (
                            in_array($type_element_options,\App\Eden\Variables::$documents_gescom_lignes)
                            ?
                            ['eden::listes.includes.options.document_lignes.'.$option] : []
                        ),
                        ['eden::listes.includes.options.'.$option]
                    )
                )
            </template>
        @endforeach
        <div ref="modales">
            @stack('modales')
        </div>
    </div>`,
    props: ['ligne'],
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
