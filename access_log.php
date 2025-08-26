<?php
include("connection.php");
session_start();


if (!isset($_SESSION['email'])) {
    header('Location: signup.php');
    exit;
}

$username = $_SESSION['email'];


$stmt = $conn->prepare("SELECT * FROM signup WHERE email = ? LIMIT 1");
$stmt->bind_param('s', $username);
$stmt->execute();
$userResult = $stmt->get_result();
$user = $userResult->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "User not found.";
    exit;
}


$logStmt = $conn->prepare(
    "SELECT a.* FROM access_logs a
     JOIN vehicle_details v ON LOWER(a.rc_no) = LOWER(v.rc_no)
     WHERE v.email = ?
     ORDER BY a.access_time DESC"
);
$logStmt->bind_param('s', $user['email']);
$logStmt->execute();
$logResult = $logStmt->get_result();

$logs = [];
while ($row = $logResult->fetch_assoc()) {
    $logs[] = $row;
}
$logStmt->close();

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>access log . Vehicall</title>

    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        :root {
            --bg: #0f1724;
            --card: #0b1220ff;
            --muted: #94a3b8;
            --accent: #00d2ff;
            --brand-2: #00d2ff;
            
            --glass: rgba(255, 255, 255, 0.04);
            --glass-2: rgba(255, 255, 255, 0.02);
            --white: #e6eef8;
            --success: #16a34a;
            --danger: #ef4444;
            --panel: 24, 28, 48;
        }

        * {
            box-sizing: border-box
        }

        body {
            margin: 0;
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
            background: radial-gradient(circle at 10% 10%, rgba(99, 102, 241, 0.06), transparent 10%),
                linear-gradient(180deg, #031025 0%, #071026 100%);
            color: var(--white);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            padding: 0;
        }

        .wrap {
            max-width: 1150px;
            margin: 0 auto;
            padding: 24px;
        }

        
        .topbar {
            position: sticky;
            top: 0;
            z-index: 50;
            backdrop-filter: saturate(140%) blur(10px);
            background: rgba(var(--panel), .55);
            border-bottom: 1px solid rgba(255, 255, 255, .06);
        }

        .topbar-inner {
            max-width: 1100px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 800;
            letter-spacing: .3px;
        }

        .brand i {
            font-size: 22px;
            color: var(--brand-2);
            filter: drop-shadow(0 0 8px rgba(0, 210, 255, .5));
        }

        .brand span {
            font-size: 18px;
        }

        .nav {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .nav a {
            text-decoration: none;
            color: #cfd7ff;
            font-weight: 600;
            padding: 10px 14px;
            border-radius: 12px;
            transition: all .2s ease;
            border: 1px solid transparent;
        }

        .nav a:hover {
            background: rgba(255, 255, 255, .06);
            border-color: rgba(255, 255, 255, .08);
        }

        .nav .logout {
            color: #ffb4b4;
        }

        .logo {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            background: var(--accent);
            box-shadow: 0 6px 30px rgba(37, 99, 235, 0.12);
            font-weight: 800;
            font-size: 18px;
            color: white
        }

        h1 {
            font-size: 20px;
            margin: 0
        }

        .sub {
            color: var(--muted);
            font-size: 13px
        }

        
        .controls {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        .search {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            background: var(--glass);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.03);
            min-width: 220px
        }

        .search input {
            background: transparent;
            border: 0;
            outline: 0;
            color: var(--white);
            font-size: 14px;
            width: 180px
        }

        .btn {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.06);
            padding: 10px 14px;
            border-radius: 10px;
            color: var(--white);
            cursor: pointer;
            font-weight: 600
        }

        .btn.primary {
            background: var(--accent);
            border: 0;
            color: #041126
        }

        
        .table-wrap {
            margin-top: 18px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.02), transparent);
            padding: 18px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.03)
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px
        }

        thead th {
            padding: 12px 10px;
            text-align: left;
            color: var(--muted);
            font-weight: 600;
            font-size: 12px;
            letter-spacing: 0.6px
        }

        tbody tr {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.01), rgba(255, 255, 255, 0.00));
            border-radius: 8px;
            margin-bottom: 10px
        }

        tbody td {
            padding: 12px 10px;
            border-top: 1px solid rgba(255, 255, 255, 0.02);
            vertical-align: top
        }

        
        tbody tr:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(2, 6, 23, 0.6)
        }

        
        .badge {
            display: inline-block;
            padding: 6px 8px;
            border-radius: 999px;
            font-size: 12px;
            background: var(--glass-2);
            color: var(--white);
            border: 1px solid rgba(255, 255, 255, 0.03)
        }

        .otp-success {
            background: rgba(22, 163, 74, 0.12);
            color: var(--success);
            border: 1px solid rgba(22, 163, 74, 0.12)
        }

        .otp-fail {
            background: rgba(239, 68, 68, 0.08);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.06)
        }

        
        .cards {
            display: none
        }

        @media (max-width:880px) {
            table {
                display: none
            }

            .cards {
                display: block;
                display: grid;
                grid-template-columns: 1fr;
                gap: 12px
            }

            .card {
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.02), rgba(255, 255, 255, 0.01));
                padding: 14px;
                border-radius: 12px;
                border: 1px solid rgba(255, 255, 255, 0.03)
            }

            .card .row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 8px
            }

            .card .small {
                font-size: 12px;
                color: var(--muted)
            }

            .controls {
                flex-direction: column;
                align-items: stretch
            }

            .search input {
                width: 100%
            }
        }

        
        .muted {
            color: var(--muted)
        }

        .rc {
            font-weight: 700;
            letter-spacing: 0.6px
        }

        .meta {
            font-size: 12px;
            color: var(--muted)
        }

        
        .note {
            margin-top: 12px;
            font-size: 13px;
            color: var(--muted);
            text-align: center
        }

        
        @media (max-width:1150px) {

            thead th:nth-child(3),
            thead th:nth-child(4),
            thead th:nth-child(6),
            thead th:nth-child(7) {
                display: none
            }

            tbody td:nth-child(3),
            tbody td:nth-child(4),
            tbody td:nth-child(6),
            tbody td:nth-child(7) {
                display: none
            }
        }

        
        .icon-btn {
            background: transparent;
            border: 0;
            color: var(--muted);
            cursor: pointer;
            padding: 6px;
            border-radius: 8px
        }

        .hamburger {
            display: none;
            background: none;
            border: none;
            color: #cfd7ff;
            font-size: 22px;
            cursor: pointer;
        }

        
        @media (max-width: 640px) {
            .hamburger {
                display: block;
            }

            .topbar-inner {
                display: flex;
                flex-direction: row;
            }


            .nav {
                display: none;
                
                flex-direction: column;
                background: rgba(var(--panel), 0.95);
                position: absolute;
                top: 100%;
                right: 0;
                left: 0;
                padding: 12px 0;
                border-bottom: 1px solid rgba(255, 255, 255, .08);
            }

            .nav a {
                display: block;
                text-align: center;
                padding: 10px;
                font-size: 0.9rem;
            }

            .nav.active {
                display: flex;
                
            }
        }
    </style>
</head>

<body>
    <header class="topbar">
        <div class="topbar-inner">
            <div class="brand">
                <i class="fa-solid fa-car-rear"></i><span>VEHICALL</span>
            </div>

            
            <button class="hamburger" id="hamburger">
                <i class="fa-solid fa-bars"></i>
            </button>

            <nav class="nav" id="nav">
                <a href="home.php"><i class="fa-solid fa-gauge"></i>&nbsp;Dashboard</a>
                <a href="register-form.php"><i class="fa-regular fa-id-card"></i>&nbsp;Registration</a>
                <a href="scanner.php"><i class="fa-solid fa-qrcode"></i>&nbsp;Fetch</a>
                <a href="#" id="career-link"><i class="fa-regular fa-lightbulb"></i>&nbsp;Career</a>
                <a class="logout" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i>&nbsp;Logout</a>
            </nav>
        </div>
    </header>

    <div class="wrap">
        <header>


            <div class="controls">
                <div class="search">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21 21l-4.35-4.35" stroke="rgba(255,255,255,0.6)" stroke-width="1.6"
                            stroke-linecap="round" stroke-linejoin="round" />
                        <circle cx="11" cy="11" r="6" stroke="rgba(255,255,255,0.6)" stroke-width="1.6"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <input id="q" placeholder="Search by RC number, name, email, city..." aria-label="Search logs">
                </div>
                <button class="btn" id="downloadCsv">Export CSV</button>
                <button class="btn primary" id="refresh">Refresh</button>
            </div>
        </header>

        <main class="table-wrap">
            <?php if (count($logs) === 0) { ?>
                <div style="padding:36px; text-align:center; color:var(--muted);">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" style="opacity:0.9">
                        <path d="M3 13h18" stroke="rgba(255,255,255,0.12)" stroke-width="1.6" stroke-linecap="round"
                            stroke-linejoin="round" />
                        <path d="M12 3v18" stroke="rgba(255,255,255,0.12)" stroke-width="1.6" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                    <h2 style="margin:8px 0 0">No access logs found</h2>
                    <p class="muted" style="margin-top:8px">You don't have any registered activity for your vehicles yet.
                    </p>
                </div>
            <?php } else { ?>

                
                <table id="logsTable">
                    <thead>
                        <tr>
                            <th>Vehicle No</th>
                            <th>User Mobile</th>
                            <th>Username</th>
                            <th>User Email</th>
                            <th>Reason</th>
                            <th>IP Address</th>
                            <th>User Agent</th>
                            <th>Access Location</th>
                            <th>Lat / Long</th>
                            <th>Provider</th>
                            <th>OTP Status</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log) { ?>
                            <tr data-rc="<?= htmlspecialchars(strtoupper($log['rc_no'])) ?>"
                                data-name="<?= htmlspecialchars($log['user_name']) ?>"
                                data-email="<?= htmlspecialchars($log['user_email']) ?>"
                                data-city="<?= htmlspecialchars($log['city']) ?>">
                                <td><span class="rc"><?= htmlspecialchars(strtoupper($log['rc_no'])) ?></span> <button
                                        class="icon-btn" title="Copy RC"
                                        onclick="copyText('<?= htmlspecialchars(strtoupper($log['rc_no'])) ?>')">📋</button>
                                </td>
                                <td><?= htmlspecialchars($log['user_mobile']) ?></td>
                                <td><?= htmlspecialchars($log['user_name']) ?></td>
                                <td><?= htmlspecialchars($log['user_email']) ?></td>
                                <td><?= htmlspecialchars($log['reason']) ?></td>
                                <td><?= htmlspecialchars($log['ip_address']) ?></td>
                                <td style="max-width:220px;word-wrap:break-word;"> <?= htmlspecialchars($log['user_agent']) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($log['village']) ?>,
                                    <?= htmlspecialchars($log['city']) ?>,
                                    <?= htmlspecialchars($log['district']) ?>,
                                    <?= htmlspecialchars($log['state']) ?>,
                                    <?= htmlspecialchars($log['country']) ?> - <?= htmlspecialchars($log['pincode']) ?>
                                </td>
                                <td style="width:150px;">Lat: <?= htmlspecialchars($log['latitude']) ?><br>Long:
                                    <?= htmlspecialchars($log['longitude']) ?></td>
                                <td><?= htmlspecialchars($log['isp']) ?></td>
                                <td>
                                    <?php if (strtolower($log['otp_status']) === 'success') { ?>
                                        <span class="badge otp-success">Success</span>
                                    <?php } else { ?>
                                        <span class="badge otp-fail"><?= htmlspecialchars($log['otp_status']) ?></span>
                                    <?php } ?>
                                </td>
                                <td class="meta"><?= htmlspecialchars($log['access_time']) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

                
                <div class="cards" id="cardsList">
                    <?php foreach ($logs as $log) { ?>
                        <article class="card" data-rc="<?= htmlspecialchars(strtoupper($log['rc_no'])) ?>"
                            data-name="<?= htmlspecialchars($log['user_name']) ?>"
                            data-email="<?= htmlspecialchars($log['user_email']) ?>"
                            data-city="<?= htmlspecialchars($log['city']) ?>">
                            <div class="row">
                                <div>
                                    <div class="rc"><?= htmlspecialchars(strtoupper($log['rc_no'])) ?></div>
                                    <div class="small muted"><?= htmlspecialchars($log['user_mobile']) ?> ·
                                        <?= htmlspecialchars($log['user_email']) ?></div>
                                </div>
                                <div style="text-align:right">
                                    <div class="small muted"><?= htmlspecialchars($log['access_time']) ?></div>
                                    <div style="margin-top:6px;"><span
                                            class="badge <?= strtolower($log['otp_status']) === 'success' ? 'otp-success' : 'otp-fail' ?>"><?= htmlspecialchars($log['otp_status']) ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="row" style="margin-top:8px">
                                <div class="small">Reason: <strong><?= htmlspecialchars($log['reason']) ?></strong></div>
                                <div class="small">ISP: <?= htmlspecialchars($log['isp']) ?></div>
                            </div>

                            <div style="margin-top:8px" class="small">Location: <?= htmlspecialchars($log['city']) ?>,
                                <?= htmlspecialchars($log['state']) ?> · Lat: <?= htmlspecialchars($log['latitude']) ?>, Long:
                                <?= htmlspecialchars($log['longitude']) ?></div>

                            <div style="margin-top:10px" class="small muted">User Agent: <span
                                    style="display:block;word-break:break-word;max-width:100%"><?= htmlspecialchars($log['user_agent']) ?></span>
                            </div>
                        </article>
                    <?php } ?>
                </div>

            <?php } ?>

            <div class="note">Showing <strong><?= count($logs) ?></strong> log<?= count($logs) !== 1 ? 's' : '' ?> ·
                Logged in as <strong><?= htmlspecialchars($user['email']) ?></strong></div>
            <p class="footer" style="text-align:center;color:#98a2bd;padding-top:20px">© <?php echo date('Y'); ?>
                Vehicall • Developed By Syed Raza Hussain</p>
        </main>
    </div>

    <script>
        const hamburger1 = document.getElementById('hamburger');
        const nav1 = document.getElementById('nav');

        hamburger1.addEventListener('click', () => {
            nav1.classList.toggle('active');
        });

        
        document.querySelectorAll('.nav a').forEach(link => {
            link.addEventListener('click', () => nav1.classList.remove('active'));
        });
    </script>


    <script>
        
        const q = document.getElementById('q');
        q.addEventListener('input', function () {
            const term = this.value.trim().toLowerCase();
            
            document.querySelectorAll('#logsTable tbody tr').forEach(tr => {
                const rc = tr.getAttribute('data-rc')?.toLowerCase() || '';
                const name = tr.getAttribute('data-name')?.toLowerCase() || '';
                const email = tr.getAttribute('data-email')?.toLowerCase() || '';
                const city = tr.getAttribute('data-city')?.toLowerCase() || '';
                const hay = rc + ' ' + name + ' ' + email + ' ' + city + ' ' + tr.innerText.toLowerCase();
                tr.style.display = hay.includes(term) ? '' : 'none';
            });
            
            document.querySelectorAll('#cardsList .card').forEach(card => {
                const rc = card.getAttribute('data-rc')?.toLowerCase() || '';
                const name = card.getAttribute('data-name')?.toLowerCase() || '';
                const email = card.getAttribute('data-email')?.toLowerCase() || '';
                const city = card.getAttribute('data-city')?.toLowerCase() || '';
                const hay = rc + ' ' + name + ' ' + email + ' ' + city + ' ' + card.innerText.toLowerCase();
                card.style.display = hay.includes(term) ? '' : 'none';
            });
        });

        
        function copyText(text) {
            navigator.clipboard?.writeText(text).then(() => {
                alert('Copied: ' + text);
            }).catch(() => {
                const el = document.createElement('input');
                el.value = text; document.body.appendChild(el); el.select(); document.execCommand('copy'); el.remove(); alert('Copied: ' + text);
            });
        }

        
        document.getElementById('downloadCsv').addEventListener('click', () => {
            const rows = [];
            
            const trs = Array.from(document.querySelectorAll('#logsTable tbody tr')).filter(r => r.style.display !== 'none');
            if (trs.length > 0) {
                const headers = ['Vehicle No', 'User Mobile', 'Username', 'User Email', 'Reason', 'IP Address', 'User Agent', 'Location', 'Latitude', 'Longitude', 'Provider', 'OTP Status', 'Access Time'];
                rows.push(headers.join(','));
                trs.forEach(tr => {
                    const cells = Array.from(tr.children).map(td => '"' + td.innerText.replace(/"/g, '""') + '"');
                    rows.push(cells.join(','));
                });
            } else {
                
                const cards = Array.from(document.querySelectorAll('#cardsList .card')).filter(c => c.style.display !== 'none');
                if (cards.length === 0) { alert('No data to export'); return; }
                rows.push('Vehicle No,User Mobile,Username,User Email,Reason,Location,Latitude,Longitude,Provider,OTP Status,Access Time');
                cards.forEach(card => {
                    const txt = card.innerText.replace(/\n/g, ' ').replace(/"/g, '""');
                    rows.push('"' + txt + '"');
                });
            }

            const blob = new Blob([rows.join('\n')], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url; a.download = 'vehicle_access_logs.csv'; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
        });

        
        document.getElementById('refresh').addEventListener('click', () => location.reload());
    </script>
</body>

</html>