<script>
    const stock_inventaire_reel = Vue.component('stock-inventaire-reel', {
        template: `
            <div style="display:flex;align-items:center;justify-content: flex-end;">
                <input class="input_stock_inventaire_reel" id_ligne="" v-model="montant" />
                <div class="bouton_stock_inventaire_reel" @click="ajustement_detail_stock_inventaire">
                    <i class="fas fa-save"></i>
                </div>
            </div>
        `,
        props:{
            article_id:0,
            entrepot_id:0,
            conditionnement_id:0,
        },
        data: function(){

            return {
                montant: '',
            }

        },

        methods:{

            ajustement_detail_stock_inventaire: async function (){

                var nouveau_montant = parseFloat(this.montant);

                if(Number.isNaN(nouveau_montant))
                    return;

                var vue_composant = this;

                if(!await confirm_eden())
                    return false;

                loading(true);

                $.post({
                    url: "/eden/rapports/ajustement_stock",
                    dataType:"json",
                    data:{
                        article_id: vue_composant.article_id,
                        entrepot_id: vue_composant.entrepot_id,
                        conditionnement_id: vue_composant.conditionnement_id,
                        quantite_reel: nouveau_montant,
                    }
                }).done(function(){

                    loading(false);

                    vue_composant.montant = '';
                    vue_composant.$parent.$parent.$emit('actualisation');
                    vue_composant.$parent.$parent.$parent.$emit('actualisation');
                });
            },

        },
    });
</script>