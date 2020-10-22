require('./bootstrap');

import Vue from 'vue'

//Main pages
import Search from './views/search.vue'


const app = new Vue({
    el: '#app',
    components: { Search }
});
