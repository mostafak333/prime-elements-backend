<?php

namespace App\Http\Controllers\AdminApi\Title;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTitleRequest;
use App\Http\Requests\UpdateTitleRequest;
use App\Http\Resources\TitleResource;
use App\Models\Title;
use App\Services\TitleService;
use App\Traits\ApiResponse;

class TitleController extends Controller
{
    use ApiResponse;

    public function __construct(
        private TitleService $titleService
    ) {
    }

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
        $this->titleService->delete($title);

        return $this->success(
            null,
            'Title deleted successfully'
        );
    }
}