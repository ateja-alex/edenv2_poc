<script>
const champ_file = Vue.component('champ-file', {
    template: `<div :class="'upload_champ_fichier ' + (mode_compact ? 'mode_compact' : '')">
            <i
                v-if="valeur"
                class="fa fa-times supprimer_piece_jointe"
                @click="supprimer_fichier()"
            ></i>
            <div class="upload_champ_fichier_contenu">
                <div class="upload_champ_fichier" v-show="afficher_input_file">
                    <div class="css_champ_file_comp" @click="$refs.file.click()" v-show="champ_disabled != 1">
                        <span v-html="'Importer ' + (nom_sql == null ? 'une image' : 'un(e) '+$root.traduction('champs_libres.' + type_element + '.' + nom_sql + '.nom'))"></span>
                        <i class="fa fa-upload"></i>
                    </div>
                    <input type="file" ref="file" :accept="accept" :id="id_random" @change="enregistrer_fichier(this)" style="display: none;" />
                    <input type="hidden" :name="name" v-model="valeur" />
                </div>

                <div class="url_champ_fichier" v-show="type_champ == 'url'">
                    <input type="text" :name="name" :value="valeur" @change="input_fichier_externe" :placeholder="$root.traduction('composant.champ_file.url_du_fichier')">
                </div>
                <a
                    href="javascript:;"
                    @click="switchTypeUpload"
                    v-show="champ_disabled != 1 && !mode_compact"
                    class="upload_champ_fichier_changement_type"
                >
                    <i
                        class="fas fa-random"
                        data-toggle="tooltip"
                        data-placement="left"
                        :title="$root.traduction('composants.champ_file.changer_type_import')"
                    />
                </a>
            </div>

            <a class="css__lien" :href="'/storage/'+valeur" target="_blank" v-if="!!valeur && (si_pas_storage(valeur) && !si_externe(valeur))">@{{valeur}}</a>
            <a class="css__lien" :href="valeur" target="_blank" v-if="!!valeur && (si_storage(valeur) || si_externe(valeur))">@{{valeur}}</a>
            <i class="fa fa-times" style="margin-left: 10px; cursor: pointer;" @click="supprimer_fichier()" v-show="!!valeur && champ_disabled != 1 && !mode_compact"></i>
            <div
                class="preview_champ_fichier"
                v-if="!!valeur && (valeur.indexOf('.png') >= 0 || valeur.indexOf('.jpg') >= 0 || valeur.indexOf('.jpeg') >= 0 || valeur.indexOf('.PNG') >= 0 || valeur.indexOf('.JPG') >= 0 || valeur.indexOf('.JPEG') >= 0)"
            >
                <img :src="'/storage/'+valeur" :class="'css_img_champ_file css_img_champ_file_'+type_element+'_'+nom_sql" 
                    v-show="si_pas_storage(valeur) && !si_externe(valeur)" 
                    :style="hauteur_apercu !== null ? 'height:' + hauteur_apercu + 'px;' : 'max-width: 150px'" />
                <img :src="valeur" :class="'css_img_champ_file css_img_champ_file_'+type_element+'_'+nom_sql" 
                    v-show="si_storage(valeur) || si_externe(valeur)" 
                    :style="hauteur_apercu !== null ? 'height:' + hauteur_apercu + 'px;' : 'max-width: 150px'" />
            </div>
        </div>`,
   props: {

		valeur: {},
		type_element: '',
		name: '',
		nom_sql: '',
		accept: '',
		champ_disabled: '',
        hauteur_apercu: null,
        mode_compact: false,
	},
	data: function () {
		return {
			chemin_fichier: '',
            type_champ: 'upload',
            extension_type_accepte: {!! collect(\App\Eden\Variables::extension_fichier_accepte()) !!},
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

        afficher_input_file() {
            if(this.mode_compact)
                return !this.valeur;
            else
                return this.type_champ == 'upload';
        },
	},
	methods: {

		si_storage(valeur) {

			if(valeur === null)
				return false;

			if(valeur === undefined)
				return false;

			if(valeur.indexOf('storage/') > -1)
				return true;

			return false;
		},

		si_externe(valeur) {

			if(valeur === null)
				return false;

			if(valeur === undefined)
				return false;

			if(valeur.indexOf('http://') > -1)
				return true;

			if(valeur.indexOf('https://') > -1)
				return true;

			return false;
		},

		si_pas_storage(valeur) {

			if(valeur === null)
				return true;

			if(valeur === undefined)
				return true;

			if(valeur.indexOf('storage/') > -1)
				return false;

			return true;
		},

		enregistrer_fichier: function() {

			var formData = new FormData();

			var id_random = this.id_random;

			var fileSelect = document.getElementById(id_random);
			var file = fileSelect.files[0];

			var vue_contexte = this;

			formData.append('image', file, file.name);

            if(!this.extension_type_accepte.includes(file.type)){
                toastr.error(this.$root.traduction('messages.php.upload.type_non_valide'));
                fileSelect.value = "";
                return;
            }

            var xhr = new XMLHttpRequest();
            xhr.responseType = 'json';

			xhr.open('POST', "{{ route('bibliotheque.upload_fichier', [], false) }}", true);

			// Set up a handler for when the request finishes.
			xhr.onload = () => {
				if (xhr.status === 200) {

                    if(xhr.response.erreur){
                        toastr.error(xhr.response.message);
                        return;
                    }

					vue_contexte.chemin_fichier = xhr.response.lien_fichier;

					vue_contexte.$emit('input', xhr.response.lien_fichier);
				}
				else {

                    toastr.error(vue_contexte.$root.traduction('composant.champ_file.erreur_inattendue'));
				}
			};

			xhr.send(formData);
		},

		async supprimer_fichier() {

            if(!await confirm_eden(this.$root.traduction('composant.champ_file.confirmation_suppression_fichier')))
                return false;

			this.valeur = '';

			this.$emit('input', '');

		},

        input_fichier_externe($event) {
            this.$emit('input', $event.target.value);
        },

        switchTypeUpload() {
            this.type_champ = this.type_champ == "upload" ? "url" : "upload";
        }
	},
	created: function() {

		this.chemin_fichier = this.valeur;

        this.type_champ = this.si_externe(this.valeur) ? "url" : "upload";
	},
    watch: {

        valeur : function (nouvelle_valeur, ancienne_valeur){

            var vue_instance = this;

            if(nouvelle_valeur == "" && ancienne_valeur != ""){

                this.chemin_fichier = "";

                $('#' + vue_instance.id_random).val('');

            }

        }
    }

});
</script>
