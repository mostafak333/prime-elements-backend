<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FaqResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'question_en' => $this->question_en,
            'question_ar' => $this->question_ar,
            'answer_en' => $this->answer_en,
            'answer_ar' => $this->answer_ar,
            'sort_order' => $this->sort_order,
        ];
    }
}
