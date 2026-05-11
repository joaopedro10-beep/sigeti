<?php
$router->group("/professor");
$router->get("/dashboard", "Teacher\\DashboardController@index");
$router->get("/chamados", "Teacher\\TicketController@index");
$router->get("/chamados/cadastrar", "Teacher\\TicketController@create");
$router->post("/chamados/cadastrar", "Teacher\\TicketController@store");

$router->get("/chamados/{ticket_id}/comentarios", "Teacher\\TicketCommentController@index");
$router->post("/chamados/{ticket_id}/comentarios", "Teacher\\TicketCommentController@store");