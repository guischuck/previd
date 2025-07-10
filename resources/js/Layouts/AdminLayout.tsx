import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import { User } from '@/types';
import { Building2, Users, FileText, GitBranch, Settings, LogOut } from 'lucide-react';

interface Props {
  children: React.ReactNode;
}

const AdminLayout: React.FC<Props> = ({ children }) => {
  const { auth } = usePage().props as { auth: { user: User } };

  const menuItems = [
    {
      name: 'Dashboard',
      href: route('admin.dashboard'),
      icon: <Building2 className="w-5 h-5" />,
    },
    {
      name: 'Empresas',
      href: route('admin.companies.index'),
      icon: <Building2 className="w-5 h-5" />,
    },
    {
      name: 'Usuários',
      href: route('admin.users.index'),
      icon: <Users className="w-5 h-5" />,
    },
    {
      name: 'Documentos',
      href: route('admin.documents.index'),
      icon: <FileText className="w-5 h-5" />,
    },
    {
      name: 'Templates',
      href: route('admin.templates.index'),
      icon: <GitBranch className="w-5 h-5" />,
    },
    {
      name: 'Configurações',
      href: route('admin.settings.index'),
      icon: <Settings className="w-5 h-5" />,
    },
  ];

  return (
    <div className="min-h-screen bg-gray-100">
      {/* Sidebar */}
      <div className="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200">
        <div className="flex flex-col h-full">
          {/* Logo */}
          <div className="flex items-center justify-center h-16 px-4 border-b border-gray-200">
            <Link href={route('admin.dashboard')} className="text-xl font-bold text-gray-900">
              Previdia Admin
            </Link>
          </div>

          {/* Navigation */}
          <nav className="flex-1 p-4 space-y-1 overflow-y-auto">
            {menuItems.map((item) => (
              <Link
                key={item.name}
                href={item.href}
                className={`flex items-center px-4 py-2 text-sm font-medium rounded-lg ${
                  route().current(item.href)
                    ? 'bg-gray-100 text-gray-900'
                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                }`}
              >
                {item.icon}
                <span className="ml-3">{item.name}</span>
              </Link>
            ))}
          </nav>

          {/* User */}
          <div className="p-4 border-t border-gray-200">
            <div className="flex items-center">
              <div className="flex-shrink-0">
                <span className="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-500">
                  <span className="text-sm font-medium leading-none text-white">
                    {auth.user.name[0]}
                  </span>
                </span>
              </div>
              <div className="ml-3">
                <p className="text-sm font-medium text-gray-900">{auth.user.name}</p>
                <p className="text-xs font-medium text-gray-500">{auth.user.email}</p>
              </div>
              <Link
                href={route('logout')}
                method="post"
                as="button"
                className="p-2 ml-auto text-gray-400 rounded-md hover:text-gray-500 hover:bg-gray-100"
              >
                <LogOut className="w-5 h-5" />
              </Link>
            </div>
          </div>
        </div>
      </div>

      {/* Main Content */}
      <div className="pl-64">
        <main className="py-12">
          <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
            {children}
          </div>
        </main>
      </div>
    </div>
  );
};

export default AdminLayout; 