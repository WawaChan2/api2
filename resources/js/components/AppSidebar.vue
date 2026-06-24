<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, FolderGit2, LayoutGrid, ChartColumnBig, ShoppingBasket, Package, ShoppingBag, Warehouse } from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
// import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { cart, catalog, dashboard, orders, stats } from '@/routes';
import type { NavItem } from '@/types';

const page = usePage();
const role = computed(() => (page.props.auth as any).role);

const adminNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Inventory',
        href: '/inventory',
        icon: Warehouse,
    },
]

const userNavItems: NavItem[] = [
    {
        title: 'Product Catalog',
        href: catalog(),
        icon: ShoppingBag,
    },
    {
        title: 'Orders',
        href: orders(),
        icon: Package,
    },
    {
        title: 'Stats',
        href: stats(),
        icon: ChartColumnBig,
    },
    {
        title: 'Cart',
        href: cart(),
        icon: ShoppingBasket,
    },
];

const mainNavItems = computed(() => role.value === 'ADMIN' ? adminNavItems : userNavItems);

const homePath = computed(() =>
    role.value === 'ADMIN' ? dashboard() : catalog()
);

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: FolderGit2,
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
                        <Link :href="homePath">
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
            <!-- <NavFooter :items="footerNavItems" /> -->
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
