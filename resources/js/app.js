/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');
window.Bus = new Vue();
window.Highcharts = require('highcharts');

/**
 * Number.prototype.format(n, x, s, c)
 *
 * @param integer n: length of decimal
 * @param integer x: length of whole part
 * @param mixed   s: sections delimiter
 * @param mixed   c: decimal delimiter
 */
Number.prototype.format = function(decimals, dec_point, thousands_sep) {
    var number = (this + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
          var k = Math.pow(10, prec);
          return '' + (Math.round(n * k) / k).toFixed(prec);
    };
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
};

Highcharts.setOptions({
    lang: {
        decimalPoint: ',',
        thousandsSep: '.'
    }
});

import Flash from './plugins/flash.js';

Vue.use(Flash);

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i);
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

Vue.component('flash-message', require('./components/partials/flashmessage.vue').default);

Vue.component('article-table', require('./components/article/table.vue').default);
Vue.component('item-table', require('./components/item/table.vue').default);
Vue.component('item-quantity-table', require('./components/item/quantity/table.vue').default);
Vue.component('order-table', require('./components/order/table.vue').default);
Vue.component('order-article-index', require('./components/order/article/index.vue').default);
Vue.component('imageable-table', require('./components/image/imageable/table.vue').default);
Vue.component('imageable-gallery', require('./components/image/imageable/gallery.vue').default);
Vue.component('home-order-index', require('./components/home/order/index.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
});

$('#menu-toggle').click(function() {
    $('#nav, #content-container').toggleClass('active');
    $('.collapse.in').toggleClass('in');
    $('a[aria-expanded=true]').attr('aria-expanded', 'false');
});

$('.collapse', 'nav#nav').on('show.bs.collapse', function(){
    $('a[data-target="#' + $(this).attr('id') +'"] i.fas', 'nav#nav').toggleClass("fa-caret-right fa-caret-down");
}).on('hide.bs.collapse', function(){
    $('a[data-target="#' + $(this).attr('id') +'"] i.fas', 'nav#nav').toggleClass("fa-caret-down fa-caret-right");
});

$('#message-create').on('show.bs.modal', function (e) {
    axios.get('/order/' + $(e.relatedTarget).attr('data-model-id') + '/message/create')
        .then(function (response) {
            $('#message-text', '#message-create').val(response.data.body);
    });
});
