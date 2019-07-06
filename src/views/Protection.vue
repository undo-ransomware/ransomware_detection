<template>
    <AppContent>
		<iron-pages selected="0">
			<div id="loading">
				<paper-spinner active></paper-spinner>
			</div>
			<div>
				<ProtectionStatus :detection-link="detectionUrl" :protection-link="servicesUrl" id="protection-status" v-on:protection-state-changed="protectionStateChanged"></ProtectionStatus>
				<div id="services">
					<ServiceStatus :link="detectionServiceUrl" v-on:service-state-changed="detectionStateChanged" class="service"></ServiceStatus>
					<ServiceStatus :link="monitorServiceUrl" v-on:service-state-changed="monitorStateChanged" class="service"></ServiceStatus>
				</div>
			</div>
		</iron-pages>
    </AppContent>
</template>

<script>
import '@polymer/paper-spinner/paper-spinner.js';
import '@polymer/iron-pages/iron-pages.js';
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
	data() {
        return {
			protectionReady: false,
			detectionReady: false,
			monitorReady: false
        };
    },
    computed: {
		detectionUrl() {
            return OC.generateUrl('/apps/ransomware_detection/api/v1/detection');
		},
		servicesUrl() {
            return OC.generateUrl('/apps/ransomware_detection/api/v1/service');
		},
        detectionServiceUrl() {
            return OC.generateUrl('/apps/ransomware_detection/api/v1/service/0');
		},
		monitorServiceUrl() {
            return OC.generateUrl('/apps/ransomware_detection/api/v1/service/1');
        }
	},
	methods: {
		protectionStateChanged() {
			this.protectionReady = true;
			this.hideSpinner();
		},
		monitorStateChanged() {
			this.monitorReady = true;
			this.hideSpinner();
		},
		detectionStateChanged() {
			this.detectionReady = true;
			this.hideSpinner();
		},
		hideSpinner() {
			if (this.protectionReady && this.monitorReady && this.detectionReady) {
				document.querySelector('iron-pages').selectIndex(1);
			}
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
		display: flex;
		align-items: center;
		height: 90vh;
		justify-content: center;
	}
</style>