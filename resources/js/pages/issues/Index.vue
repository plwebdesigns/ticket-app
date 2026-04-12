<script setup lang="ts">
import { Form, Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import TicketController from '@/actions/App/Http/Controllers/TicketController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
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
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';
import { index as issuesIndex, show as issuesShow } from '@/routes/issues';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

type SlugRow = {
    id: number;
    name: string;
    description: string | null;
    slug: string;
};

type UserOption = {
    id: number;
    name: string;
    email: string;
};

type TicketRow = {
    id: number;
    ticket_number: string;
    subject: string;
    description: string | null;
    type_id: number;
    current_state_id: number;
    priority: string;
    requested_by_id: number | null;
    assigned_to_id: number | null;
    type: SlugRow | null;
    current_state: SlugRow | null;
    requested_by: { id: number; name: string } | null;
    assigned_to: { id: number; name: string } | null;
};

type PaginatedTickets = {
    data: TicketRow[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
};

const props = defineProps<{
    tickets: PaginatedTickets;
    types: SlugRow[];
    currentStates: SlugRow[];
    users: UserOption[];
    priorities: string[];
}>();

const createOpen = ref(false);
const editOpen = ref(false);
const editingTicket = ref<TicketRow | null>(null);
const deleteOpen = ref(false);
const deletingTicket = ref<TicketRow | null>(null);

function openEdit(ticket: TicketRow): void {
    editingTicket.value = ticket;
    editOpen.value = true;
}

function openDelete(ticket: TicketRow): void {
    deletingTicket.value = ticket;
    deleteOpen.value = true;
}

watch(editOpen, (isOpen) => {
    if (!isOpen) {
        editingTicket.value = null;
    }
});

watch(deleteOpen, (isOpen) => {
    if (!isOpen) {
        deletingTicket.value = null;
    }
});

function goToTicket(ticket: TicketRow): void {
    router.visit(issuesShow.url(ticket));
}

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Issues',
                href: issuesIndex(),
            },
        ],
    },
});
</script>

<template>
    <div>
        <Head title="Issues" />

        <h1 class="sr-only">Issues</h1>

        <div
            class="flex flex-col space-y-6 p-4 md:p-6"
        >
            <Heading
                variant="small"
                title="Issues"
                description="View and manage support tickets"
            />

            <div class="flex justify-end">
                <Dialog v-model:open="createOpen">
                    <DialogTrigger as-child>
                        <Button type="button" size="sm">New ticket</Button>
                    </DialogTrigger>
                    <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-lg">
                        <Form
                            v-bind="TicketController.store.form()"
                            reset-on-success
                            class="space-y-4"
                            :options="{ preserveScroll: true }"
                            @success="createOpen = false"
                            v-slot="{ errors, processing }"
                        >
                            <DialogHeader>
                                <DialogTitle>New ticket</DialogTitle>
                                <DialogDescription>
                                    Create a ticket. A unique number is assigned
                                    automatically.
                                </DialogDescription>
                            </DialogHeader>
                            <div class="grid gap-2">
                                <Label for="ticket-subject">Subject</Label>
                                <Input
                                    id="ticket-subject"
                                    name="subject"
                                    required
                                    autocomplete="off"
                                />
                                <InputError :message="errors.subject" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="ticket-attachments">Attachments</Label>
                                <Input
                                    id="ticket-attachments"
                                    name="attachments"
                                    type="file"
                                    multiple
                                />
                                <InputError :message="errors.attachments" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="ticket-description">Description</Label>
                                <textarea
                                    id="ticket-description"
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
                                <Label for="ticket-type">Type</Label>
                                <Select name="type_id" required>
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Select a type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="t in props.types" :key="t.id" :value="t.id">{{ t.name }}</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="errors.type_id" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="ticket-state">Current state</Label>
                                <Select name="current_state_id" required>
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Select a state" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="s in props.currentStates" :key="s.id" :value="s.id">{{ s.name }}</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="errors.current_state_id" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="ticket-priority">Priority</Label>
                                <Select name="priority" required>
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Select a priority" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="p in props.priorities" :key="p" :value="p">{{ p }}</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="errors.priority" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="ticket-requested">Requested by</Label>
                                <Select name="requested_by_id">
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Select a requested by" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="u in props.users" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="errors.requested_by_id" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="ticket-assigned">Assigned to</Label>
                                <Select name="assigned_to_id">
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Select a assigned to" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="u in props.users" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="errors.assigned_to_id" />
                            </div>
                            <DialogFooter class="gap-2">
                                <DialogClose as-child>
                                    <Button type="button" variant="secondary">
                                        Cancel
                                    </Button>
                                </DialogClose>
                                <Button type="submit" :disabled="processing">
                                    Create
                                </Button>
                            </DialogFooter>
                        </Form>
                    </DialogContent>
                </Dialog>
            </div>

            <Dialog v-model:open="deleteOpen">
                <DialogContent class="sm:max-w-md">
                    <Form
                        v-if="deletingTicket"
                        v-bind="TicketController.destroy.form(deletingTicket)"
                        :options="{ preserveScroll: true }"
                        @success="deleteOpen = false"
                        v-slot="{ processing }"
                    >
                        <DialogHeader>
                            <DialogTitle>Delete ticket?</DialogTitle>
                            <DialogDescription>
                                This will permanently delete
                                {{ deletingTicket.ticket_number }} ({{ deletingTicket.subject }}).
                                This action cannot be undone.
                            </DialogDescription>
                        </DialogHeader>
                        <DialogFooter class="gap-2 sm:justify-end">
                            <DialogClose as-child>
                                <Button
                                    type="button"
                                    variant="secondary"
                                >
                                    Cancel
                                </Button>
                            </DialogClose>
                            <Button
                                type="submit"
                                variant="destructive"
                                :disabled="processing"
                            >
                                Delete ticket
                            </Button>
                        </DialogFooter>
                    </Form>
                </DialogContent>
            </Dialog>

            <div class="overflow-x-auto rounded-md border">
                <table class="w-full text-left text-sm">
                    <thead
                        class="border-b bg-chart-3 text-xs font-medium uppercase text-primary-foreground"
                    >
                        <tr>
                            <th class="px-4 py-3">Number</th>
                            <th class="px-4 py-3">Subject</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">State</th>
                            <th class="px-4 py-3">Priority</th>
                            <th class="px-4 py-3">Requested by</th>
                            <th class="px-4 py-3">Assigned to</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="ticket in props.tickets.data"
                            :key="ticket.id"
                            class="cursor-pointer border-b transition-colors last:border-0 hover:bg-muted/50"
                            tabindex="0"
                            @click="goToTicket(ticket)"
                            @keydown.enter.prevent="goToTicket(ticket)"
                        >
                            <td class="px-4 py-3 font-mono text-xs">
                                {{ ticket.ticket_number }}
                            </td>
                            <td class="px-4 py-3 font-medium">
                                {{ ticket.subject }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ ticket.type?.name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ ticket.current_state?.name ?? '—' }}
                            </td>
                            <td class="px-4 py-3">{{ ticket.priority }}</td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ ticket.requested_by?.name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ ticket.assigned_to?.name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-right" @click.stop>
                                <div class="flex justify-end gap-2">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        @click="openEdit(ticket)"
                                    >
                                        Edit
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="destructive"
                                        size="sm"
                                        @click="openDelete(ticket)"
                                    >
                                        Delete
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-if="props.tickets.last_page > 1"
                class="flex items-center justify-between border-t pt-4 text-sm"
            >
                <Link
                    v-if="props.tickets.prev_page_url"
                    :href="props.tickets.prev_page_url"
                    preserve-scroll
                    class="text-primary underline-offset-4 hover:underline"
                >
                    Previous
                </Link>
                <span v-else class="text-muted-foreground">Previous</span>
                <span class="text-muted-foreground">
                    Page {{ props.tickets.current_page }} of
                    {{ props.tickets.last_page }}
                </span>
                <Link
                    v-if="props.tickets.next_page_url"
                    :href="props.tickets.next_page_url"
                    preserve-scroll
                    class="text-primary underline-offset-4 hover:underline"
                >
                    Next
                </Link>
                <span v-else class="text-muted-foreground">Next</span>
            </div>

            <TicketEditDialog
                v-model:open="editOpen"
                :ticket="editingTicket"
                :types="props.types"
                :current-states="props.currentStates"
                :users="props.users"
                :priorities="props.priorities"
                @saved="editOpen = false"
            />
        </div>
    </div>
</template>
