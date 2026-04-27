<?php
require_once __DIR__ . "/vendor/autoload.php";
use App\Core\Session;
use App\Models\Category;
use App\Models\School;
use App\Models\Ticket;

new Session();

require __DIR__ . "/routes/web.php";

