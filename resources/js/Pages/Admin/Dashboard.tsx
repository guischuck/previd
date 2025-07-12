import React from 'react';
import { Head } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Building2, Users, FileText, GitBranch, TrendingUp } from 'lucide-react';

interface Props {
  stats: {
    companies: {
      total: number;
      active: number;
    };
    users: {
      total: number;
      active: number;
    };
    documents: {
      total: number;
    };
    petitionTemplates: {
      total: number;
      active: number;
    };
    workflowTemplates: {
      total: number;
      active: number;
    };
  };
  financial: {
    monthly_revenue: number;
    recent_payments: number;
    active_subscriptions: number;
  };
  recentCompanies: Array<{
    id: number;
    name: string;
    users_count: number;
    created_at: string;
  }>;
}

const Dashboard: React.FC<Props> = ({ stats, financial, recentCompanies }) => {
  return (
    <AdminLayout>
      <Head title="Dashboard Administrativo" />

      <div className="flex items-center justify-between mb-6">
        <h2 className="text-xl font-semibold leading-tight text-gray-800">
          Dashboard Administrativo
        </h2>
      </div>

      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {/* Empresas */}
        <Card>
          <CardHeader className="flex flex-row items-center justify-between pb-2 space-y-0">
            <CardTitle className="text-sm font-medium">Empresas</CardTitle>
            <Building2 className="w-4 h-4 text-gray-500" />
          </CardHeader>
          <CardContent>
            <div className="grid gap-4">
              <div>
                <div className="text-2xl font-bold">{stats.companies.total}</div>
                <p className="text-xs text-gray-500">Total de empresas</p>
              </div>
              <div>
                <div className="text-xl font-semibold text-green-600">{stats.companies.active}</div>
                <p className="text-xs text-gray-500">Empresas ativas</p>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Usuários */}
        <Card>
          <CardHeader className="flex flex-row items-center justify-between pb-2 space-y-0">
            <CardTitle className="text-sm font-medium">Usuários</CardTitle>
            <Users className="w-4 h-4 text-gray-500" />
          </CardHeader>
          <CardContent>
            <div className="grid gap-4">
              <div>
                <div className="text-2xl font-bold">{stats.users.total}</div>
                <p className="text-xs text-gray-500">Total de usuários</p>
              </div>
              <div>
                <div className="text-xl font-semibold text-green-600">{stats.users.active}</div>
                <p className="text-xs text-gray-500">Usuários ativos</p>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Documentos */}
        <Card>
          <CardHeader className="flex flex-row items-center justify-between pb-2 space-y-0">
            <CardTitle className="text-sm font-medium">Base de Conhecimento</CardTitle>
            <FileText className="w-4 h-4 text-gray-500" />
          </CardHeader>
          <CardContent>
            <div className="grid gap-4">
              <div>
                <div className="text-2xl font-bold">{stats.documents.total}</div>
                <p className="text-xs text-gray-500">Total de documentos</p>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Templates de Petição */}
        <Card>
          <CardHeader className="flex flex-row items-center justify-between pb-2 space-y-0">
            <CardTitle className="text-sm font-medium">Templates de Petição</CardTitle>
            <GitBranch className="w-4 h-4 text-gray-500" />
          </CardHeader>
          <CardContent>
            <div className="grid gap-4">
              <div>
                <div className="text-2xl font-bold">{stats.petitionTemplates.total}</div>
                <p className="text-xs text-gray-500">Total de templates</p>
              </div>
              <div>
                <div className="text-xl font-semibold text-green-600">
                  {stats.petitionTemplates.active}
                </div>
                <p className="text-xs text-gray-500">Templates ativos</p>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Dados Financeiros */}
        <Card>
          <CardHeader className="flex flex-row items-center justify-between pb-2 space-y-0">
            <CardTitle className="text-sm font-medium">Financeiro</CardTitle>
            <TrendingUp className="w-4 h-4 text-gray-500" />
          </CardHeader>
          <CardContent>
            <div className="grid gap-4">
              <div>
                <div className="text-2xl font-bold">
                  R$ {(financial.monthly_revenue / 100).toFixed(2)}
                </div>
                <p className="text-xs text-gray-500">Receita mensal</p>
              </div>
              <div>
                <div className="text-xl font-semibold">
                  {financial.active_subscriptions}
                </div>
                <p className="text-xs text-gray-500">Assinaturas ativas</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Empresas Recentes */}
      <div className="mt-8">
        <h3 className="mb-4 text-lg font-semibold">Empresas Recentes</h3>
        <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div className="p-6">
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-gray-50">
                  <tr>
                    <th scope="col" className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                      Nome
                    </th>
                    <th scope="col" className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                      Usuários
                    </th>
                    <th scope="col" className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                      Data de Criação
                    </th>
                  </tr>
                </thead>
                <tbody className="bg-white divide-y divide-gray-200">
                  {recentCompanies.map((company) => (
                    <tr key={company.id}>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div className="text-sm font-medium text-gray-900">
                          {company.name}
                        </div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div className="text-sm text-gray-900">
                          {company.users_count}
                        </div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div className="text-sm text-gray-900">
                          {new Date(company.created_at).toLocaleDateString('pt-BR')}
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </AdminLayout>
  );
};

export default Dashboard; 