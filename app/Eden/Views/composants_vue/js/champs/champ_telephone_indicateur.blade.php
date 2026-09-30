<script>
const champ_telephone_indicateur = Vue.component('champ-telephone-indicateur', {
    template: `<div style="display: flex;flex-direction: column;">
                    <div style="display: flex; align-items: center;min-width: max-content;">
                        <a class="fas fa-phone css_pointer css_input_ajout_selection_element css_background_couleur_primaire"
                            style="padding: 10px; height:30px;text-align: center;border: 1px solid #d4d4d4;border-right:0;"
                            :href="'tel:' + modele[nom_sql]"
                            v-show="'telephone_'+id_random"
                        ></a>
                        <span style="width:100%" :id="'information_telephone_'+id_random">
                            <input :name="name" type="hidden" v-model="modele[nom_sql]"/>
                            <input :disabled="lecture_seule" type="tel" style="height: 30px;min-width: 150px;field-sizing: content;border: 1px solid #d4d4d4;" :id_random="id_random" :id="'telephone_'+id_random" :title="modele[nom_sql] != undefined && modele[nom_sql] != '' ? modele[nom_sql] : nom" autocomplete="off"/>
                        </span>
                    </div>
                    <span class="css_pointer" style="color:red;display:flex;gap:0 10px;align-items: center;margin-top: 5px;" v-if="afficher_alerte_invalide">
                        <i class="fas fa-exclamation-triangle" style="color:red"></i>
                        @traduction('composant.champ_telephone_indicateur.numero_invalide')
                    </span>
        </div>`,
   props: {

		modele: {},
		nom_sql: '',
        name: '',
        nom: '',
        lecture_seule: {
            type: Boolean | Number,
            default : false,
        },
	},
	data: function () {

		return {

			dernier_id_recupere: false,
            placeholder : '',
            afficher_alerte_invalide : false,
		}
	},
	computed: {

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

	},

	methods: {

    	gestion_numero_telephone : function(){

			var id_random = this.id_random;

			var input = document.querySelector('#telephone_'+id_random);
            this.placeholder = input.placeholder;

			var module_numero_de_telephone = window.intlTelInputGlobals.getInstance(input);

			if (module_numero_de_telephone !== undefined) {

                if (module_numero_de_telephone.isValidNumber() == false && module_numero_de_telephone.getNumber() != '' && $("#information_telephone_" + this.id_random).children("a").length == 0) {
					this.afficher_alerte_invalide = true;
				} else if (module_numero_de_telephone.isValidNumber() == true || module_numero_de_telephone.getNumber() == '') {
					this.afficher_alerte_invalide = false;
				}

				this.recuperer_numero_de_telephone();
			}
		},

		recuperer_numero_de_telephone(){
			var composant = this;

			var id_random = this.id_random;
			var input = document.querySelector('#telephone_'+id_random);
			var module_numero_de_telephone = window.intlTelInputGlobals.getInstance(input);

			composant.modele[composant.nom_sql] = module_numero_de_telephone.getNumber();

			this.$forceUpdate();

		},
	},

	mounted: function() {

		var id_random = this.id_random;

		var input = document.querySelector("#telephone_"+id_random);

		var composant = this;

		var module_numero_de_telephone = window.intlTelInput(input, {
			initialCountry: "auto",
			utilsScript: "/eden/vendors/intl-tel-input/js/utils.js",
            autoPlaceholder: "off",
			geoIpLookup: function(success, failure) {
				success("fr");
			},
		});

		module_numero_de_telephone.promise.then(function() {

			if(composant.modele[composant.nom_sql] != null) {
				module_numero_de_telephone.setNumber(composant.modele[composant.nom_sql]);
			}

			composant.gestion_numero_telephone();

			$("#telephone_"+id_random).change(function() {
				composant.gestion_numero_telephone();
			});

			$("#telephone_"+id_random).on( "countrychange", function() {
				composant.gestion_numero_telephone();
			});

		});

	},

	watch: {

		modele: {
			handler: function (newVal, oldVal) {

				if (newVal['id'] == "" || this.derniere_id_recuperee != newVal['id'] ) {

					var id_random = this.id_random;

					var input = document.querySelector("#telephone_"+id_random);

					var module_numero_de_telephone = window.intlTelInputGlobals.getInstance(input);

					var composant = this;

					input.value = null;

					module_numero_de_telephone.destroy();

						module_numero_de_telephone = window.intlTelInput(input, {
							utilsScript: "/eden/vendors/intl-tel-input/js/utils.js",
							initialCountry: "fr",
                            autoPlaceholder: "off",
						});

						module_numero_de_telephone.promise.then(function () {

							if(composant.modele[composant.nom_sql] != null) {
								module_numero_de_telephone.setNumber(composant.modele[composant.nom_sql]);
							}

							composant.gestion_numero_telephone();

							$("#telephone_"+id_random).change(function() {
								composant.gestion_numero_telephone();
							});

							$("#telephone_"+id_random).on( "countrychange", function() {
								composant.gestion_numero_telephone();
							});
						});

				}

				this.dernier_id_recupere = newVal['id'];

				if(newVal['id'] == "")
					this.dernier_id_recupere = false;

			},
			deep: true
		},
	},
});
</script>
