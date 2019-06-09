<template>
	<button class="action-button pull-right"
		:data-type="type" :data-href="link" @click="onClickActionButton">
		<iron-icon icon="undo"></iron-icon> {{ label }}
	</button>
</template>

<script>
import axios from 'nextcloud-axios'
import '@polymer/iron-icon/iron-icon.js';
import '@polymer/iron-icons/iron-icons.js';

export default {
	name: 'Action',

	props: {
		label: {
			type: String,
			default: '',
			required: true
		},
		link: {
			type: String,
			default: '',
			required: true
		},
		type: {
			type: String,
			default: '',
			required: true
		}
	},

	methods: {
		onClickActionButton: function() {
			axios({
				method: this.type || 'GET',
				url: this.link
			})
				.then(() => {
					this.$parent._$el.fadeOut(OC.menuSpeed)
					this.$parent.$emit('remove')
					$('body').trigger(new $.Event('OCA.Notification.Action', {
						notification: this.$parent,
						action: {
							url: this.link,
							type: this.type || 'GET'
						}
					}))
				})
				.catch(() => {
					OC.Notification.showTemporary(t('notifications', 'Failed to perform action'))
				})
		}
	}
}
</script>