<?php

namespace common\enums;

/**
 * 发布状态枚举
 *
 * Class PublishedStatusEnum
 * @package common\enums
 */
class PublishedStatusEnum extends BaseEnum
{
    const DISABLED = -1;
    const DRAFT = 1;
    const PUBLISH = 2;
    const GATED1 = 11;

    /**
     * @return array
     */
    public static function getMap(): array
    {
        return [
            self::DRAFT => '草稿',
            self::PUBLISH => '发布',
            self::GATED1 => '灰度',
            self::DISABLED => '停用',
        ];
    }
}