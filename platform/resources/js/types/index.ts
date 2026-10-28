import type { LucideIcon } from 'lucide-vue-next';

export type Role = 'admin' | 'pentester' | 'client';
export type Severity = 'critical' | 'high' | 'medium' | 'low' | 'info';
export type EngagementStatus = 'planning' | 'in_progress' | 'review' | 'delivered';
export type FindingStatus = 'open' | 'retest' | 'remediated' | 'accepted_risk';

export interface Auth {
    user: User;
    role: Role;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface Option {
    value: string;
    label: string;
}

export interface SharedData {
    name: string;
    auth: Auth;
    flash: { success: string | null };
    demo: boolean;
    ziggy: {
        location: string;
        url: string;
        port: null | number;
        defaults: Record<string, unknown>;
        routes: Record<string, string>;
    };
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    role: Role;
    client_id: number | null;
    client?: { id: number; name: string } | null;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface Client {
    id: number;
    name: string;
    slug: string;
    sector: string | null;
    contact_name: string | null;
    contact_email: string | null;
    engagements_count?: number;
    users_count?: number;
}

export interface Engagement {
    id: number;
    code: string;
    title: string;
    type: string;
    status: EngagementStatus;
    scope: string;
    methodology: string | null;
    starts_on: string;
    ends_on: string;
    executive_summary: string | null;
    published_at: string | null;
    client_id: number;
    lead_id: number | null;
    client?: Pick<Client, 'id' | 'name'> & Partial<Client>;
    lead?: Pick<User, 'id' | 'name'> & Partial<User>;
    team?: Pick<User, 'id' | 'name' | 'email'>[];
    findings_count?: number;
    reports?: ReportVersion[];
}

export interface Finding {
    id: number;
    engagement_id: number;
    template_id: number | null;
    number: number;
    reference: string;
    title: string;
    cvss_vector: string;
    cvss_score: number;
    severity: Severity;
    status: FindingStatus;
    affected_assets: string;
    description: string;
    proof_of_concept: string | null;
    impact: string;
    remediation: string;
    references: string | null;
    engagement?: Pick<Engagement, 'id' | 'code' | 'title'>;
}

export interface FindingTemplate {
    id: number;
    title: string;
    category: string;
    cvss_vector: string;
    cvss_score: number;
    severity: Severity;
    description: string;
    impact: string;
    remediation: string;
    references: string | null;
    findings_count?: number;
}

export interface ReportVersion {
    id: number;
    version: number;
    status: 'queued' | 'ready' | 'failed';
    sha256: string | null;
    size: number | null;
    findings_count: number;
    created_at: string;
    author?: Pick<User, 'id' | 'name'> | null;
}

export interface ActivityLog {
    id: number;
    action: string;
    description: string;
    ip_address: string | null;
    created_at: string;
    user?: Pick<User, 'id' | 'name'> & Partial<User> | null;
}

export interface Paginated<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
}

export type BreadcrumbItemType = BreadcrumbItem;
