<?php

namespace App\Models;

use App\Core\AbstractModel;

class Category extends AbstractModel
{
    protected string $table = "categories";
    protected string $primaryKey = "id";

    protected array $fillable = [
        "name",
        "description"
    ];
    protected array $required = [
        "name" => "Este campo é obrigatorio",

    ];

    protected bool $timestamps = true;

    public function getId(): ?int
    {
        return $this->attributes["id"];
    }

    public function setName(string $name): void
    {
        $name = trim(strip_tags($name));
        if (strlen($name) < 5) {
            throw new \InvalidArgumentException("O campo nome deve ter mais de 5 caracteres!");
        }
        $this->attributes["name"] = $name;
    }

    public function getName(): ?string
    {
        return $this->attributes["name"];
    }

    public function setDescription(string $description): void
    {
        $description = trim(strip_tags($description));
        if (strlen($description) < 15) {
            throw new \InvalidArgumentException("O campo nome deve ter mais de 5 caracteres!");
        }
        $this->attributes["description"] = $description;
    }

    public function getDescription(): ?string
    {
        return $this->attributes["description"];
    }

    public function getCategoryByName(string $name):?self
    {
        return $this->where("name", "=", $name)->first();
    }

}