<?php

if (!defined('ABSPATH')) {
    exit;
}

class GFBM_Helpers {
    public static function get_days() {
        return [
            'monday'    => __('Monday', 'gravity-forms-booking-manager'),
            'tuesday'   => __('Tuesday', 'gravity-forms-booking-manager'),
            'wednesday' => __('Wednesday', 'gravity-forms-booking-manager'),
            'thursday'  => __('Thursday', 'gravity-forms-booking-manager'),
            'friday'    => __('Friday', 'gravity-forms-booking-manager'),
            'saturday'  => __('Saturday', 'gravity-forms-booking-manager'),
            'sunday'    => __('Sunday', 'gravity-forms-booking-manager'),
        ];
    }

    public static function get_day_abbreviations() {
        return [
            'monday'    => __('Mon', 'gravity-forms-booking-manager'),
            'tuesday'   => __('Tue', 'gravity-forms-booking-manager'),
            'wednesday' => __('Wed', 'gravity-forms-booking-manager'),
            'thursday'  => __('Thu', 'gravity-forms-booking-manager'),
            'friday'    => __('Fri', 'gravity-forms-booking-manager'),
            'saturday'  => __('Sat', 'gravity-forms-booking-manager'),
            'sunday'    => __('Sun', 'gravity-forms-booking-manager'),
        ];
    }

    public static function get_time_options($interval = 30) {
        $options = [];

        for ($minutes = 0; $minutes < 1440; $minutes += $interval) {
            $value = sprintf('%02d:%02d', (int) floor($minutes / 60), $minutes % 60);
            $options[] = [
                'value' => $value,
                'label' => self::format_time_label($value),
            ];
        }

        return $options;
    }

    public static function format_time_label($value) {
        if (!preg_match('/^(\d{2}):(\d{2})$/', $value, $matches)) {
            return $value;
        }

        $hour = (int) $matches[1];
        $minute = $matches[2];
        $suffix = $hour >= 12 ? 'PM' : 'AM';
        $display_hour = $hour % 12;

        if ($display_hour === 0) {
            $display_hour = 12;
        }

        return sprintf('%d:%s %s', $display_hour, $minute, $suffix);
    }

    public static function render_hour_select_options($selected = 'closed', $include_closed = true) {
        $html = '';

        if ($include_closed) {
            $html .= sprintf(
                '<option value="closed"%s>%s</option>',
                selected($selected, 'closed', false),
                esc_html__('Closed', 'gravity-forms-booking-manager')
            );
        }

        foreach (self::get_time_options() as $option) {
            $html .= sprintf(
                '<option value="%s"%s>%s</option>',
                esc_attr($option['value']),
                selected($selected, $option['value'], false),
                esc_html($option['label'])
            );
        }

        return $html;
    }

    public static function render_time_options($selected = '', $include_empty = false) {
        $html = '';

        if ($include_empty) {
            $html .= '<option value="">' . esc_html__('Select', 'gravity-forms-booking-manager') . '</option>';
        }

        foreach (self::get_time_options() as $option) {
            $html .= sprintf(
                '<option value="%s"%s>%s</option>',
                esc_attr($option['value']),
                selected($selected, $option['value'], false),
                esc_html($option['label'])
            );
        }

        return $html;
    }

    public static function get_default_business_hours() {
        $weekday = ['enabled' => true, 'open' => '09:00', 'close' => '17:00'];

        return [
            'monday'    => $weekday,
            'tuesday'   => $weekday,
            'wednesday' => $weekday,
            'thursday'  => $weekday,
            'friday'    => $weekday,
            'saturday'  => ['enabled' => false, 'open' => 'closed', 'close' => 'closed'],
            'sunday'    => ['enabled' => false, 'open' => 'closed', 'close' => 'closed'],
        ];
    }

    public static function normalize_business_hours($hours) {
        $defaults = self::get_default_business_hours();
        $normalized = [];

        if (!is_array($hours)) {
            $hours = GFBM_Helpers::decode_json_property($hours);
        }

        foreach ($defaults as $day => $default) {
            $day_hours = isset($hours[$day]) && is_array($hours[$day]) ? $hours[$day] : [];

            $open  = isset($day_hours['open']) ? (string) $day_hours['open'] : $default['open'];
            $close = isset($day_hours['close']) ? (string) $day_hours['close'] : $default['close'];

            if ($open === 'closed' || $close === 'closed') {
                $normalized[$day] = [
                    'enabled' => false,
                    'open'    => 'closed',
                    'close'   => 'closed',
                ];
                continue;
            }

            $open_norm  = GF_Booking_Manager::normalize_time($open, $default['open']);
            $close_norm = GF_Booking_Manager::normalize_time($close, $default['close']);
            $open_m     = GF_Booking_Manager::time_to_minutes($open_norm);
            $close_m    = GF_Booking_Manager::time_to_minutes($close_norm);
            $enabled    = $open_m !== null && $close_m !== null && $open_m < $close_m;

            $normalized[$day] = [
                'enabled' => $enabled,
                'open'    => $open_norm,
                'close'   => $close_norm,
            ];
        }

        return $normalized;
    }

    public static function is_day_open($settings) {
        if (!is_array($settings)) {
            return false;
        }

        $open  = isset($settings['open']) ? (string) $settings['open'] : '';
        $close = isset($settings['close']) ? (string) $settings['close'] : '';

        if ($open === 'closed' || $close === 'closed' || $open === '' || $close === '') {
            return false;
        }

        $open_m  = GF_Booking_Manager::time_to_minutes($open);
        $close_m = GF_Booking_Manager::time_to_minutes($close);

        return $open_m !== null && $close_m !== null && $open_m < $close_m;
    }

    public static function decode_json_property($raw) {
        if (is_array($raw)) {
            return $raw;
        }

        if (is_object($raw)) {
            return json_decode(wp_json_encode($raw), true) ?: [];
        }

        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    public static function encode_json_property($value) {
        if (is_string($value)) {
            return $value;
        }

        return wp_json_encode($value);
    }

    public static function normalize_blocked_times($raw) {
        if (is_array($raw)) {
            $items = $raw;
        } elseif (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);

            if (is_array($decoded)) {
                $items = $decoded;
            } else {
                return self::parse_legacy_blocked_times($raw);
            }
        } else {
            return [];
        }

        $ranges = [];
        $days = array_keys(self::get_days());

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $day = isset($item['day']) ? strtolower((string) $item['day']) : 'all';

            if ($day !== 'all' && !in_array($day, $days, true)) {
                continue;
            }

            $start = GF_Booking_Manager::normalize_time($item['start'] ?? '');
            $end   = GF_Booking_Manager::normalize_time($item['end'] ?? '');

            if (!$start || !$end) {
                continue;
            }

            $start_minutes = GF_Booking_Manager::time_to_minutes($start);
            $end_minutes   = GF_Booking_Manager::time_to_minutes($end);

            if ($start_minutes === null || $end_minutes === null || $start_minutes >= $end_minutes) {
                continue;
            }

            $ranges[] = [
                'day'   => $day,
                'start' => $start,
                'end'   => $end,
            ];
        }

        return $ranges;
    }

    private static function parse_legacy_blocked_times($raw) {
        $ranges = [];
        $lines = preg_split('/\r\n|\r|\n/', $raw);

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $parts = preg_split('/\s*-\s*/', $line);

            if (count($parts) !== 2) {
                continue;
            }

            $start = GF_Booking_Manager::normalize_time($parts[0]);
            $end   = GF_Booking_Manager::normalize_time($parts[1]);

            if (!$start || !$end) {
                continue;
            }

            $ranges[] = [
                'day'   => 'all',
                'start' => $start,
                'end'   => $end,
            ];
        }

        return $ranges;
    }

    public static function get_blocked_ranges_for_day($field, $day_key) {
        $raw = $field->gfbmBlockedTimes ?? [];
        $all = self::normalize_blocked_times($raw);
        $filtered = [];

        foreach ($all as $range) {
            if ($range['day'] === 'all' || $range['day'] === $day_key) {
                $filtered[] = [
                    'start' => $range['start'],
                    'end'   => $range['end'],
                ];
            }
        }

        return $filtered;
    }

    public static function normalize_blackout_dates($raw) {
        if (is_array($raw)) {
            $items = $raw;
        } elseif (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);

            if (is_array($decoded)) {
                $items = $decoded;
            } else {
                $items = preg_split('/\r\n|\r|\n|,/', $raw);
            }
        } else {
            return [];
        }

        $dates = [];

        foreach ($items as $item) {
            $date = trim((string) $item);

            if ($date === '') {
                continue;
            }

            $timestamp = strtotime($date);

            if ($timestamp) {
                $dates[] = date('Y-m-d', $timestamp);
            }
        }

        return array_values(array_unique($dates));
    }
}
