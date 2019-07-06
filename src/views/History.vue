<template>
    <AppContent>
        <iron-pages :selected="page">
			<div id="loading" class="page">
				<paper-spinner active></paper-spinner>
			</div>
			<div class="page">
                <Header header="History">
                    <RecoverAction id="recover" label="Recover" v-on:recover="onRecover" primary></RecoverAction>
                </Header>
                <FileOperationsTable id="ransomware-table" :data="fileOperations" v-on:table-state-changed="tableStateChanged"></FileOperationsTable>
            </div>
		</iron-pages>
    </AppContent>
</template>

<script>
import '@polymer/paper-spinner/paper-spinner.js';
import '@polymer/iron-pages/iron-pages.js';
import FileOperationsTable from '../components/FileOperationsTable'
import Header from '../components/Header'
import RecoverAction from '../components/RecoverAction'
import AppContent from 'nextcloud-vue/dist/Components/AppContent'

export default {
    name: 'History',
    components: {
		AppContent,
        FileOperationsTable,
        Header,
        RecoverAction
    },
    data() {
        return {
            fileOperations: [],
            page: 0
        };
    },
    mounted() {
        this.page = 0;
        this.fetchData();
        setInterval(() => this.fetchData(), 3000);
    },
    computed: {
        recoverUrl() {
            return OC.generateUrl('/apps/ransomware_detection/api/v1/file-operation')
        },
        fileOperationsUrl() {
            return OC.generateUrl('/apps/ransomware_detection/api/v1/file-operation')
        }
    },
    methods: {
        tableStateChanged() {
            this.page = 1;
        },
        fetchData() {
            this.$axios({
                method: 'GET',
                url: this.fileOperationsUrl
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
                this.recover(selected[i].id);
			}
        },
        remove(id) {
            for (var i = 0; i < this.fileOperations.length; i++) {
                if (this.fileOperations[i].id === id) {
                    this.fileOperations.splice(i, 1);
                }
            }
        },
        async recover(id) {
            await this.$axios({
					method: 'PUT',
					url: this.recoverUrl + '/' + id + '/recover'
				})
					.then(response => {
						switch(response.status) {
							case 204:
								this.remove(id);
								break;
							default:
								console.log(response);
								break;
						}
					})
					.catch(error => {
                        console.error(error);
					});
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