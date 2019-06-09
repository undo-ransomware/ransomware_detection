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
    name: 'History',
    components: {
        AppContent,
        ProtectionStatus
    }
}
</script>

<style scoped>
</style>