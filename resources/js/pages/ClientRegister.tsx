import { useState } from "react";
import { Head, useForm, Link } from "@inertiajs/react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Eye, EyeOff, UserPlus, Phone, Mail, User, CreditCard } from "lucide-react";

export default function ClientRegister() {
    const [showPassword, setShowPassword] = useState(false);
    const [showPasswordConfirm, setShowPasswordConfirm] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        name: "",
        cpf: "",
        email: "",
        phone: "",
        password: "",
        password_confirmation: ""
    });

    const handleSubmit = () => {
        post("/cadastro-cliente");
    };

    const formatCPF = (value) => {
        const numbers = value.replace(/\D/g, "");
        if (numbers.length <= 11) {
            return numbers.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4");
        }
        return value;
    };

    const formatPhone = (value) => {
        const numbers = value.replace(/\D/g, "");
        if (numbers.length <= 11) {
            return numbers.replace(/(\d{2})(\d{4,5})(\d{4})/, "($1) $2-$3");
        }
        return value;
    };

    return (
        <>
            <Head title="Cadastro de Cliente - PrevidIA" />
            
            <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
                <Card className="w-full max-w-md shadow-xl">
                    <CardHeader className="text-center">
                        <div className="mx-auto w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center mb-4">
                            <UserPlus className="w-6 h-6 text-white" />
                        </div>
                        <CardTitle className="text-2xl font-bold text-gray-900">
                            Cadastro de Cliente
                        </CardTitle>
                        <CardDescription className="text-gray-600">
                            Preencha seus dados para acessar o sistema
                        </CardDescription>
                    </CardHeader>

                    <CardContent>
                        <div className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="name" className="flex items-center gap-2">
                                    <User className="w-4 h-4" />
                                    Nome completo
                                </Label>
                                <Input
                                    id="name"
                                    type="text"
                                    value={data.name}
                                    onChange={(e) => setData("name", e.target.value)}
                                    placeholder="Digite seu nome completo"
                                    className={errors.name ? "border-red-500" : ""}
                                />
                                {errors.name && (
                                    <p className="text-sm text-red-600">{errors.name}</p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="cpf" className="flex items-center gap-2">
                                    <CreditCard className="w-4 h-4" />
                                    CPF
                                </Label>
                                <Input
                                    id="cpf"
                                    type="text"
                                    value={data.cpf}
                                    onChange={(e) => {
                                        const formatted = formatCPF(e.target.value);
                                        setData("cpf", formatted);
                                    }}
                                    placeholder="000.000.000-00"
                                    maxLength={14}
                                    className={errors.cpf ? "border-red-500" : ""}
                                />
                                {errors.cpf && (
                                    <p className="text-sm text-red-600">{errors.cpf}</p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="email" className="flex items-center gap-2">
                                    <Mail className="w-4 h-4" />
                                    Email
                                </Label>
                                <Input
                                    id="email"
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData("email", e.target.value)}
                                    placeholder="seu@email.com"
                                    className={errors.email ? "border-red-500" : ""}
                                />
                                {errors.email && (
                                    <p className="text-sm text-red-600">{errors.email}</p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="phone" className="flex items-center gap-2">
                                    <Phone className="w-4 h-4" />
                                    Telefone
                                </Label>
                                <Input
                                    id="phone"
                                    type="text"
                                    value={data.phone}
                                    onChange={(e) => {
                                        const formatted = formatPhone(e.target.value);
                                        setData("phone", formatted);
                                    }}
                                    placeholder="(00) 00000-0000"
                                    maxLength={15}
                                    className={errors.phone ? "border-red-500" : ""}
                                />
                                {errors.phone && (
                                    <p className="text-sm text-red-600">{errors.phone}</p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="password">Senha</Label>
                                <div className="relative">
                                    <Input
                                        id="password"
                                        type={showPassword ? "text" : "password"}
                                        value={data.password}
                                        onChange={(e) => setData("password", e.target.value)}
                                        placeholder="Mínimo 6 caracteres"
                                        className={errors.password ? "border-red-500 pr-10" : "pr-10"}
                                    />
                                    <button
                                        type="button"
                                        onClick={() => setShowPassword(!showPassword)}
                                        className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                                    >
                                        {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                                    </button>
                                </div>
                                {errors.password && (
                                    <p className="text-sm text-red-600">{errors.password}</p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="password_confirmation">Confirmar senha</Label>
                                <div className="relative">
                                    <Input
                                        id="password_confirmation"
                                        type={showPasswordConfirm ? "text" : "password"}
                                        value={data.password_confirmation}
                                        onChange={(e) => setData("password_confirmation", e.target.value)}
                                        placeholder="Confirme sua senha"
                                        className="pr-10"
                                    />
                                    <button
                                        type="button"
                                        onClick={() => setShowPasswordConfirm(!showPasswordConfirm)}
                                        className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                                    >
                                        {showPasswordConfirm ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                                    </button>
                                </div>
                            </div>

                            <Button 
                                onClick={handleSubmit}
                                className="w-full bg-blue-600 hover:bg-blue-700" 
                                disabled={processing}
                            >
                                {processing ? "Cadastrando..." : "Criar conta"}
                            </Button>

                            <div className="text-center text-sm">
                                <span className="text-gray-600">Já tem uma conta? </span>
                                <Link 
                                    href="/login" 
                                    className="text-blue-600 hover:text-blue-700 font-medium"
                                >
                                    Fazer login
                                </Link>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}