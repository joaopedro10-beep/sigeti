<?php

namespace App\Controllers\Technician;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Message;
use App\Models\Category;
use App\Models\School;
use App\Models\SchoolUser;
use App\Models\Ticket;
use App\Models\User;


class TicketController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");
        Auth::requireRole(User::TECHNICIAN);
    }

    public function index(): void
    {
        $tickets = (new Ticket())->ticketsOrderedByStatusPriorityAndOpeningDate();

        echo $this->view->render("technician/tickets/index", [
            "tickets" => $tickets
        ]);
        clear_old();
    }

    public function create(): void
    {
        $schools = School::all();
        $categories = Category::all();
        $teachers = User::usersByRole(User::TEACHER);

        echo $this->view->render("technician/tickets/create", [
            "schools" => $schools,
            "categories" => $categories,
            "teachers" => $teachers
        ]);
        clear_old();
    }

    public function store(?array $data): void
    {
        $this->validateCsrfToken($data, "/professor/chamados/cadastrar");

        $loggedUser = User::find(Auth::user()->id);
        $userSchools = $loggedUser->schoolUserLinks();

        if (empty($userSchools)) {
            Message::warning("Você não está vinculado a nenhuma escola. Contacte o administrador.");
            redirect("/professor/chamados/cadastrar");
            return;
        }

        if (count($userSchools) === 1) {

            $schoolId = $userSchools[0]->getSchoolId();

        } else {

            if (!$data["school_id"]) {
                Message::warning("Selecione a escola para o chamado.");
                redirect("/professor/chamados/cadastrar");
                return;
            }

            $schoolIds = [];

            /** @var SchoolUser $link */
            foreach ($userSchools as $link) {
                $schoolIds[] = $link->getSchoolId();
            }

            if (!in_array($data['school_id'], $schoolIds, true)) {
                Message::warning("A escola selecionada não pertence ao seu vínculo.");
                redirect("/professor/chamados/cadastrar");
                return;
            }

            $schoolId = $data["school_id"];
        }

        $ticket = new Ticket();

        $payload = [
            "title" => $data["title"] ?? null,
            "description" => $data["description"],
            "school_id" => $schoolId,
            "category_id" => $data["category_id"],
            "opened_by" => $loggedUser->getId(),
            "status" => Ticket::OPEN,
            "priority" => Ticket::MEAN,
        ];

        $errors = array_merge(
            $ticket->validate($payload),
            $ticket->validateBusinessRulesForTeacher($payload)
        );

        if ($errors) {

            flash_old($data);

            foreach ($errors as $error) {
                Message::warning($error);
            }
            redirect("/professor/chamados/cadastrar");
            return;
        }

        try {

            $ticket->fill($payload);
            $ticket->setOpenedAt();
            $ticket->save();

        } catch (\InvalidArgumentException $invalidArgumentException) {

            Message::error($invalidArgumentException->getMessage());
            redirect("/professor/chamados/cadastrar");
            return;
        }

        Message::success("Chamado aberto com sucesso.");
        redirect("/professor/chamados/" . $ticket->getId() . "/comentarios");
    }

    public function edit(?array $data): void
    {
        $technicians = User::usersByRole(User::TECHNICIAN);

        $ticket = Ticket::find($data["id"]);
        if (!$ticket) {
            Message::error("Chamado nao encontrado!");
            redirect("/tecnico/chamados/");
            return;
        }
        echo $this->view->render("technician/tickets/edit", [
            "ticket" => $ticket,
            "technicians" => $technicians
        ]);
        clear_old();
    }

    public function update(?array $data): void
    {
        $this->validateCsrfToken($data, "/tecnico/chamados/editar/" . $data['id']);

        $ticketId = $data['id'];
        $ticket = Ticket::find($ticketId);

        if (!$ticketId){
            Message::error("Chamado nao encontrado ou não existe!");
            redirect("/tecnico/chamados/editar/" . $ticket->getId());
            return;
        }
        //validação do técnico
        $errors = array_merge(
            $ticket->validateTechnician($data),
            $ticket->validateStatusTransition($data['status']),
        );


        if ($errors) {
            flash_old($data);
            foreach ($errors as $error) {
                Message::warning($error);
            }
            redirect("/tecnico/chamados/editar/" . $ticket->getId());
            return;
        }
        try {
            $ticket->fill([
                "status" => $data["status"],
                "priority" => $data["priority"]
            ]);

            if(!empty($data['assigned_to'])){
                $ticket->setAssignedTo($data['assigned_to']);
            }

            if(in_array($data['status'], [Ticket::FINISHED, Ticket::ARCHIVED], true)) {
                $ticket->setClosedAt();
            }
            $ticket->save();


        } catch (\InvalidArgumentException $invalidArgumentException) {
            Message::error($invalidArgumentException->getMessage());
            redirect("/tecnico/chamados/editar/" . $ticket->getId());
            return;
        }
        Message::success("Chamado atualizado com sucesso!");
        redirect("/tecnico/chamados/editar/" . $ticket->getId());
    }


}