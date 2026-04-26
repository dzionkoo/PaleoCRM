<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
echo json_encode(['cors' => 'working', 'php' => PHP_VERSION]);