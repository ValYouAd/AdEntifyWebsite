<?php

$ch = curl_init(urlencode('http://cdn.adentify.com/uploads/photos/products/original/529cab8540062Screen Shot 2013-11-27 at 17.25.36.png'));
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_HTTPGET, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$result = curl_exec($ch);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
echo curl_error($ch);
curl_close($ch);

echo $result;

?>