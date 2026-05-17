<?php

namespace Modules\DigitalFolder\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FolderFile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'folder_id',
        'uploaded_by',
        'name',
        'original_name',
        'path',
        'mime_type',
        'extension',
        'size',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'size' => 'integer',
    ];

    protected $appends = ['formatted_size', 'icon_class', 'download_url'];

    /**
     * Folder relationship
     */
    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * User who uploaded the file
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get human readable file size
     */
    public function getFormattedSizeAttribute()
    {
        $bytes = $this->size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            return $bytes . ' bytes';
        } elseif ($bytes == 1) {
            return '1 byte';
        } else {
            return '0 bytes';
        }
    }

    /**
     * Get icon class based on file type
     */
    public function getIconClassAttribute()
    {
        $icons = [
            // Images
            'jpg' => 'fas fa-file-image text-success',
            'jpeg' => 'fas fa-file-image text-success',
            'png' => 'fas fa-file-image text-success',
            'gif' => 'fas fa-file-image text-success',
            'svg' => 'fas fa-file-image text-success',
            'webp' => 'fas fa-file-image text-success',
            // Documents
            'pdf' => 'fas fa-file-pdf text-danger',
            'doc' => 'fas fa-file-word text-primary',
            'docx' => 'fas fa-file-word text-primary',
            'xls' => 'fas fa-file-excel text-success',
            'xlsx' => 'fas fa-file-excel text-success',
            'ppt' => 'fas fa-file-powerpoint text-warning',
            'pptx' => 'fas fa-file-powerpoint text-warning',
            'txt' => 'fas fa-file-alt text-secondary',
            'csv' => 'fas fa-file-csv text-success',
            // Archives
            'zip' => 'fas fa-file-archive text-warning',
            'rar' => 'fas fa-file-archive text-warning',
            '7z' => 'fas fa-file-archive text-warning',
            'tar' => 'fas fa-file-archive text-warning',
            'gz' => 'fas fa-file-archive text-warning',
            // Code
            'html' => 'fas fa-file-code text-info',
            'css' => 'fas fa-file-code text-info',
            'js' => 'fas fa-file-code text-warning',
            'php' => 'fas fa-file-code text-purple',
            'json' => 'fas fa-file-code text-success',
            // Media
            'mp3' => 'fas fa-file-audio text-info',
            'wav' => 'fas fa-file-audio text-info',
            'mp4' => 'fas fa-file-video text-danger',
            'avi' => 'fas fa-file-video text-danger',
            'mov' => 'fas fa-file-video text-danger',
        ];

        return $icons[strtolower($this->extension)] ?? 'fas fa-file text-secondary';
    }

    /**
     * Get download URL
     */
    public function getDownloadUrlAttribute()
    {
        return route('admin.file-explorer.download', $this->id);
    }

    /**
     * Check if file is an image
     */
    public function isImage()
    {
        return in_array(strtolower($this->extension), ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp']);
    }

    /**
     * Check if file is previewable
     */
    public function isPreviewable()
    {
        $previewable = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp', 'pdf', 'txt', 'csv', 'json', 'html', 'css', 'js'];
        return in_array(strtolower($this->extension), $previewable);
    }

    /**
     * Get full storage path
     */
    public function getFullPath()
    {
        return storage_path('app/' . $this->path);
    }

    /**
     * Scope for active files
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific extension
     */
    public function scopeOfType($query, $extension)
    {
        return $query->where('extension', $extension);
    }
}
