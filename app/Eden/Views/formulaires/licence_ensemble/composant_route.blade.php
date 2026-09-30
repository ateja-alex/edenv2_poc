Vue.component('routes', {
    template : `
        <div class="bloc_elements_licence_ensemble">
            <div class="bloc_routes_licence_ensemble" v-for="route in routes_tries" :key="route.index">
                <div class="element_licence_ensemble">
                    <div class="element_titre_licence_ensemble">
                        <input v-if="!parent_selectionne && !licence_ensemble.routes.includes('tous')" type="checkbox" :id="'route_'+route.index" name="routes[]" :value="route.index" @change="deselection_route(route)" v-model="licence_ensemble.routes">
                        <label :for="'route_'+route.index">
                            <span class="text-capitalize">@{{ route.nom | retraite_caracteres_speciaux }}</span>
                            <span>( @{{ route.nom }} )</span>
                        </label>
                    </div>
                    <i v-if="route.groupes != undefined" @click="affichage_enfants.includes(route.index) ? affichage_enfants.splice(affichage_enfants.indexOf(route.index),1) : affichage_enfants.push(route.index)"
                       :class="'fas fa-chevron-'+(affichage_enfants.includes(route.index) ? 'up' : 'down')"></i>
                </div>
                <div v-if="route.groupes != undefined" v-show="affichage_enfants.includes(route.index)" class="bloc_route_licence_ensemble">
                    <routes :recherche_route="recherche_route" :route_parent="route" :routes="route.groupes" :licence_ensemble="licence_ensemble"></routes>
                </div>
            </div>
        </div>
    `,
    props: {
        routes : {},
        route_parent : null,
        licence_ensemble : {},
        recherche_route: '',
    },
    data: function(){
        return{
            affichage_enfants : [],
        };
    },
    computed : {
        routes_tries : function(){

            var modele_routes = this.routes;

            var routes = [];

            modele_routes.sort((a, b) => {

                if(this.route_selectionne(a) && !this.route_selectionne(b))
                    return -1;

                if(!this.route_selectionne(a) && this.route_selectionne(b))
                    return 1;

                if (a.nom === b.nom)
                    return 0;

                return (a.nom < b.nom) ? -1 : 1;
            });

            if(this.recherche_route == null || this.recherche_route == '')
                return modele_routes;

            for(route of modele_routes){

                if(this.correspondance_recherche(route) || this.route_selectionne(route))
                    routes.push(route);
            }

            return routes;
        },
        parent_selectionne : function(){

            if(this.route_parent == null)
                return false;

            if(this.$parent.parent_selectionne == true)
                return true;

            return this.licence_ensemble.routes.includes(this.route_parent.index);
        },
    },
    methods : {
        deselection_route : function(route){

            if(route.groupes == undefined)
                return;

            for(route_groupe of route.groupes){
                this.deselection_enfant(route_groupe);
            }
        },
        deselection_enfant : function(route){

            var modele_routes = this.licence_ensemble.routes;
            var index = route.index;

            if(route.groupes != undefined){

                for(route_groupe of route.groupes){
                    this.deselection_enfant(route_groupe);
                }
            }

            if(modele_routes.includes(route.index))
                modele_routes.splice(modele_routes.indexOf(route.index),1);
        },
        route_selectionne : function(route){

            var route_selectionne = this.licence_ensemble.routes.includes(route.index);

            if(!route_selectionne && route.groupes != undefined){

                for(route_groupe of route.groupes){

                    if(!route_selectionne)
                        route_selectionne = this.route_selectionne(route_groupe);
                }

            }

            return route_selectionne;
        },
        correspondance_recherche : function(route){

            var correspondance_trouve = route.index.toLowerCase().includes(this.recherche_route);

            if(!correspondance_trouve && route.groupes != undefined){

                for(route_groupe of route.groupes){

                    if(!correspondance_trouve)
                        correspondance_trouve = this.correspondance_recherche(route_groupe);

                    if(correspondance_trouve && !this.affichage_enfants.includes(route.index))
                        this.affichage_enfants.push(route.index);
                }

            }

            return correspondance_trouve;
        },
    },
});