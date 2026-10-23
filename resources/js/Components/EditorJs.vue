<script setup>
import { onMounted, onBeforeUnmount, ref } from 'vue';
import EditorJS from '@editorjs/editorjs';
import Header from '@editorjs/header';
import List from '@editorjs/list';
import ImageTool from '@editorjs/image';
import Embed from '@editorjs/embed';

const props = defineProps({
    modelValue: {
        type: [Object, Array, String],
        default: () => ({ blocks: [] }),
    },
    placeholder: {
        type: String,
        default: 'Comienza a escribir aquí tu contenido...',
    },
});

const emit = defineEmits(['update:modelValue']);
const editorHolder = ref(null);
let editorInstance = null;

const normalizeListItems = (items) => {
    if (!Array.isArray(items)) return [];
    return items.map((item) => {
        if (typeof item === 'string') {
            return {
                content: item,
                meta: {},
                items: [],
            };
        }
        if (item && typeof item === 'object') {
            const rawContent = item.content !== undefined 
                ? item.content 
                : (item.text !== undefined ? item.text : '');

            let meta = {};
            if (item.meta && typeof item.meta === 'object' && !Array.isArray(item.meta)) {
                meta = { ...item.meta };
            } else if (typeof item.checked === 'boolean') {
                meta = { checked: item.checked };
            }

            return {
                content: String(rawContent ?? ''),
                meta: meta,
                items: Array.isArray(item.items) ? normalizeListItems(item.items) : [],
            };
        }
        return { content: '', meta: {}, items: [] };
    });
};

const normalizeEditorData = (data) => {
    if (!data || typeof data !== 'object') return { blocks: [] };

    // Deshacer completamente cualquier Reactividad/Proxy de Vue 3
    // que impida que el navegador use window.structuredClone sin lanzar DataCloneError
    let cleanData;
    try {
        cleanData = JSON.parse(JSON.stringify(data));
    } catch {
        cleanData = { blocks: [] };
    }

    const rawBlocks = Array.isArray(cleanData.blocks) ? cleanData.blocks : [];
    const normalizedBlocks = rawBlocks.map((block) => {
        if (block && block.type === 'list' && block.data) {
            const style = block.data.style || 'unordered';
            const items = normalizeListItems(block.data.items);

            const listData = {
                style,
                items,
            };

            // Solo incluimos 'meta' en listas ordenadas o checklists.
            // Para 'unordered', omitir deliberadamente 'meta' hace que @editorjs/list
            // evalúe `!("meta" in e)` como verdadero y salte directamente la llamada a structuredClone().
            if (style === 'ordered') {
                listData.meta = {
                    start: Number(block.data.meta?.start) || 1,
                    counterType: block.data.meta?.counterType || 'numeric',
                };
            } else if (style === 'checklist') {
                listData.meta = {};
            }

            return {
                ...block,
                data: listData,
            };
        }
        return block;
    });

    return { ...cleanData, blocks: normalizedBlocks };
};

onMounted(() => {
    let rawData = props.modelValue;

    if (typeof rawData === 'string' && rawData.trim() !== '') {
        try {
            rawData = JSON.parse(rawData);
        } catch {
            rawData = { blocks: [] };
        }
    } else if (Array.isArray(rawData)) {
        rawData = { blocks: rawData };
    } else if (rawData && typeof rawData === 'object') {
        rawData = rawData.blocks ? rawData : { blocks: [] };
    } else {
        rawData = { blocks: [] };
    }

    const parsedData = normalizeEditorData(rawData);

    const uploadUrl = typeof route === 'function' 
        ? route('admin.editor.upload') 
        : '/admin/editor/upload-image';

    editorInstance = new EditorJS({
        holder: editorHolder.value,
        placeholder: props.placeholder,
        data: parsedData,
        tools: {
            header: {
                class: Header,
                inlineToolbar: ['link'],
                config: {
                    levels: [2, 3, 4],
                    defaultLevel: 2,
                },
            },
            list: {
                class: List,
                inlineToolbar: true,
            },
            embed: {
                class: Embed,
                config: {
                    services: {
                        youtube: true,
                        vimeo: true,
                    },
                },
            },
            image: {
                class: ImageTool,
                config: {
                    endpoints: {
                        byFile: uploadUrl,
                    },
                    field: 'image',
                },
            },
        },
        async onChange() {
            if (!editorInstance) return;
            const data = await editorInstance.save();
            emit('update:modelValue', data);
        },
    });
});

onBeforeUnmount(() => {
    if (editorInstance && typeof editorInstance.destroy === 'function') {
        editorInstance.destroy();
        editorInstance = null;
    }
});
</script>

<template>
    <!-- Contenedor sin focus ring invasivo y con borde suave permanente -->
    <div class="w-full bg-white rounded-2xl border border-[#E0E0E0] shadow-xs px-6 py-5">
        <!-- Contenedor con espacio lateral suficiente para que la cruz '+' nunca choque con el borde -->
        <div ref="editorHolder" class="editor-js-holder prose max-w-none min-h-[220px]"></div>
    </div>
</template>

<style>
/* Dar espacio al bloque para que los botones de la izquierda no toquen el borde */
.editor-js-holder .codex-editor {
    padding-left: 2rem;
}

.ce-block__content,
.ce-toolbar__content {
    max-width: 100% !important;
}

.cdx-block {
    max-width: 100% !important;
}

/* Estilo para el botón '+' (Cruz) para que sea visible, estilizado y no choque */
.ce-toolbar__plus {
    color: #35449F !important;
    background-color: #F5F5F8 !important;
    border: 1px solid #E0E0E0 !important;
    border-radius: 8px !important;
    width: 26px !important;
    height: 26px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: all 0.2s ease !important;
}

.ce-toolbar__plus:hover {
    background-color: #35449F !important;
    color: #ffffff !important;
    border-color: #35449F !important;
}

/* Botón de configuración (los 6 puntos) */
.ce-toolbar__settings-btn {
    color: #71589F !important;
    background-color: #F5F5F8 !important;
    border: 1px solid #E0E0E0 !important;
    border-radius: 8px !important;
    width: 26px !important;
    height: 26px !important;
    margin-left: 4px !important;
    transition: all 0.2s ease !important;
}

.ce-toolbar__settings-btn:hover {
    background-color: #14161C !important;
    color: #ffffff !important;
    border-color: #14161C !important;
}
</style>
