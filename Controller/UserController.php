<?php

namespace App\Controller;

use App\Model\Entity\User;
use Exception;
use Model\Manager\RoleManager;
use Model\Manager\UserManager;

class UserController extends AbstractController
{

    public function registerPage()
    {
        $this->render('user/register');
    }

    public function loginPage() {
        $this->render('user/login');
    }
    /**
     * Add user and secured the form
     * !isset veux dire pas la !
     * si le bouton n est pas la  alors tu revois sur une autre page
     * empty veux dire vide / si le mail ou le username ou le password est vide alors $_Session indiquera un message d'erreur
     *
     */
    public function register()
    {
        if (!isset($_POST['submit'])) {
            self::registerPage();
        }

        if (empty(($_POST['mail']) || empty($_POST['username']) || empty($_POST['password']))) {
            $_SESSION['errors'] = "Merci de remplir tous les champs";
            self::registerPage();
        }

        //Cleans and return the security of elements
        if ($this->formSubmit()) {
            $username = $this->clean($this->getFormField('username'));

            $mail = $this->clean($this->getFormField('mail'));
            $password = $this->getFormField('password');
            $passwordR = $this->getFormField('passwordR');

            $mail = filter_var($mail, FILTER_SANITIZE_EMAIL);
            // $userManager ca creer un nouvelle objet pour l'entrer dans la base de données
            $userManager = new UserManager();

            if ($userManager->usernameExist($username)) {
                //usernameExist /pour savoir si le speudo du user existe
                $_SESSION['errors'] = "Ce nom d'utilisateur existe déjà";
                self::registerPage();
            }

            // Returns an error if the username is not 2 characters
            if (!strlen($username) >= 6 && strlen($username) <= 50) {
                $_SESSION['errors'] = "Le nom, ou pseudo, doit faire au moins 6 caractères et 50 maximum";
                self::registerPage();
            }

            // Returns an error if the password does not contain all the requested characters.
            if (!preg_match('/^(?=.*[!@#$%^&*-\])(?=.*[0-9])(?=.*[A-Z]).{8,20}$/', $password)) {
                $_SESSION['errors'] = "Le mot de passe doit contenir une majuscule, un chiffre et un caractère spécial";
                self::registerPage();
                //!preg_match oblige de placer des caractère speciaux  mascule et nombres  (pattern = expression regulière,$password )
            }

            // Passwords do not match
            if ($password !== $passwordR) {
                $_SESSION['errors'] = "Les mots de passe ne correspondent pas";
                self::registerPage();
            }

            //If no error is detected the program goes to else and authorizes the recording
            $user = new User();
            $role = RoleManager::getRoleByName('user');
            $user
                ->setUsername($username)
                ->setMail($mail)
                ->setPassword(password_hash($password, PASSWORD_DEFAULT))
                ->setRole($role);

            UserManager::addUser($user);

        }
        $this->render('home/index');
    }

    public function connexion()
    {

            if ($this->formSubmit()) {
                //Recovers and cleans data
                $username = $this->clean($this->getFormField('username'));
                $password = $this->getFormField('password');

                //Check that the fields are not empty
                if (empty($password) && empty($username)) {
                    $errorMessage = "Veuillez remplir tous les champs";
                    $_SESSION['errors'] = $errorMessage;
                    $this->render('user/login');
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

}