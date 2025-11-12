<?php
$letters = range('A', 'Z');
$saveDir = __DIR__ . '/images/default/';

if (!is_dir($saveDir)) {
    mkdir($saveDir, 0777, true);
}

foreach ($letters as $letter) {
    $url = "https://ui-avatars.com/api/?name=$letter&background=007bff&color=fff&size=128";
    $imagePath = $saveDir . $letter . '.png';

    file_put_contents($imagePath, file_get_contents($url));
    echo "Downloaded $letter.png<br>";
}
?>
