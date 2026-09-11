<?php

namespace App\Models;

use App\Models\BulkImport;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulkImportIssue extends Model
{
    protected $table = 'bulk_import_issues';

    protected $fillable = [
        'bulk_import_id',
        'row_number',
        'issue_type',
        'severity',
        'field',
        'message',
        'raw_data',
        'status',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'raw_data' => 'array',
            'resolved_at' => 'datetime',
            'row_number' => 'int',
        ];
    }

    public function bulkImport(): BelongsTo
    {
        return $this->belongsTo(BulkImport::class, 'bulk_import_id');
    }

    public function isUnresolved(): bool
    {
        return $this->status === 'unresolved';
    }

    public function isAcknowledged(): bool
    {
        return $this->status === 'acknowledged';
    }

    public function acknowledge(): void
    {
        $this->update([
            'status' => 'acknowledged',
            'resolved_at' => now(),
        ]);
    }
}
