<script>
    const champ_datetime = Vue.component('champ-datetime', {
        template: `<div class="champ_datetime" v-click_outside="deploi_datalist">
            <input type="datetime-local" v-show="false" v-model="modele[nom_sql]" :name="name">
            <input type="date" :disabled="lecture_seule" v-model="date">
            <span class="bloc_datetime_temps" v-if="!cacher_champ_time">
                <input type="text" ref="time" @focus="$refs.time.select()" @focusout="$forceUpdate()" class="datetime_temps" :disabled="lecture_seule" v-model="temps">
                <i class="fas fa-chevron-down" v-if="!lecture_seule" @click="deploi_datalist(!affichage_datalist)"></i>
                <div v-show="affichage_datalist" class="datalist" :id="'datalist_'+id_random" ref="list">
                    <p @click="temps = option;deploi_datalist()" v-for="option in options" :style="(option == temps ? 'background:var(--background_navbar);color:white;' : '')" :value="option">
                        @{{ option }}
                    </p>
                </div>
            </span>
        </div>`,
        props: {

            modele: {},
            nom_sql: '',
            name: '',
            lecture_seule : {
                type: Boolean | Number,
                default: false,
            },
            cacher_champ_time : {
                type:Boolean,
                default: false,
            },
            interval:{
                type : Number,
                default : 1
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

                var options = [];

                var date = new Date();
                date.setHours(0);
                date.setMinutes(0);
                date.setSeconds(0);

                var jour = (new Date()).getDay();

                while(date.getDay() == jour){

                    var minutes = date.getMinutes().toString();
                    var heure = date.getHours().toString();

                    if(heure[1] === undefined)
                        heure = '0'+heure[0];

                    if(minutes[1] === undefined)
                        minutes = '0'+minutes[0];

                    options.push(heure+':'+minutes);

                    var ajout_interval = this.interval * 60 * 1000;
                    date.setTime(date.getTime() + ajout_interval);
                }

                return options;
            },

            date: {
                get: function () {

                    if(this.modele[this.nom_sql] == null || this.modele[this.nom_sql] == '')
                        return null;

                    var datetime = this.modele[this.nom_sql].split(' ');

                    return datetime[0];
                },
                set: function (nouvelle_valeur) {

                    if(nouvelle_valeur == null || nouvelle_valeur == '') {
                        this.modele[this.nom_sql] = null;
                        return;
                    }

                    if(this.modele[this.nom_sql] == null || this.modele[this.nom_sql] == '')
                        var temps = '00:00';
                    else
                        var temps = this.temps;

                    this.modele[this.nom_sql] = nouvelle_valeur+' '+temps;
                }
            },

            temps: {
                get: function () {

                    if(this.modele[this.nom_sql] == null || this.modele[this.nom_sql] == '')
                        return null;

                    var datetime = this.modele[this.nom_sql].split(' ');

                    if(datetime[1] == undefined)
                        return '00:00:00';
                    else
                        return datetime[1].substring(0,5);
                },
                set: function (nouvelle_valeur) {

                    if(nouvelle_valeur.length == 4 && !nouvelle_valeur.includes(':'))
                        nouvelle_valeur = nouvelle_valeur.substring(0,2) + ':' + nouvelle_valeur.substring(2,4);

                    var temps_valide  = /^([0-1][0-9]|2[0-3]):([0-5][0-9])$/.test(nouvelle_valeur);

                    if(nouvelle_valeur == null || nouvelle_valeur == '')
                        nouvelle_valeur = '00:00';
                    else if(!temps_valide)
                        return;

                    if(this.modele[this.nom_sql] == null || this.modele[this.nom_sql] == '')
                        var date = this.$root.aujourdhui;
                    else
                        var date = this.date;

                    this.modele[this.nom_sql] = date+' '+nouvelle_valeur+':00';
                }
            }
        },

        methods: {
            deploi_datalist : function(valeur = false){

                if(this.lecture_seule == true)
                    return;

                this.affichage_datalist = valeur;

                if(valeur === true && this.temps != null) {

                    var temps = false;

                    var date = new Date();
                    date.setHours(this.temps.substring(0,2));
                    date.setMinutes(this.temps.substring(3,5));
                    date.setSeconds(0);

                    var jour = (new Date()).getDay();

                    while(jour == date.getDay() && temps === false){

                        var minutes = date.getMinutes().toString();
                        var heure = date.getHours().toString();

                        if(heure[1] === undefined)
                            heure = '0'+heure[0];

                        if(minutes[1] === undefined)
                            minutes = '0'+minutes[0];

                        if(this.options.includes(heure+':'+minutes))
                            temps = heure+':'+minutes;
                        else
                            date.setTime(date.getTime() - 60000);
                    }

                    this.$nextTick(async () => {
                        await $('#datalist_' + this.id_random).scrollTop(0);
                        $('#datalist_' + this.id_random).scrollTop($('#datalist_' + this.id_random + ' p[value="' + temps + '"]').position().top);
                    });

                }
            },
        },
        directives: {
            click_outside: {
                bind: function (el, binding, vnode) {
                    el.clickOutsideEvent = function (event) {
                        if (!(el.contains(event.target))) {
                            vnode.context[binding.expression]();
                        }
                    };
                    document.body.addEventListener('click', el.clickOutsideEvent)
                },
                unbind: function (el) {
                    document.body.removeEventListener('click', el.clickOutsideEvent)
                },
            }
        }
    });
</script>
