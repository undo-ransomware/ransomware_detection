<template>
    <div class="header">
        <h2>
            {{this.header}}
        </h2>
        <div class="actions">
            <slot/>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Header',
    props: {
        header: {
			type: String,
			default: '',
			required: true
		}
    }
}
</script>

<style lang="scss">
    h2 {
        height: 100%;
        margin: 0px;
        padding: 10px;
    }
    .actions {
        height: 100%;
        display: flex;
        align-items: center;
        padding: 0px 10px 0px 10px;
    }
    .header {
        height: 50px;
        display: flex;
        justify-content: space-between;
        align-items: stretch;
    }
</style>