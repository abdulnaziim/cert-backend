<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipient_name',
        'recipient_email',
        'recipient_address',
        'title',
        'description',
        'ipfs_cid',
        'issued_at',
        'transaction_hash',
        'on_chain_id',
        'token_id',
        'metadata',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected $appends = ['ipfs_url'];

    public function getIpfsUrlAttribute(): string
    {
        // Use app() helper to resolve the service instance
        return app(\App\Services\IpfsService::class)->getGatewayUrl($this->ipfs_cid ?? '');
    }
}





