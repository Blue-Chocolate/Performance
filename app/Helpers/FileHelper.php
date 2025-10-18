<?php


namespace App\Helpers;


use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class FileHelper
{
public static function storeAxisFile(UploadedFile $file, $orgId, $axisId)
{
$timestamp = now()->format('YmdHis');
$uuid = (string) Str::uuid();
$orig = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
$clean = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $orig);
$ext = $file->getClientOriginalExtension();
$name = "{$timestamp}_{$uuid}_{$clean}.{$ext}";
$dir = "axes_attachments/{$orgId}/{$axisId}";
Storage::disk('public')->putFileAs($dir, $file, $name);
return "storage/{$dir}/{$name}";
}


public static function deleteStoragePath($storageUrl)
{
if (!$storageUrl) return;
// expected 'storage/axes_attachments/...'
$relative = preg_replace('/^storage\//', '', $storageUrl);
Storage::disk('public')->delete($relative);
}
}