<template>
    <AppContent>
        <ProtectionStatus>
        </ProtectionStatus>
    </AppContent>
</template>

<script>
import AppContent from 'nextcloud-vue/dist/Components/AppContent'
import ProtectionStatus from '../components/ProtectionStatus'

export default {
    name: 'Protection',
    components: {
        AppContent,
        ProtectionStatus
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