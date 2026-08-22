<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolicyChunk extends Model
{
    protected $fillable = ['policy_document_id', 'chunk_index', 'content'];

    public function document(): BelongsTo
    {
        return $this->belongsTo(PolicyDocument::class, 'policy_document_id');
    }

    // The retrieval step of RAG: ranks chunks by MySQL's native relevance score
    public function scopeSearch($query, string $term, int $limit = 4)
    {
        return $query
            ->selectRaw('policy_chunks.*, MATCH(content) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance', [$term])
            ->whereRaw('MATCH(content) AGAINST(? IN NATURAL LANGUAGE MODE)', [$term])
            ->orderByDesc('relevance')
            ->limit($limit);
    }
}