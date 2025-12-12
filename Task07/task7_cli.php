<?php

function connectDatabase(): PDO
{
    define('DB_PATH', __DIR__ . '/db.sqlite');

    try {
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA foreign_keys = ON');

        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='doctors'");
        $exists = $stmt->fetch();

        if (!$exists) {
            $sqlScript = file_get_contents(__DIR__ . '/db_init.sql');
            $pdo->exec($sqlScript);
        }

        return $pdo;

    } catch (PDOException $e) {
        die("Ошибка базы данных: " . htmlspecialchars($e->getMessage()));
    }
}

function selectAllEmployees(PDO $pdo): array
{
    $query = "SELECT id, name FROM doctors ORDER BY name";

    $result = $pdo->prepare($query);
    $result->execute();

    return $result->fetchAll();
}

function selectCompletedProcedures(PDO $pdo, ?int $employeeId = null): array
{
    $query = "SELECT 
                d.id as employee_id,
                d.name as doctor_name,
                ps.performed_date || ' ' || ps.performed_time as work_date,
                s.name as service_name,
                ps.actual_price as service_price
            FROM performed_services ps
            JOIN doctors d ON ps.doctor_id = d.id
            JOIN services s ON ps.service_id = s.id";

    if ($employeeId !== null) {
        $query .= " WHERE d.id = :employee_id";
    }

    $query .= " ORDER BY d.name, ps.performed_date, ps.performed_time";

    $result = $pdo->prepare($query);

    if ($employeeId !== null) {
        $result->bindParam(':employee_id', $employeeId, PDO::PARAM_INT);
    }

    $result->execute();

    return $result->fetchAll();
}

function displayEmployeesList(array $employees): void
{
    echo "\n╔══════════════════════════════════════════════════════╗\n";
    echo "║          Доктора                                     ║\n";
    echo "╠════════╦═════════════════════════════════════════════╣\n";
    echo "║   ID   ║             Full Name                       ║\n";
    echo "╠════════╬═════════════════════════════════════════════╣\n";

    foreach ($employees as $employee) {
        printf(
            "║ %-6s ║ %-43s ║\n",
            $employee['id'],
            mb_substr($employee['name'], 0, 43)
        );
    }

    echo "╚════════╩═════════════════════════════════════════════╝\n\n";
}

function getForEmployeeId(array $validIds): ?int
{
    while (true) {
        $input = readline("ID доктора ->: ");

        if (trim($input) === '') {
            return null;
        }

        if (!ctype_digit($input)) {
            continue;
        }

        $employeeId = (int)$input;

        if (!in_array($employeeId, $validIds, true)) {
            continue;
        }

        return $employeeId;
    }
}

function calculateColumnWidths(array $data): array
{
    $widths = [
        'employee_id' => 10,
        'doctor_name' => 30,
        'work_date' => 20,
        'service_name' => 35,
        'service_price' => 12
    ];

    foreach ($data as $row) {
        $widths['doctor_name'] = max(
            $widths['doctor_name'],
            mb_strlen($row['doctor_name']) + 2
        );
        $widths['service_name'] = max(
            $widths['service_name'],
            mb_strlen($row['service_name']) + 2
        );
    }

    $widths['doctor_name'] = min($widths['doctor_name'], 40);
    $widths['service_name'] = min($widths['service_name'], 50);

    return $widths;
}

function truncateText(string $text, int $width): string
{
    if (mb_strlen($text) <= $width) {
        return $text;
    }

    return mb_substr($text, 0, $width - 3) . '...';
}

function renderProceduresTable(array $procedures): void
{
    if (empty($procedures)) {
        echo "\n╔══════════════════════════════════════╗\n";
        echo "║  Ничего не найдено                   ║\n";
        echo "╚══════════════════════════════════════╝\n\n";
        return;
    }

    $widths = calculateColumnWidths($procedures);

    renderTableBorder('top', $widths);
    renderTableHeaderRow($widths);
    renderTableBorder('middle', $widths);

    $totalPrice = 0;
    foreach ($procedures as $row) {
        printf(
            "║ %-{$widths['employee_id']}s │ %-{$widths['doctor_name']}s │ %-{$widths['work_date']}s │ %-{$widths['service_name']}s │ %{$widths['service_price']}.2f ║\n",
            $row['employee_id'],
            truncateText($row['doctor_name'], $widths['doctor_name']),
            $row['work_date'],
            truncateText($row['service_name'], $widths['service_name']),
            $row['service_price']
        );

        $totalPrice += $row['service_price'];
    }

    renderTableBorder('bottom', $widths);
}

function renderTableHeaderRow(array $widths): void
{
    printf(
        "║ %-{$widths['employee_id']}s │ %-{$widths['doctor_name']}s │ %-{$widths['work_date']}s │ %-{$widths['service_name']}s │ %-{$widths['service_price']}s ║\n",
        'Doctor ID',
        'Full Name',
        'Work Date',
        'Service',
        'Price'
    );
}

function renderTableBorder(string $position, array $widths): void
{
    $chars = [
        'top' => ['left' => '╔', 'right' => '╗', 'cross' => '╤', 'line' => '═'],
        'middle' => ['left' => '╟', 'right' => '╢', 'cross' => '┼', 'line' => '─'],
        'bottom' => ['left' => '╚', 'right' => '╝', 'cross' => '╧', 'line' => '═']
    ];

    $c = $chars[$position];

    echo $c['left'];
    echo str_repeat($c['line'], $widths['employee_id'] + 2);
    echo $c['cross'];
    echo str_repeat($c['line'], $widths['doctor_name'] + 2);
    echo $c['cross'];
    echo str_repeat($c['line'], $widths['work_date'] + 2);
    echo $c['cross'];
    echo str_repeat($c['line'], $widths['service_name'] + 2);
    echo $c['cross'];
    echo str_repeat($c['line'], $widths['service_price'] + 2);
    echo $c['right'];
    echo "\n";
}

function main(): void
{
    echo "\n";
    echo "╔════════════════════════════════════════════════════════════╗\n";
    echo "║                    Процедуры                               ║\n";
    echo "╚════════════════════════════════════════════════════════════╝\n";
    
    $pdo = connectDatabase();

    $employees = selectAllEmployees($pdo);

    if (empty($employees)) {
        return;
    }

    displayEmployeesList($employees);

    $validIds = array_column($employees, 'id');
    $employeeId = getForEmployeeId($validIds);
    $procedures = selectCompletedProcedures($pdo, $employeeId);

    echo "\n";
    if ($employeeId !== null) {
        $selectedEmployee = array_filter(
            $employees,
            fn($e) => $e['id'] == $employeeId
        );
        $employeeName = array_values($selectedEmployee)[0]['name'];
        echo ": {$employeeName}\n";
    } else {
        echo "Все доктора\n";
    }
    echo "\n";

    renderProceduresTable($procedures);
}

main();
