# 📸 Imágenes del apartado MANGA (tema monocromo B/N)

Imágenes de **PRUEBA** para la página de presentación de manga (`/manga`).
Reemplaza cada archivo manteniendo **el mismo nombre y formato** y recarga con `Ctrl + F5`.
Regenerar las de prueba: `php scripts/generate_catalog_placeholders.php`

| Archivo | Qué es | Dónde va en la pestaña de Manga |
|---|---|---|
| `hero.jpg` | Fondo del banner principal (B/N) | Detrás del texto "EXPLORA EL MUNDO DEL MANGA" y los botones (zona superior) |
| `cover-1.jpg` … `cover-8.jpg` | Portadas de las tarjetas "Mangas Destacados" | Dentro de cada tarjeta de la cuadrícula, arriba del título |
| `reco-1.jpg` … `reco-5.jpg` | Miniaturas de la columna derecha | En "Recomendados para ti", a la izquierda de cada título |
| `sidebar.jpg` | Decoración vertical del menú lateral | Detrás de la cita "El manga no es solo una historia...", en la parte baja del sidebar |
| `logo.png` | Logo del apartado (rombo con M) | Arriba a la izquierda, junto al texto ANIMOJI en el sidebar |

## Nota sobre el estilo B/N

La página de manga aplica el filtro **grayscale** a las portadas para mantener el
estilo monocromo del diseño. Si prefieres portadas a color, quita la clase
`grayscale` de las imágenes en `resources/js/Pages/Manga/Index.vue`.

## Tamaños recomendadas

| Archivo | Tamaño aprox. |
|---|---|
| `hero.jpg` | 1400 × 420 px |
| `cover-N.jpg` | 400 × 260 px |
| `reco-N.jpg` | 120 × 120 px |
| `sidebar.jpg` | 360 × 900 px |
| `logo.png` | 128 × 128 px (fondo transparente) |
