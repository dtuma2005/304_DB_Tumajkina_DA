<?php
define('DB_PATH', __DIR__ . '/db.sqlite');
try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='doctors'");
    if (!$stmt->fetch()) {
        $sqlScript = file_get_contents(__DIR__ . '/db_init.sql');
        $pdo->exec($sqlScript);
    }
} catch (PDOException $e) {
    die("Ошибка базы данных: " . htmlspecialchars($e->getMessage()));
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
    if ($employeeId !== null):
        $query .= " WHERE d.id = :employee_id";
    endif;
    $query .= " ORDER BY d.name, ps.performed_date, ps.performed_time";
    $result = $pdo->prepare($query);
    if ($employeeId !== null):
        $result->bindParam(':employee_id', $employeeId, PDO::PARAM_INT);
    endif;
    $result->execute();
    return $result->fetchAll();
}

$employees = selectAllEmployees($pdo);
$selectedEmployeeId = null;
$selectedEmployeeName = 'Все врачи';
if (isset($_GET['employee_id']) && $_GET['employee_id'] !== ''):
    $selectedEmployeeId = filter_var($_GET['employee_id'], FILTER_VALIDATE_INT);
    if ($selectedEmployeeId !== false):
        foreach ($employees as $employee):
            if ($employee['id'] == $selectedEmployeeId):
                $selectedEmployeeName = $employee['name'];
                break;
            endif;
        endforeach;
    endif;
endif;
$procedures = selectCompletedProcedures($pdo, $selectedEmployeeId);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отчет по оказанным услугам клиники</title>
</head>
<body>
    <h1>Отчет по оказанным услугам клиники</h1>
    <h2>Фильтр</h2>
    <form method="GET" action="">
        <label for="employee_id">Выберите врача:</label>
        <select name="employee_id" id="employee_id">
            <option value="">Все врачи</option>
            <?php foreach ($employees as $employee): ?>
                <option value="<?= $employee['id'] ?>" <?php if ($selectedEmployeeId == $employee['id']): ?>selected<?php endif; ?>>
                    <?= htmlspecialchars($employee['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Показать</button>
    </form>
    <?php if ($selectedEmployeeId !== null): ?>
        <p><strong>Текущий фильтр:</strong> <?= htmlspecialchars($selectedEmployeeName) ?></p>
    <?php endif; ?>
    <hr>
    <?php if (empty($procedures)): ?>
        <p>Данные не найдены. По выбранному фильтру завершенные процедуры отсутствуют.</p>
    <?php else: ?>
        <h2>Список оказанных услуг</h2>
        <table border="1">
            <thead>
                <tr>
                    <th>Номер врача</th>
                    <th>ФИО</th>
                    <th>Дата работы</th>
                    <th>Услуга</th>
                    <th>Стоимость</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($procedures as $procedure): ?>
                    <tr>
                        <td><?= htmlspecialchars($procedure['employee_id']) ?></td>
                        <td><?= htmlspecialchars($procedure['doctor_name']) ?></td>
                        <td><?= htmlspecialchars($procedure['work_date']) ?></td>
                        <td><?= htmlspecialchars($procedure['service_name']) ?></td>
                        <td><?= number_format($procedure['service_price'], 2, '.', '') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <hr>
    <?php endif; ?>
</body>
</html>
