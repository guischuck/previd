import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';

type RegisterForm = {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    company_name: string;
    company_cnpj: string;
};

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm<Required<RegisterForm>>({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        company_name: '',
        company_cnpj: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <AuthLayout title="Criar uma conta" description="Preencha seus dados para criar sua conta e empresa">
            <Head title="Registrar" />
            <form className="flex flex-col gap-6" onSubmit={submit}>
                <div className="grid gap-6">
                    <div className="grid gap-2">
                        <Label htmlFor="company_name">Nome da empresa</Label>
                        <Input
                            id="company_name"
                            type="text"
                            required
                            tabIndex={1}
                            autoComplete="organization"
                            value={data.company_name}
                            onChange={(e) => setData('company_name', e.target.value)}
                            disabled={processing}
                            placeholder="Nome da empresa"
                        />
                        <InputError message={errors.company_name} className="mt-2" />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="company_cnpj">CNPJ (opcional)</Label>
                        <Input
                            id="company_cnpj"
                            type="text"
                            tabIndex={2}
                            autoComplete="off"
                            value={data.company_cnpj}
                            onChange={(e) => setData('company_cnpj', e.target.value)}
                            disabled={processing}
                            placeholder="CNPJ da empresa"
                        />
                        <InputError message={errors.company_cnpj} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="name">Nome completo</Label>
                        <Input
                            id="name"
                            type="text"
                            required
                            tabIndex={3}
                            autoComplete="name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            disabled={processing}
                            placeholder="Seu nome completo"
                        />
                        <InputError message={errors.name} className="mt-2" />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="email">E-mail</Label>
                        <Input
                            id="email"
                            type="email"
                            required
                            tabIndex={4}
                            autoComplete="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            disabled={processing}
                            placeholder="seu@email.com"
                        />
                        <InputError message={errors.email} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="password">Senha</Label>
                        <Input
                            id="password"
                            type="password"
                            required
                            tabIndex={5}
                            autoComplete="new-password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            disabled={processing}
                            placeholder="Senha"
                        />
                        <InputError message={errors.password} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="password_confirmation">Confirme a senha</Label>
                        <Input
                            id="password_confirmation"
                            type="password"
                            required
                            tabIndex={6}
                            autoComplete="new-password"
                            value={data.password_confirmation}
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                            disabled={processing}
                            placeholder="Confirme a senha"
                        />
                        <InputError message={errors.password_confirmation} />
                    </div>
                    <Button type="submit" className="mt-2 w-full" tabIndex={7} disabled={processing}>
                        {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                        Criar conta e empresa
                    </Button>
                </div>
                <div className="text-center text-sm text-muted-foreground">
                    Já possui uma conta?{' '}
                    <TextLink href={route('login')} tabIndex={8}>
                        Entrar
                    </TextLink>
                </div>
            </form>
        </AuthLayout>
    );
}
