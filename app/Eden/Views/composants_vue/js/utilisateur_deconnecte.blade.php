<script>
const utilisateur_deconnecte = Vue.component('utilisateur-deconnecte', {
    template: `
            <div v-if="utilisateur_deconnecte">
				<transition name="modal">
					<div class="modal-mask modal_deconnection">
						<div class="modal-dialog" role="document">
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title">
										@traduction('composant.utilisateur_deconnecte.titre')
									</h5>
								</div>
								<div class="modal-body">
									@traduction('composant.utilisateur_deconnecte.message')
									<a :href="this.$root.moi.id > 0 ? '/eden/login' : '/extranet/login'" target="_blank" class="bouton_reconnexion">
										@traduction('composant.utilisateur_deconnecte.bouton')
									</a>
								</div>
							</div>
						</div>
					</div>
				</transition>
			</div>
       `,
    data:function(){
        return {
            utilisateur_deconnecte: false,
        }
    },
    methods:{
        verification_connexion : function(){

            $.post({
                url : '/eden/verification_connexion',
                dataType:'json',
                data: {
                    id: this.$root.moi.id ?? this.$root.moi_extranet.id,
                }
            }).done((reponse) => {

                if(reponse.statut == 2)
                    document.location.reload();
                else
                    this.utilisateur_deconnecte = reponse.statut == 0 ? true : false;
            });
        },
    },
    mounted: function(){

        document.addEventListener('visibilitychange',() => {
            if(document.visibilityState === 'visible')
                this.verification_connexion();
        });

    },
});
</script>