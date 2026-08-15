<?php

namespace Tests\Service;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Service\FileUploadService;
use Exception;

#[CoversClass(FileUploadService::class)]
class FileUploadServiceTest extends TestCase
{
    private FileUploadService $service;

    protected function setUp(): void
    {
        $this->service = new FileUploadService();
    }

    public function testUploadThrowsExceptionOnTransferError(): void
    {
        $fakeFile = [
            'name' => 'test.xlsx',
            'type' => 'application/vnd.ms-excel',
            'tmp_name' => '/tmp/phpYzd',
            'error' => UPLOAD_ERR_INI_SIZE, // Erreur simulée (fichier trop lourd)
            'size' => 123
        ];

        try {
            $this->service->uploadExcelFile($fakeFile);
            $this->fail("Une Exception aurait dû être levée pour erreur de transfert.");
        } catch (Exception $e) {
            $this->assertSame('Erreur lors du transfert du fichier.', $e->getMessage());
            $this->assertSame(400, $e->getCode());
        }
    }

    public function testUploadThrowsExceptionOnInvalidMimeType(): void
    {
        // On crée un faux fichier texte sur le système pour tromper mime_content_type()
        $tmpFilePath = sys_get_temp_dir() . '/fake_upload.txt';
        file_put_contents($tmpFilePath, 'Ceci est un virus déguisé en Excel');

        $fakeFile = [
            'name' => 'virus.xlsx',
            'type' => 'application/vnd.ms-excel', // Le hacker ment sur l'extension
            'tmp_name' => $tmpFilePath, // Mais mime_content_type va lire le vrai contenu ici
            'error' => UPLOAD_ERR_OK,
            'size' => 123
        ];

        try {
            $this->service->uploadExcelFile($fakeFile);
            $this->fail("Une Exception aurait dû être levée pour type MIME invalide.");
        } catch (Exception $e) {
            $this->assertSame('Format non autorisé. Seuls les fichiers Excel sont acceptés.', $e->getMessage());
            $this->assertSame(415, $e->getCode());
        } finally {
            // Nettoyage du faux fichier
            unlink($tmpFilePath);
        }
    }
}