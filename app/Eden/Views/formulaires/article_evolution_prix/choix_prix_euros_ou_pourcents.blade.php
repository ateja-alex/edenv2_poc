<template>

    <div v-if="elements_concernes != 1">
        <div class="row my-1">
            <div class="col-sm-2">
                @traduction('formulaire.choix_prix_euros_ou_pourcents.evolution')
            </div>
            <div class="col-sm-4">
                <select name="choix_prix">
                    <option value="euros">{{ traduction('formulaire.choix_prix_euros_ou_pourcents.en_devises') }}</option>
                    <option value="pourcents">{{ traduction('formulaire.choix_prix_euros_ou_pourcents.en_pourcents') }}</option>
                </select>
            </div>
        </div>
        <div class="row my-1">
            <div class="col-sm-2">
                @traduction('formulaire.choix_prix_euros_ou_pourcents.valeur')
            </div>
            <div class="col-sm-4">
                <input type="number" name="valeur_evolution" @wheel.prevent @keydown.up.prevent @keydown.down.prevent>
            </div>
        </div>
    </div>
    <div v-else>
        <div class="row my-1">
            <div class="col-sm-2">
               @traduction('formulaire.choix_prix_euros_ou_pourcents.nouveau_prix')
            </div>
            <div class="col-sm-4 css_champ_obligatoire">
                <input type="number" name="nouveau_prix" @wheel.prevent @keydown.up.prevent @keydown.down.prevent>
            </div>
        </div>
    </div>

</template>