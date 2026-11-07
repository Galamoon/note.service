<template>
  <div class="notes-app-container">
    <div class="notes-app__header">
      <h2>{{ title }}</h2>
      <button class="notes-app__add-btn" @click="handleAdd">
        {{ addNoteLabel }}
      </button>
    </div>
    <SkeletonLoader v-if="loading && notes.length === 0"/>
    <NotesList
        v-else
        :notes="notes"
        :empty-text="emptyText"
        :edit-label="editLabel"
        :delete-label="deleteLabel"
        @edit="handleEdit"
        @delete="handleDelete"

    />
    <NoteForm
        v-if="showForm"
        :note="editingNote"
        :edit-title="editFormTitle"
        :create-title="createFormTitle"
        :title-label="titleLabel"
        :text-label="textLabel"
        :title-placeholder="titlePlaceholder"
        :text-placeholder="textPlaceholder"
        :cancel-label="cancelLabel"
        :submit-label="submitLabel"
        @save="handleSave"
        @cancel="showForm = false"
    />
  </div>
</template>

<script>
import {ref, onMounted, onUnmounted} from 'vue'
import NotesList from './components/NotesList.vue'
import NoteForm from './components/NoteForm.vue'
import SkeletonLoader from './components/SkeletonLoader.vue'

export default {
  name: 'NotesApp',
  components: {NotesList, NoteForm, SkeletonLoader},
  props: {
    containerId: {type: String, required: true},
    title: {type: String, default: 'Заметки'},
    addNoteLabel: {type: String, default: 'Новая заметка'},
  },
  setup(props) {
    const notes = ref([])
    const loading = ref(true)
    const showForm = ref(false)
    const editingNote = ref(null)
    const page = ref(1)
    const hasMore = ref(true)
    const total = ref(0)
    const isFetching = ref(false)
    const csrfToken = ref(null)
    const sessid = ref('')

    // Get Bitrix sessid
    if (typeof BX !== 'undefined' && BX.bitrix_sessid) {
      sessid.value = BX.bitrix_sessid()
    } else {
      // Fallback: try to get from URL
      const match = window.location.search.match(/[?&]sessid=([^&]+)/)
      if (match) sessid.value = match[1]
    }

    const emptyText = 'Заметок пока нет'
    const editLabel = 'Редактировать'
    const deleteLabel = 'Удалить'
    const editFormTitle = 'Редактировать заметку'
    const createFormTitle = 'Новая заметка'
    const titleLabel = 'Заголовок'
    const textLabel = 'Текст'
    const titlePlaceholder = 'Введите заголовок'
    const textPlaceholder = 'Введите текст заметки'
    const cancelLabel = 'Отмена'
    const submitLabel = 'Сохранить'

    async function loadNotes(reset = false) {
      if (reset) {
        page.value = 1
        notes.value = []
        hasMore.value = true
      }

      if (!hasMore.value) return
      if (notes.value.length === 0) loading.value = true

      try {
        const response = await fetch(
            `/api/v1/notes/?page=${page.value}&limit=20&sessid=${sessid.value}`
        )
        // Extract CSRF token from response header
        const csrf = response.headers.get('X-Bitrix-New-Csrf')
        if (csrf) {
          csrfToken.value = csrf
        }
        const payload = await response.json()
        const data = payload.data || payload

        if (reset) {
          notes.value = data.items || []
        } else {
          notes.value = [...notes.value, ...(data.items || [])]
        }
        total.value = data.total || 0
        hasMore.value = notes.value.length < (data.total || 0)
        page.value++
      } catch (error) {
        console.error('Error loading notes:', error)
      } finally {
        loading.value = false
      }
    }

    async function handleEdit(note) {
      editingNote.value = {...note}
      showForm.value = true
    }

    async function handleDelete(noteId) {
      if (!confirm('Удалить заметку?')) return

      try {
        const response = await fetch(`/api/v1/notes/${noteId}/`, {
          method: 'DELETE',
          headers: {
            'X-Bitrix-CSRF': csrfToken.value || '',
          },
        })
        if (response.ok) {
          notes.value = notes.value.filter((n) => n.id !== noteId)
          total.value--
          // Подгрузить ещё, если осталось меньше лимита на странице
          if (notes.value.length < 20 && hasMore.value) {
            await loadNotes()
          }
        }
      } catch (error) {
        console.error('Error deleting note:', error)
      }
    }

    async function handleSave(noteData) {
      const noteId = editingNote.value ? editingNote.value.id : null
      const method = noteId ? 'PUT' : 'POST'
      const url = noteId ? `/api/v1/notes/${noteId}/?sessid=${sessid.value}` : `/api/v1/notes/?sessid=${sessid.value}`

      try {
        const response = await fetch(url, {
          method: method,
          headers: {
            'Content-Type': 'application/json',
            'X-Bitrix-CSRF': csrfToken.value || '',
          },
          body: JSON.stringify(noteData),
        })
        if (response.ok) {
          showForm.value = false
          editingNote.value = null
          loadNotes(true)
        }
      } catch (error) {
        console.error('Error saving note:', error)
      }
    }

    function handleAdd() {
      editingNote.value = null
      showForm.value = true
    }

    function handleWindowScroll() {
      if (!hasMore.value || isFetching.value) return
      const atBottom = window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 300
      if (atBottom) {
        isFetching.value = true
        loadNotes().finally(() => {
          isFetching.value = false
        })
      }
    }

    onMounted(() => {
      loadNotes()
      window.addEventListener('scroll', handleWindowScroll)
    })

    onUnmounted(() => {
      window.removeEventListener('scroll', handleWindowScroll)
    })

    return {
      notes,
      loading,
      showForm,
      editingNote,
      handleAdd,
      handleEdit,
      handleDelete,
      handleSave,

      emptyText,
      editLabel,
      deleteLabel,
      editFormTitle,
      createFormTitle,
      titleLabel,
      textLabel,
      titlePlaceholder,
      textPlaceholder,
      cancelLabel,
      submitLabel,
    }
  },
}
</script>

<style scoped>
.notes-app-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.notes-app__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.notes-app__header h2 {
  margin: 0;
  font-size: 24px;
  font-weight: 600;
}

.notes-app__add-btn {
  padding: 8px 16px;
  font-size: 14px;
  border-radius: 4px;
  cursor: pointer;
}
</style>
