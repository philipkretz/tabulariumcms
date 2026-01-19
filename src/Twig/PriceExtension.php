<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PriceExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('price', [$this, 'formatPrice']),
            new TwigFilter('price_with_tax', [$this, 'formatPriceWithTax'], ['is_safe' => ['html']]),
        ];
    }

    public function formatPrice(float|string|null $amount, string $locale = 'en'): string
    {
        if ($amount === null) {
            return '0,00 €';
        }

        $amount = (float) $amount;

        // Format based on locale
        if ($locale === 'de') {
            // German: comma as decimal separator
            return number_format($amount, 2, ',', '.') . ' €';
        } else {
            // Other languages: dot as decimal separator
            return number_format($amount, 2, '.', ',') . ' €';
        }
    }

    /**
     * Format price with tax breakdown (GDPR compliant)
     * Shows gross price, net price, and VAT amount
     */
    public function formatPriceWithTax(float|string|null $grossPrice, float $taxRate = 19.0, string $locale = 'en', bool $detailed = true): string
    {
        if ($grossPrice === null) {
            $grossPrice = 0.0;
        }

        $grossPrice = (float) $grossPrice;
        $netPrice = $grossPrice / (1 + ($taxRate / 100));
        $vatAmount = $grossPrice - $netPrice;

        $grossFormatted = $this->formatPrice($grossPrice, $locale);
        $netFormatted = $this->formatPrice($netPrice, $locale);
        $vatFormatted = $this->formatPrice($vatAmount, $locale);

        if ($detailed) {
            return sprintf(
                '<div class="price-with-tax">' .
                '<div class="price-gross" style="font-size: 1.5em; font-weight: bold; color: #16a34a;">%s</div>' .
                '<div class="price-tax-info" style="font-size: 0.875em; color: #6b7280; margin-top: 0.25rem;">' .
                'Includes %s VAT (%.1f%%) | Net: %s' .
                '</div>' .
                '</div>',
                $grossFormatted,
                $vatFormatted,
                $taxRate,
                $netFormatted
            );
        } else {
            return sprintf(
                '<span class="price-gross">%s</span> <span class="price-tax-note" style="font-size: 0.75em; color: #6b7280;">(incl. %.1f%% VAT)</span>',
                $grossFormatted,
                $taxRate
            );
        }
    }
}
