<template>
    <vaadin-grid theme="row-dividers" column-reordering-allowed multi-sort>
        <vaadin-grid-selection-column auto-select frozen></vaadin-grid-selection-column>
        <vaadin-grid-sort-column width="9em" path="originalName"></vaadin-grid-sort-column>
        <vaadin-grid-sort-column width="9em" path="timestamp"></vaadin-grid-sort-column>
    </vaadin-grid>
</template>

<script>
import '@vaadin/vaadin-grid/vaadin-grid.js';
import '@vaadin/vaadin-grid/vaadin-grid-selection-column.js';
import '@vaadin/vaadin-grid/vaadin-grid-sort-column.js';
import '@vaadin/vaadin-grid/vaadin-grid-column.js';

export default {
    name: 'RansomwareTable',
    mounted () {
        // Customize the "Address" column's renderer
        /*document.querySelector('#addresscolumn').renderer = (root, grid, rowData) => {
            root.textContent = `${rowData.item.address.street}, ${rowData.item.address.city}`;
        };*/

        // Populate the grid with data
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
        width: 100%;
        height: 100%;
    }
</style>