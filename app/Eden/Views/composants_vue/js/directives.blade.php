<script>

Vue.directive('clique_en_dehors', (function(){

    var surveilles = new Set();

    var au_clic = function(event){

        if(surveilles.size === 0)
            return;

        if(event.target && !document.body.contains(event.target))
            return;

        surveilles.forEach(function(el){

            if(el.contains(event.target))
                return;

            var binding = el._clique_en_dehors;

            if(!binding || !binding.value || typeof binding.value.func !== 'function')
                return;

            if(binding.value.params)
                binding.value.func(...binding.value.params);
            else
                binding.value.func();
        });
    };

    return {
        bind: function (el, binding) {

            el._clique_en_dehors = binding;
            surveilles.add(el);

            if(surveilles.size === 1)
                document.body.addEventListener('click', au_clic);
        },
        update: function (el, binding) {
            el._clique_en_dehors = binding;
        },
        unbind: function (el) {

            surveilles.delete(el);
            delete el._clique_en_dehors;

            if(surveilles.size === 0)
                document.body.removeEventListener('click', au_clic);
        },
    };
})());

</script>
