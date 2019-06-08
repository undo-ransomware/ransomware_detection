<template>
    <Content app-name="ransomware_detection">
        <AppNavigation>
            <ul>
				<AppNavigationItem v-for="item in menu" :key="item.key" :item="item" />
			</ul>
        </AppNavigation>
            <router-view />
    </Content>
</template>

<script>
import Content from 'nextcloud-vue/dist/Components/Content'
import AppNavigation from 'nextcloud-vue/dist/Components/AppNavigation'
import AppNavigationItem from 'nextcloud-vue/dist/Components/AppNavigationItem'
import AppContent from 'nextcloud-vue/dist/Components/AppContent'

export default {
    name: 'App',
    components: {
		Content,
        AppNavigation,
        AppNavigationItem,
        AppContent,
    },
    computed: {
        menu() {
            return [
				{
					id: 'app-category-protection',
					classes: [],
					router: {name: 'protection'},
					icon: 'icon-category-installed',
					text: t('ransomware_detection', 'Protection'),
				},
				{
					id: 'app-category-recover',
					classes: [],
					icon: 'icon-category-enabled',
					router: {name: 'recover'},
					text: t('ransomware_detection', 'Recover'),
				}, {
					id: 'app-category-history',
					classes: [],
					icon: 'icon-category-disabled',
					router: {name: 'history'},
					text: t('ransomware_detection', 'History'),
				}
			];
        }
    }
}
</script>

<style scoped>
</style>