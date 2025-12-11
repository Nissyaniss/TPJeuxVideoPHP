<?php

namespace App\Enum;

enum Collection: int
{
    case POSSEDE = 1;
    case SOUHAITE = 2;
    case EN_COURS = 3;
    case TERMINE = 4;
    case ABANDONNE = 5;
    case PRETE = 6;
    case VENDU = 7;
    case PLATINE = 8;

    public function getLabel(): string
    {
        return match ($this) {
            self::POSSEDE => 'Possédé',
            self::SOUHAITE => 'Souhaité',
            self::EN_COURS => 'En cours',
            self::TERMINE => 'Terminé',
            self::ABANDONNE => 'Abandonné',
            self::PRETE => 'Prêté',
            self::VENDU => 'Vendu',
            self::PLATINE => 'Platine',
        };
    }
}
