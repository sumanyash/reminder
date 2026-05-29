# Avyukta Reminder Panel — Setup & Usage Instructions

## Live URL
```
https://reminder.clouddialer.in/Remiderpaneltest.php
```

---

## Default Login

| Role  | Username | Password   |
|-------|----------|------------|
| Admin | `admin`  | `admin123` |

> Change `ADMIN_PASS` in `config.php` after first login.

Employee credentials come from the CRM sync (username = CRM user_id like `AVY-1001`, password = CRM password).

---

## 1. Fresh Server Setup

### Requirements
- PHP 8.2+ with `pdo_mysql`, `curl`, `json` extensions
- MariaDB / MySQL 10+
- Nginx with PHP-FPM
- SSL certificate (Let's Encrypt)

### Step 1 — Clone the repo
```bash
cd /var/www
git clone https://github.com/sumanyash/reminder.git reminder
cd reminder
```

### Step 2 — Create database and user
```sql
CREATE DATABASE reminder_panel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'reminder_user'@'localhost' IDENTIFIED BY 'YOUR_STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON reminder_panel.* TO 'reminder_user'@'localhost';
FLUSH PRIVILEGES;
```

### Step 3 — Run DB migration
```sql
USE reminder_panel;

CREATE TABLE sessions (
  token VARCHAR(64) PRIMARY KEY,
  user_id VARCHAR(100) NOT NULL,
  role VARCHAR(20) NOT NULL,
  expires_at DATETIME NOT NULL,
  INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE wa_config (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  instance_id VARCHAR(100) NOT NULL,
  access_token VARCHAR(100) NOT NULL,
  api_url VARCHAR(255) NOT NULL DEFAULT 'https://wa.clouddialer.in/api/v2/messages',
  is_default TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE app_settings (
  setting_key VARCHAR(100) PRIMARY KEY,
  setting_value LONGTEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE auditLog (
  rowid INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user VARCHAR(100),
  activity TEXT,
  timestamp VARCHAR(50)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE employees (
  user VARCHAR(100) PRIMARY KEY,
  id VARCHAR(100),
  name VARCHAR(255),
  phone VARCHAR(50),
  email VARCHAR(255),
  cCode VARCHAR(10),
  pNum VARCHAR(20),
  pass VARCHAR(255),
  department VARCHAR(100),
  role VARCHAR(100),
  canAssign TINYINT(1) DEFAULT 0,
  UNIQUE KEY uq_id (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE reminders (
  rowid INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  emp VARCHAR(100),
  empName VARCHAR(255),
  empId VARCHAR(100),
  empPhone VARCHAR(50),
  empEmail VARCHAR(255),
  cCode VARCHAR(10),
  pNum VARCHAR(20),
  title TEXT,
  description TEXT,
  waGroup VARCHAR(255),
  img LONGTEXT,
  reminderCount INT DEFAULT 0,
  done TINYINT(1) DEFAULT 0,
  replies LONGTEXT DEFAULT '[]',
  autoInterval INT DEFAULT 0,
  sharedWith TEXT DEFAULT '[]',
  lastRemindTs VARCHAR(50),
  timestamp VARCHAR(50),
  assignedBy VARCHAR(100),
  prevEmpName VARCHAR(255),
  totalDuration INT DEFAULT 0,
  deadline VARCHAR(50),
  notifySchedule TEXT DEFAULT '[]',
  notifiedHours TEXT DEFAULT '[]',
  escalated TINYINT(1) DEFAULT 0,
  waConfigId INT UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE cards (
  rowid INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  emp VARCHAR(100),
  empName VARCHAR(255),
  empId VARCHAR(100),
  empPhone VARCHAR(50),
  empEmail VARCHAR(255),
  reminderTitle TEXT,
  description TEXT,
  reason TEXT,
  reminders INT DEFAULT 0,
  timestamp VARCHAR(50)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE notifLog (
  rowid INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type VARCHAR(50),
  empName VARCHAR(255),
  reminderTitle TEXT,
  channel VARCHAR(100),
  message LONGTEXT,
  ts VARCHAR(50)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Step 4 — Configure credentials
```bash
cp config.example.php config.php
nano config.php
```

Fill in:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'reminder_user');
define('DB_PASS', 'YOUR_STRONG_PASSWORD');
define('DB_NAME', 'reminder_panel');
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'your_admin_password');
define('APP_DOMAIN', 'https://reminder.clouddialer.in');
define('CRON_SECRET', 'your_unique_cron_secret');
```

### Step 5 — Nginx config
```nginx
server {
    server_name reminder.clouddialer.in;
    root /var/www/reminder;
    index Remiderpaneltest.php index.php;

    location / {
        try_files $uri $uri/ =404;
    }
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    }
    location ~ /\.ht { deny all; }
}
```

Enable and get SSL:
```bash
ln -s /etc/nginx/sites-available/reminder.clouddialer.in /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx
certbot --nginx -d reminder.clouddialer.in
```

### Step 6 — Set up cron jobs
```bash
crontab -e
```
Add:
```
*/5  * * * * php /var/www/reminder/cron_reminder.php  >> /var/log/reminder_auto.log 2>&1
*/30 * * * * php /var/www/reminder/cron_crm_sync.php  >> /var/log/reminder_crm_sync.log 2>&1
```

### Step 7 — Seed WhatsApp instance
Go to **Admin → Settings → WhatsApp Instances → Add Instance** and fill in your WA credentials. Or via SQL:
```sql
INSERT INTO wa_config (name, instance_id, access_token, api_url, is_default)
VALUES ('My Number', 'YOUR_INSTANCE_ID', 'YOUR_TOKEN',
        'https://wa.clouddialer.in/api/v2/messages', 1);
```

---

## 2. Admin Usage Guide

### Sync Employees from CRM
1. Go to **Employees** tab
2. Click **🔄 Fetch from CRM**
3. All 44 CRM users are imported automatically (runs every 30 min via cron)

### Assign a Reminder
1. Go to **Reminders** tab (or tap **+** FAB button on mobile)
2. Fill in:
   - **Assign To** — select employee
   - **Title & Description** — task details
   - **Total Duration** — e.g. 12 Hours (system auto-sends at 3h, 6h, 9h, 12h)
   - **WA Instance** — which WhatsApp number to send from
3. Click **Assign Reminder →**

### Notification Schedule (auto-calculated)
| Duration | Notifications sent at |
|----------|-----------------------|
| 3h       | 1.5h, 3h              |
| 6h       | 2h, 4h, 6h            |
| 12h      | 3h, 6h, 9h, 12h       |
| 24h      | 6h, 12h, 18h, 24h     |
| 48h      | 12h, 24h, 36h, 48h    |
| 72h      | 18h, 36h, 54h, 72h    |

### Escalation
- At final notification (deadline reached), if reminder is not marked **Done**:
  - A **Penalty Card** is automatically raised
  - A critical WhatsApp message is sent
  - Reminder is marked **Escalated**

### Grant Manager Rights
In **Employees** table, click **✓ Grant** next to an employee to allow them to assign reminders to others.

### WhatsApp Instances (Settings)
- Add multiple WA numbers from **Settings → WhatsApp Instances**
- Set one as **Default** (used when no instance is specified per reminder)
- Each reminder can optionally use a different instance

---

## 3. Employee Usage Guide

1. Login with your CRM username and password
2. **Dashboard** — see your pending/completed reminders at a glance
3. **Reminders (Tasks)** — view all reminders assigned to you
   - Tap **✓ Done** when task is completed
   - Tap **💬 Chat** to send a message/update on the reminder
4. **Cards** — view penalty cards raised against you
5. **Profile** — change your login password

---

## 4. Security Notes

| Item | Status |
|------|--------|
| Admin credentials in JS | ✅ Removed — server-side only |
| API authentication | ✅ 24h session tokens |
| WA credentials in browser | ✅ Removed — DB only, server sends |
| CORS | ✅ Locked to `reminder.clouddialer.in` |
| Password hashing | ⚠️ Plain text (CRM sends plain passwords) |
| HTTPS | ✅ Let's Encrypt SSL |

---

## 5. Backup

```bash
# Database backup
mysqldump -u reminder_user -p reminder_panel > backup_$(date +%Y%m%d).sql

# Code is on GitHub
git push origin main
```

---

## 6. Logs

```bash
# Auto-reminder engine (runs every 5 min)
tail -f /var/log/reminder_auto.log

# CRM employee sync (runs every 30 min)
tail -f /var/log/reminder_crm_sync.log
```
