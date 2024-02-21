<?php

header("Access-Control-Allow-Origin: https://www.liligrow.es");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "jeje";
}