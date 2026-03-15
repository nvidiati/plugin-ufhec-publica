# 🔌 Manejador de entradas Form-to-Table (UFHEC Publica)

![WordPress](https://img.shields.io/badge/WordPress-21759B?style=for-the-badge&logo=wordpress&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![MySQL](https://img.shields.io/badge/MySQL-00000f?style=for-the-badge&logo=mysql&logoColor=white)

Un plugin robusto para WordPress diseñado para gestionar envíos de usuarios externos y visualizarlos dinámicamente. Este sistema permite a los visitantes enviar información a través de un formulario público, la cual se almacena como una entrada pendiente de revisión antes de ser publicada en una tabla dinámica en el frontend.

---

## 🚀 Flujo de Funcionamiento

1. **Envío de Datos:** Los visitantes completan un formulario en el frontend de la web.
2. **Creación de Entrada:** Cada envío genera automáticamente un _Custom Post Type_ con estado "Pendiente".
3. **Moderación:** El administrador del sitio revisa y aprueba el contenido desde el panel de control.
4. **Visualización Dinámica:** Una vez aprobada, la información aparece automáticamente en una tabla dinámica visible para todos los usuarios.

---

## ✨ Características Técnicas

- **Formulario Personalizado:** Validación de campos y procesamiento seguro de datos en el servidor.
- **Manejo de Custom Post Types (CPT):** Integración nativa con la arquitectura de WordPress para una gestión limpia de los datos.
- **Tabla Dinámica:** Renderizado de datos filtrables y organizados en tiempo real.
- **Sistema de Aprobación:** Flujo de trabajo integrado que garantiza que solo el contenido verificado sea visible al público.
- **Shortcodes Disponibles:** Fácil implementación en cualquier página o entrada mediante shortcodes personalizados.

---

## 🛠️ Herramientas y Lenguajes

- [cite_start]**PHP:** Lógica del plugin, hooks de WordPress y procesamiento de formularios[cite: 14, 25].
- [cite_start]**JavaScript/AJAX:** Mejoras en la interactividad de la tabla y validaciones en el lado del cliente[cite: 26].
- [cite_start]**WordPress API:** Uso de funciones nativas para la creación de posts y manejo de metadatos[cite: 20, 33].
- [cite_start]**CSS3:** Estilizado de la tabla y el formulario para una integración visual armónica con el tema activo[cite: 27].

---

## ⚙️ Instalación

1. Descarga el repositorio en formato `.zip`.
2. Sube el plugin a tu sitio WordPress a través de **Plugins > Añadir nuevo > Subir plugin**.
3. Activa el plugin **UFHEC Publica**.
4. Utiliza los shortcodes proporcionados en la documentación interna para mostrar el formulario y la tabla en tus páginas.

---

## 📁 Estructura del Proyecto

- `/inc`: Contiene la lógica principal y funciones de ayuda.
- `/assets`: Archivos CSS y JS para el estilo y comportamiento del frontend.
- `/templates`: Plantillas de renderizado para el formulario y la tabla.

---

## ✒️ Autor

- **Kris Bell** - _Especialista en WordPress & Web Developer_ - [nvidiati](https://github.com/nvidiati) [cite: 1, 29]
