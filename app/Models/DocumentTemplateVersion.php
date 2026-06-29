<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentTemplateVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_template_id',
        'version_number',
        'content',
        'created_by_user_id',
    ];

    protected $casts = [
        'version_number' => 'integer',
    ];

    // Relationships
    public function template()
    {
        return $this->belongsTo(DocumentTemplate::class, 'document_template_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
