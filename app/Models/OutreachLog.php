<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class OutreachLog extends Model {
    public $timestamps = false;
    protected $fillable = ['place_id','action','status','note'];
    protected $casts    = ['created_at' => 'datetime'];
    public function place() { return $this->belongsTo(Place::class); }
}
