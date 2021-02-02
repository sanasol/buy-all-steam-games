window.Vue = require('vue');

import Chart from './components/Chart'

Vue.component("chart", Chart);

const app = new Vue({
    el: '#app'
});
