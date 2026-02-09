<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { computed } from 'vue';
import { route } from 'ziggy-js';

const page = usePage();

// Convert dynamic menu data to NavItem format
const mainNavItems = computed(() => {
    const menus = page.props.menus as any[] || [];
    
    return menus.map(menu => {
        const navItem: NavItem = {
            title: menu.title,
            href: menu.route ? route(menu.route) : (menu.url || '#'),
            icon: LayoutGrid, // Always use LayoutGrid as fallback since we can't dynamically load icon classes
        };

        // Add children if they exist
        if (menu.children && menu.children.length > 0) {
            navItem.items = menu.children.map((child: any) => ({
                title: child.title,
                href: child.route ? route(child.route) : (child.url || '#'),
                icon: LayoutGrid, // Always use LayoutGrid as fallback
            }));
        }

        return navItem;
    });
});

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
