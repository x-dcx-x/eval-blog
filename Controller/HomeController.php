<?php

namespace App\Controller;

class HomeController extends AbstractController
{
    public function index() {
        $this->render('home/index');
    }
}