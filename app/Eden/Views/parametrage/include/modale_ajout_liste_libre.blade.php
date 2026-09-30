<template v-if="modale_ajout_liste_libre">
    <transition name="modal" >
        <div class="modal-mask">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" v-if="type_liste_libre == null">Gestion des liste libres</h5>
                        <h5 class="modal-title" v-else>Création : @{{ type_liste_libre.nom }}</h5>
                        <button type="button" class="close" @click="modale_ajout_liste_libre = false" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <template v-if="type_liste_libre === null">
                            <div class="row">
                                <div v-for="type_liste in types_listes"
                                     @click="choix_type_liste(type_liste)"
                                     :class="'col-sm-'+(informations_listes.liste_libre_principale_existante ? '3' : '2')+' parametrage_liste_libre_bloc_selection_element'"
                                     v-if="informations_listes.liste_libre_principale_existante === false || type_liste.index != 'principale'">
                                    <img :src="'{{ asset('eden/images/pictos')}}/icone_liste_libre_'+type_liste.index+'.png'" style="aspect-ratio: 1/1;"/>
                                    <span>@{{ type_liste.nom }}</span><br>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <form class="css_form" v-if="type_liste_libre.index == 'rapport'">
                                <div class="row">
                                    <div class="col-sm-2">Titre</div>
                                    <div class="col-sm-4 css_champ_obligatoire js_champ_obligatoire">
                                        <input  @change="calcul_id_rapport" type="text" v-model="valeurs_liste_libre.titre"  />
                                    </div>

                                    <div class="col-sm-2">Catégorie</div>

                                    <div class="col-sm-4 css_champ_obligatoire js_champ_obligatoire">
                                        <select v-model="valeurs_liste_libre.categorie" >
                                            @foreach($categories as $categorie => $nom)

                                                <option value="{{$categorie}}">{{ $nom['nom']}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-2">Description</div>
                                    <div class="col-sm-10">
                                        <textarea type="text" v-model="valeurs_liste_libre.description" ></textarea>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-2">Icone</div>
                                    <div class="col-sm-4">
                                        <select v-model="valeurs_liste_libre.icone">
                                            <option value="table">Table</option>
                                            <option value="chart-area">Chart-Area</option>
                                            <option value="info">Info</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">

                                    <div class="col-sm-2">Id Rapport</div>
                                    <div class="col-sm-4">
                                        <div class="css_champ_obligatoire js_champ_obligatoire">
                                            <input :style="(id_rapports.includes(valeurs_liste_libre.id_rapport) ? 'background-color:red;color:white' : '')" type="text" @change="calcul_id_rapport(true)" v-model="valeurs_liste_libre.id_rapport"/>
                                        </div>
                                        <span v-if="id_rapports.includes(valeurs_liste_libre.id_rapport)" style="color:red">* Id rapport déjà utilisé</span>
                                    </div>
                                </div>
                            </form>
                            <form class="css_form" v-else>
                                <div class="row">
                                    <div class="col-sm-2">
                                        Titre
                                    </div>
                                    <div class="col-sm-4 css_champ_obligatoire js_champ_obligatoire">
                                        <input type="text" v-model="valeurs_liste_libre.titre"/>
                                    </div>
                                </div>
                                <template v-if="type_liste_libre.index == 'fiche'">
                                    <div class="row">
                                        <div class="col-sm-2">
                                            Fiche
                                        </div>
                                        <div class="col-sm-4 css_champ_obligatoire js_champ_obligatoire">
                                            <select v-model="valeurs_liste_libre.fiche">
                                                <option v-for="(type_element_traduction, type_element) in types_elements_fiche" :value="type_element">
                                                    @{{ type_element_traduction }} (@{{ type_element }})
                                                </option>
                                            </select>
                                        </div>
                                        <template v-if="valeurs_liste_libre.fiche != undefined">
                                            <div class="col-sm-2">
                                                Clé primaire
                                            </div>
                                            <div class="col-sm-4 css_champ_obligatoire js_champ_obligatoire">
                                                <select v-model="valeurs_liste_libre.cle_primaire">
                                                    <option value="id">Id élément</option>
                                                    <option v-for="champ in champs_selection_type_element_fiche[valeurs_liste_libre.fiche]" :value="champ.nom_sql">
                                                        @{{traduction(champ.index_traduction,'nom')}} (@{{champ.nom_sql}})
                                                    </option>
                                                </select>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="row" v-if="valeurs_liste_libre.cle_primaire != undefined && champs_selection_type_element_fiche[valeurs_liste_libre.fiche][valeurs_liste_libre.cle_primaire] != null && champs_selection_type_element_fiche[valeurs_liste_libre.fiche][valeurs_liste_libre.cle_primaire].type == 22">
                                        <div class="col-sm-6">
                                        </div>
                                        <div class="col-sm-2">
                                            Type élément primaire
                                        </div>
                                        <div class="col-sm-4 css_champ_obligatoire js_champ_obligatoire">
                                            <select v-model="valeurs_liste_libre.type_element_primaire">
                                                <option v-for="valeur in JSON.parse(champs_selection_type_element_fiche[valeurs_liste_libre.fiche][valeurs_liste_libre.cle_primaire].contenu_ec2)" :value="valeur.type_element">
                                                    @{{traduction('tables_libres.' + valeur.type_element,'element')}} (@{{valeur.type_element}})
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <template v-if="valeurs_liste_libre.fiche != undefined">
                                        <div class="row">
                                            <div class="col-sm-2">
                                                Liste
                                            </div>
                                            <div class="col-sm-4 css_champ_obligatoire js_champ_obligatoire">
                                                <input type="text" disabled :value="type_liste_sur_fiche">
                                            </div>
                                            <div class="col-sm-2">
                                                Clé étrangère
                                            </div>
                                            <div class="col-sm-4 css_champ_obligatoire js_champ_obligatoire">
                                                <select v-model="valeurs_liste_libre.cle_etrangere">
                                                    <option v-for="(champ, nom_sql) in champs_selection_type_element_liste" :value="nom_sql">
                                                        @{{traduction(champ.index_traduction,'nom')}} (@{{nom_sql}})
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </template>
                                </template>
                            </form>
                        </template>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="modale_ajout_liste_libre = false" data-dismiss="modal">Fermer</button>
                        <template v-if="type_liste_libre !== null">
                            <button v-if="!parametrage_intranet" type="button" class="btn btn-default" @click="creer_nouvelle_liste">Retour</button>
                            <button type="button" class="btn btn-primary" @click="creation_liste">Enregistrer</button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

@push('donnees_pour_vuejs_data')
    @if(empty($parametrage_intranet))
        champs_type_element_liste: {!! collect($champs_type_element_liste) !!},
        champs_selection_type_element_liste: {!! collect($champs_selection_type_element_liste) !!},
        champs_type_element_fiche: {!! collect($champs_type_element_fiche) !!},
        champs_selection_type_element_fiche: {!! collect($champs_selection_type_element_fiche) !!},
        types_elements_fiche: {!! collect($types_elements_fiche) !!},
    @endif
    id_rapports: {!! collect($id_rapports) !!},
    parametrage_intranet: @json($parametrage_intranet ?? false),
	modale_ajout_liste_libre: false,
    type_liste_libre: null,
    type_liste_sur_fiche : '',
    types_listes : [
        {
            index : 'principale',
            nom : 'Liste libre principale',
        },
        {
            index : 'export',
            nom : 'Export',
        },
        {
            index : 'fiche',
            nom : 'Liste libre sur fiche',
        },
        {
            index : 'rapport',
            nom : 'Rapport',
        },
    ],
    valeurs_liste_libre : {},
@endpush

@push('donnees_pour_vuejs_methods')

    creer_nouvelle_liste() {

		this.modale_ajout_liste_libre = true;
		this.type_liste_libre = null;
        this.valeurs_liste_libre = {};
	},

    async choix_type_liste(type_liste){

        if(type_liste.index == 'principale'){

            this.valeurs_liste_libre['type_element'] = this.table_libre.type_element;
            this.valeurs_liste_libre['type_liste'] = type_liste.index;

            if(await confirm_eden('Êtes-vous certain de vouloir créer une liste pour ce type élément ?'))
                this.creation_liste();

            return;
        }

        else if(type_liste.index == 'rapport'){

            this.valeurs_liste_libre = {
                'id_rapport' : '',
                'titre' : '',
                'categorie' : 0,
                'description' : '',
                'icone' : '',
                'type_rapport' : 'liste_libre',
            };
        }

        else{

            var titre = this.traduction('tables_libres.' + this.table_libre.nom_table_sql + '.element_pluriel');
            titre = titre.charAt(0).toUpperCase() + titre.slice(1);

            this.type_liste_sur_fiche = titre;
    
            this.valeurs_liste_libre = {
                'champ' : null,
                'titre' : titre
            };
        }

        this.type_liste_libre = type_liste;
        this.valeurs_liste_libre['type_element'] = this.parametrage_intranet ? this.module_affichage_option.type_element : this.table_libre.type_element;
        this.valeurs_liste_libre['type_liste'] = type_liste.index;
    },

    creation_liste(){

        loading(true);

        var vue_contexte = this;

		// on enregistre le type élément sélectionné
		$.post({

			url: "{{ URL::to("eden/parametrage/liste_libre/enregistrer") }}",
			dataType: "json",
            data: vue_contexte.valeurs_liste_libre,
		}).done(function(donnees) {

			if(donnees.retour !== true) {

                loading(false);
				toastr.error(donnees.retour);
	            return;
			}
            if(vue_contexte.parametrage_intranet) {
                vue_contexte.mise_a_jour_traductions_valeurs();

                const parametrage_liste = window.open('', '_blank');

                $.get({
                    url: "{{ URL::to("eden/parametrage/intranet/recupere_liste") }}/" + donnees.id,
                    dataType: "json",

                }).done(function(liste) {
                    var module = vue_contexte.module_affichage_option;

                    if(vue_contexte.$root.cache.parametrage_intranet?.listes_type_element?.[module.type_element] != undefined){
                        vue_contexte.$root.cache.parametrage_intranet.listes_type_element[module.type_element].push(liste);
                    }

                    vue_contexte.module_affichage_option.liste = liste.id;

                    vue_contexte.modale_ajout_liste_libre = false;
                    loading(false);

                    parametrage_liste.location = donnees.redirection;
                });
                
            } else {
                window.location.href = donnees.redirection;
            }
		});
    },

    calcul_id_rapport(champ_id_rapport = false) {

		var vue_contexte = this;

        var id_rapport = this.valeurs_liste_libre.titre;

        if(champ_id_rapport == true)
            id_rapport = this.valeurs_liste_libre.id_rapport;

        // on crée l'id_rapport
        var accents = [
            /[\300-\306]/g, /[\340-\346]/g, // A, a
            /[\310-\313]/g, /[\350-\353]/g, // E, e
            /[\314-\317]/g, /[\354-\357]/g, // I, i
            /[\322-\330]/g, /[\362-\370]/g, // O, o
            /[\331-\334]/g, /[\371-\374]/g, // U, u
            /[\321]/g, /[\361]/g, // N, n
            /[\307]/g, /[\347]/g, // C, c
        ];

        var sans_accents = ['A','a','E','e','I','i','O','o','U','u','N','n','C','c'];

        for(var i = 0; i < accents.length; i++){

            id_rapport = id_rapport.replace(accents[i], sans_accents[i]);

        }

        id_rapport = id_rapport.toLowerCase();

        // autres caractères spéciaux
        var a_remplacer = [/[\41-\57]/g, /[\72-\100]/g, /[\133-\140]/g, /[\173-\176]/g, /¤/g, /£/g, /§/g, /µ/g, /¨/g, /;/g, /°/g,/ /g,/’/g];

        for(var n = 0; n < a_remplacer.length; n++){

            id_rapport = id_rapport.replace(a_remplacer[n], "_");

        }

        vue_contexte.valeurs_liste_libre.id_rapport=id_rapport;

	},

@endpush