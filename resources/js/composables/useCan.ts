import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export const useCan = () => {
    const page = usePage();
    const permissions = computed(() => (page.props.auth.permissions as string[] | undefined) ?? []);

    const can = (permission: string) => permissions.value.includes(permission);
    const canAny = (perms: string[]) => perms.some((perm) => can(perm));
    const canAll = (perms: string[]) => perms.every((perm) => can(perm));

    return { permissions, can, canAny, canAll };
};
