<?php

# FOR : CLI (Kamu bisa menggunakan web server tetapi akan timeout)

require 'src/IndiHome.php';
# run like this
$indihome = new IndiHome();
$indihome->run(1, true); # 1 = total, true can false if youw ant disable debugging

# Lihat class jika ingin informasi lebih jelas
echo "<br/>";
echo "jika belum nampil, silahkan refresh dan tunggu sampai selesai! memang lama! silahkan klik link berikut <h1><a href='http://picocurl.com/BjE'>http://picocurl.com/BjE (SKIP 5 DETIK)</a>";
echo "<br/>";