<script setup>
import { watch, onBeforeUnmount } from 'vue';
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';

const props = defineProps({
    modelValue: { type: String, default: '' },
});
const emit = defineEmits(['update:modelValue']);

const editor = useEditor({
    content: props.modelValue,
    extensions: [StarterKit],
    onUpdate: ({ editor }) => emit('update:modelValue', editor.getHTML()),
    editorProps: {
        attributes: {
            class: 'prose prose-sm max-w-none min-h-[200px] px-3 py-2 focus:outline-none dark:prose-invert',
        },
    },
});

watch(() => props.modelValue, (value) => {
    if (editor.value && value !== editor.value.getHTML()) {
        editor.value.commands.setContent(value, false);
    }
});

onBeforeUnmount(() => editor.value?.destroy());

const actions = [
    { label: 'B', title: 'Tebal', run: (e) => e.chain().focus().toggleBold().run(), active: (e) => e.isActive('bold') },
    { label: 'I', title: 'Miring', run: (e) => e.chain().focus().toggleItalic().run(), active: (e) => e.isActive('italic') },
    { label: 'H2', title: 'Judul', run: (e) => e.chain().focus().toggleHeading({ level: 2 }).run(), active: (e) => e.isActive('heading', { level: 2 }) },
    { label: 'List', title: 'Daftar', run: (e) => e.chain().focus().toggleBulletList().run(), active: (e) => e.isActive('bulletList') },
    { label: '1.', title: 'Daftar bernomor', run: (e) => e.chain().focus().toggleOrderedList().run(), active: (e) => e.isActive('orderedList') },
    { label: 'Code', title: 'Kode', run: (e) => e.chain().focus().toggleCodeBlock().run(), active: (e) => e.isActive('codeBlock') },
];
</script>

<template>
    <div class="rounded-lg border border-slate-300 dark:border-slate-600 overflow-hidden">
        <div class="flex flex-wrap gap-1 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-1.5">
            <button
                v-for="action in actions"
                :key="action.label"
                type="button"
                :title="action.title"
                class="rounded px-2 py-1 text-xs font-semibold hover:bg-slate-200 dark:hover:bg-slate-700"
                :class="editor && action.active(editor) ? 'bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-200' : 'text-slate-600 dark:text-slate-300'"
                @click="action.run(editor)"
            >
                {{ action.label }}
            </button>
        </div>
        <EditorContent :editor="editor" class="bg-white dark:bg-slate-900" />
    </div>
</template>
