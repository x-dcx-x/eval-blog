<?php

namespace App\Controller;

use App\Model\Entity\Article;
use App\Model\Manager\ArticleManager;
use Exception;
use Model\Manager\CommentManager;


class ArticleController extends AbstractController
{
    public function articleForm() {
        $this->render('article/formAddArticle');
    }

    public function displayArticle($id) {
        $this->render('article/displayArticle', [
            'article' => ArticleManager::getArticle($id),
            'comment' => CommentManager::getCommentByArticleId($id)
        ]);
    }

    public function addArticle() {
        $title = $this->clean($this->getFormField('title'));
        //Cleans the field and allows some tags
        $content = html_entity_decode(strip_tags($_POST['content'],'<div><p><br>'));

        //Checking if the writer is logged in
        $user = self::getConnectedWriter();

        //Creating a new article object
        $article = (new Article())
            ->setTitle($title)
            ->setContent($content)
            ->setImage($this->addImage())
            ->setUser($user)
        ;

        //Add the article
        ArticleManager::addArticle($article);

        //Redirection to the writer's area
        $this->render('home/index');
    }

    public function addImage(): string
    {
        $name = "";
        $error = [];
        //Checking the presence of the form field
        if(isset($_FILES['img']) && $_FILES['img']['error'] === 0){

            //Defining allowed file types for the secured
            $allowedMimeTypes = ['image/jpg', 'image/jpeg', 'image/png'];

            if(in_array($_FILES['img']['type'], $allowedMimeTypes)) {
                //Setting the maximum size
                $maxSize = 1024 * 1024;
                if ((int)$_FILES['img']['size'] <= $maxSize) {
                    //Get the temporary file name
                    $tmp_name = $_FILES['img']['tmp_name'];
                    //Assignment of the final name
                    $name = $this->getRandomName($_FILES['img']['name']);

                    //Checks if the destination file exists, otherwise it is created
                    if(!is_dir('uploads')){
                        mkdir('uploads');
                    }
                    //File move
                    move_uploaded_file($tmp_name,'../public/uploads/' . $name);
                }
                else {
                    $_SESSION['errors'] =  "Le poids est trop lourd, maximum autorisé : 1 Mo";
                    $this->render('writer/writer');
                }
            }
            else {
                $_SESSION['errors'] = "Mauvais type de fichier. Seul les formats JPG, JPEG et PNG sont acceptés";
                $this->render('writer/writer');
            }
        }
        else {
            $_SESSION['errors'] = "Une erreur s'est produite";
            $this->render('writer/writer');
        }
        $_SESSION['error'] = $error;
        return $name;
    }

    private function getRandomName(string $rName): string
    {
        //Get file extension
        $infos = pathinfo($rName);
        try {
            //Generates a random string of 15 chars
            $bytes = random_bytes(15);
        }
        catch (Exception $e) {
            //Is used on failure
            $bytes = openssl_random_pseudo_bytes(15);
        }
        //Convert binary data to hexadecimal
        return bin2hex($bytes) . '.' . $infos['extension'];
    }
}