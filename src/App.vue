<template>
    <router-view />
</template>

<script>
import AppNavigation from 'nextcloud-vue/dist/Components/AppNavigation'
import AppContent from 'nextcloud-vue/dist/Components/AppContent'
import RansomwareTable from './components/RansomwareTable';
import ProtectionStatus from './components/ProtectionStatus';

export default {
    name: 'App',
    components: {
        AppNavigation,
        AppContent,
        RansomwareTable,
        ProtectionStatus
    }
}
</script>

<style scoped>
</style>