<?php

namespace App\Controller;

use App\Model\Entity\User;

class AbstractController
{
    /**
     * Show views
     * @param string $template
     * @param array $data
     * @return void
     */
    public function render(string $template, array $data = [])
    {
        ob_start();
        require __DIR__ . '/../View/' . $template . '.html.php';
        $html = ob_get_clean();
        require __DIR__ . '/../View/base.html.php';
        exit;
    }

    /**
     * @param string $data
     * @return string
     */
    public function clean(string $data):string{
        $data= trim($data);
        $data=strip_tags($data);
        $data=htmlentities($data);
        return $data;
    }

    /**
     * @return bool
     */
    public function formSubmit():bool{
        return isset($_POST['submit']);
    }

    /**
     * @param string $field
     * @param $default
     * @return mixed|string
     */
    public function getFormField(string $field, $default=null){
        if(!isset($_POST[$field])){
            return(null===$default) ? '' : $default;
        }
        return $_POST[$field];
    }

    /**
     * @return string
     */
    public function randomChar():string{
        try {
            $bytes=random_bytes(35);
        }
        catch(\Exception $e) {
            $bytes=openssl_random_pseudo_bytes(35);
        }
        return bin2hex($bytes);
    }

    /**
     * @return bool
     */
    public function userConnected():bool{
        return isset($_SESSION['user'])&& null!==($_SESSION['user'])->getId();
    }

    /**
     * @return User|null
     */
    public function getConnectedUser(): ?User
    {
        if(!self::userConnected()) {
            return null;
        }
        return ($_SESSION['user']);
    }

    /**
     * Checks if an admin is already logged in
     * @return bool
     */
    public static function adminConnected(): bool
    {
        return isset($_SESSION['user']) && $_SESSION['user']->getRole()->getRoleName() === 'admin';
    }

    /**
     * Checks if a writer is already logged in
     * @return bool
     */
    public static function writerConnected(): bool
    {
        return isset($_SESSION['user']) && $_SESSION['user']->getRole()->getRoleName() === 'writer';

    }

    /**
     * Returns a logged-in writer, or null if not logged in.
     * @return User|null
     */
    public function getConnectedWriter(): ?User
    {
        if(!self::writerConnected()) {
            return null;
        }
        return ($_SESSION['user']);
    }
}