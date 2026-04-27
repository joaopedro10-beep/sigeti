<?php

namespace App\Controllers\Teacher;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Ticket;
use App\Models\User;

class DashboardController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");
        Auth::requireRole(User::TEACHER);
    }

    public function index(): void
    {


        $ticketsModel = new Ticket();

        $userId = Auth::user()->id;

        $tickets = (new Ticket())->ticketsOrderedByStatusPriorityAndOpeningDate();

        $quantityTicketsByMonth = $ticketsModel->countTicketsByMonth($userId);
        $quantityTicketsByCategory = $ticketsModel->countTicketsByCategory($userId);
        $quantityTicketsByStatus = $ticketsModel->countTicketsByStatus($userId);






        echo $this->view->render("teacher/dashboard", [
           "tickets" => $tickets,
            "quantityTicketsByMonth" => $quantityTicketsByMonth,
            "quantityTicketsByCategory" => $quantityTicketsByCategory,
            "quantityTicketsByStatus" => $quantityTicketsByStatus,
        ]);
    }
}