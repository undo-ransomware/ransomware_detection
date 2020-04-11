<template>
    <AppContent>
		<iron-pages selected="0">
			<div id="loading">
				<paper-spinner active></paper-spinner>
			</div>
			<div>
				<ProtectionStatus :detection-link="detectionUrl" id="protection-status" v-on:protection-state-changed="protectionStateChanged"></ProtectionStatus>
			</div>
		</iron-pages>
    </AppContent>
</template>

<script>
import '@polymer/paper-spinner/paper-spinner.js';
import '@polymer/iron-pages/iron-pages.js';
import AppContent from 'nextcloud-vue/dist/Components/AppContent'
import ProtectionStatus from '../components/ProtectionStatus'

export default {
    name: 'Protection',
    components: {
        AppContent,
		ProtectionStatus
	},
	data() {
        return {
			protectionReady: false,
        };
    },
    computed: {
		detectionUrl() {
            return OC.generateUrl('/apps/ransomware_detection/api/v1/detection');
		}
	},
	methods: {
		protectionStateChanged() {
			this.protectionReady = true;
			this.hideSpinner();
		},
		hideSpinner() {
			if (this.protectionReady) {
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
	#loading {
		display: flex;
		align-items: center;
		height: 90vh;
		justify-content: center;
	}
</style>