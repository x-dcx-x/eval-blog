<?php

namespace Model\Manager;

use App\Model\DB;
use App\Model\Entity\User;

class UserManager
{
    public const PREFIXTABLE = 'eval_blog';
    /**
     *Get all data about user
     * @param array $data
     * @return User
     */
    public static function dataUser(array $data): User
    {
        $role = RoleManager::getRoleById($data['role_id']);
        return (new User())
            ->setId($data['id'])
            ->setUsername($data['username'])
            ->setMail($data['mail'])
            ->setPassword($data['password'])
            ->setImage($data['image'])
            ->setRole($role)
            ;
    }

    /**
     * @return array
     */
    public static function getAllUser(): array
    {
        $user = [];
        $stmt = DB::getPDO()->query("SELECT * FROM " . self::PREFIXTABLE . "user");

        if($stmt) {
            foreach ($stmt->fetchAll() as $data) {
                $user[] = self::dataUser($data);
            }
        }
        return $user;
    }

    /**
     * Returns a user by their mail
     * @param string $mail
     * @return User|null
     */
    public static function getUserByMail(string $mail): ?User
    {
        $stmt = DB::getPDO()->prepare("SELECT * FROM " . self::PREFIXTABLE . "user WHERE mail = :mail ");
        $stmt->bindValue(':mail', $mail);

        if($stmt->execute() && $data = $stmt->fetch()) {
            return self::dataUser($data);
        }
        return null;
    }

    /**
     * @param string $username
     * @return User|null
     */
    public static function getUserByName(string $username): ?User
    {
        $stmt = DB::getPDO()->prepare("SELECT * FROM " . self::PREFIXTABLE . "user WHERE username = :username");
        $stmt->bindValue(':username', $username);

        if($stmt->execute() && $data = $stmt->fetch()) {
            return self::dataUser($data);
        }
        return null;
    }

    /**
     * @param User $user
     * @return bool
     */
    public static function addUser(User $user): bool
    {
        $stmt = DB::getPDO()->prepare("
            INSERT INTO " . self::PREFIXTABLE . "user (username, mail, password, role_id)
            VALUES (:username, :mail, :password, :role_id) 
        ");

        $stmt->bindValue(':username', $user->getUsername());
        $stmt->bindValue(':mail', $user->getMail());
        $stmt->bindValue(':password', $user->getPassword());
        $stmt->bindValue(':role_id', $user->getRole()->getId());

        $stmt = $stmt->execute();
        $user->setId(DB::getPDO()->lastInsertId());

        return $stmt;
    }

    /**
     * Return a user based on its id.
     * @param int $id
     * @return User
     */
    public static function getUser(int $id): ?User
    {
        $user = null;
        $stmt = DB::getPDO()->prepare("
            SELECT * FROM " . self::PREFIXTABLE . "user WHERE id = :id
         ");

        $stmt->bindParam(':id', $id);

        if($stmt->execute() && $data = $stmt->fetch()) {
            $user = self::dataUser($data);
        }
        return $user;
    }

    /**
     * @param $mail
     * @return bool
     */
    public static function userMailExist($mail): bool
    {
        $stmt = DB::getPDO()->prepare(" SELECT count(*) as cnt FROM " . self::PREFIXTABLE . "user WHERE mail = :mail");
        $stmt->bindValue(":mail", $mail);
        $stmt->execute();
        return (int)$stmt->fetch()['cnt'] > 0;
    }

    /**
     * @param $username
     * @return bool
     */
    public static function usernameExist($username): bool
    {
        $stmt = DB::getPDO()->prepare(" SELECT count(*) as cnt FROM " . self::PREFIXTABLE . "user WHERE username = :username");
        $stmt->bindValue(":username", $username);
        $stmt->execute();
        return (int)$stmt->fetch()['cnt'] > 0;
    }

    /**
     * @param $newUsername
     * @param $id
     */
    public function updateUsername($newUsername,$id)
    {
        $stmt = DB::getPDO()->prepare("
            UPDATE " . self::PREFIXTABLE . "user SET username = :newUsername WHERE id = :id
        ");

        $stmt->bindParam(':newUsername', $newUsername);
        $stmt->bindParam(':id', $id);

        $stmt->execute();
    }

    public function updateMail($newMail, $id)
    {
        $stmt = DB::getPDO()->prepare("
            UPDATE " . self::PREFIXTABLE . "user SET mail = :newMail WHERE id = :id
        ");

        $stmt->bindParam(':newMail', $newMail);
        $stmt->bindParam(':id', $id);

        $stmt->execute();
    }

    public function updatePassword($newPassword, $id)
    {
        $stmt = DB::getPDO()->prepare("
            UPDATE " . self::PREFIXTABLE . "user SET password = :newPassword WHERE id = :id
        ");

        $stmt->bindParam(':newPassword', $newPassword);
        $stmt->bindParam(':id', $id);

        $stmt->execute();
    }

    /**
     * @param $newRole
     * @param $newUsername
     */
    public static function updateRoleUser($newRole, $newUsername)
    {
        $stmt = DB::getPDO()->prepare("
            UPDATE " . self::PREFIXTABLE . "user SET role_id = :newRole WHERE username = :newUsername"
        );

        $stmt->bindParam(':newRole', $newRole);
        $stmt->bindParam(':newUsername', $newUsername);

        $stmt->execute();
    }

    /**
     * Delete a user by its id
     * @param int $id
     * @return bool
     */
    public static function deleteUser(int $id): bool
    {
        $stmt = DB::getPDO()->prepare("DELETE FROM " . self::PREFIXTABLE . "user WHERE id = :id");

        $stmt->bindParam(':id', $id);

        return $stmt->execute();

    }

    /**
     * Get image name for a user
     * @param $newImage
     * @param $id
     * @return void
     */
    public static function userImage($newImage, $id)
    {
        $stmt = DB::getPDO()->prepare("UPDATE " . self::PREFIXTABLE . "user SET image = :image  WHERE id = :id");

        $stmt->bindParam(':image', $newImage);
        $stmt->bindParam(':id', $id);

        $stmt->execute();
    }
}