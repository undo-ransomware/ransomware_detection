<template>
    <AppContent>
        <ProtectionStatus id="protection-status">
        </ProtectionStatus>
		<div id="services">
			<ServiceStatus class="service"></ServiceStatus>
			<ServiceStatus class="service"></ServiceStatus>
		</div>
    </AppContent>
</template>

<script>
import AppContent from 'nextcloud-vue/dist/Components/AppContent'
import ProtectionStatus from '../components/ProtectionStatus'
import ServiceStatus from '../components/ServiceStatus'

export default {
    name: 'Protection',
    components: {
        AppContent,
		ProtectionStatus,
		ServiceStatus
    }
}
</script>

<style scoped>
	#protection-status {
		height: 40vh;
	}
	#services {
		height: 50vh;
		display: flex;
		align-items: center;
		justify-content: space-around;
	}
	#services .service {
		width: 40%;
		height: 40%;
	}
</style>