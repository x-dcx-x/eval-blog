<?php

namespace App\Controller;

use App\Model\Entity\User;
use Exception;
use Model\Manager\ArticleManager;
use Model\Manager\RoleManager;
use Model\Manager\UserManager;

class UserController extends AbstractController
{
    /**
     * Add user and secured the form
     */
    public function register()
    {
        if (!isset($_POST['submit'])) {
            $this->render('pages/register');
            exit();
        }

        if (empty(($_POST['email']) || empty($_POST['username']) || empty($_POST['password']))) {
            $_SESSION['errors'] = "Merci de remplir tous les champs";
            $this->render('pages/register');
        }

        //Cleans and return the security of elements
        if ($this->formSubmit()) {
            $username = $this->clean($this->getFormField('username'));

            $email = $this->clean($this->getFormField('email'));
            $password = $this->getFormField('password');
            $passwordR = $this->getFormField('passwordR');

            $mail = filter_var($email, FILTER_SANITIZE_EMAIL);
            $mailR = filter_var($email, FILTER_SANITIZE_EMAIL);
            $userManager = new UserManager();

            if($userManager->usernameExist($username)) {
                $_SESSION['errors'] = "Ce nom d'utlisateur existe déjà";
                $this->render('pages/register');
            }

            if($userManager->userMailExist($mail)) {
                $_SESSION['errors'] = "Cette adresse mail existe déjà";
                $this->render('pages/register');
            }

            if ($mail !== $mailR) {
                $_SESSION['errors'] = "Les adresses mails ne correspondent pas";
                $this->render('pages/register');
            }

            // Returns an error if the username is not 2 characters
            if (!strlen($username) >= 6 && strlen($username) <= 50) {
                $_SESSION['errors'] = "Le nom, ou pseudo, doit faire au moins 6 caractères et 50 maximum";
                $this->render('pages/register');
            }

            // Returns an error if the password does not contain all the requested characters.
            if (!preg_match('/^(?=.*[!@#$%^&*-\])(?=.*[0-9])(?=.*[A-Z]).{8,20}$/', $password)) {
                $_SESSION['errors'] = "Le mot de passe doit contenir une majuscule, un chiffre et un caractère spécial";
                $this->render('pages/register');
            }

            // Passwords do not match
            if ($password !== $passwordR) {
                $_SESSION['errors'] = "Les mots de passe ne correspondent pas";
                $this->render('pages/register');
            } else {
                //If no error is detected the program goes to else and authorizes the recording

                $user = new User();
                $role = RoleManager::getRoleByName('none');
                $user
                    ->setUsername($username)
                    ->setMail($mail)
                    ->setPassword(password_hash($password, PASSWORD_DEFAULT))
                    ->setRole($role);

                UserManager::addUser($user);

                $id = UserManager::getUserByMail($mail)->getId();

            }
        }
        $this->render('home/index');
    }

    public function connexion()
    {
        if(isset($_SESSION['activation-token'])) {
            $_SESSION['errors'] = "Vous devez activer votre compte avant de vous connecter";
        }
        else {
            if ($this->formSubmit()) {
                //Recovers and cleans data
                $username = $this->clean($this->getFormField('username'));
                $password = $this->getFormField('password');

                //Check that the fields are not empty
                if (empty($password) && empty($username)) {
                    $errorMessage = "Veuillez remplir tous les champs";
                    $_SESSION['errors'] = $errorMessage;
                    $this->render('pages/login');
                    exit();
                }

                //Traces the user by his username to verify that he exists
                $user = UserManager::getUserByName($username);
                if (null === $user) {
                    $errorMessage = "Pseudo inconnu";
                    $_SESSION['errors'] = $errorMessage;
                } else {

                    //Compare the password entered and written in the DB
                    if (password_verify($password, $user->getPassword())) {
                        $user->setPassword('');
                        $_SESSION['user'] = $user;
                    } else {
                        $_SESSION['errors'] = "Le nom d'utilisateur, ou le mot de passe est incorrect";
                    }
                }

            } else {
                $successMessage = "Vous êtes connecté";
                $_SESSION['success'] = $successMessage;
            }
        }
        $this->render('home/index');
    }

    /**
     * @param int $id
     */
    public function deleteUser(int $id)
    {
        //Check if user is connected
        if (!isset($_SESSION['user'])) {
            $this->render('home/index');
        }

        //Verify that the user has admin status and if the id is the same une URL and session user
        if (self::getConnectedUser() && self::adminConnected() && self::writerConnected() && $_SESSION['user']->getId() !== $_GET['id']) {
            $_SESSION['errors'] = "Il faut être connecté et propriétaire du compte pour le supprimer !";
            $this->render('home/index');
        }
        // Compare the id in session
        if ($_SESSION['user']->getId() === $id) {
            $userManager = new UserManager();
            $delete = $userManager->deleteUser($id);
            //destroy the session after the delete action
            $this->render('home/index');
            session_destroy();
        }
        $this->render('home/index');
    }

    public function adminDeleteUser(int $id)
    {
        if (self::adminConnected()) {
            $userManager = new UserManager();
            $deleted = $userManager->deleteUser($id);
        }
        $this->render('admin/adminSpace');
    }

    /**
     * @param $id
     */
    public function updateUsername($id)
    {
        //check if the field is present
        if (!isset($_POST['newUsername'])) {
            $this->render('user/userSpace');
        }
        //check if the field is empty
        if (empty($_POST['newUsername'])) {
            $_SESSION['errors'] = "Le champs du pseudo doit être complété";
            $this->render('user/userSpace');
            exit();
        }
        //Clean the field
        $newUsername = $this->clean($_POST['newUsername']);

        $user = new UserManager();
        $user->updateUsername($newUsername, $id);
        $_SESSION['success'] = "Votre pseudo a bien été mis à jour";
        $this->render('user/userSpace');
    }

    /**
     * @param $id
     */
    public function updateEmail($id)
    {
        //check if the field is present
        if (!isset($_POST['newEmail'])) {
            $this->render('user/userSpace');
            exit();
        }
        //check if the field is empty
        if (empty($_POST['newEmail'])) {
            $_SESSION['errors'] = "Le champs de l'email doit être complété";
            $this->render('user/userSpace');
        }

        $newEmail = $this->clean($_POST['newEmail']);
        $user = new UserManager();
        $_SESSION['success'] = "Votre mail a bien été mis à jour, un email vous sera envoyé pour confirmer votre nouvelle 
            adresse";
        $this->render('user/userSpace');
    }

    /**
     * @param $id
     */
    public function updatePassword($id)
    {
        //check if the fields are present
        if (!isset($_POST['newPassword']) && !isset($_POST['newPasswordR'])) {
            $this->render('user/userSpace');
            exit();
        }
        //check if the fields are empty
        if (empty($_POST['newPassword'])) {
            $_SESSION['errors'] = "Le champs du pseudo doit être complété";
            $this->render('user/userSpace');
        }

        $newPassword = $this->getFormField('newPassword');
        $newPasswordR = $this->getFormField('newPasswordR');

        if (!preg_match('/^(?=.*[!@#$%^&*-\])(?=.*[0-9])(?=.*[A-Z]).{8,20}$/', $newPassword)) {
            $_SESSION['errors'] = "Le mot de passe doit contenir une majuscule, un chiffre et un caractère spécial";
        }

        // Passwords do not match
        if ($newPassword !== $newPasswordR) {
            $_SESSION['errors'] = "Les mots de passe ne correspondent pas";
        }
        $newPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $user = new UserManager();
        $user->updatePassword($newPassword, $id);
        $_SESSION['success'] = "Votre mot de passe a bien été mis à jour";

        $this->render('user/userSpace');
    }

    /**
     * @param $id
     * @throws Exception
     */
    public function userImage($id)
    {
        //Check if the user is connected
        if (!isset($_SESSION['user'])) {
            $_SESSION['errors'] = "Seul un utilisateur peut changer son image";
            $this->render('home/index');
        }

        $user = new UserManager();
        //change the avatar
        $newImage = $this->addAvatar();
        $user->userImage($newImage, $id);
        $this->render('user/userSpace');
    }

    /**
     * @return string
     * @throws Exception
     */
    public function addAvatar(): string
    {
        $name = "";
        //Checking the presence of the form field
        if (isset($_FILES['img']) && $_FILES['img']['error'] === 0) {

            //Defining allowed file types
            $allowedMimeTypes = ['image/jpg', 'image/jpeg', 'image/png'];
            if (in_array($_FILES['img']['type'], $allowedMimeTypes)) {
                //Setting the maximum size
                $maxSize = 1024 * 1024;
                if ((int)$_FILES['img']['size'] <= $maxSize) {
                    //Get the temporary file name
                    $tmp_name = $_FILES['img']['tmp_name'];
                    //Assignment of the final name
                    $name = $this->getRandomName($_FILES['img']['name']);
                    //Checks if the destination file exists, otherwise it is created
                    if (!is_dir('uploads')) {
                        mkdir('uploads');
                    }
                    //File move
                    move_uploaded_file($tmp_name, '../public/assets/img/avatar/' . $name);
                } else {
                    $_SESSION['errors'] = "Le poids est trop lourd, maximum autorisé : 1 Mo";
                }
            } else {
                $_SESSION['errors'] = "Mauvais type de fichier. Seul les formats JPD, JPEG et PNG sont acceptés";
            }
        } else {
            $_SESSION['errors'] = "Une erreur s'est produite";
        }
        return $name;
    }

    /**
     * @param string $rName
     * @return string
     * @throws Exception
     */
    private function getRandomName(string $rName): string
    {
        //Get file extension
        $infos = pathinfo($rName);
        try {
            //Generates a random string of 15 chars
            $bytes = random_bytes(15);
        } catch (Exception $e) {
            //Is used on failure
            $bytes = openssl_random_pseudo_bytes(15);
        }
        //Convert binary data to hexadecimal
        return bin2hex($bytes) . '.' . $infos['extension'];
    }

    /**
     * changing the role of a user
     */
    public function updateUserRole()
    {
        //check if the field is present
        if (!isset($_POST['username'])) {
            $this->render('home/index');
            exit();
        }

        //check if the admin is connected
        if (RoleManager::getRoleByName('writer') == 'writer' && RoleManager::getRoleByName('user') == 'user') {
            $_SESSION['errors'] = "Seul un administrateur peut mettre à jour un utilisateur";
            $this->render('home/index');
        }

        //clean the data
        $username = $this->clean($this->getFormField('username'));
        $user = new UserManager();
        $newRole = $_POST['role'];
        //Compare the username
        if ($username !== UserManager::getUserByName($_POST['username'])->getUsername()) {
            $_SESSION['errors'] = "Le pseudo est incorrecte";
            $this->render('admin/adminSpace');
        } else {
            $user->updateRoleUser($newRole, $username);
        }
        $this->render('admin/adminSpace');
    }
}