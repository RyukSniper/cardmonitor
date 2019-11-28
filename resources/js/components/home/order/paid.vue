<template>
    <div class="col-12 col-md-6 col-lg-4 col-xl mb-3 mb-xl-0 d-none d-xl-block" v-if="items.length > 0">
        <div class="card h-100">
            <div class="card-header d-flex">
                <div class="col">Bezahlte Bestellungen</div>
                <div><i class="fas fa-sync pointer" @click="sync" :class="{'fa-spin': syncing.status == 1}"></i></div>
            </div>

            <div class="card-body">
                <div v-if="isLoading" class="mt-3 p-5">
                    <center>
                        <span style="font-size: 48px;">
                            <i class="fas fa-spinner fa-spin"></i><br />
                        </span>
                        Lade Daten..
                    </center>
                </div>
                <table class="table table-striped table-hover" v-else>
                    <thead>
                        <tr>
                            <th>Datum</th>
                            <th>Bestellung</th>
                            <th width="100"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, key) in items">
                            <td class="align-middle">{{ item.paid_at_formatted }}</td>
                            <td class="align-middle">
                                <a :href="item.path">{{ item.cardmarket_order_id }}</a>
                                <div class="text-muted">{{ item.buyer.name }}</div>
                            </td>
                            <td class="align-middle">
                                <div>{{ item.revenue_formatted }} € </div>
                                <div>{{ item.articles_count }} Artikel</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
    export default {

        props: {
            isSyncingOrders: {
                required: true,
                type: Number,
            },
        },

        data() {
            return {
                uri: '/order',
                isLoading: true,
                syncing: {
                    status: this.isSyncingOrders,
                    interval: null,
                    timeout: null,
                },
                filter: {
                    state: 'paid',
                },
                items: [],
            };
        },

        mounted() {
            if (this.isSyncingOrders) {
                this.checkIsSyncingOrders();
            }
            else {
                this.fetch();
            }
            console.log(this.items.length);
        },

        methods: {
            fetch() {
                var component = this;
                component.isLoading = true;
                axios.get(component.uri, {
                    params: component.filter
                })
                    .then(function (response) {
                        component.items = response.data.data;
                        component.isLoading = false;
                        setTimeout( function () {
                            component.sync();
                        }, 1000 * 60 * 60);
                    })
                    .catch(function (error) {
                        Vue.error('Bestellungen konnten nicht geladen werden!');
                        console.log(error);
                    });
            },
            checkIsSyncingOrders() {
                var component = this;
                this.syncing.interval = setInterval(function () {
                    component.getIsSyncingOrders()
                }, 3000);
            },
            getIsSyncingOrders() {
                var component = this;
                axios.get('/order/sync')
                    .then(function (response) {
                        component.syncing.status = response.data.is_syncing_articles;
                        if (component.syncing.status == 0) {
                            clearInterval(component.syncing.interval)
                            component.syncing.interval = null;
                            component.fetch();
                            Vue.success('Bestellungen wurden synchronisiert.');
                        }
                    })
                    .catch(function (error) {
                        console.log(error);
                    })
                    .finally ( function () {

                    });
            },
            sync() {
                var component = this;
                if (component.syncing.status == 1) {
                    return;
                }
                clearTimeout(component.syncing.timeout);
                axios.put('/order/sync', component.filter)
                    .then(function (response) {
                        component.syncing.status = 1;
                        component.checkIsSyncingOrders();
                        Vue.success('Bestellungen werden im Hintergrund aktualisiert.');
                    })
                    .catch(function (error) {
                        Vue.error('Bestellungen konnten nicht synchronisiert werden! Ist das Cardmarket Konto verbunden?');
                        console.log(error);
                    })
                    .finally ( function () {

                    });
            },
        },

    };
</script>