<?php

namespace Model\Manager;

use App\Model\DB;
use App\Model\Entity\User;

class UserManager
{
    /**
     *Get all data about user
     * recupère toute les données utilisatuer
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
            ->setRole($role)
            ;
    }

    /**
     * @return array
     * query manière d intéroger la base de donnée de manière non sécurisé car c est de moi a moi
     * function get alluser récupère tout les utilisateur enregistrer
     */
    public static function getAllUser(): array
    {
        $user = [];
        $stmt = DB::getPDO()->query("SELECT * FROM user");

        if($stmt) {
            foreach ($stmt->fetchAll() as $data) {
                $user[] = self::dataUser($data);
            }
        }
        return $user;
    }

    /**
     * Returns a user by their mail
     * prepare securise la base de donnée quand un utilisateur rentrer ou modifie une donnée ont pouvais utilisé querry car ca reste une demande de toi a toi
     * et nous récupérons le user par le mail
     * $stmt recupère la connectiona  la base de donnée et preépare uen resquete  et selectionne toute les donnes par l'* de ma table en basde de donnée
     * qui s appelle user dans la quel ce situe le mail
     * ont check si la valeur  est bien la avec le bindvalue
     *
     *
     * @param string $mail
     * @return User|null
     */
    public static function getUserByMail(string $mail): ?User
    {
        $stmt = DB::getPDO()->prepare("SELECT * FROM user WHERE mail = :mail ");
        $stmt->bindValue(':mail', $mail);

        if($stmt->execute() && $data = $stmt->fetch()) {
            return self::dataUser($data);
        }
        return null;
    }

    /**
     * @param string $username
     * @return User|null
     * Ont utilise prepare ( est une fonction plus sécurisé servant a l intérogation du client vers la base donnée)
     * selection de toute les données de la table user dans la quel ce trouve la collone username dans la base de donnée
     *et si l execution echoue il ne retourne null
     * public static function / c est le satut de la fonction ! / qui ne peut pas etre modifier ! / Et etre apeller ailleurs dans un autre objet ( exemple userController)!
     *(string $username) car c est une chaine de caractère  et que l  ont apelle le $Username car ont veux recupérer le utilisateur
     * (?User)  le ? veux dire Null si il ne trouve pas de données /et le User c est mon Entity User.php
     * DB::getPDO() connection a la base de donnée entraine la flèche
     * ->  la flèche entraine la préparation pour faire l'execution de la demande  ("SELECT * FROM user WHERE username = :username");(pas de préfixé de table pour le moment )
     * "SELECT * FROM ca te selctionne toute les données de la collonne présente dans ta table demandé
     *
     */
    public static function getUserByName(string $username): ?User
    {
        $stmt = DB::getPDO()->prepare("SELECT * FROM user WHERE username = :username");
        $stmt->bindValue(':username', $username);

        if($stmt->execute() && $data = $stmt->fetch()) {
            return self::dataUser($data);
        }
        return null;
    }

    /**
     * @param User $user
     * @return bool
     * Ajout d utilisateur/ addUser
     * INSERT INTO user/ une insertion de valeur dans les collonne presente dans ta table user qui sont entre les ( ( username, mail, password, role_id) )
     * VALUES / ce sont les valeurs que je souhaite insérer dans la base de donnée par le biais de formulaire (:username, :mail, :password, :role_id)
     * $stmt bindValue / check la valeur et $user->getUsername());va capturer la valeur entrer dans le formulaire
     *
     *
     */
    public static function addUser(User $user): bool
    {
        $stmt = DB::getPDO()->prepare("
            INSERT INTO user (username, mail, password ,role_id)
            VALUES (:username, :mail, :password ,:role_id) 
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
     * $user =null pour partir de zero
     * ("SELECT * FROM user WHERE id = :id") cela t etablis une liste d utilisateur par id
     * $stmt->bindParam(':id', $id); il vaut préciser  l id pour savoir le retourner
     * fetch /  permet de rendre exploitable l'objet récupéré lors de la connexion après lui avoir passé différentes requêtes SQL.
     * donc $stmt contiens les données et ont le lies à fetch  pour savoir les exploiter et sont stocker dans data pourles exploiter aux bon moment
     */
    public static function getUser(int $id): ?User
    {
        $user = null;
        $stmt = DB::getPDO()->prepare("SELECT * FROM user WHERE id = :id");
        $stmt->bindParam(':id', $id);

        if($stmt->execute() && $data = $stmt->fetch()) {
            $user = self::dataUser($data);
        }
        return $user;
    }

    /**
     * @param $mail
     * @return bool
     * Bool est un booleen vrai ou faux  dans ce cas la 1 ou zero
     * (" SELECT count(*) as cnt FROM user WHERE mail = :mail"); prepare la selection de mail en utilisant un systeme de comptage  count(*)
     * as est un alias dans cette exemple c est cnt
     *FROM user  ca apelle la table user
     *  retourne un entier (int) quiest exploiter seulement si c est plus grand que zero :return (int)$stmt->fetch()['cnt'] > 0;
     *
     */
    public static function userMailExist($mail): bool
    {
        $stmt = DB::getPDO()->prepare(" SELECT count(*) as cnt FROM user WHERE mail = :mail");
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
        $stmt = DB::getPDO()->prepare(" SELECT count(*) as cnt FROM user WHERE username = :username");
        $stmt->bindValue(":username", $username);
        $stmt->execute();
        return (int)$stmt->fetch()['cnt'] > 0;
    }

    /**
     * @param $newUsername
     * @param $id
     * WHERE serre a rechercher
     */
    public function updateUsername($newUsername,$id)
    {
        $stmt = DB::getPDO()->prepare("
            UPDATE user SET username = :newUsername WHERE id = :id
        ");

        $stmt->bindParam(':newUsername', $newUsername);
        $stmt->bindParam(':id', $id);

        $stmt->execute();
    }

    public function updateMail($newMail, $id)
    {
        $stmt = DB::getPDO()->prepare("
            UPDATE user SET mail = :newMail WHERE id = :id
        ");

        $stmt->bindParam(':newMail', $newMail);
        $stmt->bindParam(':id', $id);

        $stmt->execute();
    }

    public function updatePassword($newPassword, $id)
    {
        $stmt = DB::getPDO()->prepare("
            UPDATE user SET password = :newPassword WHERE id = :id
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
            UPDATE user SET role_id = :newRole WHERE username = :newUsername"
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
        $stmt = DB::getPDO()->prepare("DELETE FROM user WHERE id = :id");

        $stmt->bindParam(':id', $id);

        return $stmt->execute();

    }
}