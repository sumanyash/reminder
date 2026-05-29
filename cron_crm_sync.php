<?php
// Auto-sync employees from CRM into the reminder panel DB.
// Cron: every 30 min — php /var/www/reminder/cron_crm_sync.php >> /var/log/reminder_crm_sync.log 2>&1

$ch = curl_init('https://reminder.clouddialer.in/api.php');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query(['data' => json_encode(['action' => 'fetchFromCRM'])]),
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT        => 30,
]);
$raw    = curl_exec($ch);
$curlErr = curl_error($ch);
curl_close($ch);

$ts = date('Y-m-d H:i:s');

if (!$raw) {
    echo "[{$ts}] CRM Sync FAILED — cURL error: {$curlErr}\n";
    exit(1);
}

$result = json_decode($raw, true);

if ($result && $result['success']) {
    if ($result['added'] > 0) {
        echo "[{$ts}] CRM Sync — Added: {$result['added']} new employees, Updated: {$result['updated']}\n";
    }
    // Silent when nothing changes
} else {
    $reason = $result['reason'] ?? 'unknown error';
    echo "[{$ts}] CRM Sync FAILED — {$reason}\n";
    exit(1);
}
