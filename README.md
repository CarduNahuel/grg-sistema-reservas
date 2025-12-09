# GRG - Gestor de Reservas Gastronómicas

Sistema de gestión de reservas para restaurantes desarrollado en PHP puro, Bootstrap 5 y MySQL.

## 📋 Requisitos

- **XAMPP** (Apache + MySQL + PHP 7.4 o superior)
- **Composer** (para gestión de dependencias)
- **PHP 7.4+** con extensiones:
  - PDO
  - PDO_MySQL
  - OpenSSL
  - MBString
- **MySQL 5.7+** o **MariaDB 10.2+**

## 🚀 Instalación

### 1. Clonar o copiar el proyecto

Coloca el proyecto en tu directorio de XAMPP:
```
c:\xampp\htdocs\grg\
```

### 2. Instalar dependencias con Composer

Abre una terminal en el directorio del proyecto y ejecuta:

```bash
composer install
```

Esto instalará:
- PHPMailer (para envío de emails)
- PHPUnit (para testing)

### 3. Configurar el entorno

Copia el archivo `.env.example` a `.env`:

```bash
copy .env.example .env
```

Edita `.env` con tus configuraciones:

```env
# Database
DB_HOST=localhost
DB_PORT=3306
DB_NAME=grg_db
DB_USER=root
DB_PASS=

# Email (SMTP) - Configura con tus credenciales
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-password-de-aplicacion
MAIL_ENCRYPTION=tls
```

**Nota sobre Gmail:** Si usas Gmail, necesitas generar una "Contraseña de aplicación":
1. Ve a tu cuenta de Google → Seguridad
2. Activa la verificación en dos pasos
3. Genera una contraseña de aplicación
4. Usa esa contraseña en `MAIL_PASSWORD`

### 4. Crear la base de datos

Abre **phpMyAdmin** (http://localhost/phpmyadmin) o usa la terminal de MySQL.

Ejecuta el script de migración:

```bash
# Desde MySQL CLI
mysql -u root -p < database/migrations/001_create_tables.sql

# O desde phpMyAdmin: importa el archivo
# database/migrations/001_create_tables.sql
```

### 5. Poblar con datos de prueba (seeders)

```bash
mysql -u root -p grg_db < database/seeders/001_seed_initial_data.sql
```

### 6. Configurar Apache (opcional)

Si quieres usar un dominio personalizado, edita `c:\xampp\apache\conf\extra\httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    DocumentRoot "c:/xampp/htdocs/grg/public"
    ServerName grg.local
    <Directory "c:/xampp/htdocs/grg/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Y agrega en `C:\Windows\System32\drivers\etc\hosts`:
```
127.0.0.1 grg.local
```

### 7. Iniciar servicios

Desde el panel de control de XAMPP:
1. Inicia **Apache**
2. Inicia **MySQL**

### 8. Acceder al sistema

Abre tu navegador y ve a:
```
http://localhost/grg/
```

O si configuraste un virtual host:
```
http://grg.local/
```

## 👥 Usuarios de Prueba

Una vez ejecutados los seeders, tendrás estos usuarios:

| Email | Contraseña | Rol |
|-------|-----------|-----|
| admin@grg.com | password123 | SUPERADMIN |
| owner1@restaurant.com | password123 | OWNER |
| admin1@restaurant.com | password123 | RESTAURANT_ADMIN |
| cliente1@email.com | password123 | CLIENTE |

## 🔧 Configurar Cron para Recordatorios

Para enviar recordatorios automáticos de reservas, configura un cron job (o tarea programada en Windows).

### En Windows (Programador de tareas):

1. Abre "Programador de tareas"
2. Crea una tarea básica
3. Trigger: cada 15 minutos
4. Acción: `C:\xampp\php\php.exe c:\xampp\htdocs\grg\cron\send_reminders.php`

### En Linux/Mac (crontab):

```bash
crontab -e

# Agregar:
*/15 * * * * php /xampp/htdocs/grg/cron/send_reminders.php >> /var/log/grg_cron.log 2>&1
```

## 🧪 Ejecutar Tests

Para ejecutar los tests con PHPUnit:

```bash
vendor/bin/phpunit tests/
```

O en Windows:
```bash
vendor\bin\phpunit tests\
```

## 📁 Estructura del Proyecto

```
grg/
├── bootstrap/          # Inicialización de la aplicación
├── config/            # Archivos de configuración
├── cron/              # Scripts para tareas programadas
├── database/
│   ├── migrations/    # Scripts SQL de creación de tablas
│   └── seeders/       # Datos iniciales
├── public/            # Punto de entrada público
│   ├── css/          # Estilos
│   ├── js/           # JavaScript
│   └── index.php     # Front controller
├── routes/            # Definición de rutas
├── src/
│   ├── Controllers/   # Controladores
│   ├── Middleware/    # Middleware de autenticación, CSRF, etc.
│   ├── Models/        # Modelos de base de datos
│   └── Services/      # Servicios (Auth, Email, Router, etc.)
├── tests/             # Tests PHPUnit
├── views/             # Vistas (templates PHP)
├── .env.example       # Configuración de ejemplo
├── .htaccess         # Configuración Apache
├── composer.json      # Dependencias
└── README.md          # Este archivo
```

## 🔐 Seguridad Implementada

- ✅ Contraseñas hasheadas con bcrypt
- ✅ Protección CSRF en formularios
- ✅ Prepared statements (PDO) contra SQL Injection
- ✅ Sesiones seguras (httpOnly, regeneración periódica)
- ✅ Validación y sanitización de inputs
- ✅ Middleware de autenticación y autorización por roles

## 🎯 Funcionalidades Principales

### Para Clientes:
- Registro e inicio de sesión
- Búsqueda de restaurantes
- Visualización de disponibilidad en tiempo real
- Creación de reservas
- Gestión de reservas (cancelación)
- Notificaciones in-app y por email

### Para Propietarios (OWNER):
- Gestión de múltiples restaurantes
- Gestión de mesas (layout, áreas, pisos)
- Confirmación/Rechazo de reservas
- Reasignación de mesas
- Panel de control con estadísticas
- Sistema de pagos para restaurantes adicionales

### Para Administradores (RESTAURANT_ADMIN):
- Gestión de reservas del restaurante asignado
- Check-in de clientes
- Marcado de no-shows

### Para SuperAdmin:
- Acceso completo al sistema
- Gestión de usuarios
- Auditoría

## 📧 Configuración de Email

El sistema usa **PHPMailer** para envío de correos. Los emails se envían para:

- Confirmación de registro
- Creación de reserva
- Confirmación de reserva
- Rechazo de reserva
- Recordatorios (1 hora antes)
- Cancelaciones

## 💳 Sistema de Pagos

El primer restaurante es **GRATIS**. Los restaurantes adicionales requieren pago (USD $50).

La integración de pasarela de pago es un **stub** en esta versión. Para implementar una pasarela real (Stripe, MercadoPago, etc.), modifica:

- `src/Controllers/PaymentController.php`
- `src/Services/PaymentService.php`

## 🐛 Troubleshooting

### Error de conexión a la base de datos
- Verifica que MySQL esté corriendo en XAMPP
- Confirma las credenciales en `.env`
- Asegúrate de que la base de datos `grg_db` exista

### Los emails no se envían
- Verifica las credenciales SMTP en `.env`
- Si usas Gmail, asegúrate de usar una "Contraseña de aplicación"
- Revisa los logs de error de PHP

### Errores 404 en las rutas
- Verifica que `mod_rewrite` esté habilitado en Apache
- Confirma que el archivo `.htaccess` existe en `/public/`
- Revisa la configuración de `AllowOverride All`

### No se cargan los estilos/JavaScript
- Verifica que la ruta base en las vistas sea correcta (`/grg/`)
- Confirma que los archivos existan en `/public/css/` y `/public/js/`

## 📝 Notas Adicionales

- El sistema usa **zona horaria de Argentina** por defecto (configurable en `bootstrap/app.php`)
- Los no-shows se marcan automáticamente después de 15 minutos de tolerancia
- Las sesiones expiran después de 2 horas de inactividad
- El sistema es **responsive** y funciona en dispositivos móviles

## 📄 Licencia

Este proyecto es de uso educativo/demostración.

## 👨‍💻 Desarrollo

Para agregar nuevas funcionalidades:

1. **Nuevas rutas**: Edita `routes/web.php`
2. **Nuevos controladores**: Crea en `src/Controllers/`
3. **Nuevos modelos**: Crea en `src/Models/`
4. **Nuevas vistas**: Crea en `views/`
5. **Nuevas migraciones**: Crea en `database/migrations/`

## 🤝 Soporte

Para reportar bugs o sugerir mejoras, contacta al equipo de desarrollo.

---

**¡Disfruta gestionando reservas con GRG!** 🍽️✨
