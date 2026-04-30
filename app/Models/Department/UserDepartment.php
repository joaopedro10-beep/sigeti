<?php

namespace App\Models\Department;

use App\Core\AbstractModel;
use App\Models\User;

class UserDepartment extends AbstractModel
{
    protected string $table = 'user_department';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'user_id',
        'department_id',
        'shift'
    ];

    protected array $required = [
        'user_id' => 'O id do usuario é obrigatório',
        'department_id' => 'O id do departamento é obrigatório',
        'shift' => 'O Campo turno é obrigatório'
    ];

    protected bool $timestamps = true;
    protected bool $softDeletes = true;

    public const MORNING = "manha";
    public const AFTERNOON = "tarde";
    public const NIGHT = "noite";
    public const WHOLE = "integral";
    public const NOT_APPLICABLE = "nao_aplicavel";

    public const SHIFTS = [
        self::MORNING,
        self::AFTERNOON,
        self::NIGHT,
        self::WHOLE,
        self::NOT_APPLICABLE
    ];

    public function getId(): int
    {
        return $this->attributes["id"];
    }

    public function setUserId(int $userId): void
    {
        if ($userId < 1) {
            throw new \InvalidArgumentException("O ID do usuário é inválido!");
        }
        $this->attributes["user_id"] = $userId;
    }

    public function getUserId(): int
    {
        return $this->attributes["user_id"];
    }

    public function setDepartmentId(int $departmentId): void
    {
        if ($departmentId < 1) {
            throw new \InvalidArgumentException("O ID do departamento é inválido!");
        }
        $this->attributes["department_id"] = $departmentId;
    }

    public function getDepartmentId(): int
    {
        return $this->attributes["department_id"];
    }


    public function user(): ?User
    {
        return User::find($this->getUserId());
    }

    public function department(): ?Department
    {
        return Department::find($this->getDepartmentId());
    }

    public function setShift(?string $shift): void
    {
        $shift = $shift ?: self::NOT_APPLICABLE;
        if (!in_array($shift, self::SHIFTS)) {
            throw new \InvalidArgumentException("O turno é invalido");
        }

        $this->attributes["shift"] = $shift;
    }

    public function getShift(): string
    {
        return $this->attributes["shift"];
    }


    public static function linksByUser(int $userId): array
    {
        return (new static())
            ->where("user_id", "=", $userId)
            ->get();
    }

    public static function validateDepartments(array $links): array
    {
        $validLinks = [];

        foreach ($links as $link) {
            $departmentId = $link["department_id"] ?? 0;

            if (Department::find((int)$departmentId)) {
                $validLinks[] = $link;
            }
        }

        return $validLinks;
    }

    public static function validateDepartmentLinks(array $links): array
    {
        if (empty($links)) {
            return ["Vincule o usuário a pelo menos um departamento."];
        }

        $links = self::validateDepartments($links);

        if (empty($links)) {
            return ["Nenhum departamento válido foi informado."];
        }

        return [];
    }
}