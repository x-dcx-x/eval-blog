<?php

namespace App\Controller;

use App\Model\Manager\ArticleManager;

class HomeController extends AbstractController
{
    public function index() {
        $this->render('home/index', [
            'article' => ArticleManager::findAll(),
        ]);
    }


}