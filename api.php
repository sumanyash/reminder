<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . APP_DOMAIN);
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

/* ── DB ──────────────────────────────────────────── */
function getDB() {
    static $pdo = null;
    if (!$pdo) {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    }
    return $pdo;
}

/* ── RESPONSE HELPERS ────────────────────────────── */
function respond($data) { echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
function fail($msg)     { respond(['success' => false, 'reason' => $msg]); }

/* ── SESSION AUTH ────────────────────────────────── */
function validateSession($token) {
    if (!$token || strlen($token) < 32) return null;
    $s = getDB()->prepare("SELECT user_id, role FROM sessions WHERE token = ? AND expires_at > NOW()");
    $s->execute([$token]);
    return $s->fetch() ?: null;
}

function requireAuth($payload, $adminOnly = false) {
    $sess = validateSession($payload['token'] ?? '');
    if (!$sess) fail('Session expired — please login again');
    if ($adminOnly && $sess['role'] !== 'admin') fail('Administrator access required');
    return $sess;
}

/* ── WHATSAPP ─────────────────────────────────────── */
function sendWA($phone, $message, $waConfigId = null) {
    $pdo = getDB();
    $cfg = null;
    if ($waConfigId) {
        $s = $pdo->prepare("SELECT * FROM wa_config WHERE id = ?");
        $s->execute([$waConfigId]);
        $cfg = $s->fetch();
    }
    if (!$cfg) {
        $cfg = $pdo->query("SELECT * FROM wa_config WHERE is_default = 1 LIMIT 1")->fetch();
    }
    if (!$cfg) return ['success' => false, 'reason' => 'No WA instance configured'];

    $clean = preg_replace('/\D/', '', $phone);
    if (strlen($clean) === 10) $clean = '91' . $clean;
    if (strlen($clean) < 10)  return ['success' => false, 'reason' => 'Invalid phone number'];

    $ch = curl_init($cfg['api_url']);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode([
            'instanceId'  => $cfg['instance_id'],
            'accessToken' => $cfg['access_token'],
            'to'          => $clean,
            'content'     => ['text' => $message],
        ]),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $cfg['access_token'],
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 15,
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true) ?? ['success' => false, 'reason' => 'No response from WA API'];
}

/* ── NOTIFICATION TEMPLATES ──────────────────────── */
function reminderTemplate($type, $name, $title, $deadline, $remainingH, $panelUrl) {
    switch ($type) {
        case 'initial': return
            "Hello {$name},\n\n" .
            "📌 *Reminder Assigned*\n" .
            "*Title:* {$title}\n" .
            "⏰ *Deadline:* {$deadline}\n\n" .
            "A reminder has been assigned to you. Please review and take the necessary action.\n\n" .
            "🔗 {$panelUrl}\n\nFrom Avyukta CRM Team";

        case 'medium': return
            "Hello {$name},\n\n" .
            "⚠️ *Reminder Still Pending — Action Required*\n" .
            "*Title:* {$title}\n" .
            "⏰ *Deadline:* {$deadline} (~{$remainingH}h remaining)\n\n" .
            "This reminder is still pending. Please provide an update or resolution as soon as possible.\n\n" .
            "🔗 {$panelUrl}\n\nFrom Avyukta CRM Team";

        case 'high': return
            "Hello {$name},\n\n" .
            "🚨 *High Priority — Immediate Action Required*\n" .
            "*Title:* {$title}\n" .
            "⏰ *Deadline:* {$deadline} (~{$remainingH}h remaining)\n\n" .
            "This reminder is approaching its deadline. No resolution has been recorded yet. Immediate attention is required.\n\n" .
            "🔗 {$panelUrl}\n\nFrom Avyukta CRM Team";

        case 'critical': return
            "Hello {$name},\n\n" .
            "🔴 *CRITICAL — Final Reminder*\n" .
            "*Title:* {$title}\n" .
            "⏰ *Deadline:* {$deadline}\n\n" .
            "The reminder deadline has expired. This will be *automatically escalated* and a Penalty Card will be raised on your profile immediately.\n\n" .
            "🔗 {$panelUrl}\n\nFrom Avyukta CRM Team";
    }
    return '';
}

/* ── SCHEDULE CALCULATOR ─────────────────────────── */
function calcSchedule($hours) {
    $h = (int)$hours;
    if ($h <= 0) return [];
    if ($h <= 2) return [$h];
    if ($h <= 4) return [(int)ceil($h / 2), $h];
    if ($h <= 8) return [(int)round($h * 0.33), (int)round($h * 0.67), $h];
    return [(int)round($h * 0.25), (int)round($h * 0.50), (int)round($h * 0.75), $h];
}

/* ── INPUT ───────────────────────────────────────── */
$raw     = $_POST['data'] ?? '';
if (!$raw) fail('No data provided');
$payload = json_decode($raw, true);
if (!$payload) fail('Invalid JSON');
$action  = $payload['action'] ?? '';

try {
    $pdo = getDB();

    switch ($action) {

    /* ═══ PUBLIC (no auth) ═══════════════════════════════ */

    case 'test':
        $pdo->query("SELECT 1");
        respond(['success' => true, 'message' => 'Database connection OK']);

    case 'login': {
        $user = trim($payload['user'] ?? '');
        $pass = trim($payload['pass'] ?? '');
        $role = $payload['role'] ?? 'employee';
        if (!$user || !$pass) fail('Username and password required');

        if ($role === 'admin') {
            if ($user !== ADMIN_USER || $pass !== ADMIN_PASS)
                fail('Invalid admin credentials');
        } else {
            $s = $pdo->prepare("SELECT user FROM employees WHERE user = ? AND pass = ?");
            $s->execute([$user, $pass]);
            if (!$s->fetch()) fail('Invalid credentials');
        }

        $token = bin2hex(random_bytes(32));
        $exp   = date('Y-m-d H:i:s', strtotime('+24 hours'));
        $pdo->prepare("DELETE FROM sessions WHERE user_id = ? AND role = ?")->execute([$user, $role]);
        $pdo->prepare("INSERT INTO sessions (token,user_id,role,expires_at) VALUES(?,?,?,?)")->execute([$token, $user, $role, $exp]);
        $pdo->exec("DELETE FROM sessions WHERE expires_at < NOW()");
        respond(['success' => true, 'token' => $token, 'role' => $role, 'user' => $user]);
    }

    case 'logout': {
        $tok = $payload['token'] ?? '';
        if ($tok) $pdo->prepare("DELETE FROM sessions WHERE token = ?")->execute([$tok]);
        respond(['success' => true]);
    }

    /* ═══ CRON (secret-gated) ════════════════════════════ */

    case 'processAutoReminders': {
        if (($payload['cronSecret'] ?? '') !== CRON_SECRET) fail('Unauthorized');

        $panelUrl = APP_DOMAIN . '/Remiderpaneltest.php';
        $now      = time();
        $sent     = 0;
        $escalated = 0;

        $rows = $pdo->query(
            "SELECT rowid, emp, empName, empId, empPhone, empEmail, title, totalDuration,
                    deadline, notifySchedule, notifiedHours, reminderCount, waConfigId
             FROM reminders WHERE done = 0 AND escalated = 0 AND totalDuration > 0"
        )->fetchAll();

        foreach ($rows as $rem) {
            $created  = strtotime($rem['timestamp'] ?? '') ?: $now;
            // recalc from DB timestamp column
            $created  = $pdo->prepare("SELECT timestamp FROM reminders WHERE rowid = ?")->execute([$rem['rowid']]) ? $pdo->query("SELECT timestamp FROM reminders WHERE rowid={$rem['rowid']}")->fetchColumn() : date('c');
            $created  = strtotime($created) ?: $now;
            $elapsed  = ($now - $created) / 3600;

            $schedule = json_decode($rem['notifySchedule'] ?? '[]', true) ?: calcSchedule($rem['totalDuration']);
            $notified = json_decode($rem['notifiedHours']  ?? '[]', true) ?: [];
            $total    = count($schedule);

            $deadline    = $rem['deadline'] ? date('d M Y, h:i A', strtotime($rem['deadline'])) : 'N/A';
            $remainingH  = $rem['deadline'] ? max(0, (int)ceil((strtotime($rem['deadline']) - $now) / 3600)) : 0;
            $name        = $rem['empName'] ?? 'Team Member';
            $title       = $rem['title']   ?? 'Reminder';
            $phone       = $rem['empPhone'] ?? '';
            $cfgId       = $rem['waConfigId'] ?: null;

            foreach ($schedule as $idx => $hour) {
                if ($elapsed < $hour)          continue; // not time yet
                if (in_array($hour, $notified)) continue; // already sent

                // Template by position
                if ($idx === $total - 1)            $tpl = 'critical';
                elseif ($idx / $total >= 0.66)      $tpl = 'high';
                elseif ($idx / $total >= 0.33)      $tpl = 'medium';
                else                                $tpl = 'initial';

                $msg = reminderTemplate($tpl, $name, $title, $deadline, $remainingH, $panelUrl);
                if ($phone) sendWA($phone, $msg, $cfgId);

                $notified[] = $hour;
                $pdo->prepare(
                    "UPDATE reminders SET reminderCount = reminderCount + 1,
                     notifiedHours = ?, lastRemindTs = ? WHERE rowid = ?"
                )->execute([json_encode($notified), date('c'), $rem['rowid']]);

                $pdo->prepare(
                    "INSERT INTO notifLog (type,empName,reminderTitle,channel,message,ts)
                     VALUES(?,?,?,?,?,?)"
                )->execute(['auto_reminder', $name, $title, '💬 WhatsApp (Auto-' . ucfirst($tpl) . ')', $msg, date('c')]);
                $sent++;

                // Final → auto-escalate
                if ($tpl === 'critical') {
                    $pdo->prepare(
                        "INSERT INTO cards (emp,empName,empId,empPhone,empEmail,reminderTitle,description,reason,reminders,timestamp)
                         VALUES(?,?,?,?,?,?,?,?,?,?)"
                    )->execute([
                        $rem['emp'], $name, $rem['empId'] ?? '', $phone, $rem['empEmail'] ?? '',
                        $title, '', 'Deadline reached without resolution',
                        ($rem['reminderCount'] ?? 0) + 1, date('c')
                    ]);
                    $pdo->prepare("UPDATE reminders SET escalated = 1 WHERE rowid = ?")->execute([$rem['rowid']]);

                    $cardMsg = "⚠️ *PENALTY CARD ISSUED*\n\nDear {$name},\n\nA Penalty Card has been raised automatically due to unresolved reminder.\n*Task:* {$title}\n\n🔗 {$panelUrl}\n\nFrom Avyukta CRM Team";
                    if ($phone) sendWA($phone, $cardMsg, $cfgId);
                    $pdo->prepare("INSERT INTO notifLog (type,empName,reminderTitle,channel,message,ts) VALUES(?,?,?,?,?,?)")
                        ->execute(['penalty_card', $name, $title, '💬 WhatsApp', $cardMsg, date('c')]);
                    $escalated++;
                }
            }
        }
        respond(['success' => true, 'sent' => $sent, 'escalated' => $escalated, 'checked' => count($rows)]);
    }

    /* ═══ AUTH REQUIRED ══════════════════════════════════ */

    case 'getCloudData': {
        requireAuth($payload);

        $employees = [];
        foreach ($pdo->query("SELECT * FROM employees") as $r) {
            $r['canAssign'] = (bool)$r['canAssign'];
            $employees[] = $r;
        }

        $reminders = [];
        foreach ($pdo->query("SELECT * FROM reminders ORDER BY rowid ASC") as $r) {
            $r['done']          = (bool)$r['done'];
            $r['escalated']     = (bool)($r['escalated'] ?? false);
            $r['reminder']      = (int)$r['reminderCount'];
            $r['autoInterval']  = (int)($r['autoInterval'] ?? 0);
            $r['totalDuration'] = (int)($r['totalDuration'] ?? 0);
            $r['replies']       = json_decode($r['replies']        ?? '[]', true) ?? [];
            $r['sharedWith']    = json_decode($r['sharedWith']     ?? '[]', true) ?? [];
            $r['notifySchedule']= json_decode($r['notifySchedule'] ?? '[]', true) ?? [];
            $r['notifiedHours'] = json_decode($r['notifiedHours']  ?? '[]', true) ?? [];
            $r['waConfigId']    = $r['waConfigId'] ? (int)$r['waConfigId'] : null;
            $r['desc']          = $r['description'];
            unset($r['reminderCount'], $r['description'], $r['rowid']);
            $reminders[] = $r;
        }

        $cards = [];
        foreach ($pdo->query("SELECT * FROM cards ORDER BY rowid ASC") as $r) {
            $r['reminders'] = (int)$r['reminders'];
            $r['desc']      = $r['description'];
            unset($r['description'], $r['rowid']);
            $cards[] = $r;
        }

        $notifLog = [];
        foreach ($pdo->query("SELECT * FROM notifLog ORDER BY rowid DESC LIMIT 200") as $r) {
            unset($r['rowid']);
            $notifLog[] = $r;
        }

        respond(['success' => true, 'data' => compact('employees', 'reminders', 'cards', 'notifLog')]);
    }

    case 'syncCloudData': {
        requireAuth($payload);
        $raw2 = $payload['cloudData'] ?? '';
        if (!$raw2) fail('No cloudData');
        $d = json_decode($raw2, true);
        if (!$d) fail('Invalid cloudData JSON');

        $pdo->beginTransaction();

        // Employees
        $pdo->exec("DELETE FROM employees");
        $sE = $pdo->prepare("INSERT INTO employees (user,id,name,phone,email,cCode,pNum,pass,department,role,canAssign) VALUES(?,?,?,?,?,?,?,?,?,?,?)");
        foreach ($d['employees'] ?? [] as $e) {
            $sE->execute([$e['user']??'',$e['id']??'',$e['name']??'',$e['phone']??'',$e['email']??'',$e['cCode']??'',$e['pNum']??'',$e['pass']??'',$e['department']??'',$e['role']??'',$e['canAssign']?1:0]);
        }

        // Reminders
        $pdo->exec("DELETE FROM reminders");
        $sR = $pdo->prepare(
            "INSERT INTO reminders (emp,empName,empId,empPhone,empEmail,cCode,pNum,title,description,
             waGroup,img,reminderCount,done,replies,autoInterval,sharedWith,lastRemindTs,timestamp,
             assignedBy,prevEmpName,totalDuration,deadline,notifySchedule,notifiedHours,escalated,waConfigId)
             VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
        );
        foreach ($d['reminders'] ?? [] as $r) {
            $sR->execute([
                $r['emp']??'',$r['empName']??'',$r['empId']??'',$r['empPhone']??'',$r['empEmail']??'',
                $r['cCode']??'',$r['pNum']??'',$r['title']??'',$r['desc']??'',$r['waGroup']??'',$r['img']??'',
                (int)($r['reminder']??0),$r['done']?1:0,json_encode($r['replies']??[]),
                (int)($r['autoInterval']??0),json_encode($r['sharedWith']??[]),
                $r['lastRemindTs']??'',$r['timestamp']??'',$r['assignedBy']??'',$r['prevEmpName']??'',
                (int)($r['totalDuration']??0),$r['deadline']??null,
                json_encode($r['notifySchedule']??[]),json_encode($r['notifiedHours']??[]),
                $r['escalated']?1:0,$r['waConfigId']??null
            ]);
        }

        // Cards
        $pdo->exec("DELETE FROM cards");
        $sC = $pdo->prepare("INSERT INTO cards (emp,empName,empId,empPhone,empEmail,reminderTitle,description,reason,reminders,timestamp) VALUES(?,?,?,?,?,?,?,?,?,?)");
        foreach ($d['cards'] ?? [] as $c) {
            $sC->execute([$c['emp']??'',$c['empName']??'',$c['empId']??'',$c['empPhone']??'',$c['empEmail']??'',$c['reminderTitle']??'',$c['desc']??'',$c['reason']??'',(int)($c['reminders']??0),$c['timestamp']??'']);
        }

        // NotifLog
        $pdo->exec("DELETE FROM notifLog");
        $sN = $pdo->prepare("INSERT INTO notifLog (type,empName,reminderTitle,channel,message,ts) VALUES(?,?,?,?,?,?)");
        foreach (array_slice($d['notifLog'] ?? [], 0, 200) as $n) {
            $sN->execute([$n['type']??'',$n['empName']??'',$n['reminderTitle']??'',$n['channel']??'',$n['message']??'',$n['ts']??'']);
        }

        $pdo->commit();
        respond(['success' => true]);
    }

    case 'sendWA': {
        requireAuth($payload);
        $phone   = $payload['phone']    ?? '';
        $msg     = $payload['message']  ?? '';
        $cfgId   = $payload['waConfigId'] ?? null;
        if (!$phone || !$msg) fail('Phone and message required');
        $result = sendWA($phone, $msg, $cfgId);
        respond(['success' => true, 'waResult' => $result]);
    }

    /* ── WA CONFIG CRUD ──────────────────────────── */

    case 'getWAConfig': {
        requireAuth($payload);
        $rows = $pdo->query(
            "SELECT id, name, instance_id, api_url, is_default FROM wa_config ORDER BY is_default DESC, id ASC"
        )->fetchAll();
        foreach ($rows as &$r) { $r['id'] = (int)$r['id']; $r['is_default'] = (bool)$r['is_default']; }
        respond(['success' => true, 'configs' => $rows]);
    }

    case 'saveWAConfig': {
        requireAuth($payload, true);
        $id    = $payload['id']           ?? null;
        $name  = trim($payload['name']    ?? '');
        $inst  = trim($payload['instance_id']  ?? '');
        $tok   = trim($payload['access_token'] ?? '');
        $url   = trim($payload['api_url'] ?? 'https://wa.clouddialer.in/api/v2/messages');
        $isDef = !empty($payload['is_default']);
        if (!$name || !$inst) fail('Name and Instance ID required');
        if ($isDef) $pdo->exec("UPDATE wa_config SET is_default = 0");
        if ($id) {
            if ($tok && $tok !== '***') {
                $pdo->prepare("UPDATE wa_config SET name=?,instance_id=?,access_token=?,api_url=?,is_default=? WHERE id=?")
                    ->execute([$name,$inst,$tok,$url,$isDef?1:0,$id]);
            } else {
                $pdo->prepare("UPDATE wa_config SET name=?,instance_id=?,api_url=?,is_default=? WHERE id=?")
                    ->execute([$name,$inst,$url,$isDef?1:0,$id]);
            }
        } else {
            if (!$tok) fail('Access Token required for new instance');
            $pdo->prepare("INSERT INTO wa_config (name,instance_id,access_token,api_url,is_default) VALUES(?,?,?,?,?)")
                ->execute([$name,$inst,$tok,$url,$isDef?1:0]);
            $id = $pdo->lastInsertId();
        }
        respond(['success' => true, 'id' => (int)$id]);
    }

    case 'deleteWAConfig': {
        requireAuth($payload, true);
        $id = $payload['id'] ?? null;
        if (!$id) fail('ID required');
        $pdo->prepare("DELETE FROM wa_config WHERE id = ?")->execute([$id]);
        respond(['success' => true]);
    }

    /* ── CRM SYNC ────────────────────────────────── */

    case 'fetchFromCRM': {
        requireAuth($payload, true);
        $ch = curl_init('https://urm.avyuktacrm.com/api/user_details.php');
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_SSL_VERIFYHOST=>false,CURLOPT_TIMEOUT=>20,CURLOPT_HTTPHEADER=>['Accept: application/json']]);
        $raw3 = curl_exec($ch); $curlErr = curl_error($ch); curl_close($ch);
        if (!$raw3) fail('CRM API unreachable: ' . $curlErr);
        $crmData = json_decode($raw3, true);
        if (!$crmData || empty($crmData['data'])) fail('Invalid CRM response');

        $chk = $pdo->prepare("SELECT user FROM employees WHERE user = ?");
        $ups = $pdo->prepare("INSERT INTO employees (user,id,name,phone,email,cCode,pNum,pass,department,role,canAssign) VALUES(?,?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE name=VALUES(name),phone=VALUES(phone),email=VALUES(email),cCode=VALUES(cCode),pNum=VALUES(pNum),pass=VALUES(pass),department=VALUES(department),role=VALUES(role),canAssign=VALUES(canAssign)");
        $added = $updated = 0;

        foreach ($crmData['data'] as $e) {
            $uid = trim($e['user_id'] ?? ''); if (!$uid) continue;
            $first = trim($e['name'] ?? ''); $last = trim($e['lastname'] ?? '');
            $fullName = ($last && $last !== 'NA') ? "$first $last" : $first;
            $rawMob = preg_replace('/\D/', '', $e['mob'] ?? '');
            $ccRaw  = preg_replace('/\D/', '', $e['countryCode'] ?? '91') ?: '91';
            $pNum = (strlen($rawMob) > 10 && strpos($rawMob, $ccRaw) === 0) ? substr($rawMob, strlen($ccRaw)) : $rawMob;
            $cCode = '+' . $ccRaw;
            $isSuperAdmin = ($e['superadmin_permission'] ?? '') === 'Y';
            $canAssign = ($isSuperAdmin || stripos($e['roles'] ?? '', 'admin') !== false) ? 1 : 0;

            $chk->execute([$uid]); $exists = $chk->fetchColumn();
            $ups->execute([$uid,$uid,$fullName,$cCode.' '.$pNum,$e['email']??'',$cCode,$pNum,$e['password']??'crm_default',$e['department']??'',$e['roles']??'',$canAssign]);
            if ($exists) $updated++; else $added++;
        }
        respond(['success' => true, 'added' => $added, 'updated' => $updated, 'total' => $added + $updated]);
    }

    /* ── AUDIT LOG ───────────────────────────────── */

    case 'auditLog': {
        requireAuth($payload);
        $pdo->prepare("INSERT INTO auditLog (user,activity,timestamp) VALUES(?,?,?)")
            ->execute([$payload['user']??'',$payload['activity']??'',$payload['timestamp']??date('c')]);
        respond(['success' => true]);
    }

    /* ── DEFAULT (acknowledge legacy audit calls) ── */

    default:
        requireAuth($payload);
        respond(['success' => true]);

    }

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    fail($e->getMessage());
}
