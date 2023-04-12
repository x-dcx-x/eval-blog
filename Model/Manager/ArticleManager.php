<?php

namespace App\Model\Manager;

use App\Model\DB;
use App\Model\Entity\Article;
use Model\Manager\UserManager;


class ArticleManager
{
    /**
     * Return all articles.
     * @return array
     */
    public static function findAll(): array
    {
        $articles = [];
        $query = DB::getPDO()->query("SELECT * FROM  article");
        if ($query) {
            $userManager = new UserManager();

            foreach ($query->fetchAll() as $articleData) {
                $articles[] = (new Article())
                    ->setId($articleData['id'])
                    ->setUser($userManager->getUser($articleData['user_id']))
                    ->setContent($articleData['content'])
                    ->setTitle($articleData['title']);
            }
        }

        return $articles;
    }

    /**
     * Select an article by its id
     * @param int $id
     * @return Article
     */
    public static function getArticle(int $id): Article
    {
        $result = DB::getPDO()->query("SELECT * FROM article WHERE id = '$id'");
        $result = $result->fetch();
        return (new Article())
            ->setId($id)
            ->setContent($result ['content'])
            ->setTitle($result['title'])
            ->setUser(UserManager::getUser($result['user_id']));
    }

    /**
     * Add a new article into the db.
     * @param Article $article
     * @return void
     */
    public static function addArticle(Article $article): bool
    {
        $stmt = DB::getPDO()->prepare("
            INSERT INTO article (title, content, user_id) VALUES (:title, :content, :user_id)
        ");

        $stmt->bindValue(':title', $article->getTitle());
        $stmt->bindValue(':content', $article->getContent());
        $stmt->bindValue(':user_id', $article->setUser()->getId());

        $result = $stmt->execute();
        $article->setId(DB::getPDO()->lastInsertId());
        return $result;
    }

    /**
     * Check that the article exists in the database by id
     * @param int $id
     * @return int|mixed
     */
    public static function articleExist(int $id): bool
    {
        $result = DB::getPDO()->query("SELECT count(*) FROM article WHERE id = $id");

        if ($result->fetch()['count(*)'] > 0) {
            return $id;
        } else {
            return false;
        }
    }

    /**
     * Deletes an item from the database by id
     * @param $id
     * @return false|int
     */
    public static function deleteArticle($id)
    {
        if (self::articleExist($id)) {
            return DB::getPDO()->exec(
                "DELETE FROM article WHERE id = '$id'
            ");
        }
        return false;
    }

    /**
     * Edit article by id
     */
    public static function updateArticle($newTitle, $newContent, $id)
    {
        $stmt = DB::getPDO()->prepare("UPDATE article 
        SET content = :newContent, title = :newTitle WHERE id = :id");

        $stmt->bindParam('newTitle', $newTitle);
        $stmt->bindParam('newContent', $newContent);
        $stmt->bindParam('id', $id);

        $stmt->execute();
    }
}