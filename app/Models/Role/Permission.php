<?php

namespace App\Models\Role;

use App\Core\AbstractModel;

class Permission extends AbstractModel
{
    protected string $table = 'permissions';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        "name",
        "label",
        "group_name",
    ];

    protected array $required = [
        "name" => "O Campo Nome é obrigatório ",
        "label" => "O Campo Descrição é obrigatório",
        "group_name" => "O Campo Grupo é obrigatório"
    ];
    protected bool $timestamps = false;
    protected bool $softDeletes = false;

    public function getId():int
    {
        return $this->attributes["id"];
    }

    public function setName(string $name): void
    {
        $name = trim(strip_tags($name));
        if (strlen($name) < 10) {
            throw new \InvalidArgumentException("O Nome da permissão deve ter ao menos 10 caracteres");
        } if (strlen($name) > 100) {
            throw new \InvalidArgumentException("O Nome da permissão deve ter até 100 caracteres");
        }

        $this->attributes["name"] = $name;
    }

    public function getName(): ?string
    {
        return $this->attributes["name"] ?? null;
    }
    public function setLabel(string $label): void
    {
        $label = trim(strip_tags($label));
        if (strlen($label) < 15) {
            throw new \InvalidArgumentException("O campo Descrição deve ter mais de 15 caracteres!");
        }
        $this->attributes["label"] = $label;
    }

    public function getLabel(){
        return $this->attributes["label"];
    }
    public function setGroupName(string $groupName): void{
        $groupName = trim(strip_tags($groupName));
        if (strlen($groupName) < 10) {
            throw new \InvalidArgumentException("O Nome do grupo da permissão deve ter ao menos 10 caracteres");
        } if (strlen($groupName) > 100) {
            throw new \InvalidArgumentException("O Nome do grupo da permissão deve ter até 100 caracteres");
        }

        $this->attributes["group_name"] = $groupName;
    }
    public function getGroupName(): string{
        return $this->attributes["group_name"];
    }

    public function groupByGroup():array
    {
        $sql = "select id,  group_name, label
                from {$this->table}
                order by group_name,label;";
        $statement = $this->connection->prepare($sql);
        $statement->execute();
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row["group_name"]][] = $row;
        }
        return $grouped;
    }
}