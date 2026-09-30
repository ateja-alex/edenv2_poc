<script>
const champ_colorpicker = Vue.component('champ-colorpicker', {
    template: `<div>
                   	<input type="color" :disabled="lecture_seule" :name="name" :id_random="id_random" :style="'background: '+modele[nom_sql]" v-model="modele[nom_sql]"/>
        </div>`,
   props: {

		modele: {},
		nom_sql: '',
        name: '',
		placeholder: '',
        lecture_seule : {
            type: Boolean | Number,
            default: false,
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
		}
	},
});
</script>