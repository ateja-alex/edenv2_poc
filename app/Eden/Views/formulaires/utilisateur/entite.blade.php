<div class="row">
    <div class="col-sm-12 css_form_ligne_titre">
        @traduction('interface.gestion_utilisateurs.modale_utilisateurs.titre_categorie.entites_principales')
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <div class="utilisateur_titre_entite">
            <input type="checkbox" id="entite_tous_acces" v-model="utilisateur.acces_toutes_entites"
                   :true-value="1" :false-value="0"  @change="changement_acces_tous_entites">
            <select class="d-none" name="acces_toutes_entites" v-model="utilisateur.acces_toutes_entites">
                <option value="0">0</option>
                <option value="1">1</option>
            </select>
            <label for="entite_tous_acces">
                <span class="text-capitalize">
                    @traduction('interface.gestion_utilisateurs.modale_utilisateurs.toutes_les_entites')
                </span>
            </label>
        </div>
        <entites :entites="entites_groupes" :modele="utilisateur" ></entites>
        <input type="hidden" name="entites" v-if="utilisateur.entites.length == 0">
    </div>
</div>

@push('donnees_pour_vuejs_data')
    entites : [],
@endpush

@push('donnees_pour_vuejs_mounted')

    $.post({
        url : 'eden/elements/entite',
        dataType:'json',
    }).done((elements) => {
        this.entites = elements;
    });
@endpush

@push('donnees_pour_vuejs_computed')

    entites_groupes : function(){
        return this.entites_recuperer_groupes();
    },
@endpush

@push('donnees_pour_vuejs_methods')

    changement_acces_tous_entites : function(){

        if(this.utilisateur.acces_toutes_entites == 1)
            this.utilisateur.entites = [];
    },

    entites_recuperer_groupes : function(entite_parent = null){

        var entites_du_groupe = [];

        var entites_du_groupe = this.entites.filter((x)=> {

            if(x.entite_parent == 0)
                x.entite_parent = null;

            return x.entite_parent == entite_parent
        });

        for(entite_groupe of entites_du_groupe){
            entite_groupe.groupes = this.entites_recuperer_groupes(entite_groupe.id);
        }

        return entites_du_groupe;
    },
@endpush

@push('donnees_pour_vuejs_created')

    Vue.component('entites', {
        template : `
        <div class="utilisateur_bloc_entites">
            <div class="utilisateur_entite" v-for="entite in entites" :key="entite.id">
                <div class="utilisateur_element_entite">
                    <div class="utilisateur_titre_entite">
                        <input v-if="!parent_selectionne" type="checkbox" :id="'entite_'+entite.id"
                               name="entites[]" :value="entite.id" @change="deselection_entite(entite)"
                               v-model="modele.entites">
                        <label :for="'entite_'+entite.id">
                            <span class="text-capitalize">@{{ entite.nom }}</span>
                        </label>
                    </div>
                </div>
                <div v-if="entite.groupes.length > 0" class="utilisateur_bloc_entite">
                    <entites :entite_parent="entite" :entites="entite.groupes" :modele="modele"></entites>
                </div>
            </div>
        </div>
        `,
        props: {
            entites: {
                type:Array,
                default : []
            },
            modele: {},
            entite_parent : null,
        },
        computed : {
            parent_selectionne : function(){

                if(this.modele.acces_toutes_entites == 1)
                    return true;

                if(this.entite_parent == null)
                    return false;

                if(this.$parent.parent_selectionne == true)
                    return true;

                return this.modele.entites.includes(this.entite_parent.id);
            },
        },
        methods : {
            deselection_entite : function(entite){

                if(entite.groupes == undefined)
                    return;

                for(entite_groupe of entite.groupes){
                    this.deselection_enfant(entite_groupe);
                }
            },
            deselection_enfant : function(entite){

                var modele_entites = this.modele.entites;
                var index = entite.id;

                if(entite.groupes != undefined){

                    for(entite_groupe of entite.groupes){
                        this.deselection_enfant(entite_groupe);
                    }
                }

                if(modele_entites.includes(index))
                    modele_entites.splice(modele_entites.indexOf(index),1);
            },
        },
    });
@endpush