<?php
// Auto-reminder engine — runs every 5 minutes via cron
// Cron: */5 * * * * php /var/www/reminder/cron_reminder.php >> /var/log/reminder_auto.log 2>&1

require_once __DIR__ . '/config.php';

$ch = curl_init(APP_DOMAIN . '/api.php');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query(['data' => json_encode([
        'action'     => 'processAutoReminders',
        'cronSecret' => CRON_SECRET,
    ])]),
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT        => 60,
]);
$raw     = curl_exec($ch);
$curlErr = curl_error($ch);
curl_close($ch);

$ts = date('Y-m-d H:i:s');

if (!$raw) {
    echo "[{$ts}] FAILED — cURL: {$curlErr}\n";
    exit(1);
}

$res = json_decode($raw, true);

if ($res && $res['success']) {
    if (($res['sent'] ?? 0) > 0 || ($res['escalated'] ?? 0) > 0) {
        echo "[{$ts}] Sent: {$res['sent']} notifications | Escalated: {$res['escalated']} | Checked: {$res['checked']}\n";
    }
    // Silent when nothing happened
} else {
    echo "[{$ts}] FAILED — " . ($res['reason'] ?? 'unknown') . "\n";
    exit(1);
}
