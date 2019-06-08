import "core-js/stable";
import "regenerator-runtime/runtime";

import Vue from 'vue'
import App from './App'
import router from './router'
import {sync} from 'vuex-router-sync'


// CSP config for webpack dynamic chunk loading
// eslint-disable-next-line
__webpack_nonce__ = btoa(OC.requestToken)

// Correct the root of the app for chunk loading
// OC.linkTo matches the apps folders
// eslint-disable-next-line
__webpack_public_path__ = OC.linkTo('ransomware_detection', 'js/')

Vue.prototype.t = t
Vue.prototype.n = n
Vue.prototype.OC = OC
Vue.prototype.OCA = OCA

/* eslint-disable-next-line no-new */
new Vue({
	el: '#content',
	router,
	render: h => h(App)
})