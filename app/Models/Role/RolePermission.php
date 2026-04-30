<?php

namespace App\Models\Role;

use App\Core\AbstractModel;

class RolePermission extends AbstractModel
{
protected string $table = 'role_permissions';
protected string $primaryKey = 'id';
protected array $fillable = [
    "role_id",
    "permission_id",
    ];
protected array $required = [

];
}