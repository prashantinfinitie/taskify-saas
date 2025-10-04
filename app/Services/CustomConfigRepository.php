<?php

namespace App\Services;

use Alexusmai\LaravelFileManager\Services\ConfigService\ConfigRepository;
use Illuminate\Support\Facades\Config;

class CustomConfigRepository implements ConfigRepository
{
    private function getAdminId()
    {
        return getAdminIdByUserRole();
    }

    private function getDynamicDiskName()
    {
        $adminId = $this->getAdminId();
        return $adminId ? "admin-{$adminId}-disk" : 'file-manager';
    }

    public function getDiskList(): array
    {
        $this->createDynamicDisk();
        return [$this->getDynamicDiskName()];
    }

    public function getLeftDisk(): ?string
    {
        return $this->getDynamicDiskName();
    }

    public function getRightDisk(): ?string
    {
        return null;
    }

    public function getLeftPath(): ?string
    {
        return null; // Start at root of the admin's disk
    }

    public function getRightPath(): ?string
    {
        return null;
    }

    public function getWindowsConfig(): int
    {
        return 2;
    }

    public function getMaxUploadFileSize(): ?int
    {
        return null;
    }

    public function getAllowFileTypes(): array
    {
        return [];
    }

    public function getHiddenFiles(): bool
    {
        return false;
    }

    public function getMiddleware(): array
    {
        return ['web', 'auth'];
    }

    public function getAcl(): bool
    {
        return false; // Disable ACL since we're using disk-level isolation
    }

    public function getAclHideFromFM(): bool
    {
        return false;
    }

    public function getAclStrategy(): string
    {
        return 'blacklist';
    }

    public function getAclRepository(): string
    {
        return \Alexusmai\LaravelFileManager\Services\ACLService\ConfigACLRepository::class;
    }

    public function getAclRulesCache(): ?int
    {
        return null;
    }

    public function getSlugifyNames(): bool
    {
        return false;
    }

    public function getRoutePrefix(): string
    {
        return 'file-manager';
    }

    public function getCache(): ?int
    {
        return 60;
    }

    /**
     * Dynamically create a disk configuration for the current admin
     */
    private function createDynamicDisk()
    {
        $adminId = $this->getAdminId();
        // dd($adminId);
        if (!$adminId) {
            // \Log::error('No admin ID found for file manager');
            return;
        }

        $diskName = $this->getDynamicDiskName();
        $adminPath = "admin-{$adminId}";
        $fullPath = storage_path("app/public/file-manager/{$adminPath}");

        // Create the directory if it doesn't exist
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0775, true);
        }

        // Dynamically register the disk configuration
        Config::set("filesystems.disks.{$diskName}", [
            'driver' => 'local',
            'root' => $fullPath,
            'url' => env('APP_URL') . "/storage/file-manager/{$adminPath}",
            'visibility' => 'public',
        ]);

        // \Log::info("Created dynamic disk: {$diskName} at {$fullPath}");
    }
}