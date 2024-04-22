<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class NotificationExtension
 *
 * @package App\Twig
 * @author Egidio Langellotti
 * @version 1.0
 *
 */
class NotificationExtension extends AbstractExtension
{

    public function getFilters(): array
    {
        return [
            new TwigFilter('colorGravity', [$this, 'colorGravity']),
            new TwigFilter('iconGravity', [$this, 'iconGravity'])
        ];
    }

    /**
     *  Return the color class based on the gravity
     *
     * @param int $gravity
     * @return string
     */
    public function colorGravity(int $gravity): string
    {
        return match ($gravity) {
            1 => 'bg-success',
            2 => 'bg-info',
            3 => 'bg-warning',
            4 => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    /**
     *  Return the icon class based on the gravity
     *
     * @param int $gravity
     * @return string
     */
    public function iconGravity(int $gravity): string
    {
        return match ($gravity) {
            1 => 'fa-check-circle',
            2 => 'fa-info-circle',
            3 => 'fa-exclamation-triangle',
            4 => 'fa-exclamation-circle',
            default => 'fa-question-circle',
        };
    }

}
