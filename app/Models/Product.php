<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 
use Illuminate\Support\Str;


class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

     protected $fillable = [
        'user_id',
        'name',
        'description',
        'starting_price',
        'current_price',
        'auction_end_time',
        'image',
        'is_active',  
        'youtube_live_url',
       'winner_id'
    ];

     protected $casts = [
        'auction_end_time' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bids()
    {
        return $this->hasMany(Bid::class)->orderBy('created_at', 'desc');
    }

    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class)->orderBy('created_at', 'asc');
    }

    public function highestBid()
    {
        return $this->hasOne(Bid::class)->orderBy('amount', 'desc');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
        public function winner()
    {
        return $this->belongsTo(User::class, 'winner_id');
    }
    
  public function getYoutubeId()
{
    if (!$this->youtube_live_url) return null;

    $url = $this->youtube_live_url;
    $host = parse_url($url, PHP_URL_HOST);

    if (Str::contains($host, 'youtu.be')) {
        // Handle shortened URL like youtu.be/VIDEO_ID
        return ltrim(parse_url($url, PHP_URL_PATH), '/');
    }

    if (Str::contains($url, '/live/')) {
        // Handle live URL like youtube.com/live/VIDEO_ID
        return basename(parse_url($url, PHP_URL_PATH));
    }

    parse_str(parse_url($url, PHP_URL_QUERY), $params);
    return $params['v'] ?? null;
}

}
