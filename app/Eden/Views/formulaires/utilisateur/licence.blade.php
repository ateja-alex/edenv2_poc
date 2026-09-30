<div class="row">
    <div class="col-sm-2">
        {!! management('utilisateur')->champ('licence_id')->nom_vue() !!}
    </div>
    <div class="col-sm-4">
        <select name="licence_id" v-model="utilisateur.licence_id" :disabled="utilisateur.type_utilisateur == 1 && {{ moi()->type_utilisateur }} == 1">
            <option v-if="licence.nombre != null" v-for="licence in licences" :value="licence.id" :disabled="licence.nombre_utilises == licence.nombre && !(licence.utilisateurs.includes(utilisateur.id))">@{{ licence.nom }} - @{{ licence.nombre_utilises == null ? 0 : licence.nombre_utilises }}/@{{ licence.nombre }}</option>
        </select>
    </div>
</div>

@push('donnees_pour_vuejs_data')

    licences: [],

@endpush

@push('donnees_pour_vuejs_mounted')

    $.ajax({

        url : 'eden/parametrage/licences',
        method : 'get',
        dataType : 'json',
    }).done((donnees) => {

        this.licences = donnees;
    });


@endpush

