<script setup lang="ts">
import { Head, Link, setLayoutProps } from '@inertiajs/vue3';
import { computed, ref, watchEffect } from 'vue';
import Heading from '@/components/Heading.vue';
import TicketEditDialog from '@/components/issues/TicketEditDialog.vue';
import { Button } from '@/components/ui/button';
import { index as issuesIndex, show as issuesShow } from '@/routes/issues';

type SlugRow = {
    id: number;
    name: string;
    description: string | null;
    slug: string;
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
};

const props = defineProps<{
    ticket: TicketDetail;
    types: SlugRow[];
    currentStates: SlugRow[];
    users: { id: number; name: string; email: string }[];
    priorities: string[];
}>();

const editOpen = ref(false);

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
</script>

<template>
    <div>
        <Head :title="`${ticket.ticket_number} · Issues`" />

        <TicketEditDialog
            v-model:open="editOpen"
            :ticket="ticketForEdit"
            :types="props.types"
            :current-states="props.currentStates"
            :users="props.users"
            :priorities="props.priorities"
            @saved="editOpen = false"
        />

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
        </div>
    </div>
</template>
