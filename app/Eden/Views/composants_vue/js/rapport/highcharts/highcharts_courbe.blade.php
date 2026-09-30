<script>
    const highcharts_courbe = Vue.component('highcharts-courbe', {
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
            libelle: {
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
            nombre_decimales_recap: {
                type: Number,
                default: 2,
            }
        },
        mounted: function () {
            this.$parent.$once('actualisation_rapport_event', () => {
                this.actualise_charts()
            })
        },
        computed: {
            categories() {
                var cat = [];

                for (var legende of this.legendes) {
                    cat.push(legende);
                }

                return cat;
            },
            series() {
                var series = [];

                for (var [nom_serie, serie] of Object.entries(this.series_rapport)) {

                    series.push({
                        name: nom_serie,
                        data: serie,
                    });

                }

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
            yAxis() {
                var objectif = {};

                if(this.libelle) {
                    objectif['title'] = {
                        text: this.libelle
                    }
                }

                if(this.objectif) {
                    objectif['minRange'] = this.objectif.valeur_objectif;
                    objectif['plotLines'] = [{
                        value: this.objectif.valeur_objectif,
                        color: this.objectif.couleur_ligne_objectif,
                        dashStyle: this.objectif.affichage_ligne_objectif,
                        width: 2,
                        label: {
                            text: this.objectif.texte_objectif
                        }
                    }]
                }

                return objectif;
            },
        },
        methods: {
            actualise_charts() {
                const nombre_decimales = this.nombre_decimales_recap;
                Highcharts.chart(this.$refs[this.id_random], {
                    chart: {
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
                    yAxis: this.yAxis,
                    xAxis: {
                        categories: this.categories
                    },
                    legend: {
                        layout: 'horizontal',
                            align: 'center',
                            verticalAlign: 'bottom'
                    },
                    exporting: {
                        buttons: {
                            contextButton: {
                                menuItems: ["viewFullscreen", "printChart", "separator", "downloadPNG", "downloadJPEG", "separator", "downloadCSV", "downloadXLS", "viewData"]
                            }
                        },
                    },
                    plotOptions: {
                        series: {
                            label: {
                                connectorAllowed: false
                            },
                            // pointStart: 2010
                        }
                    },
                    series: this.series,
                    responsive: {
                        rules: [{
                            condition: {
                                maxWidth: 500
                            },
                            chartOptions: {
                                legend: {
                                    layout: 'horizontal',
                                    align: 'center',
                                    verticalAlign: 'bottom'
                                }
                            }
                        }]
                    },
                    tooltip: {
                        formatter : function(){
                            return this.series.name + ': <b>' + parseFloat(parseFloat(this.y).toFixed(nombre_decimales)) + '</b>';
                        }
                    }
                });
            },
        },
    });
</script>