<?hh // strict
/*
 *  Copyright (c) 2017-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace Facebook\DiffLib;

use namespace HH\Lib\{C, Vec};
use function Facebook\FBExpect\expect;

/** Test string-specific functionality */
final class StringDiffTest extends \Facebook\HackTest\HackTest {
  public function testDiffLines(): void {
    $diff = StringDiff::lines("a\nb\nb\n", "a\nb\nc\n")->getDiff();
    expect(C\count($diff))->toBeSame(5);

    expect($diff[0])->toBeInstanceOf(DiffKeepOp::class);
    expect($diff[1])->toBeInstanceOf(DiffKeepOp::class);
    expect($diff[2])->toBeInstanceOf(DiffDeleteOp::class);
    expect($diff[3])->toBeInstanceOf(DiffInsertOp::class);
    expect($diff[4])->toBeInstanceOf(DiffKeepOp::class);

    expect(Vec\map($diff, $op ==> $op->getContent()))->toBeSame(
      vec['a', 'b', 'b', 'c', ''],
    );
  }

  public function testDiffCharacters(): void {
    $diff = StringDiff::characters('abb', 'abc')->getDiff();
    expect(C\count($diff))->toBeSame(4);

    expect($diff[0])->toBeInstanceOf(DiffKeepOp::class);
    expect($diff[1])->toBeInstanceOf(DiffKeepOp::class);
    expect($diff[2])->toBeInstanceOf(DiffDeleteOp::class);
    expect($diff[3])->toBeInstanceOf(DiffInsertOp::class);

    expect(Vec\map($diff, $op ==> $op->getContent()))->toBeSame(
      vec['a', 'b', 'b', 'c'],
    );
  }

  public function provideExamples(): vec<varray<string>> {
    return Vec\map(
      \glob(__DIR__.'/examples/*.a'),
      $ex ==> varray[\basename($ex, '.a')],
    );
  }

  <<DataProvider('provideExamples')>>
  public function testUnifiedDiff(string $name): void {
    $base = __DIR__.'/examples/'.$name;
    $a = \file_get_contents($base.'.a');
    $b = \file_get_contents($base.'.b');
    $diff = StringDiff::lines($a, $b)->getUnifiedDiff();

    expect($diff)->toBeSame(
      \file_get_contents($base.'.udiff.expect'),
      'Did not match expected contents '.
      '(from diff -u %s %s | tail -n +3 > %s.udiff.expect)',
      $base,
      $base,
      $base,
    );
  }
}