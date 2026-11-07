<template>
    <div class="skeleton-loader">
        <div v-for="i in 6" :key="i" class="skeleton-card">
            <div class="skeleton-card__header">
                <div class="skeleton skeleton--title"></div>
                <div class="skeleton skeleton--date"></div>
            </div>
            <div class="skeleton skeleton--text"></div>
            <div class="skeleton skeleton--text skeleton--text-short"></div>
            <div class="skeleton skeleton--footer"></div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'SkeletonLoader',
}
</script>

<style scoped>
.skeleton-loader {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 16px;
}

.skeleton-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 16px;
    background: #fff;
}

.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    border-radius: 4px;
}

@keyframes shimmer {
    0% {
        background-position: -200% 0;
    }
    100% {
        background-position: 200% 0;
    }
}

.skeleton--title {
    height: 20px;
    width: 70%;
    margin-bottom: 8px;
}

.skeleton--date {
    height: 12px;
    width: 30%;
}

.skeleton--text {
    height: 14px;
    width: 100%;
    margin-top: 8px;
}

.skeleton--text-short {
    width: 60%;
}

.skeleton--footer {
    height: 32px;
    width: 40%;
    margin-top: 16px;
    margin-left: auto;
}
</style>
