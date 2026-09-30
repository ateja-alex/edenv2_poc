<script>
    const champ_mdp_systeme = Vue.component('champ-mdp-systeme', {
        template: `
		<div class="row composant_mot_de_passe">
			<div class="col-sm-2">
                <span v-html="$root.traduction(index_traduction+'.nom')"></span>
                <span @click="changer_affichage_mot_de_passe()">( <i class="far fa-eye"></i> )</span>
            </div>
                <div class="col-sm-4 container_mot_de_passe_et_generation">
                    <button class="btn btn-secondary generation_mdp" type="button" @click="genere_mot_de_passe()">
                        <i class="fas fa-random"></i>
                    </button>
                    <div class="container_mot_de_passe">
                        <Password ref="input_mot_de_passe" v-model="mot_de_passe_local" :name="name" input-style="width:100%;" :placeholder="modele[nom_sql] == null ? '{{traduction('champs_libres.champ_mdp_systeme.definir_mot_de_passe')}}' : '{{traduction('champs_libres.champ_mdp_systeme.changer_mot_de_passe')}}'" :disabled="lecture_seule">
                            <template #footer>
                                <div class="conditions_mot_de_passe_fort">
                                    <span>
                                        <i :class="'fas fa-' + (mot_de_passe_local.match(/[a-z]/) ? 'check' : 'times')"></i> {{ traduction('interface.renouvellement_mot_de_passe.minuscule') }}
                                    </span>
                                    <span>
                                        <i :class="'fas fa-' + (mot_de_passe_local.match(/[A-Z]/) ? 'check' : 'times')"></i> {{ traduction('interface.renouvellement_mot_de_passe.majuscule') }}
                                    </span>
                                    <span>
                                        <i :class="'fas fa-' + (mot_de_passe_local.match(/[0-9]/) ? 'check' : 'times')"></i> {{ traduction('interface.renouvellement_mot_de_passe.nombre') }}
                                    </span>
                                    <span>
                                        <i :class="'fas fa-' + (mot_de_passe_local.length >= 8 ? 'check' : 'times')"></i> {{ traduction('interface.renouvellement_mot_de_passe.taille_minimale') }}
                                    </span>
                                </div>
                            </template>
                        </Password>
                    </div>
                </div>
			<div class="col-sm-2 libelle_confirmation_mot_de_passe">@traduction('interface.gestion_utilisateur_connecte.confirmation')</div>
			<div class="col-sm-4" style="display: grid;">
				<Password ref="input_verification_mot_de_passe" v-model="mot_de_passe_verif_local" :name="nom_sql + '_verification'" :feedback="false" placeholder="{{traduction('interface.reinitialisation_mot_de_passe_oublie.placeholder_confirmation_mot_de_passe')}}" :disabled="lecture_seule">
				</Password>
				<div class="help-block" v-if="mot_de_passe_verif_local != '' && mot_de_passe_local != '' && mot_de_passe_verif_local !== mot_de_passe_local">
					{{ traduction('messages.php.connexion.mdp_differents') }}
				</div>
			</div>
		</div>
        `,
        props: {
            modele: {},
            nom_sql: {
                type: String,
                default: '',
            },
            name: {
                type: String,
                default: '',
            },
            lecture_seule: {
                type: Boolean | Number,
                default: false,
            },
            index_traduction: {
                type: String,
                default: '',
            },
        },

        components: {
            'Password': password
        },

        data() {
            return {
                mot_de_passe_local: '',
                mot_de_passe_verif_local: '',
            };
        },

        methods : {

            genere_mot_de_passe: function(){
    
                var mot_de_passe_genere = '';
                var caractere_speciale = false;
                var lettre_min = false;
                var lettre_maj = false;
                var chiffre = false;
            
                // On boucle 8 fois
                for (let i = 0; i < 8; i++) {
            
                    var chiffre_hasard = Math.floor(Math.random() * 3) + 1;
                
                    // Si 1 -> lettre, 2 -> chiffre, 3 -> caractère spécial
                    if(chiffre_hasard == 1){
                
                        var lettre_au_hasard = this.lettre_au_hasard();
                        var chiffre_hasard_pour_majuscule = Math.floor(Math.random() * 2) + 1;
                
                        // Si 2 -> on met la lettre en maj
                        if(chiffre_hasard_pour_majuscule == 2){
                    
                            lettre_maj = true;
                            lettre_au_hasard = lettre_au_hasard.toUpperCase();
                        }
                        else{
                    
                            lettre_min = true;
                        }
            
                        mot_de_passe_genere = mot_de_passe_genere + lettre_au_hasard;
                    }
                    else if(chiffre_hasard == 2){
            
                        chiffre = true;
                        mot_de_passe_genere = mot_de_passe_genere + this.chiffre_au_harsard();
                    }
                    else if(chiffre_hasard == 3){
            
                        caractere_speciale = true;
                        mot_de_passe_genere = mot_de_passe_genere + this.caractere_au_harsard();
                    }
                }
            
                // On regarde si on a au moins min + maj + chiffre + caractere
                if(caractere_speciale != true || lettre_min != true || lettre_maj != true || chiffre != true)
                    this.genere_mot_de_passe();
                else{
                    this.mot_de_passe_local = mot_de_passe_genere;
                    this.mot_de_passe_verif_local = mot_de_passe_genere;
                }
            },

            // Une lettre de l'alphabet
            lettre_au_hasard: function(){

                const alphabet = "abcdefghijklmnopqrstuvwxyz"
            
                var lettre_hasard = alphabet[Math.floor(Math.random() * alphabet.length)]
            
                return lettre_hasard;
            },

            // Chiffre de 0 à 9
            chiffre_au_harsard: function(){

                return Math.floor(Math.random() * 10);
            },

            // Une caractère spécial au hasard
            caractere_au_harsard: function(){

                const caracteres = "@!_"
            
                var caractere_hasard = caracteres[Math.floor(Math.random() * caracteres.length)]
            
                return caractere_hasard;
            },

            changer_affichage_mot_de_passe : function(){

                const input_mot_de_passe = this.$refs.input_mot_de_passe.$refs.input.$el;
                const input_verification_mot_de_passe = this.$refs.input_verification_mot_de_passe.$refs.input.$el;
            
                if (input_mot_de_passe.type === 'password') {
                    input_mot_de_passe.type = 'text';
                    input_verification_mot_de_passe.type = 'text';
                } else {
                    input_mot_de_passe.type = 'password';
                    input_verification_mot_de_passe.type = 'password';
                }
            },
        },

        watch: {
            mot_de_passe_local(val) {
                if(val !== '') this.modele[this.nom_sql] = val;
            },
        }
    });
</script>