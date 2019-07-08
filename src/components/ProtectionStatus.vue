<template>
    <paper-card heading="Protection Status" v-bind:class="[protection && !detection? 'good' : 'bad']">
        <div class="card-content">
            <h1>
                <iron-icon v-if="protection && !detection" icon="verified-user"></iron-icon>
                <iron-icon v-if="!protection" icon="error"></iron-icon>
                <icon-base v-if="(protection && detection)" icon-name="locked" icon-color="white" width="66px" height="66px"><icon-locked/></icon-base>
                <span v-if="protection && !detection">You are protected.</span>
                <span v-if="!protection">You are not protected.</span>
                <span v-if="protection && detection">Ransomware detected.</span>
            </h1>
            <paper-button class="recover-button" @click="$router.push('recover')" v-if="protection && detection"><iron-icon icon="undo"></iron-icon>Recover</paper-button>
        </div>
    </paper-card>
</template>

<script>
import '@polymer/paper-card/paper-card.js';
import '@polymer/paper-button/paper-button.js';
import '@polymer/iron-icon/iron-icon.js';
import '@polymer/iron-icons/iron-icons.js';
import axios from 'nextcloud-axios'
import IconBase from '../components/IconBase'
import IconLocked from '../components/icons/IconLocked'

export default {
    name: 'ProtectionStatus',
    components: {
		IconBase,
        IconLocked
    },
    props: {
        protectionLink: {
			type: String,
			default: '',
			required: true
        },
        detectionLink: {
			type: String,
			default: '',
			required: true
		}
    },
    created() {
        this.fetchServicesStatus();
        this.fetchDetectionStatus();
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
				url: this.protectionLink
            })
            .then(json => {
                this.protection = 1;
                for (i = 0; i < json.data.length; i++) {
                    if (json.data[i].status == 0) {
                        this.protection = 0;
                    }
                }
                this.$emit('protection-state-changed');
            })
            .catch( error => { console.error(error); });
        },
        fetchDetectionStatus() {
            axios({
				method: 'GET',
				url: this.detectionLink
            })
            .then(json => {
                this.detection = 0;
                if (json.data.length > 0) {
                    this.detection = 1;
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
            flex-direction: column;
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

    h1 iron-icon {
        width: 66px;
        height: 66px;
    }

    h1 span {
        vertical-align: middle;
    }
    
    .recover-button {
        display: flex;
        border: 1px solid #fff;
    }

    .recover-button:hover {
        background-color: #fff;
        color: red;
    }
</style>