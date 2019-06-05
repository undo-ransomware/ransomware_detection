<template>
    <div id="app">
    {{ message }}
    </div>
</template>

<script>
export default {
    name: 'App',
    data: function() {
		return {
			message: 'Hello Vue.'
		}
	},
}
</script>