<?php

namespace Database\Seeders;

use App\Models\FindingTemplate;
use Illuminate\Database\Seeder;

/**
 * Starter library of reusable findings, mapped to OWASP Top 10 (2021).
 */
class FindingTemplateSeeder extends Seeder
{
    public function run(): void
    {
        foreach (self::templates() as $template) {
            FindingTemplate::updateOrCreate(['title' => $template['title']], $template);
        }
    }

    /**
     * @return list<array<string, string>>
     */
    public static function templates(): array
    {
        return [
            [
                'title' => 'Inyección SQL en parámetro de búsqueda',
                'category' => 'A03:2021 Injection',
                'cvss_vector' => 'CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H',
                'description' => 'El parámetro no se parametriza antes de concatenarse en la consulta SQL. Un atacante puede alterar la lógica de la consulta y extraer o modificar información arbitraria de la base de datos.',
                'impact' => 'Lectura y modificación completa de la base de datos, incluidas credenciales y datos personales. Posible ejecución de comandos según el motor y sus privilegios.',
                'remediation' => "Usar consultas parametrizadas / ORM en todos los accesos a datos.\nAplicar validación por lista blanca y principio de mínimo privilegio en la cuenta de base de datos.",
                'references' => "https://owasp.org/Top10/A03_2021-Injection/\nhttps://cwe.mitre.org/data/definitions/89.html",
            ],
            [
                'title' => 'Cross-Site Scripting (XSS) reflejado',
                'category' => 'A03:2021 Injection',
                'cvss_vector' => 'CVSS:3.1/AV:N/AC:L/PR:N/UI:R/S:C/C:L/I:L/A:N',
                'description' => 'La aplicación refleja la entrada del usuario en la respuesta HTML sin codificarla, permitiendo inyectar JavaScript que se ejecuta en el navegador de la víctima.',
                'impact' => 'Robo de sesión, acciones en nombre de la víctima y phishing creíble bajo el dominio legítimo.',
                'remediation' => "Codificar la salida según el contexto (HTML, atributo, JS, URL).\nDesplegar una Content-Security-Policy restrictiva y marcar las cookies de sesión como HttpOnly.",
                'references' => "https://owasp.org/www-community/attacks/xss/\nhttps://cwe.mitre.org/data/definitions/79.html",
            ],
            [
                'title' => 'Referencia directa insegura a objetos (IDOR)',
                'category' => 'A01:2021 Broken Access Control',
                'cvss_vector' => 'CVSS:3.1/AV:N/AC:L/PR:L/UI:N/S:U/C:H/I:L/A:N',
                'description' => 'Los recursos se identifican con IDs secuenciales y el servidor no comprueba que pertenezcan al usuario autenticado. Cambiando el identificador se accede a datos de otros clientes.',
                'impact' => 'Acceso a información de terceros (facturas, documentos, datos personales) con una cuenta de bajo privilegio.',
                'remediation' => 'Autorizar cada acceso en servidor contra el propietario del recurso. Usar identificadores no predecibles como defensa en profundidad, no como control.',
                'references' => "https://owasp.org/Top10/A01_2021-Broken_Access_Control/\nhttps://cwe.mitre.org/data/definitions/639.html",
            ],
            [
                'title' => 'Server-Side Request Forgery (SSRF) en importación por URL',
                'category' => 'A10:2021 SSRF',
                'cvss_vector' => 'CVSS:3.1/AV:N/AC:L/PR:L/UI:N/S:C/C:H/I:N/A:N',
                'description' => 'La funcionalidad de importación descarga cualquier URL indicada por el usuario desde el servidor, incluidas direcciones internas y el endpoint de metadatos del proveedor cloud.',
                'impact' => 'Acceso a servicios internos y robo de credenciales temporales de la instancia (169.254.169.254).',
                'remediation' => 'Validar destino contra lista blanca, resolver DNS y bloquear rangos privados y link-local. Exigir IMDSv2 en las instancias.',
                'references' => "https://owasp.org/Top10/A10_2021-Server-Side_Request_Forgery_%28SSRF%29/\nhttps://cwe.mitre.org/data/definitions/918.html",
            ],
            [
                'title' => 'Ausencia de limitación de intentos en login',
                'category' => 'A07:2021 Identification and Authentication Failures',
                'cvss_vector' => 'CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:L/I:L/A:N',
                'description' => 'El endpoint de autenticación no aplica rate limiting, bloqueo progresivo ni CAPTCHA, lo que permite ataques de fuerza bruta y credential stuffing.',
                'impact' => 'Compromiso de cuentas con contraseñas débiles o reutilizadas.',
                'remediation' => 'Limitar intentos por cuenta e IP con retroceso exponencial, alertar de patrones anómalos y ofrecer MFA.',
                'references' => 'https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html',
            ],
            [
                'title' => 'Cabeceras de seguridad HTTP ausentes',
                'category' => 'A05:2021 Security Misconfiguration',
                'cvss_vector' => 'CVSS:3.1/AV:N/AC:H/PR:N/UI:R/S:U/C:L/I:N/A:N',
                'description' => 'Las respuestas no incluyen Content-Security-Policy, Strict-Transport-Security ni X-Content-Type-Options.',
                'impact' => 'Aumenta la explotabilidad de XSS, clickjacking y ataques de degradación a HTTP.',
                'remediation' => 'Configurar CSP, HSTS (con preload), X-Content-Type-Options: nosniff, Referrer-Policy y frame-ancestors.',
                'references' => 'https://owasp.org/www-project-secure-headers/',
            ],
            [
                'title' => 'Componente con vulnerabilidad conocida',
                'category' => 'A06:2021 Vulnerable and Outdated Components',
                'cvss_vector' => 'CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:N/A:N',
                'description' => 'Se identifica una versión de librería con CVE público y exploit disponible.',
                'impact' => 'Depende del componente; en este caso, divulgación de información sensible sin autenticación.',
                'remediation' => 'Actualizar a la versión corregida y establecer SCA en CI (Dependabot, osv-scanner) con SLA de parcheo.',
                'references' => 'https://owasp.org/Top10/A06_2021-Vulnerable_and_Outdated_Components/',
            ],
            [
                'title' => 'Token JWT firmado con secreto débil',
                'category' => 'A02:2021 Cryptographic Failures',
                'cvss_vector' => 'CVSS:3.1/AV:N/AC:H/PR:N/UI:N/S:U/C:H/I:H/A:N',
                'description' => 'El secreto HS256 de los JWT se recupera con un ataque de diccionario offline, lo que permite forjar tokens válidos.',
                'impact' => 'Suplantación de cualquier usuario, incluido el administrador.',
                'remediation' => 'Rotar el secreto por uno aleatorio de 256 bits o migrar a RS256/EdDSA, invalidar las sesiones activas y fijar el algoritmo en la verificación.',
                'references' => 'https://cheatsheetseries.owasp.org/cheatsheets/JSON_Web_Token_for_Java_Cheat_Sheet.html',
            ],
            [
                'title' => 'Kerberoasting: cuentas de servicio con SPN y contraseña débil',
                'category' => 'Active Directory',
                'cvss_vector' => 'CVSS:3.1/AV:N/AC:L/PR:L/UI:N/S:C/C:H/I:H/A:N',
                'description' => 'Cualquier usuario del dominio puede solicitar tickets TGS de cuentas con SPN y crackearlos offline. Dos cuentas de servicio usan contraseñas de diccionario.',
                'impact' => 'Escalada a cuentas de servicio con privilegios elevados en servidores críticos.',
                'remediation' => 'Usar gMSA o contraseñas de 25+ caracteres, forzar AES y monitorizar peticiones TGS anómalas (evento 4769).',
                'references' => 'https://attack.mitre.org/techniques/T1558/003/',
            ],
            [
                'title' => 'Bucket de almacenamiento con listado público',
                'category' => 'Cloud',
                'cvss_vector' => 'CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:N/A:N',
                'description' => 'El bucket permite listar y descargar objetos de forma anónima, incluidas copias de seguridad.',
                'impact' => 'Exposición de datos personales y copias de seguridad de la aplicación.',
                'remediation' => 'Activar Block Public Access a nivel de cuenta, revisar políticas y servir contenido público vía CDN con firma.',
                'references' => 'https://docs.aws.amazon.com/AmazonS3/latest/userguide/access-control-block-public-access.html',
            ],
        ];
    }
}
