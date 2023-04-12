<?php

namespace Model\Manager;

use App\Model\DB;
use App\Model\Entity\Role;

class RoleManager
{
    public static function findAll(): array
    {
        $roles = [];
        $query = DB::getPDO()->query("SELECT * FROM role");
        if($query) {
            foreach($query->fetchAll() as $roleData) {
                $roles[] = (new Role())
                    ->setId($roleData['id'])
                    ->setRoleName($roleData['role_name'])
                ;
            }
        }
        return $roles;
    }

    /**
     * Return a role by name.
     * @param string $roleName
     * @return Role
     */
    public static function getRoleByName(string $roleName): Role
    {
        $role = new Role();
        $request = DB::getPDO()->query("
            SELECT * FROM role WHERE roleName = '".$roleName."'
        ");
        if($request && $roleData = $request->fetch()) {
            $role->setId($roleData['id']);
            $role->setRoleName($roleData['roleName']);
        }
        return $role;
    }

    /**
     * Return a role by id
     * @param int $id
     * @return Role
     */
    public static function getRoleById(int $id): Role
    {
        $role= new Role();
        $request = DB::getPDO()->prepare("
            SELECT * FROM role WHERE id = :id
        ");
        $request->bindValue(':id', $id);
        $request->execute();
        if($roleData = $request->fetch()) {
            $role->setId($roleData['id']);
            $role->setRoleName($roleData['role_name']);
        }
        return $role;
    }
}