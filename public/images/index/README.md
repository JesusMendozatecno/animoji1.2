# 📸 Imágenes del apartado INICIO (index)

Aquí se guardan las imágenes del **apartado principal (pantalla de inicio, antes de iniciar sesión)**.
Las imágenes actuales son de **PRUEBA** (generadas con colores neón) para que veas la estructura completa.

## Cómo reemplazarlas

1. Prepara tu imagen.
2. **Reemplaza el archivo manteniendo EL MISMO NOMBRE y formato** (`.jpg` para fotos/fondos, `.png` para logo y avatar con transparencia).
3. Refresca la página (`Ctrl + F5`).

Si quieres regenerar las de prueba: `php scripts/generate_home_placeholders.php`

---

## Lista de imágenes y dónde aparecen en la pestaña

| Archivo | Qué es | Dónde va en la página (Inicio) |
|---|---|---|
| `hero-bg.jpg` | **Fondo de TODA la página de inicio** (se ve detrás de todas las secciones, con un oscurecido encima para que se lea el texto) | Abarca toda la pestaña Inicio, desde el hero hasta el banner final |
| `hero-bg1.jpg` | Fondo del banner principal (zona roja con luna) | Detrás del texto "BIENVENIDO A ANIMOJI" y los botones, en la misma zona y tamaño de siempre |
| `logo.png` | Logo con forma de rombo rojo (tamaño cuadrado, con transparencia) | Arriba a la izquierda en la barra de navegación, y también en el banner "QUE TE DICE EL ANIME" |
| `avatar.png` | Avatar de usuario de prueba (con transparencia) | Arriba a la derecha en la barra de navegación (icono de usuario) |
| `anime.jpg` | Fondo de la tarjeta ANIME (cian) | Dentro de la 1ª tarjeta de categorías (la de color celeste), a la derecha del texto |
| `manga.jpg` | Fondo de la tarjeta MANGA (rojo) | Dentro de la 2ª tarjeta de categorías (la roja), a la derecha del texto |
| `musica.jpg` | Fondo de la tarjeta MÚSICA (morado) | Dentro de la 3ª tarjeta de categorías (la morada), a la derecha del texto |
| `juegos.jpg` | Fondo de la tarjeta VIDEOJUEGOS (turquesa) | Dentro de la 4ª tarjeta de categorías (la verde/turquesa), a la derecha del texto |
| `destacado-1.jpg` … `destacado-7.jpg` | Portadas verticales del carrusel "7 CAPITALES" | En las 7 tarjetas numeradas del carrusel (la 1 a la izquierda con el número 1, hasta la 7 a la derecha) |

## Tamaños recomendados para tus imágenes reales

| Archivo | Tamaño aprox. | Relación |
|---|---|---|
| `hero-bg.jpg` | 1920 × 1080 px | Pantalla ancha (fondo general de la página) |
| `hero-bg1.jpg` | 1920 × 900 px | Pantalla ancha (banner superior) |
| Categorías (`anime.jpg`, etc.) | 640 × 400 px | 1.6 : 1 |
| Destacados (`destacado-N.jpg`) | 320 × 460 px | Vertical (tipo portada) |
| `logo.png` | 128 × 128 px | Cuadrado, fondo transparente |
| `avatar.png` | 96 × 96 px | Cuadrado, fondo transparente |

## 🎨 Temas por color (personalización del index)

Además de estas imágenes (por defecto), existen carpetas por color:
`rojo/`, `azul/`, `aquamarine/`, `azul oscuro/`, `morado/`, `rosado/`, `vinotinto/`, `amarillo/`.
Cada usuario puede elegir un color en **Cuenta → Personalización → Imágenes del index**;
al hacerlo, el index usa los archivos de esa carpeta (mismos nombres que aquí).
Regenerar sus imágenes de prueba: `php scripts/generate_index_theme_placeholders.php`

## Organización del sistema de imágenes

Esta carpeta (`public/images/index/`) pertenece al **apartado inicio**. Cuando crees las imágenes de
otros apartados, crea su carpeta con el nombre del apartado dentro de `public/images/`:

```
public/images/
├── index/      ← inicio (esta carpeta)
├── anime/      ← imágenes del catálogo de anime (futuro)
├── manga/      ← imágenes del catálogo de manga (futuro)
├── musica/     ← imágenes del apartado de música (futuro)
└── juegos/     ← imágenes del apartado de videojuegos (futuro)
```
