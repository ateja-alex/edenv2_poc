<script>
const dropdown = Vue.component('dropdown', {
    template: `<div class="composant_dropdown" :style="style_dropdown">
        <slot>
            <template v-for="(groupe, index_groupe) in elements_par_categorie">
                <div class="titre" v-html="groupe.titre"></div>
                <div v-for="(element, index_element) in groupe.elements" 
                    @mousedown.prevent="$emit('select', element_selectionne)"
                    @mouseover="element_selectionne = index_groupe + '_' + index_element"
                    :class="{selectionne: element_selectionne == index_groupe+'_'+index_element}">
                    <span class="element" v-html="element.titre"></span>
                </div>
            </template>
        </slot>
    </div>`,
    props:{
        decalage : {
            type : Object,
            default : function(){
                return {
                    top : 0,
                    left : 0,
                }
            }
        },
        elements_par_categorie : {
            type : Array,
            default : function(){
                return [];
            },
        },
    },
    data : function(){
        return {
            style_dropdown : '',
            element_selectionne : '0_0',
        }
    },
    methods : {
        calcul_style_dropdown : function(){

            if(!this.$el.parentElement)
                return;

            const bouton = this.$el.parentElement.getBoundingClientRect();
            var hauteur = this.$el.offsetHeight ?? 300;
            var largeur = this.$el.offsetWidth ?? 300;

            var parent_overflow = this.parent_overflow();
            var hauteux_max = parent_overflow == window ? parent_overflow.innerHeight : parent_overflow.getBoundingClientRect().bottom;
            var hauteur_min = parent_overflow == window ? 0 : parent_overflow.getBoundingClientRect().top;

            if(bouton.top + this.decalage.top > hauteux_max || bouton.bottom + this.decalage.top < hauteur_min)
                var style_dropdown = "display:none;";
            else if (bouton.top + hauteur > hauteux_max)
                var style_dropdown = "top:"+ (bouton.top - this.decalage.top - hauteur) + "px";
            else
                var style_dropdown = "top:"+ (bouton.bottom + this.decalage.top) + "px";

            var gauche = bouton.left + this.decalage.left;

            if(gauche + largeur > window.innerWidth)
                gauche = Math.max(window.innerWidth - largeur, 0);

            style_dropdown += ";left:"+ gauche + "px";

            this.style_dropdown = style_dropdown;
        },
        parent_overflow : function(){
            var parent_overflow = null;
            let parent = this.$el.parentElement;

            while (parent_overflow == null && parent != null) {
                const overflowY = window.getComputedStyle(parent).overflowY;

                if (overflowY === 'auto' || overflowY === 'scroll' || overflowY === 'overlay') {
                    if (parent.scrollHeight > parent.clientHeight) {
                        parent_overflow = parent;
                    }
                }

                parent = parent.parentElement;
            }

            if(parent_overflow == null)
                parent_overflow = window;

            return parent_overflow;
        },

        mouvement_selection: function(mouvement){

            if(this.elements_par_categorie.length == 0)
                return;

            var element_selectionne = this.element_selectionne.split('_');
            var index_groupe = parseInt(element_selectionne[0]);
            var index_element = parseInt(element_selectionne[1]);

            if(mouvement == 'bas'){
                index_element++;

                if(index_element >= this.elements_par_categorie[index_groupe].elements.length){

                    index_element = 0;

                    index_groupe++;

                    if(index_groupe >= this.elements_par_categorie.length){
                        index_groupe = 0;
                    }
                }
            } else if(mouvement == 'haut'){
                index_element--;

                if(index_element < 0){

                    index_groupe--;

                    if(index_groupe < 0){
                        index_groupe = this.elements_par_categorie.length - 1;
                    }
                    
                    index_element = this.elements_par_categorie[index_groupe].elements.length - 1;
                }
            }

            this.element_selectionne = index_groupe + '_' + index_element;
            this.$nextTick(() => {
                this.gestion_scroll_selection();
            });
        },

        gestion_scroll_selection: function() {
            var selectionne = this.$el.querySelector('.selectionne');
            if (selectionne && typeof selectionne.scrollIntoView === 'function') {
                selectionne.scrollIntoView({ block: 'nearest', behavior: 'auto' });
            }
        },
    },
    watch : {
        'decalage' : {
            handler : function() {
                this.calcul_style_dropdown();
            },
            deep:true
        },
        'elements_par_categorie' : {
            handler : function() {
                this.element_selectionne = '0_0';
            },
            deep:true
        },
    },
    mounted : async function(){

        await this.$nextTick();

        this.calcul_style_dropdown();
        this.parent_overflow().addEventListener('scroll', () => {
            this.calcul_style_dropdown();
        });
    },
});