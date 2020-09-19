import "core-js/stable";
import "regenerator-runtime/runtime";

import Vue from 'vue'
import App from './App'
import router from './router'
import VueMoment from 'vue-moment'
import AsyncComputed from 'vue-async-computed'
import axios from "axios";
import vuetify from './plugins/vuetify'


// CSP config for webpack dynamic chunk loading
// eslint-disable-next-line
__webpack_nonce__ = btoa(OC.requestToken)

// Correct the root of the app for chunk loading
// OC.linkTo matches the apps folders
// eslint-disable-next-line
__webpack_public_path__ = OC.linkTo('ransomware_detection', 'js/')

import "./css/global.scss"

Vue.prototype.t = t
Vue.prototype.n = n
Vue.prototype.OC = OC
Vue.prototype.OCA = OCA
Vue.prototype.$axios = axios

Vue.use(VueMoment);
Vue.use(AsyncComputed);

Vue.config.devtools = true

/* eslint-disable-next-line no-new */
new Vue({
	el: '#content',
	vuetify,
	router,
	render: h => h(App)
})