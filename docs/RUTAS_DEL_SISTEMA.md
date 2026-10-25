# Rutas y navegación del sistema ANIMOJI

Este documento describe las rutas web actuales, dónde se definen y los archivos que
dibujan sus enlaces. Las referencias a métodos y nombres de ruta son más estables
que los números de línea, que pueden cambiar al editar los archivos.

## 1. Archivos principales

| Archivo | Qué contiene |
|---|---|
| [`routes/web.php`](../routes/web.php) | Rutas de páginas, catálogos, paneles, colecciones y administración. |
| [`routes/auth.php`](../routes/auth.php) | Registro, inicio/cierre de sesión, recuperación de contraseña y verificación. |
| [`app/Support/Navigation.php`](../app/Support/Navigation.php) | Catálogo central de destinos (`destinations()`), secciones (`sections()`), enlaces configurables (`items()`) y lectura de cambios guardados (`map()`). |
| [`app/Http/Controllers/Web/Admin/CustomizationController.php`](../app/Http/Controllers/Web/Admin/CustomizationController.php) | Muestra Personalización y guarda/restaura las direcciones mediante `updateRoute()`. |
| [`resources/views/admin/personalizacion.blade.php`](../resources/views/admin/personalizacion.blade.php) | Pestaña Direcciones, selectores, guardado y formulario separado para restaurar. |
| [`resources/views/layouts/public.blade.php`](../resources/views/layouts/public.blade.php) | Cabecera pública, navegación móvil y pie de página. |
| [`resources/views/home.blade.php`](../resources/views/home.blade.php) | Enlaces destacados y tarjetas de categorías del inicio. |
| [`resources/views/layouts/user.blade.php`](../resources/views/layouts/user.blade.php) | Menú lateral autenticado. |
| [`resources/views/layouts/admin.blade.php`](../resources/views/layouts/admin.blade.php) | Menú lateral del superadministrador. |

## 2. Páginas y rutas web

| Página o acción | Método y URL | Nombre de ruta | Implementación |
|---|---|---|---|
| Inicio público | `GET /` | `home` | `HomeController` → `resources/views/home.blade.php` |
| Registro | `GET /register`, `POST /register` | `register` | `RegisteredUserController` → `auth.register`; solo invitados |
| Inicio de sesión | `GET /login`, `POST /login` | `login` | `AuthenticatedSessionController` → `auth.login`; solo invitados |
| Recuperar contraseña | `GET/POST /forgot-password` | `password.request`, `password.email` | Controlador `PasswordResetLinkController` |
| Restablecer contraseña | `GET /reset-password/{token}`, `POST /reset-password` | `password.reset`, `password.store` | Controlador `NewPasswordController` |
| Anime | `GET /anime`, `GET /anime/{anime:slug}` | `anime.index`, `anime.show` | `CatalogController` → `catalog.index`, `catalog.show` |
| Ver episodio | `GET /anime/{anime:slug}/ver/{episode}` | `anime.watch` | `WatchController` |
| Manga | `GET /manga`, `GET /manga/{manga:slug}` | `manga.index`, `manga.show` | `CatalogController` → `catalog.index`, `catalog.show` |
| Leer capítulo | `GET /manga/{manga:slug}/leer/{chapter}` | `manga.read` | `WatchController` |
| Música | `GET /musica`, `GET /musica/{track:slug}` | `music.index`, `music.show` | `CatalogController` → `catalog.index`, `catalog.show` |
| Videojuegos | `GET /juegos`, `GET /juegos/{game:slug}` | `games.index`, `games.show` | `CatalogController` → `catalog.index`, `catalog.show` |
| Inicio autenticado | `GET /dashboard` | `dashboard` | Requiere sesión y correo verificado; `DashboardController` → `dashboard` |
| Mi colección | `GET /coleccion` | `coleccion` | Requiere sesión y correo verificado; `ColeccionController` → `coleccion` |
| Cuenta de usuario | `GET/PATCH /profile`, acciones de avatar/tema y eliminación | `profile.edit`, `profile.update`, `profile.avatar`, `profile.theme`, `profile.destroy` | Requiere sesión; `ProfileController` → `profile.edit` |
| Cerrar sesión | `POST /logout` | `logout` | Requiere sesión; `AuthenticatedSessionController::destroy` invalida la sesión |

Los cuatro catálogos (`/anime`, `/manga`, `/musica`, `/juegos`) son páginas públicas.
La sesión no es necesaria para explorarlos.

### Organización personal

`routes/web.php` registra las rutas de favoritos, listas, pendientes e historial para
cada sección declarada en `App\Support\Sections::KEYS`. Las páginas requieren sesión
y la vista correspondiente se renderiza desde `PersonalController`:

| Función | URL | Nombre de ruta |
|---|---|---|
| Favoritos por sección | `GET /{seccion}/favoritos` | `{seccion}.favoritos` |
| Listas por sección | `GET/POST /{seccion}/listas` | `{seccion}.listas`, `{seccion}.listas.store` |
| Pendientes por sección | `GET /{seccion}/pendientes` | `{seccion}.pendientes` |
| Historial/seguir por sección | `GET /{seccion}/seguir` | `{seccion}.seguir` |
| Cambiar favoritos/pendientes y progreso | `POST /personal/favoritos`, `/personal/pendientes`, `/personal/progreso` | `personal.favoritos.toggle`, `personal.pendientes.toggle`, `personal.progreso` |
| Buscar elementos personales | `GET /personal/buscar` | `personal.buscar` |

Las rutas de listas también incluyen `PUT/DELETE /listas/{list}` y
`POST/DELETE /listas/{list}/items/{item}`; la propiedad de las listas se verifica
en el servidor.

### API versión 1

Las rutas de API están en [`routes/api.php`](../routes/api.php), bajo el prefijo
`/api/v1`. Sus catálogos y detalles son públicos; registro e inicio de sesión tienen
límite de solicitudes. `GET /api/v1/me`, cierre de sesión y sincronización/consulta
de progreso requieren `auth:sanctum`.

| Función | Métodos y URLs | Controlador |
|---|---|---|
| Estado del servicio | `GET /api/v1/health` | Respuesta JSON definida en `routes/api.php` |
| Catálogos y detalles | `GET /api/v1/anime[/{anime}]`, `/manga[/{manga}]`, `/music[/{track}]`, `/games[/{game}]` | `Api\V1\CatalogController` |
| Registro e inicio de sesión | `POST /api/v1/auth/register`, `/api/v1/auth/login` | `Api\V1\AuthController` |
| Usuario y cierre de sesión | `GET /api/v1/me`, `POST /api/v1/auth/logout` | `Api\V1\AuthController` |
| Progreso | `GET/POST /api/v1/me/progress` | `Api\V1\ProgressController` |

### Administración

Todas las rutas con prefijo `/admin` requieren sesión, correo verificado, rol
`superadmin` y protección contra caché del navegador. La definición del grupo está
en `routes/web.php`.

| Sección | Rutas principales | Implementación |
|---|---|---|
| Panel | `GET /admin` (`admin.dashboard`) | `Admin\DashboardController` → `admin.dashboard` |
| Biblioteca | `GET/POST /admin/biblioteca/{section}`, `PUT/DELETE /admin/biblioteca/{section}/{id}` | `Admin\LibraryController` → `admin.biblioteca` |
| Episodios | `/admin/biblioteca/anime/{anime}/episodios`, `/admin/episodios/{episode}` | `Admin\ContentItemsController` |
| Capítulos y páginas | `/admin/biblioteca/manga/{manga}/capitulos`, `/admin/capitulos/{chapter}`, `/admin/capitulos/{chapter}/paginas`, `/admin/paginas/{page}` | `Admin\ContentItemsController` |
| Galería | `GET/POST /admin/galeria`, `DELETE /admin/galeria/{file}` | `Admin\GalleryController` → `admin.galeria` |
| Usuarios | `GET /admin/usuarios`, `PUT /admin/usuarios/{user}`, `PUT /admin/usuarios/{user}/roles`, `DELETE /admin/usuarios/{user}` | `Admin\UsuariosController` → `admin.usuarios` |
| Personalización | `GET /admin/personalizacion` y operaciones de imágenes | `Admin\CustomizationController` → `admin.personalizacion` |
| Direcciones de enlaces | `PUT /admin/personalizacion/direcciones/{key}` | `CustomizationController::updateRoute` |

La Galería de administración y Administración están destinadas al superadministrador;
ocultar un enlace no sustituye la autorización de servidor.

## 3. Menús y enlaces configurables

Las claves y los destinos predeterminados se declaran en
`Navigation::items()` de [`app/Support/Navigation.php`](../app/Support/Navigation.php).
La ubicación indicada es el Blade que representa el enlace.

| Clave de Dirección | Elemento visible | Archivo del enlace | Destino predeterminado |
|---|---|---|---|
| `header.logo` | Logo ANIMOJI | `resources/views/layouts/public.blade.php` | `/` |
| `header.inicio` | Inicio (escritorio y móvil) | `resources/views/layouts/public.blade.php` | `/` |
| `header.explorar` | Explorar (escritorio y móvil) | `resources/views/layouts/public.blade.php` | `/anime` |
| `header.mi_biblioteca` | Mi Biblioteca (escritorio y móvil) | `resources/views/layouts/public.blade.php` | `/dashboard` |
| `header.buscar` | Buscar (escritorio y móvil) | `resources/views/layouts/public.blade.php` | `/anime` |
| `header.iniciar_sesion` | Iniciar sesión para invitados | `resources/views/layouts/public.blade.php` | `/login` |
| `header.registrarme` | Registrarme para invitados | `resources/views/layouts/public.blade.php` | `/register` |
| `index.iniciar_sesion` | Botón principal del hero | `resources/views/home.blade.php` | `/login` |
| `index.mi_lista` | Mi Lista | `resources/views/home.blade.php` | `/dashboard` (el panel protegido conduce al login si el visitante no tiene sesión) |
| `index.recientes` | Recientes | `resources/views/home.blade.php` | `/anime` |
| `index.categoria_anime` | Tarjeta Anime | `resources/views/home.blade.php` | `/anime` |
| `index.categoria_manga` | Tarjeta Manga | `resources/views/home.blade.php` | `/manga` |
| `index.categoria_musica` | Tarjeta Música | `resources/views/home.blade.php` | `/musica` |
| `index.categoria_juegos` | Tarjeta Videojuegos | `resources/views/home.blade.php` | `/juegos` |
| `index.banner_explorar` | Banner Explorar ahora | `resources/views/home.blade.php` | `/anime` |
| `footer.anime` | Pie → Anime | `resources/views/layouts/public.blade.php` | `/anime` |
| `footer.manga` | Pie → Manga | `resources/views/layouts/public.blade.php` | `/manga` |
| `footer.musica` | Pie → Música | `resources/views/layouts/public.blade.php` | `/musica` |
| `footer.juegos` | Pie → Videojuegos | `resources/views/layouts/public.blade.php` | `/juegos` |
| `admin.menu.inicio` | Menú admin → Inicio | `resources/views/layouts/admin.blade.php` | `/admin` |
| `admin.menu.biblioteca` | Menú admin → Biblioteca | `resources/views/layouts/admin.blade.php` | `/admin/biblioteca` |
| `admin.menu.personalizacion` | Menú admin → Personalización | `resources/views/layouts/admin.blade.php` | `/admin/personalizacion` |
| `admin.menu.usuarios` | Menú admin → Usuarios | `resources/views/layouts/admin.blade.php` | `/admin/usuarios` |
| `admin.menu.anime` | Biblioteca admin → Anime | `resources/views/layouts/admin.blade.php` | `/anime` |
| `admin.menu.manga` | Biblioteca admin → Manga | `resources/views/layouts/admin.blade.php` | `/manga` |
| `admin.menu.musica` | Biblioteca admin → Música | `resources/views/layouts/admin.blade.php` | `/musica` |
| `admin.menu.juegos` | Biblioteca admin → Videojuegos | `resources/views/layouts/admin.blade.php` | `/juegos` |
| `admin.menu.coleccion` | Menú admin → Mi colección y filtros | `resources/views/layouts/admin.blade.php` | `/coleccion` |
| `admin.menu.galeria` | Menú admin → Imágenes | `resources/views/layouts/admin.blade.php` | `/admin/galeria` |
| `usuario.menu.inicio` | Menú usuario → Inicio | `resources/views/layouts/user.blade.php` | `/dashboard` |
| `usuario.menu.anime` | Menú usuario → Anime | `resources/views/layouts/user.blade.php` | `/anime` |
| `usuario.menu.musica` | Menú usuario → Música | `resources/views/layouts/user.blade.php` | `/musica` |
| `usuario.menu.manga` | Menú usuario → Manga | `resources/views/layouts/user.blade.php` | `/manga` |
| `usuario.menu.juegos` | Menú usuario → Videojuegos | `resources/views/layouts/user.blade.php` | `/juegos` |
| `usuario.menu.coleccion` | Menú usuario → Favoritos/colección | `resources/views/layouts/user.blade.php` | `/coleccion` |
| `usuario.menu.config` | Menú usuario → Configuración | `resources/views/layouts/user.blade.php` | `/profile` |

### Enlaces que no aparecen en «Direcciones»

No todos los enlaces del sistema son personalizables. Estos enlaces se definen
directamente en sus vistas y conservan su ruta fija:

| Elemento | Destino | Ubicación |
|---|---|---|
| Menú de cuenta: perfil y colección | `/profile`, `/coleccion` | `resources/views/layouts/public.blade.php`, `resources/views/layouts/user.blade.php`, `resources/views/layouts/admin.blade.php` y `resources/views/layouts/catalog.blade.php` |
| Menú de superadministración: Administración y Galería | `/admin`, `/admin/galeria` | `resources/views/layouts/public.blade.php` y `resources/views/layouts/user.blade.php` |
| Submenú de Galería: Videos y Favoritos | `/admin/galeria?tipo=videos`, `/admin/galeria?tipo=favoritos` | `resources/views/layouts/admin.blade.php` |
| Tarjetas del carrusel «7 CAPITALES» | `/anime` | `resources/views/home.blade.php` |
| Cerrar sesión | `POST /logout` | Formularios del menú de cuenta y pie público; acción definida en `routes/auth.php` |

Estos enlaces fijos no tienen una clave en `Navigation::items()` y, por tanto, no
se pueden cambiar desde Personalización → Direcciones. «Ninguna dirección» se
aplica a las opciones configurables descritas en la tabla anterior.

## 4. Opción «Ninguna dirección»

En **Administración → Personalización → Direcciones**, cada enlace de la tabla
anterior tiene un selector de destino:

- Elegir un destino y guardar cambia ese enlace.
- **Ninguna dirección — deja el elemento sin enlace** guarda el valor interno
  `__none__` y el Blade correspondiente omite por completo el atributo `href`.
  Así, el elemento no navega a la página actual ni a su destino anterior.
- **Restaurar por defecto** elimina la personalización y vuelve al destino definido
  en `Navigation::items()`.

El formulario vacío se reserva para restaurar el predeterminado; no equivale a
«Ninguna dirección». Los destinos se validan contra `Navigation::destinations()`;
no se aceptan URLs arbitrarias. Esta opción solo modifica el enlace de navegación:
no elimina la ruta ni cambia su middleware o permisos.

## 5. Sesión y acceso directo

Los enlaces son una ayuda de navegación, no una medida de seguridad. Las páginas
privadas verifican sesión y permisos en middleware/controladores en cada petición.
Después de cerrar sesión, las páginas autenticadas no deben servirse desde caché;
por eso los grupos autenticados usan `PreventBrowserCache`. Intentar abrir una URL
protegida directamente sin los permisos necesarios debe redirigir al login o
responder con acceso denegado.
