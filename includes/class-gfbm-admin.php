<?php

if (!defined('ABSPATH')) {
    exit;
}

class GFBM_Admin {
    const PAGE = 'gfbm-bookings';

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'register_menu'], 20);
        add_action('admin_post_gfbm_booking_action', [__CLASS__, 'handle_action']);
    }

    public static function register_menu() {
        $parent = 'gf_edit_forms';
        $cap = 'gravityforms_view_entries';

        if (!current_user_can($cap)) {
            return;
        }

        add_submenu_page(
            $parent,
            __('Bookings', 'gravity-forms-booking-manager'),
            __('Bookings', 'gravity-forms-booking-manager'),
            $cap,
            self::PAGE,
            [__CLASS__, 'render_page']
        );
    }

    public static function render_page() {
        if (!current_user_can('gravityforms_view_entries')) {
            wp_die(esc_html__('You do not have permission to view bookings.', 'gravity-forms-booking-manager'));
        }

        $notice = isset($_GET['gfbm_notice']) ? sanitize_text_field(wp_unslash($_GET['gfbm_notice'])) : '';
        $rows = self::upcoming_rows();

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Bookings', 'gravity-forms-booking-manager') . '</h1>';
        echo '<p>' . esc_html__('Upcoming appointments stored on Gravity Forms entries. Hours use the timezone in Settings → General. Cancel sends the entry to the trash, which frees the slot. Reschedule checks capacity, buffers, and the booking window before it saves.', 'gravity-forms-booking-manager') . '</p>';

        if ($notice === 'rescheduled') {
            echo '<div class="notice notice-success"><p>' . esc_html__('Appointment rescheduled.', 'gravity-forms-booking-manager') . '</p></div>';
        } elseif ($notice === 'cancelled') {
            echo '<div class="notice notice-success"><p>' . esc_html__('Appointment cancelled. The entry is in the trash.', 'gravity-forms-booking-manager') . '</p></div>';
        } elseif ($notice === 'unavailable') {
            echo '<div class="notice notice-error"><p>' . esc_html__('That time is not available.', 'gravity-forms-booking-manager') . '</p></div>';
        } elseif ($notice === 'invalid') {
            echo '<div class="notice notice-error"><p>' . esc_html__('Enter a valid date and time.', 'gravity-forms-booking-manager') . '</p></div>';
        }

        echo '<table class="widefat striped"><thead><tr>';
        echo '<th>' . esc_html__('Date', 'gravity-forms-booking-manager') . '</th>';
        echo '<th>' . esc_html__('Time', 'gravity-forms-booking-manager') . '</th>';
        echo '<th>' . esc_html__('Form', 'gravity-forms-booking-manager') . '</th>';
        echo '<th>' . esc_html__('Entry', 'gravity-forms-booking-manager') . '</th>';
        echo '<th>' . esc_html__('Reschedule', 'gravity-forms-booking-manager') . '</th>';
        echo '</tr></thead><tbody>';

        if (!$rows) {
            echo '<tr><td colspan="5">' . esc_html__('No upcoming bookings.', 'gravity-forms-booking-manager') . '</td></tr>';
        }

        foreach ($rows as $row) {
            $entry_url = admin_url('admin.php?page=gf_entries&view=entry&id=' . absint($row['form_id']) . '&lid=' . absint($row['entry_id']));
            echo '<tr>';
            echo '<td>' . esc_html($row['date']) . '</td>';
            echo '<td>' . esc_html($row['time']) . '</td>';
            echo '<td>' . esc_html($row['form_title']) . '</td>';
            echo '<td><a href="' . esc_url($entry_url) . '">#' . esc_html((string) $row['entry_id']) . '</a></td>';
            echo '<td>';

            if (current_user_can('gravityforms_edit_entries')) {
                echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">';
                wp_nonce_field('gfbm_booking_action_' . $row['entry_id']);
                echo '<input type="hidden" name="action" value="gfbm_booking_action" />';
                echo '<input type="hidden" name="gfbm_do" value="reschedule" />';
                echo '<input type="hidden" name="entry_id" value="' . esc_attr((string) $row['entry_id']) . '" />';
                echo '<input type="hidden" name="form_id" value="' . esc_attr((string) $row['form_id']) . '" />';
                echo '<input type="hidden" name="field_id" value="' . esc_attr((string) $row['field_id']) . '" />';
                echo '<input type="date" name="date" value="' . esc_attr($row['date']) . '" required="required" />';
                echo '<input type="time" name="time" value="' . esc_attr($row['time']) . '" required="required" />';
                submit_button(__('Save', 'gravity-forms-booking-manager'), 'secondary small', 'submit', false);
                echo '</form>';

                if (current_user_can('gravityforms_delete_entries')) {
                    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="margin-top:6px;">';
                    wp_nonce_field('gfbm_booking_action_' . $row['entry_id']);
                    echo '<input type="hidden" name="action" value="gfbm_booking_action" />';
                    echo '<input type="hidden" name="gfbm_do" value="cancel" />';
                    echo '<input type="hidden" name="entry_id" value="' . esc_attr((string) $row['entry_id']) . '" />';
                    echo '<input type="hidden" name="form_id" value="' . esc_attr((string) $row['form_id']) . '" />';
                    submit_button(__('Cancel', 'gravity-forms-booking-manager'), 'delete small', 'submit', false);
                    echo '</form>';
                }
            }

            echo '</td></tr>';
        }

        echo '</tbody></table></div>';
    }

    public static function handle_action() {
        if (!current_user_can('gravityforms_edit_entries')) {
            wp_die(esc_html__('You do not have permission to change bookings.', 'gravity-forms-booking-manager'));
        }

        $entry_id = absint($_POST['entry_id'] ?? 0);
        $form_id = absint($_POST['form_id'] ?? 0);
        $do = sanitize_key($_POST['gfbm_do'] ?? '');

        check_admin_referer('gfbm_booking_action_' . $entry_id);

        $redirect = admin_url('admin.php?page=' . self::PAGE);
        $entry = GFAPI::get_entry($entry_id);

        if (is_wp_error($entry) || absint($entry['form_id'] ?? 0) !== $form_id) {
            wp_safe_redirect(add_query_arg('gfbm_notice', 'invalid', $redirect));
            exit;
        }

        if ($do === 'cancel') {
            if (!current_user_can('gravityforms_delete_entries')) {
                wp_die(esc_html__('You do not have permission to cancel bookings.', 'gravity-forms-booking-manager'));
            }

            GFAPI::update_entry_property($entry_id, 'status', 'trash');
            wp_safe_redirect(add_query_arg('gfbm_notice', 'cancelled', $redirect));
            exit;
        }

        if ($do !== 'reschedule') {
            wp_safe_redirect(add_query_arg('gfbm_notice', 'invalid', $redirect));
            exit;
        }

        $field_id = absint($_POST['field_id'] ?? 0);
        $date = GF_Booking_Manager::normalize_date_string(sanitize_text_field(wp_unslash($_POST['date'] ?? '')));
        $time = GF_Booking_Manager::normalize_time(sanitize_text_field(wp_unslash($_POST['time'] ?? '')));
        $form = GFAPI::get_form($form_id);
        $field = $form ? GFFormsModel::get_field($form, $field_id) : null;

        if (!$date || !$time || !$field || $field->type !== 'booking_manager') {
            wp_safe_redirect(add_query_arg('gfbm_notice', 'invalid', $redirect));
            exit;
        }

        $available = GF_Booking_Manager::get_available_slots($field, $form_id, $field_id, $date, $entry_id);

        if (!in_array($time, $available, true)) {
            wp_safe_redirect(add_query_arg('gfbm_notice', 'unavailable', $redirect));
            exit;
        }

        $counts = GF_Booking_Manager::get_booking_counts($form_id, $field_id, $date, $entry_id);
        $booked = isset($counts[$time]) ? (int) $counts[$time] : 0;

        if (!GFBM_Slot_Lock::acquire($form_id, $field_id, $date, $time, GF_Booking_Manager::get_capacity($field), $booked)) {
            wp_safe_redirect(add_query_arg('gfbm_notice', 'unavailable', $redirect));
            exit;
        }

        GFAPI::update_entry_field($entry_id, $field_id . '.1', $date);
        GFAPI::update_entry_field($entry_id, $field_id . '.2', $time);
        GFBM_Slot_Lock::release_all();

        wp_safe_redirect(add_query_arg('gfbm_notice', 'rescheduled', $redirect));
        exit;
    }

    private static function upcoming_rows() {
        if (!class_exists('GFAPI')) {
            return [];
        }

        $forms = GFAPI::get_forms();
        $today = GF_Booking_Manager::get_server_date_today();
        $rows = [];

        if (!is_array($forms)) {
            return [];
        }

        foreach ($forms as $form) {
            if (empty($form['fields']) || !is_array($form['fields'])) {
                continue;
            }

            foreach ($form['fields'] as $field) {
                if (!is_object($field) || $field->type !== 'booking_manager') {
                    continue;
                }

                $entries = self::entries_for_field(absint($form['id']), absint($field->id));

                foreach ($entries as $entry) {
                    $date = GF_Booking_Manager::normalize_date_string(rgar($entry, $field->id . '.1'));
                    $time = GF_Booking_Manager::normalize_time(rgar($entry, $field->id . '.2'));

                    if (!$date || !$time || $date < $today) {
                        continue;
                    }

                    $rows[] = [
                        'date'       => $date,
                        'time'       => $time,
                        'form_id'    => absint($form['id']),
                        'form_title' => isset($form['title']) ? (string) $form['title'] : '',
                        'field_id'   => absint($field->id),
                        'entry_id'   => absint($entry['id'] ?? 0),
                    ];
                }
            }
        }

        usort($rows, function ($a, $b) {
            return strcmp($a['date'] . ' ' . $a['time'], $b['date'] . ' ' . $b['time']);
        });

        return array_slice($rows, 0, 200);
    }

    private static function entries_for_field($form_id, $field_id) {
        $rows = [];
        $offset = 0;
        $page_size = 200;
        $pages = 0;

        do {
            $entries = GFAPI::get_entries(
                $form_id,
                ['status' => 'active'],
                null,
                ['offset' => $offset, 'page_size' => $page_size]
            );

            if (is_wp_error($entries) || !is_array($entries)) {
                break;
            }

            foreach ($entries as $entry) {
                if (rgar($entry, $field_id . '.1')) {
                    $rows[] = $entry;
                }
            }

            $batch = count($entries);
            $offset += $page_size;
            $pages++;
        } while ($batch === $page_size && $pages < 10);

        return $rows;
    }
}
