<?php

namespace App\Models;

class Role extends Model
{
    protected $table = 'roles';
    protected $fillable = ['name', 'description'];

    public function findByName($name)
    {
        return $this->first('name = ?', [$name]);
    }

    public function getUsersByRole($roleName)
    {
        $sql = "SELECT u.* FROM users u
                INNER JOIN roles r ON u.role_id = r.id
                WHERE r.name = ?";
        return $this->db->fetchAll($sql, [$roleName]);
    }

    public static function getAvailableRoles()
    {
        return [
            'SUPERADMIN' => 'Super Administrador',
            'OWNER' => 'Propietario de Restaurante',
            'RESTAURANT_ADMIN' => 'Administrador de Restaurante',
            'CLIENTE' => 'Cliente'
        ];
    }
}
