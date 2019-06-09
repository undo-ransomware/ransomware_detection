<template>
    <AppContent>
        <Header header="Recover">
            <Action id="recover" label="Recover" link="" type="GET" primary></Action>
        </Header>
    	<RansomwareTable id="ransomware-table" :link="fileOperationsUrl"></RansomwareTable>
    </AppContent>
</template>

<script>
import RansomwareTable from '../components/RansomwareTable'
import Header from '../components/Header'
import Action from '../components/Action'
import AppContent from 'nextcloud-vue/dist/Components/AppContent'

export default {
    name: 'Recover',
    components: {
		AppContent,
        RansomwareTable,
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