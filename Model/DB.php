<?php

namespace App\Model;

use App\Config;
use PDO;
use PDOException;

class DB
{
    private static ?PDO $pdoObject = null;

    /**
     * return to connect of PDO in my DB or null in case of error
     * @return PDO
     */
    public static function getPDO(): PDO
    {
        if(self::$pdoObject === null) {
            try {
                $dsn = 'mysql:host=' . Config::DB_HOST . ';dbname=' . Config::DB_NAME . ';charset=' . Config::DB_CHARSET;
                //connexion in to DB
                self::$pdoObject = new PDO($dsn, Config::DB_USERNAME, Config::DB_PASSWORD);
                //Define the error mode of PDO on Exception
                self::$pdoObject->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                //returns an array indexed by the column name
                self::$pdoObject ->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            }
            catch (PDOException $err) {
                //crash the script in case of error
                die();
            }
        }

        return self::$pdoObject;
    }
}