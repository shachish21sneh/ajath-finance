<?php

namespace App\Helpers;

use App\Models\Company;
use App\Models\FinancialYear;

class AccountingHelper
{
    public static function formatCurrency(float|int|string|null $amount, string $symbol = '₹'): string
    {
        $amt = (float) ($amount ?? 0);
        return $symbol . ' ' . number_format($amt, 2);
    }

    public static function formatDrCr(float|int|string|null $amount, string $symbol = '₹'): string
    {
        $amt = (float) ($amount ?? 0);
        $type = $amt >= 0 ? 'Dr' : 'Cr';
        return $symbol . ' ' . number_format(abs($amt), 2) . ' ' . $type;
    }

    public static function getActiveCompany(): ?Company
    {
        $companyId = session('active_company_id');
        if ($companyId) {
            $company = Company::find($companyId);
            if ($company) {
                return $company;
            }
        }

        $defaultCompany = Company::where('is_active', true)->first();
        if ($defaultCompany) {
            session(['active_company_id' => $defaultCompany->id]);
            return $defaultCompany;
        }

        return null;
    }

    public static function getActiveFinancialYear(): ?FinancialYear
    {
        $fyId = session('active_financial_year_id');
        if ($fyId) {
            $fy = FinancialYear::find($fyId);
            if ($fy) {
                return $fy;
            }
        }

        $company = self::getActiveCompany();
        if ($company) {
            $activeFy = FinancialYear::where('company_id', $company->id)
                ->where('is_active', true)
                ->latest()
                ->first();

            if ($activeFy) {
                session(['active_financial_year_id' => $activeFy->id]);
                return $activeFy;
            }
        }

        return null;
    }

    public static function amountToWords(float|int $number): string
    {
        $decimal = round($number - ($no = floor($number)), 2) * 100;
        $words = [
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
            6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
            11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty',
            30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy',
            80 => 'Eighty', 90 => 'Ninety'
        ];
        $digits = ['', 'Hundred', 'Thousand', 'Lakh', 'Crore'];
        
        $str = [];
        $i = 0;
        while ($no > 0) {
            $divider = ($i === 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += ($divider === 10) ? 1 : 2;
            
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $hundred = ($counter === 1 && $str[0]) ? ' and ' : null;
                $str[] = ($number < 21) ? $words[$number] . ' ' . $digits[$counter] . $plural . ' ' . $hundred
                    : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10] . ' ' . $digits[$counter] . $plural . ' ' . $hundred;
            } else {
                $str[] = null;
            }
        }
        
        $result = implode('', array_reverse($str));
        $points = ($decimal) ? "and " . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' Paise' : '';
        
        $text = trim($result) ? trim($result) . ' Rupees ' . $points : 'Zero Rupees';
        return $text . ' Only';
    }
}
