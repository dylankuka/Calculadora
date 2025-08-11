<?php
namespace App\Controllers;
use App\Models\UsuarioModel; 
class Usuario extends BaseController {
public function registro() {     
return view('registro'); 

} 
public function registrar() {
$usuarioModel = new UsuarioModel(); 
$datos = [ 'nombredeusuario' => $this->request->getPost('nombredeusuario'), 
'email' => $this->request->getPost('email'), 'fecha_registro' => $this->request->getPost('fecha_registro'), 
'password' => password_hash($this->request->getPost('password'),
PASSWORD_DEFAULT) ];
$usuarioModel->insert($datos); 
return redirect()->to('/usuario/login'); 
} 
public function login() {     
return view('login');
}
public function iniciarSesion() {
$usuarioModel = new UsuarioModel();
$email = $this->request->getPost('email'); 
$password = $this->request->getPost('password');
$usuario = $usuarioModel->where('email', $email)->first();       
if ($usuario && password_verify($password, $usuario['password'])) 
{ 
session()->set('usuario', $usuario); 
return redirect()->to('/dashboard'); 
} 
else {
return view('login', ['error' => 'Credenciales incorrectas']);
 }
 }
 }
?>