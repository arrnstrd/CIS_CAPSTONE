<?php

namespace App\Models;

use App\Enums\ImportStatus;
use App\Models\BulkImportIssue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BulkImport extends Model
{
    protected $table = 'bulk_imports';

    protected $fillable = [
        'original_filename',
        'file_path',
        'file_hash',
        'status',
        'total_rows',
        'valid_count',
        'error_count',
        'warning_count',
        'success_count',
        'failed_count',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => ImportStatus::class,
            'total_rows' => 'int',
            'valid_count' => 'int',
            'error_count' => 'int',
            'warning_count' => 'int',
            'success_count' => 'int',
            'failed_count' => 'int',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(BulkImportIssue::class, 'bulk_import_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', ImportStatus::Pending->value);
    }

    public function scopeValidated($query)
    {
        return $query->where('status', ImportStatus::Validated->value);
    }

    public function isConfirmable(): bool
    {
        return $this->status === ImportStatus::Validated;
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, [ImportStatus::Pending, ImportStatus::Validated], true);
    }

    public function hasErrors(): bool
    {
        return $this->error_count > 0;
    }

    public function hasWarnings(): bool
    {
        return $this->warning_count > 0;
    }
}
