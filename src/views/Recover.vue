<template>
    <AppContent>
    	<RansomwareTable :link="fileOperationsUrl"></RansomwareTable>
    </AppContent>
</template>

<script>
import RansomwareTable from '../components/RansomwareTable'
import AppContent from 'nextcloud-vue/dist/Components/AppContent'

export default {
    name: 'Recover',
    components: {
		AppContent,
        RansomwareTable
    },
    computed: {
        fileOperationsUrl() {
            return OC.generateUrl('/apps/ransomware_detection/api/v1/file-operation');
        }
    }
}
</script>

<style scoped>
</style>