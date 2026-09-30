<div class="row" v-if="(tache.id == 0 || tache.id == undefined)">
    <div class="col-sm-12">
        <table class="table">
            <tr>
                <td>@traduction('interface.planning.dates')</td>
                <td>@traduction('interface.planning.am')</td>
                <td>@traduction('interface.planning.pm')</td>
            </tr>
            <template v-for="semaine in dates.planning.semaine">
                <tr v-for="(n, index) in semaine.dates" v-if="tache.dates != undefined">
                    <td class="date">@{{ semaine.dates[index] }}</td>
                    <td>
                        <input type="checkbox" class="css_checkbox_on js_date_choisie" v-model="tache.dates.am" :value="semaine.dates_format[index]" name="dates[am][]" />
                    </td>
                    <td>
                        <input type="checkbox" class="css_checkbox_on js_date_choisie" v-model="tache.dates.pm" :value="semaine.dates_format[index]" name="dates[pm][]" />
                    </td>
                </tr>
            </template>
        </table>
    </div>
</div>

@push('donnees_pour_vuejs_computed')

    dates : function(){

        var instance = this;

        if(instance.$parent !== undefined && instance.$parent.$attrs.dates)
            return instance.$parent.$attrs.dates;

        return {};

    },

@endpush