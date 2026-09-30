<div class="card mb-3">
    <div class="card-header">
        <h4>@traduction('module_sur_fiche.fiche.famille.sous_familles')</h4>
    </div>
    <div class="card-body">
        <div v-show="sous_familles.length == 0">
            @traduction('module_sur_fiche.fiche.famille.aucune_sous_famille')
        </div>
        <div v-show="sous_familles.length > 0">
            @traduction('module_sur_fiche.fiche.famille.liste_des_sous_familles') :
            <br>
            <br>
            <div v-for="sous_famille in sous_familles">
            - <a :href="'{{ URL::to('eden') }}/fiche/famille/'+sous_famille.id+'/afficher'">@{{ sous_famille.nom }}</a>
            </div>
        </div>
    </div>
</div>