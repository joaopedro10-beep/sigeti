<?php

namespace App\Models\Department;

use App\Core\AbstractModel;
use App\Models\Ticket;
use MongoDB\Driver\Exception\InvalidArgumentException;

class Department extends AbstractModel
{
    protected string $table = 'department';

    protected string $primaryKey = "id";

    protected array $fillable = [
        'name',
        'code',
        'description',
        'address'
    ];

    protected array $required = [
        'name' => 'O Nome do Departamento é obrigatorio',
        'code' => 'O codigo do Departamento é obrigatorio',
    ];

    protected bool $timestamps = true;
    protected bool $softDelete = false;

    public function getId(): int
    {
        return $this->attributes['id'];
    }

    public function setName(string $name): void
    {

        $name = trim(strip_tags($name));
        if (strlen($name) < 10) {
            throw new \InvalidArgumentException("O Nome do Departamento deve ter ao menos 10 caracteres");
        }
        if (strlen($name) > 100) {
            throw new \InvalidArgumentException("O Nome da Departamento deve ter até 100 caracteres");
        }

        $this->attributes["name"] = $name;
    }

    public function getName(): string
    {
        return $this->attributes["name"];
    }

    public function setCode(string $code): void
    {
        $code = trim(strip_tags($code));
        if (strlen($code) < 2) {
            throw new InvalidArgumentException("O codigo do Departamento deve ter ao menos 2 caracteres");
        } if (strlen($code) > 20) {
            throw new InvalidArgumentException("O codigo do Departamento deve ter até 20 caracteres");
        }
        $this->attributes["code"] = $code;
    }

    public function getCode(): string
    {
        return $this->attributes["code"];
    }

    public function setDescription(string $description): void
    {
        $description = trim(strip_tags($description));
        if (strlen($description) < 20) {
            throw new \InvalidArgumentException("A descrição do departamento deve ter ao menos 20 caracteres");
        }
        if (strlen($description) > 80) {
            throw new \InvalidArgumentException("A descrição do departamento deve ter até 10 caracteres");
        }
        $this->attributes["description"] = $description;
    }

    public function getDescription(): ?string
    {
        return $this->attributes["description"] ?? null;
    }
    public function setAddress(string $address): void{
        $address = trim(strip_tags($address));
        if (strlen($address) < 10) {
            throw new \InvalidArgumentException("O endereço do departamento deve ter ao menos 10 caracteres");
        }if (strlen($address) > 100) {
            throw new \InvalidArgumentException("O endereço do departamento deve ter até 100 caracteres");
        }
    }
    public function getAddress(): ?string{
        return $this->attributes["address"] ?? null;
    }

    public function existsDepartmentByCode(string $code, ?int $ignoreId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE code = :code AND deleted_at IS NULL";
        $params = ["code" => $code];

        if ($ignoreId) {
            $sql .= " AND id != :ignore_id";
            $params["ignore_id"] = $ignoreId;
        }

        $statement = $this->connection->prepare($sql);
        $statement->execute($params);

        return (int)$statement->fetchColumn() > 0;
    }

    public function existsDepartmentByName(string $name, ?int $ignoreId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE name = :name AND deleted_at IS NULL";
        $params = ["name" => $name];

        if ($ignoreId) {
            $sql .= " AND id != :ignore_id";
            $params["ignore_id"] = $ignoreId;
        }

        $statement = $this->connection->prepare($sql);
        $statement->execute($params);

        return (int)$statement->fetchColumn() > 0;
    }

    public function existsUsers(): bool
    {
        return (new UserDepartment())
                ->where("department_id", "=", $this->getId())
                ->count() > 0;
    }

    public function existsTickets(): bool
    {
        return (new Ticket())
                ->where("school_id", "=", $this->getId())
                ->count() > 0;
    }

    public function validateBusinessRule(?int $ignoreId = null): array
    {
        $errors = [];

        if ($this->existsDepartmentByName($this->getName(), $ignoreId)) {
            $errors[] = "Já existe um departamento com esse nome.";
        }

        if ($this->existsDepartmentByCode($this->getCode(), $ignoreId)) {
            $errors[] = "Já existe um departamento com esse código.";
        }

        return $errors;
    }
}