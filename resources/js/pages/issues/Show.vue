<script setup lang="ts">
import { Form, Head, Link, setLayoutProps } from '@inertiajs/vue3';
import { computed, ref, watch, watchEffect } from 'vue';
import { show as attachmentShow, destroy as attachmentDestroy } from '@/actions/App/Http/Controllers/AttachmentController';
import Heading from '@/components/Heading.vue';
import TicketEditDialog from '@/components/issues/TicketEditDialog.vue';
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
import { index as issuesIndex, show as issuesShow } from '@/routes/issues';

type SlugRow = {
    id: number;
    name: string;
    description: string | null;
    slug: string;
};

type AttachmentRef = {
    id: string;
    filename: string;
    mime_type: string;
    size: number;
};

type UserRef = {
    id: number;
    name: string;
    email: string;
};

type TicketDetail = {
    id: number;
    ticket_number: string;
    subject: string;
    description: string | null;
    type_id: number;
    current_state_id: number;
    priority: string;
    requested_by_id: number | null;
    assigned_to_id: number | null;
    created_at: string | null;
    updated_at: string | null;
    type: SlugRow | null;
    current_state: SlugRow | null;
    requested_by: UserRef | null;
    assigned_to: UserRef | null;
    created_by: UserRef | null;
    updated_by: UserRef | null;
    attachments: AttachmentRef[];
};

const props = defineProps<{
    ticket: TicketDetail;
    types: SlugRow[];
    currentStates: SlugRow[];
    users: { id: number; name: string; email: string }[];
    priorities: string[];
}>();

const editOpen = ref(false);
const attachmentOptionsOpen = ref(false);
const selectedPdfAttachment = ref<AttachmentRef | null>(null);

const ticketForEdit = computed(() => ({
    id: props.ticket.id,
    ticket_number: props.ticket.ticket_number,
    subject: props.ticket.subject,
    description: props.ticket.description,
    type_id: props.ticket.type_id,
    current_state_id: props.ticket.current_state_id,
    priority: props.ticket.priority,
    requested_by_id: props.ticket.requested_by_id,
    assigned_to_id: props.ticket.assigned_to_id,
}));

watchEffect(() => {
    setLayoutProps({
        breadcrumbs: [
            {
                title: 'Issues',
                href: issuesIndex(),
            },
            {
                title: props.ticket.ticket_number,
                href: issuesShow(props.ticket),
            },
        ],
    });
});

function formatWhen(iso: string | null): string {
    if (!iso) {
        return '';
    }

    return new Date(iso).toLocaleString();
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

const selectedAttachmentDownloadUrl = computed(() => {
    if (!selectedPdfAttachment.value) {
        return '#';
    }

    return attachmentShow.url(selectedPdfAttachment.value);
});

const selectedAttachmentInlineUrl = computed(() => {
    if (!selectedPdfAttachment.value) {
        return '#';
    }

    return attachmentShow.url(selectedPdfAttachment.value, { query: { inline: 1 } });
});

function isPdfAttachment(attachment: AttachmentRef): boolean {
    const mimeType = attachment.mime_type.toLowerCase();

    if (mimeType === 'application/pdf') {
        return true;
    }

    return attachment.filename.toLowerCase().endsWith('.pdf');
}

function onAttachmentClick(event: MouseEvent, attachment: AttachmentRef): void {
    if (!isPdfAttachment(attachment)) {
        return;
    }

    event.preventDefault();
    selectedPdfAttachment.value = attachment;
    attachmentOptionsOpen.value = true;
}

function viewSelectedPdfInBrowser(): void {
    if (!selectedPdfAttachment.value) {
        return;
    }

    window.open(selectedAttachmentInlineUrl.value, '_blank', 'noopener,noreferrer');
    attachmentOptionsOpen.value = false;
}

function downloadSelectedAttachment(): void {
    if (!selectedPdfAttachment.value) {
        return;
    }

    window.open(selectedAttachmentDownloadUrl.value, '_blank', 'noopener,noreferrer');
    attachmentOptionsOpen.value = false;
}

watch(attachmentOptionsOpen, (isOpen) => {
    if (!isOpen) {
        selectedPdfAttachment.value = null;
    }
});
</script>

<template>
    <div>
        <Head :title="`${ticket.ticket_number} · Issues`" />

        <TicketEditDialog
            v-model:open="editOpen"
            :ticket="ticketForEdit"
            :attachments="ticket.attachments"
            :types="props.types"
            :current-states="props.currentStates"
            :users="props.users"
            :priorities="props.priorities"
            @saved="editOpen = false"
        />

        <Dialog v-model:open="attachmentOptionsOpen">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Attachment options</DialogTitle>
                    <DialogDescription>
                        Choose how to open {{ selectedPdfAttachment?.filename ?? 'this PDF' }}.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2 sm:justify-end">
                    <DialogClose as-child>
                        <Button type="button" variant="secondary">
                            Cancel
                        </Button>
                    </DialogClose>
                    <Button
                        type="button"
                        variant="outline"
                        @click="downloadSelectedAttachment"
                    >
                        Download
                    </Button>
                    <Button
                        type="button"
                        @click="viewSelectedPdfInBrowser"
                    >
                        View PDF in browser
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <div
            class="flex flex-col space-y-6 p-4 md:p-6"
        >
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <Heading
                    variant="small"
                    :title="ticket.subject"
                    :description="ticket.ticket_number"
                />
                <div class="flex flex-wrap gap-2">
                    <Button
                        type="button"
                        size="sm"
                        @click="editOpen = true"
                    >
                        Edit
                    </Button>
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="issuesIndex()">Back to issues</Link>
                    </Button>
                </div>
            </div>

            <div
                class="grid gap-6 rounded-xl border border-border p-6 md:grid-cols-2"
            >
                <div class="space-y-1">
                    <p class="text-xs font-medium uppercase text-muted-foreground">
                        Type
                    </p>
                    <p class="text-sm">{{ ticket.type?.name ?? '—' }}</p>
                </div>
                <div class="space-y-1">
                    <p class="text-xs font-medium uppercase text-muted-foreground">
                        State
                    </p>
                    <p class="text-sm">{{ ticket.current_state?.name ?? '—' }}</p>
                </div>
                <div class="space-y-1">
                    <p class="text-xs font-medium uppercase text-muted-foreground">
                        Priority
                    </p>
                    <p class="text-sm">{{ ticket.priority }}</p>
                </div>
                <div class="space-y-1">
                    <p class="text-xs font-medium uppercase text-muted-foreground">
                        Requested by
                    </p>
                    <p class="text-sm">
                        <template v-if="ticket.requested_by">
                            {{ ticket.requested_by.name }}
                            <span class="text-muted-foreground">
                                ({{ ticket.requested_by.email }})
                            </span>
                        </template>
                        <template v-else>—</template>
                    </p>
                </div>
                <div class="space-y-1">
                    <p class="text-xs font-medium uppercase text-muted-foreground">
                        Assigned to
                    </p>
                    <p class="text-sm">
                        <template v-if="ticket.assigned_to">
                            {{ ticket.assigned_to.name }}
                            <span class="text-muted-foreground">
                                ({{ ticket.assigned_to.email }})
                            </span>
                        </template>
                        <template v-else>—</template>
                    </p>
                </div>
                <div class="space-y-1 md:col-span-2">
                    <p class="text-xs font-medium uppercase text-muted-foreground">
                        Description
                    </p>
                    <p
                        class="whitespace-pre-wrap text-sm text-foreground"
                    >
                        {{ ticket.description?.trim() ? ticket.description : '—' }}
                    </p>
                </div>
                <div
                    v-if="ticket.created_by || ticket.updated_by"
                    class="space-y-3 border-t border-border pt-4 md:col-span-2"
                >
                    <p class="text-xs font-medium uppercase text-muted-foreground">
                        Activity
                    </p>
                    <dl class="grid gap-2 text-sm text-muted-foreground md:grid-cols-2">
                        <div v-if="ticket.created_by">
                            <dt class="inline font-medium text-foreground">
                                Created by
                            </dt>
                            {{ ' ' }}
                            <dd class="inline">
                                {{ ticket.created_by.name }}
                                <span v-if="ticket.created_at">
                                    · {{ formatWhen(ticket.created_at) }}
                                </span>
                            </dd>
                        </div>
                        <div v-if="ticket.updated_by">
                            <dt class="inline font-medium text-foreground">
                                Last updated by
                            </dt>
                            {{ ' ' }}
                            <dd class="inline">
                                {{ ticket.updated_by.name }}
                                <span v-if="ticket.updated_at">
                                    · {{ formatWhen(ticket.updated_at) }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div
                v-if="ticket.attachments.length > 0"
                class="rounded-xl border border-border p-6"
            >
                <p class="mb-4 text-xs font-medium uppercase text-muted-foreground">
                    Attachments
                </p>
                <ul class="divide-y divide-border">
                    <li
                        v-for="attachment in ticket.attachments"
                        :key="attachment.id"
                        class="flex items-center justify-between gap-4 py-3 first:pt-0 last:pb-0"
                    >
                        <div class="min-w-0">
                            <a
                                :href="attachmentShow.url(attachment)"
                                @click="onAttachmentClick($event, attachment)"
                                class="truncate text-sm font-medium text-primary underline-offset-4 hover:underline"
                            >
                                {{ attachment.filename }}
                            </a>
                            <p class="text-xs text-muted-foreground">
                                {{ formatFileSize(attachment.size) }}
                            </p>
                        </div>
                        <Form
                            v-bind="attachmentDestroy.form(attachment)"
                            :options="{ preserveScroll: true }"
                            class="shrink-0"
                            v-slot="{ processing }"
                        >
                            <Button
                                type="submit"
                                variant="destructive"
                                size="sm"
                                :disabled="processing"
                            >
                                Delete
                            </Button>
                        </Form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
