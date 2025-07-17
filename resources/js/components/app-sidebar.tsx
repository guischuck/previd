import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import {
    Activity,
    BookOpen,
    Briefcase,
    Building,
    Clock,
    FileText,
    Folder,
    GitBranch,
    Globe,
    LayoutGrid,
    MessageCircle,
    Settings,
    Users,
    HelpCircle,
    Star,
    AlertTriangle,
} from 'lucide-react';
import AppLogo from './app-logo';

export function AppSidebar() {
    const { auth } = usePage().props as any;
    const user = auth?.user;

    // Navegação específica para super admin
    const superAdminNav: NavItem[] = [
        {
            title: 'Dashboard',
            href: '/admin',
            icon: LayoutGrid,
        },
        {
            title: 'Empresas',
            href: '/admin/companies',
            icon: Building,
        },
        {
            title: 'Usuários',
            href: '/admin/users',
            icon: Users,
        },
        {
            title: 'Base de Conhecimento',
            href: '/admin/documents',
            icon: BookOpen,
        },
        {
            title: 'Logs de Erro',
            href: '/admin/error-logs',
            icon: AlertTriangle,
        },
    ];

    // Navegação para usuários normais (empresas)
    const regularNavItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: '/dashboard',
            icon: LayoutGrid,
        },
        {
            title: 'Casos',
            href: '/cases',
            icon: Briefcase,
        },
        {
            title: 'Coletas',
            href: '/coletas',
            icon: FileText,
        },
        {
            title: 'Andamentos',
            href: '/andamentos',
            icon: Clock,
        },
        {
            title: 'Chat',
            href: '/chat',
            icon: MessageCircle,
        },
        {
            title: 'Processos',
            href: '/inss-processes',
            icon: Folder,
        },
        {
            title: 'Petições',
            href: '/petitions',
            icon: BookOpen,
        },
        {
            title: 'Workflows',
            href: '/tasks',
            icon: GitBranch,
        }
    ];

    // Escolher navegação baseada no tipo de usuário
    const mainNavItems = user?.is_super_admin ? superAdminNav : regularNavItems;

    const footerNavItems: NavItem[] = [
        {
            title: 'Configurações',
            href: '/settings',
            icon: Settings,
        },
        {
            title: 'Suporte',
            href: '/support',
            icon: HelpCircle,
        },
    ];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/dashboard" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
