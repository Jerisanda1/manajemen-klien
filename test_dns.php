<?php

echo "PHP DNS Test<br>";

echo "smtp.gmail.com: ";
echo gethostbyname('smtp.gmail.com');

echo "<br>smtp.googlemail.com: ";
echo gethostbyname('smtp.googlemail.com');

echo "<br><br>OpenSSL: ";
echo extension_loaded('openssl') ? 'Aktif' : 'Tidak aktif';

echo "<br>cURL: ";
echo extension_loaded('curl') ? 'Aktif' : 'Tidak aktif';