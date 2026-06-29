<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneratedDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_template_id',
        'lead_id',
        'title',
        'content',
        'pdf_path',
        'generated_by_user_id',
    ];

    // Relationships
    public function template()
    {
        return $this->belongsTo(DocumentTemplate::class, 'document_template_id');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by_user_id');
    }
}
