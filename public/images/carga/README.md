# 📸 Imágenes de la PANTALLA DE CARGA (splash)

Pruebas en `public/images/carga/`. Reemplaza manteniendo el nombre y `Ctrl+F5`.
Regenerar: `php scripts/generate_panel_placeholders.php`

| Archivo | Dónde va |
|---|---|
| `bg.jpg` | Fondo de **toda** la pantalla de carga (se ve detrás del anillo, el texto "Cargando sistema..." y la barra de progreso) |

Tamaño: 1920×1080.

## Color de la pantalla de carga

El color neón (anillo, barra, textos) **lo elige el usuario** en
Cuenta → Preferencias → "Color de carga". Se guarda en el navegador
(`localStorage`) y la próxima carga lo usa. Si tu `bg.jpg` ya trae un color
propio fijo, puedes dejar el color por defecto (rojo).
