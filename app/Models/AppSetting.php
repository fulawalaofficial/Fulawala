<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AppSetting extends Model {
    protected $fillable = ['setting_key','setting_value'];
    public static function valueFor(string $key, $default = null) {
        return static::where('setting_key', $key)->value('setting_value') ?? $default;
    }
}
