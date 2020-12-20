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
                <Header header="Recover"></Header>
                <div id="detections" v-if="detected">
                    <div class="detection" v-for="detection in detections" :key="detection.id">
                        <Header :header="'Detection ' + detection.id">
                            <RecoverAction id="recover" label="Recover detected files" v-on:recover="onRecover(detection.id)" primary></RecoverAction>
                        </Header>
                        <v-data-table
                            :ref="'detection' + detection.id"
                            class="ransomware-table"
                            :headers="headers"
                            :items="detection.fileOperations"
                        >
                            <template v-slot:item.timestamp = "{ item }">
                                <local-time>{{ moment(item.timestamp) }}</local-time>
                            </template>
                            <template v-slot:item.suspicionClass = "{ item }">
                                <iron-icon v-if="item.suspicionClass > 1" style="color: #ED0012;" icon="ransomware:locked"></iron-icon>
                                <iron-icon v-if="item.suspicionClass == 1 && item.type == 'folder'" style="color: #7ED221;" icon="folder"></iron-icon>
                                <iron-icon v-if="item.suspicionClass == 1 && item.type == 'file'" style="color: #7ED221;" icon="editor:insert-drive-file"></iron-icon>
                                <iron-icon v-if="item.suspicionClass < 1 && item.type == 'folder'" style="color: #9B9A9B;" icon="folder"></iron-icon>
                                <iron-icon v-if="item.suspicionClass < 1 && item.type == 'file'" style="color: #9B9A9B;" icon="editor:insert-drive-file"></iron-icon>
                            </template>
                            <template v-slot:item.command = "{ item }">
                                <span v-if="item.command == 1 && item.type == 'file'">File deleted</span>
                                <span v-if="item.command == 1 && item.type == 'folder'">Folder deleted</span>
                                <span v-if="item.command == 2 && item.type == 'file'">File renamed</span>
                                <span v-if="item.command == 2 && item.type == 'folder'">Folder renamed</span>
                                <span v-if="item.command == 3 && item.type == 'file'">File written</span>
                                <span v-if="item.command == 3 && item.type == 'folder'">Folder written</span>
                                <span v-if="item.command == 4 && item.type == 'file'">File read</span>
                                <span v-if="item.command == 4 && item.type == 'folder'">Folder read</span>
                                <span v-if="item.command == 5 && item.type == 'file'">File created</span>
                                <span v-if="item.command == 5 && item.type == 'folder'">Folder created</span>
                                <span v-if="item.command > 5 && item.command < 1 && item.type == 'file'">No information</span>
                                <span v-if="item.command > 5 && item.command < 1 && item.type == 'folder'">No information</span>
                            </template>
                        </v-data-table>
                    </div>
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
import '@polymer/iron-icons/editor-icons.js';
import '../webcomponents/ransomware-icons'
import 'time-elements/dist/time-elements';
import moment from 'moment'
import Notification from '../components/Notification'
import Header from '../components/Header'
import RecoverAction from '../components/RecoverAction'
import AppContent from 'nextcloud-vue/dist/Components/AppContent'

export default {
    name: 'Recover',
    components: {
		AppContent,
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
            notificationText: "",
            headers: [
                {
                    text: 'Status',
                    align: 'start',
                    sortable: false,
                    value: 'suspicionClass',
                },
                { text: 'Operation', value: 'command' },
                { text: 'Name', value: 'originalName' },
                { text: 'File Changed', value: 'timestamp' },
            ],
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
                notificationText = recovered + " files recovered from backup."
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
        fetchDetections() {
            this.$axios({
                method: 'GET',
                url: this.detectionsUrl
            })
            .then(json => {
                this.detections = json.data;
                this.page = 1;
            })
            .catch( error => { console.error(error); });
        },
        onRecover(id) {
            var itemsToRecover = [];
            const detectionTable = this.$refs['detection' + id];
            var items = detectionTable[0].items;
            for (var i = 0; i < items.length; i++) {
                itemsToRecover.push(items[i].id);
            }
            this.recover(id, itemsToRecover);
        },
        remove(id) {
            for (var i = 0; i < this.detections.length; i++) {
                if (this.detections[i].id === id) {
                    this.detections.splice(i, 1);
                }
            }
        },
        async recover(id, ids) {
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
								    this.remove(id);
								break;
							default:
								this.buildNotification(response.data.deleted, response.data.recovered);
								if(response.data.filesRecovered.length > 0)
								    this.remove(id);
								break;
						}
					})
					.catch(error => {
                        console.error(error);
					});
        },
        moment: function (date) {
            return moment.unix(date).format('dddd, MMMM Do YYYY, HH:mm:ss')
        },
        datetime: function(date) {
            return moment.unix(rowData.item.timestamp).format("YYYY-MM-DDTHH:mm:ss.SSS")
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

<style lang="scss">
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
    .detection {
        margin: 10px 0px 0px 0px;
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