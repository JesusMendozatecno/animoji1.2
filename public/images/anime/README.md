# 📸 Imágenes del apartado ANIME (tema rojo/rosa)

Imágenes de **PRUEBA** para la página de presentación de anime (`/anime`).
Reemplaza cada archivo manteniendo **el mismo nombre y formato** y recarga con `Ctrl + F5`.
Regenerar las de prueba: `php scripts/generate_catalog_placeholders.php`

| Archivo | Qué es | Dónde va en la pestaña de Anime |
|---|---|---|
| `hero.jpg` | Fondo del banner principal | Detrás del texto "Descubre el mundo del anime" y los botones (zona superior) |
| `cover-1.jpg` … `cover-8.jpg` | Portadas de las tarjetas "Animes Destacados" | Dentro de cada tarjeta de la cuadrícula, arriba del título (la 1 es One Piece, etc.) |
| `reco-1.jpg` … `reco-5.jpg` | Miniaturas de la columna derecha | En "Recomendados para ti", a la izquierda de cada título |
| `sidebar.jpg` | Decoración vertical del menú lateral | Detrás de la cita "Vive con lo que amas", en la parte baja del sidebar izquierdo |
| `logo.png` | Logo del apartado (rombo con A) | Arriba a la izquierda, junto al texto ANIMOJI en el sidebar |

## Tamaños recomendados

| Archivo | Tamaño aprox. |
|---|---|
| `hero.jpg` | 1400 × 420 px |
| `cover-N.jpg` | 400 × 260 px (apaisada) |
| `reco-N.jpg` | 120 × 120 px (cuadrada) |
| `sidebar.jpg` | 360 × 900 px (vertical) |
| `logo.png` | 128 × 128 px (fondo transparente) |

## Nota sobre las portadas en la BD

Las tarjetas usan el campo `cover` del catálogo (BD). Las de prueba apuntan a
`/images/anime/cover-N.jpg`. Cuando tengas tus imágenes en la BD, simplemente
ignora estos archivos o reemplázalos si prefieres servirlas desde aquí.
