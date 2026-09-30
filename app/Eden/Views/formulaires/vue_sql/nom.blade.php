<div class="row" style="margin-top: 5px;">
    <div class="col-sm-2">{!! management('vue_sql')->champ('nom')->modele->nom !!}</div>
    <div class="col-sm-4">
        <input type="text" name="nom" @change="calcul_nom_sql('vue_sql.nom_sql', 'vue_sql.nom')" v-model="vue_sql.nom"/>
    </div>
    <div class="col-sm-2">{!! management('vue_sql')->champ('nom_sql')->modele->nom !!}</div>
    <div class="col-sm-4">
        <input type="text" name="nom_sql" @change="calcul_nom_sql('vue_sql.nom_sql')" v-model="vue_sql.nom_sql"/>
    </div>

</div>

@include('eden::parametrage.include.js_calcul_nom_sql')