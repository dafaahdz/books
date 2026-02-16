<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Session;

class AuthController extends BaseController
{
    public function index()
    {
        //
    }

    public function login()
    {
        if (session()->get('logged_in')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    // public function loginDebug()
    // {
    //     $email = $this->request->getPost('email');
    //     $password = $this->request->getPost('password');
    //     $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    //     return $this->response->setJSON([
    //         'email' => $email,
    //         'password' => $password,
    //         'passwordHash' => $passwordHash
    //     ]);
    // }

    public function loginProcess()
    {
        $session = session();
        $model = new UserModel();

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $user = $model->where('email', $email)->first();

        if ($user && password_verify($password, $user['password'])) {
            $session->set([
                'user_id'   => $user['id'],
                'username'  => $user['username'],
                'email'     => $user['email'],
                'logged_in' => true
            ]);
            return redirect()->to('/dashboard');
        }

        return redirect()->back()->with('error', 'Email atau password salah')->withInput();
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
