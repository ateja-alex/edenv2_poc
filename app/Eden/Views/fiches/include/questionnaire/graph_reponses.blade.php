<div id="detail_reponses_questionnaire" style="width: 100%; height: 300px; margin: 0 auto;"></div><br/><br/>

@push('scripts')
    <script>

        Highcharts.chart('detail_reponses_questionnaire', {

            chart: {
                polar: true,
                type: 'line'
            },

            title: {
                text: '{{ traduction('module_sur_fiche.questionnaire.graph_reponses.moyenne_reponses') }}',
                x: -80
            },

            pane: {
                size: '80%'
            },

            xAxis: {
                categories: [{!! implode(',', array_keys($graph_reponses)) !!}],
                tickmarkPlacement: 'on',
                lineWidth: 0
            },

            yAxis: {
                gridLineInterpolation: 'polygon',
                lineWidth: 0,
                min: 0
            },

            exporting: {
                buttons: {
                  contextButton: {
                    menuItems: ["viewFullscreen", "printChart", "separator", "downloadPNG", "downloadJPEG", "separator", "downloadCSV", "downloadXLS", "viewData"]
                  }
                },
            },

            tooltip: {
                shared: true,
                pointFormat: '<span style="color:{series.color}">{series.name}: <b>{point.y:,.0f}</b><br/>'
            },

            legend: {
                enabled: false,
            },

            series: [{
                name: '',
                data: [{{ implode(',', $graph_reponses) }}],
                pointPlacement: 'on'
            }],

            responsive: {
                rules: [{
                    condition: {
                        maxWidth: 500
                    },
                    chartOptions: {
                        legend: {
                            align: 'center',
                            verticalAlign: 'bottom'
                        },
                        pane: {
                            size: '70%'
                        }
                    }
                }]
            }

        });

    </script>
@endpush