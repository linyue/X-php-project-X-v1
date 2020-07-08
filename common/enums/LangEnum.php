<?php

namespace common\enums;

/**
 * 语言枚举
 *
 * Class LangEnum
 * @package common\enums
 */
class LangEnum extends BaseEnum
{
    const CN = 'zh';
    const TW = 'zh-tw';

    /**
     * @return array
     */
    public static function getMap(): array
    {
        return [
            self::CN => '简体',
            self::TW => '繁体',
        ];
    }
}