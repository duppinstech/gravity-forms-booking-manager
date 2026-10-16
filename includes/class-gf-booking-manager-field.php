<?php

if (!defined('ABSPATH')) {
    exit;
}

class GF_Booking_Manager_Field extends GF_Field {
    public $type = 'booking_manager';
    public $label = 'Schedule Appointment';

    // Declared so Gravity Forms persists these when the form is saved.
    public $gfbmBusinessHours = '';
    public $gfbmDuration = '30';
    public $gfbmBlockedTimes = '';
    public $gfbmBlackoutDates = '';
    public $gfbmLayout = 'side-by-side';
    public $gfbmSlotColumns = '2';
    public $gfbmSlotMaxHeight = '320';
    public $gfbmCapacity = '1';
    public $gfbmBuffer = '0';
    public $gfbmMinNotice = '0';
    public $gfbmMaxDays = '0';

    public function __construct($data = []) {
        parent::__construct($data);

        if ($this->id) {
            $this->inputs = $this->get_entry_inputs();
        }
    }

    public function get_form_editor_field_title() {
        return esc_attr__('Booking Manager', 'gravity-forms-booking-manager');
    }

    public function get_form_editor_button() {
        return [
            'group' => 'advanced_fields',
            'text'  => $this->get_form_editor_field_title(),
        ];
    }

    public function get_form_editor_field_settings() {
        return [
            'label_setting',
            'admin_label_setting',
            'description_setting',
            'rules_setting',
            'visibility_setting',
            'css_class_setting',
            'conditional_logic_field_setting',
            'label_placement_setting',
            'hide_label_setting',

            'gfbm_business_hours_setting',
            'gfbm_duration_setting',
            'gfbm_scheduling_setting',
            'gfbm_blocked_times_setting',
            'gfbm_blackout_dates_setting',
            'gfbm_layout_setting',
            'gfbm_slots_display_setting',
        ];
    }

    public function is_conditional_logic_supported() {
        return true;
    }

    public function sanitize_settings() {
        parent::sanitize_settings();

        $this->gfbmBusinessHours = $this->sanitize_json_setting(
            $this->gfbmBusinessHours,
            GFBM_Helpers::encode_json_property(GFBM_Helpers::get_default_business_hours())
        );
        $this->gfbmBlockedTimes  = $this->sanitize_json_setting($this->gfbmBlockedTimes, '[]');
        $this->gfbmBlackoutDates = $this->sanitize_json_setting($this->gfbmBlackoutDates, '[]');
        $this->gfbmDuration       = in_array((string) $this->gfbmDuration, ['15', '30', '45', '60', '90', '120'], true)
            ? (string) $this->gfbmDuration
            : '30';
        $this->gfbmLayout         = in_array((string) $this->gfbmLayout, ['side-by-side', 'auto', 'stacked'], true)
            ? (string) $this->gfbmLayout
            : 'side-by-side';
        $this->gfbmSlotColumns    = max(1, min(4, absint($this->gfbmSlotColumns ?: 2)));
        $this->gfbmSlotMaxHeight  = max(120, min(800, absint($this->gfbmSlotMaxHeight ?: 320)));
        $this->gfbmCapacity       = (string) GF_Booking_Manager::get_capacity($this);
        $this->gfbmBuffer         = (string) GF_Booking_Manager::get_buffer_minutes($this);
        $this->gfbmMinNotice      = (string) GF_Booking_Manager::get_min_notice_minutes($this);
        $this->gfbmMaxDays        = (string) GF_Booking_Manager::get_max_days($this);
    }

    private function sanitize_json_setting($value, $fallback = '') {
        if (is_array($value) || is_object($value)) {
            return GFBM_Helpers::encode_json_property($value);
        }

        if (!is_string($value) || trim($value) === '') {
            return $fallback;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? GFBM_Helpers::encode_json_property($decoded) : $fallback;
    }

    public function validate($value, $form) {
        $date = rgpost('input_' . $this->id . '_1');
        $time = rgpost('input_' . $this->id . '_2');

        if (!$this->isRequired) {
            return;
        }

        if ($date && $time) {
            return;
        }

        $this->failed_validation  = true;
        $this->validation_message = !empty($this->errorMessage)
            ? $this->errorMessage
            : esc_html__('Please select an appointment date and time.', 'gravity-forms-booking-manager');
    }

    public function get_entry_inputs() {
        if (!$this->id) {
            return null;
        }

        return [
            [
                'id'    => (string) $this->id . '.1',
                'label' => esc_html__('Appointment Date', 'gravity-forms-booking-manager'),
                'name'  => '',
            ],
            [
                'id'    => (string) $this->id . '.2',
                'label' => esc_html__('Appointment Time', 'gravity-forms-booking-manager'),
                'name'  => '',
            ],
        ];
    }

    private function get_submitted_values($value) {
        $date = '';
        $time = '';

        if (is_array($value)) {
            $date = rgar($value, $this->id . '.1');
            $time = rgar($value, $this->id . '.2');

            if ($date === '' && $time === '') {
                $date = rgar($value, '1');
                $time = rgar($value, '2');
            }
        }

        return [
            'date' => $date,
            'time' => $time,
        ];
    }

    public function get_field_input($form, $value = '', $entry = null) {
        if ($this->is_form_editor()) {
            return '<div class="gfbm-editor-preview">' . esc_html__('Booking calendar and available time slots will appear here.', 'gravity-forms-booking-manager') . '</div>';
        }

        GF_Booking_Manager::enqueue_frontend_assets();

        $form_id  = absint($form['id']);
        $field_id = absint($this->id);
        $values   = $this->get_submitted_values($value);

        $layout = !empty($this->gfbmLayout) ? $this->gfbmLayout : 'side-by-side';
        $slot_columns = !empty($this->gfbmSlotColumns) ? absint($this->gfbmSlotColumns) : 2;
        $slot_max_height = !empty($this->gfbmSlotMaxHeight) ? absint($this->gfbmSlotMaxHeight) : 320;

        if (!$slot_columns) {
            $slot_columns = 2;
        }

        if (!$slot_max_height) {
            $slot_max_height = 320;
        }

        $blackout_dates = GF_Booking_Manager::get_blackout_dates($this);
        $business_hours = GF_Booking_Manager::get_business_hours($this);
        $disabled_days = [];

        $day_map = [
            'sunday'    => 0,
            'monday'    => 1,
            'tuesday'   => 2,
            'wednesday' => 3,
            'thursday'  => 4,
            'friday'    => 5,
            'saturday'  => 6,
        ];

        foreach ($business_hours as $day => $settings) {
            if (!GFBM_Helpers::is_day_open($settings) && isset($day_map[$day])) {
                $disabled_days[] = $day_map[$day];
            }
        }

        ob_start();
        ?>
        <div
            class="gfbm-field gfbm-layout-<?php echo esc_attr($layout); ?>"
            data-form-id="<?php echo esc_attr($form_id); ?>"
            data-field-id="<?php echo esc_attr($field_id); ?>"
            data-blackout-dates="<?php echo esc_attr(wp_json_encode($blackout_dates)); ?>"
            data-disabled-days="<?php echo esc_attr(wp_json_encode($disabled_days)); ?>"
            data-min-date="<?php echo esc_attr(GF_Booking_Manager::calendar_min_date($this)); ?>"
            data-max-date="<?php echo esc_attr(GF_Booking_Manager::calendar_max_date($this)); ?>"
            style="
                --gfbm-slot-columns: <?php echo esc_attr($slot_columns); ?>;
                --gfbm-slot-max-height: <?php echo esc_attr($slot_max_height); ?>px;
            "
        >
            <input
                type="hidden"
                name="input_<?php echo esc_attr($field_id); ?>_1"
                id="input_<?php echo esc_attr($form_id); ?>_<?php echo esc_attr($field_id); ?>_1"
                class="gfbm-date-input"
                value="<?php echo esc_attr($values['date']); ?>"
            />

            <input
                type="hidden"
                name="input_<?php echo esc_attr($field_id); ?>_2"
                id="input_<?php echo esc_attr($form_id); ?>_<?php echo esc_attr($field_id); ?>_2"
                class="gfbm-time-input"
                value="<?php echo esc_attr($values['time']); ?>"
            />

            <div class="gfbm-layout-inner">
                <div class="gfbm-calendar-wrap">
                    <input
                        type="text"
                        class="gfbm-calendar-input"
                        readonly="readonly"
                        aria-label="<?php esc_attr_e('Select appointment date', 'gravity-forms-booking-manager'); ?>"
                    />
                </div>

                <div class="gfbm-slots-wrap">
                    <div class="gfbm-slots" role="group" aria-label="<?php esc_attr_e('Available times', 'gravity-forms-booking-manager'); ?>">
                        <p class="gfbm-message"><?php esc_html_e('Choose a date to view available times.', 'gravity-forms-booking-manager'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php

        return ob_get_clean();
    }

    public function is_value_submission_empty($form_id) {
        $date = rgpost('input_' . $this->id . '_1');
        $time = rgpost('input_' . $this->id . '_2');

        if ($this->isRequired) {
            return $date === '' || $time === '';
        }

        return $date === '' && $time === '';
    }

    public function get_value_save_entry($value, $form, $input_name, $lead_id, $lead) {
        return sanitize_text_field($value);
    }

    public function get_value_entry_detail($value, $currency = '', $use_text = false, $format = 'html', $media = 'screen') {
        if (is_array($value)) {
            $values = $this->get_submitted_values($value);

            if ($values['date'] && $values['time']) {
                return esc_html($values['date'] . ' at ' . $values['time']);
            }
        }

        return esc_html(is_scalar($value) ? $value : '');
    }

    public function get_value_merge_tag($value, $input_id, $entry, $form, $modifier, $raw_value, $url_encode, $esc_html, $format, $nl2br) {
        if ($input_id) {
            $output = rgar($entry, (string) $input_id);
        } else {
            $date = rgar($entry, (string) $this->id . '.1');
            $time = rgar($entry, (string) $this->id . '.2');
            $output = trim($date . ' at ' . $time);
        }

        if ($url_encode) {
            $output = urlencode($output);
        }

        return $esc_html ? esc_html($output) : $output;
    }

    public function get_value_export($entry, $input_id = '', $use_text = false, $is_csv = false) {
        if ($input_id) {
            return rgar($entry, (string) $input_id);
        }

        $date = rgar($entry, (string) $this->id . '.1');
        $time = rgar($entry, (string) $this->id . '.2');

        return trim($date . ' ' . $time);
    }
}
