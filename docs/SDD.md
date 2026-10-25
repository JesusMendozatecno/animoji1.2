# SDD - ANIMOJI
## Especificación Guiada por Especificaciones (Spec-Driven Development)

| Campo | Valor |
|---|---|
| Proyecto | ANIMOJI |
| Versión del documento | 0.2 (borrador de levantamiento) |
| Fecha | 2026-10-22 |
| Estado | En definición de requisitos |
| Autor | Equipo ANIMOJI |
| Próximos artefactos | `plan.md` (plan técnico) y `tasks.md` (desglose de tareas) |

---

## 1. Cómo usar este documento

Este SDD se trabaja en 4 fases y no se escribe código de una funcionalidad hasta que su especificación esté aprobada.

1. **Especificar** (este documento): qué, para quién, por qué y criterios de aceptación.
2. **Planificar** (`plan.md`): arquitectura, modelo de datos, contratos de API y decisiones técnicas.
3. **Dividir** (`tasks.md`): tareas pequeñas, ordenadas y verificables.
4. **Implementar y validar**: cada tarea se cierra solo si cumple sus criterios de aceptación y pruebas.

Convenciones:

- `[x]` decidido, `[ ]` pendiente, `[?]` requiere investigación.
- Prioridad: **P0** bloqueante, **P1** importante, **P2** deseable.
- Los IDs (RF, RNF, N-01, etc.) sirven para trazabilidad entre spec, plan, tareas y pruebas.
- Las preguntas con **(Recomendado)** incluyen una opción sugerida; se puede cambiar.

Regla de oro: si una funcionalidad no tiene criterio de aceptación, no está lista para implementarse.

---

## 2. Resumen ejecutivo

ANIMOJI será una plataforma web y móvil para consumir y compartir contenido de entretenimiento: anime, manga, música y juegos, con capa social (perfiles, publicaciones, comentarios, seguidores, notificaciones), cuentas con roles, interfaz bilingüe ES/EN y monetización mediante suscripciones o compras.

El producto se especifica completo, pero se entrega por fases para reducir riesgo. La web se construye con Laravel + Inertia + Vue/React. La app móvil será **nativa** (Flutter o React Native) y consumirá una **API versionada** `/api/v1` con Sanctum. La web y la app comparten la misma lógica de negocio y la misma base de datos gestionada en la nube; ninguna app móvil se conecta directo a la base de datos. Una PWA queda como opción complementaria, no como sustituto de la app nativa.

---

## 3. Decisiones tomadas en el kickoff

| Área | Decisión | Estado | Observación |
|---|---|---|---|
| Producto | Anime, manga, música, juegos y red social | [x] | Idea principal confirmada |
| Alcance | Producto completo | [x] | Se entregará por fases |
| Frontend web | Inertia + Vue/React | [x] | Falta elegir Vue o React (X-05) |
| App móvil | Nativa (Flutter o React Native) consumiendo `/api/v1` | [x] | App real para Android/iOS; PWA opcional (T-16) |
| Comunicación web/móvil | API REST versionada con Laravel Sanctum | [x] | API-first; la app no accede a la BD |
| Autenticación | Registro, login y roles | [x] | Ver sección 6 |
| Base de datos | Gestionada, accesible desde web y móvil | [x] | Motor por definir (T-03) |
| Multimedia | Imágenes, GIFs y URLs externas | [x] | Ver requisitos de almacenamiento |
| Integraciones | Email, Stripe/PayPal, redes sociales | [x] | Ver módulo de monetización |
| Idioma | Bilingüe ES/EN | [x] | Ver sección 7.9 |
| Entorno local | XAMPP + VirtualHost `animoji.local` | [x] | DocumentRoot debe apuntar a `public/` |
| Metodología | SDD por fases | [x] | Spec -> Plan -> Tasks -> Implementación |

---

## 4. Visión, objetivos y no objetivos

### 4.1 Problema

Los usuarios de anime, manga, música y juegos consumen contenido en múltiples sitios dispersos, sin una comunidad unificada, sin seguimiento centralizado de su progreso y sin una experiencia consistente en web y móvil.

### 4.2 Propuesta de valor

Un solo lugar para descubrir, consumir, organizar y comentar entretenimiento, con comunidad, historial sincronizado y acceso desde cualquier dispositivo.

### 4.3 Objetivos medibles (propuestos)

| ID | Objetivo | Métrica | Meta inicial |
|---|---|---|---|
| OBJ-01 | Retención | Usuarios que vuelven en 7 días | >= 25% |
| OBJ-02 | Engagement | Sesiones por usuario/semana | >= 3 |
| OBJ-03 | Contenido | Catálogo inicial cargado | Por definir (N-04) |
| OBJ-04 | Conversión | Visitas que crean cuenta | >= 8% |
| OBJ-05 | Monetización | Conversión a plan premium | >= 3% |
| OBJ-06 | Calidad | Errores 5xx por semana | < 0.5% de requests |
| OBJ-07 | Rendimiento | LCP en móvil | < 2.5 s |

### 4.4 No objetivos (v1)

- No se desarrollará una versión nativa de escritorio; la web cubre ese caso.
- La app móvil nativa es parte del alcance y se entrega en la fase 4, después de estabilizar la API.
- No se implementará streaming propio de video en la fase 1; se evaluará en T-05.
- No se ofrecerá chat privado ni videollamadas salvo que S-06 lo confirme.
- No se soportarán pagos con criptomonedas.
- No se alojará contenido sin derechos de distribución (ver sección 13).

---

## 5. Alcance y fases

### 5.1 Incluido en el producto completo

- Cuentas, perfiles, roles y moderación.
- Catálogo y consumo de anime, manga, música y juegos.
- Capa social: publicaciones, comentarios, reacciones, seguidores, notificaciones.
- Buscador global y filtros.
- Panel administrativo.
- Bilingüe ES/EN.
- Monetización (suscripción y/o compras).
- API REST versionada para web y móvil.
- App móvil nativa (Android/iOS) con notificaciones push, sincronización de progreso y soporte offline básico.
- PWA instalable como canal complementario (opcional).
- Analítica, respaldos y monitoreo.

### 5.2 Fuera de alcance inicial

- Producción musical o de video propia.
- Traducción automática de contenido.
- Recomendaciones con IA en tiempo real (fase posterior).
- Marketplace de creadores con pagos automáticos (fase posterior).
- Publicación de la app en tiendas durante la fase 1; se hará en la fase 4 tras estabilizar la API.
- Versión nativa de escritorio.

### 5.3 Fases de entrega (para un producto completo)

| Fase | Contenido | Criterio de salida |
|---|---|---|
| Fase 1 - Núcleo y API | Auth, roles, perfiles, catálogo anime/manga, comentarios, admin, i18n, API v1 base | Un usuario puede registrarse, explorar y comentar; un admin carga contenido; la API responde a los flujos base |
| Fase 2 - Social y media | Música, juegos, feed, follows, notificaciones, búsqueda avanzada | Contenido social funcional y búsqueda global |
| Fase 3 - Monetización | Planes, Stripe/PayPal, facturas, premium | Un usuario puede pagar y acceder a beneficios |
| Fase 4 - App móvil nativa | Flutter/React Native para Android/iOS, push, sincronización, offline, beta en tiendas | App instalable en beta y API documentada y estable |
| Fase 5 - Crecimiento | Recomendaciones, logros, comunidad, A/B testing, lanzamiento en tiendas | Métricas de retención en meta |

---

## 6. Actores y roles

| Rol | Descripción | Permisos clave |
|---|---|---|
| Visitante | Sin cuenta | Ver catálogo y contenido público, buscar, registrarse |
| Usuario | Cuenta verificada | Perfil, favoritos, historial, comentarios, publicaciones, seguir |
| Creador | Usuario con permiso de publicación | Subir contenido propio, gestionar sus publicaciones |
| Moderador | Revisa reportes | Ocultar/eliminar contenido, silenciar usuarios, resolver reportes |
| Editor | Gestiona catálogo | CRUD de anime, manga, música y juegos |
| Administrador | Control total | Usuarios, roles, configuración, pagos, reportes |
| Soporte | Atención a usuarios | Ver tickets, reembolsos limitados, sin acceso a configuración |

### 6.1 Matriz preliminar de permisos

| Acción | Visitante | Usuario | Creador | Moderador | Editor | Admin |
|---|---|---|---|---|---|---|
| Ver catálogo | Si | Si | Si | Si | Si | Si |
| Comentar/reaccionar | No | Si | Si | Si | Si | Si |
| Publicar contenido | No | No | Si | No | Si | Si |
| Reportar contenido | No | Si | Si | Si | Si | Si |
| Moderar reportes | No | No | No | Si | No | Si |
| CRUD catálogo | No | No | No | No | Si | Si |
| Gestionar usuarios/roles | No | No | No | No | No | Si |
| Ver finanzas | No | No | No | No | No | Si |

Pregunta relacionada: U-05, U-06, U-07.

---

## 7. Requisitos funcionales

### 7.1 Cuentas y autenticación

- **RF-AUTH-01 (P0):** registro con nombre, email y contraseña; verificación de email.
- **RF-AUTH-02 (P0):** login, logout, recuperación de contraseña y "recordarme".
- **RF-AUTH-03 (P0):** roles y permisos (ver sección 6).
- **RF-AUTH-04 (P1):** login social con Google (y opcionalmente Discord).
- **RF-AUTH-05 (P1):** 2FA obligatorio para administradores.
- **RF-AUTH-06 (P1):** perfil público con avatar, bio, enlaces y estadísticas.
- **RF-AUTH-07 (P2):** sesiones activas y cierre remoto.
- **RF-AUTH-08 (P0):** eliminación de cuenta y exportación de datos (GDPR).

### 7.2 Anime

- **RF-ANIME-01 (P0):** catálogo con título, sinopsis, portada, género, año, estado, estudio y puntuación.
- **RF-ANIME-02 (P0):** series con temporadas y episodios ordenados.
- **RF-ANIME-03 (P0):** reproductor integrado (embed externo o HLS según T-05).
- **RF-ANIME-04 (P1):** subtítulos y audios disponibles por episodio.
- **RF-ANIME-05 (P0):** historial y "continuar viendo".
- **RF-ANIME-06 (P1):** favoritos, lista personal y puntuación.
- **RF-ANIME-07 (P2):** calendario de estrenos y notificaciones.
- **RF-ANIME-08 (P1):** filtros por género, año, estado, puntuación y orden.

### 7.3 Manga

- **RF-MANGA-01 (P0):** catálogo con título, autor, estado, géneros y portada.
- **RF-MANGA-02 (P0):** capítulos con páginas ordenadas.
- **RF-MANGA-03 (P0):** lector con modo vertical y paginado.
- **RF-MANGA-04 (P1):** progreso de lectura sincronizado por usuario.
- **RF-MANGA-05 (P1):** comentarios por capítulo.
- **RF-MANGA-06 (P2):** descarga offline para premium.
- **RF-MANGA-07 (P2):** notificaciones de nuevos capítulos.

### 7.4 Música

- **RF-MUS-01 (P1):** catálogo de artistas, álbumes y pistas.
- **RF-MUS-02 (P1):** reproductor persistente con cola y control de volumen.
- **RF-MUS-03 (P1):** playlists propias y públicas.
- **RF-MUS-04 (P2):** letras sincronizadas.
- **RF-MUS-05 (P1):** historial y "me gusta".
- **RF-MUS-06 (P2):** reproducción en segundo plano en web y app móvil nativa.

### 7.5 Juegos

- **RF-JUEGO-01 (P1):** catálogo con portada, descripción, géneros, requisitos y plataformas.
- **RF-JUEGO-02 (P1):** enlaces de descarga o compra externos verificados.
- **RF-JUEGO-03 (P1):** versiones y enlaces espejo.
- **RF-JUEGO-04 (P2):** reseñas y puntuaciones.
- **RF-JUEGO-05 (P2):** control de virus/verificación de enlaces.
- **RF-JUEGO-06 (P0):** solo se publicará contenido con derechos o enlaces legales (ver 13).

### 7.6 Social

- **RF-SOC-01 (P0):** publicaciones con texto, imágenes y GIFs.
- **RF-SOC-02 (P0):** comentarios (con respuestas de un nivel mínimo).
- **RF-SOC-03 (P0):** reacciones/me gusta.
- **RF-SOC-04 (P1):** seguir/dejar de seguir usuarios y feed.
- **RF-SOC-05 (P1):** menciones y hashtags.
- **RF-SOC-06 (P2):** mensajes directos (confirmar en S-06).
- **RF-SOC-07 (P0):** reportar contenido y usuarios.
- **RF-SOC-08 (P1):** bloquear usuarios y privacidad del perfil.
- **RF-SOC-09 (P0):** notificaciones en la app y por email.
- **RF-SOC-10 (P1):** compartir en redes sociales.

### 7.7 Panel administrativo

- **RF-ADMIN-01 (P0):** CRUD de todo el catálogo.
- **RF-ADMIN-02 (P0):** gestión de usuarios, roles y sanciones.
- **RF-ADMIN-03 (P0):** cola de reportes con estados.
- **RF-ADMIN-04 (P1):** métricas básicas (usuarios, contenido, ingresos).
- **RF-ADMIN-05 (P1):** registro de auditoría de acciones administrativas.
- **RF-ADMIN-06 (P1):** configuración de idioma, moneda y planes.

### 7.8 Monetización

- **RF-MON-01 (P1):** planes definidos en panel (precio, duración, beneficios).
- **RF-MON-02 (P1):** pago con Stripe y PayPal.
- **RF-MON-03 (P1):** suscripciones con renovación y cancelación.
- **RF-MON-04 (P1):** facturas y recibos descargables.
- **RF-MON-05 (P2):** cupones y periodos de prueba.
- **RF-MON-06 (P1):** control de acceso a contenido premium.
- **RF-MON-07 (P2):** reembolsos y disputas.

### 7.9 Internacionalización

- **RF-I18N-01 (P0):** interfaz ES/EN con selector y persistencia.
- **RF-I18N-02 (P0):** textos traducidos en backend y frontend.
- **RF-I18N-03 (P1):** URLs localizadas o parámetro de locale.
- **RF-I18N-04 (P2):** contenido con título y sinopsis en ambos idiomas.

### 7.10 Búsqueda y descubrimiento

- **RF-BUS-01 (P0):** búsqueda global por título y texto.
- **RF-BUS-02 (P1):** filtros por tipo de contenido y género.
- **RF-BUS-03 (P2):** búsqueda tolerante a errores y sinónimos.
- **RF-BUS-04 (P2):** secciones "tendencias" y "recomendado".

### 7.11 Notificaciones

- **RF-NOT-01 (P1):** centro de notificaciones en la web.
- **RF-NOT-02 (P1):** email transaccional (verificación, pagos, reportes).
- **RF-NOT-03 (P1):** push web y push móvil (FCM/APNs).
- **RF-NOT-04 (P1):** preferencias de notificación por usuario.

### 7.12 App móvil nativa

- **RF-APP-01 (P0):** app nativa para Android/iOS que consume `/api/v1` con tokens Sanctum.
- **RF-APP-02 (P0):** registro, login, verificación de email, recuperación de contraseña y cierre de sesión.
- **RF-APP-03 (P0):** catálogo de anime, manga, música y juegos con búsqueda y filtros.
- **RF-APP-04 (P0):** reproducción de anime/música y lector de manga adaptados a móvil.
- **RF-APP-05 (P0):** sincronización de progreso (ver, leer, escuchar) con la web en tiempo casi real.
- **RF-APP-06 (P1):** publicaciones, comentarios, reacciones, follows y notificaciones sociales.
- **RF-APP-07 (P1):** notificaciones push mediante FCM (Android) y APNs (iOS).
- **RF-APP-08 (P1):** caché/offline para catálogo, favoritos y descargas premium autorizadas.
- **RF-APP-09 (P1):** deep links y enlaces universales hacia contenido y perfiles.
- **RF-APP-10 (P0):** interfaz bilingüe ES/EN con la misma preferencia de idioma del usuario.
- **RF-APP-11 (P0):** tokens guardados en almacenamiento seguro (Keychain/Keystore), sin secretos en el binario.
- **RF-APP-12 (P2):** compras dentro de la app según políticas de Apple/Google, o redirección a pago web si se permite.
- **RF-APP-13 (P1):** actualización forzada/avisos de versión mínima desde configuración remota.
- **RF-APP-14 (P1):** manejo de red intermitente con reintentos y mensajes claros.

---

## 8. Historias de usuario clave

Formato: como <actor>, quiero <acción> para <beneficio>. Criterios en Gherkin.

**HU-01 (P0) Registro y verificación**
Como visitante, quiero crear una cuenta y verificar mi email para participar en la comunidad.
- Dado un email no registrado, cuando envío el formulario válido, entonces recibo un correo de verificación y no puedo comentar hasta verificarlo.
- Dado un email ya registrado, entonces veo un error claro sin revelar datos sensibles.

**HU-02 (P0) Continuar viendo**
Como usuario, quiero retomar un anime donde lo dejé para no buscar el episodio manualmente.
- Dado un episodio iniciado, cuando vuelvo al catálogo, entonces aparece en "Continuar viendo" con minuto y porcentaje.

**HU-03 (P0) Comentar**
Como usuario verificado, quiero comentar un episodio o capítulo para conversar con la comunidad.
- Dado un comentario válido, cuando lo envío, entonces aparece sin recargar y se registra autor y fecha.
- Dado un comentario con enlaces o palabras prohibidas, entonces se marca para moderación.

**HU-04 (P0) Panel de catálogo**
Como editor, quiero crear una serie con temporadas y episodios para publicar contenido.
- Dado el formulario completo, cuando guardo, entonces se crea en borrador y puedo publicarlo.
- Dado un campo obligatorio vacío, entonces se muestra validación y no se guarda.

**HU-05 (P1) Seguir usuarios**
Como usuario, quiero seguir a otros para ver su actividad en mi feed.
- Dado que sigo a un usuario, cuando publica, entonces su publicación aparece en mi feed.
- Dado que dejo de seguirlo, entonces su contenido deja de aparecer (sin borrar el historial).

**HU-06 (P1) Suscripción premium**
Como usuario, quiero pagar un plan para acceder a beneficios exclusivos.
- Dado un pago aprobado, entonces mi cuenta obtiene el rol premium y acceso inmediato.
- Dado un pago rechazado, entonces no se activa el beneficio y se muestra el motivo.

**HU-07 (P0) Reportar contenido**
Como usuario, quiero reportar contenido inapropiado para mantener la comunidad segura.
- Dado un reporte enviado, entonces aparece en la cola del moderador con estado "pendiente".
- Dado que el moderador resuelve, entonces el reportante recibe una notificación.

**HU-08 (P0) Cambio de idioma**
Como usuario, quiero alternar ES/EN para usar la plataforma en mi idioma.
- Dado que cambio el idioma, entonces la interfaz y los emails se muestran en ese idioma y se conserva en mi sesión.

**HU-09 (P0) App móvil con progreso sincronizado**
Como usuario, quiero usar la app nativa en mi teléfono y continuar donde lo dejé en la web.
- Dado que inicié sesión en la app, cuando abro un anime o manga, entonces veo mi progreso sincronizado con la web.
- Dado que veo un episodio en la app sin conexión, cuando recupero la conexión, entonces el progreso se sincroniza sin duplicar registros.

**HU-10 (P1) Notificaciones push**
Como usuario de la app, quiero recibir avisos de nuevos capítulos, respuestas y pagos.
- Dado que acepto notificaciones, cuando ocurre el evento, entonces recibo una push y al tocarla se abre el contenido correcto.

---

## 9. Requisitos no funcionales

| ID | Categoría | Requisito | Criterio |
|---|---|---|---|
| RNF-01 | Rendimiento | Respuesta de páginas y API | p95 < 500 ms en servidor |
| RNF-02 | Rendimiento | Carga percibida en móvil | LCP < 2.5 s, CLS < 0.1 |
| RNF-03 | Escalabilidad | Usuarios concurrentes iniciales | 1,000 sin degradación crítica |
| RNF-04 | Disponibilidad | Uptime mensual | >= 99.5% |
| RNF-05 | Seguridad | OWASP Top 10 | Sin vulnerabilidades críticas abiertas |
| RNF-06 | Seguridad | Contraseñas y sesiones | Hash Argon2/bcrypt, CSRF, rate limit |
| RNF-07 | Privacidad | Datos personales | Consentimiento, exportación y borrado |
| RNF-08 | Accesibilidad | Estándar | WCAG 2.1 AA en flujos principales |
| RNF-09 | SEO | Páginas públicas indexables | Metadatos, sitemap y canonical |
| RNF-10 | i18n | Cobertura de traducciones | 100% de textos de interfaz |
| RNF-11 | Respaldos | Frecuencia y recuperación | Diario, RPO 24 h, RTO 4 h |
| RNF-12 | Observabilidad | Errores y métricas | Sentry + logs estructurados + uptime |
| RNF-13 | Compatibilidad | Navegadores | Últimas 2 versiones de Chrome, Firefox, Safari, Edge |
| RNF-14 | Legal | Derechos de contenido | Solo contenido autorizado o enlaces legales |
| RNF-15 | Mantenibilidad | Cobertura de pruebas | >= 70% en lógica crítica |
| RNF-16 | API | Versionado y documentación | `/api/v1` + OpenAPI |
| RNF-17 | Móvil | Compatibilidad mínima | Android 8+ e iOS 15+ |
| RNF-18 | Móvil | Sincronización de progreso | < 5 s desde el evento |
| RNF-19 | Móvil | Funcionamiento offline | Catálogo/favoritos en caché; escritura se sincroniza al reconectar |
| RNF-20 | Móvil | Cumplimiento de tiendas | Políticas de Apple App Store y Google Play |
| RNF-21 | Móvil | Seguridad de tokens | Keychain/Keystore; sin secretos en el binario |
| RNF-22 | API | Compatibilidad móvil | Cambios incompatibles solo con nueva versión `/api/v2` |

---

## 10. Arquitectura técnica propuesta (API-first)

### 10.1 Stack

| Capa | Propuesta | Motivo |
|---|---|---|
| Backend | Laravel 11/12, PHP 8.2+ | Ecosistema, auth, colas, validación |
| Web | Inertia + Vue 3 + TypeScript + Tailwind | SPA sin API duplicada para la web |
| App móvil | Nativa: Flutter (recomendado) o React Native/Expo | App real en tiendas, buen rendimiento y offline |
| Comunicación | API REST `/api/v1` + Laravel Sanctum | Un solo backend para web y móvil |
| Base de datos | MySQL 8/MariaDB local y MySQL gestionado en nube (o PostgreSQL gestionado) | Acceso remoto, respaldos y escalado |
| Auth API | Laravel Sanctum (tokens por dispositivo) | Tokens para móvil; revocables |
| Roles | Spatie Laravel Permission | Roles y permisos probados |
| Admin | Filament 3 | Panel rápido y mantenible |
| Colas | Redis (o driver database en local) | Emails, imágenes, notificaciones |
| Storage | Local en desarrollo; S3/Cloudflare R2 en producción | Archivos y CDN |
| Búsqueda | Laravel Scout + Meilisearch (fase 2) | Búsqueda rápida y tolerante |
| Pagos | Laravel Cashier (Stripe) + SDK PayPal | Suscripciones |
| Push móvil | Firebase Cloud Messaging (Android) y APNs (iOS) | Notificaciones nativas |
| Offline móvil | SQLite/Drift (Flutter) o MMKV/WatermelonDB (RN) | Caché y sincronización |
| Tiempo real | Laravel Reverb (fase 2, opcional) | Notificaciones en vivo |
| Pruebas | Pest/PHPUnit + Vitest + Playwright + pruebas de app | Backend, web, E2E y móvil |
| Despliegue | Docker + GitHub Actions + hosting administrado | Reproducible y con CI/CD |

### 10.2 API-first: web y app móvil nativa

La decisión es **API + app nativa**. Esto implica:

- Laravel expone una API REST versionada (`/api/v1`) que es la única puerta de entrada para la app móvil.
- La app móvil **nunca** se conecta directo a MySQL/PostgreSQL; solo consume la API con HTTPS y tokens.
- La web puede usar Inertia para velocidad de desarrollo, pero la lógica de negocio vive en `app/Services` y `app/Actions`, reutilizada por controladores web y API.
- Los contratos de API se documentan con OpenAPI y se versionan; un cambio incompatible requiere `/api/v2`.
- La autenticación móvil usa tokens Sanctum por dispositivo, con revocación y expiración configurables.

Flujo:

```
Web (Inertia/Vue) ──┐
                    ├──> Laravel (Services/Actions) ──> BD gestionada
App (Flutter/RN) ───┘         │
                              └──> API REST /api/v1 (Sanctum)
```

Recomendación de infraestructura:
- Desarrollo local: MySQL/MariaDB de XAMPP.
- Producción: base de datos gestionada (PlanetScale/Railway/Aiven para MySQL, o Neon/Supabase para PostgreSQL) con SSL, IP allowlist y respaldos.
- Una sola fuente de verdad; web y móvil consumen los mismos servicios.

### 10.3 Estructura del repositorio propuesta

```
ANIMOJI/
├─ app/
│  ├─ Http/Controllers/Web/
│  ├─ Http/Controllers/Api/V1/
│  ├─ Models/
│  ├─ Services/
│  ├─ Actions/
│  ├─ Policies/
│  └─ Support/
├─ routes/
│  ├─ web.php
│  ├─ api.php
│  └─ auth.php
├─ resources/
│  ├─ js/ (pages, components, layouts, i18n, stores)
│  ├─ css/
│  └─ views/ (app.blade.php, emails)
├─ database/migrations, seeders, factories
├─ lang/es, lang/en
├─ public/
├─ storage/
├─ tests/Feature, tests/Unit
├─ mobile/            app Flutter o React Native (repositorio o carpeta aparte)
│  ├─ lib/features/   auth, catalog, player, reader, social
│  ├─ lib/core/       api, storage, i18n, theme
│  └─ test/
├─ docs/
│  ├─ SDD.md
│  ├─ plan.md
│  └─ tasks.md
├─ docker/
└─ .env.example
```

Recomendación de organización: mantener el backend Laravel y la app móvil en **repositorios separados** o en un monorepo claro (`backend/` + `mobile/`). Si se usa monorepo, el pipeline de CI debe detectar qué parte cambió para no desplegar ambas innecesariamente. La carpeta `mobile/` no debe mezclarse con `public/` ni con `resources/`.

### 10.4 Entornos

| Entorno | Uso | URL |
|---|---|---|
| Local | Desarrollo | `http://animoji.local` |
| Staging | Pruebas con datos similares a producción | `https://staging.animoji...` |
| Producción | Usuarios reales | `https://animoji...` |

---

## 11. Modelo de datos preliminar

Entidades sugeridas (ajustar en `plan.md`):

- **Usuarios:** `users`, `profiles`, `roles`, `permissions`, `sessions`, `follows`, `blocks`.
- **Contenido:** `anime`, `seasons`, `episodes`, `video_sources`, `manga`, `chapters`, `pages`, `artists`, `albums`, `tracks`, `playlists`, `playlist_track`, `games`, `game_versions`, `game_links`.
- **Interacción:** `posts`, `comments`, `reactions` (polimórfico), `favorites`, `ratings`, `watch_history`, `reading_progress`, `listen_history`, `reports`, `notifications`.
- **Catálogo:** `genres`, `tags`, `taggables` (polimórfico), `categories`.
- **Monetización:** `plans`, `subscriptions`, `payments`, `invoices`, `coupons`, `refunds`.
- **Móvil:** `device_tokens` (push), `devices` (plataforma, versión, último acceso), `app_releases` (versión mínima y avisos), `sync_queue`/`sync_logs` si se requiere resolución de conflictos offline.
- **Sistema:** `settings`, `activity_log`, `media`, `failed_jobs`.

Reglas preliminares:
- Comentarios y reacciones polimórficos sobre anime, manga, episodios, capítulos, juegos y posts.
- Slugs únicos por tipo de contenido.
- Borrado lógico en contenido y usuarios cuando aplique.
- Índices en búsquedas frecuentes (título, slug, género, estado).
- Dinero en enteros (centavos) y moneda explícita.

Preguntas relacionadas: T-03, T-04, S-02, S-03, P-02.

---

## 12. API y contratos

- Base: `/api/v1`, respuestas JSON, autenticación Sanctum.
- Recursos: `/auth`, `/me`, `/anime`, `/manga`, `/music`, `/games`, `/posts`, `/comments`, `/notifications`, `/plans`, `/subscriptions`.
- Recursos móviles: `/auth/login`, `/auth/register`, `/auth/logout`, `/auth/forgot-password`, `/devices` (registro de push), `/me/progress` (sincronización), `/app/config` (versión mínima, features), `/app/deep-links`.
- Paginación por cursor o página; límite por defecto 20.
- Errores estandarizados: `{ "message": "...", "errors": { "campo": ["..."] }, "code": "..." }`.
- Códigos: 200, 201, 204, 400, 401, 403, 404, 409, 422, 429, 500.
- Rate limiting por usuario/IP y por token de dispositivo.
- Documentación OpenAPI generada y versionada.
- Idempotencia en pagos, sincronización de progreso y webhooks.
- La app móvil es un cliente más: no se crean endpoints exclusivos si pueden resolverse con el mismo contrato.
- Estrategia de sincronización: el cliente envía `updated_at`/versión y el servidor resuelve conflictos con "el más reciente gana" salvo reglas específicas (por ejemplo, progreso máximo alcanzado).

Preguntas relacionadas: T-08, T-09, T-16, T-20.

---

## 13. Seguridad, legal y moderación

Este proyecto maneja contenido de terceros; la legalidad es un requisito, no un extra.

- **Licencias:** publicar solo anime, manga, música y juegos con derechos, licencia o enlaces a fuentes legales. Definir política en L-01 y L-02.
- **DMCA/takedown:** proceso, responsable y plazos para retirar contenido reportado por titulares.
- **Edad:** verificación de edad o aviso para contenido sensible (L-04).
- **Términos y privacidad:** páginas legales, cookies y consentimiento (O-03).
- **Moderación:** reglas de comunidad, cola de reportes, sanciones y apelaciones (O-01, O-02).
- **Subidas:** validar tipo/tamaño, escanear archivos, servir con URLs firmadas y sin ejecución (RNF-05).
- **Pagos:** nunca almacenar datos de tarjeta; usar Stripe Elements/PayPal y webhooks firmados.
- **Admin:** 2FA, mínimo privilegio, registro de auditoría.
- **Datos:** cifrado en tránsito, secretos en variables de entorno, respaldos cifrados.
- **App móvil:** tokens en Keychain/Keystore, no incluir claves en el binario, TLS obligatorio, ofuscación básica, revocación remota de sesiones y bloqueo de versiones vulnerables.
- **Push:** no enviar datos sensibles en la carga de la notificación; solo IDs y texto genérico.
- **Tiendas:** cumplir políticas de Apple/Google, incluidas las de pagos digitales y contenido generado por usuarios (moderación, reportes y bloqueo).
- **API móvil:** límites de tasa, validación estricta de payloads, permisos por token y registro de dispositivos.

Preguntas relacionadas: L-01 a L-10, O-01 a O-10, T-16 a T-21.

---

## 14. Criterios de aceptación y Definition of Done

### 14.1 DoD global por funcionalidad

Una funcionalidad se considera terminada cuando:

- [ ] Cumple todos sus requisitos y criterios de aceptación.
- [ ] Tiene pruebas automatizadas (unitarias y/o de integración) y pasan.
- [ ] Funciona en ES/EN y en móvil/escritorio.
- [ ] Pasa revisión de código y lint/typecheck.
- [ ] No introduce errores de accesibilidad críticos.
- [ ] Tiene manejo de errores y mensajes claros.
- [ ] Está documentada (API o guía de uso) cuando aplica.
- [ ] Fue validada en staging.

### 14.2 Criterios de aceptación por módulo

| Módulo | Criterio de aceptación resumido |
|---|---|
| Auth | Registro, verificación, login, recuperación y roles funcionan; sesiones seguras |
| Anime | Crear serie/temporada/episodio, reproducir y guardar progreso |
| Manga | Crear capítulo con páginas, leer y guardar progreso |
| Música | Crear artista/álbum/pista, reproducir y crear playlist |
| Juegos | Crear ficha, enlaces válidos y descarga/compra externa |
| Social | Publicar, comentar, reaccionar, seguir y reportar |
| Admin | CRUD completo, moderación y métricas básicas |
| Pagos | Suscribir, renovar, cancelar y reflejar acceso premium |
| i18n | Cambiar idioma sin recargar y persistir preferencia |
| API | Documentada, autenticada, paginada y con errores estándar |
| App móvil | Login, catálogo, reproducción/lectura, progreso sincronizado, push y modo offline básico en Android/iOS |

---

## 15. Riesgos y supuestos

| ID | Riesgo | Impacto | Mitigación |
|---|---|---|---|
| R-01 | Contenido sin derechos de autor | Legal/alto | Política de licencias y DMCA; enlaces legales |
| R-02 | Alcance "producto completo" muy grande | Retraso | Entrega por fases con prioridades P0 |
| R-03 | Streaming de video costoso | Costo/alto | Embeds externos en fase 1; HLS después |
| R-04 | Moderación insuficiente | Reputación | Cola de reportes y reglas claras |
| R-05 | Base de datos remota mal configurada | Seguridad | SSL, allowlist, backups, mínimos privilegios |
| R-06 | Dependencia de un solo desarrollador | Continuidad | Documentación SDD y código probado |
| R-07 | Copyright de imágenes/portadas | Legal | Licencias o uso permitido; créditos |
| R-08 | Crecimiento de tráfico | Rendimiento | CDN, caché, colas y monitoreo |
| R-09 | Duplicar lógica entre web y app | Mantenibilidad | API-first y servicios compartidos; web consume los mismos servicios |
| R-10 | Rechazo en App Store/Play Store | Lanzamiento | Revisar políticas de pagos, contenido de usuarios y moderación antes de enviar |
| R-11 | Cambios de API que rompen la app | Estabilidad | Versionado `/api/v1`, OpenAPI y pruebas de contrato |
| R-12 | Costo y tiempo de mantener dos plataformas | Retraso | Fase 4 separada; PWA opcional mientras la app madura |

Supuestos:
- Se usará Laravel como framework principal.
- Se dispone de XAMPP en local y un hosting con PHP 8.2+ en producción.
- El equipo puede mantener la infraestructura o usar servicios gestionados.
- Los usuarios aceptan términos y política de privacidad.

---

## 16. Cuestionario de especificación (preguntas abiertas)

Responde por ID. Lo marcado **P0** bloquea el plan técnico. Se puede responder "no sé" o "decidir después".

### A. Negocio y alcance

- [ ] **N-01 (P0):** ¿Cuál es el objetivo principal del primer lanzamiento: lanzar rápido, demostrar el concepto o competir con plataformas existentes?
- [ ] **N-02 (P0):** ¿Quién es el público objetivo (edad, país, idioma, hábitos)?
- [ ] **N-03 (P1):** ¿El modelo de negocio será freemium, suscripción, publicidad, donaciones o mixto?
- [ ] **N-04 (P0):** ¿El contenido será propio, subido por usuarios, agregado de terceros o enlaces a fuentes externas?
- [ ] **N-05 (P0):** Se decide app nativa. ¿Se lanzará primero en Android, primero en iOS o en ambos a la vez?
- [ ] **N-06 (P1):** ¿Cuántos usuarios y visitas esperas en 6 y 12 meses?
- [ ] **N-07 (P1):** ¿Cuál es el presupuesto mensual para infraestructura y servicios?
- [ ] **N-08 (P1):** ¿Hay fecha objetivo de lanzamiento o evento?
- [ ] **N-09 (P2):** ¿Quién mantendrá el proyecto y con qué frecuencia?
- [ ] **N-10 (P1):** ¿Ya existe nombre de dominio, marca, logo y paleta de colores?

### B. Contenido y licencias

- [ ] **L-01 (P0):** ¿De dónde saldrá el contenido y qué derechos tienes para distribuirlo?
- [ ] **L-02 (P0):** ¿Se alojará el contenido en tus servidores o solo se enlazará?
- [ ] **L-03 (P0):** ¿Quién será el responsable de atender DMCA/takedown y en qué plazo?
- [ ] **L-04 (P0):** ¿Se requiere verificación de edad o advertencia para contenido adulto/sensible?
- [ ] **L-05 (P1):** ¿Habrá restricciones por país o región?
- [ ] **L-06 (P1):** ¿Se permitirá subida de contenido por usuarios? ¿Con qué revisión previa?
- [ ] **L-07 (P1):** ¿Necesitas subtítulos/doblajes oficiales o aportados por la comunidad?
- [ ] **L-08 (P1):** ¿Qué créditos y enlaces a la fuente original se mostrarán?
- [ ] **L-09 (P2):** ¿Qué política de copyright tendrán las portadas, GIFs y avatares?
- [ ] **L-10 (P2):** ¿Se almacenarán logs de accesos y descargas por requisitos legales?

### C. Anime

- [ ] **A-01 (P0):** ¿Qué campos debe tener una ficha de anime (título, sinopsis, géneros, año, estudio, estado, puntuación, trailer)?
- [ ] **A-02 (P0):** ¿Cómo se verá el video: embed de YouTube/Vimeo, reproductor externo o HLS propio?
- [ ] **A-03 (P0):** ¿Habrá temporadas y episodios, o solo películas/contenido único?
- [ ] **A-04 (P1):** ¿Necesitas subtítulos múltiples y selección de audio?
- [ ] **A-05 (P1):** ¿El historial debe sincronizarse entre web y móvil con el mismo usuario?
- [ ] **A-06 (P1):** ¿Los usuarios podrán puntuar, reseñar y crear listas?
- [ ] **A-07 (P2):** ¿Habrá calendario de estrenos y notificaciones?
- [ ] **A-08 (P1):** ¿Qué filtros y ordenamientos son imprescindibles?
- [ ] **A-09 (P2):** ¿Se permitirá descarga offline de episodios?

### D. Manga

- [ ] **M-01 (P0):** ¿Qué campos debe tener una ficha de manga (autor, dibujante, estado, demografía, géneros)?
- [ ] **M-02 (P0):** ¿Cuántas páginas por capítulo y en qué formatos (JPG, WebP, PNG)?
- [ ] **M-03 (P0):** ¿Lector vertical continuo, paginado, doble página o todos?
- [ ] **M-04 (P1):** ¿El progreso se guarda por capítulo y página exacta?
- [ ] **M-05 (P1):** ¿Comentarios por capítulo o solo por serie?
- [ ] **M-06 (P2):** ¿Habrá equipos de traducción/scans? ¿Cómo se gestionan permisos?
- [ ] **M-07 (P2):** ¿Se permite descarga offline? ¿Solo premium?
- [ ] **M-08 (P2):** ¿Protección de imágenes contra descarga casual (marca de agua, clic derecho)?

### E. Música

- [ ] **MU-01 (P1):** ¿La música será propia, con licencia o embebida de Spotify/YouTube?
- [ ] **MU-02 (P1):** ¿Se necesita reproducción en segundo plano y controles del sistema?
- [ ] **MU-03 (P1):** ¿Playlists privadas, públicas o colaborativas?
- [ ] **MU-04 (P2):** ¿Letras sincronizadas y créditos?
- [ ] **MU-05 (P2):** ¿Descarga offline?
- [ ] **MU-06 (P2):** ¿Podcasts o audiolibros además de música?
- [ ] **MU-07 (P1):** ¿Cómo se controlan derechos de las pistas?

### F. Juegos

- [ ] **J-01 (P0):** ¿Los juegos serán gratuitos, de pago o ambos?
- [ ] **J-02 (P0):** ¿Se alojarán instaladores o solo enlaces externos?
- [ ] **J-03 (P1):** ¿Qué campos necesita la ficha (requisitos, plataformas, tamaño, versión, capturas)?
- [ ] **J-04 (P1):** ¿Habrá enlaces espejo y verificación de integridad?
- [ ] **J-05 (P1):** ¿Se escanearán archivos antes de publicarlos?
- [ ] **J-06 (P2):** ¿Reseñas, puntuaciones y compatibilidad reportada por usuarios?
- [ ] **J-07 (P2):** ¿Se permitirá contenido de la comunidad (mods, fan games)?
- [ ] **J-08 (P0):** ¿Cómo se garantizará que solo se distribuyan juegos con permiso?

### G. Social

- [ ] **S-01 (P0):** ¿Qué tipos de publicación habrá: texto, imagen, GIF, video, encuesta, enlace?
- [ ] **S-02 (P0):** ¿Los comentarios serán de un nivel o con respuestas anidadas?
- [ ] **S-03 (P0):** ¿Qué reacciones existirán: solo "me gusta" o varias?
- [ ] **S-04 (P1):** ¿El feed será cronológico, por seguidos, por tendencias o mixto?
- [ ] **S-05 (P1):** ¿Habrá hashtags, menciones y búsqueda de usuarios?
- [ ] **S-06 (P2):** ¿Se necesitan mensajes directos? ¿Privados o con solicitudes?
- [ ] **S-07 (P1):** ¿Se podrán crear grupos o comunidades?
- [ ] **S-08 (P0):** ¿Qué se puede reportar y qué sanciones existen (aviso, silencio, suspensión, ban)?
- [ ] **S-09 (P1):** ¿Perfiles públicos por defecto o privados configurables?
- [ ] **S-10 (P2):** ¿Verificación de cuentas (badge) para creadores?
- [ ] **S-11 (P1):** ¿Se permite compartir publicaciones en redes externas?
- [ ] **S-12 (P2):** ¿Notificaciones push y en tiempo real?

### H. Cuentas y roles

- [ ] **U-01 (P0):** ¿Registro con email/contraseña, login social o ambos?
- [ ] **U-02 (P1):** ¿Verificación de email obligatoria para participar?
- [ ] **U-03 (P1):** ¿Qué política de contraseñas y bloqueo por intentos?
- [ ] **U-04 (P1):** ¿2FA obligatorio para admins y opcional para usuarios?
- [ ] **U-05 (P0):** ¿Qué roles exactos existirán y quién los asigna?
- [ ] **U-06 (P1):** ¿Habrá solicitud para ser creador o lo asigna un admin?
- [ ] **U-07 (P1):** ¿Cómo se gestionan bans, apelaciones y eliminación de cuentas?
- [ ] **U-08 (P1):** ¿Se requiere exportación de datos y borrado GDPR?

### I. Monetización

- [ ] **P-01 (P1):** ¿Qué será premium: contenido, sin anuncios, descargas, calidad, acceso anticipado?
- [ ] **P-02 (P1):** ¿Suscripción mensual/anual, pago único por contenido o ambos?
- [ ] **P-03 (P1):** ¿Precios y monedas iniciales?
- [ ] **P-04 (P1):** ¿Stripe, PayPal o ambos? ¿En qué países operarás?
- [ ] **P-05 (P2):** ¿Habrá reparto de ingresos a creadores?
- [ ] **P-06 (P1):** ¿Necesitas facturas con impuestos y datos fiscales?
- [ ] **P-07 (P2):** ¿Cupones, pruebas gratis y referidos?
- [ ] **P-08 (P2):** ¿Publicidad de terceros? ¿Con qué red?

### J. UI/UX e i18n

- [ ] **X-01 (P1):** ¿Tienes referencias visuales o sitios de inspiración?
- [ ] **X-02 (P1):** ¿Modo oscuro obligatorio, opcional o no?
- [ ] **X-03 (P1):** ¿Qué dispositivos y tamaños son prioritarios?
- [ ] **X-04 (P1):** ¿Qué nivel de accesibilidad se exige (WCAG AA)?
- [ ] **X-05 (P1):** ¿Vue 3 o React para Inertia? **(Recomendado: Vue 3)**
- [ ] **X-06 (P1):** ¿Tailwind, Bootstrap o librería de componentes (PrimeVue, shadcn)?
- [ ] **X-07 (P1):** ¿URLs con prefijo de idioma (`/es`, `/en`) o selector sin prefijo?
- [ ] **X-08 (P2):** ¿El contenido (títulos/sinopsis) se traducirá o solo la interfaz?
- [ ] **X-09 (P1):** ¿La app móvil tendrá diseño propio o reutilizará el sistema de diseño de la web?
- [ ] **X-10 (P1):** ¿Navegación móvil con tabs inferiores, drawer o mixta?

### K. Técnico y arquitectura

- [ ] **T-01 (P0):** ¿Versión de Laravel y PHP objetivo? **(Recomendado: Laravel 12, PHP 8.3)**
- [ ] **T-02 (P0):** ¿Se confirma Inertia + Vue/React o se prefiere Blade/Livewire?
- [ ] **T-03 (P0):** ¿MySQL gestionado o PostgreSQL gestionado en producción? ¿Proveedor?
- [ ] **T-04 (P1):** ¿Dónde se alojará: VPS, hosting compartido, Railway/Render, AWS, otro?
- [ ] **T-05 (P0):** ¿Video embebido externo o streaming propio (HLS/S3 + CDN)?
- [ ] **T-06 (P1):** ¿Almacenamiento de imágenes en disco local, S3 o Cloudflare R2?
- [ ] **T-07 (P1):** ¿Se usará Redis para colas/caché o el driver de base de datos?
- [ ] **T-08 (P0):** ¿La app móvil consumirá `/api/v1` con Sanctum?
- [ ] **T-09 (P1):** ¿Se necesita documentación OpenAPI pública o interna?
- [ ] **T-10 (P1):** ¿Búsqueda con Meilisearch, Algolia o base de datos?
- [ ] **T-11 (P2):** ¿Notificaciones en tiempo real con Reverb, Pusher o polling?
- [ ] **T-12 (P1):** ¿CI/CD con GitHub Actions y despliegue automático?
- [ ] **T-13 (P1):** ¿Se usará Docker en local y producción?
- [ ] **T-14 (P1):** ¿Monitoreo con Sentry y uptime externo?
- [ ] **T-15 (P1):** ¿Backups automáticos y plan de restauración probado?
- [ ] **T-16 (P0):** ¿Flutter o React Native/Expo para la app nativa? **(Recomendado: Flutter)**
- [ ] **T-17 (P1):** ¿Versiones mínimas y prioridad Android/iOS?
- [ ] **T-18 (P1):** ¿Push con FCM/APNs directo o un servicio como OneSignal?
- [ ] **T-19 (P1):** ¿Qué debe funcionar offline: catálogo, favoritos, progreso, descargas premium?
- [ ] **T-20 (P0):** ¿Los pagos dentro de la app usarán compras de tienda (Apple/Google) o se redirigirá a Stripe/PayPal web?
- [ ] **T-21 (P1):** ¿Ya existen cuentas de desarrollador Apple y Google Play? ¿Quién publica y firma?
- [ ] **T-22 (P1):** ¿Se usará CI/CD para compilar y distribuir betas (TestFlight/Play Internal)?
- [ ] **T-23 (P2):** ¿Se requiere análisis de crashes (Crashlytics/Sentry) y analítica móvil?
- [ ] **T-24 (P2):** ¿Deep links con dominio propio y enlaces universales?

### L. Seguridad, legal y operación

- [ ] **O-01 (P0):** ¿Qué reglas de comunidad y contenido prohibido se aplicarán?
- [ ] **O-02 (P0):** ¿Cuál será el SLA de respuesta a reportes (24 h, 72 h)?
- [ ] **O-03 (P0):** ¿Quién redacta términos, privacidad y cookies?
- [ ] **O-04 (P1):** ¿Se requiere consentimiento de cookies y analítica?
- [ ] **O-05 (P1):** ¿Qué retención de datos y logs se aplicará?
- [ ] **O-06 (P1):** ¿Se escanearán archivos subidos (antivirus)?
- [ ] **O-07 (P1):** ¿Rate limiting y protección anti-bot (CAPTCHA)?
- [ ] **O-08 (P1):** ¿Auditoría de acciones administrativas?
- [ ] **O-09 (P2):** ¿Plan de respuesta a incidentes y contacto de seguridad?
- [ ] **O-10 (P1):** ¿Cumplimiento fiscal para pagos y facturación?

### M. Métricas y validación

- [ ] **V-01 (P1):** ¿Qué KPIs definen el éxito del primer año?
- [ ] **V-02 (P1):** ¿Qué herramienta de analítica (GA4, Plausible, Umami)?
- [ ] **V-03 (P2):** ¿Se harán pruebas A/B?
- [ ] **V-04 (P1):** ¿Cómo se recogerá feedback (formulario, Discord, email)?
- [ ] **V-05 (P1):** ¿Cuál es el criterio para pasar de staging a producción?
- [ ] **V-06 (P1):** ¿Quiénes serán los beta testers?
- [ ] **V-07 (P2):** ¿Se medirá retención por cohortes?
- [ ] **V-08 (P2):** ¿Qué funcionalidad se eliminará si no alcanza su métrica?

---

## 17. Próximos pasos

1. Responder las preguntas **P0**: N-01, N-02, N-04, N-05, L-01, L-02, L-03, L-04, A-01, A-02, A-03, M-01, M-02, M-03, J-01, J-02, J-08, S-01, S-02, S-03, S-08, U-01, U-05, T-01, T-02, T-03, T-05, T-08, T-16, T-20, O-01, O-02, O-03.
2. Con esas respuestas, generar `docs/plan.md` con arquitectura final, modelo de datos y contratos.
3. Generar `docs/tasks.md` con tareas ordenadas por fase y sus pruebas.
4. Implementar la Fase 1 y validar sus criterios de aceptación.
5. Revisar este SDD al cerrar cada fase (control de cambios y versión).

---

## 18. Registro de cambios

| Versión | Fecha | Cambio |
|---|---|---|
| 0.1 | 2026-10-22 | Borrador inicial con decisiones del kickoff y cuestionario |
| 0.2 | 2026-10-22 | Decisión API + app nativa (Flutter/React Native), requisitos móviles, arquitectura API-first, riesgos y preguntas T-16 a T-24 |
