# 🔍 AUDITORÍA COMPLETA DEL SISTEMA — **ANIMOJI**

> Documento actualizado el **25 de octubre de 2026** con el estado de la migración a Blade.
> Contiene: arquitectura, rutas, diseño, carpetas de imágenes (con la ubicación exacta de cada una), roles, personalización e infraestructura.

---

## 1. 🧰 Stack tecnológico

| Capa | Tecnología |
|---|---|
| Backend | **Laravel 13** (PHP 8.3+) con Breeze |
| Frontend | **Laravel Blade** + **Tailwind CSS** + JavaScript nativo |
| Base de datos | **MariaDB/MySQL** (XAMPP), sesión en BD |
| Roles | **spatie/laravel-permission** |
| API | **Laravel Sanctum** (API REST v1) |
| Servidor | **Apache de XAMPP** en el puerto **8082** |
| Build | Vite (`npm run build`) |

Las rutas y pantallas activas usan Blade. La migración eliminó los componentes y dependencias heredadas de Vue/Inertia; JavaScript se limita a interacciones puntuales dentro de las vistas Blade.

**Configuración clave (`.env`):**
- `APP_NAME=ANIMOJI`
- `APP_URL=http://animoji.test:8082`
- `DB_DATABASE=animoji` (la base `bd-animoji` ya **no se usa**)
- `SESSION_DRIVER=database`

---

## 2. 🌐 Infraestructura y puertos (XAMPP)

- Apache escucha en los puertos **80, 8081 y 8082** (`httpd.conf`).
- **ANIMOJI usa el 8082** mediante un VirtualHost en `C:/xampp/apache/conf/extra/httpd-vhosts.conf`:

```apache
<VirtualHost *:8082>
    DocumentRoot "C:/xampp/htdocs/Nueva carpeta/ANIMOJI/public"
    ServerName animoji.test
    ServerAlias animoji.local localhost
</VirtualHost>
```

- El proyecto **sigejub 2** usa el 8081, así que **no chocan de puertos**.
- URLs válidas: `http://animoji.test:8082`, `http://animoji.local:8082` o `http://localhost:8082`.
- Apache y MySQL están configurados para **arrancar con Windows** (servicios `Apache2.4` y `mysql`).

---

## 3. 🗄️ Base de datos `animoji`

### Tablas principales

| Tabla | Contenido |
|---|---|
| `users` | Usuarios. Columnas propias: `index_theme` (tema de color del index), `customizing` (modo personalización activo) |
| `animes`, `mangas`, `music_tracks`, `games` | Catálogos de cada apartado (título, slug, descripción, portada, año, rating…) |
| `posts`, `comments`, `favorites`, `media_progress`, `reports` | Comunidad: publicaciones, comentarios, favoritos, progreso y reportes |
| `system_images` | Imágenes personalizadas por "zona" del sistema (modo personalización) |
| `route_overrides` | Modificador de rutas ("Direcciones"): clave de botón → destino elegido |
| `roles`, `permissions`, `model_has_roles` | Sistema de roles (spatie) |
| `sessions`, `cache` | Sesiones y caché en BD |

### Usuarios y roles actuales

| Email | Rol |
|---|---|
| admin@animoji.local | **admin** + **superadmin** |
| jesus17otaku@gmail.com (Lobo118) | **admin** + **superadmin** |

### Roles disponibles en BD

`admin`, `superadmin`, `editor`, `moderator`, `support`, `creator`, `user`

- Los usuarios **existentes** son superadmin (todos los permisos).
- Quien se **registra a partir de ahora** recibe el rol `user` (asignado en `RegisteredUserController`).

---

## 4. 🧭 Rutas del sistema

### Rutas web (`routes/web.php`)

| URL | Controlador / Página | Acceso | Nombre |
|---|---|---|---|
| `/` | HomeController → `resources/views/home.blade.php` | público | `home` |
| `/anime` | CatalogController → `resources/views/catalog/index.blade.php` | público | `anime.index` |
| `/anime/{slug}` | Ficha compartida → `resources/views/catalog/show.blade.php` | público | `anime.show` |
| `/anime/{slug}/ver/{episode}` | Reproductor → `resources/views/watch/anime.blade.php` | público | `anime.watch` |
| `/manga` | CatalogController → `resources/views/catalog/index.blade.php` | público | `manga.index` |
| `/manga/{slug}` | Ficha compartida → `resources/views/catalog/show.blade.php` | público | `manga.show` |
| `/manga/{slug}/leer/{chapter}` | Lector → `resources/views/watch/manga.blade.php` | público | `manga.read` |
| `/musica` | CatalogController → `resources/views/catalog/index.blade.php` | público | `music.index` |
| `/musica/{slug}` | Ficha compartida → `resources/views/catalog/show.blade.php` | público | `music.show` |
| `/juegos` | CatalogController → `resources/views/catalog/index.blade.php` | público | `games.index` |
| `/juegos/{slug}` | Ficha compartida → `resources/views/catalog/show.blade.php` | público | `games.show` |
| `/dashboard` | Dashboard → `resources/views/dashboard.blade.php` | auth + verificado | `dashboard` |
| `/coleccion` | Coleccion → `resources/views/coleccion.blade.php` | auth + verificado | `coleccion` |
| `/admin` | Admin → `resources/views/admin/dashboard.blade.php` | auth + rol **admin** | `admin.dashboard` |
| `/admin/galeria` | `resources/views/admin/galeria.blade.php` | auth + rol admin | `admin.galeria` |
| `/admin/personalizacion` | `resources/views/admin/personalizacion.blade.php` | auth + rol admin | `admin.customizations.index` |
| `/admin/personalizacion/toggle` | Activa/desactiva el modo personalización | admin | `customizations.toggle` |
| `/admin/personalizacion/galeria` | Devuelve las imágenes de la galería (JSON) | admin | `customizations.gallery` |
| `/admin/personalizacion/zona/{zone}` | Guarda la imagen de una zona | admin | `customizations.update` |
| `/admin/personalizacion/direcciones/{key}` | Cambia el destino de un botón | admin | `direcciones.update` |
| `/profile` | Cuenta de usuario (`resources/views/profile/edit.blade.php`) | auth | `profile.edit` |
| `/profile` (PATCH) | Actualiza perfil | auth | `profile.update` |
| `/profile/theme` | Guarda el tema de color del index | auth | `profile.theme` |
| `/login`, `/register`, logout, password… | Breeze (`routes/auth.php`) | público | — |

### API REST (`routes/api.php`, prefijo `/api/v1`)

- `GET /api/v1/health` — estado del servicio.
- Catálogos: `/anime`, `/manga`, `/music`, `/games` (+ detalle por slug).
- Auth: `POST /auth/register`, `POST /auth/login`, `GET /me`, `POST /auth/logout` (Sanctum).
- Progreso: `GET|POST /me/progress` (Sanctum).

---

## 5. 🎨 Diseño y estructura de páginas

### 5.1 Index (`/`) — `resources/views/home.blade.php`

- **Fondo de TODO el index**: `/images/index/hero-bg.jpg` — cubre toda la página detrás de todas las secciones.
- **Personaje del hero**: `/images/index/hero-bg1.jpg` — PNG transparente superpuesto sobre el fondo, en la misma posición (zona superior). Es una **zona personalizable** (`home.hero`).
- Secciones, de arriba a abajo:
  1. **Header** (`resources/views/layouts/public.blade.php`): logo, Inicio, Explorar, Mi Biblioteca, Buscar. Sesión iniciada → círculo de usuario + desplegable; sin sesión → botones **Iniciar sesión** / **Registrarme**.
  2. **Hero**: "BIENVENIDO A ANIMOJI" + botones *Inicio de Sección*, *Mi Lista*, *Recientes*.
  3. **4 tarjetas de categorías**: ANIME (celeste), MANGA (rojo), MÚSICA (morado), VIDEOJUEGOS (turquesa) — cada una con fondo propio.
  4. **Carrusel "7 CAPITALES"** con 7 portadas verticales (`destacado-1..7.jpg`).
  5. Banner "Explorar ahora" y pie de página con enlaces a los 4 catálogos.
- **Temas de color del index**: si el usuario tiene `index_theme`, Home carga todas las imágenes de `/images/index/<color>/` en lugar de `/images/index/`. Se elige en **Cuenta → Personalización**.

### 5.2 Login / Registro (`/login`, `/register`)

- Vistas: `resources/views/auth/`. **Cada visita rota el diseño**: rojo → morado → azul → aquamarine → rosado → azul oscuro → blanco → vinotinto (guardado en sesión, `login_theme_idx`).
- Cada tema aporta **fondo** (`fondo X.png` o `bg.jpg`) + **personaje** (`X.png`) + acento de color.
- El logo del color se toma de `/images/logo/logo-<color>.png` si existe.
- Credenciales de prueba: admin@animoji.local.

### 5.3 Catálogos (Anime, Manga, Música, Videojuegos)

- Catálogos y pantallas personales usan layouts Blade en `resources/views/layouts/`.
- Temas: anime = rojo/rosa, manga = monocromo B/N, música = azul/púrpura, **juegos = turquesa gamer**.
- El índice compartido `resources/views/catalog/index.blade.php` muestra hero, tarjetas, recomendados y panel lateral.
- `section` y `theme` se pasan al layout (`'anime'|'manga'|'musica'|'juegos'` y `'red'|'mono'|'blue'|'teal'`).

### 5.4 Panel de usuario (`/dashboard`, `/coleccion`)

- Vistas: `resources/views/dashboard.blade.php`, `resources/views/coleccion.blade.php` y `resources/views/personal/`. Los menús están en sus layouts Blade.
- Zona inferior del sidebar con decoración + cita (personalizable).

### 5.5 Panel administrativo (`/admin`)

- Layout: `resources/views/layouts/admin.blade.php`. Menú lateral: inicio, **Personalización**, Biblioteca, Usuarios y Galería.
- Páginas: `resources/views/admin/` (panel, biblioteca, galería, personalización y usuarios).

### 5.6 Cuenta de usuario (`/profile`)

- `resources/views/profile/edit.blade.php`: datos personales, cambiar contraseña, eliminar cuenta y preferencias.

---

## 6. 🖼️ Sistema de imágenes (`public/images/`) — GUÍA COMPLETA

Cada apartado tiene su **carpeta propia** con un `README.md` que explica dónde va cada archivo y sus tamaños recomendados.

### 6.1 `index/` — Página de inicio (24 archivos)

| Archivo | Dónde va en la pestaña Inicio |
|---|---|
| `hero-bg.jpg` | Fondo de **toda** la página (detrás de todas las secciones) |
| `hero-bg1.jpg` | Personaje del banner (PNG transparente, encima del fondo) |
| `logo.png` | Arriba a la izquierda en el header |
| `avatar.png` | Círculo de usuario en el header |
| `anime.jpg` / `manga.jpg` / `musica.jpg` / `juegos.jpg` | Fondo de cada tarjeta de categoría |
| `destacado-1.jpg` … `destacado-7.jpg` | Portadas del carrusel "7 CAPITALES" |
| **Subcarpetas de color** (`rojo/`, `morado/`, `azul/`, `aquamarine/`, `rosado/`, `azul oscuro/`, `vinotinto/`) | Versión de TODAS estas imágenes en ese color; se usan si el usuario eligió ese tema. Contienen: hero-bg, hero-bg1, anime, manga, musica, juegos y destacado-1..7 |

### 6.2 `login/` — Diseños de inicio de sesión/registro (8 temas)

Cada subcarpeta = un diseño con **fondo + personaje**. Se rotan solos en cada visita a `/login` o `/register`:

| Carpeta | Fondo | Personaje |
|---|---|---|
| `rojo/` | `bg.jpg` | `character.png` (+ `bg1.jpg`, `character1.jpg`, `logo.png`) |
| `morado/` | `fondo morado.png` | `morado.png` |
| `azul/` | `fondo azul.png` | `azul.png` |
| `aquamarine/` | `fondo aquamarine.png` | `aquamarine.png` |
| `rosado/` | `fondo rosa.png` | `rosa.png` |
| `azul oscuro/` | `fondo azul oscuro.png` | `azul oscuro.png` |
| `blanco/` | `fondo blanco.png` | `blanco.png` |
| `vinotinto/` | `fondo vinotinto.png` | `vinotinto.png` |

### 6.3 `logo/` — Logos por color (9 archivos)

`logo-rojo.png`, `logo-morado.png`, `logo-azul.png`, `logo-aquamarine.png`, `logo-rosado.png`, `logo-azul oscuro.png`, `logo-blanco.png`, `logo-vinotinto.png` + `Gato cibernético dorado con auriculares.png`. El sistema elige automáticamente el logo del color del tema activo.

### 6.4 `anime/`, `manga/`, `musica/`, `juegos/` — Catálogos

Imágenes de prueba generadas por `scripts/generate_catalog_placeholders.php`:

| Archivo | Dónde va (en cada pestaña de catálogo) |
|---|---|
| `hero.jpg` | Fondo del banner principal |
| `cover-1..8.jpg` | Portadas del carrusel de destacados |
| `reco-1..5.jpg` | Miniaturas de "Recomendados para ti" |
| `sidebar.jpg` | Decoración del menú lateral (parte baja) |
| `logo.png` | Icono del apartado |
| *Música además*: `playlist-1..5.jpg`, `player-cover.png` | Lista del reproductor |
| *Juegos además*: `genre-1..5.png` (iconos de géneros), `status-cover.jpg` (panel "Tu partida") | Chips de género y carátula del jugador |

### 6.5 `usuario/` — Panel de usuario (22 archivos)

`sidebar.jpg`, `sidebar1.jpg`, `hero-coleccion1.jpg`, `destacado.jpg`, `cover-1..5.jpg`, `recent-1..4.jpg`, `cat-anime.jpg`, `cat-manga.jpg`, `cat-musica.jpg`, `cat-juegos.jpg`, `tile-anime.jpg`, `tile-manga.jpg`, `tile-musica.jpg`, `tile-juegos.jpg` + `Mirada Carmesí entre Sombras.png`.

### 6.6 `cuenta/` — Cuenta de usuario (4 archivos)

`avatar.png`, `banner.jpg`, `promo.jpg`, `sidebar.jpg`.

### 6.7 `admin/` — Panel administrativo (4 archivos)

`banner.jpg`, `sidebar.jpg`, `card-anime.jpg`, `card-manga.jpg`, `card-musica.jpg`.

### 6.8 `galeria/` — Galería del sistema (148 archivos)

Imágenes subidas desde **Administración → Imágenes**. También las usa el modo de personalización para elegir imágenes de zonas.

### 6.9 `carga/` — Pantalla de carga

`bg.jpg`: fondo de la pantalla de carga; su acento de color lo da el "color de pantalla de carga" que el usuario elige en Cuenta → Personalización.

### 📌 Regla general para reemplazar imágenes
> Reemplaza el archivo **con el mismo nombre y formato** en la misma carpeta y recarga con `Ctrl + F5`. Las carpetas se regeneran con los scripts de `scripts/` (`generate_home_placeholders.php`, `generate_login_placeholders.php`, `generate_catalog_placeholders.php`, `generate_index_theme_placeholders.php`, `generate_logo_placeholders.php`, `generate_panel_placeholders.php`).

---

## 7. 🎛️ Personalización de sistema (Administración)

Activable desde **Administración → Personalización → "Personalizar imágenes de sistema"** (botón toggle que enciende `users.customizing`). Mientras está activo, cada **zona de imagen** muestra un botón **"+ Agregar imagen"** que:

1. Abre la **galería del sistema** (con filtro por carpeta).
2. Permite **ajustar la imagen** (posición X/Y arrastrando y escala).
3. Guarda en la tabla `system_images` (zona → imagen + ajustes).

**Zonas disponibles hoy:**

| Zona | Ubicación |
|---|---|
| `admin.sidebar` | Menú lateral del panel administrativo |
| `usuario.sidebar` | Menú lateral del panel de usuario |
| `cuenta.sidebar` | Menú lateral de la cuenta |
| `home.hero` | Personaje del hero del index (`hero-bg1`) |

Las zonas personalizables se renderizan desde las vistas Blade de inicio y administración.

---

## 8. 📍 Direcciones (modificador de rutas)

En **Administración → Personalización → "Direcciones"** se listan todos los botones/enlaces navegables agrupados por sección, y el admin puede **reasignar su destino** (se guarda en `route_overrides` y las vistas Blade lo leen mediante `Navigation::map()`).

**Secciones:** `header` (cabecera), `index` (página de inicio), `admin` (menú administrativo), `usuario` (menú de usuario).

**Botones registrados (28)** — por ejemplo:

- Cabecera: logo, Inicio, Explorar, Mi Biblioteca, Buscar, Iniciar sesión, Registrarme.
- Index: botones del hero (Inicio de Sección, Mi Lista, Recientes), las 4 tarjetas de categoría, banner "Explorar ahora", pie de página.
- Menú admin: Inicio, Personalización, Anime, Manga, Música, Videojuegos, Mi colección, Imágenes.
- Menú usuario: Inicio, Anime, Música, Manga, Videojuegos, Mi colección, Configuración.

**Destinos posibles:** Index, login, register, dashboard, colección, perfil, los 4 catálogos, panel admin, galería y personalización. Si no hay personalización, se usa el destino por defecto.

---

## 9. 🔐 Autenticación y roles

- Breeze (sesión) + verificación de email.
- Tras iniciar sesión se redirige al **index (`/`)**, con el header mostrando el círculo de usuario y el desplegable con: **Mi cuenta (perfil), Mi colección, Mi panel, Administrar (solo admin), Imágenes (solo admin), Cerrar sesión**.
- Las páginas web activas se renderizan en el servidor con Laravel Blade; Inertia ya no forma parte de las dependencias activas.
- Rutas `/admin/*` protegidas con `role:admin`.

---

## 10. 🧪 Calidad

- **140 tests** de Laravel — todos en verde (476 assertions) en la última ejecución.
- Build Vite sin errores.
- Páginas verificadas en navegador: login (rotación de temas), registro, index, los 4 catálogos, panel de usuario, panel admin, galería y personalización.

---

## 11. 🗃️ Estructura de archivos clave

```
resources/views/
├── home.blade.php                 ← portada
├── catalog/
│   ├── index.blade.php            ← índices de anime, manga, música y juegos
│   └── show.blade.php             ← fichas compartidas del catálogo
├── watch/
│   ├── anime.blade.php            ← reproductor de episodios
│   └── manga.blade.php            ← lector de capítulos
├── personal/                      ← favoritos, listas, pendientes e historial
├── admin/                          ← panel, biblioteca, galería y usuarios
├── auth/                           ← inicio de sesión y recuperación de cuenta
├── profile/                        ← perfil
├── layouts/                        ← menús y estructuras compartidas
└── partials/music-player.blade.php ← reproductor persistente de música

app/
├── Http/Controllers/
│   ├── Web/{HomeController,CatalogController,DashboardController,ColeccionController}.php
│   ├── Web/Admin/{DashboardController,CustomizationController}.php
│   ├── Api/V1/{AuthController,CatalogController,ProgressController}.php
│   └── Auth/… , ProfileController.php
├── Models/ (User, Anime, Manga, MusicTrack, Game, Comment, Report, MediaProgress, RouteOverride, SystemImage…)
├── Support/{Themes.php,Navigation.php}
└── Support/Navigation.php

scripts/  ← generadores de imágenes de prueba
public/images/  ← TODAS las carpetas de imágenes del sistema (ver sección 6)
```

---

*Fin de la auditoría. Cualquier cambio de diseño, rutas o imágenes debe reflejarse también en las secciones 4, 5 y 6 de este documento.*
