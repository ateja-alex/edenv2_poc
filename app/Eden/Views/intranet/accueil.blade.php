@extends('eden::intranet.template')

@section('titre')
{{ traduction('interface.intranet.titre_rh') }}
@endsection

@section('contenu')
<section class="home on">
    @foreach($structure_intranet['lignes'] as $modules_intranets_par_ligne)

        <div class="row" v-show="bloc_affiche == ''">
            @foreach($modules_intranets_par_ligne['modules'] as $module_intranet)

                <div class="col-sm-{{$module_intranet['taille']}}"
                    style="margin-left:{{$module_intranet['taille_avant']*100/12}}%;margin-right:{{$module_intranet['taille_apres']*100/12}}%" @if(!empty($module_intranet['condition_affichage'])) v-if="{{$module_intranet['condition_affichage']}}" @endif>
                    <a @if($module_intranet['type_module'] == 'redirection')
                           href="{{route($module_intranet['route'])}}"
                        @else
                            @click="affichage_bloc('{{$module_intranet["id"]}}')"
                       @endif
                    >
                        <div @mouseover="affichage_couleur($event,'{{$module_intranet["couleur"]}}')"
                             @mouseout="desaffichage_couleur($event)"
                             class="box box-{{$module_intranet['id']}} {{$module_intranet['id']}}-link-box" style="height:{{$structure_intranet['parametrage']['bloc']['taille'].'px'}};" >
                            <p class="title" style="font-size:{{$structure_intranet['parametrage']['bloc']['taille_texte'].'px'}};">
                                <b>@traduction('intranet.modules.module_{{$module_intranet["id"] }}','nom')</b>
                            </p>
                            <span style="font-size:{{$structure_intranet['parametrage']['bloc']['taille_icone'] .'px'}};" class="icone_intranet {{$module_intranet['icone']}}"></span>
                        </div>
                    </a>
                </div>

                @push('section_modules')
                    <template @if(!empty($module_intranet['condition_affichage'])) v-if="{{$module_intranet['condition_affichage']}}" @endif>
                        @if($module_intranet['type_module'] == 'module_par_defaut' && !empty($module_intranet['module_par_defaut']))
                            @include('eden::intranet.modules.'.$module_intranet['module_par_defaut'],$module_intranet)
                        @elseif($module_intranet['type_module'] == 'formulaire')
                            @if(view()->exists('eden::intranet.modules.formulaire.'.$module_intranet['type_element']))
                                @include('eden::intranet.modules.formulaire.'.$module_intranet['type_element'],$module_intranet)
                            @else
                                @include('eden::intranet.formulaire',$module_intranet)
                            @endif
                        @elseif($module_intranet['type_module'] == 'liste')
                            @include('eden::intranet.liste',$module_intranet)
                        @endif
                    </template>
                @endpush
            @endforeach
        </div>
    @endforeach
</section>
@endsection

@push('donnees_pour_vuejs_data')
    structure_intranet : {!! collect($structure_intranet) !!},
    listes_par_type_element : {!! collect($listes) !!},
    formulaires_charges : [],
    bloc_affiche : '',
@endpush

@push('donnees_pour_vuejs_methods')

    affichage_bloc(module_id){

        var module = this.informations_module(module_id);

        if(module !== null){
            if(module.type_module == "formulaire"){

                var nom_formulaire = module.nom_formulaire != null ? module.nom_formulaire : 'intranet_'+module.type_element;
                if(!this.formulaires_charges.includes(nom_formulaire)) {
                    return this.$on('formulaire_charger',(formulaire) =>  {
                        if(formulaire == module.type_element)
                            return this.affichage_bloc(module_id);
                    });
                }
                this.$refs['formulaire_'+module_id].reinitialisation_modele();
                this.$emit('affichage_formulaire_'+module_id);
                this[module.type_element] = this.$refs['formulaire_'+module_id].element;
                this.$forceUpdate();

                if(module.type_element == 'note_de_frais')
                    this.etape_note_de_frais = 'informations';
            }

            this.$emit('affichage_module_'+module_id);
        }

        this.bloc_affiche = module_id;
    },

    affichage_couleur(event,couleur){

        $(event.target).closest('.box').css('background-color',couleur);
    },

    desaffichage_couleur(event){

        $(event.target).closest('.box').css('background-color','rgba(145, 145, 145, 1)');
    },

    enregistrer(module_id,type_element){

        var formulaire = $('#formulaire_'+module_id);

        var route = '/eden/element/'+type_element+'/creer';

        if(this[type_element].id !== undefined)
            route = '/eden/element/'+type_element+'/'+this[type_element].id+'/enregistrer';

        loading(true);

		$.post({

			url: route,
			dataType: "json",
			data: formulaire.serialize(),

		}).done((donnees) => {

            loading(false);
			if(donnees.retour !== true) {

				$('#alerte_erreur_'+module_id).html(donnees.retour).show('fast').delay(5000).hide('fast');
				return;
			}

            $('.intranet').css('pointer-events','none');

			$('#alerte_succes_'+module_id).html(
                this.traduction('messages.js.enregistrement_succes')
            ).show('fast').delay(5000).hide('fast');
			setTimeout(() => {
                this.bloc_affiche = '';
                $('.intranet').css('pointer-events','unset');
            }, 500);
		});
    },

    enregistrer_formulaire: async function(module_id,type_element,champ_utilisateur,type_champ){

		// On afficher le loader
		loading(true);

        var informations = {};

        if(type_champ == 10)
            informations[champ_utilisateur] = this.$refs['formulaire_'+module_id].element[champ_utilisateur];
        else
            informations[champ_utilisateur] = this.$root.moi.id;

		var donnees = await this.$refs['formulaire_'+module_id].enregistrer(informations);

		if(donnees.retour === true){

            $('.intranet').css('pointer-events','none');

			$('#alerte_succes_'+module_id).html(
                this.traduction('messages.js.enregistrement_succes')
            ).show('fast').delay(5000).hide('fast');
			setTimeout(() => {
                this.bloc_affiche = '';
                $('.intranet').css('pointer-events','unset');
            }, 500);

            for(ref in this.$refs){
                if(ref.includes('liste_libre_'))
                    this.$refs[ref].actualiser();
            }
        }

		loading(false);
    },

    charger_formulaire(type_element,element_id,liste_id){

        var bloc_a_charger = null;

        const module_liste = this.module_avec_liste_id(liste_id);

        this.structure_intranet.lignes.forEach(function(ligne){
            ligne.modules.forEach(function(module){
                if(module.type_module == 'formulaire' && module.type_element == type_element && module_liste.formulaire_liste == module.id)
                    bloc_a_charger = module.id;
            });
        });

        if(bloc_a_charger == null){
            toastr.error('Formulaire indisponible');
            return;
        }

        $.ajax({

            url: "eden/element/"+type_element+"/"+element_id,
            dataType: "json",

        }).done((element) => {

            this.$refs['formulaire_'+bloc_a_charger].element = element;
            this.$emit('affichage_formulaire_'+bloc_a_charger);
            this[type_element] = element;

            this.bloc_affiche = bloc_a_charger;

            if(type_element == 'note_de_frais')
                this.mise_a_jour_montant();
        });
    },

    module_avec_liste_id: function(liste_id){
        
        for (const ligne of this.structure_intranet.lignes) {
            for (const module of ligne.modules) {
                if (module.type_module == 'liste' && module.liste == liste_id) 
                    return module;
            }
        }

        return false;
    },

    informations_module(module_id){

        var vue_instance = this;

        var informations_module = null;

        vue_instance.structure_intranet.lignes.forEach(function(ligne){
            ligne.modules.forEach(function(module){
                if(module.id == module_id)
                    informations_module = module;
            });
        });

        return informations_module;
    },

@endpush

@push('donnees_pour_vuejs_mounted')

    this.$on('formulaire_charger',(formulaire) => {
        this.formulaires_charges.push(formulaire);
    });
@endpush