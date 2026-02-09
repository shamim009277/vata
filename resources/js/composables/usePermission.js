import { usePage } from '@inertiajs/vue3';

export function usePermission() {
    const hasPermission = (name) => {
        const { props } = usePage();
        const permissions = props.auth.permissions || [];
        const roles = props.auth.roles || [];
        
        if (roles.includes('Super Admin')) {
            return true;
        }
        
        return permissions.includes(name);
    };

    const hasRole = (name) => {
        const { props } = usePage();
        const roles = props.auth.roles || [];
        return roles.includes(name);
    };

    return { hasPermission, hasRole };
}
