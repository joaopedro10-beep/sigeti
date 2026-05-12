<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Permission;
use App\Models\Department\Department;
use App\Models\Role\Role;
use App\Models\Ticket\Ticket;
use App\Models\User;

class DashboardController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");
        Auth::requirePermission(Permission::VIEW_MANAGER_DASHBOARD);
    }

    public function index(): void
    {
        Auth::requirePermission(Permission::VIEW_MANAGER_DASHBOARD);
        $role = new Role();
        $departments = new Department();
        $user = new User();
        $ticket = new Ticket();
        $totalOpenTickets = $ticket->totalOpenTickets();
        $totalUsers = $user->totalUsers();
        $recentUsers = $user->recentUsers();
        $recentRegisteredUsers = $user->recentRegisteredUsers();
        $totalDepartments = $departments->totalDepartments();
        $totalRoles = $role->totalRoles();

        echo $this->view->render("admin/dashboard", [
            "totalUsers" => $totalUsers,
            "totalDepartments" => $totalDepartments,
            "totalRoles" => $totalRoles,
            "totalOpenTickets" => $totalOpenTickets,
            "recentUsers" => $recentUsers,
            "recentRegisteredUsers" => $recentRegisteredUsers
        ]);
    }
}