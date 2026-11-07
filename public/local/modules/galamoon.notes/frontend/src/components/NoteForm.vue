<template>
    <div class="note-form-overlay" @click.self="$emit('cancel')">
        <div class="note-form">
            <h3>{{ isEdit ? editTitle : createTitle }}</h3>

            <div class="note-form__field">
                <label class="note-form__label" for="note-title">{{ titleLabel }}</label>
                <input
                    id="note-title"
                    v-model="form.title"
                    type="text"
                    class="note-form__input"
                    :class="{ 'note-form__input_error': errors.title }"
                    :placeholder="titlePlaceholder"
                />
                <span v-if="errors.title" class="note-form__error">{{ errors.title }}</span>
            </div>

            <div class="note-form__field">
                <label class="note-form__label" for="note-text">{{ textLabel }}</label>
                <textarea
                    id="note-text"
                    v-model="form.text"
                    class="note-form__textarea"
                    :class="{ 'note-form__textarea_error': errors.text }"
                    :placeholder="textPlaceholder"
                    rows="6"
                ></textarea>
                <span v-if="errors.text" class="note-form__error">{{ errors.text }}</span>
            </div>

            <div class="note-form__actions">
                <button class="notes-button" @click="$emit('cancel')">{{ cancelLabel }}</button>
                <button class="notes-button notes-button_primary" @click="handleSubmit">
                    {{ submitLabel }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, watch } from 'vue'

export default {
    name: 'NoteForm',
    props: {
        note: { type: Object, default: null },
        editTitle: { type: String, default: 'Редактировать заметку' },
        createTitle: { type: String, default: 'Новая заметка' },
        titleLabel: { type: String, default: 'Заголовок' },
        textLabel: { type: String, default: 'Текст' },
        titlePlaceholder: { type: String, default: 'Введите заголовок' },
        textPlaceholder: { type: String, default: 'Введите текст заметки' },
        cancelLabel: { type: String, default: 'Отмена' },
        submitLabel: { type: String, default: 'Сохранить' },
    },
    emits: ['save', 'cancel'],
    setup(props, { emit }) {
        const form = ref({
            title: props.note?.title ?? '',
            text: props.note?.text ?? '',
        })

        const errors = ref({})

        watch(
            () => props.note,
            (newNote) => {
                if (newNote) {
                    form.value = {
                        title: newNote.title ?? '',
                        text: newNote.text ?? '',
                    }
                    errors.value = {}
                }
            }
        )

        function validate() {
            const newErrors = {}

            if (!form.value.title || !form.value.title.trim()) {
                newErrors.title = 'Заголовок обязателен'
            }

            if (!form.value.text || !form.value.text.trim()) {
                newErrors.text = 'Текст обязателен'
            }

            errors.value = newErrors
            return Object.keys(newErrors).length === 0
        }

        function handleSubmit() {
            if (!validate()) return
            emit('save', {
                title: form.value.title,
                text: form.value.text,
            })
        }

        return { form, errors, validate, handleSubmit }
    },
    computed: {
        isEdit() {
            return !!this.note
        },
    },
}
</script>

<style scoped>
.note-form-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.note-form {
    background: #fff;
    border-radius: 8px;
    padding: 24px;
    width: 100%;
    max-width: 500px;
}

.note-form__field {
    margin-bottom: 16px;
}

.note-form__label {
    display: block;
    margin-bottom: 4px;
    font-weight: 500;
}

.note-form__input,
.note-form__textarea {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.note-form__input_error,
.note-form__textarea_error {
    border-color: #e53935;
}

.note-form__error {
    color: #e53935;
    font-size: 12px;
    margin-top: 4px;
    display: block;
}

.note-form__actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
}
</style>
