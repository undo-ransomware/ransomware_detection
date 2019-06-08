<template>
    <paper-card>
        <div class="card-content">
            <h1>
                <iron-icon icon="verified-user"></iron-icon><span>You are protected.</span>
            </h1>
        </div>
    </paper-card>
</template>

<script>
import '@polymer/paper-card/paper-card.js';
import '@polymer/paper-button/paper-button.js';
import '@polymer/iron-icon/iron-icon.js';
import '@polymer/iron-icons/iron-icons.js';

export default {
    name: 'ProtectionStatus',
}
</script>

<style scoped>
    paper-card {
        width: 100%;
        height: 30vh;
        background-color: #fff;
        color: #247209;
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
    }
</style>