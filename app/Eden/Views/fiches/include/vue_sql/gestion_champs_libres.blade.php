<div class="row">
        <div class="col-md-12">
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center">
                    <h4>
                        @traduction('module_sur_fiche.vue_sql.gestion_champs_libres.titre')
                    </h4>
                    <span @click="enregistrer_gestion_champs_libres" class="css_ajouter_element ml-auto" data-toggle="tooltip" data-placement="left" title="{{ traduction('interface.modales.enregistrer') }}">
					    <i class="css_action_icon secondaire far fa-save css_font_16"></i>
				    </span>
                </div>
                @if($vue_sql->type_de_vue != 1)
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <span style="margin-right: 5px;" v-for="(nom_element,type_element) in gestion_champs_libres['type_element']" class="badge badge-default" @click="descend_au_type(type_element)">
                                    @{{ nom_element }}
                                </span>
                            </div>
                        </div>
                        <br>
                        <div class="row" v-for="(nom_element,type_element) in gestion_champs_libres['type_element']" :id="'gestion_champs_libres_'+type_element">
                            <div class="col-md-12">
                                <div class="card mb-3">
                                    <div class="card-header d-flex align-items-center">
                                        <h4>
                                            @{{ nom_element }}
                                        </h4>
                                        <span class="ml-auto" data-placement="left">
                                            <span class="css__lien" v-if="!champs_libres_statut(type_element,'inactif')" style="cursor:pointer;margin:10px;" @click="tout_a_un_etat(type_element,false)">
                                                <i class="far fa-square"></i>
                                                @traduction('module_sur_fiche.vue_sql.gestion_champs_libres.tout_deselectionner')
                                            </span>
                                            <span class="css__lien" v-if="!champs_libres_statut(type_element,'actif')" style="cursor:pointer;margin:10px;" @click="tout_a_un_etat(type_element,true)">
                                                <i class="far fa-check-square" ></i>
                                                @traduction('module_sur_fiche.vue_sql.gestion_champs_libres.tout_selectionner')
                                            </span>
                                            <input class='css_input_recherche_liste js_input_recherche_liste' placeholder="{{traduction('module_sur_fiche.vue_sql.gestion_champs_libres.recherche')}}" style="padding-left: 5px" type="text" v-model="input_recherche_champs_libres[type_element]" />
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <template v-for="champs_libres in gestion_champs_libres[type_element]" v-if="champs_libres.modele.nom_sql.includes(input_recherche_champs_libres[type_element].toLowerCase()) || champs_libres.modele.nom.toLowerCase().includes(input_recherche_champs_libres[type_element].toLowerCase())">
                                                <div class="col-md-2" style="padding-right:10px;padding-bottom:10px;">
                                                    <input type="checkbox" :id="type_element+'_'+champs_libres.modele.nom_sql" v-model="champs_libres.actif" style="margin-right:10px;cursor:pointer;">
                                                    <label :for="type_element+'_'+champs_libres.modele.nom_sql" style="cursor:pointer;display: inline;">
                                                        @{{ champs_libres.modele.nom }}
                                                        <span style="font-size:10px;font-style: italic">(@{{ champs_libres.modele.nom_sql }})</span>
                                                    </label>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                   <div class="card-body">
                        <div class="table-responsive">
                            <form>
                                <table class="table table-bordered table-hover css_form css_table_fin_padding" width="100%" cellspacing="0">
                                    <thead>
                                    <tr>
                                        <th scope="col">@traduction('module_sur_fiche.vue_sql.gestion_champs_libres.champ_reference')</th>
                                        <th scope="col">@traduction('module_sur_fiche.vue_sql.gestion_champs_libres.nom_sql')</th>
                                        <th scope="col">@traduction('module_sur_fiche.vue_sql.gestion_champs_libres.nom')</th>
                                        <th scope="col">@traduction('module_sur_fiche.vue_sql.gestion_champs_libres.options')</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <template v-for="(champ_libre, index) in gestion_champs_libres">
                                        <tr>
                                            <input type="hidden" v-model="champ_libre.id_cl" />
                                            <th scope="col"><input type="text" v-model="champ_libre.parent" /></th>
                                            <th scope="col">
                                                <input v-if="champ_libre.id_cl == null" type="text" v-model="champ_libre.nom_sql" />
                                                <span v-else>@{{ champ_libre.nom_sql }}</span>
                                            </th>
                                            <th scope="col"><input type="text" v-model="champ_libre.nom" /></th>
                                            <th scope="col" @click="gestion_champs_libres.splice(index,1)"><i class="fa fa-fw fa-trash"></i></th>
                                        </tr>
                                    </template>
                                    <tr>
                                        <th scope="col"><input type="text" v-model="nouveau_champ_libre.parent" @change="gestion_champ_libre(nouveau_champ_libre)" /></th>
                                        <th scope="col"><input type="text" v-model="nouveau_champ_libre.nom_sql" @change="gestion_champ_libre(nouveau_champ_libre)" /></th>
                                        <th scope="col"><input type="text" v-model="nouveau_champ_libre.nom" @change="gestion_champ_libre(nouveau_champ_libre)" /></th>
                                        <th scope="col"></th>
                                    </tr>
                                    </tbody>
                                </table>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    
    
@push('donnees_pour_vuejs_data')
    gestion_champs_libres: {!! collect($gestion_champs_libres) !!},
    @if($vue_sql->type_de_vue != 1)
        input_recherche_champs_libres: {
        @foreach($gestion_champs_libres['type_element'] as $type_element => $nom_element)
            {{ $type_element }} : '',
        @endforeach
        },
    @else
        nouveau_champ_libre: {
            id_cl : null,
            nom : '',
            nom_sql : '',
            parent : '',
        },
    @endif
@endpush

@push('donnees_pour_vuejs_methods')

    descend_au_type: function(type_element){
    position = $('#gestion_champs_libres_'+type_element).offset().top - 100;
    if($('#mainNav').height()){
    position = position - $('#mainNav').height();
    }
    $('html,body').animate({
    scrollTop: position
    }, 'slow');
    },

    tout_a_un_etat(type_element,etat){

        vue_instance.gestion_champs_libres[type_element].forEach(function(champ_libre, index) {

            champ_libre.actif = etat;
        });
    },

    champs_libres_statut: function(type_element,statut) {

    tous_actif = true;
    this.gestion_champs_libres[type_element].forEach(function(champ_libre, index) {

        if(statut == 'actif' && !champ_libre.actif){
            tous_actif = false;
        }

        if(statut == 'inactif' && champ_libre.actif){
            tous_actif = false;
        }

    });


    return tous_actif;

    },

    enregistrer_gestion_champs_libres(){

        var vue_instance = this;

        loading(true);

        // on enregistre les infos du champ libre
        $.post({

        url: "{{ URL::to("eden/fiche/vue_sql/") }}/"+vue_instance.element_id+"/post/enregistrer_gestion_champs_libres",
        dataType: "json",
        data: {
            gestion_champs_libres : vue_instance.gestion_champs_libres,
        },
        }).done(async function(donnees) {

        loading(false);
        if(donnees.retour !== true) {

        await erreur(donnees.retour);
        return;
        }

        if(donnees.gestion_champs_libres != undefined){
            vue_instance.gestion_champs_libres = donnees.gestion_champs_libres;
        }

        info("{{traduction('module_sur_fiche.vue_sql.gestion_champs_libres.enregistrement_ok')}}");

        });

    },

    gestion_champ_libre(champ_libre){

        if(champ_libre.nom != '' && champ_libre.nom_sql != '' && champ_libre.parent != ''){

            this.gestion_champs_libres.push(champ_libre);

            this.nouveau_champ_libre = {id_cl : null,nom : '',nom_sql : '',parent : '',};
        }
    },

@endpush
