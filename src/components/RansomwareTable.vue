<template>
    <vaadin-grid theme="row-dividers" column-reordering-allowed multi-sort>
        <vaadin-grid-selection-column auto-select frozen></vaadin-grid-selection-column>
        <vaadin-grid-column width="5em" flex-grow="0" id="status" header="Status"></vaadin-grid-column>
        <vaadin-grid-sort-column width="9em" path="originalName"></vaadin-grid-sort-column>
        <vaadin-grid-sort-column width="9em" path="timestamp" id="time"></vaadin-grid-sort-column>
    </vaadin-grid>
</template>

<script>
import '@vaadin/vaadin-grid/vaadin-grid.js';
import '@vaadin/vaadin-grid/vaadin-grid-selection-column.js';
import '@vaadin/vaadin-grid/vaadin-grid-sort-column.js';
import '@vaadin/vaadin-grid/vaadin-grid-column.js';
import '@polymer/iron-icon/iron-icon.js';
import '@polymer/iron-icons/iron-icons.js';
import 'time-elements/dist/time-elements';

export default {
    name: 'RansomwareTable',
    mounted () {
        document.querySelector('#status').renderer = (root, grid, rowData) => {
            const icon = document.createElement('iron-icon');
            icon.setAttribute('icon', 'verified-user');
            icon.style = "color: green;";
            root.innerHTML = '';
            root.appendChild(icon);
        }

        document.querySelector('#time').renderer = (root, grid, rowData) => {
            const localTime = document.createElement('local-time');
            localTime.setAttribute('datetime', moment.unix(rowData.item.timestamp).format("YYYY-MM-DDTHH:mm:ss.SSS"));
            localTime.textContent = moment.unix(rowData.item.timestamp).format('dddd, MMMM Do YYYY, HH:mm:ss');
            root.innerHTML = '';
            root.appendChild(localTime);
        }

        this.fetchData();
    },
    methods: {
        fetchData() {
            const grid = document.querySelector('vaadin-grid');
            fetch(new Request(this.fileOperationsUrl))
                .then(response => response.json())
                .then(json => {
                    grid.items = json;
                })
                .catch( error => { console.log(error); });
        }
    },
    computed: {
        fileOperationsUrl() {
            return OC.generateUrl('/apps/ransomware_detection/api/v1/file-operation');
        }
    }
}
</script>

<style scoped>
    vaadin-grid {
        height: 100%;
    }
</style>