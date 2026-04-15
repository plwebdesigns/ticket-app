<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { computed, useAttrs } from "vue"
import { useVModel } from "@vueuse/core"
import { cn } from "@/lib/utils"

defineOptions({ inheritAttrs: false })

const props = defineProps<{
  defaultValue?: string | number
  modelValue?: string | number
  class?: HTMLAttributes["class"]
  type?: HTMLInputElement["type"]
}>()

const attrs = useAttrs()

const emits = defineEmits<{
  (e: "update:modelValue", payload: string | number): void
}>()

const modelValue = useVModel(props, "modelValue", emits, {
  passive: true,
  defaultValue: props.defaultValue,
})

const resolvedType = computed((): HTMLInputElement["type"] | undefined => {
  if (props.type !== undefined) {
    return props.type
  }

  const t = attrs.type

  return typeof t === "string" ? (t as HTMLInputElement["type"]) : undefined
})

/**
 * File inputs must not use v-model: Vue cannot bind two-way state to them, and doing so
 * can reset the selected FileList (especially with multiple files) when the component re-renders.
 */
const isFileInput = computed(() => resolvedType.value === "file")

const inputClass = computed(() =>
  cn(
    "file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground dark:bg-input/30 border-input h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm",
    "focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]",
    "aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive",
    props.class,
  ),
)
</script>

<template>
  <input
    v-if="!isFileInput"
    v-model="modelValue"
    data-slot="input"
    :class="inputClass"
    v-bind="attrs"
    :type="resolvedType"
  />
  <input
    v-else
    data-slot="input"
    :class="inputClass"
    v-bind="attrs"
    :type="resolvedType"
  />
</template>
