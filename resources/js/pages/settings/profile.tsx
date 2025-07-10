import { type BreadcrumbItem, type SharedData } from '@/types';
import { Transition } from '@headlessui/react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { Key, Download, Copy, CheckCircle, Eye, EyeOff } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Configurações de perfil',
        href: '/settings/profile',
    },
];

type ProfileForm = {
    name: string;
    email: string;
};

type Company = {
    id: number;
    name: string;
    api_key: string;
};

type ProfileProps = {
    mustVerifyEmail: boolean;
    status?: string;
    company?: Company;
};

export default function Profile({ mustVerifyEmail, status, company }: ProfileProps) {
    const { auth } = usePage<SharedData>().props;
    const [apiKeyVisible, setApiKeyVisible] = useState(false);
    const [copied, setCopied] = useState(false);

    const { data, setData, patch, errors, processing, recentlySuccessful } = useForm<Required<ProfileForm>>({
        name: auth.user.name,
        email: auth.user.email,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        patch(route('profile.update'), {
            preserveScroll: true,
        });
    };

    const copyToClipboard = async (text: string) => {
        try {
            await navigator.clipboard.writeText(text);
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        } catch (err) {
            console.error('Erro ao copiar texto: ', err);
        }
    };

    const toggleApiKeyVisibility = () => {
        setApiKeyVisible(!apiKeyVisible);
    };

    const formatApiKey = (key: string) => {
        if (!apiKeyVisible) {
            return '*'.repeat(key.length);
        }
        return key;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Configurações de perfil" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall title="Informações do perfil" description="Atualize seu nome e endereço de e-mail" />

                    <form onSubmit={submit} className="space-y-6">
                        <div className="grid gap-2">
                            <Label htmlFor="name">Nome</Label>

                            <Input
                                id="name"
                                className="mt-1 block w-full"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                required
                                autoComplete="name"
                                placeholder="Nome completo"
                            />

                            <InputError className="mt-2" message={errors.name} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="email">Endereço de e-mail</Label>

                            <Input
                                id="email"
                                type="email"
                                className="mt-1 block w-full"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                required
                                autoComplete="username"
                                placeholder="Endereço de e-mail"
                            />

                            <InputError className="mt-2" message={errors.email} />
                        </div>

                        {mustVerifyEmail && auth.user.email_verified_at === null && (
                            <div>
                                <p className="-mt-4 text-sm text-muted-foreground">
                                    Seu endereço de e-mail não foi verificado.{' '}
                                    <Link
                                        href={route('verification.send')}
                                        method="post"
                                        as="button"
                                        className="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                    >
                                        Clique aqui para reenviar o e-mail de verificação.
                                    </Link>
                                </p>

                                {status === 'verification-link-sent' && (
                                    <div className="mt-2 text-sm font-medium text-green-600">
                                        Um novo link de verificação foi enviado para seu endereço de e-mail.
                                    </div>
                                )}
                            </div>
                        )}

                        <div className="flex items-center gap-4">
                            <Button disabled={processing}>Salvar</Button>

                            <Transition
                                show={recentlySuccessful}
                                enter="transition ease-in-out"
                                enterFrom="opacity-0"
                                leave="transition ease-in-out"
                                leaveTo="opacity-0"
                            >
                                <p className="text-sm text-neutral-600">Salvo</p>
                            </Transition>
                        </div>
                    </form>

                    {company && (
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Key className="h-5 w-5" />
                                    Integração - Extensão Chrome
                                </CardTitle>
                                <CardDescription>
                                    Configurações para integração com a extensão do Chrome
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-6">
                                <div className="space-y-2">
                                    <Label htmlFor="api_key">Chave API do Escritório</Label>
                                    <div className="flex items-center gap-2">
                                        <div className="flex-1 relative">
                                            <Input
                                                id="api_key"
                                                type="text"
                                                value={formatApiKey(company.api_key)}
                                                className="pr-10"
                                                readOnly
                                            />
                                            <button
                                                type="button"
                                                onClick={toggleApiKeyVisibility}
                                                className="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                                            >
                                                {apiKeyVisible ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                                            </button>
                                        </div>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            onClick={() => copyToClipboard(company.api_key)}
                                            className="flex items-center gap-2"
                                        >
                                            {copied ? (
                                                <>
                                                    <CheckCircle className="h-4 w-4" />
                                                    Copiado!
                                                </>
                                            ) : (
                                                <>
                                                    <Copy className="h-4 w-4" />
                                                    Copiar
                                                </>
                                            )}
                                        </Button>
                                    </div>
                                    <p className="text-sm text-muted-foreground">
                                        Use esta chave para autenticar a extensão do Chrome com o sistema
                                    </p>
                                </div>

                                <div className="space-y-2">
                                    <Label>Download da Extensão</Label>
                                    <div className="flex items-center gap-2">
                                        <a
                                            href="https://chromewebstore.google.com/detail/verificador-de-protocolos/difpmpgkhlmphkpeghlagnjpeefpoodl"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                                        >
                                            <Download className="h-4 w-4" />
                                            Download da Extensão
                                        </a>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            onClick={() => copyToClipboard('https://chromewebstore.google.com/detail/verificador-de-protocolos/difpmpgkhlmphkpeghlagnjpeefpoodl')}
                                        >
                                            <Copy className="h-4 w-4" />
                                        </Button>
                                    </div>
                                    <p className="text-sm text-muted-foreground">
                                        Instale a extensão do Chrome para sincronizar protocolos automaticamente
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    )}
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
