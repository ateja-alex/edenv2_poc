<div class="row" v-if="menus_liens.type_lien == 3">
    <div class="col-sm-2">Route</div>
    <div class="col-sm-10">
        <select name="route" v-model="menus_liens.route">
            <option v-for="(route, index) in routes.routes_frequentes" :value="route.nom" v-text="route.libelle"></option>
        </select>
    </div>
</div>
<div class="row" v-else-if="menus_liens.type_lien == 4">
    <div class="col-sm-2">Route</div>
    <div class="col-sm-10">
        <input type="text" name="route" v-model="menus_liens.route">
    </div>
</div>
<div class="row" v-else-if="menus_liens.type_lien == 5">
    <div class="col-sm-2">Route</div>
    <div class="col-sm-10">
        <select name="route" v-model="menus_liens.route">
            <optgroup label="Routes fréquemment utilisées">
                <option v-for="(route, index) in routes.routes_frequentes" :value="route.nom" v-text="route.libelle"></option>
            </optgroup>
            <optgroup v-for="(routes_controller, nom_controller) in routes.routes" :label="nom_controller">
                <option v-for="route in routes_controller" :value="route" v-text="route"></option>
            </optgroup>
        </select>
    </div>
</div>	
<div class="row" v-if="menus_liens.type_lien == 1">
    <div class="col-sm-2">Paramètres</div>
    <div class="col-sm-10">
        <select name="parametres[0]" v-model="menus_liens.parametres[0]">
            <option v-for="(table_libre, index) in parametres_par_type[menus_liens.type_lien]" :value="table_libre.type_element" 
                v-text="table_libre.element + ' (' + table_libre.type_element + ')'">
            </option>
        </select>
    </div>
</div>
<div class="row" v-else-if="menus_liens.type_lien == 2">
    <div class="col-sm-2">Paramètres</div>
    <div class="col-sm-10">
        <select name="parametres[0]" v-model="menus_liens.parametres[0]">
            <option v-for="(rapport, index) in parametres_par_type[menus_liens.type_lien]" :value="rapport.id_rapport" 
                v-html="$root.traduction(rapport.index_traduction,'titre')">
            </option>
        </select>
    </div>
</div>
<template v-else-if="menus_liens.type_lien == 5 && menus_liens.route" >
    <div class="row" v-for="(parametre,index) in parametres_par_type[menus_liens.type_lien][menus_liens.route]">
        <div class="col-sm-2" v-text="'Paramètres : ' + parametre.name + (parametre.obligatoire ? '*' : '')"></div>
        <div class="col-sm-10">
            <input type="text"  :name="'parametres[' + index + ']'" v-model="menus_liens.parametres[index]">
        </div>
    </div>
</template>

@push('donnees_pour_vuejs_data')
	
	routes: {},
    parametres_par_type: {},
@endpush

@push('donnees_pour_vuejs_mounted')
	
    this.$on('changement_valeur', (donnees) => {
        
        if(donnees.nom_sql != 'type_lien')
            return;

        this.menus_liens.route = '';
        this.menus_liens.parametres = [];
    });

	$.ajax({

        url: "{{ route('parametrage.menu.donnees_selection_routes') }}",
        method: 'post',
    }).done((donnees) => {
        
        this.routes = donnees.routes; ;
        this.parametres_par_type = donnees.parametres_par_type; ;
    });
@endpush