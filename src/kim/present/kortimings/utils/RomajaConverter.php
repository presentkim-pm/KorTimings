<?php

/**
 *  ____                           _   _  ___
 * |  _ \ _ __ ___  ___  ___ _ __ | |_| |/ (_)_ __ ___
 * | |_) | '__/ _ \/ __|/ _ \ '_ \| __| ' /| | '_ ` _ \
 * |  __/| | |  __/\__ \  __/ | | | |_| . \| | | | | | |
 * |_|   |_|  \___||___/\___|_| |_|\__|_|\_\_|_| |_| |_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author  PresentKim (debe3721@gmail.com)
 * @link    https://github.com/PresentKim
 * @license https://www.gnu.org/licenses/lgpl-3.0 LGPL-3.0 License
 *
 *   (\ /)
 *  ( . .) ♥
 *  c(")(")
 *
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection SpellCheckingInspection
 */

declare(strict_types=1);

namespace kim\present\kortimings\utils;

use function array_search;
use function count;
use function hexdec;
use function implode;
use function json_encode;
use function preg_match;
use function preg_split;
use function str_replace;
use function substr;

final class RomajaConverter{
    private const TYPE_DEFAULT = 0;
    private const TYPE_CHO = 1;
    private const TYPE_JUNG = 2;
    private const TYPE_JONG = 3;

    /** 초중종 로마자 변환표 */
    private const CHO_TABLE = [
        "ㄱ" => "g",
        "ㄲ" => "kk",
        "ㄴ" => "n",
        "ㄷ" => "d",
        "ㄸ" => "tt",
        "ㄹ" => "r",
        "ㅁ" => "m",
        "ㅂ" => "b",
        "ㅃ" => "pp",
        "ㅅ" => "s",
        "ㅆ" => "ss",
        "ㅇ" => "",
        "ㅈ" => "j",
        "ㅉ" => "jj",
        "ㅊ" => "ch",
        "ㅋ" => "k",
        "ㅌ" => "t",
        "ㅍ" => "p",
        "ㅎ" => "h"
    ];
    private const JUNG_TABLE = [
        "ㅏ" => "a",
        "ㅐ" => "ae",
        "ㅑ" => "ya",
        "ㅒ" => "yae",
        "ㅓ" => "eo",
        "ㅔ" => "e",
        "ㅕ" => "yeo",
        "ㅖ" => "ye",
        "ㅗ" => "o",
        "ㅘ" => "wa",
        "ㅙ" => "wae",
        "ㅚ" => "oe",
        "ㅛ" => "yo",
        "ㅜ" => "u",
        "ㅝ" => "wo",
        "ㅞ" => "we",
        "ㅟ" => "wi",
        "ㅠ" => "yu",
        "ㅡ" => "eu",
        "ㅢ" => "ui",
        "ㅣ" => "i"
    ];
    private const JONG_TABLE = [
        "ㄱ" => "k",
        "ㄲ" => "k",
        "ㄳ" => "k",
        "ㄴ" => "n",
        "ㄵ" => "n",
        "ㄶ" => "n",
        "ㄷ" => "t",
        "ㄹ" => "l",
        "ㄺ" => "k",
        "ㄻ" => "m",
        "ㄼ" => "p",
        "ㄽ" => "t",
        "ㄾ" => "t",
        "ㄿ" => "p",
        "ㅀ" => "l",
        "ㅁ" => "m",
        "ㅂ" => "p",
        "ㅄ" => "p",
        "ㅅ" => "t",
        "ㅆ" => "t",
        "ㅇ" => "ng",
        "ㅈ" => "t",
        "ㅊ" => "t",
        "ㅋ" => "k",
        "ㅌ" => "t",
        "ㅍ" => "p",
        "ㅎ" => ""
    ];

    /** 초종성 목록 */
    private const CHO_MAP = [
        "ㄱ",
        "ㄲ",
        "ㄴ",
        "ㄷ",
        "ㄸ",
        "ㄹ",
        "ㅁ",
        "ㅂ",
        "ㅃ",
        "ㅅ",
        "ㅆ",
        "ㅇ",
        "ㅈ",
        "ㅉ",
        "ㅊ",
        "ㅋ",
        "ㅌ",
        "ㅍ",
        "ㅎ"
    ];
    private const JUNG_MAP = [
        "ㅏ",
        "ㅐ",
        "ㅑ",
        "ㅒ",
        "ㅓ",
        "ㅔ",
        "ㅕ",
        "ㅖ",
        "ㅗ",
        "ㅘ",
        "ㅙ",
        "ㅚ",
        "ㅛ",
        "ㅜ",
        "ㅝ",
        "ㅞ",
        "ㅟ",
        "ㅠ",
        "ㅡ",
        "ㅢ",
        "ㅣ",
    ];
    private const JONG_MAP = [
        "",
        "ㄱ",
        "ㄲ",
        "ㄳ",
        "ㄴ",
        "ㄵ",
        "ㄶ",
        "ㄷ",
        "ㄹ",
        "ㄺ",
        "ㄻ",
        "ㄼ",
        "ㄽ",
        "ㄾ",
        "ㄿ",
        "ㅀ",
        "ㅁ",
        "ㅂ",
        "ㅄ",
        "ㅅ",
        "ㅆ",
        "ㅇ",
        "ㅈ",
        "ㅊ",
        "ㅋ",
        "ㅌ",
        "ㅍ",
        "ㅎ"
    ];

    /** 자음 동화 목록 */
    private const TRANSFORM_MAP = [
        "ㄱㄴ" => "ㅇㄴ",
        "ㄱㄹ" => "ㅇㄴ",
        "ㄱㅁ" => "ㅇㅁ",
        "ㄱㅇ" => "ㄱ",
        "ㄲㄴ" => "ㅇㄴ",
        "ㄲㄹ" => "ㅇㄴ",
        "ㄲㅁ" => "ㅇㅁ",
        "ㄲㅇ" => "ㄲ",
        "ㄳㅇ" => "ㄱㅅ",
        'ㄴㄱ' => 'ㅇㄱ',
        'ㄴㅁ' => 'ㅁㅁ',
        'ㄴㅂ' => 'ㅁㅂ',
        'ㄴㅍ' => 'ㅁㅍ',
        "ㄴㄹ" => "ㄹㄹ",
        "ㄴㅋ" => "ㅇㅋ",
        "ㄵㄱ" => "ㄴㄲ",
        "ㄵㄷ" => "ㄴㄸ",
        "ㄵㄹ" => "ㄹㄹ",
        "ㄵㅂ" => "ㄴㅃ",
        "ㄵㅅ" => "ㄴㅆ",
        "ㄵㅇ" => "ㄴㅈ",
        "ㄵㅈ" => "ㄴㅉ",
        "ㄵㅋ" => "ㅇㅋ",
        "ㄵㅎ" => "ㄴㅊ",
        "ㄶㄱ" => "ㄴㅋ",
        "ㄶㄷ" => "ㄴㅌ",
        "ㄶㄹ" => "ㄹㄹ",
        "ㄶㅂ" => "ㄴㅍ",
        "ㄶㅈ" => "ㄴㅊ",
        "ㄷㄴ" => "ㄴㄴ",
        "ㄷㄹ" => "ㄴㄴ",
        "ㄷㅁ" => "ㅁㅁ",
        "ㄷㅂ" => "ㅂㅂ",
        "ㄷㅇ" => " ㄷ",
        "ㄹㄴ" => "ㄹㄹ",
        "ㄹㅇ" => "ㄹ",
        "ㄺㄴ" => "ㄹㄹ",
        "ㄺㅇ" => "ㄹㄱ",
        "ㄻㄴ" => "ㅁㄴ",
        "ㄻㅇ" => "ㄹㅁ",
        "ㄼㄴ" => "ㅁㄴ",
        "ㄼㅇ" => "ㄹㅂ",
        "ㄽㄴ" => "ㄴㄴ",
        "ㄽㅇ" => "ㄹㅅ",
        "ㄾㄴ" => "ㄷㄴ",
        "ㄾㅇ" => "ㄹㅌ",
        "ㄿㄴ" => "ㅁㄴ",
        "ㄿㅇ" => "ㄹㅍ",
        "ㅀㄴ" => "ㄴㄴ",
        "ㅀㅇ" => "ㄹ",
        "ㅁㄹ" => "ㅁㄴ",
        "ㅂㄴ" => "ㅁㄴ",
        "ㅂㄹ" => "ㅁㄴ",
        "ㅂㅁ" => "ㅁㅁ",
        "ㅂㅇ" => "ㅂ",
        "ㅄㄴ" => "ㅁㄴ",
        "ㅄㄹ" => "ㅁㄴ",
        "ㅄㅁ" => "ㅁㅁ",
        "ㅄㅇ" => "ㅂㅅ",
        "ㅅㄴ" => "ㄴㄴ",
        "ㅅㄹ" => "ㄴㄴ",
        "ㅅㅁ" => "ㅁㅁ",
        "ㅅㅂ" => "ㅂㅂ",
        "ㅅㅇ" => "ㅅ",
        "ㅆㄴ" => "ㄴㄴ",
        "ㅆㄹ" => "ㄴㄴ",
        "ㅆㅁ" => "ㅁㅁ",
        "ㅆㅂ" => "ㅂㅂ",
        "ㅆㅇ" => "ㅆ",
        "ㅇㄹ" => "ㅇㄴ",
        "ㅈㅇ" => "ㅈ",
        "ㅊㅇ" => "ㅊ",
        "ㅋㅇ" => "ㅋ",
        "ㅌㅇ" => "ㅌ",
        "ㅍㅇ" => "ㅍ"
    ];

    public static function convert(string $str) : string{
        //문자열을 한글자씩 분리
        $chars = preg_split("//u", $str);

        //글자를 초-중-성으로 분리
        $parts = [];
        foreach($chars as $char){
            if($char === ""){
                continue;
            }elseif(preg_match("/[가-힣]/u", $char)){
                $char = hexdec(substr(json_encode($char), 3, 4)) - 44032;

                $cho = self::CHO_MAP[$char / 28 / 21] ?? "";
                if(!empty($cho)){
                    $parts[] = [self::TYPE_CHO, $cho];
                }

                $jung = self::JUNG_MAP[(int) $char / 28 % 21] ?? "";
                if(!empty($jung)){
                    $parts[] = [self::TYPE_JUNG, $jung];
                }

                $jong = self::JONG_MAP[(int) $char % 28] ?? "";
                if(!empty($jong)){
                    $parts[] = [self::TYPE_JONG, $jong];
                }
            }else{
                $parts[] = [self::TYPE_DEFAULT, $char];
            }
        }

        //각 문자를 처리
        $count = count($parts);
        $converted = [];
        for($i = 0; $i < $count; $i++){
            [$type, $part] = $parts[$i];

            switch($type){
                case self::TYPE_CHO:
                    $converted[] = self::CHO_TABLE[$part];
                    break;

                case self::TYPE_JUNG:
                    $converted[] = self::JUNG_TABLE[$part];
                    break;

                case self::TYPE_JONG:
                    if(isset($parts[$i + 1])){
                        [$nextType, $nextPart] = $parts[$i + 1];
                        if($nextType === self::TYPE_CHO){
                            $key = $part . $nextPart;
                            if(isset(self::TRANSFORM_MAP[$key])){
                                $transform = self::TRANSFORM_MAP[$key];
                                if(mb_strlen($transform) <= 1){
                                    $parts[$i + 1][1] = self::CHO_MAP[array_search($transform[0], self::CHO_MAP)];
                                    break;
                                }else{
                                    $part = self::JONG_MAP[array_search($transform[0], self::JONG_MAP)];
                                    $parts[$i + 1][1] = self::CHO_MAP[array_search($transform[1], self::CHO_MAP)];
                                }
                            }
                        }
                    }
                    if(!empty($part)){
                        $converted[] = self::JONG_TABLE[$part];
                    }
                    break;

                default:
                    $converted[] = $part;
            }
        }

        //반복되는 글자를 제거한 뒤 결과를 반환
        return str_replace(["kkk", "ttt", "ppp"], ["kk", "tt", "pp"], implode("", $converted));
    }
}