<?php

namespace CthulhuDen\DockerRegistryV2\Authorization\Challenge;

use CthulhuDen\DockerRegistryV2\Authorization\Challenge;
use CthulhuDen\DockerRegistryV2\Authorization\Exception\InvalidChallengeException;
use vektah\parser_combinator\combinator\Choice;
use vektah\parser_combinator\combinator\Many;
use vektah\parser_combinator\combinator\Sequence;
use vektah\parser_combinator\exception\GrammarException;
use vektah\parser_combinator\formatter\Closure;
use vektah\parser_combinator\formatter\Concatenate;
use vektah\parser_combinator\formatter\Ignore;
use vektah\parser_combinator\parser\EofParser;
use vektah\parser_combinator\parser\RegexParser;
use vektah\parser_combinator\parser\RepSep;

final class ChallengeParser implements ChallengeParserInterface
{
    private $parser;

    public function parse(string $wwwAuthentication): Challenge
    {
        try {
            return $this->getParser()->parseString($wwwAuthentication);
        } catch (\Throwable $e) {
            throw new InvalidChallengeException($e);
        }
    }

    private function getParser()
    {
        if ($this->parser !== null) {
            return $this->parser;
        }

        $intro = new RegexParser('Bearer', 'i', false);
        $space = new Ignore(new Many(' '));

        $propKey = new RegexParser('[a-z0-9]+', 'i');

        $valueUnquoted = new RegexParser('[^ ,"]+');

        $valueQuoted = new Concatenate(new Sequence(
            new Ignore('"'),
            new Many([
                new Closure(preg_quote('\\\\'), function (): string {
                    return '\\';
                }),
                new Closure(preg_quote('\\"'), function (): string {
                    return '"';
                }),
                new RegexParser('[^"\\\\]+'),
                '\\',
            ]),
            new Ignore('"'),
        ));

        $keyVal = new Sequence(
            $propKey,
            new Ignore('='),
            new Choice($valueUnquoted, $valueQuoted),
        );

        return $this->parser = new Closure(
            new Sequence(
                $intro,
                $space,
                new RepSep($keyVal, ',', false),
                new EofParser(),
            ),
            function (array $data): Challenge {
                $endpoint = $service = null;
                $scopes = [];

                foreach ($data[0] as list($key, $value)) {
                    switch ($key) {
                        case 'realm':
                            $endpoint = $value;
                            break;
                        case 'service':
                            $service = $value;
                            break;
                        case 'scope':
                            $scopes[] = $value;
                            break;
                    }
                }

                if ($endpoint === null) {
                    throw new GrammarException('realm must be set');
                }

                if ($service === null) {
                    throw new GrammarException('service must be set');
                }

                return new Challenge($endpoint, $service, ...$scopes);
            },
        );
    }
}
