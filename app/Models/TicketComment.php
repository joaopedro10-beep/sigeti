<?php

namespace App\Models;

use App\Core\AbstractModel;
use App\Models\Ticket;

class TicketComment extends AbstractModel
{
    protected string $table = 'tickets_comments';

    protected string $primaryKey = 'id';
    protected array $fillable = [
        "ticket_id",
        "user_id",
        "comment"

    ];
    protected array $required = [
        "ticket_id" => "O campo Chamado é obrigatorio",
        "user_id" => "O campo Usuario é obrigatorio",
        "comment" => "O campo comentario é obrigatorio"
    ];

    protected bool $timestamps = true;

    public function getId(): ?int
    {
        return $this->attributes["id"];
    }

    public function setTicketId(int $ticketId): void
    {
        $this->attributes["ticket_id"] = $ticketId;
    }

    public function getTicketId(): int
    {
        return $this->attributes["ticket_id"];
    }

    public function setUserId(int $userId): void
    {
        $this->attributes["user_id"] = $userId;
    }

    public function getUserId(): int
    {
        return $this->attributes["user_id"];
    }

    public function setComment(string $comment): void
    {
        $comment = (strip_tags($comment));
        if (strlen($comment) < 20) {
            throw new \InvalidArgumentException("Este campo deve ter pelo menos 20 caracteres");
        }
        $this->attributes["comment"] = $comment;
    }

    public function getComment(): string
    {
        return $this->attributes["comment"];
    }

    public function getCreatedAT():string
    {
        return $this->attributes["created_at"];
    }

    public function Ticket(): ?Ticket
    {
        return Ticket::find($this->getTicketId());
    }

    public function User(): ?User
    {
        return User::find($this->getUserId());
    }


    public function validateBusinessRules(array $data): array
    {
        $errors = [];

//        $statusInvalid = [
//            Ticket::FINISHED,
//            Ticket::ARCHIVED
//        ];

        $ticket = Ticket::find((int)$data['ticket_id']);

//        if (in_array($ticket->getStatus(), $statusInvalid, true)) {
//            $errors[] = "Não é possivel comentar em chamados com status ". $ticket->getStatus();
//        }

        if ($ticket->getStatus() === Ticket::FINISHED || $ticket->getStatus() === Ticket::ARCHIVED) {
            $errors[] = "Não é possivel comentar em chamados com status ". $ticket->getStatus();
        }

        return $errors;
    }

    public static function commentsByTickets(int $ticketId): ?array
    {
           return (new static())
               ->where("ticket_id","=", $ticketId)
               ->orderBy("created_at")
               ->get();
    }
}