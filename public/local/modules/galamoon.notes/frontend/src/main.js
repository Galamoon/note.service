import './styles.css'

import { createApp } from 'vue'
import NotesApp from './NotesApp.vue'

export function initNotesApp(containerId) {
    const app = createApp(NotesApp, {
        containerId,
    })

    const container = document.getElementById(containerId)
    if (container) {
        const root = document.getElementById('notes-app-root')
        if (root) {
            app.mount(root)
        }
    }

    return app
}

function bootstrap() {
    document.querySelectorAll('.notes-app').forEach((container) => {
        const id = container.id
        if (id) {
            initNotesApp(id)
        }
    })
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootstrap)
} else {
    bootstrap()
}
