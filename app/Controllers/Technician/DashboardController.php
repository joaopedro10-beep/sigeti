<?php

namespace App\Controllers\Technician;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Ticket;
use App\Models\User;

class DashboardController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");
        Auth::requireRole(User::TECHNICIAN);

    }

    public function index()
    {
        $ticketsModel = new Ticket();
        $tickets = (new Ticket())->ticketsOrderedByStatusPriorityAndOpeningDate();
        $quantityTicketsByMonth = $ticketsModel->countTicketsByMonth();
        $quantityTicketsByCategory = $ticketsModel->countTicketsByCategory();
        $quantityTicketsByStatus = $ticketsModel->countTicketsByStatus();

        $avgResolutionsDays = $ticketsModel->avgResolutionDaysByMonthCurrentYear(2024);
        $ticketsPriorityAndStatus = $ticketsModel->countByPriorityAndStatusCurrentYear(2024);

        echo $this->view->render("technician/dashboard", [
            "tickets" => $tickets,
            "quantityTicketsByMonth" => $quantityTicketsByMonth,
            "quantityTicketsByCategory" => $quantityTicketsByCategory,
            "quantityTicketsByStatus" => $quantityTicketsByStatus,
            "avgResolutionsDays" => $avgResolutionsDays,
            "ticketsPriorityAndStatus" => $ticketsPriorityAndStatus
        ]);
    }
}