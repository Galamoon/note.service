<?php

namespace Galamoon\Notes\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\Engine\Response\Json as JsonResponse;
use Bitrix\Main\Error;
use Bitrix\Main\Application;
use Bitrix\Main\Data\Cache;
use Galamoon\Notes\Helpers\Sanitizer;
use Galamoon\Notes\Services\NoteService;
use OpenApi\Attributes as OAT;

#[OAT\Info(version: '1.0.0', description: 'REST API for managing personal notes', title: 'Notes API')]
#[OAT\Server(url: '/api/v1', description: 'API server')]
class NoteController extends Controller
{
    /**
     * Default pre-filters for all actions.
     */
    protected function getDefaultPreFilters(): array
    {
        return [
            new Authentication(),
        ];
    }

    #[OAT\Get(
        path: '/api/v1/notes',
        operationId: 'listNotes',
        summary: 'List active notes',
        tags: ['Notes'],
        parameters: [
            new OAT\Parameter(name: 'limit', in: 'query', schema: new OAT\Schema(type: 'integer', default: 20)),
            new OAT\Parameter(name: 'page', in: 'query', schema: new OAT\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OAT\Response(response: 200, description: 'List of notes', content: new OAT\JsonContent(
                properties: [
                    new OAT\Property(property: 'items', type: 'array', items: new OAT\Items(ref: OAT\Property::class)),
                    new OAT\Property(property: 'total', type: 'integer'),
                    new OAT\Property(property: 'limit', type: 'integer'),
                    new OAT\Property(property: 'page', type: 'integer'),
                ]
            )),
            new OAT\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function listAction(): JsonResponse
    {
        $userId = $this->getCurrentUser()->getId();
        $params = [
            'limit' => (int)($this->getRequest()->getQuery('limit') ?? 20),
            'page' => (int)($this->getRequest()->getQuery('page') ?? 1),
        ];

        $cache = Cache::createInstance();
        $taggedCache = Application::getInstance()->getTaggedCache();
        $cacheId = 'notes_list_' . $userId . '_' . $params['limit'] . '_' . $params['page'];
        $cacheDir = '/galamoon/notes';
        $cacheTtl = 300;

        if ($cache->initCache($cacheTtl, $cacheId, $cacheDir)) {
            $result = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            $taggedCache->startTagCache($cacheDir);
            $taggedCache->registerTag('galamoon_notes');

            $result = NoteService::getList($userId, $params);

            if (empty($result['items'])) {
                $cache->abortDataCache();
            } else {
                $taggedCache->endTagCache();
                $cache->endDataCache($result);
            }
        }

        return AjaxJson::createSuccess($result);
    }

    #[OAT\Get(
        path: '/api/v1/notes/{id}',
        operationId: 'getNote',
        summary: 'Get single note',
        tags: ['Notes'],
        parameters: [
            new OAT\Parameter(name: 'id', in: 'path', required: true, schema: new OAT\Schema(type: 'integer')),
        ],
        responses: [
            new OAT\Response(response: 200, description: 'Note details'),
            new OAT\Response(response: 404, description: 'Note not found'),
            new OAT\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function getAction(int $id): JsonResponse
    {
        $userId = $this->getCurrentUser()->getId();
        $note = NoteService::getById($id, $userId);

        if (!$note) {
            $this->addError(new Error('Note not found or access denied', 'NOT_FOUND'));
            return new JsonResponse(null, 404);
        }

        return AjaxJson::createSuccess($note);
    }

    #[OAT\Post(
        path: '/api/v1/notes',
        operationId: 'createNote',
        summary: 'Create a new note',
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                required: ['title', 'text'],
                properties: [
                    new OAT\Property(property: 'title', type: 'string', maxLength: 255),
                    new OAT\Property(property: 'text', type: 'string'),
                ],
                example: ['title' => 'Meeting notes', 'text' => 'Discuss project timeline']
            )
        ),
        tags: ['Notes'],
        responses: [
            new OAT\Response(response: 201, description: 'Note created'),
            new OAT\Response(response: 422, description: 'Validation error'),
            new OAT\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function createAction(): JsonResponse
    {
        $userId = $this->getCurrentUser()->getId();
        $data = $this->getRequest()->getJsonList()->toArray();

        // Sanitize input data
        if (isset($data['title'])) {
            $data['title'] = Sanitizer::sanitize($data['title']);
        }
        if (isset($data['text'])) {
            $data['text'] = Sanitizer::sanitize($data['text']);
        }

        $result = NoteService::create($userId, $data);

        if (!$result->isSuccess()) {
            return new JsonResponse([
                'errors' => $this->formatErrors($result->getErrors()),
            ], 422);
        }

        $primary = $result->getPrimary();
        $noteId = $primary['ID'] ?? $result->getData()['ID'] ?? null;

        $note = NoteService::getById((int)$noteId, $userId);
        Application::getInstance()->getTaggedCache()->clearByTag('galamoon_notes');

        return AjaxJson::createSuccess($note);
    }

    #[OAT\Put(
        path: '/api/v1/notes/{id}',
        operationId: 'updateNote',
        summary: 'Update an existing note',
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                properties: [
                    new OAT\Property(property: 'title', type: 'string', maxLength: 255),
                    new OAT\Property(property: 'text', type: 'string'),
                ]
            )
        ),
        tags: ['Notes'],
        parameters: [
            new OAT\Parameter(name: 'id', in: 'path', required: true, schema: new OAT\Schema(type: 'integer')),
        ],
        responses: [
            new OAT\Response(response: 200, description: 'Note updated'),
            new OAT\Response(response: 404, description: 'Note not found'),
            new OAT\Response(response: 422, description: 'Validation error'),
            new OAT\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function updateAction(int $id): JsonResponse
    {
        $userId = $this->getCurrentUser()->getId();
        $data = $this->getRequest()->getJsonList()->toArray();

        // Sanitize input data
        if (isset($data['title'])) {
            $data['title'] = Sanitizer::sanitize($data['title']);
        }
        if (isset($data['text'])) {
            $data['text'] = Sanitizer::sanitize($data['text']);
        }

        $result = NoteService::update($id, $userId, $data);

        if (!$result->isSuccess()) {
            return new JsonResponse([
                'errors' => $this->formatErrors($result->getErrors()),
            ], 422);
        }

        $note = NoteService::getById($id, $userId);
        Application::getInstance()->getTaggedCache()->clearByTag('galamoon_notes');

        return AjaxJson::createSuccess($note);
    }

    #[OAT\Delete(
        path: '/api/v1/notes/{id}',
        operationId: 'deleteNote',
        summary: 'Soft delete a note',
        tags: ['Notes'],
        parameters: [
            new OAT\Parameter(name: 'id', in: 'path', required: true, schema: new OAT\Schema(type: 'integer')),
        ],
        responses: [
            new OAT\Response(response: 200, description: 'Note deleted'),
            new OAT\Response(response: 404, description: 'Note not found'),
            new OAT\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function deleteAction(int $id): JsonResponse
    {
        $userId = $this->getCurrentUser()->getId();

        $result = NoteService::softDelete($id, $userId);

        if (!$result->isSuccess()) {
            return new JsonResponse([
                'errors' => $this->formatErrors($result->getErrors()),
            ], 403);
        }

        Application::getInstance()->getTaggedCache()->clearByTag('galamoon_notes');
        return AjaxJson::createSuccess(['deleted' => true, 'id' => $id]);
    }

    /**
     * Format Bitrix errors for API response.
     */
    private function formatErrors(array $errors): array
    {
        $formatted = [];
        foreach ($errors as $error) {
            $formatted[] = [
                'code' => $error->getCode(),
                'message' => $error->getMessage(),
            ];
        }
        return $formatted;
    }
}
