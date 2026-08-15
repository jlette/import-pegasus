<?php

namespace Tests\Service;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Service\ConcoursService;
use App\Interface\CodeRepositoryInterface;

#[CoversClass(ConcoursService::class)]
class ConcoursServiceTest extends TestCase
{
    public function testFindByPlatformeReturnsRepositoryData(): void
    {
        // 1. ARRANGE : On mock le repository
        $repositoryMock = $this->createMock(CodeRepositoryInterface::class);
        
        // On lui dit : "Quand on t'appelle avec 'SCEI', tu dois retourner ce faux tableau"
        $fakeData = [['ANNUAIRE_CONC_CODE' => 'MP', 'CONC_CODE' => 'C-MP']];
        $repositoryMock->expects($this->once())
                       ->method('findByPlatforme')
                       ->with('SCEI')
                       ->willReturn($fakeData);

        // On injecte le faux repository dans le vrai service
        $service = new ConcoursService($repositoryMock);

        // 2. ACT
        $result = $service->findByPlatforme('SCEI');

        // 3. ASSERT
        $this->assertSame($fakeData, $result);
    }
}