<template>
    <paper-card :heading="serviceName">
        <div class="card-content">
            <h1>
                <iron-icon v-if="serviceStatus" class="good" icon="verified-user"></iron-icon>
                <iron-icon v-else class="bad" icon="error"></iron-icon>
            </h1>
        </div>
    </paper-card>
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
            })
            .catch( error => { console.error(error); });
        }
    }
}
</script>

<style lang="scss" scoped>
    paper-card {
        width: 100%;
        height: 100%;
        background-color: #fff;
        box-shadow: none;
        --paper-card-header-text: {
            text-align: center;
        };
    }

    .card-content {
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    h1 {
        font-size: 48px;
    }

    iron-icon {
        width: 66px;
        height: 66px;
        &.good {
            color: #247209;
        }
        &.bad {
            color: red;
        }
    }

</style>