<script>
const bouton_creation_pour_champ = Vue.component('bouton-creation-pour-champ', {
    template: `<div class="bouton_creation_pour_champ">
                    <div @click="creer_a_la_volee_select_vuejs()">
                        <slot name="bouton">
                            <i class="fas fa-plus css_pointer css_input_ajout_selection_element css_background_couleur_primaire"></i>
                        </slot>
                    </div>
                    <div :id="'stack_modale_'+this.id_random">
                        <template v-if="creation_a_la_volee_en_cours">
                            <transition name="modal"  >
                                <div class="modal-mask" style="position: fixed;z-index: 1059;" >
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">@traduction('composant.bouton_creation_pour_champ.gestion_des_elements')</h5>
                                            </div>
                                            <div class="modal-body css_form js_selection_element" >
                                                <formulaire ref="formulaire" :nom_formulaire="type_element_ajax" contexte="creation_volee_"></formulaire>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" @click="creation_a_la_volee_en_cours = false">@traduction('composant.bouton_creation_pour_champ.fermer')</button>
                                                <div v-if="enregistrement_disponible" class="btn btn-primary" @click="enregistrer_a_la_volee_select_vuejs()">@traduction('composant.bouton_creation_pour_champ.enregistrer')</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </transition>
                        </template>
                    </div>
				</div>`,
                props: {
		type_element_ajax: '',
		type_element: '',
		nom_sql: '',
        valeurs_par_defaut : {
			type: Object,
			default : () => {
				return {};
			}
		},
	},
	data: function () {
		return {
			@yield('donnees_pour_vuejs_data')
			@stack('donnees_pour_vuejs_data')
			creation_a_la_volee_en_cours: false,
			enregistrement_disponible: true,
		}
	},
	computed: {
		@yield('donnees_pour_vuejs_computed')
		@stack('donnees_pour_vuejs_computed')
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
		}
	},
	methods: {
		@yield('donnees_pour_vuejs_methods')
		@stack('donnees_pour_vuejs_methods')
		// on enregistre l'élément créé à la volée
		creer_a_la_volee_select_vuejs: function() {

            this.$on('formulaire_charger',(formulaire) => {
				for(cle of Object.keys(this.valeurs_par_defaut)){

					this.$refs.formulaire.element[cle] = this.valeurs_par_defaut[cle];
				}
			});

			this.creation_a_la_volee_en_cours = true;

            this.$nextTick(() => {
                if($('.bouton_creation_pour_champ #stack_modale_'+this.id_random).length > 0)
                    $('#stack_modales_composants').append($('.bouton_creation_pour_champ #stack_modale_'+this.id_random));
            });
			this.$forceUpdate();
		},
		// on enregistre l'élément créé à la volée
		enregistrer_a_la_volee_select_vuejs: async function() {

            // On afficher le loader
            loading(true);

            var donnees = await this.$refs.formulaire.enregistrer();

            if(donnees.retour === true) {
                this.creation_a_la_volee_en_cours = false;
				this.$emit('rechargement_options',donnees.element.id);
				this.$emit('enregistrement',donnees.element);
            }

			loading(false);
		},
	},
	mounted: function() {
		@yield('donnees_pour_vuejs_mounted')
		@stack('donnees_pour_vuejs_mounted')
		var instance = this;
		instance.$on('enregistrement_disponible',function(enregistrement_disponible){
			instance.enregistrement_disponible = enregistrement_disponible;
		});

	},
	watch: {
		@yield('donnees_pour_vuejs_watch')
		@stack('donnees_pour_vuejs_watch')
	}
});
</script>