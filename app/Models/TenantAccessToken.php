<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantAccessToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'token',
        'last_used_at',
    ];

    protected $hidden = [
        'token',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    public function tenant(): BelongsTo {
        return $this->belongsTo(Tenant::class);
    }

    public static function generate($tenant_id): TenantAccessToken {
        do {
            $str = Str::random(64);
        } while (self::tokenExists($str));

        $token = new TenantAccessToken();
        $token->tenant_id = $tenant_id;
        $token->token = $str;
        $token->save();

        return $token;
    }

    public static function findFromToken($token): ?TenantAccessToken {
        return self::where('token', $token)->first();
    }

    public static function tokenExists($token): bool {
        return self::where('token', $token)->first() instanceof self;
    }
}
