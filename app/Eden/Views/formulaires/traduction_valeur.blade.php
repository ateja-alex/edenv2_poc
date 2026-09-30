@php
    $background_navbar = maquette('background_navbar');

    list($r, $g, $b) = sscanf($background_navbar, "#%02x%02x%02x");

    $rgba = $r.', '.$g.', '.$b.',0.3';
@endphp

<div class="row">
    <div class="col-sm-2">@traduction('formulaire.traduction.index')</div>
    <div class="col-sm-10">
         @php
            $champ_index = management('traduction_index')->champ('index');
            $champ_index->modele->obligatoire = true;
        @endphp
        {!! $champ_index->vmodel(true,'traduction_element')->attr('disabled','!traduction_element.creation',1)->cree() !!}
    </div>
</div>
<div class="row">
    <div class="col-sm-2">@traduction('formulaire.traduction.categorie')</div>
    <div class="col-sm-10">
        @php
            $champ_categorie = management('traduction_index')->champ('categorie');
            $champ_categorie->modele->obligatoire = true;
        @endphp
        {!! $champ_categorie->vmodel(true,'traduction_element')->attr('disabled','!traduction_element.creation',1)->cree() !!}
    </div>
</div>

<div class="row">
    <div class="col-sm-12 css_form_ligne_titre">@traduction('formulaire.traduction.valeurs')</div>
</div>

<table class="table table-bordered table-hover">
    <thead>
        <th v-if="index_langue_affichage_ajout > 0"></th>
        <th v-for="langue in langues_affichage_ajout">@{{ langue.nom }}</th>
        <th v-if="(langues_affichage_ajout.length + index_langue_affichage_ajout) < langues.length"></th>
    </thead>
    <tbody>
        <td  v-if="index_langue_affichage_ajout > 0">
            <i @click="modification_langue(-1)" class="fas fa-arrow-left"></i>
        </td>
        <td v-if="traduction_element.creation" v-for="langue in langues_affichage_ajout">
            @if(env('BASE_TRADUCTION') === true)
                <textarea v-if="traduction_element[langue.code].textarea === true" @dblclick="traduction_element[langue.code].textarea = false;$forceUpdate();" v-model="traduction_element[langue.code].traduction_standard"></textarea>
                <input v-else @dblclick="traduction_element[langue.code].textarea = true;$forceUpdate();" type="text" v-model="traduction_element[langue.code].traduction_standard">
            @else
                <textarea v-if="traduction_element[langue.code].textarea === true" @dblclick="traduction_element[langue.code].textarea = false;$forceUpdate();" v-model="traduction_element[langue.code].traduction_specifique"></textarea>
                <input v-else @dblclick="traduction_element[langue.code].textarea = true;$forceUpdate();" type="text" v-model="traduction_element[langue.code].traduction_specifique">
            @endif
        </td>
        <td v-else>
            @if(env('BASE_TRADUCTION') === true)
                <textarea v-if="traduction_element[langue.code].textarea === true" @dblclick="traduction_element[langue.code].textarea = false;$forceUpdate();" style="width:100%;height: 150px;"  v-model="traduction_element[langue.code].traduction_standard"></textarea>
                <input v-else @dblclick="traduction_element[langue.code].textarea = true;$forceUpdate();" style="width: 100%;" type="text" v-model="traduction_element[langue.code].traduction_standard">
            @else
                <div v-if="traduction_element.type == 'specifique'">
                    <textarea v-if="traduction_element[langue.code].textarea === true" @dblclick="traduction_element[langue.code].textarea = false;$forceUpdate();" style="width:100%;height: 150px;background-color:rgba({{$rgba}});"  v-model="traduction_element[langue.code].traduction_specifique"></textarea>
                    <input v-else @dblclick="traduction_element[langue.code].textarea = true;$forceUpdate();" style="width:100%;background-color:rgba({{$rgba}});"  type="text" v-model="traduction_element[langue.code].traduction_specifique">
                </div>
                <div v-else-if="traduction_element[langue.code].traduction_specifique == null || traduction_element[langue.code].traduction_specifique == ''">
                    <textarea v-if="traduction_element[langue.code].textarea === true" @dblclick="traduction_element[langue.code].textarea = false;$forceUpdate();" style="width:100%;height: 150px;"  @change="affectation_valeur_specifique($event,traduction_element,langue)" :value="traduction_element[langue.code].traduction_standard"></textarea>
                    <input v-else @dblclick="traduction_element[langue.code].textarea = true;$forceUpdate();" style="width: 100%;"  @change="affectation_valeur_specifique($event,traduction_element,langue)" type="text" :value="traduction_element[langue.code].traduction_standard">
                </div>
                <div style="display: inline-flex;width:100%" v-else>
                    <textarea v-if="traduction_element[langue.code].textarea === true" @dblclick="traduction_element[langue.code].textarea = false;$forceUpdate();" style="width:100%;height: 150px;background-color:rgba({{$rgba}});"  v-model="traduction_element[langue.code].traduction_specifique"></textarea>
                    <input v-else @dblclick="traduction_element[langue.code].textarea = true;$forceUpdate();" style="width:100%;background-color:rgba({{$rgba}});" type="text" v-model="traduction_element[langue.code].traduction_specifique">
                    <span style="width: 30px;background-color: red;height: 30px;cursor: pointer;display: inline-flex;align-items: center;justify-content: center;"  @click="traduction_element[langue.code].traduction_specifique = null">
                        <i class="fas fa-times" style="color: white;"></i>
                    </span>
                </div>
            @endif
        </td>
        <td  v-if="(langues_affichage_ajout.length + index_langue_affichage_ajout) < langues.length">
            <i @click="modification_langue(1)" class="fas fa-arrow-right"></i>
        </td>
    </tbody>
</table>
