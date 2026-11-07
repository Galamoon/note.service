<template>
    <div class="note-card">
        <div class="note-card__header">
            <h3 class="note-card__title">{{ note.title }}</h3>
            <span class="note-card__date">{{ formatDate(note.dateModify) }}</span>
        </div>
        <div class="note-card__body">
            <p class="note-card__text">{{ previewText }}</p>
        </div>
        <div class="note-card__footer">
            <button class="notes-button notes-button_edit" @click="$emit('edit')">
                {{ editLabel }}
            </button>
            <button class="notes-button notes-button_delete" @click="$emit('delete')">
                {{ deleteLabel }}
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'NoteCard',
    props: {
        note: { type: Object, required: true },
        previewLength: { type: Number, default: 100 },
        editLabel: { type: String, default: 'Редактировать' },
        deleteLabel: { type: String, default: 'Удалить' },
    },
    emits: ['edit', 'delete'],
    computed: {
        previewText() {
            const text = this.note.text || ''
            if (text.length <= this.previewLength) return text
            return text.substring(0, this.previewLength) + '...'
        },
    },
    methods: {
        formatDate(dateStr) {
            if (!dateStr) return ''
            const date = new Date(dateStr)
            return date.toLocaleDateString('ru-RU')
        },
    },
}
</script>

<style scoped>
.note-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 16px;
    background: #fff;
    transition: box-shadow 0.2s;
}

.note-card:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.note-card__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
}

.note-card__title {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}

.note-card__date {
    font-size: 12px;
    color: #999;
    white-space: nowrap;
}

.note-card__text {
    margin: 0 0 12px;
    font-size: 14px;
    color: #555;
    line-height: 1.4;
}

.note-card__footer {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
}
</style>
