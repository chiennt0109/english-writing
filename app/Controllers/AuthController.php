<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Repository;

class AuthController extends Controller
{
    public function loginForm(): void { $this->view('auth/login'); }

    public function login(): void
    {
        if (!verify_csrf()) { http_response_code(419); exit('CSRF mismatch'); }
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        if (!$email || $password === '') { $this->view('auth/login',['error'=>'Invalid credentials']); return; }

        $repo = new Repository();
        $user = $repo->findUserByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->view('auth/login',['error'=>'Invalid credentials']); return;
        }
        $_SESSION['user'] = ['id'=>$user['id'],'name'=>$user['name'],'email'=>$user['email'],'role'=>$user['role']];
        redirect('/dashboard');
    }

    public function logout(): void
    {
        session_destroy();
        redirect('/login');
    }
}
