<?php

namespace App\Http\Resources\File;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'owner' => $this->owner,
            'is_folder' => $this->is_folder,
            'created_by' => $this->created_by,
            'parent_id' => $this->parent_id,
            'is_starred_file' => $this->is_starred,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at->diffForHumans(),
            'updated_at' => $this->updated_at->diffForHumans(),
            'size' => $this->file_size,
            'path' => $this->path,
            'mime' => $this->mime,
        ];
    }
}
