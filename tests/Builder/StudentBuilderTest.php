<?php

namespace Tests\Builder;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use App\Builder\StudentBuilder;
use DateTime;

#[CoversClass(StudentBuilder::class)]
class StudentBuilderTest extends TestCase
{
    /**
     * Régression : strtoupper() et ucfirst() opèrent octet par octet et
     * laissaient intacts les caractères accentués multi-octets.
     */
    #[DataProvider('identitesProvider')]
    public function testSetIdentiteNormaliseLesCaracteresMultiOctets(
        string $nom,
        string $prenom,
        string $nomAttendu,
        string $prenomAttendu,
    ): void {
        $etudiant = $this->build($nom, $prenom);

        $this->assertSame($nomAttendu, $etudiant->nom);
        $this->assertSame($prenomAttendu, $etudiant->prenom);
    }

    public static function identitesProvider(): array
    {
        return [
            'tréma minuscule dans le nom' => ['Müller', 'Chloé', 'MÜLLER', 'Chloé'],
            'prénom entièrement capitalisé' => ['NUÑEZ', 'JOSÉ', 'NUÑEZ', 'José'],
            'nom en minuscules' => ['dupont', 'zoé', 'DUPONT', 'Zoé'],
            'prénom composé' => ['MARTIN', 'jean-luc', 'MARTIN', 'Jean-Luc'],
            'espaces superflus' => ['  bernard ', ' ANNE ', 'BERNARD', 'Anne'],
        ];
    }

    private function build(string $nom, string $prenom): \App\Model\Student\Normalien
    {
        return (new StudentBuilder())
            ->setInfosPegasus(new DateTime(), 0, 0, 'da', '', 1, 'EOL')
            ->setScolarite(2026, 'ANDENS1', 2026, 'ENS-DENS ETUDIANT')
            ->setIdentite($nom, $prenom, 'Madame', 'F')
            ->setConnaissance([])
            ->buildNormalienStudent([], '', '', new DateTime(), '', '', '', '', '', '', '', '', '');
    }
}
