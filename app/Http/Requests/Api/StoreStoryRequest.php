<?php

namespace App\Http\Requests\Api;

class StoreStoryRequest extends CustomRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string'],
            'file' => ['required', 'mimetypes:image/jpeg,image/png,image/jpg,image/gif,video/mp4,video/mpeg,video/quicktime', 'max:10240',
            ],
        ];
    }
}
