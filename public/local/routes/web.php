<?php

use Bitrix\Main\Routing\RoutingConfigurator;
use Galamoon\Notes\Controller\NoteController;

return static function (RoutingConfigurator $router) {
    // REST API routes for notes (specific routes first)
    $router->get('/api/v1/notes/{id}/', [NoteController::class, 'getAction'])->where('id', '\d+');
    $router->put('/api/v1/notes/{id}/', [NoteController::class, 'updateAction'])->where('id', '\d+');
    $router->delete('/api/v1/notes/{id}/', [NoteController::class, 'deleteAction'])->where('id', '\d+');
    $router->get('/api/v1/notes/', [NoteController::class, 'listAction']);
    $router->post('/api/v1/notes/', [NoteController::class, 'createAction']);

    // Legacy page route
    $router->get('/notes/', static function () {
        require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
        global $APPLICATION;

        global $USER;
        // Проверяем авторизацию. Если нет — редирект на авторизацию
        if (!$USER->IsAuthorized()) {
            LocalRedirect('/auth/?backurl='.urlencode('/notes/'));
            die();
        }

        $APPLICATION->IncludeComponent('galamoon:notes', '.default', [
            'USE_NAVIGATION' => 'N',
        ]);
        require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
    })->name('galamoon_notes_page');
};
