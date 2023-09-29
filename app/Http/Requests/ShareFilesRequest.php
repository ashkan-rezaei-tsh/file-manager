<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShareFilesRequest extends FileActionRequest
{

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'emails' => 'required|array',
            'emails.*' => ['required', 'email',/* 'exists:users,email'*/]
        ]);
    }
}
