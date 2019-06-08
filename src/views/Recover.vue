<template>
    <AppContent>
    	<RansomwareTable></RansomwareTable>
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
    }
}
</script>

<style scoped>
</style>