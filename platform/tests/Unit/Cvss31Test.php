<?php

use App\Enums\Severity;
use App\Support\Cvss\Cvss31;

dataset('reference vectors', [
    // Vectors and scores checked against the FIRST calculator.
    'network RCE' => ['CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H', 9.8, Severity::Critical],
    'scope changed max' => ['CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:C/C:H/I:H/A:H', 10.0, Severity::Critical],
    'reflected XSS' => ['CVSS:3.1/AV:N/AC:L/PR:N/UI:R/S:C/C:L/I:L/A:N', 6.1, Severity::Medium],
    'IDOR' => ['CVSS:3.1/AV:N/AC:L/PR:L/UI:N/S:U/C:H/I:L/A:N', 7.1, Severity::High],
    'SSRF scope changed' => ['CVSS:3.1/AV:N/AC:L/PR:L/UI:N/S:C/C:H/I:N/A:N', 7.7, Severity::High],
    'local low' => ['CVSS:3.1/AV:L/AC:H/PR:H/UI:R/S:U/C:L/I:N/A:N', 1.8, Severity::Low],
    'no impact' => ['CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:N/I:N/A:N', 0.0, Severity::Info],
    'physical' => ['CVSS:3.1/AV:P/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H', 6.8, Severity::Medium],
]);

it('computes the base score and severity', function (string $vector, float $score, Severity $severity) {
    $cvss = Cvss31::parse($vector);

    expect($cvss->baseScore())->toBe($score)
        ->and($cvss->severity())->toBe($severity);
})->with('reference vectors');

it('normalises metric order', function () {
    $cvss = Cvss31::parse('CVSS:3.1/A:H/I:H/C:H/S:U/UI:N/PR:N/AC:L/AV:N');

    expect($cvss->toString())->toBe('CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H');
});

it('rejects malformed vectors', function (string $vector) {
    expect(Cvss31::isValid($vector))->toBeFalse();
})->with([
    'wrong version' => 'CVSS:3.0/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H',
    'missing metric' => 'CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:H',
    'bad value' => 'CVSS:3.1/AV:X/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H',
    'duplicated' => 'CVSS:3.1/AV:N/AV:L/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H',
    'garbage' => 'not-a-vector',
]);

it('rounds up per appendix A', function () {
    expect(Cvss31::roundUp(4.02))->toBe(4.1)
        ->and(Cvss31::roundUp(4.0))->toBe(4.0)
        ->and(Cvss31::roundUp(4.000001))->toBe(4.0);
});
