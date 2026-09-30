const bloc_suivi_jours_travailles = Vue.component('bloc-suivi-jours-travailles',{
    template: `
        <div class="corps">
            <label v-for="temps in ['matin','apres_midi']" :class="'bloc_horaire statut_'+(jour_travaille.jour_travaille[temps] == 1 ? 1 : 0)">
                <i class="far fa-clock"></i>
                <div class="contenu">
                    <span v-html="$root.traduction('composant.suivi_jours_travailles.'+temps)"></span>
                    <span style="font-weight: bold" v-html="$root.traduction('composant.suivi_jours_travailles.travaille.'+(jour_travaille.jour_travaille[temps] == 1 ? 1 : 0))"></span>
                </div>
                <input :disabled="jour_travaille.jour_travaille.statut > 0" @change="debounce_changement(jour_travaille)" v-model="jour_travaille.jour_travaille[temps]" type="checkbox" :true-value="1" :false-value="0">
            </label>
            <div class="validation_repos">
                <input :disabled="jour_travaille.jour_travaille.statut > 0" @change="debounce_changement(jour_travaille)" :id="'validation_repos_'+jour_travaille.date" type="checkbox" :true-value="1" :false-value="0" v-model="jour_travaille.jour_travaille.repos">
                <label :for="'validation_repos_'+jour_travaille.date">@traduction('composant.suivi_jours_travailles.repos')</label>
            </div>
            <div class="commentaire" ref="commentaire" v-if="jour_travaille.non_travaille || jour_travaille.jour_travaille.repos != 1 || jour_travaille.jour_travaille.matin != 1 || jour_travaille.jour_travaille.apres_midi != 1">
                <textarea v-model="jour_travaille.jour_travaille.commentaire" :placeholder="$root.traduction('composant.suivi_jours_travailles.commentaire.placeholder')" :disabled="jour_travaille.jour_travaille.statut > 0" @change="debounce_changement(jour_travaille)">
                </textarea>
                <div class="champ_obligatoire">*</div>
            </div>
        </div>
    `,
    props:{
        jour_travaille : {
            type:Object,
            default : function(){
                return {}
            }
        },
        parametres : {
            type:Object,
            default : function(){
                return {}
            }
        }
    },
    data:function(){
        return{
            debounce : null,
        }
    },
    methods:{
        debounce_changement:function (jour_travaille) {
            clearTimeout(this.debounce);
            this.debounce = setTimeout(() => { this.changement_valeur(jour_travaille); }, 1000);
        },

        changement_valeur : function(jour_travaille){

            this.$emit('enregistrement_valeur');

            var url = '{{ route('base_eden.element.creer','jour_travaille', false) }}';

            if(jour_travaille.jour_travaille.id > 0)
                url = "eden/element/jour_travaille/"+jour_travaille.jour_travaille.id+"/enregistrer";

            var donnees= {
                matin: jour_travaille.jour_travaille.matin,
                apres_midi: jour_travaille.jour_travaille.apres_midi,
                repos: jour_travaille.jour_travaille.repos,
                date: jour_travaille.date,
                commentaire: jour_travaille.non_travaille || jour_travaille.jour_travaille.repos != 1 || jour_travaille.jour_travaille.matin != 1 || jour_travaille.jour_travaille.apres_midi != 1 ? jour_travaille.jour_travaille.commentaire : '',
                utilisateur_id: this.parametres.utilisateur_id,
                total_jours: (parseInt(jour_travaille.jour_travaille.matin) + parseInt(jour_travaille.jour_travaille.apres_midi)) / 2
            }

            $.post({
                url: url,
                dataType: "json",
                data: donnees
            }).done(async (donnees) => {
                this.$set(jour_travaille,'jour_travaille',donnees.element);
                this.$emit('fin_enregistrement_valeur');
            });
        },
    },
});
