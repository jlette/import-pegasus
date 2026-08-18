<?php

namespace Tests\Service;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Service\CsvExportService;
use App\Canevas\CanevasProfile;

#[CoversClass(CsvExportService::class)]
class CsvExportServiceTest extends TestCase
{
    public function testGenerateCsvReturnsEmptyStringWhenStudentArrayIsEmpty(): void
    {
        $service = new CsvExportService();
        
        // 2. ACT : On passe un tableau vide
        $result = $service->generateCsv([], '/tmp', 'prefix_test', CanevasProfile::normalien());

        // 3. ASSERT : Le service doit s'arrêter et renvoyer une chaîne vide
        $this->assertSame('', $result);
    }
}