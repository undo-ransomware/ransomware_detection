<template>
    <AppContent>
        <iron-pages selected="0">
			<div id="loading" class="page">
				<paper-spinner active></paper-spinner>
			</div>
			<div class="page">
                <Header header="Recover">
                    <RecoverAction v-if="detected" id="recover" label="Recover" v-on:recover="onRecover" primary></RecoverAction>
                </Header>
                <RecoveryTable v-if="detected" id="ransomware-table" :data="fileOperations" v-on:table-state-changed="tableStateChanged"></RecoveryTable>
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
import RecoverAction from '../components/RecoverAction'
import AppContent from 'nextcloud-vue/dist/Components/AppContent'

export default {
    name: 'Recover',
    components: {
		AppContent,
        RecoveryTable,
        Header,
        RecoverAction
    },
    data() {
        return {
            detected: 0,
            fileOperations: []
        };
    },
    created() {
        this.fetchDetectionStatus();
        this.fetchData();
    },
    methods: {
        fetchDetectionStatus() {
            this.$axios({
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
        },
        fetchData() {
            this.$axios({
                method: 'GET',
                url: this.recoverUrl
            })
            .then(json => {
                this.fileOperations = json.data;
            })
            .catch( error => { console.error(error); });
        },
        onRecover() {
            const items = document.querySelector('#ransomware-table').items;
            const selected = document.querySelector('#ransomware-table').selectedItems;
            for (var i = 0; i < selected.length; i++) {
                this.remove(selected[i].id);
				/*this.$axios({
					method: this.type || 'GET',
					url: this.link + '/' + selected[i].id + '/recover'
				})
					.then(response => {
						switch(response.status) {
							case 204:
								document.querySelector('#ransomware-table').remove(selected[i].id);
								break;
							default:
								console.log(response);
								break;
						}
					})
					.catch(() => {
					});*/
			}
        },
        remove(id) {
            for (var i = 0; i < this.fileOperations.length; i++) {
                if (this.fileOperations[i].id === id) {
                    this.fileOperations.splice(i, 1);
                }
            }
        }
    },
    computed: {
        detectionsUrl() {
            return OC.generateUrl('/apps/ransomware_detection/api/v1/detection');
        },
        recoverUrl() {
            return OC.generateUrl('/apps/ransomware_detection/api/v1/file-operation')
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