export interface Company {
  id: number;
  name: string;
  is_active: boolean;
  users_count: number;
  cases_count: number;
  created_at: string;
  updated_at: string;
}

export interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  is_super_admin: boolean;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

export interface BreadcrumbItem {
  title: string;
  href: string;
} 