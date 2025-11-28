# Memoria Descriptiva del Software - MIS Library

## 1. Descripción del Software

**MIS Library** es una solución integral de gestión bibliotecaria diseñada para modernizar y optimizar los procesos de administración de bibliotecas, tanto físicas como digitales.

El software actúa como un sistema centralizado que permite a las instituciones educativas y bibliotecas públicas gestionar eficientemente su catálogo de libros, controlar el flujo de préstamos y devoluciones, y ofrecer a los usuarios finales una plataforma intuitiva para la búsqueda, reserva y consumo de contenido.

A diferencia de los sistemas bibliotecarios tradicionales (ILS) que suelen ser rígidos y con interfaces obsoletas, MIS Library se centra en la **Experiencia de Usuario (UX)**, ofreciendo una interfaz moderna, responsiva y rápida, similar a las aplicaciones comerciales de consumo de contenido.

### Objetivos del Sistema

- **Centralización**: Unificar la gestión de inventario físico y archivos digitales (PDFs).
- **Automatización**: Gestionar préstamos, vencimientos y penalizaciones de forma automática.
- **Accesibilidad**: Permitir el acceso al catálogo desde cualquier dispositivo (móvil, tablet, escritorio).
- **Seguridad**: Proteger la propiedad intelectual y los datos de los usuarios mediante roles y permisos granulares.

---

## 2. Lenguajes de Programación y Tecnologías

El desarrollo de MIS Library se basa en un stack tecnológico de vanguardia, seleccionado por su robustez, seguridad y escalabilidad.

### Backend

- **Lenguaje**: **PHP 8.2+**. Elegido por su madurez, rendimiento en web y amplia comunidad.
- **Framework**: **Laravel 12**. Proporciona una arquitectura MVC (Modelo-Vista-Controlador) sólida, seguridad integrada (protección CSRF, SQL Injection) y un ecosistema rico de herramientas.
- **Base de Datos**: **MySQL**. Sistema de gestión de bases de datos relacional para almacenar usuarios, libros, transacciones y configuraciones.

### Frontend

- **Lenguaje**: **TypeScript**. Superset de JavaScript que añade tipado estático, mejorando la calidad del código y reduciendo errores en tiempo de ejecución.
- **Framework**: **Vue.js 3**. Utilizado con **Composition API** para construir interfaces de usuario reactivas y modulares.
- **Estilos**: **Tailwind CSS 4**. Framework de diseño "utility-first" que permite crear interfaces personalizadas y adaptables (responsive) sin salir del HTML.

### Arquitectura de Integración

- **Inertia.js**: Esta es una pieza clave de la originalidad técnica del proyecto. Permite construir una **Single Page Application (SPA)** moderna utilizando el enrutamiento y controladores clásicos de Laravel. Esto elimina la complejidad de gestionar una API REST separada, manteniendo la velocidad y fluidez de una aplicación de cliente.

### Herramientas Adicionales

- **Vite**: Empaquetador de módulos de última generación para tiempos de carga instantáneos durante el desarrollo.
- **Laravel Fortify**: Motor de autenticación backend agnóstico del frontend.
- **Spatie Permission**: Gestión avanzada de Roles (Admin, Bibliotecario, Usuario) y Permisos.

---

## 3. Funcionamiento del Software

El sistema opera a través de módulos interconectados que cubren todo el ciclo de vida de la gestión bibliotecaria.

### 3.1. Módulo de Catálogo y Búsqueda

El núcleo del sistema es un catálogo híbrido capaz de gestionar:

- **Libros Físicos**: Control de stock, ubicación y copias disponibles.
- **Libros Digitales**: Gestión de archivos PDF con control de acceso.

**Funcionamiento**:

1. El usuario accede al buscador global.
2. El sistema filtra en tiempo real por título, autor, ISBN o categoría.
3. Los resultados muestran disponibilidad inmediata (física) o acceso directo (digital).

### 3.2. Sistema de Préstamos y Reservas

Gestiona el flujo de material físico fuera de la biblioteca.

**Flujo de Trabajo**:

1. **Solicitud**: El usuario añade libros a su "Carrito de Préstamos" y confirma la solicitud.
2. **Validación**: El sistema verifica automáticamente:
    - Si el usuario tiene membresía activa.
    - Si no ha excedido su límite de préstamos simultáneos.
    - Si no tiene devoluciones pendientes o multas.
3. **Aprobación**: Un bibliotecario revisa y aprueba la solicitud.
4. **Devolución**: El sistema rastrea la fecha de devolución y cambia el estado a "Vencido" si se supera el plazo.

### 3.3. Gestión de Contenido Digital (Descargas)

Para los libros digitales, el sistema implementa un control de consumo:

- Los usuarios tienen un **límite diario de descargas** (configurable, por defecto 5).
- El sistema registra cada descarga para estadísticas de uso.
- Los archivos se sirven de forma segura, ocultando la ruta real del servidor para evitar descargas no autorizadas (hotlinking).

### 3.4. Administración y Seguridad

- **Roles y Permisos**: Los administradores tienen control total. Los bibliotecarios pueden gestionar libros y préstamos pero no la configuración del sistema. Los usuarios tienen acceso de solo lectura al catálogo y gestión de su propia cuenta.
- **Autenticación de Dos Factores (2FA)**: Capa extra de seguridad opcional para todas las cuentas.
- **Aprobación de Usuarios**: Flujo de registro donde los nuevos usuarios permanecen en estado "Pendiente" hasta ser verificados por un administrador.

---

## 4. Originalidad y Valor Diferencial

MIS Library se distingue de otras soluciones de mercado por varios factores clave:

### 4.1. Arquitectura Híbrida (Monolito Moderno)

A diferencia de la tendencia común de separar completamente Backend (API) y Frontend (Cliente), MIS Library utiliza **Inertia.js** para unir lo mejor de ambos mundos. Esto permite:

- **Desarrollo más rápido**: No es necesario duplicar lógica de validación o modelos de datos.
- **Menor latencia**: La aplicación se siente instantánea como una SPA, pero mantiene la simplicidad de despliegue de un monolito PHP.

### 4.2. Enfoque en "Developer Experience" y Calidad de Código

El proyecto no es solo funcional, sino que está construido con estándares de ingeniería de software de alto nivel:

- Uso estricto de **TypeScript** para prevenir errores.
- Pruebas automatizadas con **Pest PHP**.
- Diseño modular de componentes Vue reutilizables.

### 4.3. Control Dual (Físico + Digital)

Muchos sistemas se especializan en uno u otro. MIS Library trata ambos tipos de recursos como ciudadanos de primera clase, permitiendo a las bibliotecas transicionar suavemente hacia un modelo híbrido sin necesitar dos sistemas separados.
