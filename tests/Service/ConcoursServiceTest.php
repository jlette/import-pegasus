<?php

namespace Tests\Service;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Service\ConcoursService;
use App\Interface\CodeRepositoryInterface;
use App\Interface\SourceDeRepliInterface;
use App\Constant\ConcoursDeSecours;

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

    /**
     * Une source ordinaire, incapable de repli, n'émet aucune réserve : le cas
     * nominal ne doit pas alarmer le gestionnaire.
     */
    public function testAucuneReserveQuandLaSourceNeSaitPasSeReplier(): void
    {
        $service = new ConcoursService($this->createMock(CodeRepositoryInterface::class));

        $this->assertSame([], $service->avertissements());
    }

    /**
     * Une source capable de repli qui n'en a pas eu besoin n'émet rien non plus.
     */
    public function testAucuneReserveTantQueLAnnuaireARepondu(): void
    {
        $service = new ConcoursService($this->source(repliActive: false));

        $this->assertSame([], $service->avertissements());
    }

    /**
     * Après un repli, le gestionnaire doit apprendre trois choses : que
     * l'annuaire n'a pas répondu, de quand date la table qui l'a remplacé, et
     * ce qu'on attend de lui avant d'importer dans PEGASUS.
     */
    public function testLeRepliProduitUneReserveDateeEtActionnable(): void
    {
        $service = new ConcoursService($this->source(repliActive: true, code: 'ORA-28000'));

        $reserves = $service->avertissements();

        $this->assertCount(1, $reserves);
        $this->assertStringContainsString('injoignable', $reserves[0]->message);
        $this->assertStringContainsString('secours', $reserves[0]->message);
        $this->assertStringContainsString(
            date_create_immutable(ConcoursDeSecours::RELEVE_LE)->format('d/m/Y'),
            $reserves[0]->message
        );
        $this->assertStringContainsString('Vérifiez', $reserves[0]->message);
    }

    /**
     * Le code Oracle est réservé au journal : affiché au gestionnaire, il
     * l'inquiéterait sans rien lui apprendre d'actionnable.
     */
    public function testLeCodeOracleResteHorsDuMessageAffiche(): void
    {
        $service = new ConcoursService($this->source(repliActive: true, code: 'ORA-28000'));

        $reserve = $service->avertissements()[0];

        $this->assertSame('ORA-28000', $reserve->code);
        $this->assertStringNotContainsString('ORA-', $reserve->message);
    }

    private function source(bool $repliActive, string $code = ''): CodeRepositoryInterface
    {
        return new class($repliActive, $code) implements CodeRepositoryInterface, SourceDeRepliInterface {
            public function __construct(private bool $repliActive, private string $code) {}

            public function findByPlatforme(string $platforme): array
            {
                return [];
            }

            public function repliActive(): bool
            {
                return $this->repliActive;
            }

            public function codeTechnique(): string
            {
                return $this->code;
            }
        };
    }
}