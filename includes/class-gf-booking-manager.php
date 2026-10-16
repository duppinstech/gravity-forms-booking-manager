<?php

if (!defined('ABSPATH')) {
    exit;
}

class GF_Booking_Manager {
    private static $instance = null;

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'register_assets']);
        add_action('admin_enqueue_scripts', [$this, 'register_assets']);

        add_action('wp_ajax_gfbm_get_slots', [$this, 'ajax_get_slots']);
        add_action('wp_ajax_nopriv_gfbm_get_slots', [$this, 'ajax_get_slots']);

        add_filter('gform_validation', [$this, 'validate_booking']);
        add_filter('gform_validation', [$this, 'release_locks_if_invalid'], 999);
        add_action('gform_entry_created', [$this, 'release_slot_locks'], 10, 2);
        add_filter('gform_pre_render', [$this, 'ensure_field_inputs']);
        add_filter('gform_pre_validation', [$this, 'ensure_field_inputs']);
        add_filter('gform_admin_pre_render', [$this, 'ensure_field_inputs']);
        add_filter('gform_form_post_get_meta', [$this, 'normalize_field_settings']);
        add_filter('gform_pre_save_form', [$this, 'preserve_booking_field_settings'], 10, 1);
        add_action('gform_enqueue_scripts', [$this, 'maybe_enqueue_form_assets'], 10, 2);
    }

    public function normalize_field_settings($form) {
        if (empty($form['fields']) || !is_array($form['fields'])) {
            return $form;
        }

        foreach ($form['fields'] as &$field) {
            if (!is_object($field) || $field->type !== 'booking_manager') {
                continue;
            }

            if (method_exists($field, 'sanitize_settings')) {
                $field->sanitize_settings();
            }
        }

        return $form;
    }

    public function preserve_booking_field_settings($form) {
        if (empty($form['fields']) || !is_array($form['fields'])) {
            return $form;
        }

        foreach ($form['fields'] as &$field) {
            if (!is_object($field) || $field->type !== 'booking_manager') {
                continue;
            }

            if (method_exists($field, 'sanitize_settings')) {
                $field->sanitize_settings();
            }

            if (empty($field->gfbmBusinessHours)) {
                $field->gfbmBusinessHours = GFBM_Helpers::encode_json_property(GFBM_Helpers::get_default_business_hours());
            }

            if ($field->gfbmBlockedTimes === null || $field->gfbmBlockedTimes === false) {
                $field->gfbmBlockedTimes = '[]';
            }

            if ($field->gfbmBlackoutDates === null || $field->gfbmBlackoutDates === false) {
                $field->gfbmBlackoutDates = '[]';
            }
        }

        return $form;
    }

    public function maybe_enqueue_form_assets($form, $is_ajax) {
        if (empty($form['fields']) || !is_array($form['fields'])) {
            return;
        }

        foreach ($form['fields'] as $field) {
            if (is_object($field) && $field->type === 'booking_manager') {
                self::enqueue_frontend_assets();
                break;
            }
        }
    }

    public function ensure_field_inputs($form) {
        if (empty($form['fields']) || !is_array($form['fields'])) {
            return $form;
        }

        foreach ($form['fields'] as &$field) {
            if (!is_object($field) || $field->type !== 'booking_manager' || empty($field->id)) {
                continue;
            }

            if (empty($field->inputs) && method_exists($field, 'get_entry_inputs')) {
                $field->inputs = $field->get_entry_inputs();
            }
        }

        return $form;
    }

    public function register_assets() {
        wp_register_style(
            'gfbm-flatpickr',
            GFBM_URL . 'assets/vendor/flatpickr/flatpickr.min.css',
            [],
            '4.6.13'
        );

        wp_register_script(
            'gfbm-flatpickr',
            GFBM_URL . 'assets/vendor/flatpickr/flatpickr.min.js',
            [],
            '4.6.13',
            true
        );

        wp_register_style(
            'gfbm-booking-manager',
            GFBM_URL . 'assets/css/booking-manager.css',
            ['gfbm-flatpickr'],
            GFBM_VERSION
        );

        wp_register_script(
            'gfbm-booking-manager',
            GFBM_URL . 'assets/js/booking-manager.js',
            ['gfbm-flatpickr', 'jquery'],
            GFBM_VERSION,
            true
        );

        wp_localize_script('gfbm-booking-manager', 'GFBM', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('gfbm_nonce'),
            'strings' => [
                'loading'  => __('Loading available times...', 'gravity-forms-booking-manager'),
                'none'     => __('No available times for this date.', 'gravity-forms-booking-manager'),
                'error'    => __('Unable to load available times. Please try again.', 'gravity-forms-booking-manager'),
                'times'    => __('Available times', 'gravity-forms-booking-manager'),
            ],
        ]);
    }

    public static function enqueue_frontend_assets() {
        wp_enqueue_style('gfbm-flatpickr');
        wp_enqueue_style('gfbm-booking-manager');

        wp_enqueue_script('gfbm-flatpickr');
        wp_enqueue_script('gfbm-booking-manager');
    }

    public function ajax_get_slots() {
        check_ajax_referer('gfbm_nonce', 'nonce');

        $form_id  = absint($_POST['formId'] ?? 0);
        $field_id = absint($_POST['fieldId'] ?? 0);
        $date     = sanitize_text_field($_POST['date'] ?? '');

        if (!$form_id || !$field_id || !$date) {
            wp_send_json_error(['message' => 'Missing booking data.']);
        }

        $form = GFAPI::get_form($form_id);

        if (!$form) {
            wp_send_json_error(['message' => 'Form not found.']);
        }

        $field = GFFormsModel::get_field($form, $field_id);

        if (!$field || $field->type !== 'booking_manager') {
            wp_send_json_error(['message' => 'Booking Manager field not found.']);
        }

        $date = self::normalize_date_string($date);

        if (!$date || !self::is_date_available($field, $date)) {
            wp_send_json_success([
                'slots' => [],
            ]);
        }

        wp_send_json_success([
            'slots' => self::get_available_slots($field, $form_id, $field_id, $date),
        ]);
    }

    public static function normalize_time($time, $fallback = '') {
        $time = trim((string) $time);

        if ($time === '') {
            return $fallback;
        }

        $timestamp = strtotime($time);

        if (!$timestamp) {
            return $fallback;
        }

        return date('H:i', $timestamp);
    }

    public static function time_to_minutes($time) {
        $time = self::normalize_time($time);

        if (!$time) {
            return null;
        }

        [$hours, $minutes] = array_map('intval', explode(':', $time));

        return ($hours * 60) + $minutes;
    }

    public static function get_default_business_hours() {
        return GFBM_Helpers::get_default_business_hours();
    }

    public static function get_business_hours($field) {
        $raw = $field->gfbmBusinessHours ?? '';

        return GFBM_Helpers::normalize_business_hours($raw);
    }

    public static function get_day_key_from_date($date) {
        $timestamp = strtotime($date);

        if (!$timestamp) {
            return '';
        }

        return strtolower(date('l', $timestamp));
    }

    public static function get_blackout_dates($field) {
        $raw = $field->gfbmBlackoutDates ?? [];

        return GFBM_Helpers::normalize_blackout_dates($raw);
    }

    public static function is_date_available($field, $date) {
        $timestamp = strtotime($date);

        if (!$timestamp) {
            return false;
        }

        $date = date('Y-m-d', $timestamp);

        if ($date < self::get_server_date_today()) {
            return false;
        }

        $offset = self::day_offset_from_today($date);
        $max_days = self::get_max_days($field);

        if ($offset === null || ($max_days > 0 && $offset > $max_days)) {
            return false;
        }

        if (in_array($date, self::get_blackout_dates($field), true)) {
            return false;
        }

        $day_key = self::get_day_key_from_date($date);
        $hours = self::get_business_hours($field);

        if (!$day_key || !GFBM_Helpers::is_day_open($hours[$day_key] ?? [])) {
            return false;
        }

        $open = self::time_to_minutes($hours[$day_key]['open']);
        $close = self::time_to_minutes($hours[$day_key]['close']);

        if ($open === null || $close === null || $open >= $close) {
            return false;
        }

        $duration = self::get_duration($field);
        $last_start = $close - $duration;

        if ($last_start < $open) {
            return false;
        }

        return !self::is_slot_before_notice($date, $last_start, self::get_min_notice_minutes($field));
    }

    public static function get_blocked_time_ranges($field, $day_key = '') {
        if ($day_key !== '') {
            return GFBM_Helpers::get_blocked_ranges_for_day($field, $day_key);
        }

        $raw = $field->gfbmBlockedTimes ?? [];
        $all = GFBM_Helpers::normalize_blocked_times($raw);
        $ranges = [];

        foreach ($all as $range) {
            $ranges[] = [
                'start' => $range['start'],
                'end'   => $range['end'],
            ];
        }

        return $ranges;
    }

    public static function slot_overlaps_blocked_time($slot, $duration, $blocked_ranges) {
        $slot_start = self::time_to_minutes($slot);

        if ($slot_start === null) {
            return true;
        }

        $slot_end = $slot_start + absint($duration);

        foreach ($blocked_ranges as $range) {
            $blocked_start = self::time_to_minutes($range['start']);
            $blocked_end   = self::time_to_minutes($range['end']);

            if ($blocked_start === null || $blocked_end === null) {
                continue;
            }

            if ($slot_start < $blocked_end && $slot_end > $blocked_start) {
                return true;
            }
        }

        return false;
    }

    public static function get_server_date_today() {
        return current_time('Y-m-d');
    }

    public static function get_server_time_minutes_now() {
        return ((int) current_time('H') * 60) + (int) current_time('i');
    }

    public static function is_past_slot_for_today($slot_minutes, $date) {
        if ($date !== self::get_server_date_today()) {
            return false;
        }

        return $slot_minutes <= self::get_server_time_minutes_now();
    }

    public static function build_slots($field, $date = '') {
        if ($date && !self::is_date_available($field, $date)) {
            return [];
        }

        $duration = self::get_duration($field);

        $hours = self::get_business_hours($field);
        $day_key = $date ? self::get_day_key_from_date($date) : 'monday';

        if (!$day_key || !GFBM_Helpers::is_day_open($hours[$day_key] ?? [])) {
            return [];
        }

        $open  = self::time_to_minutes($hours[$day_key]['open']);
        $close = self::time_to_minutes($hours[$day_key]['close']);

        if ($open === null || $close === null || $open >= $close) {
            return [];
        }

        $blocked_ranges = self::get_blocked_time_ranges($field, $day_key);
        $slots = [];

        for ($minutes = $open; $minutes + $duration <= $close; $minutes += $duration) {
            $hours_part = floor($minutes / 60);
            $minutes_part = $minutes % 60;
            $slot = sprintf('%02d:%02d', $hours_part, $minutes_part);

            if (self::slot_overlaps_blocked_time($slot, $duration, $blocked_ranges)) {
                continue;
            }

            if (self::is_past_slot_for_today($minutes, $date)) {
                continue;
            }

            if ($date && self::is_slot_before_notice($date, $minutes, self::get_min_notice_minutes($field))) {
                continue;
            }

            $slots[] = $slot;
        }

        return $slots;
    }

    public static function get_booked_slots($form_id, $field_id, $date) {
        $counts = self::get_booking_counts($form_id, $field_id, $date);
        $booked = [];

        foreach ($counts as $time => $count) {
            if ((int) $count > 0) {
                $booked[] = $time;
            }
        }

        return $booked;
    }

    public static function get_booking_counts($form_id, $field_id, $date, $exclude_entry_id = 0) {
        $counts = [];

        if (!class_exists('GFAPI')) {
            return $counts;
        }

        $offset = 0;
        $page_size = 200;
        $pages = 0;

        do {
            $entries = GFAPI::get_entries(
                $form_id,
                [
                    'status' => 'active',
                    'field_filters' => [
                        [
                            'key'   => (string) $field_id . '.1',
                            'value' => $date,
                        ],
                    ],
                ],
                null,
                [
                    'offset'    => $offset,
                    'page_size' => $page_size,
                ]
            );

            if (is_wp_error($entries) || !is_array($entries)) {
                break;
            }

            foreach ($entries as $entry) {
                $entry_id = isset($entry['id']) ? absint($entry['id']) : 0;

                if ($exclude_entry_id && $entry_id === absint($exclude_entry_id)) {
                    continue;
                }

                $time = self::normalize_time(rgar($entry, (string) $field_id . '.2'));

                if (!$time) {
                    continue;
                }

                if (!isset($counts[$time])) {
                    $counts[$time] = 0;
                }

                $counts[$time]++;
            }

            $batch = count($entries);
            $offset += $page_size;
            $pages++;
        } while ($batch === $page_size && $pages < 50);

        return $counts;
    }

    public static function get_available_slots($field, $form_id, $field_id, $date, $exclude_entry_id = 0) {
        $slots = self::build_slots($field, $date);

        if (!$slots) {
            return [];
        }

        $counts = self::get_booking_counts($form_id, $field_id, $date, $exclude_entry_id);
        $capacity = self::get_capacity($field);
        $counts = GFBM_Slot_Lock::add_lock_counts($counts, $slots, $form_id, $field_id, $date, $capacity);

        return self::filter_available_slots(
            $slots,
            $counts,
            self::get_duration($field),
            self::get_buffer_minutes($field),
            $capacity
        );
    }

    public static function filter_available_slots($slots, $counts, $duration, $buffer, $capacity) {
        $available = [];
        $capacity = max(1, absint($capacity));
        $buffer = max(0, absint($buffer));
        $duration = max(1, absint($duration));

        foreach ($slots as $slot) {
            $used = isset($counts[$slot]) ? (int) $counts[$slot] : 0;

            if ($used >= $capacity) {
                continue;
            }

            $start = self::time_to_minutes($slot);

            if ($start === null) {
                continue;
            }

            $end = $start + $duration;
            $blocked = false;

            foreach ($counts as $booked_time => $count) {
                if ((int) $count < 1 || (string) $booked_time === (string) $slot) {
                    continue;
                }

                $booked_start = self::time_to_minutes($booked_time);

                if ($booked_start === null) {
                    continue;
                }

                if (self::intervals_conflict($start, $end, $booked_start, $booked_start + $duration, $buffer)) {
                    $blocked = true;
                    break;
                }
            }

            if (!$blocked) {
                $available[] = $slot;
            }
        }

        return $available;
    }

    public static function intervals_conflict($start_a, $end_a, $start_b, $end_b, $buffer) {
        $buffer = max(0, absint($buffer));

        return $start_a < ($end_b + $buffer) && ($end_a + $buffer) > $start_b;
    }

    public static function get_duration($field) {
        $duration = !empty($field->gfbmDuration) ? absint($field->gfbmDuration) : 30;

        return $duration ? $duration : 30;
    }

    public static function get_capacity($field) {
        $capacity = isset($field->gfbmCapacity) ? absint($field->gfbmCapacity) : 1;

        if ($capacity < 1) {
            return 1;
        }

        return min(20, $capacity);
    }

    public static function get_buffer_minutes($field) {
        $allowed = [0, 5, 10, 15, 30, 45, 60];
        $value = isset($field->gfbmBuffer) ? absint($field->gfbmBuffer) : 0;

        return in_array($value, $allowed, true) ? $value : 0;
    }

    public static function get_min_notice_minutes($field) {
        $allowed = [0, 30, 60, 120, 240, 480, 1440, 2880];
        $value = isset($field->gfbmMinNotice) ? absint($field->gfbmMinNotice) : 0;

        return in_array($value, $allowed, true) ? $value : 0;
    }

    public static function get_max_days($field) {
        $allowed = [0, 7, 14, 30, 60, 90, 180, 365];
        $value = isset($field->gfbmMaxDays) ? absint($field->gfbmMaxDays) : 0;

        return in_array($value, $allowed, true) ? $value : 0;
    }

    public static function normalize_date_string($date) {
        $timestamp = strtotime((string) $date);

        if (!$timestamp) {
            return '';
        }

        return date('Y-m-d', $timestamp);
    }

    public static function day_offset_from_today($date) {
        $date = self::normalize_date_string($date);
        $today = self::get_server_date_today();

        if (!$date || !$today) {
            return null;
        }

        $today_ts = strtotime($today . ' 00:00:00');
        $date_ts = strtotime($date . ' 00:00:00');

        if (!$today_ts || !$date_ts) {
            return null;
        }

        return (int) round(($date_ts - $today_ts) / 86400);
    }

    public static function is_slot_before_notice($date, $slot_minutes, $notice_minutes) {
        $notice_minutes = absint($notice_minutes);

        if ($notice_minutes < 1) {
            return false;
        }

        $offset = self::day_offset_from_today($date);

        if ($offset === null) {
            return true;
        }

        $slot_absolute = ($offset * 1440) + (int) $slot_minutes;

        return $slot_absolute < (self::get_server_time_minutes_now() + $notice_minutes);
    }

    public static function calendar_min_date($field) {
        $today = self::get_server_date_today();

        for ($i = 0; $i <= 366; $i++) {
            $date = date('Y-m-d', strtotime($today . ' +' . $i . ' days'));

            if (self::is_date_available($field, $date)) {
                return $date;
            }
        }

        return $today;
    }

    public static function calendar_max_date($field) {
        $max_days = self::get_max_days($field);

        if ($max_days < 1) {
            return '';
        }

        return date('Y-m-d', strtotime(self::get_server_date_today() . ' +' . $max_days . ' days'));
    }

    public function release_locks_if_invalid($validation_result) {
        if (empty($validation_result['is_valid'])) {
            GFBM_Slot_Lock::release_all();
        }

        return $validation_result;
    }

    public function release_slot_locks() {
        GFBM_Slot_Lock::release_all();
    }

    public function validate_booking($validation_result) {
        $form = $validation_result['form'];

        foreach ($form['fields'] as &$field) {
            if ($field->type !== 'booking_manager') {
                continue;
            }

            $date = self::normalize_date_string(sanitize_text_field(rgpost('input_' . $field->id . '_1')));
            $time = self::normalize_time(sanitize_text_field(rgpost('input_' . $field->id . '_2')));

            if ($field->isRequired && (!$date || !$time)) {
                $validation_result['is_valid'] = false;
                $field->failed_validation = true;
                $field->validation_message = __('Please select an appointment date and time.', 'gravity-forms-booking-manager');
                continue;
            }

            if (!$date || !$time) {
                continue;
            }

            $available = self::get_available_slots($field, absint($form['id']), absint($field->id), $date);

            if (!in_array($time, $available, true)) {
                $validation_result['is_valid'] = false;
                $field->failed_validation = true;
                $field->validation_message = __('Please select an available appointment time.', 'gravity-forms-booking-manager');
                continue;
            }

            $counts = self::get_booking_counts(absint($form['id']), absint($field->id), $date);
            $booked = isset($counts[$time]) ? (int) $counts[$time] : 0;

            if (!GFBM_Slot_Lock::acquire(absint($form['id']), absint($field->id), $date, $time, self::get_capacity($field), $booked)) {
                $validation_result['is_valid'] = false;
                $field->failed_validation = true;
                $field->validation_message = __('Sorry, this time slot was just booked. Please choose another time.', 'gravity-forms-booking-manager');
            }
        }

        $validation_result['form'] = $form;

        return $validation_result;
    }
}