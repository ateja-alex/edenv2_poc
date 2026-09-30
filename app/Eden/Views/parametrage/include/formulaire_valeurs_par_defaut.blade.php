<div class="card mb-3">
    <div class="card-header">
        <h4>
            Valeurs par défaut
        </h4>
        <span @click="enregistrer_valeurs_par_defaut()"  data-toggle="tooltip" data-placement="top" title="Enregistrer les valeurs" class="css_ajouter_element css__lien bouton_header">
                                <i aria-hidden="true" class="css_action_icon fa fa-fw fa-save"></i>
                            </span>
    </div>
    <div class="card-body css_form css_parametrage_formulaire ">
        <table class="table table-bordered table-hover">
            <thead>
            <tr>
                <th style="width: 50%">Champ</th>
                <th style="width: 50%">Valeur</th>
                <th style="width: 5px;">
                    <i class="fas fa-plus-square css_pointer" @click="valeurs_par_defaut.push({nom_sql : null, valeur: null})"></i>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(valeur_par_defaut,index_valeur_par_defaut) in valeurs_par_defaut">
                <td>
                    <select-champs-libres :champs_libres="[
                                                {'type_element' : type_element,
                                                'index_traduction' : 'tables_libres.'+type_element+'.nom_table',
                                                'champs_libres' :modele_champs_libres.filter(champ_libre =>
                                                    !valeurs_par_defaut.map(valeur_par_defaut => valeur_par_defaut.nom_sql)
                                                    .includes(champ_libre.nom_sql)
                                                    || champ_libre.nom_sql == valeur_par_defaut.nom_sql
                                                )
                                                }
                                            ]"
                                          :type_element_origine="type_element"
                                          :type_element="type_element"
                                          :nom_sql="valeur_par_defaut.nom_sql"
                                          @changement_select_champs_libres="valeur_par_defaut.nom_sql = $event.nom_sql;"
                    >
                    </select-champs-libres>
                </td>
                <td>
                    <component v-if="valeur_par_defaut.nom_sql != null" :is="champs_valeurs_par_defaut[valeur_par_defaut.nom_sql]"
                               :element="valeur_par_defaut"
                               :type_element="type_element"
                    />
                </td>
                <td>
                    <i class="fas fa-trash css_pointer" @click="valeurs_par_defaut.splice(index_valeur_par_defaut,1);"></i>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

@push('donnees_pour_vuejs_data')

    valeurs_par_defaut : {!! $valeurs_par_defaut !!},
    champ: {},
    champs_valeurs_par_defaut: {},
@endpush

@push('donnees_pour_vuejs_methods')

    enregistrer_valeurs_par_defaut : function(){

        loading(true);

        $.post({

            url: "{{ route('parametrage.formulaire.enregistrer_valeurs_par_defaut') }}",
            dataType:'json',
            data:{
                formulaire: this.formulaire,
                valeurs_par_defaut: this.valeurs_par_defaut
            }
        }).done(() => {

            info('Enregistrement effectué !');

            loading(false);
        });
    },
@endpush

@push('donnees_pour_vuejs_mounted')

    @foreach($champs_valeurs_par_defaut as $champ_libre)

        this.champs_valeurs_par_defaut.{{$champ_libre['nom_sql']}} = {
            template:`{!! $champ_libre['management']->cree() !!}`,
            methods:this.$options.methods,
            props:{
                element:{},
                type_element:{},
            },
            data : function(){
                return {
                    {{$type_element}}: {}
                }
            },
            created:function(){

                var valeur = this.element.valeur;

                @if(in_array($champ_libre['management']->modele->type,[10,11,12]))
                    if(!Array.isArray(valeur))
                        valeur = valeur != null ? JSON.parse(valeur).map(index => parseInt(index)) : [];
                @endif

                this.$set(this[this.type_element],this.element.nom_sql,valeur);
            },
            watch:{
                '{{$type_element}}.{{$champ_libre['nom_sql']}}' : function(valeur){
                    this.element.valeur = valeur;
                }
            }
        };
    @endforeach

    this.$forceUpdate();
@endpush