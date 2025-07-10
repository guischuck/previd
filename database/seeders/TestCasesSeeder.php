<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LegalCase;
use App\Models\User;
use App\Models\Company;

class TestCasesSeeder extends Seeder
{
    public function run(): void
    {
        // Verificar se já existem casos
        if (LegalCase::count() > 0) {
            echo "Já existem casos no banco de dados.\n";
            return;
        }

        // Criar uma empresa de teste se não existir
        $company = Company::firstOrCreate([
            'name' => 'Escritório de Teste',
            'slug' => 'escritorio-teste'
        ]);

        // Criar um usuário admin se não existir
        $user = User::firstOrCreate([
            'email' => 'admin@teste.com'
        ], [
            'name' => 'Admin Teste',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => 'admin'
        ]);

        // Criar casos de teste
        $testCases = [
            [
                'case_number' => 'CASO001',
                'client_name' => 'João Silva Santos',
                'client_cpf' => '123.456.789-00',
                'benefit_type' => 'aposentadoria_idade',
                'status' => 'pendente',
                'description' => 'Caso de aposentadoria por idade',
                'company_id' => $company->id,
                'created_by' => $user->id,
            ],
            [
                'case_number' => 'CASO002',
                'client_name' => 'Maria Oliveira Costa',
                'client_cpf' => '987.654.321-00',
                'benefit_type' => 'auxilio_doenca',
                'status' => 'em_coleta',
                'description' => 'Auxílio-doença por problemas de saúde',
                'company_id' => $company->id,
                'created_by' => $user->id,
            ],
            [
                'case_number' => 'CASO003',
                'client_name' => 'Pedro Fernandes Lima',
                'client_cpf' => '456.789.123-00',
                'benefit_type' => 'aposentadoria_especial',
                'status' => 'aguarda_peticao',
                'description' => 'Aposentadoria especial por atividade insalubre',
                'company_id' => $company->id,
                'created_by' => $user->id,
            ],
            [
                'case_number' => 'CASO004',
                'client_name' => 'Ana Paula Rodrigues',
                'client_cpf' => '789.123.456-00',
                'benefit_type' => 'pensao_morte',
                'status' => 'protocolado',
                'description' => 'Pensão por morte do cônjuge',
                'company_id' => $company->id,
                'created_by' => $user->id,
            ],
            [
                'case_number' => 'CASO005',
                'client_name' => 'Carlos Eduardo Souza',
                'client_cpf' => '321.654.987-00',
                'benefit_type' => 'aposentadoria_tempo',
                'status' => 'concluido',
                'description' => 'Aposentadoria por tempo de contribuição',
                'company_id' => $company->id,
                'created_by' => $user->id,
            ]
        ];

        foreach ($testCases as $caseData) {
            LegalCase::create($caseData);
        }

        echo "Criados 5 casos de teste com sucesso!\n";
        echo "Empresa: {$company->name} (ID: {$company->id})\n";
        echo "Usuário: {$user->name} (ID: {$user->id})\n";
    }
} 