# 📸 Imágenes del diseño ROJO de Login / Registro

Carpeta del apartado de **inicio de sesión y registro (tema rojo)**.
Las imágenes actuales son de **PRUEBA**. Reemplaza cada archivo manteniendo
**el mismo nombre y formato** y recarga con `Ctrl + F5`.
Regenerar las de prueba: `php scripts/generate_login_placeholders.php`

| Archivo | Qué es | Dónde se ve en la pestaña |
|---|---|---|
| `bg.jpg` | **Imagen de FONDO** de toda la pantalla (ciudad neón roja) | Cubre toda la página detrás de todo: el personaje, el panel y los textos |
| `character.png` | **Imagen del PERSONAJE** (chica/estilo anime, con transparencia) | Lado izquierdo en **Login**, lado derecho en **Registro**, de pie sobre el fondo |
| `logo.png` | Logo pequeño (rombo rojo con ojo) | Dentro del panel, arriba del título "INICIAR SESIÓN" / "REGISTRARSE" |

## ⚠️ Cómo colocar tus imágenes reales

1. **Fondo:** reemplaza `bg.jpg` (recomendado 1920×1080 px). Si tu imagen ya
   incluye al personaje dibujado dentro, deja `character.png` como está o
   elimina la etiqueta `<img>` del personaje en `AuthLayout.vue` (está anotada).
2. **Personaje:** reemplaza `character.png`. Debe ser un PNG **con fondo transparente**
   para que se vea bien sobre el fondo. Tamaño aprox. 700×1000 px.
3. **Logo:** reemplaza `logo.png` (128×128 px, fondo transparente).

## Estructura de carpetas por tema

Esta carpeta corresponde al tema **rojo**. Los próximos temas tendrán su
propia carpeta:

```
public/images/login/
├── rojo/       ← tema rojo (esta carpeta)
│   ├── bg.jpg
│   ├── character.png
│   └── logo.png
├── azul/       ← tema azul (futuro)
├── mono/       ← tema monocromo (futuro)
└── ...         ← etc.
```

En `resources/js/Layouts/AuthLayout.vue` existe la constante `themeImages`
que define de qué carpeta se cargan las imágenes. Cuando agregues un tema
nuevo, solo cambia esa ruta.
