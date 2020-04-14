<template>
    <div class="container" v-bind:class="[protection && !detection? 'good' : 'bad']">
            <h1>
                <span v-if="protection && !detection"><iron-icon icon="ransomware:shield"></iron-icon> Your files are protected against destruction by ransomware.</span>
                <span v-if="!protection"><iron-icon icon="error"></iron-icon> Your files are not protected. One service is not working properly.</span>
                <span v-if="protection && detection"><iron-icon icon="ransomware:locked"></iron-icon> Ransomware attack detected.</span>
            </h1>
            <paper-button class="recover-button" @click="$router.push('recover')" v-if="protection && detection"><iron-icon icon="undo"></iron-icon>Recover</paper-button>
    </div>
</template>

<script>
import '@polymer/paper-button/paper-button.js';
import '@polymer/iron-icon/iron-icon.js';
import '@polymer/iron-icons/iron-icons.js';
import '../webcomponents/ransomware-icons'
import axios from 'nextcloud-axios'

export default {
    name: 'ProtectionStatus',
    props: {
        detectionLink: {
			type: String,
			default: '',
			required: true
		}
    },
    created() {
        this.fetchDetectionStatus();
    },
    data() {
        return {
            detection: 0,
            protection: 1
        };
    },
    methods: {
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
                this.$emit('protection-state-changed');
            })
            .catch( error => { console.error(error); });
        }
    }
}
</script>

<style lang="scss" scoped>
    .container {
        h1 {
            height: calc(100% - 52px);
            color: #fff;
            line-height: 48px;
            display: flex;
            align-items: center;
            font-size: 32px;
            iron-icon {
                width: 48px;
                height: 48px;
            }
            span {
                vertical-align: middle;
            }
            padding: 0px 10px 0px 30px;
        }

        width: 100%;
        height: 100%;
        box-shadow: none;
        color: #fff;
        padding: 0px 10px 0px 10px;
        &.good {
            background-color: #18b977;
        }
        &.bad {
            background-color: #e2523d;
        }
    }
    
    .recover-button {
        display: flex;
        border: 1px solid #fff;
    }

    .recover-button:hover {
        background-color: #fff;
        color: #c00;
    }
</style>