<?php

use Carbon\Carbon;

Carbon::setLocale('id'); // Set locale to Indonesian

if (! function_exists('format_date')) {
    /**
     * Format date to a given format with Indonesian day and month names
     *
     * @param  string  $date
     * @param  string  $format
     * @return string
     */
    function format_date($date, $format = 'd-m-Y H:i:s')
    {
        return Carbon::parse($date)->translatedFormat($format);
    }
}

if (! function_exists('human_readable_date')) {
    /**
     * Format date to a human-readable format in Indonesian
     *
     * @param  string  $date
     * @return string
     */
    function human_readable_date($date)
    {
        return Carbon::parse($date)->diffForHumans();
    }
}

if (! function_exists('current_date')) {
    /**
     * Get current date in a given format with Indonesian day and month names
     *
     * @param  string  $format
     * @return string
     */
    function current_date($format = 'd-m-Y H:i:s')
    {
        return Carbon::now()->translatedFormat($format);
    }
}

if (! function_exists('add_days_to_date')) {
    /**
     * Add days to a given date with Indonesian day and month names
     *
     * @param  string  $date
     * @param  int  $days
     * @param  string  $format
     * @return string
     */
    function add_days_to_date($date, $days, $format = 'd-m-Y H:i:s')
    {
        return Carbon::parse($date)->addDays($days)->translatedFormat($format);
    }
}

if (! function_exists('subtract_days_from_date')) {
    /**
     * Subtract days from a given date with Indonesian day and month names
     *
     * @param  string  $date
     * @param  int  $days
     * @param  string  $format
     * @return string
     */
    function subtract_days_from_date($date, $days, $format = 'd-m-Y H:i:s')
    {
        return Carbon::parse($date)->subDays($days)->translatedFormat($format);
    }
}

if (! function_exists('is_weekend')) {
    /**
     * Check if a given date is a weekend
     *
     * @param  string  $date
     * @return bool
     */
    function is_weekend($date)
    {
        $carbonDate = Carbon::parse($date);

        return $carbonDate->isSaturday() || $carbonDate->isSunday();
    }
}

if (! function_exists('start_of_day')) {
    /**
     * Get the start of the day for a given date with Indonesian day and month names
     *
     * @param  string  $date
     * @param  string  $format
     * @return string
     */
    function start_of_day($date, $format = 'd-m-Y H:i:s')
    {
        return Carbon::parse($date)->startOfDay()->translatedFormat($format);
    }
}

if (! function_exists('end_of_day')) {
    /**
     * Get the end of the day for a given date with Indonesian day and month names
     *
     * @param  string  $date
     * @param  string  $format
     * @return string
     */
    function end_of_day($date, $format = 'd-m-Y H:i:s')
    {
        return Carbon::parse($date)->endOfDay()->translatedFormat($format);
    }
}

if (! function_exists('start_of_month')) {
    /**
     * Get the start of the month for a given date with Indonesian day and month names
     *
     * @param  string  $date
     * @param  string  $format
     * @return string
     */
    function start_of_month($date, $format = 'd-m-Y H:i:s')
    {
        return Carbon::parse($date)->startOfMonth()->translatedFormat($format);
    }
}

if (! function_exists('end_of_month')) {
    /**
     * Get the end of the month for a given date with Indonesian day and month names
     *
     * @param  string  $date
     * @param  string  $format
     * @return string
     */
    function end_of_month($date, $format = 'd-m-Y H:i:s')
    {
        return Carbon::parse($date)->endOfMonth()->translatedFormat($format);
    }
}

if (! function_exists('start_of_year')) {
    /**
     * Get the start of the year for a given date with Indonesian day and month names
     *
     * @param  string  $date
     * @param  string  $format
     * @return string
     */
    function start_of_year($date, $format = 'd-m-Y H:i:s')
    {
        return Carbon::parse($date)->startOfYear()->translatedFormat($format);
    }
}

if (! function_exists('end_of_year')) {
    /**
     * Get the end of the year for a given date with Indonesian day and month names
     *
     * @param  string  $date
     * @param  string  $format
     * @return string
     */
    function end_of_year($date, $format = 'd-m-Y H:i:s')
    {
        return Carbon::parse($date)->endOfYear()->translatedFormat($format);
    }
}

if (! function_exists('days_diff')) {
    /**
     * Get the difference in days between two dates
     *
     * @param  string  $startDate
     * @param  string  $endDate
     * @return int
     */
    function days_diff($startDate, $endDate)
    {
        return Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate));
    }
}

if (! function_exists('months_diff')) {
    /**
     * Get the difference in months between two dates
     *
     * @param  string  $startDate
     * @param  string  $endDate
     * @return int
     */
    function months_diff($startDate, $endDate)
    {
        return Carbon::parse($startDate)->diffInMonths(Carbon::parse($endDate));
    }
}

if (! function_exists('years_diff')) {
    /**
     * Get the difference in years between two dates
     *
     * @param  string  $startDate
     * @param  string  $endDate
     * @return int
     */
    function years_diff($startDate, $endDate)
    {
        return Carbon::parse($startDate)->diffInYears(Carbon::parse($endDate));
    }
}

if (! function_exists('format_rupiah')) {
    /**
     * Format number to Indonesian Rupiah currency format
     *
     * @param  float  $amount
     * @return string
     */
    function format_rupiah($amount)
    {
        return 'Rp '.number_format($amount, 2, ',', '.');
    }
}
