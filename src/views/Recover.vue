<template>
    <AppContent>
        <iron-pages selected="0">
			<div id="loading" class="page">
				<paper-spinner active></paper-spinner>
			</div>
			<div class="page">
                <Header header="Recover">
                    <Action v-if="detected" id="recover" label="Recover" link="" type="GET" primary></Action>
                </Header>
                <RecoveryTable v-if="detected" id="ransomware-table" :link="detectionsUrl" v-on:table-state-changed="tableStateChanged"></RecoveryTable>
                <span id="message" v-if="!detected">
                    <iron-icon icon="verified-user"></iron-icon>
                    Nothing found. You are save.
                </span>
            </div>
		</iron-pages>
    </AppContent>
</template>

<script>
import '@polymer/paper-spinner/paper-spinner.js';
import '@polymer/iron-pages/iron-pages.js';
import '@polymer/iron-icon/iron-icon.js';
import '@polymer/iron-icons/iron-icons.js';
import RecoveryTable from '../components/RecoveryTable'
import Header from '../components/Header'
import Action from '../components/Action'
import AppContent from 'nextcloud-vue/dist/Components/AppContent'
import axios from 'nextcloud-axios'

export default {
    name: 'Recover',
    components: {
		AppContent,
        RecoveryTable,
        Header,
        Action
    },
    data() {
        return {
            detected: 0
        };
    },
    created() {
        this.fetchDetectionStatus();
    },
    methods: {
        fetchDetectionStatus() {
            axios({
				method: 'GET',
				url: this.detectionsUrl
            })
            .then(json => {
                if (json.data.length > 0) {
                    this.detected = 1;
                } else {
                    document.querySelector('iron-pages').selectIndex(1);
                }
            })
            .catch( error => { console.error(error); });
        },
        tableStateChanged() {
            document.querySelector('iron-pages').selectIndex(1);
        }
    },
    computed: {
        detectionsUrl() {
            return OC.generateUrl('/apps/ransomware_detection/api/v1/detection');
        }
    }
}
</script>

<style scoped>
    #ransomware-table {
        height: calc(100% - 50px);
    }
    #recover {
        background-color: green;
        color: #fff;
    }
    #message {
        display: flex;
        justify-content: center;
        font-size: 1.5em;
        font-weight: bold;
    }
    iron-pages {
        height: 100%;
    }
    .page {
        height: 100%;
    }
    #loading {
		display: flex;
		align-items: center;
		height: 90vh;
		justify-content: center;
	}
</style>