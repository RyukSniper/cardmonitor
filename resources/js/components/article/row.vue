<template>
    <tr v-if="isEditing">
        <td class="align-middle d-none d-lg-table-cell text-center"><i class="fas fa-fw" :class="item.sync_icon" :title="item.sync_error || 'Karte synchronisiert'"></i></td>
        <td class="align-middle d-none d-xl-table-cell text-center pointer"><i class="fas fa-image" @mouseover="show($event)" @mouseout="$emit('hide')"></i></td>
        <td class="align-middle">
            <span class="flag-icon" :class="'flag-icon-' + item.language.code" :title="item.language.name"></span> {{ item.localName }}<span v-if="item.card.number"> ({{ item.card.number }})</span>
            <div class="d-none d-xl-table-cell text-muted" v-if="item.language_id != 1">{{ item.card.name }}</div>
        </td>
        <td class="align-middle text-center"><expansion-icon :expansion="item.card.expansion" :show-name="false"></expansion-icon></td>
        <td class="align-middle d-none d-xl-table-cell text-center"><rarity :value="item.card.rarity"></rarity></td>
        <td class="align-middle d-none d-lg-table-cell text-center">
            <select class="form-control" v-model="form.language_id">
                <option :value="language_id" v-for="(name, language_id) in languages">{{ name }}</option>
            </select>
            <div class="invalid-feedback" v-text="'unit_price_formatted' in errors ? errors.unit_price_formatted[0] : ''"></div>
        </td>
        <td class="align-middle d-none d-lg-table-cell text-center">
            <select class="form-control" v-model="form.condition">
                <option :value="id" v-for="(name, id) in conditions">{{ name }}</option>
            </select>
            <div class="invalid-feedback" v-text="'condition' in errors ? errors.condition[0] : ''"></div>
        </td>

        <td class="align-middle d-none d-xl-table-cell">
            <div class="form-group form-check mb-0">
                <input class="form-check-input" type="checkbox" id="is_foil" v-model="form.is_foil">
                <label class="form-check-label" for="is_foil">Foil</label>
            </div>
            <div class="form-group form-check mb-0">
                <input class="form-check-input" type="checkbox" id="is_signed" v-model="form.is_signed">
                <label class="form-check-label" for="is_signed">Signed</label>
            </div>
            <div class="form-group form-check mb-0">
                <input class="form-check-input" type="checkbox" id="is_playset" v-model="form.is_playset">
                <label class="form-check-label" for="is_playset">Playset</label>
            </div>
        </td>
        <td class="align-middle d-none d-xl-table-cell text-center">
            <select class="form-control" v-model="form.storage_id">
                <option :value="null">Kein Lagerplatz</option>
                <option :value="storage.id" v-for="(storage, key) in storages" v-html="storage.indentedName"></option>
            </select>
            <div class="invalid-feedback" v-text="'unit_price_formatted' in errors ? errors.unit_price_formatted[0] : ''"></div>
        </td>
        <td class="align-middle d-none d-sm-table-cell text-right">
            <div class="input-group">
                <input class="form-control text-right" :class="'unit_price_formatted' in errors ? 'is-invalid' : ''" type="text" v-model="form.unit_price_formatted" @keydown.enter="update(false)">
                <div class="input-group-append" v-if="item.rule_id">
                    <span class="input-group-text text-left pointer" :title="'Regel ' + item.rule.name" @click="form.unit_price_formatted = item.price_rule_formatted">
                        {{ item.price_rule_formatted }}€
                    </span>
                </div>
            </div>
            <div class="invalid-feedback" v-text="'unit_price_formatted' in errors ? errors.unit_price_formatted[0] : ''"></div>
        </td>
        <td class="align-middle d-none d-xl-table-cell text-right">
            <input class="form-control text-right" :class="'unit_cost_formatted' in errors ? 'is-invalid' : ''" type="text" v-model="form.unit_cost_formatted" @keydown.enter="update(false)">
            <div class="invalid-feedback" v-text="'unit_cost_formatted' in errors ? errors.unit_cost_formatted[0] : ''"></div>
        </td>
        <td class="align-middle d-none d-xl-table-cell text-right">{{ Number(item.provision).format(2, ',', '.') }} €</td>
        <td class="align-middle text-right d-none d-xl-table-cell pointer">{{ Number(item.unit_price - item.unit_cost - item.provision).format(2, ',', '.') }} €</td>
        <td class="align-middle d-none d-sm-table-cell text-right">
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-secondary" title="Speichern" @click="update(false)"><i class="fas fa-fw fa-save"></i></button>
                <button type="button" class="btn btn-secondary" title="Speichern & Exportieren" @click="update(true)"><i class="fas fa-fw fa-cloud-upload-alt"></i></button>
                <button type="button" class="btn btn-secondary" title="Löschen" @click="destroy"><i class="fas fa-fw fa-trash"></i></button>
            </div>
        </td>
    </tr>
    <tr v-else>
        <td class="align-middle d-none d-lg-table-cell text-center"><i class="fas fa-fw fa-euro-sign text-success" title="Verkauft"></i></td>
        <td class="align-middle d-none d-xl-table-cell pointer"><i class="fas fa-image" @mouseover="show($event)" @mouseout="$emit('hide')"></i></td>
        <td class="align-middle">
            <span class="flag-icon" :class="'flag-icon-' + item.language.code" :title="item.language.name"></span> {{ item.localName }} ({{ item.card.number }})
            <div class="text-muted" v-if="item.language_id != 1">{{ item.card.name }}</div></td>
        <td class="align-middle text-center"><expansion-icon :expansion="item.card.expansion" :show-name="false"></expansion-icon></td>
        <td class="align-middle d-none d-xl-table-cell text-center"><rarity :value="item.card.rarity"></rarity></td>
        <td class="align-middle d-none d-lg-table-cell text-center"><span class="flag-icon" :class="'flag-icon-' + item.language.code" :title="item.language.name"></span></td>
        <td class="align-middle d-none d-lg-table-cell text-center"><condition :value="item.condition"></condition></td>
        <td class="align-middle d-none d-xl-table-cell text-center">
            <i class="fas fa-star text-warning" v-if="item.is_foil"></i>
            <span v-if="item.is_signed">S</span>
            <span v-if="item.is_playset">P</span>
        </td>
        <td class="align-middle d-none d-xl-table-cell">{{ item.storage_id ? item.storage.full_name : 'Kein Lagerplatz' }}</td>
        <td class="align-middle d-none d-sm-table-cell text-right">{{ Number(item.unit_price).format(2, ',', '.') }} €</td>
        <td class="align-middle d-none d-xl-table-cell text-right">
            <input class="form-control text-right" :class="'unit_cost_formatted' in errors ? 'is-invalid' : ''" type="text" v-model="form.unit_cost_formatted" @keydown.enter="update(false)">
            <div class="invalid-feedback" v-text="'unit_cost_formatted' in errors ? errors.unit_cost_formatted[0] : ''"></div>
        </td>
        <td class="align-middle d-none d-xl-table-cell text-right">
            <input class="form-control text-right" :class="'provision_formatted' in errors ? 'is-invalid' : ''" type="text" v-model="form.provision_formatted" @keydown.enter="update">
            <div class="invalid-feedback" v-text="'provision_formatted' in errors ? errors.provision_formatted[0] : ''"></div></td>
        <td class="align-middle d-none d-xl-table-cell text-right pointer">{{ Number(item.unit_price - item.unit_cost - item.provision).format(2, ',', '.') }} €</td>
        <td class="align-middle d-none d-sm-table-cell text-right">
            <div class="btn-group btn-group-sm" role="group">
                <a class="btn btn-secondary" :href="item.orders[0].path" :title="'Bestellung ' + item.orders[0].cardmarket_order_id"><i class="fas fa-box"></i></a>
                <button type="button" class="btn btn-secondary" title="Speichern" @click="update(false)"><i class="fas fa-fw fa-save"></i></button>
            </div>
        </td>
    </tr>
</template>

<script>
    import condition from '../partials/emoji/condition.vue';
    import rarity from '../partials/emoji/rarity.vue';
    import expansionIcon from '../expansion/icon.vue';

    export default {

        components: {
            condition,
            expansionIcon,
            rarity,
        },

        props: ['item', 'uri', 'selected', 'conditions', 'languages', 'storages'],

        data () {
            return {
                id: this.item.id,
                isEditing: (this.item.sold_at ? false : true),
                isAdvancedEditing: false,
                form: {
                    cardmarket_comments: this.item.cardmarket_comments,
                    condition: this.item.condition,
                    language_id: this.item.language_id,
                    unit_cost_formatted: this.item.unit_cost_formatted,
                    unit_price_formatted: this.item.unit_price_formatted,
                    provision_formatted: this.item.provision_formatted,
                    is_foil: this.item.is_foil,
                    is_signed: this.item.is_signed,
                    is_playset: this.item.is_playset,
                    sync: false,
                    storage_id: this.item.storage_id,
                },
                errors: {},
            };
        },

        mounted() {
            this.$watch('item', function (newValue, oldValue) {
                this.form = {
                    cardmarket_comments: newValue.cardmarket_comments,
                    condition: newValue.condition,
                    language_id: newValue.language_id,
                    storage_id: newValue.storage_id,
                    unit_cost_formatted: newValue.unit_cost_formatted,
                    unit_price_formatted: newValue.unit_price_formatted,
                    provision_formatted: newValue.provision_formatted,
                    is_foil: newValue.is_foil,
                    is_signed: newValue.is_signed,
                    is_playset: newValue.is_playset,
                    sync: false,
                };
            }, {
                deep: true
            });
        },

        methods: {
            show(event) {
                var position = this.GetScreenCordinates(event.target);
                this.$emit('show', {
                    src: this.item.card.imagePath,
                    top: (position.y - 200) + 'px',
                    left: (position.x - document.getElementById('nav').offsetLeft - 150) + 'px',
                });
            },
            toShow() {
                this.$emit('toshow');
            },
            destroy() {
                var component = this;
                axios.delete(component.item.path)
                    .then( function (response) {
                        if (response.data.deleted) {
                            Vue.success('Karte gelöscht')
                            component.$emit("deleted", component.id);
                            return;
                        }

                        Vue.error('Karte konnte nicht gelöscht werden.');
                    })
            },
            update(sync) {
                var component = this;
                component.form.sync = sync;
                axios.put(component.item.path, component.form)
                    .then( function (response) {
                        component.errors = {};
                        component.$emit('updated', response.data);
                        Vue.success('Artikel gespeichert' + (sync ? ' und hochgeladen' : '') +'.');
                    })
                    .catch(function (error) {
                        component.errors = error.response.data.errors;
                        Vue.error('Artikel konnte nicht gespeichert werden.');
                });
            },
            GetScreenCordinates(obj) {
                var p = {};
                p.x = obj.offsetLeft;
                p.y = obj.offsetTop;
                while (obj.offsetParent) {
                    p.x = p.x + obj.offsetParent.offsetLeft;
                    p.y = p.y + obj.offsetParent.offsetTop;
                    if (obj == document.getElementsByTagName("body")[0]) {
                        break;
                    }
                    else {
                        obj = obj.offsetParent;
                    }
                }
                return p;
            },
        },
    };
</script>

<style>

    .price_rule_wrapper {
        white-space: nowrap;
        max-width: 100%;
    }

    .price_rule {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>