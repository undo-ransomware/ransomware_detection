<template>
    <AppContent>
        <iron-pages selected="0">
			<div id="loading" class="page">
				<paper-spinner active></paper-spinner>
			</div>
			<div class="page">
                <Header header="History">
                    <Action id="recover" label="Recover" link="" type="GET" primary></Action>
                </Header>
                <HistoryTable id="ransomware-table" :link="fileOperationsUrl" v-on:table-state-changed="tableStateChanged"></HistoryTable>
            </div>
		</iron-pages>
    </AppContent>
</template>

<script>
import '@polymer/paper-spinner/paper-spinner.js';
import '@polymer/iron-pages/iron-pages.js';
import HistoryTable from '../components/HistoryTable'
import Header from '../components/Header'
import Action from '../components/Action'
import AppContent from 'nextcloud-vue/dist/Components/AppContent'

export default {
    name: 'History',
    components: {
		AppContent,
        HistoryTable,
        Header,
        Action
    },
    computed: {
        fileOperationsUrl() {
            return OC.generateUrl('/apps/ransomware_detection/api/v1/file-operation');
        }
    },
    methods: {
        tableStateChanged() {
            document.querySelector('iron-pages').selectIndex(1);
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