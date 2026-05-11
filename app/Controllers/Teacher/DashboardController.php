<?php

namespace App\Controllers\Teacher;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Ticket\Ticket;
use App\Core\Permission;

class DashboardController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");
        Auth::requirePermission(Permission::VIEW_REQUESTER_DASHBOARD);
    }

    public function index(): void
    {
        Auth::requirePermission(Permission::VIEW_REQUESTER_DASHBOARD);

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