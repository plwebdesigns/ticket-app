<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import CurrentStateController from '@/actions/App/Http/Controllers/Admin/CurrentStateController';
import RoleController from '@/actions/App/Http/Controllers/Admin/RoleController';
import TypeController from '@/actions/App/Http/Controllers/Admin/TypeController';
import UserController from '@/actions/App/Http/Controllers/Admin/UserController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
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
import { index as adminIndex } from '@/routes/admin';

type RoleRow = {
    id: number;
    name: string;
    description: string | null;
};

type SlugRow = {
    id: number;
    name: string;
    description: string | null;
    slug: string;
};

type UserRow = {
    id: number;
    name: string;
    email: string;
    role_id: number;
    role: RoleRow | null;
};

type Tab = 'roles' | 'types' | 'currentStates' | 'users';

const props = defineProps<{
    roles: RoleRow[];
    types: SlugRow[];
    currentStates: SlugRow[];
    users: UserRow[];
}>();

const activeTab = ref<Tab>('roles');

const roleCreateOpen = ref(false);
const roleEditOpen = ref(false);
const editingRole = ref<RoleRow | null>(null);

const typeCreateOpen = ref(false);
const typeEditOpen = ref(false);
const editingType = ref<SlugRow | null>(null);

const stateCreateOpen = ref(false);
const stateEditOpen = ref(false);
const editingState = ref<SlugRow | null>(null);

const userCreateOpen = ref(false);
const userEditOpen = ref(false);
const editingUser = ref<UserRow | null>(null);

function openRoleEdit(role: RoleRow): void {
    editingRole.value = role;
    roleEditOpen.value = true;
}

function openTypeEdit(row: SlugRow): void {
    editingType.value = row;
    typeEditOpen.value = true;
}

function openStateEdit(row: SlugRow): void {
    editingState.value = row;
    stateEditOpen.value = true;
}

function openUserEdit(user: UserRow): void {
    editingUser.value = user;
    userEditOpen.value = true;
}

const tabs: { id: Tab; label: string }[] = [
    { id: 'roles', label: 'Roles' },
    { id: 'types', label: 'Types' },
    { id: 'currentStates', label: 'Current states' },
    { id: 'users', label: 'Users' },
];

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Administration',
                href: adminIndex(),
            },
        ],
    },
});
</script>

<template>
    <div>
        <Head title="Administration" />

        <h1 class="sr-only">Administration</h1>

        <div class="flex flex-col space-y-6">
            <Heading
                variant="small"
                title="Administration"
                description="Manage roles, types, workflow states, and users"
            />

            <div
                class="flex flex-wrap gap-2 border-b border-border pb-2"
                role="tablist"
                aria-label="Admin sections"
            >
                <Button
                    v-for="tab in tabs"
                    :key="tab.id"
                    type="button"
                    variant="ghost"
                    size="sm"
                    :class="
                        cn(
                            'rounded-none border-b-2 border-transparent px-3',
                            activeTab === tab.id &&
                                'border-primary text-foreground',
                        )
                    "
                    :aria-selected="activeTab === tab.id"
                    role="tab"
                    @click="activeTab = tab.id"
                >
                    {{ tab.label }}
                </Button>
            </div>

            <!-- Roles -->
            <div v-show="activeTab === 'roles'" class="space-y-4">
                <div class="flex justify-end">
                    <Dialog v-model:open="roleCreateOpen">
                        <DialogTrigger as-child>
                            <Button type="button" size="sm">Add role</Button>
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-md">
                            <Form
                                v-bind="RoleController.store.form()"
                                reset-on-success
                                class="space-y-4"
                                :options="{ preserveScroll: true }"
                                @success="roleCreateOpen = false"
                                v-slot="{ errors, processing }"
                            >
                                <DialogHeader>
                                    <DialogTitle>Add role</DialogTitle>
                                    <DialogDescription>
                                        Create a new role for the application.
                                    </DialogDescription>
                                </DialogHeader>
                                <div class="grid gap-2">
                                    <Label for="role-name">Name</Label>
                                    <Input
                                        id="role-name"
                                        name="name"
                                        required
                                        autocomplete="off"
                                    />
                                    <InputError :message="errors.name" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="role-desc">Description</Label>
                                    <Input
                                        id="role-desc"
                                        name="description"
                                        autocomplete="off"
                                    />
                                    <InputError :message="errors.description" />
                                </div>
                                <DialogFooter class="gap-2">
                                    <DialogClose as-child>
                                        <Button
                                            type="button"
                                            variant="secondary"
                                        >
                                            Cancel
                                        </Button>
                                    </DialogClose>
                                    <Button type="submit" :disabled="processing">
                                        Save
                                    </Button>
                                </DialogFooter>
                            </Form>
                        </DialogContent>
                    </Dialog>
                </div>

                <div class="overflow-x-auto rounded-md border">
                    <table class="w-full text-left text-sm">
                        <thead
                            class="border-b bg-muted/50 text-xs font-medium uppercase text-muted-foreground"
                        >
                            <tr>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Description</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="role in props.roles"
                                :key="role.id"
                                class="border-b last:border-0"
                            >
                                <td class="px-4 py-3 font-medium">
                                    {{ role.name }}
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ role.description ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            @click="openRoleEdit(role)"
                                        >
                                            Edit
                                        </Button>
                                        <Form
                                            v-bind="
                                                RoleController.destroy.form(
                                                    role,
                                                )
                                            "
                                            :options="{
                                                preserveScroll: true,
                                            }"
                                            class="inline"
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
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <Dialog v-model:open="roleEditOpen">
                    <DialogContent
                        v-if="editingRole"
                        class="sm:max-w-md"
                        @pointer-down-outside="editingRole = null"
                    >
                        <Form
                            v-bind="RoleController.update.form(editingRole)"
                            reset-on-success
                            class="space-y-4"
                            :options="{ preserveScroll: true }"
                            @success="roleEditOpen = false"
                            v-slot="{ errors, processing }"
                        >
                            <DialogHeader>
                                <DialogTitle>Edit role</DialogTitle>
                                <DialogDescription>
                                    Update role details.
                                </DialogDescription>
                            </DialogHeader>
                            <div class="grid gap-2">
                                <Label :for="`edit-role-name-${editingRole.id}`"
                                    >Name</Label
                                >
                                <Input
                                    :id="`edit-role-name-${editingRole.id}`"
                                    name="name"
                                    required
                                    :default-value="editingRole.name"
                                    autocomplete="off"
                                />
                                <InputError :message="errors.name" />
                            </div>
                            <div class="grid gap-2">
                                <Label :for="`edit-role-desc-${editingRole.id}`"
                                    >Description</Label
                                >
                                <Input
                                    :id="`edit-role-desc-${editingRole.id}`"
                                    name="description"
                                    :default-value="
                                        editingRole.description ?? ''
                                    "
                                    autocomplete="off"
                                />
                                <InputError :message="errors.description" />
                            </div>
                            <DialogFooter class="gap-2">
                                <DialogClose as-child>
                                    <Button type="button" variant="secondary">
                                        Cancel
                                    </Button>
                                </DialogClose>
                                <Button type="submit" :disabled="processing">
                                    Save
                                </Button>
                            </DialogFooter>
                        </Form>
                    </DialogContent>
                </Dialog>
            </div>

            <!-- Types -->
            <div v-show="activeTab === 'types'" class="space-y-4">
                <div class="flex justify-end">
                    <Dialog v-model:open="typeCreateOpen">
                        <DialogTrigger as-child>
                            <Button type="button" size="sm">Add type</Button>
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-md">
                            <Form
                                v-bind="TypeController.store.form()"
                                reset-on-success
                                class="space-y-4"
                                :options="{ preserveScroll: true }"
                                @success="typeCreateOpen = false"
                                v-slot="{ errors, processing }"
                            >
                                <DialogHeader>
                                    <DialogTitle>Add type</DialogTitle>
                                    <DialogDescription>
                                        Types classify tickets. Slug is
                                        optional and derived from the name when
                                        left blank.
                                    </DialogDescription>
                                </DialogHeader>
                                <div class="grid gap-2">
                                    <Label for="type-name">Name</Label>
                                    <Input
                                        id="type-name"
                                        name="name"
                                        required
                                        autocomplete="off"
                                    />
                                    <InputError :message="errors.name" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="type-desc">Description</Label>
                                    <Input
                                        id="type-desc"
                                        name="description"
                                        autocomplete="off"
                                    />
                                    <InputError :message="errors.description" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="type-slug">Slug</Label>
                                    <Input
                                        id="type-slug"
                                        name="slug"
                                        autocomplete="off"
                                        placeholder="auto from name"
                                    />
                                    <InputError :message="errors.slug" />
                                </div>
                                <DialogFooter class="gap-2">
                                    <DialogClose as-child>
                                        <Button
                                            type="button"
                                            variant="secondary"
                                        >
                                            Cancel
                                        </Button>
                                    </DialogClose>
                                    <Button type="submit" :disabled="processing">
                                        Save
                                    </Button>
                                </DialogFooter>
                            </Form>
                        </DialogContent>
                    </Dialog>
                </div>

                <div class="overflow-x-auto rounded-md border">
                    <table class="w-full text-left text-sm">
                        <thead
                            class="border-b bg-muted/50 text-xs font-medium uppercase text-muted-foreground"
                        >
                            <tr>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Slug</th>
                                <th class="px-4 py-3">Description</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="row in props.types"
                                :key="row.id"
                                class="border-b last:border-0"
                            >
                                <td class="px-4 py-3 font-medium">
                                    {{ row.name }}
                                </td>
                                <td class="px-4 py-3 font-mono text-xs">
                                    {{ row.slug }}
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ row.description ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            @click="openTypeEdit(row)"
                                        >
                                            Edit
                                        </Button>
                                        <Form
                                            v-bind="
                                                TypeController.destroy.form(row)
                                            "
                                            :options="{
                                                preserveScroll: true,
                                            }"
                                            class="inline"
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
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <Dialog v-model:open="typeEditOpen">
                    <DialogContent
                        v-if="editingType"
                        class="sm:max-w-md"
                        @pointer-down-outside="editingType = null"
                    >
                        <Form
                            v-bind="TypeController.update.form(editingType)"
                            reset-on-success
                            class="space-y-4"
                            :options="{ preserveScroll: true }"
                            @success="typeEditOpen = false"
                            v-slot="{ errors, processing }"
                        >
                            <DialogHeader>
                                <DialogTitle>Edit type</DialogTitle>
                                <DialogDescription>
                                    Update type details.
                                </DialogDescription>
                            </DialogHeader>
                            <div class="grid gap-2">
                                <Label :for="`edit-type-name-${editingType.id}`"
                                    >Name</Label
                                >
                                <Input
                                    :id="`edit-type-name-${editingType.id}`"
                                    name="name"
                                    required
                                    :default-value="editingType.name"
                                    autocomplete="off"
                                />
                                <InputError :message="errors.name" />
                            </div>
                            <div class="grid gap-2">
                                <Label :for="`edit-type-desc-${editingType.id}`"
                                    >Description</Label
                                >
                                <Input
                                    :id="`edit-type-desc-${editingType.id}`"
                                    name="description"
                                    :default-value="
                                        editingType.description ?? ''
                                    "
                                    autocomplete="off"
                                />
                                <InputError :message="errors.description" />
                            </div>
                            <div class="grid gap-2">
                                <Label :for="`edit-type-slug-${editingType.id}`"
                                    >Slug</Label
                                >
                                <Input
                                    :id="`edit-type-slug-${editingType.id}`"
                                    name="slug"
                                    :default-value="editingType.slug"
                                    autocomplete="off"
                                />
                                <InputError :message="errors.slug" />
                            </div>
                            <DialogFooter class="gap-2">
                                <DialogClose as-child>
                                    <Button type="button" variant="secondary">
                                        Cancel
                                    </Button>
                                </DialogClose>
                                <Button type="submit" :disabled="processing">
                                    Save
                                </Button>
                            </DialogFooter>
                        </Form>
                    </DialogContent>
                </Dialog>
            </div>

            <!-- Current states -->
            <div v-show="activeTab === 'currentStates'" class="space-y-4">
                <div class="flex justify-end">
                    <Dialog v-model:open="stateCreateOpen">
                        <DialogTrigger as-child>
                            <Button type="button" size="sm"
                                >Add current state</Button
                            >
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-md">
                            <Form
                                v-bind="CurrentStateController.store.form()"
                                reset-on-success
                                class="space-y-4"
                                :options="{ preserveScroll: true }"
                                @success="stateCreateOpen = false"
                                v-slot="{ errors, processing }"
                            >
                                <DialogHeader>
                                    <DialogTitle>Add current state</DialogTitle>
                                    <DialogDescription>
                                        Workflow states for tickets. Slug is
                                        optional when left blank.
                                    </DialogDescription>
                                </DialogHeader>
                                <div class="grid gap-2">
                                    <Label for="state-name">Name</Label>
                                    <Input
                                        id="state-name"
                                        name="name"
                                        required
                                        autocomplete="off"
                                    />
                                    <InputError :message="errors.name" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="state-desc">Description</Label>
                                    <Input
                                        id="state-desc"
                                        name="description"
                                        autocomplete="off"
                                    />
                                    <InputError :message="errors.description" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="state-slug">Slug</Label>
                                    <Input
                                        id="state-slug"
                                        name="slug"
                                        autocomplete="off"
                                        placeholder="auto from name"
                                    />
                                    <InputError :message="errors.slug" />
                                </div>
                                <DialogFooter class="gap-2">
                                    <DialogClose as-child>
                                        <Button
                                            type="button"
                                            variant="secondary"
                                        >
                                            Cancel
                                        </Button>
                                    </DialogClose>
                                    <Button type="submit" :disabled="processing">
                                        Save
                                    </Button>
                                </DialogFooter>
                            </Form>
                        </DialogContent>
                    </Dialog>
                </div>

                <div class="overflow-x-auto rounded-md border">
                    <table class="w-full text-left text-sm">
                        <thead
                            class="border-b bg-muted/50 text-xs font-medium uppercase text-muted-foreground"
                        >
                            <tr>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Slug</th>
                                <th class="px-4 py-3">Description</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="row in props.currentStates"
                                :key="row.id"
                                class="border-b last:border-0"
                            >
                                <td class="px-4 py-3 font-medium">
                                    {{ row.name }}
                                </td>
                                <td class="px-4 py-3 font-mono text-xs">
                                    {{ row.slug }}
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ row.description ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            @click="openStateEdit(row)"
                                        >
                                            Edit
                                        </Button>
                                        <Form
                                            v-bind="
                                                CurrentStateController.destroy.form(
                                                    row,
                                                )
                                            "
                                            :options="{
                                                preserveScroll: true,
                                            }"
                                            class="inline"
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
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <Dialog v-model:open="stateEditOpen">
                    <DialogContent
                        v-if="editingState"
                        class="sm:max-w-md"
                        @pointer-down-outside="editingState = null"
                    >
                        <Form
                            v-bind="
                                CurrentStateController.update.form(editingState)
                            "
                            reset-on-success
                            class="space-y-4"
                            :options="{ preserveScroll: true }"
                            @success="stateEditOpen = false"
                            v-slot="{ errors, processing }"
                        >
                            <DialogHeader>
                                <DialogTitle>Edit current state</DialogTitle>
                                <DialogDescription>
                                    Update state details.
                                </DialogDescription>
                            </DialogHeader>
                            <div class="grid gap-2">
                                <Label
                                    :for="`edit-state-name-${editingState.id}`"
                                    >Name</Label
                                >
                                <Input
                                    :id="`edit-state-name-${editingState.id}`"
                                    name="name"
                                    required
                                    :default-value="editingState.name"
                                    autocomplete="off"
                                />
                                <InputError :message="errors.name" />
                            </div>
                            <div class="grid gap-2">
                                <Label
                                    :for="`edit-state-desc-${editingState.id}`"
                                    >Description</Label
                                >
                                <Input
                                    :id="`edit-state-desc-${editingState.id}`"
                                    name="description"
                                    :default-value="
                                        editingState.description ?? ''
                                    "
                                    autocomplete="off"
                                />
                                <InputError :message="errors.description" />
                            </div>
                            <div class="grid gap-2">
                                <Label
                                    :for="`edit-state-slug-${editingState.id}`"
                                    >Slug</Label
                                >
                                <Input
                                    :id="`edit-state-slug-${editingState.id}`"
                                    name="slug"
                                    :default-value="editingState.slug"
                                    autocomplete="off"
                                />
                                <InputError :message="errors.slug" />
                            </div>
                            <DialogFooter class="gap-2">
                                <DialogClose as-child>
                                    <Button type="button" variant="secondary">
                                        Cancel
                                    </Button>
                                </DialogClose>
                                <Button type="submit" :disabled="processing">
                                    Save
                                </Button>
                            </DialogFooter>
                        </Form>
                    </DialogContent>
                </Dialog>
            </div>

            <!-- Users -->
            <div v-show="activeTab === 'users'" class="space-y-4">
                <div class="flex justify-end">
                    <Dialog v-model:open="userCreateOpen">
                        <DialogTrigger as-child>
                            <Button type="button" size="sm">Add user</Button>
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-md">
                            <Form
                                v-bind="UserController.store.form()"
                                reset-on-success
                                class="space-y-4"
                                :options="{ preserveScroll: true }"
                                @success="userCreateOpen = false"
                                v-slot="{ errors, processing }"
                            >
                                <DialogHeader>
                                    <DialogTitle>Add user</DialogTitle>
                                    <DialogDescription>
                                        Create a user account with a role and
                                        initial password.
                                    </DialogDescription>
                                </DialogHeader>
                                <div class="grid gap-2">
                                    <Label for="user-name">Name</Label>
                                    <Input
                                        id="user-name"
                                        name="name"
                                        required
                                        autocomplete="name"
                                    />
                                    <InputError :message="errors.name" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="user-email">Email</Label>
                                    <Input
                                        id="user-email"
                                        type="email"
                                        name="email"
                                        required
                                        autocomplete="email"
                                    />
                                    <InputError :message="errors.email" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="user-role">Role</Label>
                                    <select
                                        id="user-role"
                                        name="role_id"
                                        required
                                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <option value="" disabled selected>
                                            Select role
                                        </option>
                                        <option
                                            v-for="r in props.roles"
                                            :key="r.id"
                                            :value="r.id"
                                        >
                                            {{ r.name }}
                                        </option>
                                    </select>
                                    <InputError :message="errors.role_id" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="user-password">Password</Label>
                                    <PasswordInput
                                        id="user-password"
                                        name="password"
                                        required
                                        autocomplete="new-password"
                                    />
                                    <InputError :message="errors.password" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="user-password-confirmation"
                                        >Confirm password</Label
                                    >
                                    <PasswordInput
                                        id="user-password-confirmation"
                                        name="password_confirmation"
                                        required
                                        autocomplete="new-password"
                                    />
                                    <InputError
                                        :message="errors.password_confirmation"
                                    />
                                </div>
                                <DialogFooter class="gap-2">
                                    <DialogClose as-child>
                                        <Button
                                            type="button"
                                            variant="secondary"
                                        >
                                            Cancel
                                        </Button>
                                    </DialogClose>
                                    <Button type="submit" :disabled="processing">
                                        Save
                                    </Button>
                                </DialogFooter>
                            </Form>
                        </DialogContent>
                    </Dialog>
                </div>

                <div class="overflow-x-auto rounded-md border">
                    <table class="w-full text-left text-sm">
                        <thead
                            class="border-b bg-muted/50 text-xs font-medium uppercase text-muted-foreground"
                        >
                            <tr>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Email</th>
                                <th class="px-4 py-3">Role</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="u in props.users"
                                :key="u.id"
                                class="border-b last:border-0"
                            >
                                <td class="px-4 py-3 font-medium">{{ u.name }}</td>
                                <td class="px-4 py-3">{{ u.email }}</td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ u.role?.name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            @click="openUserEdit(u)"
                                        >
                                            Edit
                                        </Button>
                                        <Form
                                            v-bind="UserController.destroy.form(u)"
                                            :options="{
                                                preserveScroll: true,
                                            }"
                                            class="inline"
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
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <Dialog v-model:open="userEditOpen">
                    <DialogContent
                        v-if="editingUser"
                        class="sm:max-w-md"
                        @pointer-down-outside="editingUser = null"
                    >
                        <Form
                            v-bind="UserController.update.form(editingUser)"
                            reset-on-success
                            class="space-y-4"
                            :options="{ preserveScroll: true }"
                            @success="userEditOpen = false"
                            v-slot="{ errors, processing }"
                        >
                            <DialogHeader>
                                <DialogTitle>Edit user</DialogTitle>
                                <DialogDescription>
                                    Update user details. Leave password blank to
                                    keep the current password.
                                </DialogDescription>
                            </DialogHeader>
                            <div class="grid gap-2">
                                <Label :for="`edit-user-name-${editingUser.id}`"
                                    >Name</Label
                                >
                                <Input
                                    :id="`edit-user-name-${editingUser.id}`"
                                    name="name"
                                    required
                                    :default-value="editingUser.name"
                                    autocomplete="name"
                                />
                                <InputError :message="errors.name" />
                            </div>
                            <div class="grid gap-2">
                                <Label :for="`edit-user-email-${editingUser.id}`"
                                    >Email</Label
                                >
                                <Input
                                    :id="`edit-user-email-${editingUser.id}`"
                                    type="email"
                                    name="email"
                                    required
                                    :default-value="editingUser.email"
                                    autocomplete="email"
                                />
                                <InputError :message="errors.email" />
                            </div>
                            <div class="grid gap-2">
                                <Label :for="`edit-user-role-${editingUser.id}`"
                                    >Role</Label
                                >
                                <select
                                    :id="`edit-user-role-${editingUser.id}`"
                                    name="role_id"
                                    required
                                    class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option
                                        v-for="r in props.roles"
                                        :key="r.id"
                                        :value="r.id"
                                        :selected="r.id === editingUser.role_id"
                                    >
                                        {{ r.name }}
                                    </option>
                                </select>
                                <InputError :message="errors.role_id" />
                            </div>
                            <div class="grid gap-2">
                                <Label
                                    :for="`edit-user-password-${editingUser.id}`"
                                    >New password</Label
                                >
                                <PasswordInput
                                    :id="`edit-user-password-${editingUser.id}`"
                                    name="password"
                                    autocomplete="new-password"
                                />
                                <InputError :message="errors.password" />
                            </div>
                            <div class="grid gap-2">
                                <Label
                                    :for="`edit-user-password-2-${editingUser.id}`"
                                    >Confirm new password</Label
                                >
                                <PasswordInput
                                    :id="`edit-user-password-2-${editingUser.id}`"
                                    name="password_confirmation"
                                    autocomplete="new-password"
                                />
                                <InputError
                                    :message="errors.password_confirmation"
                                />
                            </div>
                            <DialogFooter class="gap-2">
                                <DialogClose as-child>
                                    <Button type="button" variant="secondary">
                                        Cancel
                                    </Button>
                                </DialogClose>
                                <Button type="submit" :disabled="processing">
                                    Save
                                </Button>
                            </DialogFooter>
                        </Form>
                    </DialogContent>
                </Dialog>
            </div>
        </div>
    </div>
</template>
