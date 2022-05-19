<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers;

use App\Services\Helpers\Phone;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class PhoneTest extends TestCase
{
    /**
     * @dataProvider phoneTexts
     */
    public function testRawPhonesFromText(string $text, array $phones): void
    {
        Assert::assertSame($phones, Phone::getAllRawFromText($text));
    }

    /**
     * @dataProvider phoneTexts
     */
    public function testNormalizedPhonesFromText(string $text, array $phones, array $normalizedPhones): void
    {
        Assert::assertSame($normalizedPhones, Phone::getAllNormalizedFromText($text));
    }

    /**
     * @dataProvider phoneTexts
     */
    public function testFirstFromText(string $text, array $phones, array $normalizedPhones): void
    {
        Assert::assertSame($normalizedPhones[0], Phone::getFirstFromText($text));
    }

    public function phoneTexts(): array
    {
        return [
            // multiple numbers of various format
            [
                'Some phones +48 777 666 777 and 375 (29) 111 11 11 and +390-50-896 78 456',
                [
                    '+48 777 666 777',
                    '375 (29) 111 11 11',
                    '+390-50-896 78 456',
                ],
                [
                    '48777666777',
                    '375291111111',
                    '3905089678456',
                ],
            ],
            // single number
            [
                '+48 777 666 777',
                [
                    '+48 777 666 777',
                ],
                [
                    '48777666777',
                ],
            ],
            // comma separated numbers with spaces and without
            [
                '+48 777 666 777, +48 777 666 777,+48 777 666 777',
                [
                    '+48 777 666 777',
                    '+48 777 666 777',
                    '+48 777 666 777',
                ],
                [
                    '48777666777',
                ],
            ],
            // normalized number
            [
                '48777666777',
                [
                    '48777666777',
                ],
                [
                    '48777666777',
                ],
            ],
            // numbers without divider and with spaces at the beginning and the end
            [
                ' +390 (50) 234 234 234+390 (50) 234 234 234 ',
                [
                    '+390 (50) 234 234 234',
                    '+390 (50) 234 234 234',
                ],
                [
                    '39050234234234',
                ],
            ],
            // very long number
            [
                '+390 (50) 234 234 234 234 234 234',
                [
                    '+390 (50) 234 234 234 234 23',
                ],
                [
                    '3905023423423423423',
                ],
            ],
        ];
    }
}
