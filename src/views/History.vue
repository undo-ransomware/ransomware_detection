<template>
    <AppContent>
        <Header header="History">
            <Action id="recover" label="Recover" link="" type="GET" primary></Action>
        </Header>
    	<HistoryTable id="ransomware-table" :link="fileOperationsUrl"></HistoryTable>
    </AppContent>
</template>

<script>
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
</style>