<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class PublicController extends Controller
{
    public function home(): void
    {
        $this->view('public/home', ['title' => 'Home']);
    }

    public function about(): void
    {
        $this->view('public/about', ['title' => 'Sobre']);
    }

    public function courses(): void
    {
        $this->view('public/courses', ['title' => 'Cursos']);
    }

    public function events(): void
    {
        $this->view('public/events', ['title' => 'Eventos']);
    }

    public function library(): void
    {
        $this->view('public/library', ['title' => 'Biblioteca']);
    }

    public function community(): void
    {
        $this->view('public/community', ['title' => 'Comunidade']);
    }
}
