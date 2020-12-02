<template>
	<div class="notification" v-show="visible">
		{{ text }} <iron-icon icon="clear" @click="onClickCloseButton"></iron-icon>
	</div>
</template>

<script>
import '@polymer/iron-icon/iron-icon.js';
import '@polymer/iron-icons/iron-icons.js';
import '../webcomponents/ransomware-icons'

export default {
	name: 'Notification',

	props: {
		text: {
			type: String,
			default: '',
			required: true
        },
        visible: {
            type: Boolean,
            default: false
        }
    },
    methods: {
		onClickCloseButton: function() {
			this.$emit('on-close');
		}
    },
    watch: {
        visible (val) {
            this.visible = val
        }
    }
}
</script>

<style lang="scss">
    .notification {
        background-color: #E7E7E7;
        color: #000;
        font-size: 16px;
        font-weight: bold;
        width: 60%;
        height: 60px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0px 10px 0px 10px !important;
        margin: 20px 0px 20px 0px;
        iron-icon {
            cursor: pointer;
        }
    }
</style>