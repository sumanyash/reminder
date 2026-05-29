<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/config.php';

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

function respond($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function fail($reason) {
    respond(['success' => false, 'reason' => $reason]);
}

$rawInput = $_POST['data'] ?? '';
if (!$rawInput) fail('No data provided');

$payload = json_decode($rawInput, true);
if (!$payload) fail('Invalid JSON payload');

$action = $payload['action'] ?? '';

try {
    $pdo = getDB();

    switch ($action) {

        case 'getCloudData': {
            $employees = [];
            foreach ($pdo->query("SELECT * FROM employees") as $row) {
                $row['canAssign'] = (bool)$row['canAssign'];
                $employees[] = $row;
            }

            $reminders = [];
            foreach ($pdo->query("SELECT * FROM reminders ORDER BY rowid ASC") as $row) {
                $row['done']          = (bool)$row['done'];
                $row['reminder']      = (int)$row['reminderCount'];
                $row['autoInterval']  = (int)$row['autoInterval'];
                $row['replies']       = json_decode($row['replies'] ?? '[]', true) ?? [];
                $row['sharedWith']    = json_decode($row['sharedWith'] ?? '[]', true) ?? [];
                $row['desc']          = $row['description'];
                unset($row['reminderCount'], $row['description'], $row['rowid']);
                $reminders[] = $row;
            }

            $cards = [];
            foreach ($pdo->query("SELECT * FROM cards ORDER BY rowid ASC") as $row) {
                $row['reminders'] = (int)$row['reminders'];
                $row['desc']      = $row['description'];
                unset($row['description'], $row['rowid']);
                $cards[] = $row;
            }

            $notifLog = [];
            foreach ($pdo->query("SELECT * FROM notifLog ORDER BY rowid DESC LIMIT 200") as $row) {
                unset($row['rowid']);
                $notifLog[] = $row;
            }

            respond([
                'success' => true,
                'data'    => compact('employees', 'reminders', 'cards', 'notifLog'),
            ]);
        }

        case 'syncCloudData': {
            $cloudData = $payload['cloudData'] ?? '';
            if (!$cloudData) fail('No cloudData provided');
            $parsed = json_decode($cloudData, true);
            if (!$parsed) fail('Invalid cloudData JSON');

            $pdo->beginTransaction();

            // --- employees ---
            $pdo->exec("DELETE FROM employees");
            $stmtEmp = $pdo->prepare(
                "INSERT INTO employees (user,id,name,phone,email,cCode,pNum,pass,department,role,canAssign)
                 VALUES (:user,:id,:name,:phone,:email,:cCode,:pNum,:pass,:department,:role,:canAssign)"
            );
            foreach (($parsed['employees'] ?? []) as $e) {
                $stmtEmp->execute([
                    ':user'       => $e['user']       ?? '',
                    ':id'         => $e['id']         ?? '',
                    ':name'       => $e['name']       ?? '',
                    ':phone'      => $e['phone']      ?? '',
                    ':email'      => $e['email']      ?? '',
                    ':cCode'      => $e['cCode']      ?? '',
                    ':pNum'       => $e['pNum']       ?? '',
                    ':pass'       => $e['pass']       ?? '',
                    ':department' => $e['department'] ?? '',
                    ':role'       => $e['role']       ?? '',
                    ':canAssign'  => $e['canAssign']  ? 1 : 0,
                ]);
            }

            // --- reminders ---
            $pdo->exec("DELETE FROM reminders");
            $stmtRem = $pdo->prepare(
                "INSERT INTO reminders
                 (emp,empName,empId,empPhone,empEmail,cCode,pNum,title,description,waGroup,
                  img,reminderCount,done,replies,autoInterval,sharedWith,lastRemindTs,
                  timestamp,assignedBy,prevEmpName)
                 VALUES
                 (:emp,:empName,:empId,:empPhone,:empEmail,:cCode,:pNum,:title,:desc,:waGroup,
                  :img,:reminder,:done,:replies,:autoInterval,:sharedWith,:lastRemindTs,
                  :timestamp,:assignedBy,:prevEmpName)"
            );
            foreach (($parsed['reminders'] ?? []) as $r) {
                $stmtRem->execute([
                    ':emp'          => $r['emp']          ?? '',
                    ':empName'      => $r['empName']      ?? '',
                    ':empId'        => $r['empId']        ?? '',
                    ':empPhone'     => $r['empPhone']     ?? '',
                    ':empEmail'     => $r['empEmail']     ?? '',
                    ':cCode'        => $r['cCode']        ?? '',
                    ':pNum'         => $r['pNum']         ?? '',
                    ':title'        => $r['title']        ?? '',
                    ':desc'         => $r['desc']         ?? '',
                    ':waGroup'      => $r['waGroup']      ?? '',
                    ':img'          => $r['img']          ?? '',
                    ':reminder'     => (int)($r['reminder'] ?? 0),
                    ':done'         => $r['done'] ? 1 : 0,
                    ':replies'      => json_encode($r['replies']    ?? []),
                    ':autoInterval' => (int)($r['autoInterval']    ?? 0),
                    ':sharedWith'   => json_encode($r['sharedWith'] ?? []),
                    ':lastRemindTs' => $r['lastRemindTs'] ?? '',
                    ':timestamp'    => $r['timestamp']    ?? '',
                    ':assignedBy'   => $r['assignedBy']   ?? '',
                    ':prevEmpName'  => $r['prevEmpName']  ?? '',
                ]);
            }

            // --- cards ---
            $pdo->exec("DELETE FROM cards");
            $stmtCard = $pdo->prepare(
                "INSERT INTO cards
                 (emp,empName,empId,empPhone,empEmail,reminderTitle,description,reason,reminders,timestamp)
                 VALUES
                 (:emp,:empName,:empId,:empPhone,:empEmail,:reminderTitle,:desc,:reason,:reminders,:timestamp)"
            );
            foreach (($parsed['cards'] ?? []) as $c) {
                $stmtCard->execute([
                    ':emp'           => $c['emp']          ?? '',
                    ':empName'       => $c['empName']      ?? '',
                    ':empId'         => $c['empId']        ?? '',
                    ':empPhone'      => $c['empPhone']     ?? '',
                    ':empEmail'      => $c['empEmail']     ?? '',
                    ':reminderTitle' => $c['reminderTitle']?? '',
                    ':desc'          => $c['desc']         ?? '',
                    ':reason'        => $c['reason']       ?? '',
                    ':reminders'     => (int)($c['reminders'] ?? 0),
                    ':timestamp'     => $c['timestamp']    ?? '',
                ]);
            }

            // --- notifLog (keep latest 200) ---
            $pdo->exec("DELETE FROM notifLog");
            $stmtLog = $pdo->prepare(
                "INSERT INTO notifLog (type,empName,reminderTitle,channel,message,ts)
                 VALUES (:type,:empName,:reminderTitle,:channel,:message,:ts)"
            );
            foreach (array_slice($parsed['notifLog'] ?? [], 0, 200) as $n) {
                $stmtLog->execute([
                    ':type'          => $n['type']          ?? '',
                    ':empName'       => $n['empName']       ?? '',
                    ':reminderTitle' => $n['reminderTitle'] ?? '',
                    ':channel'       => $n['channel']       ?? '',
                    ':message'       => $n['message']       ?? '',
                    ':ts'            => $n['ts']            ?? '',
                ]);
            }

            $pdo->commit();
            respond(['success' => true]);
        }

        case 'fetchFromCRM': {
            $ch = curl_init('https://urm.avyuktacrm.com/api/user_details.php');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_TIMEOUT        => 20,
                CURLOPT_HTTPHEADER     => ['Accept: application/json'],
            ]);
            $raw = curl_exec($ch);
            $curlErr = curl_error($ch);
            curl_close($ch);

            if (!$raw) fail('CRM API unreachable: ' . $curlErr);

            $crmData = json_decode($raw, true);
            if (!$crmData || empty($crmData['data'])) fail('Invalid CRM response');

            $stmtCheck = $pdo->prepare("SELECT user FROM employees WHERE user = :user");
            $stmtUpsert = $pdo->prepare(
                "INSERT INTO employees (user,id,name,phone,email,cCode,pNum,pass,department,role,canAssign)
                 VALUES (:user,:id,:name,:phone,:email,:cCode,:pNum,:pass,:dept,:role,:canAssign)
                 ON DUPLICATE KEY UPDATE
                   name=VALUES(name), phone=VALUES(phone), email=VALUES(email),
                   cCode=VALUES(cCode), pNum=VALUES(pNum), pass=VALUES(pass),
                   department=VALUES(department), role=VALUES(role), canAssign=VALUES(canAssign)"
            );

            $added = 0; $updated = 0;

            foreach ($crmData['data'] as $emp) {
                $userId = trim($emp['user_id'] ?? '');
                if (!$userId) continue;

                // Full name
                $first = trim($emp['name'] ?? '');
                $last  = trim($emp['lastname'] ?? '');
                $fullName = ($last && $last !== 'NA') ? "$first $last" : $first;

                // Phone normalisation
                $rawMob = preg_replace('/\D/', '', $emp['mob'] ?? '');
                $ccRaw  = preg_replace('/\D/', '', $emp['countryCode'] ?? '91') ?: '91';
                // Strip leading country code if mob is too long
                if (strlen($rawMob) > 10 && strpos($rawMob, $ccRaw) === 0) {
                    $pNum = substr($rawMob, strlen($ccRaw));
                } else {
                    $pNum = $rawMob;
                }
                $cCode = '+' . $ccRaw;
                $phone = $cCode . ' ' . $pNum;

                $role   = $emp['roles'] ?? '';
                $dept   = $emp['department'] ?? '';
                $isSuperAdmin = ($emp['superadmin_permission'] ?? '') === 'Y';
                $canAssign = ($isSuperAdmin || stripos($role, 'admin') !== false) ? 1 : 0;

                $stmtCheck->execute([':user' => $userId]);
                $exists = $stmtCheck->fetchColumn();

                $stmtUpsert->execute([
                    ':user'      => $userId,
                    ':id'        => $userId,
                    ':name'      => $fullName,
                    ':phone'     => $phone,
                    ':email'     => $emp['email'] ?? '',
                    ':cCode'     => $cCode,
                    ':pNum'      => $pNum,
                    ':pass'      => $emp['password'] ?? 'crm_default',
                    ':dept'      => $dept,
                    ':role'      => $role,
                    ':canAssign' => $canAssign,
                ]);

                if ($exists) $updated++; else $added++;
            }

            respond(['success' => true, 'added' => $added, 'updated' => $updated, 'total' => $added + $updated]);
        }

        case 'auditLog': {
            $stmt = $pdo->prepare(
                "INSERT INTO auditLog (user,activity,timestamp) VALUES (:user,:activity,:timestamp)"
            );
            $stmt->execute([
                ':user'      => $payload['user']      ?? '',
                ':activity'  => $payload['activity']  ?? '',
                ':timestamp' => $payload['timestamp'] ?? date('c'),
            ]);
            respond(['success' => true]);
        }

        case 'test': {
            $pdo->query("SELECT 1");
            respond(['success' => true, 'message' => 'Database connection OK']);
        }

        default: {
            // All other sheet-audit actions (addEmployee, addReminder, reminder, etc.)
            // are acknowledged — the canonical data is persisted via syncCloudData.
            respond(['success' => true]);
        }
    }

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fail($e->getMessage());
}
