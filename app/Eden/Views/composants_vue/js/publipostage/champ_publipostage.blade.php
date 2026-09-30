<script>
    const champ_publipostage = Vue.component('champ-publipostage', {
        template: `
            <div>
                <textarea ref="champ" v-if="type_champ == 'textarea'" v-model="contenu" :name="name" :placeholder="placeholder"></textarea>
                <component ref="champ" v-else :is="type_champ" :modele="this"
                    :value="contenu"
                    nom_sql="contenu" :placeholder="placeholder" :name="name"
                    @editeur_initialise="ecouter_modification()">
                </component>
                <dropdown @select="selection_valeur($event)" 
                    :decalage="decalage" 
                    :elements_par_categorie="variables_par_type_filtres" 
                    v-if="recherche !== false" 
                    ref="dropdown">
                </dropdown>
            </div>
        `,
        props : {
            type_element : {
                type : String,
                required : true,
            },
            elements : {
                type : Array,
                default : function(){
                    return [];
                },
            },
            modele : {
                type : Object,
                default : function(){
                    return {};
                },
            },
            nom_sql : {
                type : String,
                default : '',
            },
            name : {
                type : String,
                default : '',
            },
            type_champ : {
                type : String,
                default : 'champ-texte',
            },
            placeholder : {
                type : String,
                default : '',
            },
            variables_par_type_contexte : {
                type : Array,
                default : function(){
                    return [];
                },
            },
            exclusion_balises_parametrage : {
                type : [Array,Boolean],
                default : function(){
                    return false;
                },
            }
        },
        data:function(){
            return{
                variables_par_type : [],
                champs_libres : {},
                decalage : {
                    top : 0,
                    left : 0,
                },
                elements_affichage: {},
                recherche: false,
                position_input_recherche : {
                    debut : -1,
                    curseur : -1,
                },
                dernier_chargement_recherche : null,
                champs_origine: {},
                evenements: {},
                tables_libres : {!! \App\Eden\Models\Table_libre::get() !!},

            }
        },
        mounted : async function(){
            
            await this.$nextTick();

            if (this.type_champ != 'textarea-wysiwyg-vue')
                this.ecouter_modification();
        },
        methods : {

            gestion_origine_valeur : async function(){

                var dernier_element = this.recherche.lastIndexOf('[');
                var base_recherche = dernier_element === -1 ? '' : this.recherche.substring(0, dernier_element + 1);

                if(base_recherche == this.dernier_chargement_recherche)
                    return;

                this.dernier_chargement_recherche = base_recherche;

                var recherches = this.recherche.replaceAll(']','').split('[');

                if(recherches.length <= 1){
                    await this.chargement_variables();
                    return;
                }

                var champ = null;

                for(recherche of recherches){

                    var type_element = champ?.type_element_ajax ?? this.type_element;

                    if(!this.champs_libres[type_element])
                        break;

                    nouveau_champ = this.champs_libres[type_element].find(c => c.nom_sql == recherche);

                    if(nouveau_champ == null || (nouveau_champ.type != 42 && nouveau_champ.type_reference != 42))
                        break;

                    champ = nouveau_champ;
                }

                await this.chargement_variables(champ ?? null);
            },

            chargement_variables : async function(champ_origine = null){

                var type_element = champ_origine?.type_element_ajax ?? this.type_element;

                this.variables_par_type = structuredClone(this.variables_par_type_contexte);

                var dernier_element = this.recherche.lastIndexOf('[');
                var base_recherche = dernier_element === -1 ? '' : this.recherche.substring(0, dernier_element + 1);

                if(this.exclusion_balises_parametrage !== true){

                    var filtrage = [{
                        champ : 'type_element_id',
                        condition : 'where',
                        valeur : this.tables_libres.find(t => t.type_element == type_element)?.id ?? 0
                    }];

                    if(this.exclusion_balises_parametrage !== false)
                        filtrage.push({
                            champ : 'id',
                            condition : 'whereNotIn',
                            valeur : this.exclusion_balises_parametrage.map(b => b.id)
                        });

                    var balises_parametrage = await $.post({
                        url : 'eden/elements/parametrage_balise_publipostage',
                        dataType : 'json',
                        data : {
                            filtrage : filtrage,
                        }
                    });

                    if(balises_parametrage.length > 0)
                        this.variables_par_type.push({
                            titre : this.$root.traduction('composant.champ_publipostage.balises_parametrage'),
                            id: 'balises_parametrage',
                            elements : balises_parametrage.map(balise => {
                                return {
                                    nom_sql : 'balise_'+balise.id,
                                    titre : balise.nom,
                                    valeur : "\{\{"+(base_recherche+"#balise_"+balise.id+"/"+balise.nom+(base_recherche != '' ? ']':''))+"\}\}",
                                }
                            }),
                        });
                }

                if(!this.champs_libres[type_element])
                    this.champs_libres[type_element] = await $.ajax({
                        url:'eden/champs/valeurs/'+type_element,
                        dataType:'json'
                    });

                var champs_libres = this.champs_libres[type_element];

                var variables = [];

                if(this.elements.length > 0)
                    var elements_affichage = await this.recuperer_elements_affichage(this.recherche,champ_origine);

                for(champ of champs_libres){

                    if(this.elements.length > 0){
                        var valeur = elements_affichage
                            .map(element => element.affichages[champ.nom_sql])
                            .filter((v,i,a)=>a.indexOf(v)==i)
                            .join(', ');

                    }
                    else
                        var valeur = "\{\{"+(base_recherche+champ.nom_sql+(base_recherche != '' ? ']':''))+"\}\}";

                    if(valeur == '')
                        continue;

                    variables.push({
                        nom_sql : champ.nom_sql,
                        titre : this.$root.traduction(champ.index_traduction+'.nom') + ' (' + champ.nom_sql + ') : <span class="valeur">'+valeur+'</span>',
                        index_traduction : champ.index_traduction,
                        valeur : valeur,
                    });
                }

                this.variables_par_type.push({
                    titre : this.$root.traduction('tables_libres.'+type_element+'.nom_table'),
                    id: 'valeur',
                    valeur : type_element,
                    elements : variables,
                });

                var liens_champs = champs_libres
                    .filter(champ => variables.map(v => v.nom_sql).includes(champ.nom_sql) && (champ.type == 42 || champ.type_reference == 42));

                if(liens_champs.length > 0)
                    this.variables_par_type.push({
                        titre : 'Lien champ',
                        id: 'lien_champ',
                        elements : liens_champs.map(champ => {
                            return {
                                nom_sql : champ.nom_sql,
                                titre : this.$root.traduction(champ.index_traduction+'.nom') + ' (' + champ.nom_sql + ')',
                                index_traduction : champ.index_traduction,
                                valeur : "\{\{"+(base_recherche+champ.nom_sql+(base_recherche != '' ? ']':''))+"[",
                            }
                        }),
                    });

                var autres_variables = [
                    {
                        'id' : 'id',
                        'titre' : 'ID',
                    },
                    {
                        'id' : 'lien_element',
                        'titre' : 'Lien vers l\'élément avec affichage',
                    },
                    {
                        'id' : 'url_lien_element',
                        'titre' : 'Lien vers l\'élément avec URL',
                    },
                ];

                if(champ_origine == null)
                    autres_variables.push({
                        'id' : 'maintenant',
                        'titre' : 'Date et heure de l\'action (FORMAT d/m à H:i:s)',
                    });

                this.variables_par_type.push({
                    titre : 'Autres',
                    id: 'autres',
                    elements : autres_variables.map(variable => {
                        return {
                            nom_sql : variable.id,
                            titre : variable.titre + ' (' + variable.id + ')',
                            valeur : "\{\{"+(base_recherche+'#'+variable.id+(base_recherche != '' ? ']':''))+"\}\}",
                        }
                    }),
                });
            },

            recuperer_elements_affichage : function(recherche,champ_origine = null){

                return new Promise(async (resolve) => {
                    if(this.elements_affichage[recherche]){
                        resolve(this.elements_affichage[recherche]);
                    }
                    else{

                        if(champ_origine == null){
                            var elements = this.elements.map(e => {
                                return {
                                    type_element : this.type_element,
                                    element_id : e.id
                                }
                            });
                        }
                        else{

                            var base_recherche = recherche.replace(champ_origine.nom_sql+'][','');

                            var elements_parent = await this.recuperer_elements_affichage(base_recherche);

                            var elements = elements_parent.map(e => e[champ_origine.nom_sql]).flat().filter((v,i,a) => a.indexOf(v) === i).map(e => {
                                return {
                                    type_element : champ_origine.type_element_ajax,
                                    element_id : e
                                }
                            });
                        }

                        $.post({
                            url:'eden/elements/affichage',
                            dataType:'json',
                            data : {
                                donnees : elements
                            },
                            success : (response) => {
                                this.elements_affichage[recherche] = Object.values(response);
                                resolve(this.elements_affichage[recherche]);
                            }
                        });
                    }
                });
            },

            initialisation_recherche(e){

                e.preventDefault();
                e.stopPropagation()

                var objet_champ_publipostage = this.objet_champ_publipostage;

                if(!objet_champ_publipostage)
                    return;

                var initialisation_recherche = false;

                if(this.type_champ == 'textarea-wysiwyg-vue'){
                    var range = objet_champ_publipostage.selection.getRng(true);
                    var debut = range.startOffset;
                    var texte = range.startContainer.data || '';
                    var caracteres = debut == 1 ? '' : texte.substring(debut - 2, debut);
                    
                    if (caracteres === '{{' && this.position_input_recherche.debut != debut - 2) {

                        this.position_input_recherche = {
                            debut : debut - 2,
                            curseur : debut,
                        };

                        objet_champ_publipostage.selection.setRng(range);
                        range.setStart(range.startContainer, debut - 2);
                        range.deleteContents();
                        
                        this.balise_recherche_wysiwyg();

                        initialisation_recherche = true;
                    }
                }
                else{

                    var debut = objet_champ_publipostage.selectionStart;
                    var texte = objet_champ_publipostage.value ?? objet_champ_publipostage.innerText;
                    var caracteres = debut == 1 ? '' : texte.substring(debut - 2, debut);
                    
                    if (caracteres === '{{' && this.position_input_recherche.debut != debut - 2) {

                        this.position_input_recherche = {
                            debut : debut - 2,
                            curseur : debut,
                        };

                        initialisation_recherche = true;
                    }
                }

                if(!initialisation_recherche)
                    return;

                if(this.recherche !== false)
                    this.suppression_evenenement();

                this.mise_en_place_evenenement();
                this.recherche = '';
                this.modification_recherche();
            },

            modification_recherche: async function() {

                var objet_champ_publipostage = this.objet_champ_publipostage;

                if(!objet_champ_publipostage)
                    return;

                var decalage = { top: 0, left: 0 };

                if(this.type_champ == 'textarea-wysiwyg-vue'){
 
                    var recherche = $(objet_champ_publipostage.getBody()).find('#balise_publipostage_recherche').text();

                    this.recherche = $.trim(recherche).replace('\u200b', '').replace('{{', '');
                
                    var balise_html = $(objet_champ_publipostage.getBody()).find('#balise_publipostage')[0].getBoundingClientRect();
                    var editeur_html = objet_champ_publipostage.getBody().getBoundingClientRect();

                    decalage.top = (editeur_html.bottom - balise_html.bottom + balise_html.height) *-1;
                    decalage.left = balise_html.left;
                }
                else{
                    var valeur = this.contenu;

                    var position_debut = this.position_input_recherche.debut;

                    var texte_avant_curseur = valeur.substring(0, this.position_input_recherche.curseur);
                    var texte = texte_avant_curseur.substring(position_debut + 2);

                    this.recherche = texte.replace(' ', '');

                    decalage.left = position_debut * 8;
                }

                this.decalage = decalage;

                this.gestion_origine_valeur();
            },

            suppression_recherche: function() {

                if(this.recherche === false)
                    return;
                
                var objet_champ_publipostage = this.objet_champ_publipostage;

                if(!objet_champ_publipostage)
                    return;

                if(this.type_champ == 'textarea-wysiwyg-vue'){
                    var texte = this.recherche;
                    var balise_html = objet_champ_publipostage.dom.select('span#balise_publipostage')[0] ?? null;
                    
                    if (balise_html != null) {

                        objet_champ_publipostage.dom.remove(balise_html);

                        if(texte == '')
                            objet_champ_publipostage.execCommand('mceInsertContent', false, '{');
                        else
                            objet_champ_publipostage.execCommand('mceInsertContent', false, '{{'+texte);
                    }
                }

                this.suppression_evenenement();
                this.decalage = { top: 0, left: 0 };
                this.position_input_recherche = {
                    debut: -1,
                    curseur: -1,
                };
                this.recherche = false;
            },

            selection_valeur : async function(index_valeur){

                var objet_champ_publipostage = this.objet_champ_publipostage;

                if(!objet_champ_publipostage)
                    return;

                var index_valeur = index_valeur.split('_');
                var index_groupe = parseInt(index_valeur[0]);
                var index_element = parseInt(index_valeur[1]);

                var groupe_selectionne = this.variables_par_type_filtres[index_groupe];
                var element_selectionne = this.variables_par_type_filtres[index_groupe].elements[index_element];

                if(element_selectionne.valeur.includes('#') && this.elements.length > 0){

                    var balise = element_selectionne.valeur.replaceAll('\{\{','').replaceAll('\}\}','').replaceAll(' ','');

                    element_selectionne.valeur = (await $.post({
                        url : 'eden/email/gestion_publipostage',
                        dataType : 'json',
                        data : {
                            type_element : this.type_element,
                            groupes_ids : [this.elements.map(e => e.id)],
                            valeurs_a_publiposter: [balise],
                        },
                    })).valeurs_publipostes[0][balise] ?? '';
                }

                if (this.type_champ === 'textarea-wysiwyg-vue') {
                                     
                    objet_champ_publipostage.focus();
                    if (groupe_selectionne.id == 'lien_champ') {
                        var nouvelle_recherche = element_selectionne.valeur.replace('{{', '');
                        var recherche_span = objet_champ_publipostage.dom.select('span#balise_publipostage_recherche span.dummy')[0];
                                                
                        recherche_span.innerHTML = nouvelle_recherche;
                        
                        if (recherche_span) {
                            objet_champ_publipostage.selection.select(recherche_span);
                            objet_champ_publipostage.selection.collapse(0);
                        }
                        
                        this.recherche = nouvelle_recherche;
                                                    
                        this.gestion_origine_valeur();
                    } else {

                        var balise_html = objet_champ_publipostage.dom.select('span#balise_publipostage')[0];

                        objet_champ_publipostage.dom.remove(balise_html);
                        objet_champ_publipostage.execCommand('mceInsertContent', false, element_selectionne.valeur);
                        this.suppression_recherche();
                    }
                }
                else{
                    var nouvelleValeur = element_selectionne.valeur;
                    var valeur = this.contenu;
                    var textAfterDebut = valeur.substring(this.position_input_recherche.debut);
                    
                    var position_fin = this.position_input_recherche.curseur;

                    var avant = valeur.substring(0, this.position_input_recherche.debut);
                    var apres = valeur.substring(position_fin);

                    this.contenu = avant + nouvelleValeur + apres;

                    var nouvelle_position = avant.length + nouvelleValeur.length;
                    
                    if(groupe_selectionne.id == 'lien_champ'){
                        this.recherche = nouvelleValeur.replace("\{\{","");
                        this.gestion_origine_valeur();
                        this.position_input_recherche = {
                            debut: avant.length,
                            curseur: nouvelle_position,
                        }
                    }
                    else
                        this.suppression_recherche();

                    await this.$nextTick();
                    if(objet_champ_publipostage.setSelectionRange)
                        objet_champ_publipostage.setSelectionRange(nouvelle_position, nouvelle_position);
                    else{
                        var range = document.createRange();
                        range.setStart(objet_champ_publipostage.firstChild, nouvelle_position);
                        range.collapse(true);
                        var sel = window.getSelection();
                        sel.removeAllRanges();
                        sel.addRange(range);
                    }
                }
            },

            gestion_suppression_recherche(){

                var objet_champ_publipostage = this.objet_champ_publipostage;

                if(!objet_champ_publipostage)
                    return;

                if(this.type_champ == 'textarea-wysiwyg-vue'){
                    var valeur_delimiteur = $(objet_champ_publipostage.dom.select('span#balise_publipostage_delimiteur')).text();
                    var balise_recherche = objet_champ_publipostage.dom.select('span#balise_publipostage_recherche')[0] ?? null;

                    if(balise_recherche == null)
                        valeur_delimiteur = '';
                }  
                else
                    var valeur_delimiteur = this.contenu.substring(this.position_input_recherche.debut,this.position_input_recherche.debut+2);

                if(valeur_delimiteur !== '{{'){
                    this.suppression_recherche();
                }
                else
                    this.modification_recherche();
            },

            balise_recherche_wysiwyg: function() {
                var html_balise = '<span id="balise_publipostage">' +
                    '<span id="balise_publipostage_delimiteur">{{</span>' +
                    '<span id="balise_publipostage_recherche"><span class="dummy">\u200b</span></span>' +
                    '</span>';
                this.objet_champ_publipostage.execCommand('mceInsertContent', false, html_balise);
                this.objet_champ_publipostage.focus();
                this.objet_champ_publipostage.selection.select(this.objet_champ_publipostage.selection.dom.select('span#balise_publipostage_recherche span')[0]);
                this.objet_champ_publipostage.selection.collapse(0);
            },

            ecouter_modification : function(){
                var fonction = this.type_champ == 'textarea-wysiwyg-vue' ? 'on' :'addEventListener';

                this.objet_champ_publipostage[fonction]('keyup',(e) => this.initialisation_recherche(e));
            },

            mise_en_place_evenenement : function(){

                var fonction = this.type_champ == 'textarea-wysiwyg-vue' ? 'on' :'addEventListener';

                this.objet_champ_publipostage[fonction]('keyup', this.evenements.keyup = (e) => this.action_keyup(e));
                this.objet_champ_publipostage[fonction]('keydown', this.evenements.keydown = (e) => this.action_keydown(e), true);
                this.objet_champ_publipostage[fonction]('focusout', this.evenements.focusout = (e) => this.suppression_recherche());
            },

            suppression_evenenement : function(){

                var fonction = this.type_champ == 'textarea-wysiwyg-vue' ? 'off' : 'removeEventListener';

                this.objet_champ_publipostage[fonction]('keyup', this.evenements.keyup);
                this.objet_champ_publipostage[fonction]('keydown', this.evenements.keydown, true);
                this.objet_champ_publipostage[fonction]('focusout', this.evenements.focusout);
            },
            action_keydown : function(e){

                var code_cle = e.which || e.keyCode;

                switch (code_cle) {
                    case 9:
                    case 38:
                    case 40:
                        if (this.$refs.dropdown) {
                            e.preventDefault();
                            this.$refs.dropdown.mouvement_selection(code_cle == 38 ? 'haut' : 'bas');
                        }
                        break;

                    //ENTER
                    case 13:
                    //ESC
                    case 27:
                        e.preventDefault();
                        break;
                    
                    default:
                        break;
                }

                e.stopPropagation();
            },

            action_keyup : function(e){

                if(this.type_champ != 'textarea-wysiwyg-vue')
                    this.position_input_recherche.curseur = this.objet_champ_publipostage.selectionStart;

                var code_cle = e.which || e.keyCode;

                switch (code_cle) {

                    //DOWN ARROW
                    case 40:
                    //UP ARROW
                    case 38:
                    //SHIFT
                    case 16:
                    //CTRL
                    case 17:
                    //ALT
                    case 18:
                    //TAB
                    case 9:
                        break;
                    
                    case 13: // ENTER
                        if (this.$refs.dropdown) {
                            this.selection_valeur(this.$refs.dropdown.element_selectionne);
                        }
                        break;
                    
                    case 27: // ESC
                        this.suppression_recherche();
                        break;
                    
                    default:
                        this.gestion_suppression_recherche();
                }
            },
        },
        computed : {

            variables_par_type_filtres : function(){
                
                var recherches = this.recherche.split('[');

                recherche = recherches[recherches.length - 1];

                if(recherche == '')
                    return this.variables_par_type;

                var variables_par_type = structuredClone(this.variables_par_type);

                for(variables of variables_par_type){
                    variables.elements = variables.elements.filter(variable => variable.nom_sql.includes(recherche) || (variable.index_traduction && this.$root.traduction(variable.index_traduction+'.nom').toLowerCase().includes(recherche.toLowerCase())));
                }

                return variables_par_type.filter(v => v.elements.length > 0);
            },

            objet_champ_publipostage : function(){

                if(this.$refs.champ == null)
                    return;

                if(this.type_champ == 'champ-texte')
                    return this.$refs.champ.$el.querySelector('input');
                else if(this.type_champ == 'textarea')
                    return this.$refs.champ;
                else if(this.type_champ == 'textarea-wysiwyg-vue')
                    return this.$refs.champ.objet_tinymce;
            },

            contenu : {
                get(){
                    return this.modele[this.nom_sql];
                },
                set(valeur){
                    this.$set(this.modele,this.nom_sql,valeur);
                    this.$emit('change');
                }
            },
        },
    });
</script>