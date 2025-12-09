# GUÍA DE INICIO RÁPIDO - GRG

## ⚡ Instalación Rápida (5 minutos)

### 1. Instalar dependencias
```bash
cd c:\xampp\htdocs\grg
composer install
```

### 2. Configurar base de datos

**Opción A: Desde phpMyAdmin**
1. Abre http://localhost/phpmyadmin
2. Importa: `database/migrations/001_create_tables.sql`
3. Importa: `database/seeders/001_seed_initial_data.sql`

**Opción B: Desde línea de comandos**
```bash
mysql -u root -p < database/migrations/001_create_tables.sql
mysql -u root -p grg_db < database/seeders/001_seed_initial_data.sql
```

### 3. Configurar Email (Opcional)

Edita `.env` y configura:
```
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-password-app
```

### 4. Iniciar XAMPP

1. Abre XAMPP Control Panel
2. Start Apache
3. Start MySQL

### 5. Acceder al sistema

Abre en tu navegador:
```
http://localhost/grg/
```

## 👥 Usuarios de Prueba

| Email | Password | Rol |
|-------|----------|-----|
| cliente1@email.com | password123 | Cliente |
| owner1@restaurant.com | password123 | Owner |
| admin@grg.com | password123 | SuperAdmin |

## 🎯 Flujo de Prueba

1. **Registrarse** como cliente nuevo
2. **Explorar restaurantes** en http://localhost/grg/restaurants
3. **Crear una reserva** seleccionando mesa y horario
4. **Iniciar sesión como Owner** para gestionar la reserva
5. **Confirmar/Rechazar** la reserva desde el panel de gestión

## 🐛 Problemas Comunes

### "Can't connect to database"
- Verifica que MySQL esté corriendo en XAMPP
- Confirma que existe la base de datos `grg_db`

### "404 Not Found"
- Verifica que `mod_rewrite` esté habilitado
- Revisa que el `.htaccess` exista en `/public/`

### Los estilos no cargan
- Verifica la ruta: debe ser `/grg/` al inicio de las URLs
- Confirma que Apache esté sirviendo desde htdocs

## 📱 Testing

Ejecutar tests:
```bash
vendor/bin/phpunit
```

## ✅ Checklist de Instalación

- [ ] Composer instalado
- [ ] Base de datos creada (grg_db)
- [ ] Migraciones ejecutadas
- [ ] Seeders ejecutados
- [ ] Apache iniciado
- [ ] MySQL iniciado
- [ ] Acceso a http://localhost/grg/
- [ ] Login funcionando

## 📞 Siguiente Paso

Lee el **README.md** completo para información detallada sobre:
- Configuración avanzada
- Cron jobs para recordatorios
- Tests
- Estructura del proyecto
- API y extensiones

---

**¡Listo para empezar! 🚀**
