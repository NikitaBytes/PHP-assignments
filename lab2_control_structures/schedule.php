<?php
// Получаем текущий день недели (например, "Monday", "Tuesday", ...)
$currentDay = date('l');

// Определяем дни, в которые сотрудники работают
$johnWorkDays = array('Monday', 'Wednesday', 'Friday');
$janeWorkDays = array('Tuesday', 'Thursday', 'Saturday');

// Определяем график для John Styles
if (in_array($currentDay, $johnWorkDays)) {
    $johnSchedule = "8:00-12:00";
} else {
    $johnSchedule = "Нерабочий день";
}

// Определяем график для Jane Doe
if (in_array($currentDay, $janeWorkDays)) {
    $janeSchedule = "12:00-16:00";
} else {
    $janeSchedule = "Нерабочий день";
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Расписание работы</title>
    <style>
        table {
            border-collapse: collapse;
            width: 60%;
            margin: 20px auto;
        }
        th, td {
            border: 1px solid #333;
            padding: 8px 12px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        h1 {
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Расписание работы на сегодня (<?php echo $currentDay; ?>)</h1>
    <table>
        <tr>
            <th>№</th>
            <th>Фамилия Имя</th>
            <th>График работы</th>
        </tr>
        <tr>
            <td>1</td>
            <td>John Styles</td>
            <td><?php echo $johnSchedule; ?></td>
        </tr>
        <tr>
            <td>2</td>
            <td>Jane Doe</td>
            <td><?php echo $janeSchedule; ?></td>
        </tr>
    </table>
</body>
</html>