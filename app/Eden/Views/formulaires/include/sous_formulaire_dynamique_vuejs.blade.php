<template @if(!isset($modification_autorisee) || (isset($modification_autorisee) && !$modification_autorisee)) v-if="{{$type_element}}.id == '' || {{$type_element}}.id == undefined || {{$type_element}}.id == null"@endif>

    <span v-if="sous_formulaires_retraite['{!! $nom_sous_formulaire !!}'] != undefined && afficher_bouton_sous_formulaire(sous_formulaires_retraite['{!! $nom_sous_formulaire !!}'])" @click="ajout_sous_formulaire_optionnel(sous_formulaires_retraite['{!! $nom_sous_formulaire !!}'], '{{ $type_element }}')" class="css_form_ligne_titre css_bouton_ajout_sous_formulaire">
        <i class="fas fa-plus"></i>
        <span style="margin-left: 5px">
            {!! traduction_blade("formulaire." . $nom_formulaire . ".sous_formulaire." . $nom_sous_formulaire . ".titre") !!}
        </span>
    </span>

    <template v-if="sous_formulaires_par_type_element != undefined && sous_formulaires_retraite['{!! $nom_sous_formulaire !!}'] != undefined && sous_formulaires_par_type_element[sous_formulaires_retraite['{!! $nom_sous_formulaire !!}'].type_element_pour_nom] != undefined">

        <template v-for="(sous_formulaire, key_sous_formulaire) in sous_formulaires_par_type_element[sous_formulaires_retraite['{!! $nom_sous_formulaire !!}'].type_element_pour_nom]">
            <div class="col-sm-12 css_form_ligne_titre" v-if="sous_formulaire.nom_formulaire_parent != undefined && sous_formulaire.nom_sous_formulaire != undefined" style="display: flex; justify-content: space-between; align-items: center;padding-right: 15px;margin-top: 15px;">
                <div>
                    @{{ $root.traduction("formulaire." + sous_formulaire.nom_formulaire_parent + ".sous_formulaire." + sous_formulaire.nom_sous_formulaire + ".titre") }}
                    <span v-if="key_sous_formulaire > 0" v-html="key_sous_formulaire"></span>
                </div>
                <span v-if="affichage_fermeture_sous_formulaire(sous_formulaires_retraite['{!! $nom_sous_formulaire !!}'])" @click="supprime_sous_formulaire(key_sous_formulaire, sous_formulaires_retraite['{!! $nom_sous_formulaire !!}'].type_element_pour_nom)" style="cursor: pointer;" class="fas fa-times"></span>
            </div>
            <div class="col-sm-12">
                <component :is="sous_formulaire" :key="key_sous_formulaire" :nom_sous_formulaire="sous_formulaire.type_element_remplacement"></component>
            </div>
        </template>

    </template>

</template>

@push('donnees_pour_vuejs_created')

    @if(!isset($modification_autorisee) || (isset($modification_autorisee) && !$modification_autorisee))
        if(this.{{$type_element}}.id == '' || this.{{$type_element}}.id == undefined || this.{{$type_element}}.id == null){
    @endif
            this.sous_formulaires_a_include.{{ $nom_sous_formulaire }} = {

                'nom_formulaire' : '{{ $nom_formulaire }}',
                'nom_sous_formulaire' : '{{ $nom_sous_formulaire }}',
                'informations_type_element' : {!! collect($informations_type_element) !!},
                'type_element' : '{{ $type_element }}'

            };

            if(this.sous_formulaires_retraite['{{ $nom_sous_formulaire }}'] == undefined){

                @php

                    $sous_formulaire_dynamique = retraite_sous_formulaire($nom_sous_formulaire);

                @endphp

                this.sous_formulaires_retraite['{{ $nom_sous_formulaire }}'] = {!! collect($sous_formulaire_dynamique) !!};

                (async () => {
                    @if(!empty($sous_formulaire_dynamique) && $sous_formulaire_dynamique['optionnel'] !== 1)
                        @for($i = 0; $i < ($sous_formulaire_dynamique['nombre'] ?? 1); $i++)
                            await this.ajout_sous_formulaire_optionnel(this.sous_formulaires_retraite['{!! $nom_sous_formulaire !!}'], '{{ $type_element }}');
                        @endfor
                    @endif
                })();
                this.$forceUpdate();

            }
    @if(!isset($modification_autorisee) || (isset($modification_autorisee) && !$modification_autorisee))
        }
    @endif
@endpush
