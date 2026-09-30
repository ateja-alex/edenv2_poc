<script>
    const champ_time = Vue.component('champ-time', {
        template: `<div class="champ_time">
            <input :disabled="lecture_seule" :list="options.length > 0 ? 'options_' + id_random : ''" type="time" v-model="modele[nom_sql]" :name="name" :step="format === 'H:i:s' ? 1 : 60">
            <datalist v-if="options.length > 0" :id="'options_' + id_random">
                <option v-for="temps_possible in options" :value="temps_possible" v-text="temps_possible"></option>
            </datalist>
        </div>`,
        props: {

            modele: {},
            nom_sql: '',
            name: '',
            lecture_seule : {
                type: Boolean | Number,
                default: false,
            },
            format: {
                type: String,
                default : 'H:i:s',
            },
            interval:{
                type : Number,
                default : 0
            }
        },
        data: function(){
            return {
                affichage_datalist : false,
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

            options : function(){

                if(this.interval === 0)
                    return [];

                var options = [];

                var date = new Date();
                date.setHours(0);
                date.setMinutes(0);
                date.setSeconds(0);

                var jour = (new Date()).getDay();

                while(date.getDay() == jour){

                    var minutes = date.getMinutes().toString();
                    var heure = date.getHours().toString();
                    var secondes = date.getSeconds().toString();

                    if(heure[1] === undefined)
                        heure = '0'+heure[0];

                    if(minutes[1] === undefined)
                        minutes = '0'+minutes[0];

                    if(secondes[1] === undefined)
                        secondes = '0'+secondes[0];

                    options.push(heure+':'+minutes+(this.format === 'H:i:s' ? ':'+secondes : ''));

                    var ajout_interval = this.interval * 1000;

                    if(this.format === 'H:i')
                        ajout_interval *= 60;
                    
                    date.setTime(date.getTime() + ajout_interval);
                }

                return options;
            },
        },
    });
</script>
