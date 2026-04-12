<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { destroy as attachmentDestroy } from '@/actions/App/Http/Controllers/AttachmentController';
import TicketController from '@/actions/App/Http/Controllers/TicketController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

export type SlugRow = {
    id: number;
    name: string;
    description: string | null;
    slug: string;
};

export type UserOption = {
    id: number;
    name: string;
    email: string;
};

export type TicketEditable = {
    id: number;
    ticket_number: string;
    subject: string;
    description: string | null;
    type_id: number;
    current_state_id: number;
    priority: string;
    requested_by_id: number | null;
    assigned_to_id: number | null;
};

export type AttachmentRef = {
    id: string;
    filename: string;
    mime_type: string;
    size: number;
};

const props = defineProps<{
    ticket: TicketEditable | null;
    attachments?: AttachmentRef[];
    types: SlugRow[];
    currentStates: SlugRow[];
    users: UserOption[];
    priorities: string[];
}>();

const open = defineModel<boolean>('open', { required: true });

const editDescription = ref('');

const emit = defineEmits<{
    saved: [];
}>();

watch(
    [() => props.ticket, open],
    () => {
        if (props.ticket !== null && open.value) {
            editDescription.value = props.ticket.description ?? '';
        }
    },
    { immediate: true },
);

function onSuccess(): void {
    emit('saved');
}

function formatFileSize(bytes: number): string {
    if (bytes < 1024) {
        return `${bytes} B`;
    }
    if (bytes < 1024 * 1024) {
        return `${(bytes / 1024).toFixed(1)} KB`;
    }

    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent
            v-if="ticket"
            class="max-h-[90vh] overflow-y-auto sm:max-w-lg"
        >
            <Form
                :key="ticket.id"
                v-bind="TicketController.update.form(ticket)"
                reset-on-success
                class="space-y-4"
                :options="{ preserveScroll: true }"
                @success="onSuccess"
                v-slot="{ errors, processing }"
            >
                <DialogHeader>
                    <DialogTitle>Edit ticket</DialogTitle>
                    <DialogDescription>
                        Update ticket {{ ticket.ticket_number }}.
                    </DialogDescription>
                </DialogHeader>
                <div class="grid gap-2">
                    <Label :for="`edit-subject-${ticket.id}`">Subject</Label>
                    <Input
                        :id="`edit-subject-${ticket.id}`"
                        name="subject"
                        required
                        :default-value="ticket.subject"
                        autocomplete="off"
                    />
                    <InputError :message="errors.subject" />
                </div>
                <div class="grid gap-2">
                    <Label :for="`edit-desc-${ticket.id}`">Description</Label>
                    <textarea
                        :id="`edit-desc-${ticket.id}`"
                        v-model="editDescription"
                        name="description"
                        rows="4"
                        :class="
                            cn(
                                'placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground dark:bg-input/30 border-input w-full min-w-0 rounded-md border bg-transparent px-3 py-2 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                                'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                                'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                            )
                        "
                    />
                    <InputError :message="errors.description" />
                </div>
                <div class="grid gap-2">
                    <Label :for="`edit-type-${ticket.id}`">Type</Label>
                    <select
                        :id="`edit-type-${ticket.id}`"
                        name="type_id"
                        required
                        :class="
                            cn(
                                'border-input h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none',
                                'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                            )
                        "
                    >
                        <option
                            v-for="t in props.types"
                            :key="t.id"
                            :value="t.id"
                            :selected="t.id === ticket.type_id"
                        >
                            {{ t.name }}
                        </option>
                    </select>
                    <InputError :message="errors.type_id" />
                </div>
                <div class="grid gap-2">
                    <Label :for="`edit-state-${ticket.id}`">Current state</Label>
                    <select
                        :id="`edit-state-${ticket.id}`"
                        name="current_state_id"
                        required
                        :class="
                            cn(
                                'border-input h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none',
                                'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                            )
                        "
                    >
                        <option
                            v-for="s in props.currentStates"
                            :key="s.id"
                            :value="s.id"
                            :selected="s.id === ticket.current_state_id"
                        >
                            {{ s.name }}
                        </option>
                    </select>
                    <InputError :message="errors.current_state_id" />
                </div>
                <div class="grid gap-2">
                    <Label :for="`edit-priority-${ticket.id}`">Priority</Label>
                    <select
                        :id="`edit-priority-${ticket.id}`"
                        name="priority"
                        required
                        :class="
                            cn(
                                'border-input h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none',
                                'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                            )
                        "
                    >
                        <option
                            v-for="p in props.priorities"
                            :key="p"
                            :value="p"
                            :selected="p === ticket.priority"
                        >
                            {{ p }}
                        </option>
                    </select>
                    <InputError :message="errors.priority" />
                </div>
                <div class="grid gap-2">
                    <Label :for="`edit-req-${ticket.id}`">Requested by</Label>
                    <select
                        :id="`edit-req-${ticket.id}`"
                        name="requested_by_id"
                        :class="
                            cn(
                                'border-input h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none',
                                'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                            )
                        "
                    >
                        <option
                            value=""
                            :selected="!ticket.requested_by_id"
                        >
                            —
                        </option>
                        <option
                            v-for="u in props.users"
                            :key="u.id"
                            :value="u.id"
                            :selected="u.id === ticket.requested_by_id"
                        >
                            {{ u.name }} ({{ u.email }})
                        </option>
                    </select>
                    <InputError :message="errors.requested_by_id" />
                </div>
                <div class="grid gap-2">
                    <Label :for="`edit-asg-${ticket.id}`">Assigned to</Label>
                    <select
                        :id="`edit-asg-${ticket.id}`"
                        name="assigned_to_id"
                        :class="
                            cn(
                                'border-input h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none',
                                'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                            )
                        "
                    >
                        <option
                            value=""
                            :selected="!ticket.assigned_to_id"
                        >
                            —
                        </option>
                        <option
                            v-for="u in props.users"
                            :key="`ea-${u.id}`"
                            :value="u.id"
                            :selected="u.id === ticket.assigned_to_id"
                        >
                            {{ u.name }} ({{ u.email }})
                        </option>
                    </select>
                    <InputError :message="errors.assigned_to_id" />
                </div>
                <div class="grid gap-2">
                    <Label :for="`edit-attachments-${ticket.id}`">Add attachments</Label>
                    <Input
                        :id="`edit-attachments-${ticket.id}`"
                        name="attachments"
                        type="file"
                        multiple
                    />
                    <InputError :message="errors.attachments" />
                </div>
                <div
                    v-if="props.attachments && props.attachments.length > 0"
                    class="grid gap-2"
                >
                    <Label>Existing attachments</Label>
                    <ul class="divide-y divide-border rounded-md border">
                        <li
                            v-for="attachment in props.attachments"
                            :key="attachment.id"
                            class="flex items-center justify-between gap-3 px-3 py-2"
                        >
                            <span class="min-w-0 truncate text-sm">
                                {{ attachment.filename }}
                                <span class="text-muted-foreground">
                                    ({{ formatFileSize(attachment.size) }})
                                </span>
                            </span>
                            <Form
                                v-bind="attachmentDestroy.form(attachment)"
                                :options="{ preserveScroll: true }"
                                class="shrink-0"
                                v-slot="{ processing: deleting }"
                            >
                                <Button
                                    type="submit"
                                    variant="destructive"
                                    size="sm"
                                    :disabled="deleting"
                                >
                                    Remove
                                </Button>
                            </Form>
                        </li>
                    </ul>
                </div>
                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button type="button" variant="secondary">
                            Cancel
                        </Button>
                    </DialogClose>
                    <Button type="submit" :disabled="processing">Save</Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
