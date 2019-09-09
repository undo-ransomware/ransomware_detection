<template>
    <div>
        <h2 class="container">
            <div class="item name">{{ serviceName }}</div>
            <div v-if="serviceStatus" class="item status active">Active</div>
            <div v-if="!serviceStatus" class="item status offline">Offline</div>    
        </h2>
        <div v-if="!serviceStatus" class="description">{{description}}</div>
    </div>
</template>

<script>
import '@polymer/paper-card/paper-card.js';
import '@polymer/paper-button/paper-button.js';
import '@polymer/iron-icon/iron-icon.js';
import '@polymer/iron-icons/iron-icons.js';
import axios from 'nextcloud-axios'

export default {
    name: 'ServiceStatus',
    props: {
        link: {
			type: String,
			default: '',
			required: true
        },
        description: {
            type: String,
            default: '',
            required: true
        }
    },
    data() {
        return {
            serviceName: "Not available.",
            serviceStatus: 0
        };
    },
    created () {
        this.fetchServiceName();
        this.fetchServiceStatus();
    },
    methods: {
        fetchServiceName: function() {
            axios({
                method: 'GET',
                url: this.link
            })
            .then(json => {
                this.serviceName = json.data.name;
            })
            .catch( error => { console.error(error); });
        },
        fetchServiceStatus() {
            axios({
				method: 'GET',
				url: this.link
            })
            .then(json => {
                this.serviceStatus = json.data.status;
                this.$emit('service-state-changed');
            })
            .catch( error => { console.error(error); });
        }
    }
}
</script>

<style lang="scss" scoped>
    .container {
        display: flex;
        width: 100%;
        justify-content: space-between;
    }
    h2 {
        margin: 0px;
    }
    .item {
        padding: 5px 10px 5px 10px;
    }
    .description {
        color: #9b9b9b;
        padding: 0px 10px 0px 10px;
    }
    .active {
        color: #18b977;
    }
    .offline {
        color: #e2523d;
    }

</style>