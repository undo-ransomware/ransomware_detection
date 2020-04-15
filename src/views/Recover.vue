<template>
    <AppContent>
        <iron-pages :selected="page">
			<div id="loading" class="page">
				<paper-spinner active></paper-spinner>
			</div>
			<div class="page">
                <div class="notification-wrapper">
                    <Notification :text.sync="notificationText" @on-close="closeNotification" :visible.sync="visible"></Notification>
                </div>
                <Header header="Recover">
                    <RecoverAction id="recover" label="Recover selected files" v-on:recover="onRecover" primary></RecoverAction>
                </Header>
                <div id="tables" v-if="detected">
                    <FileOperationsTable v-for="(detection, index) in detections" :key="index" class="ransomware-table" :data="detection.fileOperations" v-on:table-state-changed="tableStateChanged"></FileOperationsTable>
                </div>
                <span id="message" v-if="!detected">
                    <iron-icon icon="verified-user"></iron-icon>
                    Nothing found. You are safe.
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
import FileOperationsTable from '../components/FileOperationsTable'
import Notification from '../components/Notification'
import Header from '../components/Header'
import RecoverAction from '../components/RecoverAction'
import AppContent from 'nextcloud-vue/dist/Components/AppContent'

export default {
    name: 'Recover',
    components: {
		AppContent,
        FileOperationsTable,
        Header,
        RecoverAction,
        Notification
    },
    data() {
        return {
            detected: 0,
            detections: [],
            page: 0,
            visible: false,
            notificationText: ""
        };
    },
    mounted() {
        this.page = 0;
        this.fetchDetectionStatus();
        this.fetchDetections();
    },
    methods: {
        closeNotification() {
            this.visible = false
        },
        notice(text) {
            this.notificationText = text;
            this.visible = true;

        },
        buildNotification(deleted, recovered) {
            var notificationText = "";
            if (deleted > 0 && recovered > 0) {
                notificationText = deleted + " files deleted, " + recovered + " files recovered from backup."
            }
            if (recovered > 0 && deleted == 0) {
                notificationText = deleted + " files recovered from backup."
            }
            if (deleted > 0 && recovered == 0) {
                notificationText = deleted + " files deleted."
            }
            if (deleted == 0 && recovered == 0) {
                notificationText = "No files deleted or recovered."
            }
            this.notice(notificationText);
        },
        fetchDetectionStatus() {
            this.$axios({
				method: 'GET',
				url: this.detectionsUrl
            })
            .then(json => {
                if (json.data.length > 0) {
                    this.detected = 1;
                } else {
                    this.page = 1;
                }
            })
            .catch( error => { console.error(error); });
        },
        tableStateChanged() {
            this.page = 1;
        },
        fetchDetections() {
            this.$axios({
                method: 'GET',
                url: this.detectionsUrl
            })
            .then(json => {
                this.detections = json.data;
            })
            .catch( error => { console.error(error); });
        },
        onRecover() {
            var itemsToRecover = [];
            const items = document.querySelector('#ransomware-table').items;
            const selected = document.querySelector('#ransomware-table').selectedItems;
            for (var i = 0; i < selected.length; i++) {
                itemsToRecover.push(selected[i].id);
            }
            this.recover(itemsToRecover);
        },
        remove(ids) {
            ids.forEach(id => {
                for (var i = 0; i < this.fileOperations.length; i++) {
                    if (this.fileOperations[i].id === id) {
                        this.fileOperations.splice(i, 1);
                    }
                }
            });
        },
        async recover(ids) {
            await this.$axios({
					method: 'PUT',
                    url: this.recoverUrl + '/recover',
                    data: {
                        ids: ids
                    }
				})
					.then(response => {
						switch(response.status) {
							case 200:
                                this.buildNotification(response.data.deleted, response.data.recovered);
                                if(response.data.filesRecovered.length > 0)
								    this.remove(response.data.filesRecovered);
								break;
							default:
								this.buildNotification(response.data.deleted, response.data.recovered);
								if(response.data.filesRecovered.length > 0)
								    this.remove(response.data.filesRecovered);
								break;
						}
					})
					.catch(error => {
                        console.error(error);
					});
        }
    },
    computed: {
        detectionsUrl() {
            return OC.generateUrl('/apps/ransomware_detection/api/v1/detection');
        },
        recoverUrl() {
            return OC.generateUrl('/apps/ransomware_detection/api/v1/file-operations')
        },
        fileOperationsUrl() {
            return OC.generateUrl('/apps/ransomware_detection/api/v1/file-operation')
        }
    }
}
</script>

<style lang="scss" scoped>
    #tables {
        height: calc(100% - 50px);
    }
    #recover {
        background-color: grey;
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
    .notification-wrapper {
        display: flex;
        justify-content: center;
    }
</style>