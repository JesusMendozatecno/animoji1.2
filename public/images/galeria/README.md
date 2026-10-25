# 📸 Imágenes de la GALERÍA (`/admin/galeria`)

Pruebas en `public/images/galeria/`. Reemplaza manteniendo el nombre y `Ctrl+F5`.
Regenerar: `php scripts/generate_panel_placeholders.php`

| Archivo | Dónde va en la Galería |
|---|---|
| `banner.jpg` | Fondo del banner "GALERÍA DE IMÁGENES" (zona superior con el título) |
| `img-1.jpg` … `img-8.jpg` | Las tarjetas de la cuadrícula (Logo ANIMOJI, Anime 01, Anime 02…). El nombre visible de cada tarjeta se lista en `galleryItems` dentro de `resources/js/Pages/Admin/Galeria.vue` |

Tamaños: banner 1600×360 · imágenes 480×300.

**Para agregar tus fotos**: suelta tus archivos aquí (cualquier nombre) y añade
su ruta/nombre al arreglo `galleryItems` en la página — cada entrada es
`{ name, tag, image }`.
