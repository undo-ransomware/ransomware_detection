<template>
    <paper-card heading="Protection Status" v-bind:class="[protection && !detection? 'good' : 'bad']">
        <div class="card-content">
            <h1>
                <iron-icon v-if="protection && !detection" icon="verified-user"></iron-icon>
                <iron-icon v-if="!protection || (protection && detection)" icon="error"></iron-icon>
                <span v-if="protection && !detection">You are protected.</span>
                <span v-if="!protection">You are not protected.</span>
                <span v-if="protection && detection">Ransomware detected.</span>
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
    name: 'ProtectionStatus',
    props: {
        link: {
			type: String,
			default: '',
			required: true
		}
    },
    created() {
        this.fetchServicesStatus();
    },
    data() {
        return {
            detection: 0,
            protection: 0
        };
    },
    methods: {
        fetchServicesStatus() {
            axios({
				method: 'GET',
				url: this.link
            })
            .then(json => {
                this.protection = 1;
                for (i = 0; i < json.data.length; i++) {
                    if (json.data[i].status == 0) {
                        this.protection = 0;
                    }
                }
            })
            .catch( error => { console.error(error); });
        }
    }
}
</script>

<style lang="scss" scoped>
    paper-card {
        .card-content {
            height: calc(100% - 52px);
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        width: 100%;
        height: 100%;
        box-shadow: none;
        --paper-card-header-color: #fff;
        &.good {
            --paper-card-background-color: #247209;
        }
        &.bad {
            --paper-card-background-color: red;
        }
    }
    
    h1 {
        font-size: 48px;
    }

    iron-icon {
        width: 66px;
        height: 66px;
    }
</style>