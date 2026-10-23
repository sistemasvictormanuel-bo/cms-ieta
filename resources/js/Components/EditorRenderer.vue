<script setup>
import { computed } from 'vue';
import { assetUrl } from '@/utils/asset';

const props = defineProps({
    content: {
        type: [Object, Array, String],
        default: () => ({ blocks: [] }),
    },
});

const parsedBlocks = computed(() => {
    let data = props.content;
    if (typeof data === 'string' && data.trim() !== '') {
        try {
            data = JSON.parse(data);
        } catch {
            return [];
        }
    }
    if (Array.isArray(data)) {
        return data;
    }
    if (data && typeof data === 'object' && Array.isArray(data.blocks)) {
        return data.blocks;
    }
    return [];
});
</script>

<template>
    <div class="editor-content space-y-4 text-gray-800">
        <template v-for="(block, index) in parsedBlocks" :key="block.id || index">
            <!-- Encabezados -->
            <h2
                v-if="block.type === 'header' && block.data.level === 2"
                class="text-2xl font-extrabold text-[#14161C] tracking-tight mt-6 mb-2"
                v-html="block.data.text"
            ></h2>
            <h3
                v-else-if="block.type === 'header' && block.data.level === 3"
                class="text-xl font-bold text-[#14161C] tracking-tight mt-5 mb-2"
                v-html="block.data.text"
            ></h3>
            <h4
                v-else-if="block.type === 'header' && block.data.level >= 4"
                class="text-lg font-bold text-[#14161C] tracking-tight mt-4 mb-1"
                v-html="block.data.text"
            ></h4>

            <!-- Párrafos -->
            <p
                v-else-if="block.type === 'paragraph'"
                class="text-[15px] leading-relaxed text-gray-700"
                v-html="block.data.text"
            ></p>

            <!-- Listas -->
            <div v-else-if="block.type === 'list'" class="my-3 pl-2">
                <ol
                    v-if="block.data.style === 'ordered'"
                    class="list-decimal list-inside space-y-2 text-[15px] text-gray-700 font-normal"
                >
                    <li v-for="(item, iIdx) in block.data.items" :key="iIdx" class="leading-relaxed">
                        <span v-html="typeof item === 'object' ? (item.content ?? item.text ?? '') : item"></span>
                        <!-- Sublistas anidadas si existen -->
                        <ol
                            v-if="typeof item === 'object' && item.items && item.items.length"
                            class="list-decimal list-inside pl-6 mt-1 space-y-1"
                        >
                            <li v-for="(sub, sIdx) in item.items" :key="sIdx">
                                <span v-html="typeof sub === 'object' ? (sub.content ?? sub.text ?? '') : sub"></span>
                            </li>
                        </ol>
                    </li>
                </ol>

                <ul
                    v-else
                    class="list-disc list-inside space-y-2 text-[15px] text-gray-700 font-normal"
                >
                    <li v-for="(item, iIdx) in block.data.items" :key="iIdx" class="leading-relaxed">
                        <span v-html="typeof item === 'object' ? (item.content ?? item.text ?? '') : item"></span>
                        <!-- Sublistas anidadas si existen -->
                        <ul
                            v-if="typeof item === 'object' && item.items && item.items.length"
                            class="list-disc list-inside pl-6 mt-1 space-y-1"
                        >
                            <li v-for="(sub, sIdx) in item.items" :key="sIdx">
                                <span v-html="typeof sub === 'object' ? (sub.content ?? sub.text ?? '') : sub"></span>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>

            <!-- Imágenes -->
            <figure
                v-else-if="block.type === 'image'"
                class="my-6 rounded-2xl overflow-hidden border border-gray-200"
                :class="{ 'p-3 bg-gray-50': block.data.withBackground }"
            >
                <img
                    :src="assetUrl(block.data.file?.url || block.data.url)"
                    :alt="block.data.caption || 'Imagen'"
                    class="w-full object-cover max-h-[500px] rounded-xl"
                    :class="{ 'border border-gray-300': block.data.withBorder }"
                />
                <figcaption
                    v-if="block.data.caption"
                    class="text-xs text-center text-gray-500 mt-2 italic px-4"
                    v-html="block.data.caption"
                ></figcaption>
            </figure>

            <!-- Videos / Embeds -->
            <div
                v-else-if="block.type === 'embed'"
                class="my-6 rounded-2xl overflow-hidden border border-gray-200 aspect-video shadow-xs"
            >
                <iframe
                    :src="block.data.embed"
                    class="w-full h-full"
                    frameborder="0"
                    allowfullscreen
                ></iframe>
            </div>
        </template>
    </div>
</template>
