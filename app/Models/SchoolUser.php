<?php

namespace App\Models;

use App\Core\AbstractModel;
use http\Exception\InvalidArgumentException;

class SchoolUser extends AbstractModel
{
    protected string $table = "school_users";
    protected string $primaryKey = "id";

    protected array $fillable = [
        "school_id",
        "user_id",
        "shift"
    ];

    protected array $required = [
        "school_id" => "O campo, escola é obrigatoria",
        "user_id" => "O campo, usuario é obrigatorio",
        "shift" => "O campo turno é obrigatório"
    ];

    protected bool $timestamps = false;

    public function getId(): ?int
    {
        return $this->attributes["id"];
    }

    public function setSchoolId(int $schoolId): void
    {
        $this->attributes["school_id"] = $schoolId;
    }

    public function getSchoolId(): int
    {
        return $this->attributes["school_id"];
    }

    public function setUserId(int $userId): void
    {
        $this->attributes["user_id"] = $userId;
    }

    public function getUserId(): int
    {
        return $this->attributes["user_id"];
    }

    public const MORNING = "manha";
    public const AFTERNOON = "tarde";
    public const WHOLE = "integral";

    public const SHIFTS = [
        self::MORNING,
        self::AFTERNOON,
        self::WHOLE,
    ];

    public function setShift(string $shift): void
    {
        $shift = $shift ?? self::WHOLE;
        if (!in_array($shift, self::SHIFTS)) {
            throw new \InvalidArgumentException("O turno é invalido");
        }
        $this->attributes["shift"] = $shift;
    }

    public function getShift(): string{
        return $this->attributes["shift"];
    }

    public function school(): ?School
    {
        return School::find($this->getSchoolId());
    }

    public function user(): ?User
    {
        return User::find($this->getSchoolId());
    }

    public function findBySchoolAndId(int $schoolId, int $userId): ?self
    {
        return (new static())
            ->where("school_id", $schoolId)
            ->where("user_id", $userId)
            ->first();
    }

    public static function linksByUser(?int $userId): ?array
    {
        return (new static())->where("user_id", "=", $userId)->get();
    }

    public static function validateSchoolUserLinks(array $links): ?array
    {
        if (empty($links)) {
            return ["Vincule o professor a pelo menos uma escola."];
        }

        $links = self::validateSchools($links);

        if (empty($links)) {
            return ["Nenhuma escola válida foi informada."];
        }

        $errors = [];

        $shifts = [];

        foreach ($links as $link) {
            if (!empty($link['shift'])) {
                $shifts[] = $link['shift'];
            }
        }

        if (in_array(self::WHOLE, $shifts, true) && count($shifts) > 1) {
            $errors[] = "Se o professor é do turno INTEGRAL, não pode selecionar outros turnos.";
        }

        $shiftsCount = array_count_values($shifts);

        foreach ($shiftsCount as $shift => $count) {
            if ($count > 1) {
                $value = match ($shift) {
                    self::MORNING => "Manha",
                    self::AFTERNOON => "Tarde",
                    self::WHOLE => "Integral"
                };

                $errors[] = "O turno {$value} não pode ser usado em mais de uma escola.";
            }
        }

        return $errors;
    }

    public static function validateSchools(array $links): array
    {
        $validSchools = [];

        foreach ($links as $link) {
            $schoolId = $link["school_id"] ?? 0;

            if (School::find((int)$schoolId)) {
                $validSchools[] = $link;
            }
        }

        return $validSchools;
    }
}