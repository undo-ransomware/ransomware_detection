<template>
    <paper-card heading="Protection Status">
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

<style lang="scss" scoped>
    paper-card {
        .card-content {
            height: calc(100% - 52px);
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        --paper-card-background-color: #247209;
        width: 100%;
        height: 100%;
        box-shadow: none;
        --paper-card-header-color: #fff;
    }
    
    h1 {
        font-size: 48px;
    }

    iron-icon {
        width: 66px;
        height: 66px;
    }
</style>