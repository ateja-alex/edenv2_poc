<script>
const formulaire_fiche = Vue.component('formulaire-fiche', {
    template: `<div>
            <div class="card mb-3 css_bloc_formulaire_fiche">
			<div class="card-header d-flex align-items-center">
				<h4 style="width:100%" class="css_titre_formulaire_fiche_element">

					<slot name="titre"></slot>

                    <template v-if="$root.moi_extranet == null">
                        <span class="fa fa-bell" @click="active_notification()" v-show="notification_activee == 1" style="cursor:pointer;color: #f6bd1b; margin-left: 15px;"></span>
                        <span class="fa fa-bell" @click="active_notification()" v-show="notification_activee == 0 || notification_activee == '' || notification_activee == undefined" style="cursor:pointer;color: #d5cfcf; margin-left: 15px;"></span>
                    </template>
					<template v-if="mode_parametrage == 1 ">
						<a :href="'/eden/parametrage/table_libre/zoom/'+type_element"
						 class="css_bouton_modifier_liste_primaire">
							<i class="fas fa-cog"></i> Paramétrer "@{{ type_element }}"
						</a>
					</template>
				</h4>
			</div>
			<div class="card-body">
               <formulaire ref="formulaire" :nom_formulaire="type_element" contexte="fiche_"></formulaire>
			</div>
		</div>
        </div>`,
   props: {

		route: {},
		type_element: '',
		element_id: '',
		mode_parametrage : '',
	},
	data: function () {
		return {
			langue: 1,
			ancienne_langue: 1,
			langues_telechargees: [],
			donnees_par_langue: [],
			donnees_traduites: [],
			notification_activee: '',
		}
	},
	methods: {

		active_notification: function() {

			var type_element = this.type_element;
			var element_id = this.element_id;

			var composant = this;

			$.ajax({
				method: 'POST',
				url: '{{ route('base_eden.notifications.activer_suivi_element', [], false) }}',
				dataType: "json",
				data: {

					type_element: type_element,
					element_id: element_id
				}
			}).done(function(notification_activee) {

				composant.notification_activee = notification_activee;
			});
		},
	},
    mounted : function(){

        this.$once('formulaire_charger',()=>{
            this.$refs.formulaire.element = this.$parent[this.type_element];
        });

        this.$root.$on('trigger_enregistre_formulaire_fiche',() => {
            this.enregistrer();
        });
    },
	created: function() {

		// on récupère la notification
		var type_element = this.type_element;
		var element_id = this.element_id;

		var composant = this;

		$.ajax({
			method: 'POST',
			url: '{{ route('base_eden.notifications.recuperer_suivi_element', [], false) }}',
			dataType: "json",
			data: {

				type_element: type_element,
				element_id: element_id
			}
		}).done(function(notification_activee) {

			//console.log(notification_activee);

			composant.notification_activee = notification_activee;
		});
	},
});
</script>
