<?php

namespace App\Enums;

class LevelExperience
{
    const BEGINNER = 'beginner';
    const INTERMEDIATE = 'intermediate';
    const ADVANCED = 'advanced';
    const EXPERT = 'expert';

    public static function getLevels(): array
    {
        return [
            self::BEGINNER,
            self::INTERMEDIATE,
            self::ADVANCED,
            self::EXPERT,
        ];
    }
}
