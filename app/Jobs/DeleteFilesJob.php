<?php

namespace App\Jobs;

use App\Models\File;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class DeleteFilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private int $parentId,
        private array $data
    ) {
        //
    }

    public function handle(): void
    {
        $parent = File::find($this->parentId);
        if (! is_null(Arr::get($this->data, 'all'))) {
            $childrens = $parent->descendants;

            foreach ($childrens as $child) {
                $child->delete();
            }
        } else {
            foreach (Arr::get($this->data, 'ids') as $id) {
                $file = File::find($id);
                $file->delete();
            }
        }
    }
}
