<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BulkImportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'original_filename'   => $this->original_filename,
            'file_hash'           => $this->file_hash,
            'duplicate_warning'   => $this->duplicate_warning ?? null,
            'status'              => $this->status?->value ?? $this->status,
            'total_rows'          => $this->total_rows,
            'valid_count'         => $this->valid_count,
            'error_count'         => $this->error_count,
            'warning_count'       => $this->warning_count,
            'success_count'       => $this->success_count,
            'failed_count'        => $this->failed_count,
            'progress_percentage' => $this->total_rows > 0
                ? round((($this->success_count + $this->failed_count) / $this->total_rows) * 100, 1)
                : 0,
            'issues_count'        => $this->whenCounted('issues'),
            'created_by'          => $this->whenLoaded('createdBy', fn() => [
                'id'   => $this->createdBy->id,
                'name' => ($this->createdBy->first_name ?? '') . ' ' . ($this->createdBy->last_name ?? ''),
            ]),
            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,
        ];
    }
}
