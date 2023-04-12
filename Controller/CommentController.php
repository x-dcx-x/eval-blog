<?php

namespace App\Controller;

use App\Model\Entity\Comment;
use App\Model\Manager\ArticleManager;
use Model\Manager\CommentManager;

class CommentController extends AbstractController
{
    function index()
    {
        $this->render('home/index');
    }

    public function allComment()
    {
        $this->render('comment/allComment');
    }

    /**
     * Get the comment id for the redirect
     * @param int $id
     */
    public function updateComment(int $id)
    {
        $this->render('comment/editComment', $data=[$id]);
    }

    /**
     * Add a comment
     * @param int $id
     */
    public function addComment(int $id)
    {
        //Get and cleans data
        $content = $this->clean($this->getFormField('content'));

        //Check that the fields are free, otherwise we exit
        if(empty($content)) {
            $_SESSION['errors'] = "le champ doit être rempli";
            $this->render('home/index');
        }

        //Checks if the user is logged in
        $user = self::getConnectedUser();
        if($user === null) {
            $errorMessage = "Il faut être connecter pour pouvoir écrire un commentaire";
            $_SESSION['errors'] = $errorMessage;
            $this->render('home/index');
        }

        //checking that the article exists by its ID
        $article = ArticleManager::articleExist($id);
        if($article === false) {
            $this->render('home/index');
        }
        //Creating a new comment object
        $comment = (new Comment())
            ->setContent($content)
            ->setUser($user)
            ->setArticle(ArticleManager::getArticle($id))
        ;

        $commentManager = new CommentManager();
        $commentManager->addNewComment($comment);
        $this->render('home/index');
    }

    public function editComment($id)
    {
        //Checks if the writer is logged in
        if(!self::writerConnected()) {
            $_SESSION['errors'] = "Seul un administrateur peut éditer un commentaire";
            $this->render('home/index');
        }
        //Check that the field is present
        if(!isset($_POST['content'])) {
            $this->render('home/index');
        }
        ////Get and cleans data
        $newContent = $this->clean($_POST['content']);

        ////Creating a new comment object
        $comment = new CommentManager($newContent, $id);
        $comment->editComment($newContent, $id);
        $this->render('home/index');
    }

    public function deleteComment(int $id)
    {
        ////Checks if the writer is logged in
        if(!self::writerConnected()) {
            $_SESSION['errors'] = "Seul un rédacteur peut supprimer un article";
            $this->render('home/index');
        }

        //Checks that the comment exists by its id
        if(CommentManager::commentExist($id)) {
            $comment = CommentManager::getComment($id);
            $deleted = CommentManager::deleteComment($comment);
            $this->render('home/index');
        }
    }
}