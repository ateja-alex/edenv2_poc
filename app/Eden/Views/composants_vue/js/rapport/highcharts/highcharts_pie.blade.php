<script>
    const highcharts_pie = Vue.component('highcharts-pie', {
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
            series_rapport: {
                type: Object,
                default: {},
            },
            legendes: {
                type: Array,
                default: [],
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
            serie_data() {
                var series = [];

                for (var [index_valeur, valeur] of Object.entries(this.series_rapport.valeurs)) {

                    var name = this.legendes[index_valeur];

                    if(name)
                        name = name.replaceAll("'"," ");

                    series.push({
                        name: name,
                        y: valeur,
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
        },
        methods: {
            actualise_charts() {
                const nombre_decimales = this.nombre_decimales_recap;
                // Build the chart
                Highcharts.chart(this.$refs[this.id_random], {
                    chart: {
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false,
                        type: 'pie',
                        animation: false,
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
                        text: this.titre,
                    },
                    tooltip: {
                        formatter: function() {
                            return this.series.name + ': <b>' + parseFloat(parseFloat(this.y).toFixed(nombre_decimales)) + '</b>';
                        }
                    },
                    accessibility: {
                        point: {
                            valueSuffix: '%'
                        }
                    },
                    exporting: {
                        buttons: {
                            contextButton: {
                                menuItems: ["viewFullscreen", "printChart", "separator", "downloadPNG", "downloadJPEG", "separator", "downloadCSV", "downloadXLS", "viewData"]
                            }
                        },
                    },
                    plotOptions: {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: true,
                                format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                                connectorColor: 'silver'
                            }
                        }
                    },
                    series: [{
                        name: this.series_rapport.nom,
                        data: this.serie_data,
                    }]
                });
            },
        },
    });
</script>