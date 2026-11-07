<template>
    <div class="notes-list">
        <div class="notes-grid">
            <NoteCard
                v-for="note in notes"
                :key="note.id"
                :note="note"
                :edit-label="editLabel"
                :delete-label="deleteLabel"
                @edit="$emit('edit', note)"
                @delete="$emit('delete', note.id)"
            />
        </div>
        <div v-if="notes.length === 0" class="notes-list__empty">
            {{ emptyText }}
        </div>
    </div>
</template>

<script>
import { } from 'vue'
import NoteCard from './NoteCard.vue'

export default {
    name: 'NotesList',
    components: { NoteCard },
    props: {
        notes: { type: Array, required: true },
        emptyText: { type: String, default: 'Заметок пока нет' },
        editLabel: { type: String, default: 'Редактировать' },
        deleteLabel: { type: String, default: 'Удалить' },
    },
    emits: ['edit', 'delete'],
    setup(props, { emit }) {
    },
}
</script>

<style scoped>
.notes-list {
    width: 100%;
}

.notes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 16px;
}

.notes-list__empty {
    text-align: center;
    padding: 40px;
    color: #999;
}
</style>
