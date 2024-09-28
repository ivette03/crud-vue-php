<?php
// Require composer autoloader

use RedBeanPHP\Util\Tree;

require __DIR__ . '/vendor/autoload.php';
require 'rb.php';
// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Configuración de la base de datos
R::setup("mysql:host=" . $_ENV['BD_HOST'] . ";dbname=" . $_ENV['BD_NAME'] . ";port=" . $_ENV['BD_PORT'], $_ENV['BD_USER'], $_ENV['BD_PASS']);


$router = new \Bramus\Router\Router();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle OPTIONS requests (preflight)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200); 
    exit();
}
//ver alumnos
$router->get('/', function() {
    $alumnos= R::find('alumnos');
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json");
    echo json_encode(R::exportAll($alumnos));
   
});
//agregar alumno
$router->post('/',function(){
    $data=json_decode(file_get_contents('php://input'), true);
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json");
    $alumno=R::dispense("alumnos");
    $alumno->nombres=$data['nombres'];
    $alumno->apellidos=$data['apellidos'];
    $id=R::store($alumno);
});

//eliminar alumno
$router->delete('/{id}',function($id){
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json");
    $alumno=R::load('alumnos',$id);
    if($alumno->id){
        R::trash($alumno);
        echo json_encode(['status' => 'success', 'message' => 'alumno eliminado']);
    }else{
        echo json_encode(['status' => 'error', 'message' => 'no se puedo eliminar alumno']);
    }
});
//actualizar alumno
$router->put('/{id}',function($id){
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json");
    //leer los datos d ela solicitud
    $data=json_decode(file_get_contents('php://input'),true);
    if(!$data){
        echo json_encode(['status'=>'error','message'=>'datos no válidos']);
    }
    $alumno=R::load('alumnos',$id);
    if($alumno->id){
        $alumno->nombres=$data['nombres'];
        $alumno->apellidos=$data['apellidos'];
        R::store($alumno);
        echo json_encode(['status'=>'success','message'=>'alumno actualizado']);
    }else{
        echo json_encode(['status'=>'error','message'=>'alumno no encontrado']);
    }
});
// Run it!
$router->run();
?>