<template>
	<button class="action-button pull-right" @click="onClickActionButton">
		<iron-icon icon="undo"></iron-icon> {{ label }}
	</button>
</template>

<script>
import '@polymer/iron-icon/iron-icon.js';
import '@polymer/iron-icons/iron-icons.js';

export default {
	name: 'RecoverAction',

	props: {
		label: {
			type: String,
			default: '',
			required: true
		}
	},
	methods: {
		onClickActionButton() {
			this.$emit("recover");
		}
	}
}
</script>