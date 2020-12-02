import Vue from 'vue'
import Router from 'vue-router'
import {generateUrl} from 'nextcloud-server/dist/router'

const Protection = () => import('./views/Protection')
const Recover = () => import('./views/Recover')
const History = () => import('./views/History')

Vue.use(Router)

export default new Router({
	base: generateUrl('/apps/ransowmare_detection/'),
	linkActiveClass: 'active',
	routes: [
		{
			path: '/',
			name: 'protection',
			component: Protection,
        },
        {
			path: '/recover',
			name: 'recover',
			component: Recover,
        },
        {
			path: '/history',
			name: 'history',
			component: History,
		},
	],
})