<?php
// API para obtener socorristas de una instalación específica
require_once __DIR__ . '/../../classes/AdminAuthService.php';
require_once __DIR__ . '/../../classes/AdminService.php';
header('Content-Type: application/json; charset=utf-8');
ob_clean();
error_reporting(0);
$auth=new AdminAuthService();
$service=new AdminService();
if(!$auth->estaAutenticadoAdmin()){
    http_response_code(401);
    echo json_encode(['error'=>'No autenticado']);
    exit;
}
if($_SERVER['REQUEST_METHOD']!=='GET'){
    http_response_code(405);
    echo json_encode(['error'=>'Método no permitido']);
    exit;
}
$instalacionId=$_GET['instalacion_id']??null;
if(!$instalacionId){
    http_response_code(400);
    echo json_encode(['error'=>'ID requerido']);
    exit;
}
try{
    $soc=$service->getSocorristasPorInstalacion($instalacionId);
    echo json_encode(['success'=>true,'socorristas'=>$soc]);
}catch(Exception $e){
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
