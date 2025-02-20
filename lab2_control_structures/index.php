<?php
echo "<h2>Цикл for</h2>";

// Версия с использованием цикла for
$a = 0;
$b = 0;

for ($i = 0; $i <= 5; $i++) {
    $a += 10;
    $b += 5;
    echo "For Iteration $i: a = $a, b = $b<br>";
}
echo "End of the for loop: a = $a, b = $b<br><br>";

echo "<h2>Цикл while</h2>";

// Версия с использованием цикла while
$a = 0;
$b = 0;
$i = 0;
while ($i <= 5) {
    $a += 10;
    $b += 5;
    echo "While Iteration $i: a = $a, b = $b<br>";
    $i++;
}
echo "End of the while loop: a = $a, b = $b<br><br>";

echo "<h2>Цикл do-while</h2>";

// Версия с использованием цикла do-while
$a = 0;
$b = 0;
$i = 0;
do {
    $a += 10;
    $b += 5;
    echo "Do-while Iteration $i: a = $a, b = $b<br>";
    $i++;
} while ($i <= 5);
echo "End of the do-while loop: a = $a, b = $b<br>";
?>