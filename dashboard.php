<?php
// MCVoR Renewal STC Dashboard
// Data file for storing statuses
$data_file = __DIR__ . '/data.json';

// List of stations
$stations = [
    'CC1 DBG', 'CC2 BBS', 'CC3 EPN', 'CC4 PMN', 'CC5 NCH', 'CC6 SDM', 'CC7 MBT', 'CC8 DKT', 'CC9 PYL', 'CC10 MPS',
    'CC11 TSG', 'CC12 BLY', 'CC13 SER', 'CC14 LRC', 'CC15 BSH', 'CC16 MRM', 'CC17 CDT', 'CC18 BKB', 'CC19 BTN', 'CC20 FRR',
    'CC21 HLV', 'CC22 BNV', 'CC23 ONH', 'CC24 KRG', 'CC25 HPV', 'CC26 PPJ', 'CC27 LBD', 'CC28 TLB', 'CC29 HBF', 'CC30 KPL',
    'CC31 CTM', 'CC32 PER', 'CC33 MRB', 'CC34 BFT', 'OCC', 'BOCC', 'KCDE'
];

// List of test cases
$testcases = [
    'STC-C860E-STN-MCR-INSP-001', 'STC-C860E-STN-MCR-INSP-002', 'STC-C860E-STN-MCR-INSP-003', 'STC-C860E-STN-MCR-INSP-004', 'STC-C860E-STN-MCR-INSP-005',
    'STC-C860E-STN-MCR-PW-001', 'STC-C860E-STN-MCR-LOGIN-002', 'STC-C860E-STN-MCR-ARCH-003', 'STC-C860E-STN-MCR-ARCH-004', 'STC-C860E-STN-MCR-CONF-005',
    'STC-C860E-STN-MCR-USER-006', 'STC-C860E-STN-MCR-REP-007', 'STC-C860E-STN-MCR-LOG-008', 'STC-C860E-STN-MCR-009', 'STC-C860E-STN-MCR-RED-010',
    'STC-C860E-STN-MCR-ALM-011', 'STC-C860E-STN-MCR-RFAN-012', 'STC-C860E-STN-MCR-CLK-013', 'STC-C860E-STN-MCR-014', 'STC-C860E-STN-MCR-ANL-015',
    'STC-C860E-STN-MCR-PA-016', 'STC-C860E-STN-MCR-017', 'STC-C860E-STN-MCR-018', 'STC-C860E-STN-MCR-019', 'STC-C860E-STN-MCR-020',
    'STC-C860E-STN-MCR-021', 'STC-C860E-STN-MCR-SOAK-022', 'Huawei Switch Hardening'
];
$extra_testcases_OCC_BOCC = [
    'ITSec Benchmark for Microsoft Windows 10T - MCVoR',
    'ITSec Benchmark for Microsoft Windows 11 Pro Replay',
    'ITSec Benchmark for Microsoft Windows 7 Pro Replay',
    'Antivirus Hardening',
    'LANTech Switch Hardening'
];

// Status options
$status_options = ['Not Started', 'In-Progress', 'Passed'];

// Load or initialize data
if (!file_exists($data_file)) {
    $data = [];
    foreach ($stations as $station) {
        $station_testcases = $testcases;
        if (in_array($station, ['OCC', 'BOCC'])) {
            $station_testcases = array_merge($testcases, $extra_testcases_OCC_BOCC);
        }
        foreach ($station_testcases as $tc) {
            $data[$station][$tc] = 'Not Started';
        }
    }
    file_put_contents($data_file, json_encode($data));
} else {
    $data = json_decode(file_get_contents($data_file), true);
}

// Handle updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['station'], $_POST['testcase'], $_POST['status'])) {
    $station = $_POST['station'];
    $testcase = $_POST['testcase'];
    $status = $_POST['status'];
    $valid_testcases = $testcases;
    if (in_array($station, ['OCC', 'BOCC'])) {
        $valid_testcases = array_merge($testcases, $extra_testcases_OCC_BOCC);
    }
    if (in_array($station, $stations) && in_array($testcase, $valid_testcases) && in_array($status, $status_options)) {
        $data[$station][$testcase] = $status;
        file_put_contents($data_file, json_encode($data));
        echo 'OK';
        exit;
    }
    echo 'Invalid';
    exit;
}

// Calculate summary
$summary = [];
$total_status = ['Not Started' => 0, 'In-Progress' => 0, 'Passed' => 0];
foreach ($stations as $station) {
    $summary[$station] = ['Not Started' => 0, 'In-Progress' => 0, 'Passed' => 0];
    $station_testcases = $testcases;
    if (in_array($station, ['OCC', 'BOCC'])) {
        $station_testcases = array_merge($testcases, $extra_testcases_OCC_BOCC);
    }
    foreach ($station_testcases as $tc) {
        $status = isset($data[$station][$tc]) ? $data[$station][$tc] : 'Not Started';
        $summary[$station][$status]++;
        $total_status[$status]++;
    }
}
$total_cases = $total_status['Not Started'] + $total_status['In-Progress'] + $total_status['Passed'];
$completed_percent = $total_cases > 0 ? round($total_status['Passed'] / $total_cases * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MCVoR Renewal STC Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            background: #f6f8fa;
            margin: 0;
            padding: 0;
            color: #222;
        }
        .header-bar {
            background: linear-gradient(90deg, #003366 60%, #00509e 100%);
            color: #fff;
            padding: 24px 0 18px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            text-align: center;
            margin-bottom: 32px;
        }
        .header-bar h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 700;
            letter-spacing: 2px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 16px;
        }
        .dashboard-section {
            display: flex;
            flex-wrap: wrap;
            gap: 32px;
            margin-bottom: 32px;
            align-items: flex-start;
        }
        .dashboard-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 24px 32px 32px 32px;
            flex: 1 1 350px;
            min-width: 320px;
            margin-bottom: 0;
        }
        .dashboard-card h2 {
            margin-top: 0;
            font-size: 1.3rem;
            color: #00509e;
            font-weight: 700;
        }
        .summary-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            margin-bottom: 0;
            background: #fafdff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .summary-table th, .summary-table td {
            border: none;
            padding: 10px 8px;
            text-align: center;
        }
        .summary-table th {
            background: #00509e;
            color: #fff;
            font-weight: 700;
        }
        .summary-table tr:nth-child(even) td {
            background: #f2f6fa;
        }
        .summary-table tr:hover td {
            background: #e3f0ff;
        }
        .pie-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 0;
        }
        .station-section {
            background: #fff;
            margin: 32px 0 0 0;
            padding: 24px 18px 18px 18px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            transition: box-shadow 0.2s;
        }
        .station-section:hover {
            box-shadow: 0 6px 24px rgba(0,80,158,0.13);
        }
        .station-section h3 {
            margin-top: 0;
            color: #003366;
            font-size: 1.15rem;
            font-weight: 700;
        }
        .testcase-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            background: #fafdff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0,0,0,0.03);
        }
        .testcase-table th, .testcase-table td {
            border: none;
            padding: 8px 6px;
            text-align: center;
        }
        .testcase-table th {
            background: #0074d9;
            color: #fff;
            font-weight: 700;
        }
        .testcase-table tr:nth-child(even) td {
            background: #f2f6fa;
        }
        .testcase-table tr:hover td {
            background: #e3f0ff;
        }
        .status-Not\\ Started { background: #eee; }
        .status-In-Progress { background: #ffe082; }
        .status-Passed { background: #b9f6ca; }
        select {
            padding: 4px 8px;
            border-radius: 5px;
            border: 1px solid #bbb;
            background: #fff;
            font-size: 1rem;
            transition: border 0.2s;
        }
        select:focus {
            border: 1.5px solid #00509e;
            outline: none;
        }
        @media (max-width: 900px) {
            .dashboard-section {
                flex-direction: column;
                gap: 0;
            }
            .dashboard-card {
                margin-bottom: 24px;
            }
        }
        .floating-home {
            position: fixed;
            bottom: 32px;
            right: 32px;
            z-index: 999;
            background: #00509e;
            color: #fff;
            border-radius: 50%;
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 16px rgba(0,0,0,0.18);
            cursor: pointer;
            transition: background 0.2s, box-shadow 0.2s, transform 0.2s;
        }
        .floating-home:hover {
            background: #003366;
            box-shadow: 0 8px 24px rgba(0,80,158,0.18);
            transform: scale(1.08);
        }
        .floating-home svg {
            width: 28px;
            height: 28px;
            display: block;
        }
        @media (max-width: 600px) {
            .floating-home {
                width: 44px;
                height: 44px;
                right: 16px;
                bottom: 16px;
            }
            .floating-home svg {
                width: 22px;
                height: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="header-bar">
        <h1>MCVoR Renewal STC Dashboard</h1>
    </div>
    <div class="container">
        <div class="dashboard-section">
            <div class="dashboard-card pie-container" style="flex-direction:row;justify-content:center;align-items:center;gap:32px;">
                <div style="display:flex;flex-direction:column;align-items:center;">
                    <h2 style="margin-bottom:10px;">Overall Progress</h2>
                    <canvas id="progressPie" width="300" height="300"></canvas>
                </div>
                <div style="display:flex;align-items:center;height:300px;">
                    <div style="background:#b9f6ca;color:#00509e;font-size:2.2rem;font-weight:700;padding:32px 36px;border-radius:18px;box-shadow:0 2px 12px rgba(0,80,158,0.10);min-width:120px;text-align:center;">
                        <?php echo $completed_percent; ?>%<br>
                        <span style="font-size:1rem;font-weight:400;color:#0074d9;">Completed</span>
                    </div>
                </div>
            </div>
            <div class="dashboard-card">
                <h2>Stations Progress Summary</h2>
                <table class="summary-table">
                    <tr>
                        <th>Station</th>
                        <th>Not Started</th>
                        <th>In-Progress</th>
                        <th>Passed</th>
                    </tr>
                    <?php foreach ($stations as $station): ?>
                    <tr>
                        <td><a href="#station-<?php echo urlencode($station); ?>" style="color:#00509e;text-decoration:underline;font-weight:500;"> <?php echo htmlspecialchars($station); ?> </a></td>
                        <td><?php echo $summary[$station]['Not Started']; ?></td>
                        <td><?php echo $summary[$station]['In-Progress']; ?></td>
                        <td><?php echo $summary[$station]['Passed']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
        <?php foreach ($stations as $station): ?>
        <div class="station-section" id="station-<?php echo urlencode($station); ?>">
            <h3><?php echo htmlspecialchars($station); ?></h3>
            <div style="display:flex;align-items:center;gap:24px;margin-bottom:18px;flex-wrap:wrap;">
                <canvas id="stationPie-<?php echo md5($station); ?>" width="180" height="180"></canvas>
                <div style="font-size:1.05rem;">
                    <strong>Not Started:</strong> <?php echo $summary[$station]['Not Started']; ?><br>
                    <strong>In-Progress:</strong> <?php echo $summary[$station]['In-Progress']; ?><br>
                    <strong>Passed:</strong> <?php echo $summary[$station]['Passed']; ?>
                </div>
            </div>
            <table class="testcase-table">
                <tr>
                    <th>Test Case</th>
                    <th>Status</th>
                </tr>
                <?php 
                $station_testcases = $testcases;
                if (in_array($station, ['OCC', 'BOCC'])) {
                    $station_testcases = array_merge($testcases, $extra_testcases_OCC_BOCC);
                }
                foreach ($station_testcases as $tc): ?>
                <tr>
                    <td><?php echo htmlspecialchars($tc); ?></td>
                    <td class="status-<?php echo str_replace(' ', '-', isset($data[$station][$tc]) ? $data[$station][$tc] : 'Not Started'); ?>">
                        <select onchange="updateStatus('<?php echo htmlspecialchars($station); ?>', '<?php echo htmlspecialchars($tc); ?>', this.value)" >
                            <?php foreach ($status_options as $opt): ?>
                            <option value="<?php echo $opt; ?>" <?php if ((isset($data[$station][$tc]) ? $data[$station][$tc] : 'Not Started') === $opt) echo 'selected'; ?>><?php echo $opt; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="floating-home" onclick="window.scrollTo({top:0,behavior:'smooth'});" title="Back to Top">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M3 11.5L12 4L21 11.5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M5 10V20H19V10" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script>
    const pieData = {
        labels: ['Not Started', 'In-Progress', 'Passed'],
        datasets: [{
            data: [<?php echo $total_status['Not Started']; ?>, <?php echo $total_status['In-Progress']; ?>, <?php echo $total_status['Passed']; ?>],
            backgroundColor: ['#eee', '#ffe082', '#b9f6ca'],
            borderColor: ['#ccc', '#ffd54f', '#00c853'],
            borderWidth: 1
        }]
    };
    new Chart(document.getElementById('progressPie'), {
        type: 'pie',
        data: pieData,
        options: {
            responsive: false,
            plugins: {
                legend: { position: 'bottom' },
                title: { display: true, text: 'Overall Test Case Status' }
            }
        }
    });
    <?php foreach ($stations as $station): ?>
    new Chart(document.getElementById('stationPie-<?php echo md5($station); ?>'), {
        type: 'pie',
        data: {
            labels: ['Not Started', 'In-Progress', 'Passed'],
            datasets: [{
                data: [<?php echo $summary[$station]['Not Started']; ?>, <?php echo $summary[$station]['In-Progress']; ?>, <?php echo $summary[$station]['Passed']; ?>],
                backgroundColor: ['#eee', '#ffe082', '#b9f6ca'],
                borderColor: ['#ccc', '#ffd54f', '#00c853'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: false,
            plugins: {
                legend: { position: 'bottom' },
                title: { display: false },
                datalabels: {
                    color: '#333',
                    font: { weight: 'bold', size: 14 },
                    formatter: (value, context) => {
                        const dataArr = context.chart.data.datasets[0].data;
                        const total = dataArr.reduce((a, b) => a + b, 0);
                        if (total === 0 || value === 0) return '';
                        return (value / total * 100).toFixed(1) + '%';
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
    <?php endforeach; ?>
    function updateStatus(station, testcase, status) {
        var formData = new FormData();
        formData.append('station', station);
        formData.append('testcase', testcase);
        formData.append('status', status);
        fetch('', {
            method: 'POST',
            body: formData
        }).then(r => r.text()).then(resp => {
            if (resp === 'OK') {
                location.reload();
            } else {
                alert('Failed to update status!');
            }
        });
    }
    </script>
</body>
</html> 