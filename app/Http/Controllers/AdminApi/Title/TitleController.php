<?php

namespace App\Http\Controllers\AdminApi\Title;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Title\{ StoreTitleRequest, UpdateTitleRequest };
use App\Http\Resources\Admin\TitleResource;
use App\Models\Title;
use App\Services\TitleService;
use App\Traits\ApiResponse;
use Illuminate\Validation\ValidationException;

class TitleController extends Controller
{
    use ApiResponse;

    public function __construct(
        private TitleService $titleService
    ) {}

    public function index()
    {
        $titles = $this->titleService->getAll();

        return $this->success([
            'titles' => TitleResource::collection($titles),
            'pagination' => [
                'current_page' => $titles->currentPage(),
                'last_page' => $titles->lastPage(),
                'per_page' => $titles->perPage(),
                'total' => $titles->total(),
            ],
        ]);
    }

    public function store(StoreTitleRequest $request)
    {
        $title = $this->titleService->create(
            $request->validated()
        );

        return $this->success(
            new TitleResource($title),
            'Title created successfully',
            201
        );
    }

    public function show(Title $title)
    {
        return $this->success(
            new TitleResource($title)
        );
    }

    public function update(
        UpdateTitleRequest $request,
        Title $title
    ) {
        $title = $this->titleService->update(
            $title,
            $request->validated()
        );

        return $this->success(
            new TitleResource($title),
            'Title updated successfully'
        );
    }

    public function destroy(Title $title)
    {
        try {
            $this->titleService->delete($title);

            return $this->success(
                null,
                'Title deleted successfully'
            );
        } catch (ValidationException $e) {
            return $this->error(
                'Cannot delete title because it is linked to categories',
                422
            );
        }
    }
}
