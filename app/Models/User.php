<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Photo files and upload folders to remove once the user row
     * (and, by cascade, their listings) is deleted.
     *
     * @var array{files: list<string>, folders: list<string>}
     */
    protected array $photosToDelete = ['files' => [], 'folders' => []];

    protected static function booted(): void
    {
        // The database cascade removes listing and image rows but not the files on disk,
        // so collect the paths first (soft-deleted listings included) and delete them afterwards.
        static::deleting(function (User $user) {
            $listingIds = Listing::withTrashed()->where('user_id', $user->id)->pluck('id');

            $user->photosToDelete = [
                'files' => ListingImage::whereIn('listing_id', $listingIds)->pluck('path')->all(),
                'folders' => $listingIds->map(fn ($id) => "listings/{$id}")->all(),
            ];
        });

        static::deleted(function (User $user) {
            $disk = Storage::disk('public');

            $disk->delete($user->photosToDelete['files']);
            foreach ($user->photosToDelete['folders'] as $folder) {
                $disk->deleteDirectory($folder);
            }
        });
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }
}
