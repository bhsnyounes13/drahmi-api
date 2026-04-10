<?php
header('Content-Type: application/json');
echo json_encode(['status' => 'ok', 'version' => '2', 'message' => 'API running']);