<?php

namespace App\Models;

use App\Core\AbstractModel;
use App\Models\Department\UserDepartment;
use App\Models\Role\Role;
use App\Models\Role\RolePermission;
use App\Models\Ticket\Ticket;

class User extends AbstractModel
{
    protected string $table = "users";

    protected string $primaryKey = 'id';

    protected array $fillable = [
        "name",
        "email",
        "password",
        "document",
        "role_id",
        "role",
        "last_login_at",
        "status",
        "reset_token",
        "reset_expires_at"
    ];

    protected array $required = [
        "name" => "O campo NOME é obrigatório.",
        "email" => "O campo EMAIL é obrigatório.",
        "password" => "O campo SENHA é obrigatório.",
        "role_id" => "o campo PERFIL é obrigatório."
    ];

    protected bool $timestamps = true;

    protected bool $softDelete = true;

    public const TEACHER = "professor";
    public const TECHNICIAN = "tecnico";

    private const ROLES = [
        self::TEACHER,
        self::TECHNICIAN
    ];

    public const REGISTERED = "registrado";
    public const ACTIVE = "ativo";
    public const INACTIVE = "inativo";
    private const STATUS = [
        self::REGISTERED,
        self::ACTIVE,
        self::INACTIVE
    ];

    public function getId(): ?int
    {
        return $this->attributes["id"];
    }

    public function setName(string $name): void
    {
        $name = trim(strip_tags($name));

        if (strlen($name) < 3) {
            throw new \InvalidArgumentException("O nome do usuário deve ter pelo menos 3 caracteres.");
        }

        $this->attributes["name"] = $name;
    }

    public function getName(): ?string
    {
        return $this->attributes["name"];
    }

    public function setEmail(string $email): void
    {
        $email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);

        if (!$email) {
            throw new \InvalidArgumentException("O Email é inválido");
        }

        $this->attributes["email"] = $email;
    }

    public function getEmail(): ?string
    {
        return $this->attributes["email"];
    }

    public function setPassword(?string $password): void
    {
        if ($password === null || $password === "") {
            throw new \InvalidArgumentException("A senha não pode ser null ou vazia.");
        }

        if (strlen($password) < 8 || strlen($password) > 16) {
            throw new \InvalidArgumentException("A senha deve ter entre 8 e 16 caracteres.");
        }

        $this->attributes["password"] = password_hash($password, PASSWORD_DEFAULT);
    }

    public function getPassword(): ?string
    {
        return $this->attributes["password"];
    }

    public function passwordVerify(string $password): bool
    {
        return password_verify($password, $this->attributes["password"] ?? null);
    }

    public function setDocument(?string $document): void
    {
        if ($document) {
            $document = preg_replace('/[^0-9]/', '', $document);

            if (strlen($document) !== 11) {
                throw new \InvalidArgumentException("O documento deve ter exatamente 11 caracteres.");
            }
        }

        $this->attributes["document"] = $document;

    }

    public function getDocument(): ?string
    {
        return $this->attributes["document"] ?? null;
    }

    public function setRoleId(int $roleId): void
    {
        if ($roleId < 1) {
            throw new \InvalidArgumentException("O ID DO PERFIL do usuário é inválido.");
        }

        $this->attributes["role_id"] = $roleId;
    }

    public function getRoleId(): int
    {
        return $this->attributes["role_id"];
    }

    public function role(): ?Role
    {
        return $this->getRoleId() ? Role::find($this->getRoleId()) : null;
    }

    public function hasPermission(string $permission): bool
    {
        if (!$this->getRoleId()) {
            return false;
        }

        return RolePermission::userHasPermission($this->getRoleId(), $permission);
    }

    public static function usersByPermission(string $permission): array
    {
        $instance = new static();

        $sql = "SELECT DISTINCT users.*
            FROM users
            INNER JOIN roles ON roles.id = users.role_id
            INNER JOIN role_permissions ON role_permissions.role_id = roles.id
            INNER JOIN permissions ON permissions.id = role_permissions.permission_id
            WHERE permissions.name = :permission
              AND users.status = 'ativo'
              AND users.deleted_at IS NULL";

        $statement = $instance->connection->prepare($sql);
        $statement->bindValue(":permission", $permission, \PDO::PARAM_STR);
        $statement->execute();

        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $users = [];
        foreach ($rows as $row) {
            $users[] = static::hydrate($row);
        }

        return $users;
    }

    public function departments(): array
    {
        return UserDepartment::linksByUser($this->getId());
    }

    public function existsDepartmentLinks(): bool
    {
        return (new UserDepartment())
                ->where("user_id", "=", $this->getId())
                ->count() > 0;
    }

    public function setRole(?string $role): void
    {
        $role = $role ?? self::TEACHER;

        if (!in_array($role, self::ROLES)) {
            throw new \InvalidArgumentException("O perfil é inválido.");
        }

        $this->attributes["role"] = $role;
    }

    public function getRole(): ?string
    {
        return $this->attributes["role"];
    }

    public function setLastLoginAt(): void
    {
        $timezone = new \DateTimeZone(APP_TIMEZONE);
        $now = new \DateTimeImmutable("now", $timezone);
        $this->attributes["last_login_at"] = $now->format("Y-m-d H:i:s");
    }

    public function getLastLoginAt(): ?string
    {
        return $this->attributes["last_login_at"];
    }

    public function setStatus(?string $status): void
    {
        $status = $status ?? self::REGISTERED;

        if (!in_array($status, self::STATUS)) {
            throw new \InvalidArgumentException("O status é inválido");
        }

        $this->attributes["status"] = $status;

    }

    public function getStatus(): ?string
    {
        return $this->attributes["status"];
    }

    public static function findByEmail(?string $email): ?self
    {
        return (new static())->where("email", "=", $email)->first();
    }

    public function setResetToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->attributes["reset_token"] = hash("sha256", $token);
        $this->setResetExpiresAt();
        return $token;
    }

    public function getResetToken(): ?string
    {
        return $this->attributes["reset_token"] ?? null;
    }

    public function setResetExpiresAt(): void
    {
        $timezone = new \DateTimeZone(APP_TIMEZONE);
        $expiresAt = new \DateTimeImmutable("now", $timezone);
        $this->attributes["reset_expires_at"] = $expiresAt->modify("+2 hours")->format("Y-m-d H:i:s");
    }

    public function getResetExpiresAt(): ?string
    {
        return $this->attributes["reset_expires_at"] ?? null;
    }

    public static function findByResetToken(string $token): ?self
    {
        $hash = hash("sha256", $token);
        return (new static())->where("reset_token", "=", $hash)->first();
    }

    public function schools(): ?array
    {
        $links = $this->schoolUserLinks();

        $schools = [];

        /** @var SchoolUser $link */
        foreach ($links as $link) {
            $schools[] = School::find($link->getSchoolId());
        }

        return $schools ?? null;
    }

    public function schoolUserLinks(): ?array
    {
        return (new SchoolUser())->where("user_id", "=", $this->getId())->get();
    }

    public static function usersByRole(string $role): ?array
    {
        return (new static())->where("role", "=", $role)->get();
    }

    public function existsUserByEmail(string $email, ?int $ignoreId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE email = :email";
        $params = ['email' => $email];

        if ($ignoreId) {
            $sql .= " AND id != :ignore_id";
            $params['ignore_id'] = $ignoreId;
        }

        $statement = $this->connection->prepare($sql);
        $statement->execute($params);
        return (int)$statement->fetchColumn() > 0;
    }

    public function existsUserByDocument(string $document, ?int $ignoreId = null): bool
    {
        $document = preg_replace('/[^0-9]/', '', $document);

        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE document = :document";
        $params = ['document' => $document];

        if ($ignoreId) {
            $sql .= " AND id != :ignore_id";
            $params['ignore_id'] = $ignoreId;
        }

        $statement = $this->connection->prepare($sql);
        $statement->execute($params);
        return (int)$statement->fetchColumn() > 0;
    }

    public function validateBusinessRule(?int $ignoreId = null): array
    {
        $errors = [];

        if ($this->existsUserByEmail($this->getEmail(), $ignoreId)) {
            $errors[] = "Já existe um usuário com esse e-mail.";
        }

        $document = $this->getDocument();
        if ($document !== null && $this->existsUserByDocument($document, $ignoreId)) {
            $errors[] = "Já existe um usuário com esse documento.";
        }

        return $errors;
    }

    public function existsTickets(): bool
    {
        return (new Ticket())
                ->where("opened_by", "=", $this->getId())
                ->count() > 0;
    }

    public function existsSchoolLinks(): bool
    {
        return (new SchoolUser())
                ->where("user_id", "=", $this->getId())
                ->count() > 0;
    }

    public function totalNumberOfActiveAndRegisteredUsersNotDeleted(): ?int
    {
        return (new static())
            ->where("status", "!=", 'inativo')
            ->orderBy("created_at", "DESC")
            ->count();
    }

    public function recentlyCreatedActiveRegisteredAndNonDeletedUsers(): ?array
    {
        return (new static())
            ->where("status", "!=", 'inativo')
            ->orderBy("created_at", "DESC")
            ->limit(5)
            ->get();
    }

    public static function totalUsers():?int
    {
        $instance = new static();
        $sql = "select count(*) from users where deleted_at IS NULL and status = 'ativo'";

        $statement = $instance->connection->prepare($sql);
        $statement->execute();

        $totalUsers = $statement->fetchColumn();

        return $totalUsers;
    }

    public static function recentUsers():?array
    {
        $instance = new static();
        $sql = "select * from users where deleted_at IS NULL and status != 'inativo' order by created_at desc limit 7";

        $statement = $instance->connection->prepare($sql);
        $statement->execute();

        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $recentUsers = $statement->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $recentUsers[] = static::hydrate($row);
        }

        return $recentUsers;
    }

    public static function recentRegisteredUsers():?array
    {
        $instance = new static();
        $sql = "select * from users where deleted_at IS NULL and status = 'registrado'";

        $statement = $instance->connection->prepare($sql);
        $statement->execute();

        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $recentRegisteredUsers = $statement->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $recentRegisteredUsers[] = static::hydrate($row);
        }

        return $recentRegisteredUsers;
    }
}