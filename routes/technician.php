<?php
$router->group("/tecnico");
$router->get("/dashboard", "Technician\\DashboardController@index");
$router->get("/categorias", "Technician\\CategoryController@index");
$router->get("/categorias/cadastrar", "Technician\\CategoryController@create");
$router->post("/categorias/cadastrar", "Technician\\CategoryController@store");
$router->get("/categorias/editar/{id}", "Technician\\CategoryController@edit");
$router->put("/categorias/editar/{id}", "Technician\\CategoryController@update");
$router->delete("/categorias/excluir/{id}", "Technician\\CategoryController@destroy");

$router->get("/escolas", "Technician\\SchoolController@index");
$router->get("/escolas/cadastrar", "Technician\\SchoolController@create");
$router->post("/escolas/cadastrar", "Technician\\SchoolController@store");
$router->get("/escolas/editar/{id}", "Technician\\SchoolController@edit");
$router->put("/escolas/editar/{id}", "Technician\\SchoolController@update");

$router->get("/usuarios", "Technician\\UserController@index");
$router->get("/usuarios/cadastrar", "Technician\\UserController@create");
$router->post("/usuarios/cadastrar", "Technician\\UserController@store");
$router->get("/usuarios/editar/{id}", "Technician\\UserController@edit");
$router->put("/usuarios/editar/{id}", "Technician\\UserController@update");

$router->get("/chamados", "Technician\\TicketController@index");
$router->get("/chamados/cadastrar", "Technician\\TicketController@create");
$router->post("/chamados/cadastrar", "Technician\\TicketController@store");
$router->get("/chamados/editar/{id}", "Technician\\TicketController@edit");
$router->put("/chamados/editar/{id}", "Technician\\TicketController@update");


$router->get("/chamados/{ticket_id}/comentarios", "Technician\\TicketCommentController@index");
$router->post("/chamados/{ticket_id}/comentarios", "Technician\\TicketCommentController@store");