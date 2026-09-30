<script>
    const highcharts_carte = Vue.component('highcharts-carte', {
        template: `
            <div>
                <div id="js-google-map" ref="jsGoogleMap" style="height: 750px; "></div>
                <div class="css_legende_google_maps">

                    <div v-for="(adresses_par_couleur, legende) in adresses_par_type" style="display: flex;justify-content: center;align-items: center;">
                        <template v-for="(adresses, couleur) in adresses_par_couleur" v-if="adresses[0]">
                            <span style="width: 25px;" :id="couleur_svg(couleur)" :ref="'icone_gmap_' + couleur" v-html="adresses[0].icone_google_map"></span><span> - @{{ legende }}</span>
                        </template>
                    </div>
                </div>
            </div>
        `,
        props: {
            adresses_par_type: {
                type: Object,
                default: () => ({}),
            },
            latitude: {
                type: Number,
                default: 48.864716,
            },
            longitude: {
                type: Number,
                default: 2.349014,
            },
            mappage_latitude: {
                type: String,
                default: "",
            },
            mappage_longitude: {
                type: String,
                default: "",
            },
            zoom: {
                type: Number,
                default: 5,
            },
            centre_points: {
                type: Boolean,
                default: false,
            },
            desactiver_clusterisation: {
                type: Boolean,
                default: false,
            }
        },
        mounted: function () {
            this.$parent.$on('actualisation_rapport_event', () => {
                this.actualise_charts()
            })
        },
        methods: {
            parse_gps(input) {

                if( input.indexOf( 'N' ) == -1 && input.indexOf( 'S' ) == -1 &&
                    input.indexOf( 'W' ) == -1 && input.indexOf( 'E' ) == -1 ) {
                    return input.split(',');
                }

                var parts = input.split(/[:°'"]+/).join(' ').split(/[^\w\S]+/);

                if(['N','E','S','W'].includes(parts[0])) {
                    var direction_tmp = parts[0];
                    parts.splice(0, 1);
                    if(parts[parts.length - 1].length === 0)
                        parts.splice(parts.length - 1, 1);
                    parts.push(direction_tmp);
                }


                var directions = [];
                var coords = [];
                var dd = 0;
                var pow = 0;

                for( i in parts ) {

                    // we end on a direction
                    if( isNaN( parts[i] ) ) {

                        var _float = parseFloat( parts[i] );

                        var direction = parts[i];

                        if( !isNaN(_float ) ) {
                            dd += ( _float / Math.pow( 60, pow++ ) );
                            direction = parts[i].replace( _float, '' );
                        }

                        direction = direction[0];

                        if( direction == 'S' || direction == 'W' )
                            dd *= -1;

                        directions[ directions.length ] = direction;

                        coords[ coords.length ] = dd;
                        dd = pow = 0;

                    } else {

                        dd += ( parseFloat(parts[i]) / Math.pow( 60, pow++ ) );

                    }

                }

                if( directions[0] == 'W' || directions[0] == 'E' ) {
                    var tmp = coords[0];
                    coords[0] = coords[1];
                    coords[1] = tmp;
                }

                return coords;
            },
            async actualise_charts() {
                if(!this.$refs['jsGoogleMap'])
                    return;

                var map = L.map(this.$refs['jsGoogleMap'])
                
                if(!this.centre_points)
                    map = map.setView([this.latitude, this.longitude], this.zoom);

                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                }).addTo(map);

                if(!this.desactiver_clusterisation){
                    var markers = L.markerClusterGroup({
                        removeOutsideVisibleBounds: true,
                        showCoverageOnHover: true,
                    });
                }
                
                let latlng = [];

                for(const [legende,adresses_par_couleur] of Object.entries(this.adresses_par_type)) {

                    Object.entries(adresses_par_couleur).forEach(([couleur, adresses]) => {

                        adresses.forEach((adresse) => {
                            var latitude = adresse[this.mappage_latitude];
                            var longitude = adresse[this.mappage_longitude];

                            if(typeof longitude == 'string')
                                longitude = longitude.replace(',', '.');
                            if(typeof latitude == 'string')
                                latitude = latitude.replace(',', '.');

                            if(isNaN(parseFloat(latitude))) {
                                if(latitude.includes('°')) {
                                    latitude = this.parse_gps(adresse[this.mappage_latitude].replace("''", '"'));
                                    latitude = latitude[0] ?? latitude[1];
                                }
                                else
                                    return;
                            }
                            if(isNaN(parseFloat(longitude))) {
                                if(longitude.includes('°')) {
                                    longitude = this.parse_gps(adresse[this.mappage_longitude].replace("''", '"'));
                                    longitude = longitude[0] ?? longitude[1];
                                }
                                else
                                    return;
                            }

                            if(isNaN(parseFloat(latitude)) || isNaN(parseFloat(longitude)))
                                return;

                            divIcon = L.divIcon({
                                className: "leaflet-data-marker",
                                html: L.Util.template(adresse.icone_google_map, {
                                    mapIconUrl: adresse.icone_google_map,
                                    mapIconColor: couleur,
                                    mapIconColorInnerCircle: '#0C0058',
                                }),
                                iconSize: [25, 25],
                            });
                            
                            // Create a new marker
                            var marker = L.marker([parseFloat(latitude), parseFloat(longitude)], {icon: divIcon}).bindPopup(adresse.affichage_carte_google_map);
                            latlng.push([parseFloat(latitude), parseFloat(longitude)]);
                            
                            if(this.desactiver_clusterisation)
                                marker.addTo(map);
                            else
                                markers.addLayer(marker);
                        });

                    });
                }
                if(!this.desactiver_clusterisation)
                    map.addLayer(markers);
                if(this.centre_points)
                    map.fitBounds(latlng);
            },
            couleur_svg(couleur) {
                this.$nextTick(() => {
                    var svg = $(this.$refs['icone_gmap_' + couleur])[0].querySelector('svg');
                    $(svg.querySelector('#Shape')).attr('fill', couleur);
                    $(svg).attr('width', '25px');
                    $(svg).attr('height', '25px');
                    
                    var circle = $(svg)[0].querySelector('circle');
                    $(circle).attr('fill', '#0C0058');
                })
                return 'icone_gmap_' + couleur;
            },


    },
    });
</script>
