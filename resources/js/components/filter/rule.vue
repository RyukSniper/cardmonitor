<template>
    <div class="form-group">
        <label for="filter-rule">Regel</label>
        <select class="form-control" id="filter-rule" v-model="value" @change="$emit('input', value)">
            <option :value="0">Alle</option>
            <option :value="null">Ohne Regel</option>
            <option v-for="(option, key) in sortedOptions" :value="option.id">{{ option.name }}</option>
        </select>
    </div>
</template>

<script>
    export default {
        props: [
            'initialValue',
            'options',
        ],

        computed: {
            sortedOptions: function() {
                function compare(a, b) {
                    if (a.name < b.name) {
                        return -1;
                    }

                    if (a.name > b.name) {
                        return 1;
                    }

                    return 0;
                }

                return this.options.sort(compare);
            },
        },

        data () {
            return {
                value: this.initialValue || 0,
            };
        },
    };
</script>