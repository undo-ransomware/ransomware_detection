<template>
    <AppContent>
        <ProtectionStatus :link="servicesUrl" id="protection-status"></ProtectionStatus>
		<div id="services">
			<ServiceStatus :link="detectionServiceUrl" class="service"></ServiceStatus>
			<ServiceStatus :link="monitorServiceUrl" class="service"></ServiceStatus>
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
	},
    computed: {
		servicesUrl() {
            return OC.generateUrl('/apps/ransomware_detection/api/v1/service');
		},
        detectionServiceUrl() {
            return OC.generateUrl('/apps/ransomware_detection/api/v1/service/0');
		},
		monitorServiceUrl() {
            return OC.generateUrl('/apps/ransomware_detection/api/v1/service/1');
        }
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
	#loading {
		display: inline-block;
		vertical-align: sub;
	}
</style>