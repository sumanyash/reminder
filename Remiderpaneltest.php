<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Avyukta Intellicall - Reminder panel</title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📞</text></svg>">
<link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&family=Syne:wght@400;500;600;700&family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

<style>
:root {
  --bg: #07090f;
  --s1: #0e1220;
  --s2: #131929;
  --s3: #1c2538;
  --s4: #232e45;
  --b1: rgba(255,255,255,0.06);
  --b2: rgba(255,255,255,0.11);
  --b3: rgba(255,255,255,0.18);
  --acc: #3b82f6;
  --acc2: #6366f1;
  --ok: #22c55e;
  --warn: #f59e0b;
  --err: #ef4444;
  --txt: #dde4f0;
  --txt2: #8695b0;
  --txt3: #4a5a74;
  --login-grad: #0d1f3c;
  --r: 12px;
  --r2: 16px;
}

/* Full dark mode toggle */
[data-theme="dark"] {
  --bg:   #070b17;
  --s1:   rgba(12,20,40,.96);
  --s2:   rgba(18,28,52,.95);
  --s3:   rgba(26,40,70,.92);
  --s4:   rgba(38,55,90,.9);
  --b1:   rgba(255,255,255,0.07);
  --b2:   rgba(255,255,255,0.12);
  --b3:   rgba(255,255,255,0.20);
  --txt:  #e2e8f0;
  --txt2: #8fa5c8;
  --txt3: #5f7a9e;
  --acc:  #4a90d9;
  --acc2: #3b6fba;
  --ok:   #22c55e;
  --warn: #f59e0b;
  --err:  #ef4444;
  --login-grad: #0d1f3c;
}

*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--txt);min-height:100vh;overflow-x:hidden; transition: background 0.3s, color 0.3s;}

/* ── BASE64 IMAGE LOGO CONFIG ── */
.base64-logo { width: 100%; height: 100%; object-fit: contain; border-radius: 50%; background-color: transparent; }

/* ── LOGIN ── */
#loginPage{min-height:100vh;display:flex;align-items:center;justify-content:center;background:radial-gradient(ellipse 80% 60% at 50% 0%,var(--login-grad),var(--bg) 70%); padding: 1rem; transition: background 0.3s;}
.login-card{width:100%; max-width:400px; background:var(--s1);border:1px solid var(--b2);border-radius:20px;padding:2.5rem 2rem;box-shadow:0 32px 80px rgba(0,0,0,0.5); transition: background 0.3s, border-color 0.3s;}
.brand{text-align:center;margin-bottom:2rem;}
.brand-icon{width:70px;height:70px;background:transparent;display:inline-flex;align-items:center;justify-content:center;margin-bottom:.75rem;}
.brand h1{font-family:'Jost',sans-serif;font-style:italic;font-size:24px;font-weight:700;letter-spacing:-.5px;}
.brand p{font-family:'Jost',sans-serif;font-style:italic;color:var(--txt2);font-size:14px;margin-top:4px;}

/* ── FORM ELEMENTS ── */
.fg{margin-bottom:1rem;}
.fg label{display:block;font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:.8px;color:var(--txt3);margin-bottom:6px;}
.fg input,.fg select,.fg textarea{
  width:100%;background:var(--s2);border:1px solid var(--b1);color:var(--txt);
  border-radius:var(--r);padding:10px 14px;font-family:'DM Sans',sans-serif;
  font-size:14px;outline:none;transition:border-color .2s, background 0.3s, color 0.3s;
}
.fg input:focus,.fg select:focus,.fg textarea:focus{border-color:var(--acc);}
.fg input:disabled { opacity: 0.6; cursor: not-allowed; }
.fg select option{background:var(--s1); color:var(--txt);}
.fg textarea{resize:vertical;min-height:80px;line-height:1.6;}
.row2{display:grid;grid-template-columns:1fr 1fr;gap:12px;}

/* ── BUTTONS ── */
.btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:9px 18px;border:none;border-radius:var(--r);font-family:'DM Sans',sans-serif;font-size:13px;font-weight:500;cursor:pointer;transition:all .15s;}
.btn-primary{background:var(--acc);color:#fff;}
.btn-primary:hover:not(:disabled){background:#2563eb;transform:translateY(-1px);}
.btn-primary:disabled { opacity:0.6; cursor:not-allowed; }
.btn-full{width:100%;padding:12px;}
.btn-ghost{background:var(--s3);color:var(--txt2);border:1px solid var(--b1);}
.btn-ghost:hover{background:var(--s4);color:var(--txt);}
.btn-ok{background:rgba(34,197,94,.14);color:var(--ok);border:1px solid rgba(34,197,94,.25);}
.btn-ok:hover{background:rgba(34,197,94,.22);}
.btn-warn{background:rgba(245,158,11,.14);color:var(--warn);border:1px solid rgba(245,158,11,.25);}
.btn-warn:hover{background:rgba(245,158,11,.22);}
.btn-err{background:rgba(239,68,68,.14);color:var(--err);border:1px solid rgba(239,68,68,.25);}
.btn-err:hover{background:rgba(239,68,68,.22);}
.btn-sm{padding:6px 12px;font-size:12px;border-radius:7px;}
.btn-xs{padding:4px 9px;font-size:11px;border-radius:6px;}

/* ── EXPORT BAR & ICONS ── */
.export-bar{display:flex;gap:10px;align-items:center;background:var(--s1);padding:12px 16px;border-radius:var(--r);border:1px solid var(--b1);margin-bottom:1.25rem;flex-wrap:wrap;}
.export-bar select, .export-bar input[type="date"]{background:var(--s2);border:1px solid var(--b1);color:var(--txt);padding:7px 12px;border-radius:6px;font-size:12px;outline:none;font-family:'DM Sans',sans-serif;}
.export-bar .sep{width:1px;height:24px;background:var(--b1);margin:0 5px;}
.ico-btn { display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; border-radius:8px; border:1px solid var(--b1); background:var(--s2); cursor:pointer; transition:all 0.2s; color:var(--txt2); }
.ico-btn:hover { background:var(--s3); transform:translateY(-2px); border-color:var(--b3); }
.ico-csv:hover { color:#10b981; border-color:rgba(16,185,129,0.3); }
.ico-xls:hover { color:#059669; border-color:rgba(5,150,105,0.3); }
.ico-pdf:hover { color:#ef4444; border-color:rgba(239,68,68,0.3); }
.admin-export-btns.hidden { display:none !important; }

/* ── LAYOUT & RESPONSIVENESS ── */
#appPage{display:none;}
/* Hide login instantly when restoring session — prevents white flash */
.session-loading #loginPage{display:none!important;}
.session-loading #appPage{display:none!important;}
.layout{display:flex;min-height:100vh;}
.sidebar{width:240px;flex-shrink:0;background:var(--s1);border-right:1px solid var(--b1);display:flex;flex-direction:column;position:fixed;top:0;left:0;height:100vh;z-index:100; transition: transform 0.3s ease, background 0.3s;}
.main{margin-left:240px;padding:2rem;flex:1; min-width: 0;} 

/* Mobile Elements */
.mobile-topbar { display: none; align-items: center; justify-content: space-between; padding: 1rem 1.5rem; background: var(--s1); border-bottom: 1px solid var(--b1); position: sticky; top: 0; z-index: 90; }
.mobile-nav-btn { background: transparent; border: none; color: var(--txt); font-size: 24px; cursor: pointer; }
.mobile-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 95; backdrop-filter: blur(2px); }
.mobile-overlay.on { display: block; }

@media (max-width: 768px) {
  .sidebar { transform: translateX(-100%); }
  .sidebar.open { transform: translateX(0); box-shadow: 4px 0 20px rgba(0,0,0,0.5); }
  .main { margin-left: 0; padding: 1rem; width: 100%; }
  .mobile-topbar { display: flex; }
  .stats { grid-template-columns: 1fr 1fr; gap: 0.75rem; }
  .row2 { grid-template-columns: 1fr; }
  .export-bar { flex-direction: column; align-items: flex-start; }
  .export-bar select, .export-bar input[type="date"] { width: 100%; }
  .export-bar .sep { display: none; }
  .modal { width: 95vw; padding: 1.5rem; }
}

@media (max-width: 480px) {
  .stats { grid-template-columns: 1fr; }
  .reminder-card { flex-direction: column; }
  .reminder-thumb { width: 100%; height: 180px; }
  .reply-area { flex-direction: column; }
  .reply-area button { width: 100%; justify-content: center; }
  .chat-widget { width: 90vw; right: 5vw; bottom: 20px; }
}

/* ── SIDEBAR INTERNALS ── */
.sb-logo{display:flex;align-items:center;gap:10px;padding:1.25rem 1.25rem 1rem;border-bottom:1px solid var(--b1);}
.sb-logo .icon{width:36px;height:36px;background:transparent;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.sb-logo .title{font-family:'Jost',sans-serif;font-style:italic;font-size:18px;font-weight:700;line-height:1.2;}
.sb-logo .sub{font-family:'Jost',sans-serif;font-style:italic;font-size:10px;color:var(--txt3);letter-spacing:1px;}
.nav-section{padding:.75rem 1rem .25rem;font-size:10px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:var(--txt3);}
.nav-item{display:flex;align-items:center;gap:10px;padding:9px 1.25rem;color:var(--txt2);font-size:14px;cursor:pointer;border-left:2px solid transparent;transition:all .15s;position:relative;}
.nav-item:hover{color:var(--txt);background:var(--s2);}
.nav-item.active{color:var(--acc);border-left-color:var(--acc);background:rgba(59,130,246,.08);}
.nav-item .ni{font-size:15px;width:20px;text-align:center;}
.nav-badge{margin-left:auto;background:var(--err);color:#fff;border-radius:20px;font-size:10px;padding:2px 7px;font-family:'DM Mono',monospace;}
.sb-footer{margin-top:auto;padding:1rem 1.25rem;border-top:1px solid var(--b1);}
.user-chip{display:flex;align-items:center;gap:10px;}
.avatar{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;flex-shrink:0;font-family:'Syne',sans-serif; color:#ffffff;}
.av-admin{background:linear-gradient(135deg,var(--acc2),var(--acc));}
.av-emp{background:linear-gradient(135deg,#0891b2,#06b6d4);}
.uc-info .name{font-size:13px;font-weight:500;}
.uc-info .role{font-size:11px;color:var(--txt3);}
.btn-out{background:none;border:none;color:var(--txt3);cursor:pointer;font-size:18px;transition:color .15s;}
.btn-out:hover{color:var(--err);}

/* ── MAIN INTERNALS ── */
.ph { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; }
.ph-left { flex: 1 1 200px; }
.ph-right { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }
.ph-left h2{font-family:'Syne',sans-serif;font-size:22px;font-weight:700;letter-spacing:-.3px;}
.ph-left p{color:var(--txt2);font-size:13px;margin-top:3px;}

/* ── BENTO DASHBOARD CARDS & STATS ── */
.card{background:var(--s1);border:1px solid var(--b1);border-radius:var(--r2);padding:1.5rem;margin-bottom:1.25rem; transition: background 0.3s, border-color 0.3s;}
.ch{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;}
.ct{font-family:'Syne',sans-serif;font-size:15px;font-weight:600;}

.stats{display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:1.25rem;margin-bottom:1.75rem;}
.stat{background:var(--s1);border:1px solid var(--b1);border-radius:var(--r2);padding:1.5rem; display:flex; flex-direction:column; position:relative; overflow:hidden; transition: transform 0.2s, background 0.3s, border-color 0.3s;}
.stat:hover { transform: translateY(-3px); border-color:var(--b2); }
.stat-icon { position:absolute; top:1.25rem; right:1.5rem; font-size:24px; opacity:0.15; }
.stat .s-label{font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.6px;color:var(--txt2);margin-bottom:12px;}
.stat .s-val{font-size:36px;font-weight:700;font-family:'DM Mono',monospace;line-height:1;}
.stat .s-sub{font-size:12px;color:var(--txt3);margin-top:8px;}

/* ── TABLE ── */
.tbl-wrap{overflow-x:auto; -webkit-overflow-scrolling: touch; border-radius: 8px;}
table{width:100%;border-collapse:collapse;font-size:13px; min-width: 600px;}
th{background:var(--s2);color:var(--txt3);font-size:11px;font-weight:600;letter-spacing:.6px;text-transform:uppercase;padding:12px 14px;text-align:left;border-bottom:1px solid var(--b1);}
td{padding:14px;border-bottom:1px solid var(--b1);color:var(--txt);vertical-align:middle;}
tr:last-child td{border-bottom:none;}
tr:hover td{background:var(--s2);}

/* ── BADGES & CARDS ── */
.badge{display:inline-flex;align-items:center;gap:3px;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:500;}
.b-pending{background:rgba(245,158,11,.14);color:var(--warn);}
.b-done{background:rgba(34,197,94,.14);color:var(--ok);}
.b-esc{background:rgba(239,68,68,.14);color:var(--err);}
.b-warn{background:rgba(245,158,11,.14);color:var(--warn);}
.b-info{background:rgba(59,130,246,.14);color:var(--acc);}
.b-auto{background:rgba(99,102,241,.14);color:#a5b4fc;}

.rem-pip{display:inline-flex;align-items:center;gap:5px;font-size:11px;background:var(--s3);border:1px solid var(--b1);border-radius:20px;padding:4px 10px;font-family:'DM Mono',monospace;color:var(--txt2);}
.rem-pip.hot{border-color:rgba(239,68,68,.35);color:var(--err);}

.reminder-card{background:var(--s1);border:1px solid var(--b1);border-radius:var(--r2);padding:1.5rem;margin-bottom:1rem;display:flex;gap:1.25rem;transition:border-color .2s, background 0.3s;}
.reminder-card:hover{border-color:var(--b2);}
.reminder-card.esc{border-left:3px solid var(--err);}
.reminder-card.done-card{opacity:.6;}
.reminder-thumb{width:90px;height:90px;border-radius:var(--r);object-fit:cover;flex-shrink:0;border:1px solid var(--b1);cursor:pointer;transition:transform 0.2s;}
.reminder-thumb:hover{transform:scale(1.05);}
.reminder-body{flex:1;min-width:0;}
.reminder-title{font-family:'Syne',sans-serif;font-size:16px;font-weight:600;margin-bottom:6px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
.reminder-desc{font-size:13px;color:var(--txt2);line-height:1.6;margin-bottom:12px; word-break:break-word;}
.reminder-meta{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px;align-items:center;}
.reply-area{display:flex;gap:8px;margin-top:12px; flex-wrap:wrap;}
.reply-area input{flex:1;background:var(--s2);border:1px solid var(--b1);border-radius:8px;padding:10px 14px;color:var(--txt);font-size:13px;font-family:'DM Sans',sans-serif;outline:none; min-width:150px;}
.reply-area input:focus{border-color:var(--acc);}

.pen-item{background:var(--s1);border:1px solid var(--b1);border-left:3px solid var(--err);border-radius:var(--r2);padding:1.25rem;margin-bottom:1rem;cursor:pointer;display:flex;align-items:center;justify-content:space-between;transition:background .15s, border-color .3s;}
.pen-item:hover{background:var(--s2);}

/* ── MODALS & EXTRAS ── */
.overlay{position:fixed;inset:0;background:rgba(0,0,0,.7);display:none;align-items:center;justify-content:center;z-index:999;}
.overlay.on{display:flex;}
.modal{background:var(--s1);border:1px solid var(--b2);border-radius:24px;padding:2rem;width:460px;max-width:92vw; transition: background 0.3s;}
.modal h3{font-family:'Syne',sans-serif;font-size:18px;font-weight:700;margin-bottom:1.5rem;}
.mf{margin-bottom:.75rem;}
.mf-l{font-size:10px;text-transform:uppercase;letter-spacing:.6px;color:var(--txt3);margin-bottom:4px;}
.mf-v{font-size:14px;color:var(--txt); word-break:break-word;}
.modal-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:1.5rem; flex-wrap:wrap;}
.notif-preview{background:var(--s2);border:1px solid var(--b1);border-radius:var(--r);padding:1.25rem;font-size:13px;color:var(--txt2);line-height:1.7;margin-bottom:1rem;font-family:'DM Mono',monospace;white-space:pre-wrap;}
.toast{position:fixed;bottom:24px;right:24px;background:var(--s1);border:1px solid var(--b2);border-radius:var(--r);padding:14px 20px;font-size:13px;color:var(--txt);z-index:9999;opacity:0;transform:translateY(6px);transition:all .25s;display:flex;align-items:center;gap:12px;max-width:320px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);}
.toast.on{opacity:1;transform:translateY(0);}
.sheet-pill{display:flex;align-items:center;gap:6px;font-size:12px;color:var(--txt3);padding:6px 12px;background:var(--s2);border:1px solid var(--b1);border-radius:20px;}
.dot{width:7px;height:7px;border-radius:50%;background:var(--ok);animation:blink 2s infinite;}
.dot.off{background:var(--txt3);animation:none;}
@keyframes blink{0%,100%{opacity:1;}50%{opacity:.25};}
.file-drop{display:flex;align-items:center;gap:10px;background:var(--s2);border:1px dashed var(--b2);border-radius:var(--r);padding:12px 14px;cursor:pointer;font-size:13px;color:var(--txt2);transition:border-color .2s;}
.file-drop:hover{border-color:var(--acc);color:var(--txt);}
input[type=file]{display:none;}
.sg h4{font-size:14px;font-weight:600;color:var(--txt2);margin-bottom:1rem;padding-bottom:.5rem;border-bottom:1px solid var(--b1);}
.sg{margin-bottom:1.75rem;}
.settings-note{font-size:12px;color:var(--txt3);line-height:1.7;margin:.5rem 0 1rem;}
.hidden{display:none!important;}
.sp{display:none;}
.sp.on{display:block;}
.admin-only-tag{display:inline-flex;align-items:center;gap:4px;font-size:10px;background:rgba(99,102,241,.15);color:#a5b4fc;border:1px solid rgba(99,102,241,.25);border-radius:20px;padding:3px 8px;}
.notif-log{font-size:13px;background:var(--s2);border-radius:var(--r);padding:1rem;margin-top:.5rem; word-break:break-word;}
.nl-item{display:flex;gap:12px;padding:8px 0;border-bottom:1px solid var(--b1);align-items:flex-start;}
.nl-item:last-child{border-bottom:none;}
.nl-ico{font-size:14px;flex-shrink:0;margin-top:2px;}
.nl-text{flex:1;color:var(--txt2);}
.sync-status{font-size:11px;padding:4px 10px;border-radius:20px;font-family:'DM Mono',monospace;}
.sync-ok{background:rgba(34,197,94,.12);color:var(--ok);border:1px solid rgba(34,197,94,.2);}
.sync-err{background:rgba(239,68,68,.12);color:var(--err);border:1px solid rgba(239,68,68,.2);}
hr{border:none;border-top:1px solid var(--b1);margin:1.5rem 0;}

/* ── FLOATING CHAT WIDGET ── */
.chat-widget { position: fixed; bottom: 24px; right: 24px; width: 350px; height: 480px; background: var(--s1); border: 1px solid var(--b2); border-radius: 16px; box-shadow: 0 15px 40px rgba(0,0,0,0.6); display: none; flex-direction: column; z-index: 9999; overflow: hidden; transition: background 0.3s; }
.chat-widget.show { display: flex; animation: slideUp 0.3s ease; }
@keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.cw-header { background: var(--s2); padding: 14px 18px; border-bottom: 1px solid var(--b1); display: flex; justify-content: space-between; align-items: center; }
.cw-title { font-weight: 600; font-size: 14px; color: var(--txt); }
.cw-sub { font-size: 11px; color: var(--txt3); max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px; }
.cw-header > button { background: none; border: none; color: var(--txt2); cursor: pointer; font-size: 18px; width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
.cw-header > button:hover { color: var(--err); background: rgba(239,68,68,0.1); }
.cw-body { flex: 1; overflow-y: auto; padding: 14px; display: flex; flex-direction: column; gap: 10px; background: var(--bg); }
.cw-footer { padding: 14px; background: var(--s2); border-top: 1px solid var(--b1); display: flex; gap: 8px; }
.cw-footer input { flex: 1; background: var(--s1); border: 1px solid var(--b1); border-radius: 20px; padding: 10px 16px; color: var(--txt); font-size: 13px; outline: none; font-family: 'DM Sans', sans-serif; }
.cw-footer input:focus { border-color: var(--acc); }
.cw-footer button { background: var(--acc); border: none; color: #fff; width: 38px; height: 38px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 14px; transition: transform 0.15s; }
.cw-footer button:hover { transform: scale(1.05); }

.chat-msg { max-width:85%; display:flex; flex-direction:column; }
.chat-msg.me { align-self:flex-end; }
.chat-msg.other { align-self:flex-start; }
.chat-msg .name { font-size:10px; color:var(--txt3); margin-bottom:4px; }
.chat-msg.me .name { text-align:right; }
.chat-msg.other .name { text-align:left; }
.chat-msg .bubble { padding:10px 14px; border-radius:12px; font-size:13px; color:#fff; word-break:break-word; line-height: 1.5; }
.chat-msg.me .bubble { background:var(--acc); border-bottom-right-radius: 4px; }
.chat-msg.other .bubble { background:var(--s3); border:1px solid var(--b1); border-bottom-left-radius: 4px; }
.chat-msg .time { font-size:9px; color:var(--txt3); margin-top:4px; }
.chat-msg.me .time { text-align:right; }
.chat-msg.other .time { text-align:left; }
.cw-add-user-btn { background:var(--acc); color:#fff; border:none; font-size:10px; padding:3px 8px; border-radius:12px; cursor:pointer; margin-left:8px; font-family:'DM Sans',sans-serif; }
.cw-add-user-btn:hover { background:#2563eb; }

/* IMAGE VIEWER MODAL CSS */
.img-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.85); display: none; align-items: center; justify-content: center; z-index: 9999; cursor: pointer; }
.img-overlay.on { display: flex; }
#imgModalContent { max-width: 90vw; max-height: 90vh; border-radius: var(--r); box-shadow: 0 10px 40px rgba(0,0,0,0.5); object-fit: contain; }
</style>
<style>
/* ══ DESIGN SYSTEM — Avyukta Reminder Panel ══════════ */
:root{
  --bg:#eef2f8;--s1:#fff;--s2:#f4f7fb;--s3:#e8eef6;--s4:#dae3f0;
  --b1:rgba(0,0,0,.06);--b2:rgba(0,0,0,.11);--b3:rgba(0,0,0,.19);
  --acc:#1565c0;--acc2:#0d47a1;--ok:#16a34a;--warn:#d97706;--err:#dc2626;
  --txt:#0f172a;--txt2:#2e4a7a;--txt3:#5c7499;
  --r:10px;--r2:14px;
  --sb-bg:#1b2544;--sb-border:rgba(255,255,255,.07);
  --sb-txt:#c5d3ec;--sb-txt2:#7a96c4;
  --sb-hover:rgba(255,255,255,.07);--sb-active:rgba(25,101,192,.25);--sb-accent:#4a90d9;
}
html{scroll-behavior:smooth;-webkit-tap-highlight-color:transparent;}
body{background:var(--bg);color:var(--txt);transition:background .3s,color .3s;}
body::before{display:none;}
::selection{background:rgba(21,101,192,.18);color:inherit;}

/* ── LOGIN ── */
#loginPage{background:radial-gradient(ellipse 85% 55% at 50% 0%,#1e3a6a,#0b1525 70%);}
.login-card{background:rgba(18,28,52,.97);border:1px solid rgba(255,255,255,.09);border-radius:20px;padding:2rem 1.75rem;box-shadow:0 24px 60px rgba(0,0,0,.45);backdrop-filter:blur(20px);}
.brand h1{color:#fff;font-size:22px;}.brand p{color:rgba(255,255,255,.4);}
.brand-icon{background:linear-gradient(135deg,#1565c0,#0d47a1);border-radius:16px;box-shadow:0 10px 24px rgba(13,71,161,.35);}
#loginPage .fg label{color:rgba(255,255,255,.4);font-weight:600;}
#loginPage .fg input,#loginPage .fg select{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:#fff;border-radius:12px;padding:12px 14px;}
#loginPage .fg input::placeholder{color:rgba(255,255,255,.28);}
#loginPage .fg input:focus,#loginPage .fg select:focus{border-color:rgba(74,144,217,.75);box-shadow:0 0 0 3px rgba(74,144,217,.14);background:rgba(255,255,255,.09);}
#loginPage .btn-primary{background:linear-gradient(135deg,#1565c0,#0d47a1);padding:13px;}

/* ── SIDEBAR (always dark) ── */
.sidebar{background:var(--sb-bg)!important;border-right:1px solid var(--sb-border)!important;width:220px;}
.sb-logo{padding:1.1rem 1rem .9rem;border-bottom:1px solid var(--sb-border)!important;}
.sb-logo .title{color:#fff!important;font-size:15px;}.sb-logo .sub{color:var(--sb-txt2)!important;}
.sb-logo .icon{width:36px;height:36px;border-radius:12px;}
.nav-section{padding:.55rem 1rem .2rem;color:var(--sb-txt2)!important;font-size:9.5px;}
.nav-item{padding:9px 1rem;color:var(--sb-txt)!important;border-left:2px solid transparent;font-size:13px;}
.nav-item:hover{background:var(--sb-hover)!important;color:#fff!important;}
.nav-item.active{background:var(--sb-active)!important;border-left-color:var(--sb-accent)!important;color:#fff!important;}
.sb-footer{border-top:1px solid var(--sb-border)!important;padding:.85rem 1rem;}
.uc-info .name{color:#fff!important;font-size:12px;}.uc-info .role{color:var(--sb-txt2)!important;font-size:10px;}
.btn-out{color:var(--sb-txt2)!important;}.btn-out:hover{color:#f87171!important;}
.av-admin{background:linear-gradient(135deg,#1565c0,#0d47a1);}

/* ── MOBILE TOPBAR ── */
.mobile-topbar{background:var(--sb-bg)!important;border-bottom:1px solid var(--sb-border)!important;padding:.7rem 1rem;position:sticky;top:0;z-index:90;}
.mobile-topbar .title-text{color:#fff;font-size:16px;font-family:'Jost',sans-serif;font-style:italic;font-weight:700;}
.mobile-nav-btn{color:rgba(255,255,255,.7)!important;font-size:20px;}

/* ── LAYOUT / MAIN ── */
.main{padding:1.35rem 1.75rem;background:var(--bg);}
.ph{margin-bottom:1rem;}.ph-left h2{font-size:19px;}.ph-left p{font-size:12px;color:var(--txt3);margin-top:2px;}

/* ── CARDS ── */
.card{background:var(--s1);border:1px solid var(--b1);border-radius:var(--r2);padding:1.25rem;box-shadow:0 1px 6px rgba(0,0,0,.05);transition:box-shadow .2s,transform .2s;}
.card:hover{box-shadow:0 4px 16px rgba(0,0,0,.09);}
.ch{margin-bottom:1rem;}.ct{font-size:14px;}

/* ── STATS ── */
.stats{grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:.875rem;margin-bottom:1.1rem;}
.stat{background:var(--s1);border:1px solid var(--b1);border-radius:var(--r2);padding:1rem 1.1rem;box-shadow:0 1px 5px rgba(0,0,0,.04);transition:all .2s;}
.stat:hover{border-color:rgba(21,101,192,.2);transform:translateY(-2px);box-shadow:0 6px 18px rgba(0,0,0,.09);}
.stat .s-label{font-size:10.5px;margin-bottom:6px;}.stat .s-val{font-size:28px;}.stat .s-sub{font-size:10.5px;margin-top:4px;}

/* ── TABLE ── */
.tbl-wrap{border-radius:10px;overflow-x:auto;-webkit-overflow-scrolling:touch;}
table{font-size:12.5px;}
th{background:#f0f4fa;color:var(--txt3);padding:9px 12px;font-size:10px;white-space:nowrap;}
td{padding:10px 12px;}
tr:hover td{background:#f7f9fd;}
.tbl-wrap::-webkit-scrollbar{height:4px;}.tbl-wrap::-webkit-scrollbar-thumb{background:var(--b2);border-radius:4px;}

/* ── BUTTONS ── */
.btn{border-radius:9px;font-weight:600;transition:all .18s;font-size:13px;}
.btn:hover:not(:disabled){transform:translateY(-1px);}
.btn-primary{background:linear-gradient(135deg,#1565c0,#0d47a1);color:#fff;box-shadow:0 3px 10px rgba(13,71,161,.22);}
.btn-primary:hover:not(:disabled){box-shadow:0 6px 18px rgba(13,71,161,.3);}
.btn-ghost{background:var(--s2);border:1px solid var(--b2);color:var(--txt2);}
.btn-ghost:hover{background:var(--s3);color:var(--txt);}
.btn-sm{padding:7px 13px;font-size:12px;border-radius:8px;}
.btn-xs{padding:4px 9px;font-size:11px;border-radius:7px;}
.btn-full{padding:12px;width:100%;}
.btn-ok{background:rgba(22,163,74,.1);color:var(--ok);border:1px solid rgba(22,163,74,.2);}
.btn-ok:hover{background:rgba(22,163,74,.18);}
.btn-warn{background:rgba(217,119,6,.1);color:var(--warn);border:1px solid rgba(217,119,6,.2);}
.btn-warn:hover{background:rgba(217,119,6,.18);}
.btn-err{background:rgba(220,38,38,.1);color:var(--err);border:1px solid rgba(220,38,38,.2);}
.btn-err:hover{background:rgba(220,38,38,.18);}

/* ── EXPORT BAR ── */
.export-bar{background:var(--s1);border:1px solid var(--b1);border-radius:var(--r);padding:10px 14px;margin-bottom:1rem;}
.export-bar select,.export-bar input[type="date"]{background:var(--s2);border:1px solid var(--b1);color:var(--txt);border-radius:7px;padding:8px 11px;font-size:12px;}
.ico-btn{width:36px;height:36px;border-radius:8px;border:1px solid var(--b1);background:var(--s2);}
.ico-btn:hover{background:var(--s3);border-color:var(--b2);}

/* ── FORMS ── */
.fg{margin-bottom:.875rem;}
.fg label{font-weight:600;letter-spacing:.05em;font-size:10.5px;color:var(--txt3);}
.fg input,.fg select,.fg textarea{background:var(--s2);border:1px solid var(--b2);color:var(--txt);border-radius:9px;padding:10px 13px;font-size:13px;transition:border-color .2s,box-shadow .2s;}
.fg input:focus,.fg select:focus,.fg textarea:focus{border-color:rgba(21,101,192,.55);box-shadow:0 0 0 3px rgba(21,101,192,.09);background:var(--s1);}
.fg input:disabled{opacity:.55;cursor:not-allowed;}
.fg select option{background:var(--s1);color:var(--txt);}
.fg textarea{min-height:70px;resize:vertical;line-height:1.6;}
.row2{display:grid;grid-template-columns:1fr 1fr;gap:12px;}

/* ── REMINDER CARDS ── */
.reminder-card{background:var(--s1);border:1px solid var(--b1);border-radius:var(--r2);padding:.875rem 1rem;margin-bottom:.65rem;box-shadow:0 1px 5px rgba(0,0,0,.04);transition:all .2s;display:flex;gap:1rem;}
.reminder-card:hover{border-color:rgba(21,101,192,.18);box-shadow:0 4px 14px rgba(0,0,0,.08);transform:translateY(-1px);}
.reminder-card.esc{border-left:3px solid var(--err);}
.reminder-card.done-card{opacity:.55;}
.reminder-title{font-size:14px;font-family:'Syne',sans-serif;font-weight:600;margin-bottom:4px;}
.reminder-desc{font-size:12px;color:var(--txt2);line-height:1.55;margin-bottom:8px;}
.reminder-meta{display:flex;gap:5px;flex-wrap:wrap;margin-bottom:8px;align-items:center;}
.reminder-thumb{width:76px;height:76px;border-radius:10px;border:1px solid var(--b1);flex-shrink:0;object-fit:cover;cursor:pointer;transition:transform .2s;}
.reminder-thumb:hover{transform:scale(1.04);}
.reply-area{display:flex;gap:7px;flex-wrap:wrap;margin-top:8px;}
.reply-area input{flex:1;background:var(--s2);border:1px solid var(--b2);color:var(--txt);border-radius:9px;padding:9px 13px;font-size:13px;min-width:130px;}
.reply-area input:focus{border-color:rgba(21,101,192,.5);outline:none;}

/* ── PENALTY CARDS ── */
.pen-item{background:var(--s1);border:1px solid var(--b1);border-left:3px solid var(--err);border-radius:var(--r2);padding:.875rem 1rem;margin-bottom:.6rem;cursor:pointer;transition:all .18s;display:flex;align-items:center;justify-content:space-between;}
.pen-item:hover{background:var(--s2);transform:translateX(2px);}

/* ── OVERLAYS & MODALS ── */
.overlay{background:rgba(0,0,0,.45);backdrop-filter:blur(4px);}
.modal{background:var(--s1);border:1px solid var(--b2);border-radius:18px;padding:1.75rem;box-shadow:0 20px 50px rgba(0,0,0,.18);max-height:90vh;overflow-y:auto;}
.modal h3{font-size:17px;margin-bottom:1.25rem;}
.modal-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:1.25rem;flex-wrap:wrap;}
.notif-preview{background:var(--s2);border:1px solid var(--b1);border-radius:9px;padding:1rem;font-size:13px;color:var(--txt2);line-height:1.7;margin-bottom:1rem;font-family:'DM Mono',monospace;white-space:pre-wrap;}
.mf{margin-bottom:.6rem;}.mf-l{font-size:10px;text-transform:uppercase;letter-spacing:.6px;color:var(--txt3);margin-bottom:3px;}.mf-v{font-size:13px;color:var(--txt);}

/* ── TOAST ── */
.toast{position:fixed;bottom:72px;right:16px;background:var(--s1);border:1px solid var(--b2);border-radius:11px;padding:12px 18px;font-size:13px;color:var(--txt);z-index:9999;opacity:0;transform:translateY(6px);transition:all .25s;display:flex;align-items:center;gap:10px;max-width:300px;box-shadow:0 8px 24px rgba(0,0,0,.14);}
.toast.on{opacity:1;transform:translateY(0);}

/* ── MISC ── */
.sheet-pill{display:flex;align-items:center;gap:5px;font-size:11px;color:var(--txt3);padding:5px 10px;background:var(--s2);border:1px solid var(--b1);border-radius:20px;}
.dot{width:6px;height:6px;border-radius:50%;background:var(--ok);animation:blink 2s infinite;}.dot.off{background:var(--txt3);animation:none;}
.file-drop{display:flex;align-items:center;gap:8px;background:var(--s2);border:1px dashed var(--b2);border-radius:9px;padding:11px 13px;cursor:pointer;font-size:13px;color:var(--txt2);transition:border-color .2s;}
.file-drop:hover{border-color:rgba(21,101,192,.5);background:rgba(21,101,192,.04);}
.sg h4{font-size:13px;font-weight:600;color:var(--txt2);margin-bottom:.875rem;padding-bottom:.5rem;border-bottom:1px solid var(--b1);}
.sg{margin-bottom:1.5rem;}.settings-note{font-size:11.5px;color:var(--txt3);line-height:1.7;margin:.4rem 0 .875rem;}
.notif-log{font-size:12.5px;background:var(--s2);border-radius:var(--r);padding:.875rem;}
.nl-item{display:flex;gap:10px;padding:7px 0;border-bottom:1px solid var(--b1);align-items:flex-start;}
.nl-item:last-child{border-bottom:none;}.nl-ico{font-size:13px;flex-shrink:0;margin-top:2px;}.nl-text{flex:1;color:var(--txt2);}
.sync-status{font-size:11px;padding:4px 10px;border-radius:20px;font-family:'DM Mono',monospace;}
.sync-ok{background:rgba(22,163,74,.1);color:var(--ok);border:1px solid rgba(22,163,74,.2);}
.sync-err{background:rgba(220,38,38,.1);color:var(--err);border:1px solid rgba(220,38,38,.2);}
.admin-only-tag{display:inline-flex;align-items:center;gap:4px;font-size:10px;background:rgba(99,102,241,.12);color:#a5b4fc;border:1px solid rgba(99,102,241,.22);border-radius:20px;padding:3px 8px;}
.badge{display:inline-flex;align-items:center;gap:3px;padding:3px 9px;border-radius:20px;font-size:10.5px;font-weight:500;}
.b-pending{background:rgba(217,119,6,.12);color:var(--warn);}
.b-done{background:rgba(22,163,74,.12);color:var(--ok);}
.b-esc{background:rgba(220,38,38,.12);color:var(--err);}
.b-info{background:rgba(21,101,192,.12);color:var(--acc);}
.b-auto{background:rgba(99,102,241,.12);color:#a5b4fc;}
.rem-pip{display:inline-flex;align-items:center;gap:4px;font-size:11px;background:var(--s3);border:1px solid var(--b1);border-radius:20px;padding:3px 9px;font-family:'DM Mono',monospace;color:var(--txt2);}
.rem-pip.hot{border-color:rgba(220,38,38,.3);color:var(--err);}
hr{border:none;border-top:1px solid var(--b1);margin:1.25rem 0;}

/* ── CHAT WIDGET ── */
.chat-widget{position:fixed;bottom:72px;right:16px;width:330px;height:460px;background:var(--s1);border:1px solid var(--b2);border-radius:16px;box-shadow:0 12px 36px rgba(0,0,0,.14);display:none;flex-direction:column;z-index:9999;overflow:hidden;}
.chat-widget.show{display:flex;animation:slideUp .3s ease;}
.cw-header{background:var(--sb-bg);border-bottom:1px solid var(--sb-border);padding:12px 16px;display:flex;justify-content:space-between;align-items:center;}
.cw-title{font-weight:600;font-size:13px;color:#fff!important;}
.cw-sub{font-size:10px;color:var(--sb-txt2)!important;max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:2px;}
.cw-header>button{background:none;border:none;color:var(--sb-txt)!important;cursor:pointer;font-size:16px;width:26px;height:26px;border-radius:6px;display:flex;align-items:center;justify-content:center;transition:all .2s;}
.cw-header>button:hover{color:var(--err)!important;}
.cw-body{flex:1;overflow-y:auto;padding:12px;display:flex;flex-direction:column;gap:8px;background:var(--bg);}
.cw-footer{padding:12px;background:var(--s2);border-top:1px solid var(--b1);display:flex;gap:7px;}
.cw-footer input{flex:1;background:var(--s1);border:1px solid var(--b1);border-radius:20px;padding:9px 15px;color:var(--txt);font-size:12.5px;outline:none;}
.cw-footer input:focus{border-color:var(--acc);}
.cw-footer button{background:var(--acc);border:none;color:#fff;width:36px;height:36px;border-radius:50%;cursor:pointer;font-size:13px;display:flex;align-items:center;justify-content:center;transition:transform .15s;}
.cw-footer button:hover{transform:scale(1.08);}
.chat-msg{max-width:85%;display:flex;flex-direction:column;}
.chat-msg.me{align-self:flex-end;}.chat-msg.other{align-self:flex-start;}
.chat-msg .name{font-size:9px;color:var(--txt3);margin-bottom:3px;}
.chat-msg.me .name{text-align:right;}
.chat-msg .bubble{padding:8px 12px;border-radius:10px;font-size:12.5px;word-break:break-word;line-height:1.5;}
.chat-msg.me .bubble{background:var(--acc);color:#fff;border-bottom-right-radius:3px;}
.chat-msg.other .bubble{background:var(--s3);border:1px solid var(--b1);color:var(--txt);border-bottom-left-radius:3px;}
.chat-msg .time{font-size:9px;color:var(--txt3);margin-top:3px;}
.chat-msg.me .time{text-align:right;}
.cw-add-user-btn{background:var(--acc);color:#fff;border:none;font-size:9px;padding:3px 8px;border-radius:12px;cursor:pointer;}

/* ── PAGINATION ── */
.pagination{display:flex;align-items:center;justify-content:center;gap:4px;flex-wrap:wrap;padding:.75rem 0 .25rem;}
.pg-btn{min-width:30px;height:30px;padding:0 8px;border:1px solid var(--b2);background:var(--s1);color:var(--txt2);border-radius:8px;font-size:12px;font-family:'DM Mono',monospace;cursor:pointer;transition:all .15s;display:inline-flex;align-items:center;justify-content:center;}
.pg-btn:hover:not(:disabled){background:var(--s3);border-color:rgba(21,101,192,.3);color:var(--acc);}
.pg-btn.pg-active{background:var(--acc);color:#fff;border-color:var(--acc);}
.pg-btn:disabled{opacity:.35;cursor:not-allowed;}
.pg-ellipsis{color:var(--txt3);padding:0 3px;font-size:12px;line-height:30px;}
.pg-info{font-size:10.5px;color:var(--txt3);margin-left:6px;font-family:'DM Mono',monospace;white-space:nowrap;}

/* ── EMPTY STATE ── */
.empty-state{text-align:center;padding:2.5rem 1rem;color:var(--txt3);}
.empty-state .es-icon{font-size:36px;margin-bottom:.6rem;opacity:.6;}
.empty-state .es-title{font-size:14px;font-weight:600;color:var(--txt);margin-bottom:4px;}
.empty-state .es-sub{font-size:12px;}

/* ── MOBILE BOTTOM NAV ── */
.mobile-bottom-nav{display:none;position:fixed;bottom:0;left:0;right:0;height:58px;background:var(--sb-bg);border-top:1px solid var(--sb-border);z-index:200;padding:0 6px;padding-bottom:env(safe-area-inset-bottom);}
.mbn-item{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:2px;color:var(--sb-txt2);cursor:pointer;padding:5px 2px;border-radius:8px;transition:all .18s;position:relative;}
.mbn-item.active{color:#fff;}
.mbn-item.active .mbn-icon-wrap::after{content:'';position:absolute;bottom:-3px;left:50%;transform:translateX(-50%);width:18px;height:2px;background:var(--sb-accent);border-radius:2px;}
.mbn-icon-wrap{position:relative;display:flex;align-items:center;justify-content:center;}
.mbn-icon{font-size:16px;line-height:1;}
.mbn-label{font-size:9px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;}
.mbn-badge{position:absolute;top:-4px;right:-6px;background:var(--err);color:#fff;font-size:8px;min-width:14px;height:14px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-family:'DM Mono',monospace;padding:0 3px;}
.img-overlay{background:rgba(0,0,0,.88);}

/* ── b-ok alias ── */
.b-ok{background:rgba(22,163,74,.12);color:var(--ok);border:1px solid rgba(22,163,74,.2);}

/* ── RESPONSIVE ── */
@media(max-width:1100px){.sidebar{width:200px;}.main{margin-left:200px!important;padding:1.25rem 1.5rem;}}
@media(max-width:768px){
  /* Sidebar becomes slide-in drawer (not display:none) */
  .sidebar{transform:translateX(-100%);display:flex!important;z-index:150;width:240px;}
  .sidebar.open{transform:translateX(0);box-shadow:6px 0 30px rgba(0,0,0,.5);}
  .mobile-bottom-nav{display:flex;}
  .mobile-topbar{display:flex!important;}
  .main{margin-left:0!important;padding:.875rem .875rem calc(62px + .875rem);}
  .layout{padding-top:0;}
  .toast{bottom:70px;right:12px;max-width:calc(100vw - 24px);}
  .chat-widget{width:calc(100vw - 20px);right:10px;height:400px;bottom:70px;}
  .stats{grid-template-columns:1fr 1fr;gap:.65rem;}
  .stat{padding:.875rem;}.stat .s-val{font-size:24px;}
  .card{padding:.875rem;border-radius:12px;}
  .row2{grid-template-columns:1fr;gap:0;}
  .fg{margin-bottom:.75rem;}
  .export-bar{flex-direction:column;align-items:stretch;gap:7px;}
  .export-bar select,.export-bar input[type="date"]{width:100%;}
  .export-bar .sep{display:none;}
  .modal{width:96vw;padding:1.1rem;border-radius:16px;max-height:88vh;}
  .modal h3{font-size:16px;}
  .reminder-card{flex-direction:column;gap:.6rem;padding:.875rem;}
  .reminder-thumb{width:100%;height:150px;border-radius:10px;}
  .reply-area{flex-direction:column;}
  .reply-area button{justify-content:center;width:100%;}
  .ph{flex-direction:column;gap:.5rem;align-items:flex-start;}
  .ph-right{justify-content:flex-start;}
  .ph-left h2{font-size:17px;}
  th,td{padding:8px 10px;font-size:12px;}
  .admin-export-btns.hidden{display:none!important;}
}
@media(max-width:480px){
  .main{padding:.75rem .75rem calc(60px + .75rem);}
  .stats{grid-template-columns:1fr 1fr;}
  .modal{width:99vw;padding:1rem;border-radius:14px;}
}

/* ── DARK MODE ── */
[data-theme="dark"]{
  --bg:#070b17;--s1:rgba(12,20,40,.97);--s2:rgba(18,28,52,.95);--s3:rgba(26,40,70,.92);--s4:rgba(38,55,90,.9);
  --b1:rgba(255,255,255,.06);--b2:rgba(255,255,255,.11);--b3:rgba(255,255,255,.19);
  --txt:#e2e8f0;--txt2:#8fa5c8;--txt3:#5f7a9e;--acc:#4a90d9;--acc2:#3b6fba;
  --ok:#22c55e;--warn:#f59e0b;--err:#ef4444;
}
[data-theme="dark"] body{background:var(--bg);}
[data-theme="dark"] .fg input,[data-theme="dark"] .fg select,[data-theme="dark"] .fg textarea{background:var(--s2);border-color:var(--b1);color:var(--txt);}
[data-theme="dark"] th{background:rgba(255,255,255,.05);}
[data-theme="dark"] tr:hover td{background:rgba(255,255,255,.03);}
[data-theme="dark"] .export-bar select,[data-theme="dark"] .export-bar input[type="date"]{background:var(--s3);color:var(--txt);}
[data-theme="dark"] .pg-btn{background:var(--s2);border-color:var(--b2);color:var(--txt2);}
[data-theme="dark"] .btn-ghost{background:var(--s3);border-color:var(--b2);color:var(--txt2);}
</style>
</head>
<body>

<div id="loginPage">
  <div class="login-card">
    <div class="brand">
      <div class="brand-icon">
        <img src="data:image/jpeg;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=" alt="Logo" class="base64-logo">
      </div>
      <h1>Avyukta Intellicall</h1>
      <p>Reminder panel</p>
    </div>
    
    <form onsubmit="event.preventDefault(); login();">
      <div class="fg">
        <label>Sign in as</label>
        <select id="role">
          <option value="admin">🔐 Admin</option>
          <option value="employee">👤 Employee</option>
        </select>
      </div>
      
      <div class="fg"><label>Username</label><input id="username" placeholder="Enter username" autocomplete="username" required></div>
      <div class="fg" style="position:relative;">
        <label>Password</label>
        <input id="password" type="password" placeholder="Enter password" style="padding-right: 40px;" autocomplete="current-password" required>
        <button type="button" onclick="togglePassword('password', this)" style="position:absolute; right:10px; top:25px; background:none; border:none; cursor:pointer; font-size:14px; opacity:0.7;">👁️</button>
      </div>
      
      <div class="fg" style="display:flex; justify-content:space-between; align-items:center;">
        <label style="margin-bottom:0; display:flex; align-items:center; gap:6px; cursor:pointer; text-transform:none; letter-spacing:normal; font-size:12px; color:var(--txt2);">
          <input type="checkbox" id="rememberMe" style="width:auto;"> Remember me
        </label>
        <a href="#" onclick="alert('Reach out to Admin to reset your password.')" style="font-size:12px; color:var(--acc); text-decoration:none;">Forgot Password?</a>
      </div>

      <button type="submit" id="loginBtn" class="btn btn-primary btn-full" style="padding: 14px; font-size: 14px;">Sign In &rarr;</button>
    </form>
  </div>
</div>

<div id="appPage">

<div class="mobile-topbar">
   <div style="display:flex; align-items:center; gap:10px;">
     <img src="data:image/jpeg;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=" alt="Logo" style="width:28px; height:28px; border-radius:50%;">
     <div style="font-family:'Jost',sans-serif;font-style:italic;font-size:18px;font-weight:700;">Avyukta Intellicall</div>
   </div>
   <button class="mobile-nav-btn" onclick="toggleSidebar()">☰</button>
</div>
<div class="mobile-overlay" id="mobileOverlay" onclick="toggleSidebar()"></div>

<div class="layout">

<div class="sidebar">
  <div class="sb-logo">
    <div class="icon">
        <img src="data:image/jpeg;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=" alt="Logo" class="base64-logo">
    </div>
    <div><div class="title">Avyukta Intellicall</div><div class="sub">PANEL</div></div>
  </div>

  <div class="nav-section">Overview</div>
  <div class="nav-item active" id="nav-dashboard" onclick="showSec('dashboard')">
    <span class="ni">▦</span> Dashboard
  </div>

  <div class="nav-section">Manage</div>
  <div class="nav-item" id="nav-reminders" onclick="showSec('reminders')">
    <span class="ni">✓</span> Reminders
  </div>
  <div class="nav-item" id="nav-cards" onclick="showSec('cards')">
    <span class="ni">⚠</span> Penalty Cards <span class="nav-badge hidden" id="cardBadge">0</span>
  </div>
  
  <div class="nav-item" id="nav-profile" onclick="showSec('profile')">
    <span class="ni">👤</span> My Profile
  </div>

  <div class="nav-section admin-nav-section hidden">System</div>
  <div class="nav-item hidden" id="nav-employees" onclick="showSec('employees')">
    <span class="ni">👥</span> Employees
  </div>
  <div class="nav-item hidden" id="nav-notifications" onclick="showSec('notifications')">
    <span class="ni">🔔</span> Notification Log
  </div>
  <div class="nav-item hidden" id="nav-settings" onclick="showSec('settings')">
    <span class="ni">⚙</span> Settings
  </div>

  <div class="sb-footer">
    <div class="user-chip">
      <div class="avatar" id="sbAvatar">AD</div>
      <div class="uc-info" style="flex:1;">
        <div class="name" id="sbName">Admin</div>
        <div class="role" id="sbRole">Administrator</div>
      </div>
      <button class="btn-out theme-btn" onclick="toggleTheme()" title="Toggle Light/Dark Mode">☀️</button>
      <button class="btn-out" onclick="logout()" title="Logout">⏻</button>
    </div>
  </div>
</div>

<div class="main">

  <div class="sp on" id="sec-dashboard">
    <div class="ph">
      <div class="ph-left"><h2>Dashboard</h2><p id="dashSubText">Live overview of reminders &amp; team activity</p></div>
      <div class="ph-right">
        <div class="sheet-pill"><span class="dot" id="sheetDot"></span><span id="sheetTxt">Checking…</span></div>
        <button class="btn btn-ghost btn-sm" onclick="forceSheetsSync()" style="margin-left: 8px;">🔄 Refresh</button>
      </div>

    </div>

    <div style="background:var(--s2); padding:10px 16px; border-radius:8px; margin-bottom:1.25rem; font-size:11px; color:var(--txt3); font-family:'DM Mono',monospace;">
      Data Status: Employees: <span id="dataEmpCount">0</span> | Reminders: <span id="dataRemCount">0</span> | Cards: <span id="dataCardCount">0</span> | Notifications: <span id="dataNotifCount">0</span>
    </div>
    
    <div class="stats" id="statsRow"></div>
    
    <div class="card">
      <div class="ch"><span class="ct" id="recentActTitle">Recent Activity</span></div>
      <div class="tbl-wrap" id="dashReminders"></div>
    </div>
  </div>

  <div class="sp" id="sec-reminders">
    <div class="ph">
      <div class="ph-left"><h2>Reminders</h2><p id="reminderSubtitle">All assigned reminders</p></div>
      <div class="ph-right">
        <button class="btn btn-ghost btn-sm" onclick="forceSheetsSync()">🔄 Refresh Data</button>
      </div>
    </div>

    <div class="export-bar" id="reminderFilterBar">
      <span style="font-size:11px;color:var(--txt2);font-weight:600;letter-spacing:1px;text-transform:uppercase;">Filter:</span>
      <select id="reminderFilterType" onchange="reminderPage=1; toggleCustomDates('reminder'); renderReminders();">
        <option value="month" selected>This Month</option>
        <option value="prev_month">Previous Month</option>
        <option value="all">All Time</option>
        <option value="year">This Year</option>
        <option value="custom">Custom Date Range</option>
      </select>
      <input type="date" id="reminderDateStart" class="hidden" onchange="reminderPage=1; renderReminders()">
      <span id="reminderDateTo" class="hidden" style="font-size:12px;color:var(--txt3);">to</span>
      <input type="date" id="reminderDateEnd" class="hidden" onchange="reminderPage=1; renderReminders()">
      
      <div class="sep admin-export-btns"></div>
      
      <button class="ico-btn ico-csv admin-export-btns" title="Download CSV" onclick="exportData('reminder', 'csv')">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><text x="12" y="17" text-anchor="middle" font-size="7" font-family="'DM Sans', sans-serif" font-weight="700" stroke="none" fill="currentColor">CSV</text></svg>
      </button>
      <button class="ico-btn ico-xls admin-export-btns" title="Download Excel" onclick="exportData('reminder', 'excel')">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><text x="12" y="17" text-anchor="middle" font-size="7" font-family="'DM Sans', sans-serif" font-weight="700" stroke="none" fill="currentColor">XLS</text></svg>
      </button>
      <button class="ico-btn ico-pdf admin-export-btns" title="Download PDF" onclick="exportData('reminder', 'pdf')">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><text x="12" y="17" text-anchor="middle" font-size="7" font-family="'DM Sans', sans-serif" font-weight="700" stroke="none" fill="currentColor">PDF</text></svg>
      </button>
    </div>
    
    <div class="card hidden" id="assignCard">
      <div class="ch">
        <span class="ct">Assign New Reminder</span>
        <span class="admin-only-tag" id="assignAuthTag">🔐 Admin Only</span>
      </div>
      <div class="row2">
        <div class="fg"><label>Assign To Employee</label><select id="empSelect"></select></div>
        <div class="fg"><label>Reminder Title</label><input id="reminderTitle" placeholder="Enter reminder title"></div>
      </div>
      <div class="fg"><label>Description</label><textarea id="reminderDesc" placeholder="Describe the reminder in detail…"></textarea></div>
      <div class="row2">
        <div class="fg"><label>WA Group Name (Optional)</label><input id="reminderWaGroup" placeholder="e.g. Project Alpha Team"></div>
        <div class="fg">
          <label>Auto-Reminder Interval</label>
          <select id="reminderInterval">
            <option value="0">Disabled (Manual Only)</option>
            <option value="1">Every 1 Hour</option>
            <option value="2">Every 2 Hours</option>
            <option value="3" selected>Every 3 Hours</option>
            <option value="6">Every 6 Hours</option>
            <option value="12">Every 12 Hours</option>
            <option value="24">Every 24 Hours</option>
          </select>
        </div>
      </div>
      <div class="row2">
        <div class="fg">
          <label>Total Duration (Auto-Escalate After)</label>
          <select id="reminderDuration" onchange="previewSchedule()">
            <option value="0">No Auto-Escalation</option>
            <option value="3">3 Hours</option>
            <option value="6">6 Hours</option>
            <option value="12" selected>12 Hours</option>
            <option value="24">24 Hours</option>
            <option value="48">48 Hours (2 Days)</option>
            <option value="72">72 Hours (3 Days)</option>
          </select>
          <div id="schedulePreview" style="font-size:11px;color:var(--acc);margin-top:5px;font-family:'DM Mono',monospace;">📅 Notifications at: 3h, 6h, 9h, 12h</div>
        </div>
        <div class="fg">
          <label>Send via WhatsApp Instance</label>
          <select id="reminderWAConfig">
            <option value="">— Default Instance —</option>
          </select>
        </div>
      </div>
      <div class="fg" style="max-width: 50%;">
          <label>Attachment (optional)</label>
          <label class="file-drop" for="reminderImg">📎 Click to attach image</label>
          <input type="file" id="reminderImg" accept="image/*" onchange="previewFile(this)">
          <span id="fileLabel" style="font-size:11px;color:var(--txt3);margin-top:5px;display:block;"></span>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:10px;">
        <button class="btn btn-primary" onclick="assignReminder()">Assign Reminder →</button>
        <button class="btn btn-ghost" onclick="clearAssignForm()">Clear</button>
      </div>
    </div>
    <div id="reminderListWrap"></div>
  </div>

  <div class="sp" id="sec-cards">
    <div class="ph">
      <div class="ph-left"><h2>Penalty Cards</h2><p>Employees with 3+ unanswered reminders</p></div>
    </div>

    <div class="export-bar" id="cardFilterBar">
      <span style="font-size:11px;color:var(--txt2);font-weight:600;letter-spacing:1px;text-transform:uppercase;">Filter:</span>
      <select id="cardFilterType" onchange="cardPage=1; toggleCustomDates('card'); renderCards();">
        <option value="month" selected>This Month</option>
        <option value="prev_month">Previous Month</option>
        <option value="all">All Time</option>
        <option value="year">This Year</option>
        <option value="custom">Custom Date Range</option>
      </select>
      <input type="date" id="cardDateStart" class="hidden" onchange="cardPage=1; renderCards()">
      <span id="cardDateTo" class="hidden" style="font-size:12px;color:var(--txt3);">to</span>
      <input type="date" id="cardDateEnd" class="hidden" onchange="cardPage=1; renderCards()">
      
      <div class="sep admin-export-btns"></div>
      
      <button class="ico-btn ico-csv admin-export-btns" title="Download CSV" onclick="exportData('card', 'csv')">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><text x="12" y="17" text-anchor="middle" font-size="7" font-family="'DM Sans', sans-serif" font-weight="700" stroke="none" fill="currentColor">CSV</text></svg>
      </button>
      <button class="ico-btn ico-xls admin-export-btns" title="Download Excel" onclick="exportData('card', 'excel')">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><text x="12" y="17" text-anchor="middle" font-size="7" font-family="'DM Sans', sans-serif" font-weight="700" stroke="none" fill="currentColor">XLS</text></svg>
      </button>
      <button class="ico-btn ico-pdf admin-export-btns" title="Download PDF" onclick="exportData('card', 'pdf')">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><text x="12" y="17" text-anchor="middle" font-size="7" font-family="'DM Sans', sans-serif" font-weight="700" stroke="none" fill="currentColor">PDF</text></svg>
      </button>
    </div>

    <div id="cardListWrap"></div>
  </div>

  <div class="sp" id="sec-profile">
    <div class="ph">
      <div class="ph-left"><h2>My Profile</h2><p>Manage your account security</p></div>
    </div>
    <div class="card" style="max-width:500px;">
      <div class="ch"><span class="ct">Change Password</span></div>
      <p style="font-size:12px; color:var(--txt2); margin-bottom:1rem;">To update your password, verify your current password below. If you forgot your password, contact an administrator.</p>
      <div class="fg"><label>Current Password</label>
        <div style="position:relative;">
            <input id="profOldPass" type="password" placeholder="••••••" style="padding-right:40px;">
            <button type="button" onclick="togglePassword('profOldPass', this)" style="position:absolute; right:10px; top:10px; background:none; border:none; cursor:pointer; font-size:14px; opacity:0.7;">👁️</button>
        </div>
      </div>
      <div class="fg"><label>New Password</label>
        <div style="position:relative;">
            <input id="profNewPass" type="password" placeholder="••••••" style="padding-right:40px;">
            <button type="button" onclick="togglePassword('profNewPass', this)" style="position:absolute; right:10px; top:10px; background:none; border:none; cursor:pointer; font-size:14px; opacity:0.7;">👁️</button>
        </div>
      </div>
      <button class="btn btn-primary" onclick="changeMyPassword()">Update Password</button>
    </div>
  </div>

  <div class="sp" id="sec-employees">
    <div class="ph">
      <div class="ph-left"><h2>Employees</h2><p>Fetch from CRM or add/manage team members manually</p></div>
      <div class="ph-right"><span class="admin-only-tag">🔐 Admin Only</span></div>
    </div>
    <div class="card" style="max-width:650px;">
      <div class="ch"><span class="ct">Fetch Employees from CRM</span></div>
      <p style="font-size:14px;color:var(--txt2);margin-bottom:1rem;">Import employee data from Avyukta CRM. This will sync all users with their details.</p>
      <button class="btn btn-primary" onclick="fetchEmployeesFromCRM()">🔄 Fetch from CRM</button>
      <div id="crmStatus" style="margin-top:1rem;font-size:13px;"></div>
    </div>
    <div class="card" style="max-width:650px;">
      <div class="ch"><span class="ct">Add New Employee</span></div>
      <div class="row2">
        <div class="fg"><label>Employee ID</label><input id="empId" placeholder="e.g. EMP-001"></div>
        <div class="fg"><label>Full Name</label><input id="empName" placeholder="John Doe"></div>
      </div>
      <div class="row2">
        <div class="fg">
          <label>Phone Number (10 Digits)</label>
          <div style="display:flex;gap:8px;">
            <select id="empCountryCode" style="width:100px;">
              <option value="+91">+91 (IN)</option>
              <option value="+1">+1 (US)</option>
              <option value="+44">+44 (UK)</option>
              <option value="+61">+61 (AU)</option>
              <option value="+977">+977 (NP)</option>
            </select>
            <input id="empPhone" type="tel" placeholder="9876543210" maxlength="10" style="flex:1;">
          </div>
        </div>
        <div class="fg"><label>Email Address</label><input id="empEmail" type="email" placeholder="john@company.com"></div>
      </div>
      <div class="row2">
        <div class="fg"><label>Department</label><input id="empDepartment" placeholder="e.g. Sales"></div>
        <div class="fg"><label>Role</label><input id="empRole" placeholder="e.g. Manager"></div>
      </div>
      <div class="row2">
        <div class="fg"><label>Username (login)</label><input id="empUser" placeholder="johndoe"></div>
        <div class="fg"><label>Password</label><input id="empPass" type="password" placeholder="••••••"></div>
      </div>
      <div class="fg">
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;text-transform:none;letter-spacing:normal;font-size:13px;color:var(--txt);">
          <input type="checkbox" id="empCanAssign" style="width:16px;height:16px;">
          Grant permission to assign reminders to other employees
        </label>
      </div>
      <button class="btn btn-primary" onclick="addEmployee()">Add Employee</button>
    </div>
    <div class="card">
      <div class="ch"><span class="ct">Team Directory</span></div>
      <div class="tbl-wrap" id="empTableWrap"></div>
    </div>
  </div>

  <div class="sp" id="sec-notifications">
    <div class="ph">
      <div class="ph-left"><h2>Notification Log</h2><p>All emails and SMS sent from the system</p></div>
      <div class="ph-right"><button class="btn btn-ghost btn-sm" onclick="clearNotifLog()">Clear Log</button></div>
    </div>
    <div class="card">
      <div id="notifLogWrap"></div>
    </div>
  </div>

  <div class="sp" id="sec-settings">
    <div class="ph">
      <div class="ph-left"><h2>Settings</h2><p>WhatsApp instances &amp; system configuration</p></div>
      <div class="ph-right"><span class="admin-only-tag">🔐 Admin Only</span></div>
    </div>

    <!-- WA Config -->
    <div class="card" style="max-width:760px;margin-bottom:1.25rem;">
      <div class="ch"><span class="ct">WhatsApp Instances</span></div>
      <div class="tbl-wrap" id="waConfigList" style="margin-bottom:1rem;">
        <p style="color:var(--txt3);font-size:13px;padding:.5rem 0;">Loading…</p>
      </div>
      <div style="background:var(--s2);border:1px solid var(--b1);border-radius:12px;padding:1.25rem;">
        <p style="font-size:12px;font-weight:600;color:var(--txt2);margin-bottom:1rem;text-transform:uppercase;letter-spacing:.5px;">Add / Edit Instance</p>
        <input type="hidden" id="waEditId">
        <div class="row2">
          <div class="fg"><label>Display Name</label><input id="waName" placeholder="e.g. Yash (RHS2B4V4AY)"></div>
          <div class="fg"><label>Instance ID</label><input id="waInstanceId" placeholder="RHS2B4V4AY"></div>
        </div>
        <div class="row2">
          <div class="fg"><label>Access Token</label><input id="waToken" placeholder="D6D9Q6VM  (leave blank to keep existing)"></div>
          <div class="fg"><label>API URL</label><input id="waApiUrl" value="https://wa.clouddialer.in/api/v2/messages"></div>
        </div>
        <div class="fg">
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer;text-transform:none;letter-spacing:normal;font-size:13px;color:var(--txt);">
            <input type="checkbox" id="waIsDefault" style="width:16px;height:16px;"> Set as default (used for all reminders unless overridden)
          </label>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
          <button class="btn btn-primary btn-sm" onclick="saveWAConfig()">💾 Save Instance</button>
          <button class="btn btn-ghost btn-sm" onclick="clearWAForm()">✕ Clear</button>
        </div>
      </div>
    </div>

    <!-- DB Connection -->
    <div class="card" style="max-width:760px;">
      <div class="sg">
        <h4>Database API</h4>
        <p class="settings-note">All data is stored in a local MySQL database via <strong>api.php</strong>.</p>
        <input type="hidden" id="scriptUrl" value="api.php">
        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
          <button class="btn btn-ghost btn-sm" onclick="testConnection()">🔌 Test DB Connection</button>
          <span id="testStatusBadge" style="display:none;"></span>
        </div>
      </div>
    </div>
  </div>

</div></div>

<!-- ── MOBILE BOTTOM NAV ── -->
<nav class="mobile-bottom-nav" id="mobileBottomNav">
  <div class="mbn-item active" id="mbn-dashboard" onclick="showSec('dashboard')">
    <div class="mbn-icon-wrap"><span class="mbn-icon">▦</span></div>
    <span class="mbn-label">Home</span>
  </div>
  <div class="mbn-item" id="mbn-reminders" onclick="showSec('reminders')">
    <div class="mbn-icon-wrap"><span class="mbn-icon">✓</span></div>
    <span class="mbn-label">Tasks</span>
  </div>
  <div class="mbn-item" id="mbn-cards" onclick="showSec('cards')">
    <div class="mbn-icon-wrap"><span class="mbn-icon">⚠</span><span class="mbn-badge hidden" id="mbnCardBadge">0</span></div>
    <span class="mbn-label">Cards</span>
  </div>
  <div class="mbn-item admin-mbn hidden" id="mbn-employees" onclick="showSec('employees')">
    <div class="mbn-icon-wrap"><span class="mbn-icon">👥</span></div>
    <span class="mbn-label">Team</span>
  </div>
  <div class="mbn-item" id="mbn-profile" onclick="showSec('profile')">
    <div class="mbn-icon-wrap"><span class="mbn-icon">👤</span></div>
    <span class="mbn-label">Profile</span>
  </div>
</nav>

</div>

<div class="chat-widget" id="chatWidget">
  <div class="cw-header">
    <div>
      <div class="cw-title" style="display:flex; align-items:center; gap:8px;">
        Reminder Chat
        <button class="cw-add-user-btn" onclick="openAddUserModal()" title="Add another user to this chat">+ Add User</button>
      </div>
      <div class="cw-sub" id="chatReminderTitle">—</div>
    </div>
    <button onclick="closeChatModal()">✕</button>
  </div>
  <div class="cw-body" id="chatMessages"></div>
  <div class="cw-footer">
    <input type="text" id="chatInput" placeholder="Type a message..." onkeypress="if(event.key==='Enter') sendChatReply();">
    <button onclick="sendChatReply()">➤</button>
  </div>
</div>

<div class="img-overlay" id="imgModal" onclick="closeImageModal()">
  <img id="imgModalContent" src="" alt="Reminder Attachment">
</div>

<div class="overlay" id="penModal">
  <div class="modal">
    <h3>⚠ Penalty Card Details</h3>
    <div id="penModalContent"></div>
    <div class="modal-actions">
        <button class="btn btn-ok btn-sm hidden" id="waiveCardBtn" onclick="waiveCard()">Waive Off 🛡️</button>
        <button class="btn btn-ghost btn-sm" onclick="closePenModal()">Close</button>
    </div>
  </div>
</div>

<div class="overlay" id="remModal">
  <div class="modal">
    <h3>🔔 Send Manual Reminder</h3>
    <p style="font-size:13px;color:var(--txt2);margin-bottom:1rem;">Preview the notification that will be sent via <strong style="color:var(--txt);">Email &amp; WhatsApp</strong>.</p>
    <div class="mf"><div class="mf-l">Employee</div><div class="mf-v" id="remEmpName">—</div></div>
    <div class="row2" style="margin-bottom:1rem;">
      <div class="mf"><div class="mf-l">📧 Email</div><div class="mf-v" id="remEmpEmail" style="font-size:12px;color:var(--txt2);">—</div></div>
      <div class="mf"><div class="mf-l">📱 Phone</div><div class="mf-v" id="remEmpPhone" style="font-size:12px;color:var(--txt2);">—</div></div>
    </div>
    <div class="mf-l" style="margin-bottom:6px;">Notification Preview</div>
    <div class="notif-preview" id="remPreview">—</div>
    <div class="modal-actions">
      <button class="btn btn-ghost btn-sm" onclick="closeRemModal()">Cancel</button>
      <button class="btn btn-warn btn-sm" onclick="confirmReminder()">Send Manual Reminder 🔔</button>
    </div>
  </div>
</div>

<div class="overlay" id="reassignModal">
  <div class="modal">
    <h3>🔄 Reassign Reminder</h3>
    <div class="mf"><div class="mf-l">Reminder</div><div class="mf-v" id="reassignReminderTitle" style="font-weight:600;">—</div></div>
    <div class="mf"><div class="mf-l">Current Assignee</div><div class="mf-v" id="reassignOldEmp" style="color:var(--warn);">—</div></div>
    <div class="fg">
      <label>Reassign To New Employee</label>
      <select id="reassignEmpSelect"></select>
    </div>
    <div class="modal-actions">
      <button class="btn btn-ghost btn-sm" onclick="closeReassignModal()">Cancel</button>
      <button class="btn btn-primary btn-sm" onclick="confirmReassign()">Confirm Reassignment</button>
    </div>
  </div>
</div>

<div class="overlay" id="editRemModal">
  <div class="modal">
    <h3>✏️ Edit Reminder</h3>
    <div class="fg"><label>Reminder Title</label><input id="editRemTitle"></div>
    <div class="fg"><label>Description</label><textarea id="editRemDesc"></textarea></div>
    <div class="fg"><label>WA Group</label><input id="editRemWaGroup"></div>
    <div class="fg">
      <label>Auto-Reminder Interval</label>
      <select id="editRemInterval">
        <option value="0">Disabled (Manual Only)</option>
        <option value="1">Every 1 Hour</option>
        <option value="2">Every 2 Hours</option>
        <option value="3">Every 3 Hours</option>
        <option value="6">Every 6 Hours</option>
        <option value="12">Every 12 Hours</option>
        <option value="24">Every 24 Hours</option>
      </select>
    </div>
    <div class="modal-actions">
      <button class="btn btn-ghost btn-sm" onclick="closeEditModal()">Cancel</button>
      <button class="btn btn-primary btn-sm" onclick="saveEditedReminder()">Save Changes</button>
    </div>
  </div>
</div>

<div class="overlay" id="editEmpModal">
  <div class="modal">
    <h3>✏️ Edit Employee</h3>
    <div class="row2">
      <div class="fg"><label>Employee ID</label><input id="editEmpId" disabled style="opacity:0.6;cursor:not-allowed;"></div>
      <div class="fg"><label>Username</label><input id="editEmpUser" disabled style="opacity:0.6;cursor:not-allowed;"></div>
    </div>
    <div class="fg"><label>Full Name</label><input id="editEmpName"></div>
    <div class="row2">
      <div class="fg">
        <label>Phone Number</label>
        <div style="display:flex;gap:8px;">
          <select id="editEmpCountryCode" style="width:100px;">
            <option value="+91">+91 (IN)</option>
            <option value="+1">+1 (US)</option>
            <option value="+44">+44 (UK)</option>
            <option value="+61">+61 (AU)</option>
            <option value="+977">+977 (NP)</option>
          </select>
          <input id="editEmpPhone" type="tel" maxlength="10" style="flex:1;">
        </div>
      </div>
      <div class="fg"><label>Email Address</label><input id="editEmpEmail" type="email"></div>
    </div>
    <div class="row2">
      <div class="fg"><label>Department</label><input id="editEmpDepartment"></div>
      <div class="fg"><label>Role</label><input id="editEmpRole"></div>
    </div>
    <div class="fg"><label>Password</label><input id="editEmpPass" type="text"></div>
    <div class="fg">
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;text-transform:none;letter-spacing:normal;font-size:13px;color:var(--txt);">
        <input type="checkbox" id="editEmpCanAssign" style="width:16px;height:16px;">
        Grant permission to assign reminders
      </label>
    </div>
    <div class="modal-actions">
      <button class="btn btn-ghost btn-sm" onclick="closeEditEmpModal()">Cancel</button>
      <button class="btn btn-primary btn-sm" onclick="saveEditedEmployee()">Save Changes</button>
    </div>
  </div>
</div>

<div class="overlay" id="addUserModal">
  <div class="modal">
    <h3>➕ Add User to Chat</h3>
    <p style="font-size:12px; color:var(--txt2); margin-bottom:15px;">Select an employee to grant them access to read and reply in this chat.</p>
    <div class="fg">
      <label>Select Employee</label>
      <select id="chatAddUserSelect"></select>
    </div>
    <div class="modal-actions">
      <button class="btn btn-ghost btn-sm" onclick="closeAddUserModal()">Cancel</button>
      <button class="btn btn-primary btn-sm" onclick="confirmAddUserToChat()">Add to Chat</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"><span class="toast-icon" id="toastIco">✓</span><span id="toastMsg"></span></div>

<script>
/* ══════════════════════════════════════════════
   DATA & CONFIG
══════════════════════════════════════════════ */
let employees = [];
let reminders = [];
let cards     = [];
let notifLog  = [];
let waConfigs = [];

const PAGE_SIZE = 10;
let reminderPage = 1;
let cardPage     = 1;
let empPage      = 1;

// Session — persisted across page refreshes
let sessionToken = sessionStorage.getItem('reminderToken') || '';
function _saveSession(tok, user, role) {
  sessionToken = tok;
  sessionStorage.setItem('reminderToken', tok);
  sessionStorage.setItem('reminderUser',  user);
  sessionStorage.setItem('reminderRole',  role);
}
function _clearSession() {
  sessionToken = '';
  ['reminderToken','reminderUser','reminderRole'].forEach(k => sessionStorage.removeItem(k));
}

// Reset data to empty state
function resetAllData() {
  employees = [];
  reminders = [];
  cards     = [];
  notifLog  = [];
  reminderPage = 1;
  cardPage     = 1;
  empPage      = 1;
}

const DEFAULT_SCRIPT_URL = "api.php";
let scriptUrl = DEFAULT_SCRIPT_URL;

let pendingRemIdx = null;
let pendingReassignIdx = null;
let pendingEditRemIdx = null;
let pendingCardIdx = null;
let currentChatIdx = null;
let pendingEditEmpUser = null;
let currentUser = "", currentRole = "";

let knownSheetReminderSignatures = new Set();
let sheetSyncInitialized = false;

function normalizeReminderData(reminder) {
  reminder.autoInterval = parseInt(reminder.autoInterval) || 0;
  reminder.reminder = parseInt(reminder.reminder) || 0;
  if (!reminder.timestamp || isNaN(new Date(reminder.timestamp).getTime())) {
    reminder.timestamp = new Date().toISOString();
  }
  if (!reminder.lastRemindTs || isNaN(new Date(reminder.lastRemindTs).getTime())) {
    reminder.lastRemindTs = reminder.timestamp;
  }
  if (typeof reminder.done === "string") {
    reminder.done = reminder.done === "true" || reminder.done === "1";
  }
  if (!Array.isArray(reminder.replies)) {
    reminder.replies = reminder.replies || [];
  }
  return reminder;
}

/* ══════════════════════════════════════════════
   THEME TOGGLE SYSTEM (LIGHT / DARK)
══════════════════════════════════════════════ */
function toggleTheme() {
    const root = document.documentElement;
    const isDark = root.getAttribute('data-theme') === 'dark';
    if (isDark) {
        root.removeAttribute('data-theme');
        document.querySelectorAll('.theme-btn').forEach(b => b.textContent = '🌙');
    } else {
        root.setAttribute('data-theme', 'dark');
        document.querySelectorAll('.theme-btn').forEach(b => b.textContent = '☀️');
    }
}

/* ══════════════════════════════════════════════
   DATABASE SYNC SYSTEM
══════════════════════════════════════════════ */
async function fetchSheetsData() {
  try {
    console.log("Fetching data from database...");
    resetAllData();

    const result = await syncSheet({action: "getCloudData"});
    if (!result.success) {
      throw new Error(result.reason || "Failed to fetch data from database");
    }

    const json = result.data;
    console.log("DB response:", json);
    
    if(json && json.success && json.data) {
        const parsed = typeof json.data === "string" ? JSON.parse(json.data) : json.data;
        employees = parsed.employees || [];
        reminders = parsed.reminders || [];
        cards = parsed.cards || [];
        notifLog = parsed.notifLog || [];

        let dataFixed = false;
        reminders = reminders.map(function(reminder) {
          const before = JSON.stringify(reminder);
          const normalized = normalizeReminderData(reminder);
          if (JSON.stringify(normalized) !== before) {
            dataFixed = true;
          }
          return normalized;
        });

        if (dataFixed) {
          console.log("Normalized reminder fields and persisting missing timestamp data.");
          await save();
        }

        await processNewSheetReminders(reminders);
        console.log("Data loaded successfully. Employees:", employees.length, "Reminders:", reminders.length, "Cards:", cards.length);
    } else if (json && !json.success) {
        console.warn("DB returned success=false:", json.reason);
        throw new Error(json.reason || "Database returned invalid response");
    } else {
        console.warn("DB returned no data. Arrays remain empty.", json);
    }
  } catch(e) {
    console.error("Database sync failed:", e);
    resetAllData();
    throw e;
  }
}

async function pushSheetsData() {
  try {
    const dump = JSON.stringify({employees: employees, reminders: reminders, cards: cards, notifLog: notifLog});
    const result = await syncSheet({action: "syncCloudData", cloudData: dump});
    if (!result.success) {
      throw new Error(result.reason || "Failed to sync to database");
    }
  } catch(e) {
    console.error("Database push failed", e);
    showToast("Failed to save to database: " + e.message, "err");
    throw e;
  }
}



function save(){
  return pushSheetsData(); 
}

async function forceSheetsSync() {
  showToast("Syncing data from database... ⏳");
  try {
    await fetchSheetsData();
    refreshAll();
    showToast("Data refreshed! (Employees: " + employees.length + ", Reminders: " + reminders.length + ")", "ok");
  } catch (e) {
    showToast("Sync failed: " + e.message, "err");
  }
}

// ── ON PAGE LOAD INIT ──
document.addEventListener("DOMContentLoaded", async function() {
  document.querySelectorAll('.theme-btn').forEach(b => b.textContent = '🌙');

  const savedUser = sessionStorage.getItem('reminderUser') || '';
  const savedRole = sessionStorage.getItem('reminderRole') || '';

  if (sessionToken && savedUser && savedRole) {
    // Hide both pages immediately to avoid flash, show a loading indicator
    document.body.classList.add('session-loading');
    const loader = document.createElement('div');
    loader.id = 'pageLoader';
    loader.style.cssText = 'position:fixed;inset:0;background:#0b1525;display:flex;align-items:center;justify-content:center;z-index:99999;flex-direction:column;gap:12px;';
    loader.innerHTML = '<div style="width:36px;height:36px;border:3px solid rgba(255,255,255,.1);border-top-color:#4a90d9;border-radius:50%;animation:spin .7s linear infinite;"></div><div style="color:rgba(255,255,255,.4);font-size:12px;font-family:DM Sans,sans-serif;">Loading…</div>';
    document.body.appendChild(loader);

    try {
      currentUser = savedUser; currentRole = savedRole;
      await fetchSheetsData();
      await loadWAConfigs();
      document.body.classList.remove('session-loading');
      loader.remove();
      initApp();
      return;
    } catch(e) {
      _clearSession(); currentUser = ""; currentRole = "";
      document.body.classList.remove('session-loading');
      loader.remove();
    }
  }

  document.getElementById("loginPage").style.display = "flex";
});
// Spinner keyframe (injected once)
(function(){ var s=document.createElement('style'); s.textContent='@keyframes spin{to{transform:rotate(360deg)}}'; document.head.appendChild(s); })();

function togglePassword(id, btn) {
  const input = document.getElementById(id);
  if (input.type === "password") {
    input.type = "text";
    btn.textContent = "🙈";
  } else {
    input.type = "password";
    btn.textContent = "👁️";
  }
}

/* ══════════════════════════════════════════════
   WHATSAPP — server-side (credentials in DB)
══════════════════════════════════════════════ */
async function sendWhatsAppMessage(phoneStr, message, waConfigId) {
  if (!phoneStr) return;
  if (phoneStr.replace(/\D/g, '').length < 10) return;
  try {
    const result = await syncSheet({
      action: "sendWA",
      phone: phoneStr,
      message: message,
      waConfigId: waConfigId || null
    });
    return result;
  } catch (err) {
    console.error("WA send error:", err);
  }
}

function getReminderSignature(reminder) {
  return [reminder.emp || "", reminder.empName || "", reminder.empId || "", reminder.title || "", reminder.desc || "", reminder.timestamp || ""].join("||");
}

function buildWhatsAppMessageForReminder(reminder) {
  let waMsg = "Hello " + (reminder.empName || "there") + ",\n\n📌 *New Reminder Assigned*";
  if (reminder.reminder) waMsg += " (Reminder #" + reminder.reminder + ")";
  waMsg += "\n*Title:* " + (reminder.title || "—") + "\n*Description:* " + (reminder.desc || "—") + "\n";
  if (reminder.waGroup) waMsg += "*Group:* " + reminder.waGroup + "\n\n";
  const currentUrl = window.location.href.split('?')[0].split('#')[0];
  waMsg += "Please log in to your panel to check updates.\n";
  waMsg += "🔗 *Panel Link:* " + currentUrl + "\n\n";
  waMsg += "Signature : From Avyukta CRM Team";
  return waMsg;
}

function markKnownReminders(list) {
  list.forEach(function(reminder) {
    knownSheetReminderSignatures.add(getReminderSignature(reminder));
  });
}

function recordNotification(type, empName, reminderTitle, channel, message) {
  notifLog.unshift({ type: type, empName: empName, reminderTitle: reminderTitle, channel: channel, message: message, ts: new Date().toISOString() });
  if (notifLog.length > 200) notifLog.pop();
}

async function processNewSheetReminders(remindersList) {
  if (!sheetSyncInitialized) {
    markKnownReminders(remindersList);
    sheetSyncInitialized = true;
    return;
  }

  const newItems = remindersList.filter(function(item) {
    return !knownSheetReminderSignatures.has(getReminderSignature(item));
  });

  if (!newItems.length) {
    markKnownReminders(remindersList);
    return;
  }

  let sentCount = 0;
  for (const item of newItems) {
    const phone = item.empPhone || item.pNum || item.phone || item.empPhone;
    if (!phone) continue;
    const waMsg = buildWhatsAppMessageForReminder(item);
    await sendWhatsAppMessage(phone, waMsg);
    recordNotification("reminder", item.empName || item.emp, item.title || "Reminder", "💬 WhatsApp", waMsg);
    sentCount++;
  }

  if (sentCount) save();
  markKnownReminders(remindersList);
  if (sentCount) showToast(sentCount + " new reminder(s) synced from database and sent to WhatsApp", "ok");
}

/* ══════════════════════════════════════════════
   DB SYNC & AUDIT LOGGER
══════════════════════════════════════════════ */
async function syncSheet(payload, retryCount = 0){
  if(!scriptUrl) { setSyncStatus("not-configured"); return { success: false, reason: "no-url" }; }
  setSyncStatus("syncing");
  try {
    // Always attach session token if available
    const body = sessionToken ? {...payload, token: sessionToken} : payload;
    const params = new URLSearchParams();
    params.append("data", JSON.stringify(body));
    const res = await fetch(scriptUrl, { method: "POST", body: params });
    const text = await res.text();
    let json = {};
    try { json = JSON.parse(text); } catch(e) { throw new Error("Server error - check api.php and database connection."); }
    if (json.success === false) {
      // Handle session expiry
      if (json.reason && (json.reason.includes('Session expired') || json.reason.includes('Please login'))) {
        _clearSession();
        showToast("Session expired — please login again.", "warn");
        setTimeout(function() {
          currentUser = ""; currentRole = "";
          document.getElementById("appPage").style.display = "none";
          document.getElementById("loginPage").style.display = "flex";
        }, 1800);
      }
      throw new Error(json.reason || "Unknown API Error");
    }
    setSyncStatus("ok");
    return { success: true, data: json };
  } catch(err) {
    if (retryCount === 0 && (err.name === "TypeError" || (err.message && err.message.includes("fetch")))) {
      await new Promise(function(r) { setTimeout(r, 1500); });
      return syncSheet(payload, 1);
    }
    setSyncStatus("error", err.message);
    return { success: false, reason: err.message };
  }
}

function setSyncStatus(state, msg){
  const dot = document.getElementById("sheetDot");
  const txt = document.getElementById("sheetTxt");
  if (!dot || !txt) return;
  if(state === "ok"){ dot.classList.remove("off"); txt.textContent = "DB Synced " + new Date().toLocaleTimeString([],{hour:"2-digit",minute:"2-digit"}); }
  else if(state === "syncing"){ dot.classList.remove("off"); txt.textContent = "Saving…"; }
  else if(state === "error"){ dot.classList.add("off"); txt.textContent = "DB error"; }
  else if(state === "not-configured"){ dot.classList.add("off"); txt.textContent = "API not set"; }
}

function logNotif(type, empName, reminderTitle, channel, message){
  notifLog.unshift({ type: type, empName: empName, reminderTitle: reminderTitle, channel: channel, message: message, ts: new Date().toISOString() });
  if(notifLog.length > 200) notifLog.pop();
  save();
}

function logAudit(activityMsg) {
  const ts = new Date().toISOString();
  let userName = currentUser;
  if(currentRole === "admin"){
      userName = "Administrator";
  } else {
      const emp = employees.find(function(e) { return e.user === currentUser; });
      if(emp) userName = emp.name;
  }
  syncSheet({ action: "auditLog", user: userName, activity: activityMsg, timestamp: ts });
}

/* ══════════════════════════════════════════════
   AUTO-REMINDER ENGINE & COUNTDOWNS
══════════════════════════════════════════════ */
setInterval(checkAutoReminders, 60000);
setInterval(updateCountdowns, 60000);
setInterval(async function() {
    if (currentUser && document.getElementById("appPage").style.display === "block") {
        await fetchSheetsData();
        refreshAll();
    }
}, 60000); // Auto-refresh from database (Every 1 Minute)

async function checkAutoReminders() {
  // Handled server-side by cron_reminder.php every 5 minutes
  if (currentRole !== "admin") return;
  const now = Date.now();
  for (let i = 0; i < reminders.length; i++) {
    const t = reminders[i];
    if (!t.done && t.autoInterval > 0 && !t.totalDuration) { // legacy manual-interval reminders only
      const lastTs = new Date(t.lastRemindTs ? t.lastRemindTs : t.timestamp).getTime();
      if (isNaN(lastTs)) {
        t.lastRemindTs = t.timestamp || new Date().toISOString();
        await save();
        continue;
      }
      const intervalMs = t.autoInterval * 60 * 60 * 1000;
      const nextTs = lastTs + intervalMs;
      if (now >= nextTs) {
        await executeReminder(i, true);
      }
    }
  }
}

function getCountdownHTML(t) {
    if (t.done || t.autoInterval <= 0) return "";
    const lastTs = new Date(t.lastRemindTs ? t.lastRemindTs : t.timestamp).getTime();
    const nextTs = lastTs + (t.autoInterval * 60 * 60 * 1000);
    return `<span class="badge b-warn countdown-timer" data-time="${nextTs}" style="margin-left:6px; font-family:'DM Mono',monospace; letter-spacing:0px;">⏱ Calc...</span>`;
}

function updateCountdowns() {
    const timers = document.querySelectorAll('.countdown-timer');
    for(let i=0; i<timers.length; i++) {
        const el = timers[i];
        const target = parseInt(el.getAttribute('data-time'));
        const diff = target - Date.now();
        if (diff <= 0) {
            el.innerHTML = "⏱ Due now";
            el.classList.remove('b-warn');
            el.classList.add('b-esc');
        } else {
            const h = Math.floor(diff / 3600000);
            const m = Math.floor((diff % 3600000) / 60000);
            el.innerHTML = "⏱ Next in " + h + "h " + m + "m";
            el.classList.remove('b-esc');
            el.classList.add('b-warn');
        }
    }
}

/* ══════════════════════════════════════════════
   AUTH & INIT
══════════════════════════════════════════════ */
async function login(){
  const roleEl = document.getElementById("role");
  const userEl = document.getElementById("username");
  const passEl = document.getElementById("password");
  const btn    = document.getElementById("loginBtn");

  const role = roleEl.value;
  const user = userEl.value.trim();
  const pass = passEl.value.trim();
  if (!user || !pass) { showToast("Enter username and password", "warn"); return; }

  btn.disabled  = true;
  btn.textContent = "Signing in…";
  try {
    const result = await syncSheet({action: "login", user, pass, role});
    if (!result.success || !result.data || !result.data.success) {
      throw new Error(result.data ? result.data.reason : (result.reason || "Login failed"));
    }
    const d = result.data;
    _saveSession(d.token, d.user, d.role);
    currentUser = d.user;
    currentRole = d.role;

    await fetchSheetsData();
    await loadWAConfigs();
    logAudit("Logged into the system");
    initApp();
  } catch(err) {
    showToast(err.message, "err");
  } finally {
    btn.disabled = false;
    btn.innerHTML = "Sign In &rarr;";
  }
}

function toggleSidebar() {
  document.querySelector('.sidebar').classList.toggle('open');
  document.getElementById('mobileOverlay').classList.toggle('on');
}

function initApp(){
  try {
    document.getElementById("loginPage").style.display="none";
    document.getElementById("appPage").style.display="block";
    
    let displayName = currentUser;
    let isManager = false;
    
    if (currentRole === "admin") {
        document.getElementById("sbAvatar").textContent = "AD";
        document.getElementById("sbAvatar").className = "avatar av-admin";
        displayName = "Administrator";
        document.getElementById("sbRole").textContent = "Admin";
    } else {
        const emp = employees.find(function(e) { return e.user === currentUser; });
        if(emp) {
            document.getElementById("sbAvatar").textContent = currentUser.slice(0,2).toUpperCase();
            document.getElementById("sbAvatar").className = "avatar av-emp";
            displayName = emp.name;
            isManager = emp.canAssign;
        }
        document.getElementById("sbRole").textContent = "Employee";
    }
    
    document.getElementById("sbName").textContent = displayName;

    if(currentRole === "admin"){
      const adminNavs = ["nav-employees","nav-notifications","nav-settings"];
      for(let i=0; i<adminNavs.length; i++) {
          const el = document.getElementById(adminNavs[i]);
          if(el) el.classList.remove("hidden");
      }
      const adminSec = document.querySelector(".admin-nav-section");
      if(adminSec) adminSec.classList.remove("hidden");
      
      document.getElementById("reminderSubtitle").textContent = "Manage & assign reminders to employees";
      
      const adminBtns = document.querySelectorAll(".admin-export-btns");
      for(let i=0; i<adminBtns.length; i++) adminBtns[i].classList.remove("hidden");
      // Show admin items in mobile bottom nav
      document.querySelectorAll(".admin-mbn").forEach(function(el){ el.classList.remove("hidden"); });
    } else {
      const adminNavs = ["nav-employees","nav-notifications","nav-settings"];
      for(let i=0; i<adminNavs.length; i++) {
          const el = document.getElementById(adminNavs[i]);
          if(el) el.classList.add("hidden");
      }
      const adminSec = document.querySelector(".admin-nav-section");
      if(adminSec) adminSec.classList.add("hidden");
      
      document.getElementById("reminderSubtitle").textContent = "Your assigned reminders";
      
      const adminBtns = document.querySelectorAll(".admin-export-btns");
      for(let i=0; i<adminBtns.length; i++) adminBtns[i].classList.add("hidden");
    }

    if(currentRole === "admin" || isManager) {
      document.getElementById("assignCard").classList.remove("hidden");
      document.getElementById("assignAuthTag").innerHTML = currentRole==="admin" ? "🔐 Admin Only" : "🔑 Authorized Manager";
      loadEmpSelect();
    } else {
      document.getElementById("assignCard").classList.add("hidden");
    }

    setSyncStatus("ok");
    loadWASelect();
    refreshAll();
    showSec("dashboard");
    
  } catch(e) {
      alert("Error initializing application. Please check console. " + e.message);
  }
}

function logout(){
  logAudit("Logged out of the system");
  syncSheet({action: "logout"}); // fire-and-forget
  _clearSession();
  currentUser = ""; currentRole = "";
  document.getElementById("loginPage").style.display  = "flex";
  document.getElementById("appPage").style.display = "none";
}

function showSec(id){
  document.querySelectorAll(".sp").forEach(function(s){ s.classList.remove("on"); });
  document.querySelectorAll(".nav-item").forEach(function(n){ n.classList.remove("active"); });
  document.querySelectorAll(".mbn-item").forEach(function(n){ n.classList.remove("active"); });

  var sec = document.getElementById("sec-"+id);
  if(sec) sec.classList.add("on");
  var ni = document.getElementById("nav-"+id);
  if(ni) ni.classList.add("active");
  var mni = document.getElementById("mbn-"+id);
  if(mni) mni.classList.add("active");

  if(window.innerWidth <= 768){
    var sb = document.querySelector('.sidebar');
    if(sb) sb.classList.remove('open');
    var ov = document.getElementById('mobileOverlay');
    if(ov) ov.classList.remove('on');
    window.scrollTo({top:0, behavior:'smooth'});
  }
  refreshAll();
}

/* ══════════════════════════════════════════════
   FILTERS & EXPORTS
══════════════════════════════════════════════ */
function toggleCustomDates(prefix) {
  const filterEl = document.getElementById(prefix+'FilterType');
  if(!filterEl) return;
  const isCustom = filterEl.value === 'custom';
  if(document.getElementById(prefix+'DateStart')) document.getElementById(prefix+'DateStart').classList.toggle('hidden', !isCustom);
  if(document.getElementById(prefix+'DateTo')) document.getElementById(prefix+'DateTo').classList.toggle('hidden', !isCustom);
  if(document.getElementById(prefix+'DateEnd')) document.getElementById(prefix+'DateEnd').classList.toggle('hidden', !isCustom);
}

function getFilteredData(prefix) {
  const filterEl = document.getElementById(prefix+'FilterType');
  if(!filterEl) return [];
  const filter = filterEl.value;
  const start = document.getElementById(prefix+'DateStart') ? document.getElementById(prefix+'DateStart').value : "";
  const end = document.getElementById(prefix+'DateEnd') ? document.getElementById(prefix+'DateEnd').value : "";
  
  let baseList = prefix === 'reminder' ? reminders : cards;
  
  if (currentRole !== 'admin') {
      if (prefix === 'reminder') {
          baseList = baseList.filter(function(t) { 
             return t.emp === currentUser || 
                    t.assignedBy === currentUser || 
                    (t.sharedWith && t.sharedWith.includes(currentUser)); 
          });
      } else {
          baseList = baseList.filter(function(c) { return c.emp === currentUser; });
      }
  }

  const now = new Date();
  const currMonth = now.getMonth();
  const currYear = now.getFullYear();

  return baseList.filter(function(item) {
      const itemDate = new Date(item.timestamp);
      if (filter === 'month') {
          return itemDate.getMonth() === currMonth && itemDate.getFullYear() === currYear;
      } else if (filter === 'prev_month') {
          const prevMonth = currMonth === 0 ? 11 : currMonth - 1;
          const prevYear = currMonth === 0 ? currYear - 1 : currYear;
          return itemDate.getMonth() === prevMonth && itemDate.getFullYear() === prevYear;
      } else if (filter === 'year') {
          return itemDate.getFullYear() === currYear;
      } else if (filter === 'custom') {
          if (!start || !end) return true;
          const sDate = new Date(start);
          const eDate = new Date(end);
          eDate.setHours(23, 59, 59, 999);
          return itemDate >= sDate && itemDate <= eDate;
      }
      return true;
  });
}

function formatDataForExport(data, prefix) {
  if (prefix === 'reminder') {
      return data.map(function(t) { return {
          Date: new Date(t.timestamp).toLocaleDateString(),
          Time: new Date(t.timestamp).toLocaleTimeString(),
          Employee: t.empName,
          "Emp ID": t.empId ? t.empId : "-",
          Reminder: t.title,
          "WA Group": t.waGroup ? t.waGroup : "-",
          Status: t.done ? "Completed" : (t.reminder >= 3 ? "Escalated" : "Pending"),
          Reminders: t.reminder,
          "Assigned By": t.assignedBy ? t.assignedBy : "Admin"
      };});
  } else {
      return data.map(function(c) { return {
          Date: new Date(c.timestamp).toLocaleDateString(),
          Employee: c.empName,
          "Emp ID": c.empId ? c.empId : "-",
          Reminder: c.reminderTitle,
          Reason: c.reason,
          Reminders: c.reminders
      };});
  }
}

function exportData(prefix, format) {
  if (currentRole !== 'admin') return;

  const rawData = getFilteredData(prefix);
  if (!rawData.length) { showToast("No data to export for selected filter", "warn"); return; }
  
  const exportArr = formatDataForExport(rawData, prefix);
  const nameLabel = prefix === 'reminder' ? 'Reminders' : 'Penalty_Cards';
  const fileName = "ReminderFlow_" + nameLabel + "_Report_" + new Date().toISOString().slice(0,10);

  logAudit("Exported " + nameLabel + " report in " + format.toUpperCase() + " format");

  if (format === 'csv' || format === 'excel') {
      const ws = XLSX.utils.json_to_sheet(exportArr);
      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, "Report");
      if (format === 'csv') {
          XLSX.writeFile(wb, fileName + ".csv");
      } else {
          XLSX.writeFile(wb, fileName + ".xlsx");
      }
      showToast(format.toUpperCase() + " Exported successfully", "ok");
  } else if (format === 'pdf') {
      try {
          const doc = new window.jspdf.jsPDF();
          const headers = Object.keys(exportArr[0]);
          const rows = exportArr.map(function(obj) { return Object.values(obj); });
          
          doc.setFontSize(14);
          doc.text("ReminderFlow Pro - " + nameLabel.replace('_', ' ') + " Report", 14, 15);
          doc.setFontSize(10);
          doc.text("Generated on: " + new Date().toLocaleString(), 14, 22);

          doc.autoTable({
              head: [headers],
              body: rows,
              startY: 28,
              theme: 'grid',
              styles: { fontSize: 8 },
              headStyles: { fillColor: [59, 130, 246] }
          });
          
          doc.save(fileName + ".pdf");
          showToast("PDF Exported successfully", "ok");
      } catch (err) {
          showToast("Error generating PDF. Check console.", "err");
      }
  }
}

/* ══════════════════════════════════════════════
   USER PROFILE & PASSWORD MANAGEMENT
══════════════════════════════════════════════ */
function changeMyPassword() {
  if (currentRole === "admin") {
      showToast("Administrator password cannot be changed here.", "warn");
      return;
  }
  const oldP = document.getElementById("profOldPass").value;
  const newP = document.getElementById("profNewPass").value;
  if(!oldP || !newP) { showToast("Please fill all password fields", "warn"); return; }
  
  const emp = employees.find(function(e) { return e.user === currentUser; });
  if(!emp || emp.pass !== oldP) { showToast("Incorrect current password", "err"); return; }
  
  emp.pass = newP;
  save();
  logAudit("Changed their personal account password");
  
  document.getElementById("profOldPass").value = "";
  document.getElementById("profNewPass").value = "";
  showToast("Password updated successfully!", "ok");
}

function adminResetPassword(user) {
  if (currentRole !== "admin") return;
  const newPass = prompt("Enter a new temporary password for user '" + user + "':", "default123");
  if (newPass) {
      const emp = employees.find(function(e) { return e.user === user; });
      if (emp) {
          emp.pass = newPass;
          save();
          logAudit("Administrator force-reset password for employee " + emp.name);
          showToast("Password for " + emp.name + " reset to: " + newPass, "ok");
      }
  }
}

/* ══════════════════════════════════════════════
   EMPLOYEES MANAGEMENT
══════════════════════════════════════════════ */
async function fetchEmployeesFromCRM(){
  const statusEl = document.getElementById("crmStatus");
  statusEl.innerHTML = "Fetching employees from CRM via server... ⏳";
  statusEl.style.color = "var(--txt)";

  try {
    const result = await syncSheet({action: "fetchFromCRM"});
    if (!result.success) throw new Error(result.reason || "Server error");

    const data = result.data;
    if (!data || !data.success) throw new Error((data && data.reason) || "CRM fetch failed");

    // Reload fresh data from DB so the JS arrays reflect the new employees
    await fetchSheetsData();
    loadEmpSelect();
    renderEmpTable();

    logAudit("Fetched employees from CRM: " + data.added + " added, " + data.updated + " updated");
    statusEl.innerHTML = "✅ CRM sync done: " + data.added + " new, " + data.updated + " updated (" + data.total + " total)";
    statusEl.style.color = "var(--ok)";
    showToast("Employees synced from CRM!", "ok");

  } catch (error) {
    console.error("CRM fetch error:", error);
    statusEl.innerHTML = "❌ Failed: " + error.message;
    statusEl.style.color = "var(--err)";
    showToast("CRM sync failed: " + error.message, "err");
  }
}

function addEmployee(){
  const id    = document.getElementById("empId").value.trim();
  const name  = document.getElementById("empName").value.trim();
  const cCode = document.getElementById("empCountryCode").value;
  const pNum  = document.getElementById("empPhone").value.trim();
  const email = document.getElementById("empEmail").value.trim();
  const department = document.getElementById("empDepartment").value.trim();
  const role = document.getElementById("empRole").value.trim();
  const user  = document.getElementById("empUser").value.trim();
  const pass  = document.getElementById("empPass").value.trim();
  const canAssign = document.getElementById("empCanAssign").checked;

  if(!id||!name||!pNum||!email||!user||!pass){ showToast("Fill all employee fields","warn"); return; }
  if(!/^\d{10}$/.test(pNum)){ showToast("Phone number must be exactly 10 digits","err"); return; }
  const phone = cCode + " " + pNum;

  if(employees.find(function(e) { return e.user === user; })){ showToast("Username already exists","err"); return; }
  if(employees.find(function(e) { return e.id === id; })){ showToast("Employee ID already exists","err"); return; }
  if(email.indexOf("@") === -1){ showToast("Enter a valid email address","err"); return; }

  employees.push({id:id, name:name, phone:phone, email:email, department:department, role:role, user:user, pass:pass, cCode:cCode, pNum:pNum, canAssign:canAssign});
  save();
  const idsToClear = ["empId","empName","empPhone","empEmail","empDepartment","empRole","empUser","empPass"];
  for(let i=0; i<idsToClear.length; i++) {
      if(document.getElementById(idsToClear[i])) document.getElementById(idsToClear[i]).value="";
  }
  document.getElementById("empCanAssign").checked = false;
  loadEmpSelect(); renderEmpTable();
  logAudit("Created new employee record for " + name + " [" + id + "]");
  showToast("Employee added successfully","ok");

  syncSheet({ action:"addEmployee", id:id, name:name, cCode:cCode, pNum:pNum, email:email, department:department, role:role, user:user, canAssign:canAssign, timestamp:new Date().toISOString() });
}

function toggleRights(user) {
  const emp = employees.find(function(e) { return e.user === user; });
  if (emp) {
    emp.canAssign = !emp.canAssign;
    save();
    renderEmpTable();
    logAudit((emp.canAssign ? 'Granted' : 'Revoked') + " reminder assignment permission for " + emp.name);
    showToast("Reminder assignment rights " + (emp.canAssign ? 'granted to' : 'revoked from') + " " + emp.name, 'ok');
    syncSheet({ action: "updateRights", user: emp.user, canAssign: emp.canAssign, timestamp: new Date().toISOString() });
  }
}

function deleteEmployee(user) {
  if (currentRole !== "admin") return;
  if (!confirm("Are you sure you want to delete this employee? This action cannot be undone and will remove all associated reminders and penalty cards.")) return;
  
  const empIndex = employees.findIndex(function(e) { return e.user === user; });
  if (empIndex === -1) return;
  
  const emp = employees[empIndex];
  const empName = emp.name;
  const empId = emp.id;
  
  reminders = reminders.filter(function(r) { return r.emp !== user; });
  cards = cards.filter(function(c) { return c.emp !== user; });
  
  employees.splice(empIndex, 1);
  
  save();
  loadEmpSelect();
  renderEmpTable();
  renderReminders();
  renderCards();
  
  logAudit("Deleted employee " + empName + " [" + empId + "] and all associated reminders and penalty cards");
  showToast("Employee and associated data deleted successfully", "ok");
  
  syncSheet({ action: "deleteEmployee", user: user, timestamp: new Date().toISOString() });
}

function openEditEmpModal(user) {
  if (currentRole !== "admin") return;
  const emp = employees.find(e => e.user === user);
  if (!emp) return;
  pendingEditEmpUser = user;
  document.getElementById("editEmpId").value = emp.id || "";
  document.getElementById("editEmpUser").value = emp.user || "";
  document.getElementById("editEmpName").value = emp.name || "";
  document.getElementById("editEmpCountryCode").value = emp.cCode || "+91";
  document.getElementById("editEmpPhone").value = emp.pNum || (emp.phone ? emp.phone.replace(emp.cCode, "").trim() : "");
  document.getElementById("editEmpEmail").value = emp.email || "";
  document.getElementById("editEmpDepartment").value = emp.department || "";
  document.getElementById("editEmpRole").value = emp.role || "";
  document.getElementById("editEmpPass").value = emp.pass || "";
  document.getElementById("editEmpCanAssign").checked = emp.canAssign || false;
  document.getElementById("editEmpModal").classList.add("on");
}

function closeEditEmpModal() {
  document.getElementById("editEmpModal").classList.remove("on");
  pendingEditEmpUser = null;
}

function saveEditedEmployee() {
  if (pendingEditEmpUser === null) return;
  const emp = employees.find(e => e.user === pendingEditEmpUser);
  if (!emp) return;

  const oldName = emp.name;

  emp.name = document.getElementById("editEmpName").value.trim();
  emp.cCode = document.getElementById("editEmpCountryCode").value;
  emp.pNum = document.getElementById("editEmpPhone").value.trim();
  emp.phone = emp.cCode + " " + emp.pNum;
  emp.email = document.getElementById("editEmpEmail").value.trim();
  emp.pass = document.getElementById("editEmpPass").value.trim();
  emp.canAssign = document.getElementById("editEmpCanAssign").checked;

  if (!emp.name || !emp.pNum || !emp.email || !emp.pass) {
    showToast("Please fill all required fields", "warn");
    return;
  }

  const ts = new Date().toISOString();
  save();
  renderEmpTable();
  loadEmpSelect();
  closeEditEmpModal();

  logAudit("Admin updated details for employee: " + emp.name);
  
  syncSheet({
    action: "editEmployee",
    empId: emp.id,
    oldName: oldName,
    newName: emp.name,
    newPhone: emp.phone,
    newEmail: emp.email,
    updatedBy: currentUser,
    timestamp: ts
  });

  showToast("Employee details updated", "ok");
}

function loadEmpSelect(){
  const sel1 = document.getElementById("empSelect");
  const sel2 = document.getElementById("reassignEmpSelect");
  let options = "";
  if(employees.length > 0) {
      options = employees.map(function(e) { return "<option value='" + e.user + "'>" + e.name + " [" + e.id + "]</option>"; }).join("");
  } else {
      options = "<option value=''>— No employees added yet —</option>";
  }
  if(sel1) sel1.innerHTML = options;
  if(sel2) sel2.innerHTML = options;
}

function renderEmpTable(){
  const wrap = document.getElementById("empTableWrap");
  if(!employees.length){
    wrap.innerHTML='<div class="empty-state"><div class="es-icon">👥</div><div class="es-title">No employees yet</div><div class="es-sub">Add employees below or fetch from CRM.</div></div>';
    return;
  }

  const totalPages = Math.ceil(employees.length / PAGE_SIZE);
  empPage = Math.max(1, Math.min(empPage, totalPages));
  const eStart = (empPage-1)*PAGE_SIZE;
  const eItems = employees.slice(eStart, eStart+PAGE_SIZE);
  const isMobile = window.innerWidth < 769;

  if(isMobile){
    // Mobile: compact cards
    let html = '';
    eItems.forEach(function(e){
      const tCount = reminders.filter(function(t){ return t.emp===e.user; }).length;
      html += `<div class="card" style="margin-bottom:.65rem;padding:.875rem;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:.5rem;">
          <div class="avatar av-emp" style="width:36px;height:36px;font-size:12px;">${e.name.slice(0,2).toUpperCase()}</div>
          <div style="flex:1;min-width:0;">
            <div style="font-weight:600;font-size:13px;">${e.name}</div>
            <div style="font-size:11px;color:var(--txt3);font-family:'DM Mono',monospace;">${e.id}</div>
          </div>
          <span class="badge b-info">${tCount} tasks</span>
        </div>
        <div style="font-size:11px;color:var(--txt3);display:flex;flex-wrap:wrap;gap:6px;margin-bottom:.6rem;">
          <span>${e.department||'—'}</span> · <span>${e.role||'—'}</span> · <span style="font-family:'DM Mono',monospace;">${e.phone}</span>
        </div>
        <div style="display:flex;gap:5px;flex-wrap:wrap;">
          <button class="btn btn-ghost btn-xs" onclick="openEditEmpModal('${e.user}')">✏️ Edit</button>
          <button class="btn btn-ghost btn-xs" onclick="toggleRights('${e.user}')">${e.canAssign?'✕ Revoke':'✓ Grant'}</button>
          <button class="btn btn-warn btn-xs" onclick="adminResetPassword('${e.user}')">🔑</button>
          <button class="btn btn-err btn-xs" onclick="deleteEmployee('${e.user}')">🗑️</button>
        </div>
      </div>`;
    });
    html += renderPagination(employees.length, empPage, 'setEmpPage');
    wrap.innerHTML = html;
  } else {
    let html = `<table><thead><tr><th>ID</th><th>Name</th><th>Dept</th><th>Role</th><th>Phone</th><th>Email</th><th>Login</th><th>Tasks</th><th>Actions</th></tr></thead><tbody>`;
    eItems.forEach(function(e){
      const tCount = reminders.filter(function(t){ return t.emp===e.user; }).length;
      html += `<tr>
        <td><span class="rem-pip">${e.id}</span></td>
        <td><strong>${e.name}</strong></td>
        <td>${e.department||'—'}</td>
        <td>${e.role||'—'}</td>
        <td style="font-family:'DM Mono',monospace;font-size:11px;">${e.phone}</td>
        <td style="font-size:11px;color:var(--txt2);">${e.email}</td>
        <td style="font-family:'DM Mono',monospace;font-size:11px;color:var(--txt3);">${e.user}</td>
        <td><span class="badge b-info">${tCount}</span></td>
        <td style="white-space:nowrap;">
          <button class="btn btn-ghost btn-xs" onclick="openEditEmpModal('${e.user}')">✏️</button>
          <button class="btn btn-ghost btn-xs" onclick="toggleRights('${e.user}')" style="margin-left:3px;">${e.canAssign?'✕ Revoke':'✓ Grant'}</button>
          <button class="btn btn-warn btn-xs" onclick="adminResetPassword('${e.user}')" style="margin-left:3px;">🔑</button>
          <button class="btn btn-err btn-xs" onclick="deleteEmployee('${e.user}')" style="margin-left:3px;">🗑️</button>
        </td>
      </tr>`;
    });
    html += `</tbody></table>`;
    html += renderPagination(employees.length, empPage, 'setEmpPage');
    wrap.innerHTML = html;
  }
}

/* ══════════════════════════════════════════════
   REMINDERS, REASSIGNMENT & EDIT
══════════════════════════════════════════════ */
function openImageModal(src) {
  document.getElementById('imgModalContent').src = src;
  document.getElementById('imgModal').classList.add('on');
}
function closeImageModal() {
  document.getElementById('imgModal').classList.remove('on');
  document.getElementById('imgModalContent').src = "";
}

function previewFile(input){ 
    const label = document.getElementById("fileLabel");
    if(label && input.files && input.files.length > 0) {
        label.textContent = input.files[0].name;
    } else {
        if(label) label.textContent = "";
    }
}

function clearAssignForm(){
    const ids = ["reminderTitle","reminderDesc","reminderWaGroup"];
    for(let i=0; i<ids.length; i++) {
        const el = document.getElementById(ids[i]);
        if(el) el.value = "";
    }
    const imgEl = document.getElementById("reminderImg");
    if(imgEl) imgEl.value = "";
    const lbl = document.getElementById("fileLabel");
    if(lbl) lbl.textContent = "";
}

/* ══════════════════════════════════════════════
   SCHEDULE CALCULATOR
══════════════════════════════════════════════ */
function calculateScheduleJS(hours) {
  const h = parseInt(hours) || 0;
  if (h <= 0) return [];
  if (h <= 2) return [h];
  if (h <= 4) return [Math.ceil(h/2), h];
  if (h <= 8) return [Math.round(h*0.33), Math.round(h*0.67), h];
  return [Math.round(h*0.25), Math.round(h*0.50), Math.round(h*0.75), h];
}

function previewSchedule() {
  const dur = parseInt(document.getElementById("reminderDuration").value) || 0;
  const el  = document.getElementById("schedulePreview");
  if (!el) return;
  if (!dur) { el.textContent = "⚠️ No automatic notifications or escalation"; return; }
  const sch = calculateScheduleJS(dur);
  el.textContent = "📅 Auto-notifications at: " + sch.map(h => h+"h").join(", ") + " — escalates at " + dur + "h if unresolved";
}

/* ══════════════════════════════════════════════
   WA CONFIG MANAGEMENT
══════════════════════════════════════════════ */
async function loadWAConfigs() {
  try {
    const res = await syncSheet({action: "getWAConfig"});
    if (res.success && res.data && res.data.configs) {
      waConfigs = res.data.configs;
      loadWASelect();
      renderWAConfigs();
    }
  } catch(e) { console.warn("Could not load WA configs:", e); }
}

function loadWASelect() {
  const sel = document.getElementById("reminderWAConfig");
  if (!sel) return;
  let opts = '<option value="">— Default Instance —</option>';
  waConfigs.forEach(function(c) {
    opts += '<option value="' + c.id + '"' + (c.is_default ? ' selected' : '') + '>' + c.name + ' [' + c.instance_id + ']</option>';
  });
  sel.innerHTML = opts;
}

function renderWAConfigs() {
  const wrap = document.getElementById("waConfigList");
  if (!wrap) return;
  if (!waConfigs.length) {
    wrap.innerHTML = '<p style="color:var(--txt3);font-size:13px;padding:.5rem 0;">No WhatsApp instances configured yet. Add one below.</p>';
    return;
  }
  let html = '<table><thead><tr><th>Name</th><th>Instance ID</th><th>API URL</th><th>Default</th><th>Actions</th></tr></thead><tbody>';
  waConfigs.forEach(function(c) {
    html += '<tr>' +
      '<td><strong>' + c.name + '</strong></td>' +
      '<td><span class="rem-pip">' + c.instance_id + '</span></td>' +
      '<td style="font-size:11px;color:var(--txt3);">' + c.api_url + '</td>' +
      '<td>' + (c.is_default ? '<span class="badge b-ok">✓ Default</span>' : '') + '</td>' +
      '<td>' +
        '<button class="btn btn-ghost btn-xs" onclick="editWAConfig(' + c.id + ')">✏️ Edit</button>' +
        '<button class="btn btn-err btn-xs" style="margin-left:4px;" onclick="deleteWAConfig(' + c.id + ')">🗑️</button>' +
      '</td></tr>';
  });
  html += '</tbody></table>';
  wrap.innerHTML = html;
}

async function saveWAConfig() {
  if (currentRole !== "admin") return;
  const id   = document.getElementById("waEditId").value || null;
  const name = document.getElementById("waName").value.trim();
  const inst = document.getElementById("waInstanceId").value.trim();
  const tok  = document.getElementById("waToken").value.trim();
  const url  = document.getElementById("waApiUrl").value.trim();
  const isDef= document.getElementById("waIsDefault").checked;
  if (!name || !inst) { showToast("Name and Instance ID required", "warn"); return; }
  const res = await syncSheet({action:"saveWAConfig", id, name, instance_id:inst, access_token:tok||'***', api_url:url, is_default:isDef});
  if (res.success && res.data && res.data.success) {
    showToast("WA instance saved!", "ok");
    clearWAForm();
    await loadWAConfigs();
  } else {
    showToast((res.data && res.data.reason) || "Save failed", "err");
  }
}

function editWAConfig(id) {
  const c = waConfigs.find(function(x) { return x.id === id; });
  if (!c) return;
  document.getElementById("waEditId").value    = c.id;
  document.getElementById("waName").value      = c.name;
  document.getElementById("waInstanceId").value= c.instance_id;
  document.getElementById("waToken").value     = "";
  document.getElementById("waApiUrl").value    = c.api_url;
  document.getElementById("waIsDefault").checked = c.is_default;
}

async function deleteWAConfig(id) {
  if (currentRole !== "admin") return;
  if (!confirm("Delete this WhatsApp instance?")) return;
  const res = await syncSheet({action:"deleteWAConfig", id});
  if (res.success) { showToast("Instance deleted", "ok"); await loadWAConfigs(); }
}

function clearWAForm() {
  ["waEditId","waName","waInstanceId","waToken"].forEach(function(id) { const el=document.getElementById(id); if(el) el.value=""; });
  document.getElementById("waApiUrl").value = "https://wa.clouddialer.in/api/v2/messages";
  document.getElementById("waIsDefault").checked = false;
}

function assignReminder(){
  let isManager = false;
  if(currentRole === "employee") {
      const e = employees.find(function(el){ return el.user === currentUser; });
      if(e && e.canAssign) isManager = true;
  }
  
  if(currentRole !== "admin" && !isManager){ showToast("You do not have permission to assign reminders","err"); return; }
  
  const emp     = document.getElementById("empSelect").value;
  const title   = document.getElementById("reminderTitle").value.trim();
  const desc    = document.getElementById("reminderDesc").value.trim();
  const waGroup = document.getElementById("reminderWaGroup").value.trim();
  const fileInput = document.getElementById("reminderImg");
  const file    = (fileInput && fileInput.files && fileInput.files.length > 0) ? fileInput.files[0] : null;
  const autoInterval   = parseInt(document.getElementById("reminderInterval").value) || 0;
  const totalDuration  = parseInt(document.getElementById("reminderDuration").value) || 0;
  const waConfigId     = parseInt(document.getElementById("reminderWAConfig").value) || null;
  const deadline       = totalDuration > 0 ? new Date(Date.now() + totalDuration * 3600000).toISOString() : null;
  const notifySchedule = calculateScheduleJS(totalDuration);

  if(!emp){ showToast("Select an employee","warn"); return; }
  if(!title||!desc){ showToast("Enter reminder title and description","warn"); return; }

  let empObj = employees.find(function(e) { return e.user === emp; });
  if(!empObj) empObj = {name: emp, id: "", phone: "", email: "", cCode: "", pNum: ""};
  
  const ts = new Date().toISOString();

  const doSave = async function(imgData){
    let waMsg = "Hello " + empObj.name + ",\n\n📌 *New Reminder Assigned (Reminder #1)*\n*Title:* " + title + "\n*Description:* " + desc + "\n";
    if(waGroup) waMsg += "*Group:* " + waGroup + "\n\n";
    const currentUrl = window.location.href.split('?')[0].split('#')[0];
    waMsg += "Please log in to your panel to check updates.\n";
    waMsg += "🔗 *Panel Link:* " + currentUrl + "\n\n";
    waMsg += "Signature : From Avyukta CRM Team";

    reminders.push({
      emp: emp, empName: empObj.name, empId: empObj.id, empPhone: empObj.phone, empEmail: empObj.email,
      cCode: empObj.cCode, pNum: empObj.pNum,
      title: title, desc: desc, waGroup: waGroup, img: imgData, reminder: 1, done: false, replies: [],
      autoInterval: autoInterval, totalDuration: totalDuration, deadline: deadline,
      notifySchedule: notifySchedule, notifiedHours: [0], escalated: false,
      waConfigId: waConfigId, sharedWith: [], lastRemindTs: ts, timestamp: ts, assignedBy: currentUser
    });
    markKnownReminders([reminders[reminders.length - 1]]);
    
    save(); clearAssignForm(); renderReminders(); renderDashboard(); 
    logAudit("Assigned new reminder '" + title + "' to " + empObj.name);
    showToast("Reminder assigned to " + empObj.name, "ok");

    syncSheet({ action:"addReminder", emp:emp, empName:empObj.name, empId:empObj.id, empCCode:empObj.cCode, empPNum:empObj.pNum, empEmail:empObj.email, empPhone:empObj.phone, title:title, desc:desc, waGroup:waGroup, autoInterval:autoInterval, reminder:1, status:"Pending", timestamp:ts, message:waMsg, sendSMS:true, sendEmail:true });

    await sendWhatsAppMessage(empObj.phone, waMsg);
    logNotif("reminder", empObj.name, title, "💬 WhatsApp", waMsg);
  };
  
  if(file){ 
      const r = new FileReader(); 
      r.onload = function(){ doSave(r.result); }; 
      r.readAsDataURL(file); 
  } else {
      doSave("");
  }
}

function buildNotifMsg(t, num, isAuto){
  const isPenalty = (num % 3 === 0);
  const remainingForCard = 3 - (num % 3);
  
  let nextActionTime = "N/A (Manual Trigger)";
  let nextActionLabel = "Next Reminder Time";
  
  if (t.autoInterval > 0) {
      const nextTs = Date.now() + (t.autoInterval * 60 * 60 * 1000);
      nextActionTime = new Date(nextTs).toLocaleString([], {dateStyle: 'short', timeStyle: 'short'});
      if (remainingForCard === 1 && !isPenalty) {
         nextActionLabel = "Next Reminder & Penalty Card Time";
      }
  }

  let msg = "Dear " + (t.empName || "Employee") + ",\n\n";
  msg += "This is " + (isAuto ? "an AUTOMATED " : "a ") + "Reminder #" + num + " for your pending task.\n\n";
  msg += "📌 *Reminder:* " + t.title + "\n";
  msg += "📝 *Details:* " + t.desc + "\n\n";
  
  msg += "⏱ *" + nextActionLabel + ":* " + nextActionTime + "\n";
  
  if (isPenalty) {
      msg += "⚠ *WARNING:* 1 Penalty Card has just been raised on your profile due to non-response.\n\n";
  } else {
      msg += "🚨 *Penalty Alert:* 1 Penalty Card will be raised if you miss " + remainingForCard + " more reminder(s).\n\n";
  }

  const currentUrl = window.location.href.split('?')[0].split('#')[0]; 
  msg += "🔗 *Panel Link:* " + currentUrl + "\n\n";
  msg += "Please acknowledge and update the status of this reminder at the earliest.\n\n";
  msg += "Signature : From Avyukta CRM Team";
  
  return msg;
}

function openReminder(i){
  let isManager = false;
  if(currentRole === "employee") {
      const e = employees.find(function(el){ return el.user === currentUser; });
      if(e && e.canAssign) isManager = true;
  }
  
  if(currentRole !== "admin" && !isManager){ showToast("Only authorized managers can send reminders","err"); return; }
  
  const list = getFilteredData('reminder');
  const t = list[i];
  pendingRemIdx = reminders.indexOf(t);
  const num = (t.reminder ? t.reminder : 0) + 1;
  document.getElementById("remEmpName").textContent  = (t.empName ? t.empName : "") + " [" + (t.empId ? t.empId : "—") + "]";
  document.getElementById("remEmpEmail").textContent = t.empEmail ? t.empEmail : "Not set";
  document.getElementById("remEmpPhone").textContent = t.empPhone ? t.empPhone : "Not set";
  document.getElementById("remPreview").textContent  = buildNotifMsg(t, num, false);
  document.getElementById("remModal").classList.add("on");
}
function closeRemModal(){ document.getElementById("remModal").classList.remove("on"); pendingRemIdx=null; }

async function executeReminder(i, isAuto) {
  if(typeof isAuto === 'undefined') isAuto = false;
  if(!reminders[i].reminder) reminders[i].reminder = 0;
  reminders[i].reminder++;
  const t = reminders[i];
  const ts = new Date().toISOString();
  t.lastRemindTs = ts;

  const msg = buildNotifMsg(t, t.reminder, isAuto);

  let safeCCode = t.cCode ? t.cCode : "";
  let safePNum = t.pNum ? t.pNum : "";
  if (!safeCCode && t.empPhone) {
      const parts = t.empPhone.split(" ");
      if (parts.length > 1) { safeCCode = parts[0]; safePNum = parts.slice(1).join(" "); }
      else { safePNum = t.empPhone; }
  }

  if(t.reminder > 0 && t.reminder % 3 === 0){
    cards.push({ emp:t.emp, empName:t.empName, empId:t.empId, empPhone:t.empPhone, empEmail:t.empEmail, reminderTitle:t.title, desc:t.desc, reason:"No response after " + t.reminder + " reminders", reminders:t.reminder, timestamp:ts });
    logAudit("System issued penalty card to " + t.empName + " for " + t.reminder + " unanswered reminders on '" + t.title + "'");
    
    syncSheet({ action:"penaltyCard", emp:t.emp, empName:t.empName, empId:t.empId, empCCode:safeCCode, empPNum:safePNum, empEmail:t.empEmail, reminderTitle:t.title, desc:t.desc, reason:"No response after " + t.reminder + " reminders", reminders:t.reminder, timestamp:ts });

    const cardMsg = "⚠ *PENALTY CARD ISSUED*\n\nDear " + t.empName + ",\n1 Penalty Card has been issued to your profile due to continued non-response.\n\n*Reminder:* " + t.title + "\n*Reminders Ignored:* " + t.reminder + "\n\nSignature : From Avyukta CRM Team";
    await sendWhatsAppMessage(t.empPhone, cardMsg);
    logNotif("card", t.empName, t.title, "💬 WhatsApp", cardMsg);
  }

  const result = await syncSheet({
    action:"reminder", emp:t.emp, empName:t.empName, empId:t.empId, empPhone:t.empPhone, empEmail:t.empEmail,
    reminderTitle:t.title, reminderDesc:t.desc, reminderCount:t.reminder, status:(t.done ? "Completed" : (t.reminder >= 3 ? "Escalated" : "Pending")),
    message:msg, sendEmail:true, sendSMS:true, isAuto: isAuto, timestamp:ts
  });

  await sendWhatsAppMessage(t.empPhone, msg);

  logNotif("reminder", t.empName, t.title, "💬 WhatsApp", msg);
  logNotif("reminder", t.empName, t.title, "📧 Email", msg);
  logAudit("Triggered " + (isAuto ? 'Auto-' : '') + "Reminder #" + t.reminder + " to " + t.empName + " for '" + t.title + "'");

  save(); refreshAll();
  const typeTag = isAuto ? "Auto-Reminder" : "Reminder";

  if(result && result.success){ showToast(typeTag + " #" + t.reminder + " sent to " + t.empName, (t.reminder>=3 ? "err" : "warn")); } 
  else if (result && result.reason==="no-url") { showToast(typeTag + " logged locally. Check DB API.", "warn"); }
}

async function confirmReminder(){
  if(pendingRemIdx===null) return;
  const i = pendingRemIdx;
  closeRemModal();
  await executeReminder(i, false);
}

function openReassign(i) {
  if (currentRole !== "admin") { showToast("Only administrators can reassign reminders.", "err"); return; }
  const list = getFilteredData('reminder');
  const t = list[i];
  pendingReassignIdx = reminders.indexOf(t);
  
  document.getElementById("reassignReminderTitle").textContent = t.title;
  document.getElementById("reassignOldEmp").textContent = t.empName + " [" + (t.empId ? t.empId : "") + "]";
  
  const selectBox = document.getElementById("reassignEmpSelect");
  let opts = "";
  for(let j=0; j<employees.length; j++) {
      const e = employees[j];
      const dis = (e.user === t.emp) ? "disabled" : "";
      opts += "<option value='" + e.user + "' " + dis + ">" + e.name + " [" + e.id + "]</option>";
  }
  selectBox.innerHTML = opts;
  
  document.getElementById("reassignModal").classList.add("on");
}

function closeReassignModal() {
  document.getElementById("reassignModal").classList.remove("on");
  pendingReassignIdx = null;
}

async function confirmReassign() {
  if (pendingReassignIdx === null) return;
  const newEmpUser = document.getElementById("reassignEmpSelect").value;
  if (!newEmpUser) { showToast("Select an employee to reassign to", "warn"); return; }
  
  const t = reminders[pendingReassignIdx];
  const oldEmpName = t.empName;
  const newEmp = employees.find(function(e) { return e.user === newEmpUser; });
  const ts = new Date().toISOString();

  let reassignerName = currentUser;
  if (currentRole === "admin") {
      reassignerName = "Admin";
  } else {
      const uEmp = employees.find(function(e) { return e.user === currentUser; });
      if(uEmp) reassignerName = uEmp.name;
  }

  t.prevEmpName = oldEmpName;
  t.emp = newEmp.user;
  t.empName = newEmp.name;
  t.empId = newEmp.id;
  t.empPhone = newEmp.phone;
  t.empEmail = newEmp.email;
  t.reminder = 1; 
  t.lastRemindTs = ts;

  const note = "Reminder reassigned from " + oldEmpName + " to " + newEmp.name + " by " + reassignerName;
  if(!t.replies) t.replies = [];
  t.replies.push({ text: note, ts: ts, sender: reassignerName, senderId: currentUser, role: currentRole });

  save(); renderReminders(); renderDashboard(); closeReassignModal();
  logAudit("Reassigned reminder '" + t.title + "' from " + oldEmpName + " to " + newEmp.name);
  showToast("Reminder reassigned successfully", "ok");

  syncSheet({ action:"reassignReminder", newEmp:t.emp, newEmpName:t.empName, newEmpId:t.empId, newEmpCCode:newEmp.cCode, newEmpPNum:newEmp.pNum, newEmpEmail:t.empEmail, reminderTitle:t.title, status:"Reassigned from " + oldEmpName, reminderTimestamp:t.timestamp, timestamp:ts, oldEmpName:oldEmpName, assignedBy: reassignerName });

  const currentUrl = window.location.href.split('?')[0].split('#')[0];
  const waMsg = "Hello " + newEmp.name + ",\n\n📌 *Reminder Reassigned to You (Reminder #1)*\n*Title:* " + t.title + "\n*Description:* " + t.desc + "\n\nThis reminder was reassigned by " + reassignerName + ".\n\n🔗 *Panel Link:* " + currentUrl + "\n\nSignature : From Avyukta CRM Team";
  await sendWhatsAppMessage(newEmp.phone, waMsg);
  logNotif("reminder", newEmp.name, t.title, "💬 WhatsApp", waMsg);
}

function openEditReminder(i) {
    if (currentRole !== "admin") return;
    const list = getFilteredData('reminder');
    const t = list[i];
    pendingEditRemIdx = reminders.indexOf(t);
    document.getElementById("editRemTitle").value = t.title;
    document.getElementById("editRemDesc").value = t.desc;
    document.getElementById("editRemWaGroup").value = t.waGroup || "";
    document.getElementById("editRemInterval").value = t.autoInterval || "0";
    document.getElementById("editRemModal").classList.add("on");
}

function closeEditModal() {
    document.getElementById("editRemModal").classList.remove("on");
    pendingEditRemIdx = null;
}

function saveEditedReminder() {
    if (pendingEditRemIdx === null) return;
    const t = reminders[pendingEditRemIdx];
    const oldTitle = t.title;
    
    t.title = document.getElementById("editRemTitle").value.trim();
    t.desc = document.getElementById("editRemDesc").value.trim();
    t.waGroup = document.getElementById("editRemWaGroup").value.trim();
    t.autoInterval = parseInt(document.getElementById("editRemInterval").value) || 0;

    logAudit("Edited reminder: '" + oldTitle + "' -> '" + t.title + "'");
    syncSheet({ action: "editReminder", emp: t.emp, reminderTitle: oldTitle, newTitle: t.title, desc: t.desc, waGroup: t.waGroup, autoInterval: t.autoInterval, timestamp: new Date().toISOString() });

    save();
    renderReminders();
    closeEditModal();
    showToast("Reminder updated successfully", "ok");
}

function openChat(i) {
  const list = getFilteredData('reminder');
  const t = list[i];
  currentChatIdx = reminders.indexOf(t);
  
  document.getElementById('chatReminderTitle').textContent = t.title;
  renderChatMessages();
  document.getElementById('chatWidget').classList.add('show');
}
function closeChatModal() {
  document.getElementById('chatWidget').classList.remove('show');
  currentChatIdx = null;
}
function renderChatMessages() {
  if (currentChatIdx === null) return;
  const t = reminders[currentChatIdx];
  const box = document.getElementById('chatMessages');
  if (!t.replies || !t.replies.length) {
    box.innerHTML = `<div style="text-align:center; color:var(--txt3); font-size:12px; margin-top:20px;">No messages yet. Start the conversation!</div>`;
    return;
  }
  
  let html = "";
  for(let i=0; i<t.replies.length; i++) {
      const r = t.replies[i];
      const text = r.text ? r.text : (typeof r === 'string' ? r : ''); 
      const sender = r.sender ? r.sender : (typeof r === 'string' ? (t.empName || "System") : 'System');
      const isMe = (r.senderId === currentUser) || (currentRole === 'admin' && sender === 'Admin');
      const isSystem = (r.role === 'system');
      const alignClass = isMe ? 'me' : 'other';
      const displaySender = isMe ? 'You' : sender;
      const timeStr = r.ts ? new Date(r.ts).toLocaleTimeString([],{hour:"2-digit",minute:"2-digit"}) : '';
      
      if (isSystem) {
          html += `<div style="text-align:center; font-size:10px; color:var(--txt3); margin: 10px 0; font-family:'DM Mono', monospace;">— ${text} —<br><span style="font-size:8px;">${timeStr}</span></div>`;
          continue;
      }
      
      html += `<div class="chat-msg ${alignClass}">
        <span class="name">${displaySender}</span>
        <div class="bubble">${text}</div>
        <span class="time">${timeStr}</span>
      </div>`;
  }
  box.innerHTML = html;
  box.scrollTop = box.scrollHeight;
}
function sendChatReply() {
  if (currentChatIdx === null) return;
  const input = document.getElementById('chatInput');
  const val = input.value.trim();
  if (!val) return;
  
  const i = currentChatIdx;
  const t = reminders[i];
  const ts = new Date().toISOString();
  
  let senderName = currentUser;
  if(currentRole === 'admin') {
      senderName = "Admin";
  } else {
      const emp = employees.find(function(e) { return e.user === currentUser; });
      if(emp) senderName = emp.name;
  }
  
  if(!t.replies) t.replies=[];
  t.replies.push({text:val, ts:ts, sender: senderName, senderId: currentUser, role: currentRole});
  
  logAudit("Sent a chat message on reminder '" + t.title + "'");

  syncSheet({ 
      action:"reply", 
      emp:t.emp, 
      empName:t.empName, 
      empId:t.empId, 
      reminderTitle:t.title, 
      reply:val, 
      repliedBy:senderName, 
      role: currentRole, 
      timestamp:ts 
  });
  
  save(); 
  renderChatMessages(); 
  renderReminders(); 
  input.value = "";
}

function openAddUserModal() {
    if (currentChatIdx === null) return;
    const t = reminders[currentChatIdx];
    const select = document.getElementById("chatAddUserSelect");
    
    let opts = "";
    let addedCount = 0;
    employees.forEach(e => {
        if (e.user !== t.emp && e.user !== t.assignedBy && !(t.sharedWith && t.sharedWith.includes(e.user))) {
            opts += `<option value="${e.user}">${e.name} [${e.id}]</option>`;
            addedCount++;
        }
    });
    
    if (addedCount === 0) opts = `<option value="">— No other available employees —</option>`;
    select.innerHTML = opts;
    document.getElementById("addUserModal").classList.add("on");
}

function closeAddUserModal() {
    document.getElementById("addUserModal").classList.remove("on");
}

async function confirmAddUserToChat() {
    if (currentChatIdx === null) return;
    const select = document.getElementById("chatAddUserSelect");
    const newUser = select.value;
    if (!newUser) { showToast("No valid employee selected", "warn"); return; }
    
    const t = reminders[currentChatIdx];
    if (!t.sharedWith) t.sharedWith = [];
    t.sharedWith.push(newUser);
    
    const empObj = employees.find(e => e.user === newUser);
    const addedName = empObj ? empObj.name : newUser;
    
    let senderName = currentUser;
    if (currentRole === 'admin') {
        senderName = "Admin";
    } else {
        const uEmp = employees.find(function(e) { return e.user === currentUser; });
        if(uEmp) senderName = uEmp.name;
    }
    
    const sysMsg = `${senderName} added ${addedName} to the chat`;
    const ts = new Date().toISOString();
    
    if (!t.replies) t.replies = [];
    t.replies.push({text: sysMsg, ts: ts, sender: "System", senderId: "system", role: "system"});
    
    syncSheet({ action:"reply", emp:t.emp, empName:t.empName, empId:t.empId, reminderTitle:t.title, reply:sysMsg, repliedBy:"System", role: "system", timestamp:ts });
    logAudit(sysMsg);
    
    save(); 
    renderChatMessages();
    closeAddUserModal();
    showToast(`${addedName} was added to the chat`, "ok");
    
    if (empObj && empObj.phone) {
        const currentUrl = window.location.href.split('?')[0].split('#')[0];
        const waMsg = "Hello " + addedName + ",\n\nYou have been added to a reminder chat by " + senderName + ".\n\n*Reminder:* " + t.title + "\n\nPlease log in to check the updates.\n\n🔗 *Panel Link:* " + currentUrl + "\n\nSignature : From Avyukta CRM Team";
        await sendWhatsAppMessage(empObj.phone, waMsg);
        logNotif("reminder", addedName, t.title, "💬 WhatsApp", waMsg);
    }
}

function getStatus(t){ return t.done ? "Completed" : ((t.reminder && t.reminder >= 3) ? "Escalated" : "Pending"); }
function getBadgeClass(s){ return s==="Completed" ? "b-done" : (s==="Escalated" ? "b-esc" : "b-pending"); }

function renderReminders(){
  const wrap = document.getElementById("reminderListWrap");
  const list = getFilteredData('reminder');

  if(!list.length){
    wrap.innerHTML='<div class="empty-state"><div class="es-icon">📋</div><div class="es-title">No reminders found</div><div class="es-sub">No reminders match the selected filter period.</div></div>';
    return;
  }

  const totalPages = Math.ceil(list.length / PAGE_SIZE);
  reminderPage = Math.max(1, Math.min(reminderPage, totalPages));
  const start = (reminderPage-1)*PAGE_SIZE;
  const items = list.slice(start, start+PAGE_SIZE);
  const isMobile = window.innerWidth < 769;

  if(currentRole==="admin" && !isMobile){
    let html = `<div class="card" style="padding:0;overflow:hidden;"><div class="tbl-wrap"><table>
      <thead><tr><th>Employee</th><th>Reminder</th><th>Schedule / Deadline</th><th>Sent</th><th>Status</th><th style="min-width:220px;">Action</th></tr></thead>
      <tbody>`;
    for(let i=0; i<items.length; i++) {
        const t = items[i]; const absIdx = start+i;
        const st = getStatus(t); const remCount = t.reminder||0; const hot = remCount>=2;
        const ri = reminders.indexOf(t);
        const dueStr = t.deadline ? new Date(t.deadline).toLocaleDateString('en-IN',{day:'2-digit',month:'short'}) + ' ' + new Date(t.deadline).toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'}) : '';
        const notifProg = (t.notifySchedule && t.notifySchedule.length) ? `<span style="font-size:10px;color:var(--txt3);">${(t.notifiedHours||[]).length}/${t.notifySchedule.length} sent</span>` : '';
        html += `<tr>
          <td><strong>${t.empName||''}</strong><div style="font-size:10px;color:var(--txt3);">${t.empId||'—'}</div>${t.prevEmpName?`<div style="font-size:9px;color:var(--warn);">↩ ${t.prevEmpName}</div>`:''}</td>
          <td><div style="font-weight:600;font-size:13px;">${t.title}</div><div style="font-size:11px;color:var(--txt3);max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${t.desc}</div>${t.waGroup?`<span class="badge b-info" style="font-size:9px;margin-top:3px;">WA: ${t.waGroup}</span>`:''}</td>
          <td>${t.totalDuration>0?`<span class="badge b-auto">⏱ ${t.totalDuration}h</span>${dueStr?`<div style="font-size:10px;color:var(--txt3);margin-top:2px;">Due: ${dueStr}</div>`:''}`:t.autoInterval>0?`<span class="badge b-auto">⏱ ${t.autoInterval}h loop</span>`:`<span style="font-size:11px;color:var(--txt3);">Manual</span>`}</td>
          <td><span class="rem-pip ${hot?'hot':''}">${remCount}</span> ${notifProg} ${getCountdownHTML(t)}</td>
          <td><span class="badge ${getBadgeClass(st)}">${st}</span></td>
          <td style="white-space:nowrap;">${!t.done?`
            <button class="btn btn-warn btn-xs" onclick="openReminder(${absIdx})">🔔</button>
            <button class="btn btn-ghost btn-xs" onclick="openReassign(${absIdx})" title="Reassign">🔄</button>
            <button class="btn btn-ghost btn-xs" onclick="openEditReminder(${absIdx})" title="Edit">✏️</button>
            <button class="btn btn-ok btn-xs" onclick="markDone(${ri})" title="Done">✓</button>
            <button class="btn btn-err btn-xs" onclick="deleteReminder(${ri})" title="Delete">🗑️</button>
            <button class="btn btn-ghost btn-xs" onclick="openChat(${absIdx})">💬 ${t.replies?t.replies.length:0}</button>
          `:`<span style="font-size:11px;color:var(--ok);">✓ Done</span>
            <button class="btn btn-err btn-xs" style="margin-left:4px;" onclick="deleteReminder(${ri})">🗑️</button>
            <button class="btn btn-ghost btn-xs" style="margin-left:4px;" onclick="openChat(${absIdx})">💬</button>`}</td>
        </tr>`;
    }
    html += `</tbody></table></div><div style="padding:.5rem 1.25rem 1rem;">${renderPagination(list.length, reminderPage, 'setReminderPage')}</div></div>`;
    wrap.innerHTML = html;
  } else {
    // Card view (mobile or employee)
    let html = "";
    for(let i=0; i<items.length; i++) {
      const t = items[i]; const absIdx = start+i;
      const ri = reminders.indexOf(t);
      const st = getStatus(t); const remCount = t.reminder||0;
      const isMine = t.emp === currentUser;
      const isAssignedByMe = t.assignedBy === currentUser && !isMine;
      const isShared = t.sharedWith && t.sharedWith.includes(currentUser);
      const isAdminCard = currentRole === "admin";
      const dueStr = t.deadline ? `<span class="badge" style="background:rgba(217,119,6,.1);color:var(--warn);font-size:9px;">⏰ ${new Date(t.deadline).toLocaleDateString('en-IN',{day:'2-digit',month:'short'})}</span>` : '';
      const notifProg = (t.totalDuration>0 && t.notifySchedule && t.notifySchedule.length) ? `<span class="rem-pip" style="font-size:10px;">${(t.notifiedHours||[]).length}/${t.notifySchedule.length} notifs</span>` : '';

      html += `<div class="reminder-card ${st==="Escalated"?"esc":""} ${t.done?"done-card":""}">
        ${t.img?`<img class="reminder-thumb" src="${t.img}" onclick="openImageModal('${t.img}')" title="Click to enlarge">`:``}
        <div class="reminder-body" style="flex:1;min-width:0;">
          <div class="reminder-title" style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;flex-wrap:wrap;">
            <span>${t.title}${isAdminCard?`<span style="font-size:11px;color:var(--txt3);display:block;font-family:normal;font-weight:400;">${t.empName||''} · ${t.empId||''}</span>`:''}</span>
            <span class="badge ${getBadgeClass(st)}" style="flex-shrink:0;">${st}</span>
          </div>
          <div class="reminder-desc">${t.desc}</div>
          <div class="reminder-meta">
            <span class="rem-pip ${remCount>=2?'hot':''}">🔔 ${remCount}</span>
            ${dueStr} ${notifProg}
            ${t.waGroup?`<span class="badge b-info" style="font-size:9px;">WA: ${t.waGroup}</span>`:''}
            ${t.prevEmpName?`<span class="badge b-warn" style="font-size:9px;">↩ ${t.prevEmpName}</span>`:''}
            ${isShared?`<span class="badge b-auto" style="font-size:9px;">Shared</span>`:''}
            ${getCountdownHTML(t)}
          </div>
          ${!t.done?`<div class="reply-area">
            <button class="btn btn-ghost btn-sm" onclick="openChat(${absIdx})">💬 Chat${t.replies&&t.replies.length?` (${t.replies.length})`:''}  </button>
            ${isMine||isAdminCard?`<button class="btn btn-ok btn-sm" onclick="markDone(${ri})">✓ Done</button>`:''}
            ${(isAdminCard||isAssignedByMe)?`<button class="btn btn-warn btn-sm" onclick="openReminder(${absIdx})">🔔 Remind</button>`:''}
            ${isAdminCard?`<button class="btn btn-ghost btn-sm" onclick="openReassign(${absIdx})">🔄</button><button class="btn btn-ghost btn-sm" onclick="openEditReminder(${absIdx})">✏️</button><button class="btn btn-err btn-sm" onclick="deleteReminder(${ri})">🗑️</button>`:''}
          </div>`:`<div style="font-size:11px;color:var(--ok);margin-top:6px;font-weight:500;">✓ Completed${isAdminCard?`<button class="btn btn-err btn-xs" style="margin-left:8px;" onclick="deleteReminder(${ri})">🗑️</button>`:''}  </div>`}
        </div></div>`;
    }
    html += renderPagination(list.length, reminderPage, 'setReminderPage');
    wrap.innerHTML = html;
  }
  updateCountdowns();
}

function markDone(i){
  reminders[i].done=true; const ts=new Date().toISOString();
  let userName = currentUser;
  const emp = employees.find(function(e){ return e.user === currentUser; });
  if(emp) {
      userName = emp.name;
  } else if (currentRole === "admin") {
      userName = "Admin";
  }

  logAudit("Marked reminder '" + reminders[i].title + "' as completed");
  syncSheet({ action:"statusUpdate", emp:reminders[i].emp, empName:userName, empId:reminders[i].empId, reminderTitle:reminders[i].title, reminderTimestamp:reminders[i].timestamp, status:"Completed", timestamp:ts });
  save(); renderReminders(); renderDashboard(); showToast("Reminder marked complete","ok");
}

function deleteReminder(i){
  if(currentRole !== "admin"){ showToast("Only administrators can delete reminders","err"); return; }
  if(!confirm("Are you sure you want to permanently delete this reminder?")) return;
  
  const t = reminders[i];
  logAudit("Admin deleted reminder: '" + t.title + "' assigned to " + t.empName);
  
  syncSheet({ action:"deleteReminder", emp:t.emp, reminderTitle:t.title, timestamp:new Date().toISOString() });
  
  reminders.splice(i, 1);
  save(); 
  renderReminders(); 
  renderDashboard(); 
  showToast("Reminder deleted successfully","ok");
}

/* ══════════════════════════════════════════════
   PAGINATION
══════════════════════════════════════════════ */
function renderPagination(total, current, setFn, prefix) {
  if (total <= PAGE_SIZE) return '';
  const totalPages = Math.ceil(total / PAGE_SIZE);
  const start = (current - 1) * PAGE_SIZE + 1;
  const end   = Math.min(current * PAGE_SIZE, total);
  let html = '<div class="pagination">';
  html += '<button class="pg-btn" ' + (current===1?'disabled':'onclick="'+setFn+'('+( current-1)+')"') + '>‹</button>';
  for (let p = 1; p <= totalPages; p++) {
    if (p===1 || p===totalPages || (p>=current-1 && p<=current+1)) {
      html += '<button class="pg-btn' + (p===current?' pg-active':'') + '" onclick="'+setFn+'('+p+')">' + p + '</button>';
    } else if (p===current-2 || p===current+2) {
      html += '<span class="pg-ellipsis">…</span>';
    }
  }
  html += '<button class="pg-btn" ' + (current===totalPages?'disabled':'onclick="'+setFn+'('+(current+1)+')"') + '>›</button>';
  html += '<span class="pg-info">' + start + '–' + end + ' / ' + total + '</span>';
  html += '</div>';
  return html;
}
function setReminderPage(p){ reminderPage=p; renderReminders(); window.scrollTo({top:0,behavior:'smooth'}); }
function setCardPage(p)    { cardPage=p;     renderCards();     window.scrollTo({top:0,behavior:'smooth'}); }
function setEmpPage(p)     { empPage=p;      renderEmpTable();  window.scrollTo({top:0,behavior:'smooth'}); }

function updateCardBadge(){
  const b  = document.getElementById("cardBadge");
  const mb = document.getElementById("mbnCardBadge");
  const list = getFilteredData('card');
  const n = list.length;
  if(b){  if(n){ b.textContent=n;  b.classList.remove("hidden");  } else b.classList.add("hidden");  }
  if(mb){ if(n){ mb.textContent=n; mb.classList.remove("hidden"); } else mb.classList.add("hidden"); }
}

function renderCards(){
  const wrap = document.getElementById("cardListWrap");
  const list = getFilteredData('card');

  if(!list.length){
    wrap.innerHTML='<div class="empty-state"><div class="es-icon">✅</div><div class="es-title">No penalty cards</div><div class="es-sub">No cards found for the selected period.</div></div>';
    updateCardBadge(); return;
  }

  const totalPages = Math.ceil(list.length / PAGE_SIZE);
  cardPage = Math.max(1, Math.min(cardPage, totalPages));
  const cStart = (cardPage-1)*PAGE_SIZE;
  const cItems = list.slice(cStart, cStart+PAGE_SIZE);

  let html = "";
  for(let i=0; i<cItems.length; i++) {
    const c = cItems[i]; const absIdx = cStart+i;
    const isAuto = c.reason && c.reason.includes('auto');
    html += `<div class="pen-item" onclick="showPenModal(${absIdx})">
      <div style="flex:1;min-width:0;">
        <div style="font-size:13px;font-weight:600;display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
          ⚠ ${c.empName} ${c.empId?`<span class="rem-pip" style="font-size:10px;">${c.empId}</span>`:''}
          ${isAuto?`<span class="badge b-auto" style="font-size:9px;">Auto</span>`:''}
        </div>
        <div style="font-size:12px;color:var(--txt2);margin-top:2px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${c.reminderTitle}</div>
        <div style="font-size:11px;color:var(--txt3);margin-top:1px;">${c.reason} · ${new Date(c.timestamp).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'})}</div>
      </div>
      <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
        <span class="badge b-esc">${c.reminders}×</span>
        <span style="color:var(--txt3);">›</span>
      </div>
    </div>`;
  }
  html += renderPagination(list.length, cardPage, 'setCardPage');
  wrap.innerHTML = html;
  updateCardBadge();
}

function showPenModal(idx){
  const list = getFilteredData('card');
  const c = list[idx];
  pendingCardIdx = cards.indexOf(c);
  
  document.getElementById("penModalContent").innerHTML=`
    <div class="row2"><div class="mf"><div class="mf-l">Employee</div><div class="mf-v">${c.empName}</div></div><div class="mf"><div class="mf-l">Employee ID</div><div class="mf-v">${c.empId ? c.empId : "—"}</div></div></div>
    <div class="row2"><div class="mf"><div class="mf-l">📧 Email</div><div class="mf-v" style="font-size:13px;color:var(--txt2);">${c.empEmail ? c.empEmail : "—"}</div></div><div class="mf"><div class="mf-l">📱 Phone</div><div class="mf-v" style="font-size:13px;color:var(--txt2);">${c.empPhone ? c.empPhone : "—"}</div></div></div>
    <div class="mf"><div class="mf-l">Reminder</div><div class="mf-v">${c.reminderTitle}</div></div>
    <div class="mf"><div class="mf-l">Reason</div><div class="mf-v" style="color:var(--err);">${c.reason}</div></div>
    <div class="mf"><div class="mf-l">Total Reminders</div><div class="mf-v">${c.reminders}</div></div>`;
  
  const waiveBtn = document.getElementById("waiveCardBtn");
  if (currentRole === "admin") {
      waiveBtn.classList.remove("hidden");
  } else {
      waiveBtn.classList.add("hidden");
  }

  document.getElementById("penModal").classList.add("on");
}

function closePenModal(){ 
    document.getElementById("penModal").classList.remove("on"); 
    pendingCardIdx = null;
}

function waiveCard() {
    if (currentRole !== "admin" || pendingCardIdx === null) return;
    if (!confirm("Are you sure you want to waive off this penalty card?")) return;
    
    const c = cards[pendingCardIdx];
    logAudit("Waived off penalty card for " + c.empName + " regarding '" + c.reminderTitle + "'");
    syncSheet({ action: "waiveCard", emp: c.emp, reminderTitle: c.reminderTitle, timestamp: new Date().toISOString() });
    
    cards.splice(pendingCardIdx, 1);
    save();
    renderCards();
    renderDashboard();
    closePenModal();
    showToast("Penalty card waived off", "ok");
}

function renderDashboard(){
  document.getElementById("dashSubText").textContent = currentRole === "admin" ? "System-wide reminders & team overview" : "Your personal reminder & performance overview";

  let total=0, done=0, pend=0, esc=0;
  let targetCards = 0;
  let recent = [];

  if (currentRole === "admin") {
      total = reminders.length;
      for(let i=0; i<reminders.length; i++) {
          if(reminders[i].done) done++;
          else if(reminders[i].reminder && reminders[i].reminder >= 3) esc++;
          else pend++;
      }
      for(let i=reminders.length-1; i>=0 && recent.length<8; i--) recent.push(reminders[i]);
  } else {
      const myReminders = getFilteredData('reminder'); 
      total = myReminders.length;
      for(let i=0; i<myReminders.length; i++) {
          if(myReminders[i].done) done++;
          else if(myReminders[i].reminder && myReminders[i].reminder >= 3) esc++;
          else pend++;
      }
      targetCards = cards.filter(function(c) { return c.emp === currentUser; }).length;
      for(let i=myReminders.length-1; i>=0 && recent.length<8; i--) recent.push(myReminders[i]);
  }
  
  if (currentRole === "admin") {
      document.getElementById("statsRow").innerHTML=`
        <div class="stat"><div class="stat-icon">📋</div><div class="s-label">Total Reminders</div><div class="s-val" style="color:var(--acc);">${total}</div><div class="s-sub">All assigned</div></div>
        <div class="stat"><div class="stat-icon">✓</div><div class="s-label">Completed</div><div class="s-val" style="color:var(--ok);">${done}</div><div class="s-sub">${total?Math.round(done/total*100):0}% rate</div></div>
        <div class="stat"><div class="stat-icon">⏳</div><div class="s-label">Pending</div><div class="s-val" style="color:var(--warn);">${pend}</div><div class="s-sub">Awaiting action</div></div>
        <div class="stat"><div class="stat-icon">⚠</div><div class="s-label">Escalated</div><div class="s-val" style="color:var(--err);">${esc}</div><div class="s-sub">Penalty issued</div></div>`;
  } else {
      document.getElementById("statsRow").innerHTML=`
        <div class="stat"><div class="stat-icon">📋</div><div class="s-label">My Reminders</div><div class="s-val" style="color:var(--acc);">${total}</div><div class="s-sub">All assigned</div></div>
        <div class="stat"><div class="stat-icon">✓</div><div class="s-label">My Completed</div><div class="s-val" style="color:var(--ok);">${done}</div><div class="s-sub">${total?Math.round(done/total*100):0}% completion</div></div>
        <div class="stat"><div class="stat-icon">⏳</div><div class="s-label">My Pending</div><div class="s-val" style="color:var(--warn);">${pend}</div><div class="s-sub">Action required</div></div>
        <div class="stat"><div class="stat-icon">⚠</div><div class="s-label">My Cards</div><div class="s-val" style="color:var(--err);">${targetCards}</div><div class="s-sub">Penalties</div></div>`;
  }
  
  document.getElementById("recentActTitle").textContent = currentRole === "admin" ? "Recent Activity" : "My Recent Reminders";

  if(recent.length > 0) {
      let rHtml = `<table><thead><tr><th>Employee</th><th>ID</th><th>Reminder</th><th>Reminders</th><th>Status</th></tr></thead><tbody>`;
      for(let i=0; i<recent.length; i++) {
          const t = recent[i];
          const st = getStatus(t);
          rHtml += `<tr><td><strong>${t.empName}</strong></td><td><span class="rem-pip">${t.empId ? t.empId : "—"}</span></td><td>${t.title}</td><td style="text-align:center;">${t.reminder ? t.reminder : 0}</td><td><span class="badge ${getBadgeClass(st)}">${st}</span></td></tr>`;
      }
      rHtml += `</tbody></table>`;
      document.getElementById("dashReminders").innerHTML = rHtml;
  } else {
      document.getElementById("dashReminders").innerHTML = `<p style="text-align:center;padding:2rem;color:var(--txt3);font-size:13px;">No reminders yet.</p>`;
  }
}

function renderNotifLog(){
  const wrap = document.getElementById("notifLogWrap");
  if(!notifLog.length){ wrap.innerHTML=`<p style="text-align:center;padding:2rem;">No notifications sent yet.</p>`; return; }
  
  let html = `<div class="notif-log">`;
  for(let i=0; i<notifLog.length; i++) {
      const n = notifLog[i];
      const chan = n.channel ? n.channel : "";
      const ico = chan.indexOf("WhatsApp") > -1 ? "💬" : (chan.indexOf("📧") === 0 ? "📧" : "📱");
      html += `<div class="nl-item"><span class="nl-ico">${ico}</span>
        <div class="nl-text"><strong style="color:var(--txt);">${n.empName}</strong> — ${n.reminderTitle}<span style="background:var(--s3);font-size:10px;padding:1px 6px;border-radius:4px;margin-left:4px;">${chan}</span><div style="color:var(--txt3);font-size:11px;margin-top:2px;">${new Date(n.ts).toLocaleString()}</div></div></div>`;
  }
  html += `</div>`;
  wrap.innerHTML = html;
}

function clearNotifLog(){ notifLog=[]; save(); renderNotifLog(); showToast("Log cleared","ok"); }

function saveSettings(){ scriptUrl = document.getElementById("scriptUrl").value.trim() || "api.php"; setSyncStatus(scriptUrl ? "ok" : "not-configured"); showToast("Settings saved","ok"); }
async function testConnection(){
  const url = document.getElementById("scriptUrl").value.trim() || "api.php";
  scriptUrl = url; showToast("Testing DB connection…");
  document.getElementById("testStatusBadge").style.display = "none";
  
  const result = await syncSheet({ action:"test", timestamp:new Date().toISOString() });
  const badge = document.getElementById("testStatusBadge"); badge.style.display = "inline-block";
  
  if(result && result.success && result.data && result.data.success !== false){ 
      badge.className = "sync-status sync-ok"; badge.textContent = "✓ Connected"; showToast("Connection successful!","ok"); 
  } else { 
      const reason = (result && result.data && result.data.reason) || (result && result.reason) || "error";
      badge.className = "sync-status sync-err"; badge.textContent = "✕ Failed"; showToast("Connection failed: " + reason,"err"); 
  }
}

let toastTimer = null;
function showToast(msg, type){
  if(!type) type = "info";
  try {
    const icons={ok:"✓",warn:"⚠",err:"✕",info:"ℹ"}, colors={ok:"ok",warn:"warn",err:"err",info:"acc"}, t=document.getElementById("toast");
    if(!t) { alert(msg); return; }
    document.getElementById("toastIco").textContent=icons[type]||"ℹ"; 
    document.getElementById("toastIco").style.color=`var(--${colors[type]||'acc'})`;
    document.getElementById("toastMsg").textContent=msg; 
    t.classList.add("on");
    if(toastTimer) clearTimeout(toastTimer); toastTimer = setTimeout(function(){ t.classList.remove("on"); }, 4000);
  } catch(e) {
    alert(msg);
  }
}

function refreshAll(){ 
  renderDashboard(); 
  renderReminders(); 
  renderCards(); 
  renderEmpTable(); 
  renderNotifLog();
  
  const empCountEl = document.getElementById("dataEmpCount");
  const remCountEl = document.getElementById("dataRemCount");
  const cardCountEl = document.getElementById("dataCardCount");
  const notifCountEl = document.getElementById("dataNotifCount");
  
  if (empCountEl) empCountEl.textContent = employees.length;
  if (remCountEl) remCountEl.textContent = reminders.length;
  if (cardCountEl) cardCountEl.textContent = cards.length;
  if (notifCountEl) notifCountEl.textContent = notifLog.length;
}

document.getElementById("penModal").addEventListener("click",function(e){if(e.target===this)closePenModal();});

// Re-render adaptive views on screen resize / device rotation
var _resizeTimer;
window.addEventListener('resize', function(){
  clearTimeout(_resizeTimer);
  _resizeTimer = setTimeout(function(){
    if(currentUser){
      renderReminders();
      renderEmpTable();
    }
  }, 200);
});
document.getElementById("remModal").addEventListener("click",function(e){if(e.target===this)closeRemModal();});
document.getElementById("reassignModal").addEventListener("click",function(e){if(e.target===this)closeReassignModal();});
document.getElementById("editRemModal").addEventListener("click",function(e){if(e.target===this)closeEditModal();});
document.getElementById("addUserModal").addEventListener("click",function(e){if(e.target===this)closeAddUserModal();});
document.getElementById("editEmpModal").addEventListener("click",function(e){if(e.target===this)closeEditEmpModal();});
</script>
</body>
</html>
