<?php

if (!defined('ABSPATH')) {
    exit;
}

class GFBM_Slot_Lock {
    const TTL = 120;

    private static $held = [];

    public static function base_key($form_id, $field_id, $date, $time) {
        return 'gfbm_lock_' . md5(absint($form_id) . '|' . absint($field_id) . '|' . $date . '|' . $time);
    }

    public static function acquire($form_id, $field_id, $date, $time, $capacity, $booked_count) {
        $capacity = max(1, absint($capacity));
        $booked_count = max(0, absint($booked_count));

        if ($booked_count >= $capacity) {
            return false;
        }

        for ($i = $booked_count; $i < $capacity; $i++) {
            $key = self::base_key($form_id, $field_id, $date, $time) . '_' . $i;
            self::purge_if_stale($key);

            if (add_option($key, time() . '|' . $key, '', false)) {
                self::$held[] = $key;
                return true;
            }
        }

        return false;
    }

    public static function add_lock_counts($counts, $slots, $form_id, $field_id, $date, $capacity) {
        foreach ($slots as $slot) {
            $locks = self::active_count($form_id, $field_id, $date, $slot, $capacity);

            if ($locks < 1) {
                continue;
            }

            if (!isset($counts[$slot])) {
                $counts[$slot] = 0;
            }

            $counts[$slot] += $locks;
        }

        return $counts;
    }

    public static function active_count($form_id, $field_id, $date, $time, $capacity) {
        $capacity = max(1, absint($capacity));
        $count = 0;

        for ($i = 0; $i < $capacity; $i++) {
            $key = self::base_key($form_id, $field_id, $date, $time) . '_' . $i;
            self::purge_if_stale($key);

            if (get_option($key, false) !== false) {
                $count++;
            }
        }

        return $count;
    }

    public static function release_all() {
        foreach (self::$held as $key) {
            delete_option($key);
        }

        self::$held = [];
    }

    private static function purge_if_stale($key) {
        $raw = get_option($key, false);

        if ($raw === false) {
            return;
        }

        $parts = explode('|', (string) $raw, 2);
        $created = isset($parts[0]) ? (int) $parts[0] : 0;

        if ($created < 1 || (time() - $created) > self::TTL) {
            delete_option($key);
        }
    }
}
