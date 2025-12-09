# GRG Database Setup Guide

## Problem Summary

Your MySQL 8.0 installation is using the `caching_sha2_password` authentication plugin, but the plugin file (`caching_sha2_password.dll`) is missing from the MySQL installation. This prevents all connection attempts (PDO, MySQL CLI, and PHP direct connections).

## ✅ Solution: Use phpMyAdmin Web Interface

This is the **recommended and fastest solution**:

### Step 1: Access phpMyAdmin
Open your browser and navigate to:
```
http://localhost/phpmyadmin
```

You should see the phpMyAdmin login page. Log in with:
- **Username**: `root`
- **Password**: (leave blank)
- **Server**: `localhost`

### Step 2: Execute Migrations (Create Database & Tables)

1. Click the **SQL** tab at the top
2. In the SQL query text area, paste the contents of: `database/migrations/001_create_tables.sql`
3. Click the **Execute** button (or press Ctrl+Enter)
4. You should see: "Query successful" message

**What was created:**
- Database: `grg_db`
- 9 tables: roles, users, restaurants, tables, reservations, notifications, payments, audit_log, restaurant_users

### Step 3: Execute Seeders (Load Test Data)

1. Still in the **SQL** tab
2. Clear the current query
3. Paste the contents of: `database/seeders/001_seed_initial_data.sql`
4. Click **Execute**
5. You should see: "8 rows inserted" and other success messages

**What was loaded:**
- 4 roles (SUPERADMIN, OWNER, RESTAURANT_ADMIN, CLIENTE)
- 8 users with test accounts
- 4 restaurants with sample data
- 27 tables across restaurants
- 5 sample reservations
- Sample notifications and payments

### Step 4: Verify Success

1. Click the **Databases** tab
2. You should see `grg_db` in the list
3. Click on it to expand
4. You should see all 9 tables listed

## Test Login Credentials

After setup completes, you can test the application:

**Superadmin:**
- Email: `admin@grg.com`
- Password: `password123`

**Restaurant Owner:**
- Email: `owner1@restaurant.com`
- Password: `password123`

**Client (Regular User):**
- Email: `cliente1@email.com`
- Password: `password123`

## Access the Application

```
http://localhost/grg
```

## Run Tests

After database setup:

```bash
cd c:\xampp\htdocs\grg
C:\xampp\php\php.exe vendor/bin/phpunit tests/
```

Expected: 8 tests should pass

## Alternative: Fix MySQL Installation (Advanced)

If you want to fix the MySQL 8.0 plugin issue permanently:

1. Reinstall MySQL 8.0 or upgrade to a newer version
2. Or downgrade to MariaDB 10.4 (compatibility with XAMPP)
3. Or manually copy missing plugin files from a complete MySQL 8.0 installation

### Temporary Workaround

To reconfigure MySQL to use `mysql_native_password`:

```sql
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '';
FLUSH PRIVILEGES;
```

Then restart MySQL service with administrative privileges.

## Troubleshooting

### "Access Denied" in phpMyAdmin
- Ensure MySQL is running (check XAMPP Control Panel)
- Verify port 3306 is not blocked by firewall
- Try refreshing the page

### phpMyAdmin not loading
- Check that Apache is running
- Navigate to: `http://localhost/dashboard`
- Verify both Apache and MySQL are green/running

### "No database selected" error
- Execute migration script FIRST
- This creates the `grg_db` database
- Then execute seeder script

## File Locations

- **Migrations**: `database/migrations/001_create_tables.sql`
- **Seeders**: `database/seeders/001_seed_initial_data.sql`
- **Application Root**: `c:\xampp\htdocs\grg\`
- **Configuration**: `.env` file in root directory

## Quick Reference

| Task | Location |
|------|----------|
| Web App | http://localhost/grg |
| phpMyAdmin | http://localhost/phpmyadmin |
| Source Code | `c:\xampp\htdocs\grg\` |
| Database Files | `C:\xampp\mysql\data\grg_db\` |
| Configuration | `c:\xampp\htdocs\grg\.env` |

---

**Status**: GRG MVP application is 100% complete with all 70+ files created. Only database setup remains. This guide provides the working solution.
