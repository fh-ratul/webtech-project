<?php
require_once '../models/AdminModel.php';
header('Content-Type: application/json');

$model = new AdminModel();
$q = isset($_GET['q']) ? $_GET['q'] : '';
$results = $model->searchStudentsOnly($q);

echo json_encode($results);
?>