<template>
    <vaadin-grid theme="row-dividers" column-reordering-allowed multi-sort :items.prop="fileOperations">
        <vaadin-grid-selection-column auto-select frozen></vaadin-grid-selection-column>
        <vaadin-grid-column width="5em" flex-grow="0" id="status" header="Status"></vaadin-grid-column>
        <vaadin-grid-sort-column width="9em" path="originalName" header="Name"></vaadin-grid-sort-column>
        <vaadin-grid-sort-column width="9em" path="timestamp" id="time" header="Geändert"></vaadin-grid-sort-column>
    </vaadin-grid>
</template>

<script>
import '@vaadin/vaadin-grid/vaadin-grid.js';
import '@vaadin/vaadin-grid/vaadin-grid-selection-column.js';
import '@vaadin/vaadin-grid/vaadin-grid-sort-column.js';
import '@vaadin/vaadin-grid/vaadin-grid-column.js';
import '@polymer/iron-icon/iron-icon.js';
import '@polymer/iron-icons/iron-icons.js';
import '../webcomponents/ransomware-icons'
import 'time-elements/dist/time-elements';

export default {
    name: 'FileOperationsTable',
    data() {
        return {
            fileOperations: this.items
        }
    },
    props: {
        data: {
            type: Array,
            required: true
        }
    },
    watch: {
        data: {
            immediate: true, 
            handler (newVal, oldVal) {
                this.fileOperations = newVal;
                this.$emit('table-state-changed');
                if (oldVal !== undefined) {
                    document.querySelector('vaadin-grid').clearCache();
                    document.querySelector('vaadin-grid vaadin-grid-selection-column').selectAll = false;
                }
            }
        }
    },
    mounted () {
        document.querySelector('#status').renderer = (root, grid, rowData) => {
            const icon = document.createElement('iron-icon');
            switch (rowData.item.status) {
                case 0:
                    icon.setAttribute('icon', 'ransomware:timelapse');
                    icon.style = "color: blue;";
                    break;
                case 1:
                    icon.setAttribute('icon', 'verified-user');
                    icon.style = "color: green;";
                    break;
                case 2:
                    icon.setAttribute('icon', 'error');
                    icon.style = "color: red;";
                    break;
                default:
                    icon.setAttribute('icon', 'ransomware:timelapse');
                    icon.style = "color: blue;";
                    break;
            }
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
    }
}
</script>

<style scoped>
    vaadin-grid {
        border: none;
    }
</style>