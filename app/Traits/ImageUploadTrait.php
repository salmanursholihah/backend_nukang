<?php

namespace App\Traits;

use Illuminate\Support\Facades\File;

trait ImageUploadTrait
{
    /**
     * Upload image ke public/images/{folder}/
     * Folder dibuat OTOMATIS jika belum ada.
     *
     * Struktur folder:
     *   public/images/profiles/     ← avatar user
     *   public/images/tukang/       ← foto profil & KTP tukang
     *   public/images/orders/       ← foto progress order
     *   public/images/services/     ← thumbnail service
     *   public/images/categories/   ← icon category
     *   public/images/chats/        ← attachment chat
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $folder   nama subfolder, contoh: 'profiles', 'tukang', 'orders'
     * @return string            path relatif dari public/, contoh: "images/profiles/abc_123.jpg"
     *
     * Cara akses URL: asset($path)
     * → http://domain.com/images/profiles/abc_123.jpg
     */
    protected function uploadImage($file, string $folder): string
    {
        $directory = public_path("images/{$folder}");

        // Buat folder otomatis jika belum ada
        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return "images/{$folder}/{$filename}";
    }

    /**
     * Hapus image dari public/
     *
     * @param  string|null  $path  path relatif dari public/, contoh: "images/profiles/abc.jpg"
     */
    protected function deleteImage(?string $path): void
    {
        if (! $path) return;

        $fullPath = public_path($path);
        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }

    /**
     * Upload image + hapus image lama sekaligus
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $folder
     * @param  string|null  $oldPath   path lama yang akan dihapus
     * @return string                  path baru
     */
    protected function replaceImage($file, string $folder, ?string $oldPath = null): string
    {
        $this->deleteImage($oldPath);
        return $this->uploadImage($file, $folder);
    }

    /**
     * Helper: kembalikan full URL dari path
     */
    protected function imageUrl(?string $path): ?string
    {
        return $path ? asset($path) : null;
    }
}
