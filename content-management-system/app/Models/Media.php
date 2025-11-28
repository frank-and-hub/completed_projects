<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'media';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'role_id', 'slug', 'path', 'name', 'mime_type', 'extension', 'size', 'tags', 'thumbnail'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'tags' => 'array',
        'thumbnail' => 'array'
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($media) {
            Storage::delete($media->path);
            foreach ($media->thumbnail as $t) {
                Storage::delete($t);
            }
        });


    }

    public function parkimage(){
        return $this->hasMany(ParkImage::class,'media_id');
    }

    /**
     * Get the
     *
     * @param  string  $value
     * @return string
     */
    public function getFullPathAttribute(): string
    {
        return Storage::url($this->path);
    }

    public static function save_media($file, string $dir = 'all', array $tags = [], int $user_id = null, int $role_id = null, $store_as = 'file')
    {
        $path = $file->storePublicly("0/" .$dir);

        $thumbnail = [];
        if ($store_as == 'image') {
            $thumbnail = Media::create_thumbnails($file, $path);
        }

        $image_slug = \App\Helpers\Slug::create($file->getClientOriginalName(), Media::class);

        return Media::create([
            "user_id" => $user_id,
            "role_id" => $role_id,
            "slug" => $image_slug,
            "path" => $path,
            "name" => $file->getClientOriginalName(),
            "mime_type" => $file->getMimeType(),
            "extension" => $file->getClientOriginalExtension(),
            "size" => $file->getSize(),
            "tags" => $tags,
            "thumbnail" => $thumbnail,
        ]);
    }

    public static function create_thumbnails($file, $path)
    {
        $img = \Intervention\Image\Facades\Image::make($file);

        $img->resize(300, null, function ($constraint) {
            $constraint->aspectRatio();
        });

        $path1 = '1' . $path;
        Storage::put($path1, $img->encode("jpg", 75), 'public');

        $img->resize(120, null, function ($constraint) {
            $constraint->aspectRatio();
        });

        $path2 = '2' . $path;
        Storage::put($path2, $img->encode("jpg", 75), 'public');

        [$width,$height] = getimagesize($file);
        if($width>1600){
            $img->resize(1600, null, function ($constraint) {
                $constraint->aspectRatio();
            }); 
            $path3 = '3'.$path;
            Storage::put($path3,$img->encode("jpg",75),'public');
        return ["md" =>  $path1,  "sm" => $path2,'resized'=>$path3];

        }
        return ["md" =>  $path1,  "sm" => $path2];
    }

    function get_size($dec = 2)
    {
        $size   = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $factor = floor((strlen($this->size) - 1) / 3);

        return sprintf("%.{$dec}f", $this->size / pow(1024, $factor)) . @$size[$factor];
    }

    public function get_thumbnails()
    {
        $urls = [];
        foreach ($this->thumbnail as $k => $t) {
            $urls[$k] = Storage::url($t);
        }
        return $urls;
    }


    /**
     * Get the user that owns the Media
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
