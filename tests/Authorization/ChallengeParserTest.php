<?php

namespace CthulhuDen\DockerRegistryV2\Tests\Authorization;

use CthulhuDen\DockerRegistryV2\Authorization\Challenge;
use CthulhuDen\DockerRegistryV2\Authorization\ChallengeParser;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use PHPUnit\Framework\TestCase;

class ChallengeParserTest extends TestCase
{
    use ArraySubsetAsserts;

    private $parser;

    protected function setUp(): void
    {
        $this->parser = new ChallengeParser();
    }

    public function testParse(): void
    {
        $challenge = $this->parser->parse('BeaRer  realm="https://example.com/token",service=registry,scope="some, :god:\damn:\"scope"');
        $this->addToAssertionCount(1); // No exception - already something

        $this->assertInstanceOf(Challenge::class, $challenge);
        $this->assertSame('https://example.com/token', $challenge->getEndpoint());
        $this->assertArraySubset([
            'service' => 'registry',
            'scope' => 'some, :god:\damn:"scope',
        ], $challenge->getParameters(), true);
    }
}
