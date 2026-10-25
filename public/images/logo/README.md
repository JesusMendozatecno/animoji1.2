# 🖼️ Carpeta LOGO — logos por color

Aquí viven los **logos del sistema** (el mismo diseño en varios colores).
Se usan en el login/registro (cada tema de color usa su logo) y en cualquier
zona donde se necesite el logo.

## Cómo colocar tus logos

1. Pon tu logo en esta carpeta con el nombre exacto de su color:
   `logo-rojo.png`, `logo-azul.png`, `logo-aquamarine.png`,
   `logo-azul oscuro.png`, `logo-morado.png`, `logo-rosado.png`,
   `logo-vinotinto.png`, `logo-blanco.png`.
2. Formato **PNG cuadrado con fondo transparente** (128×128 o 256×256 px).
3. Refresca con `Ctrl + F5`. Los logos actuales son de PRUEBA.

Regenerar los de prueba: `php scripts/generate_logo_placeholders.php`

> Nota: si un color no tiene `logo-<color>.png`, el sistema usa el logo rojo
> de `public/images/login/rojo/logo.png` como respaldo.