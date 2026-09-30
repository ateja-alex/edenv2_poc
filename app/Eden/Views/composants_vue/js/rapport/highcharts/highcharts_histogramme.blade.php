<script>
    const highcharts_histogramme = Vue.component('highcharts-histogramme', {
        template: `
            <div :ref="id_random" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
        `,
        props: {
            id: {
                type: String,
                default: 'graphique_highcharts',
            },
            titre: {
                type: String,
                default: 'Sans titre',
            },
            sous_titre: {
                type: String,
                default: '',
            },
            series_rapport: {
                type: Object,
                default: {},
            },
            legendes: {
                type: Array,
                default: [],
            },
            objectif: {
                type: Object,
                default: {},
            },
            groupe_par: {
                type: String,
                default: '',
            },
            nombre_decimales_recap: {
                type: Number,
                default: 2,
            }
        },
        mounted: function () {
            this.$parent.$once('actualisation_rapport_event', () => {
                this.actualise_charts()
            })

            var vue_instance = this;
        },
        computed: {
            categories() {
                var cat = [];

                for (var legende of this.legendes) {
                    cat.push(legende);
                }

                return cat;
            },
            yAxis() {
                if (!this.objectif)
                    return {}

                var objectif = {
                    minRange: this.objectif.valeur_objectif,
                    title: {
                        text: ''
                    },
                    plotLines: [{
                        value: this.objectif.valeur_objectif,
                        color: this.objectif.couleur_ligne_objectif,
                        dashStyle: this.objectif.affichage_ligne_objectif,
                        width: 2,
                        label: {
                            text: this.objectif.texte_objectif
                        }
                    }],
                }

                if (this.groupe_par) {
                    objectif.stackLabels = {
                        enabled: true,
                        style: {
                            fontWeight: 'bold',
                            color: ( // theme
                                Highcharts.defaultOptions.title.style &&
                                Highcharts.defaultOptions.title.style.color
                            ) || 'gray',
                            textOutline: 'none'
                        }
                    }
                }

                return objectif;
            },
            series() {
                var series = [];
                var data_serie_cumule = {};

                for (var [nom_serie, serie] of Object.entries(this.series_rapport)) {

                    data_serie_cumule = {
                        name: nom_serie,
                        id: nom_serie,
                        data: serie,
                    };

                    if (typeof serie === 'object' && this.groupe_par) {
                        for (var [nom_serie_cumule, serie_cumule] of Object.entries(serie)) {
                            var nom_serie_cumule_explode = nom_serie_cumule.split('(');

                            data_serie_cumule = {
                                name: nom_serie_cumule_explode[0].trim(),
                                data: serie_cumule,
                            };

                            data_serie_cumule.stack = nom_serie;

                            data_serie_cumule.id = nom_serie_cumule_explode[0].trim();

                            if (nom_serie.includes('N-1'))
                                data_serie_cumule.id += 'N_1';

                            data_serie_cumule.id = data_serie_cumule.id.toLowerCase();

                            series.push(data_serie_cumule);
                        }
                    }
                    else
                        series.push(data_serie_cumule);
                }

                series.forEach(function (serie, index_serie) {

                    if(!serie.stack || !serie.stack.includes('N-1'))
                        return;

                    series[index_serie].linkedTo = serie.name.toLowerCase();
                    serie.name += ' (N-1)';

                });

                if(series.length == 0) {
                    series.push({
                        name: this.$root.traduction('rapport.divers.aucune_donnee'),
                        id: this.$root.traduction('rapport.divers.aucune_donnee'),
                        data: [],
                    });
                }

                series = this.ordonner_series(series);

                return series;
            },

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

                return this.id + '_' + str;
            },
        },
        methods: {
            actualise_charts() {
                const nombre_decimales = this.nombre_decimales_recap;
                Highcharts.chart(this.$refs[this.id_random], {
                    chart: {
                        type: 'column',
                        events: {
                            exportData: ({ dataRows }) => {
                                dataRows.forEach((row, i) => {
                                    if (i > 1) { // skip headers
                                        row.forEach((column, j) => {
                                            if(j > 0 && row[j]) {
                                                row[j] = parseFloat(parseFloat(row[j]).toFixed(this.nombre_decimales_recap));
                                            }
                                        })
                                    }
                                });
                            }
                        },
                    },
                    title: {
                        text: this.titre
                    },
                    subtitle: {
                        text: this.sous_titre
                    },
                    exporting: {
                        buttons: {
                            contextButton: {
                                menuItems: ["viewFullscreen", "printChart", "separator", "downloadPNG", "downloadJPEG", "separator", "downloadCSV", "downloadXLS", "viewData"]
                            }
                        },
                    },
                    xAxis: {
                        categories: this.categories,
                        crosshair: true
                    },
                    yAxis: this.yAxis,
                    tooltip: {
                        formatter: function() {
                            var format = '<div class="histogramme_tooltip">'+
                                '<span style="font-size:15px">'+this.x+'</span>';

                            var stackName = this.series.userOptions.stack;

                            format += '<br/><span style="font-size:15px;font-weight: bold;">' + vue_instance.$root.traduction("rapport.divers.serie") + ' : </span><span style="font-size:15px">'+(stackName ? stackName : vue_instance.$root.traduction("rapport.divers.serie_princiaple"))+'</span>';

                            format += '<table>';

                            if (this.y != 0) {
                                var valeur_formatee = this.y % 1 === 0 ? this.y : parseFloat(this.y.toFixed(nombre_decimales)); //permet de mettre "nombre_decimales_recap" chiffres après la virgule si ce n'est pas un nombre entier
                                format += '<tr><td style="color:' + this.series.color + ';padding:0">' + (this.series.name ? this.series.name : vue_instance.$root.traduction("rapport.divers.serie_princiaple")) + ': </td>' +
                                    '<td style="padding:0"><b>' + valeur_formatee + '</b></td></tr>';
                            }

                            format += '</table></div>';

                            return format;
                        },
                        useHTML: true
                    },
                    plotOptions: {
                        column: {
                            pointPadding: 0.2,
                            borderWidth: 0,
                            stacking: this.groupe_par ? 'normal' : '',
                        }
                    },
                    series: this.series,
                });
            },
            ordonner_series: function(series){
                var series_ordonnees = [];
                
                if (this.groupe_par) {
                    series.forEach(serie => {
                        if (!serie.linkedTo) {
                            series.forEach(serie_liee => {
                                if (serie_liee.linkedTo === serie.id) {
                                    series_ordonnees.push(serie_liee);
                                }
                            });
                            series_ordonnees.push(serie);
                        }
                    });
                } else {
                    for (let i = 0; i < series.length; i += 2) {
                        const serie_normale = series[i];
                        const serie_n1 = series[i + 1];
                        if (serie_n1) series_ordonnees.push(serie_n1);
                        series_ordonnees.push(serie_normale);
                    }
                }

                return series_ordonnees;
            },
        },
    });
</script>