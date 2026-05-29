# Avyukta Reminder Panel — Project Structure

## File Structure

```
/var/www/reminder/
├── Remiderpaneltest.php   # Main app (frontend HTML/CSS/JS + PHP header)
├── api.php                # Backend REST API (all server-side logic)
├── config.php             # DB credentials + secrets (NOT in git)
├── config.example.php     # Template for config.php (in git)
├── cron_reminder.php      # Auto-reminder engine (runs every 5 min)
├── cron_crm_sync.php      # CRM employee sync (runs every 30 min)
├── instruction.md         # Setup and usage guide
└── structure.md           # This file
```

---

## Architecture

```
Browser (Remiderpaneltest.php)
        │
        │  HTTPS POST /api.php
        │  { action: "...", token: "...", ...data }
        ▼
    api.php  ──── MySQL (reminder_panel DB)
        │
        │  cURL POST
        ▼
WhatsApp API (wa.clouddialer.in)

Cron (every 5 min)
    cron_reminder.php ──► api.php?processAutoReminders
```

### Data Flow
1. User logs in → `api.php` validates credentials → returns 24h session token
2. Token stored in `sessionStorage` → sent with every API call
3. All data fetched as one payload (`getCloudData`) → stored in JS arrays
4. Changes saved via `syncCloudData` (full state replace) → MySQL
5. WhatsApp sends go through `api.php sendWA` → never from browser directly

---

## Database Schema

### `employees`
| Column     | Type         | Description                          |
|------------|--------------|--------------------------------------|
| user       | VARCHAR(100) | PK — Login username (e.g. AVY-1001)  |
| id         | VARCHAR(100) | Employee ID (same as user for CRM)   |
| name       | VARCHAR(255) | Full name                            |
| phone      | VARCHAR(50)  | Full phone with country code         |
| email      | VARCHAR(255) | Email address                        |
| cCode      | VARCHAR(10)  | Country code prefix (+91)            |
| pNum       | VARCHAR(20)  | Phone number without code            |
| pass       | VARCHAR(255) | Plain-text password (from CRM)       |
| department | VARCHAR(100) | Department name                      |
| role       | VARCHAR(100) | Role/designation                     |
| canAssign  | TINYINT(1)   | 1 = can assign reminders to others   |

### `reminders`
| Column          | Type          | Description                              |
|-----------------|---------------|------------------------------------------|
| rowid           | INT (PK)      | Auto-increment primary key               |
| emp             | VARCHAR(100)  | Assigned employee username               |
| empName         | VARCHAR(255)  | Employee display name                    |
| empId           | VARCHAR(100)  | Employee ID                              |
| empPhone        | VARCHAR(50)   | Employee phone (for WA)                  |
| empEmail        | VARCHAR(255)  | Employee email                           |
| cCode           | VARCHAR(10)   | Country code                             |
| pNum            | VARCHAR(20)   | Phone number                             |
| title           | TEXT          | Reminder title                           |
| description     | TEXT          | Reminder description                     |
| waGroup         | VARCHAR(255)  | WA group name (optional)                 |
| img             | LONGTEXT      | Base64 encoded image attachment          |
| reminderCount   | INT           | Total notifications sent so far          |
| done            | TINYINT(1)    | 1 = marked as completed                  |
| replies         | LONGTEXT      | JSON array of chat messages              |
| autoInterval    | INT           | Legacy: manual repeat interval (hours)   |
| sharedWith      | TEXT          | JSON array of users with chat access     |
| lastRemindTs    | VARCHAR(50)   | ISO timestamp of last notification sent  |
| timestamp       | VARCHAR(50)   | ISO timestamp when reminder was created  |
| assignedBy      | VARCHAR(100)  | Username who assigned this reminder      |
| prevEmpName     | VARCHAR(255)  | Previous assignee name (after reassign)  |
| totalDuration   | INT           | Total hours until auto-escalation        |
| deadline        | VARCHAR(50)   | ISO timestamp of deadline                |
| notifySchedule  | TEXT          | JSON array: [3, 6, 9, 12] hours          |
| notifiedHours   | TEXT          | JSON array: hours already notified       |
| escalated       | TINYINT(1)    | 1 = penalty card raised, escalated       |
| waConfigId      | INT UNSIGNED  | FK to wa_config.id (NULL = use default)  |

### `cards` (Penalty Cards)
| Column        | Type         | Description                         |
|---------------|--------------|-------------------------------------|
| rowid         | INT (PK)     | Auto-increment primary key          |
| emp           | VARCHAR(100) | Employee username                   |
| empName       | VARCHAR(255) | Employee display name               |
| empId         | VARCHAR(100) | Employee ID                         |
| empPhone      | VARCHAR(50)  | Employee phone                      |
| empEmail      | VARCHAR(255) | Employee email                      |
| reminderTitle | TEXT         | Title of the unresolved reminder    |
| description   | TEXT         | Description                         |
| reason        | TEXT         | Why the card was raised             |
| reminders     | INT          | How many reminders were sent        |
| timestamp     | VARCHAR(50)  | When the card was raised            |

### `wa_config` (WhatsApp Instances)
| Column       | Type         | Description                               |
|--------------|--------------|-------------------------------------------|
| id           | INT (PK)     | Auto-increment primary key                |
| name         | VARCHAR(100) | Display name (e.g. "Yash Personal")       |
| instance_id  | VARCHAR(100) | WA API instance ID (e.g. RHS2B4V4AY)     |
| access_token | VARCHAR(100) | Bearer token for WA API                   |
| api_url      | VARCHAR(255) | API endpoint URL                          |
| is_default   | TINYINT(1)   | 1 = used when no instance specified       |
| created_at   | TIMESTAMP    | When added                                |

### `sessions`
| Column     | Type         | Description                     |
|------------|--------------|---------------------------------|
| token      | VARCHAR(64)  | PK — random 32-byte hex token   |
| user_id    | VARCHAR(100) | Username                        |
| role       | VARCHAR(20)  | admin / employee                |
| expires_at | DATETIME     | Token expiry (24h from login)   |

### `notifLog`
| Column        | Type         | Description                       |
|---------------|--------------|-----------------------------------|
| rowid         | INT (PK)     | Auto-increment primary key        |
| type          | VARCHAR(50)  | reminder / auto_reminder / card   |
| empName       | VARCHAR(255) | Recipient name                    |
| reminderTitle | TEXT         | Related reminder                  |
| channel       | VARCHAR(100) | 💬 WhatsApp / 📧 Email           |
| message       | LONGTEXT     | Full message text sent            |
| ts            | VARCHAR(50)  | ISO timestamp                     |

### `auditLog`
| Column    | Type         | Description           |
|-----------|--------------|-----------------------|
| rowid     | INT (PK)     | Auto-increment        |
| user      | VARCHAR(100) | Who performed action  |
| activity  | TEXT         | What they did         |
| timestamp | VARCHAR(50)  | When                  |

### `app_settings`
| Column        | Type      | Description            |
|---------------|-----------|------------------------|
| setting_key   | VARCHAR   | PK — setting name      |
| setting_value | LONGTEXT  | Value (JSON or string) |

---

## API Reference (`api.php`)

All requests: `POST /api.php` with body `data=<JSON>`

### Public Actions (no token required)
| Action  | Payload                          | Returns                          |
|---------|----------------------------------|----------------------------------|
| `login` | `{user, pass, role}`             | `{success, token, role, user}`   |
| `logout`| `{token}`                        | `{success}`                      |
| `test`  | `{}`                             | `{success, message}`             |

### Authenticated Actions (token required)
| Action           | Admin only | Description                             |
|------------------|------------|-----------------------------------------|
| `getCloudData`   | No         | Fetch all employees/reminders/cards/log |
| `syncCloudData`  | No         | Save full state to DB                   |
| `sendWA`         | No         | Send WhatsApp via server-side cURL      |
| `auditLog`       | No         | Write to audit log                      |
| `getWAConfig`    | No         | List WA instances (no tokens returned)  |
| `saveWAConfig`   | Yes        | Create / update WA instance             |
| `deleteWAConfig` | Yes        | Delete WA instance                      |
| `fetchFromCRM`   | Yes        | Pull employees from Avyukta CRM         |

### Cron Action (cronSecret required)
| Action                  | Description                               |
|-------------------------|-------------------------------------------|
| `processAutoReminders`  | Check all reminders, send due notifications, raise penalty cards |

---

## Frontend Sections

| Section       | Route ID       | Who can see       |
|---------------|----------------|-------------------|
| Dashboard     | `dashboard`    | Everyone          |
| Reminders     | `reminders`    | Everyone          |
| Penalty Cards | `cards`        | Everyone          |
| My Profile    | `profile`      | Everyone          |
| Employees     | `employees`    | Admin only        |
| Notification Log | `notifications` | Admin only     |
| Settings      | `settings`     | Admin only        |

---

## Mobile Navigation

```
Bottom Nav (mobile ≤768px):
  [▦ Home] [✓ Tasks] [⚠ Cards] [👥 Team*] [👤 Profile]
                                   *admin only

Top Bar (mobile):
  [Logo · Avyukta] ←————————→ [Avatar · Name] [⏻ Logout] [☰ Menu]

FAB Button (bottom-right):
  [+] — Quick assign reminder (only on Tasks tab, only for admin/managers)
```

---

## Automated Reminder Engine

```
cron_reminder.php  (every 5 min)
        │
        ▼
api.php → processAutoReminders
        │
        ├── For each active reminder with totalDuration > 0:
        │       elapsed = now - createdAt (in hours)
        │       schedule = [25%, 50%, 75%, 100%] of totalDuration
        │
        ├── For each milestone passed but not yet notified:
        │       Template 1 (initial)  → "Reminder Assigned"
        │       Template 2 (medium)   → "Still Pending"
        │       Template 3 (high)     → "Approaching Deadline"
        │       Template 4 (critical) → "Deadline Reached — ESCALATE"
        │
        └── On Template 4:
                → INSERT into cards table
                → UPDATE reminders SET escalated = 1
                → Send penalty WA message
```

---

## CRM Sync

```
Source:  https://urm.avyuktacrm.com/api/user_details.php
Trigger: Manual (Admin → Fetch from CRM) OR cron every 30 min
Logic:   INSERT ... ON DUPLICATE KEY UPDATE
         (new employees added, existing ones updated)
Fields:  user_id → user/id, name+lastname → name,
         mob/countryCode → phone, password → pass,
         roles/department, superadmin_permission → canAssign
```

---

## Environment

| Item        | Value                                      |
|-------------|--------------------------------------------|
| Server      | Debian Linux, Nginx 1.22                   |
| PHP         | 8.2 (FPM)                                  |
| Database    | MariaDB 10.11                              |
| DB Name     | `reminder_panel`                           |
| DB User     | `reminder_user`                            |
| Domain      | `reminder.clouddialer.in`                  |
| SSL         | Let's Encrypt (auto-renew via Certbot)     |
| Git Repo    | `https://github.com/sumanyash/reminder`    |
| WA API      | `https://wa.clouddialer.in/api/v2/messages`|
| CRM API     | `https://urm.avyuktacrm.com/api/`         |
