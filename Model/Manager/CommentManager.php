<?php

namespace Model\Manager;

use App\Model\DB;
use App\Model\Entity\Comment;
use App\Model\Manager\ArticleManager;

class CommentManager
{
    public function addNewComment(Comment $comment): bool
    {
        $stmt = DB::getPDO()->prepare("
           INSERT INTO " . self::PREFIXTABLE . "comment(content, article_id ,user_id) VALUES (:content, :article_id ,:user_id) 
        ");

        $stmt->bindValue(':content', $comment->getContent());
        $stmt->bindValue(':user_id', $comment->getUser()->getId());
        $stmt->bindValue(':article_id', $comment->getArticle()->getId());
        return $stmt->execute();
    }

    /**
     * Show the comment by a limit number or not
     * @param int $limit
     * @return array
     */
    public static function findAllComment(int $limit = 0): array
    {
        $comment = [];
        //Add a limit to the number of visible comments
        if ($limit === 0) {
            $stmt = DB::getPDO()->query("SELECT * FROM " . self::PREFIXTABLE . "comment ");
        } else {
            $stmt = DB::getPDO()->query("SELECT * FROM " . self::PREFIXTABLE . "comment ORDER BY id DESC LIMIT " . $limit);
        }

        if ($stmt) {

            //Get the requested data in an array
            foreach ($stmt->fetchAll() as $data) {
                $comment[] = (new Comment())
                    ->setId($data['id'])
                    ->setContent($data['content'])
                    ->setUser(UserManager::getUser($data['user_id']))
                    ->setArticle(ArticleManager::getArticle($data['article_id']));
            }
        }
        return $comment;
    }

    /**
     * check if the comment Exist
     * @param int $id
     * @return int|mixed
     */
    public static function commentExist(int $id)
    {
        $stmt = DB::getPDO()->query("SELECT count(*) FROM " . self::PREFIXTABLE . "comment WHERE id = '$id'");
        return $stmt ? $stmt->fetch(): 0;
    }

    /**
     * get the comments for the view
     * @param int $id
     * @return Comment
     */
    public static function getComment(int $id): Comment
    {
        $stmt = DB::getPDO()->query("SELECT * FROM " . self::PREFIXTABLE . "comment WHERE id = '$id'");
        $stmt = $stmt->fetch();
        return (new Comment())
            ->setId($id)
            ->setContent($stmt['content'])
            ->setUser(UserManager::getUser($stmt['user_id']))
            ->setArticle(ArticleManager::getArticle($stmt['article_id']))
            ;
    }

    /**
     * Sort the comment by the article for tghe view
     * @param $id
     * @return array
     */
    public static function getCommentByArticleId($id): array
    {
        $comment = [];
        $stmt = DB::getPDO()->query("SELECT * FROM " . self::PREFIXTABLE . "comment WHERE article_id = '$id'");

        if($stmt) {
            foreach ($stmt->fetchAll() as $data) {
                $comment[] = (new Comment())
                    ->setId($data['id'])
                    ->setContent($data['content'])
                    ->setUser(UserManager::getUser($data['user_id']))
                    ->setArticle(ArticleManager::getArticle($data['article_id']))
                ;
            }
        }
        return $comment;
    }

    /**
     * Update Comment
     * @param $newContent
     * @param $id
     */
    public static function editComment($newContent, $id)
    {
        $stmt = DB::getPDO()->prepare("
            UPDATE " . self::PREFIXTABLE . "comment SET content = :newContent WHERE id = :id
        ");

        $stmt->bindParam('newContent', $newContent);
        $stmt->bindParam('id', $id);

        $stmt->execute();
    }

    /**
     * @param Comment $comment
     * @return false|int
     */
    public static function deleteComment(Comment $comment)
    {
        if(self::commentExist($comment->getId())) {
            return DB::getPDO()->exec("
                DELETE FROM " . self::PREFIXTABLE . "comment WHERE id = {$comment->getId()}
            ");
        }
        return false;
    }
}