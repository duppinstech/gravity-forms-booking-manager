<?php



if (!defined('ABSPATH')) {

    exit;

}



class GFBM_Form_Editor {

    public static function init() {

        add_action('gform_editor_js', [__CLASS__, 'editor_script']);

        add_action('gform_field_standard_settings', [__CLASS__, 'render_standard_settings'], 10, 2);

        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_assets']);

    }



    public static function enqueue_assets($hook) {

        if (!self::is_form_editor_screen($hook)) {

            return;

        }



        wp_enqueue_style(

            'gfbm-flatpickr',

            GFBM_URL . 'assets/vendor/flatpickr/flatpickr.min.css',

            [],

            GFBM_VERSION

        );



        wp_enqueue_style(

            'gfbm-editor',

            GFBM_URL . 'assets/css/booking-manager.css',

            ['gfbm-flatpickr'],

            GFBM_VERSION

        );



        wp_enqueue_script(

            'gfbm-flatpickr',

            GFBM_URL . 'assets/vendor/flatpickr/flatpickr.min.js',

            [],

            GFBM_VERSION,

            true

        );



        $time_options = '';



        foreach (GFBM_Helpers::get_time_options() as $option) {

            $time_options .= sprintf(

                '<option value="%s">%s</option>',

                esc_attr($option['value']),

                esc_html($option['label'])

            );

        }



        $editor_deps = ['jquery', 'gfbm-flatpickr'];

        foreach (['gform_form_editor', 'gform_gravityforms_admin', 'gform_gravityforms'] as $handle) {
            if (wp_script_is($handle, 'registered')) {
                $editor_deps[] = $handle;
                break;
            }
        }

        wp_enqueue_script(

            'gfbm-editor',

            GFBM_URL . 'assets/js/booking-manager-editor.js',

            $editor_deps,

            GFBM_VERSION,

            true

        );



        wp_localize_script('gfbm-editor', 'GFBM_EDITOR', [

            'days'         => GFBM_Helpers::get_days(),

            'timeOptions'  => $time_options,

            'defaultLabel' => __('Schedule Appointment', 'gravity-forms-booking-manager'),

            'strings'      => [

                'closed'  => __('Closed', 'gravity-forms-booking-manager'),

                'allDays' => __('All days', 'gravity-forms-booking-manager'),

                'to'      => __('to', 'gravity-forms-booking-manager'),

                'remove'  => __('Remove', 'gravity-forms-booking-manager'),

            ],

        ]);

    }



    private static function is_form_editor_screen($hook) {

        if (function_exists('rgget') && rgget('page') === 'gf_edit_forms') {

            return true;

        }

        return is_string($hook) && strpos($hook, 'gf_edit_forms') !== false;

    }



    public static function editor_script() {

        ?>

        <script type="text/javascript">

            if (typeof fieldSettings !== 'undefined') {

                fieldSettings.booking_manager = '.label_setting, .admin_label_setting, .description_setting, .rules_setting, .visibility_setting, .css_class_setting, .conditional_logic_field_setting, .label_placement_setting, .hide_label_setting, .gfbm_business_hours_setting, .gfbm_duration_setting, .gfbm_scheduling_setting, .gfbm_blocked_times_setting, .gfbm_blackout_dates_setting, .gfbm_layout_setting, .gfbm_slots_display_setting';

            }

            if (typeof window.gfbmRegisterFormSaveHooks === 'function') {

                window.gfbmRegisterFormSaveHooks();

            } else if (typeof gform !== 'undefined' && gform.addAction) {

                gform.addAction('gform_form_saving_action_save_began', function (form) {

                    if (typeof window.gfbmSyncFormBeforeSave === 'function') {

                        window.gfbmSyncFormBeforeSave(form);

                    }

                }, 10, 1);

                gform.addAction('gform_form_saving_action_save_in_progress', function (form) {

                    if (typeof window.gfbmSyncFormBeforeSave === 'function') {

                        window.gfbmSyncFormBeforeSave(form);

                    }

                }, 10, 1);

            }

        </script>

        <?php

    }



    public static function render_standard_settings($position, $form_id) {

        if ((int) $position !== 10) {

            return;

        }



        $days = GFBM_Helpers::get_day_abbreviations();

        ?>



        <li class="gfbm_business_hours_setting field_setting">

            <details id="gfbm_hours_panel" class="gfbm-editor-collapsible" open>

                <summary><?php esc_html_e('Business Hours', 'gravity-forms-booking-manager'); ?></summary>



                <div class="gfbm-editor-collapsible-body">

                    <div class="gfbm-editor-apply-row">

                        <span><?php esc_html_e('Apply Monday to:', 'gravity-forms-booking-manager'); ?></span>

                        <button type="button" class="button gfbm-apply-hours" data-source="monday" data-scope="weekdays" onclick="return window.gfbmApplyHours && window.gfbmApplyHours('monday', 'weekdays');">

                            <?php esc_html_e('Tue–Fri', 'gravity-forms-booking-manager'); ?>

                        </button>

                        <button type="button" class="button gfbm-apply-hours" data-source="monday" data-scope="all" onclick="return window.gfbmApplyHours && window.gfbmApplyHours('monday', 'all');">

                            <?php esc_html_e('All days', 'gravity-forms-booking-manager'); ?>

                        </button>

                    </div>



                    <div class="gfbm-editor-business-hours">

                        <?php foreach ($days as $day_key => $day_label) :

                            $is_weekend = in_array($day_key, ['saturday', 'sunday'], true);

                            $open_default  = $is_weekend ? 'closed' : '09:00';

                            $close_default = $is_weekend ? 'closed' : '17:00';

                            ?>

                            <div class="gfbm-editor-day-row" id="gfbm_day_<?php echo esc_attr($day_key); ?>">

                                <span class="gfbm-editor-day-name"><?php echo esc_html($day_label); ?></span>



                                <div class="gfbm-editor-hours-inline">

                                    <select

                                        class="gfbm-day-open"

                                        id="gfbm_<?php echo esc_attr($day_key); ?>_open"

                                        data-day="<?php echo esc_attr($day_key); ?>"

                                    >

                                        <?php echo GFBM_Helpers::render_hour_select_options($open_default); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

                                    </select>



                                    <span class="gfbm-editor-sep"><?php esc_html_e('to', 'gravity-forms-booking-manager'); ?></span>



                                    <select

                                        class="gfbm-day-close"

                                        id="gfbm_<?php echo esc_attr($day_key); ?>_close"

                                        data-day="<?php echo esc_attr($day_key); ?>"

                                    >

                                        <?php echo GFBM_Helpers::render_hour_select_options($close_default); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

                                    </select>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                </div>

            </details>

        </li>



        <li class="gfbm_duration_setting field_setting">

            <label for="gfbm_duration">

                <?php esc_html_e('Appointment Length / Slot Interval', 'gravity-forms-booking-manager'); ?>

            </label>



            <select id="gfbm_duration" onchange="SetFieldProperty('gfbmDuration', this.value);">

                <option value="15">15 minutes</option>

                <option value="30">30 minutes</option>

                <option value="45">45 minutes</option>

                <option value="60">60 minutes</option>

                <option value="90">90 minutes</option>

                <option value="120">120 minutes</option>

            </select>

        </li>



        <li class="gfbm_scheduling_setting field_setting">
            <label for="gfbm_capacity"><?php esc_html_e('Capacity per slot', 'gravity-forms-booking-manager'); ?></label>
            <input type="number" id="gfbm_capacity" min="1" max="20" step="1" onchange="SetFieldProperty('gfbmCapacity', this.value);" />

            <label for="gfbm_buffer" class="gfbm-editor-sub-label"><?php esc_html_e('Buffer after each appointment', 'gravity-forms-booking-manager'); ?></label>
            <select id="gfbm_buffer" onchange="SetFieldProperty('gfbmBuffer', this.value);">
                <option value="0"><?php esc_html_e('None', 'gravity-forms-booking-manager'); ?></option>
                <option value="5"><?php esc_html_e('5 minutes', 'gravity-forms-booking-manager'); ?></option>
                <option value="10"><?php esc_html_e('10 minutes', 'gravity-forms-booking-manager'); ?></option>
                <option value="15"><?php esc_html_e('15 minutes', 'gravity-forms-booking-manager'); ?></option>
                <option value="30"><?php esc_html_e('30 minutes', 'gravity-forms-booking-manager'); ?></option>
                <option value="45"><?php esc_html_e('45 minutes', 'gravity-forms-booking-manager'); ?></option>
                <option value="60"><?php esc_html_e('60 minutes', 'gravity-forms-booking-manager'); ?></option>
            </select>

            <label for="gfbm_min_notice" class="gfbm-editor-sub-label"><?php esc_html_e('Minimum notice', 'gravity-forms-booking-manager'); ?></label>
            <select id="gfbm_min_notice" onchange="SetFieldProperty('gfbmMinNotice', this.value);">
                <option value="0"><?php esc_html_e('None', 'gravity-forms-booking-manager'); ?></option>
                <option value="30"><?php esc_html_e('30 minutes', 'gravity-forms-booking-manager'); ?></option>
                <option value="60"><?php esc_html_e('1 hour', 'gravity-forms-booking-manager'); ?></option>
                <option value="120"><?php esc_html_e('2 hours', 'gravity-forms-booking-manager'); ?></option>
                <option value="240"><?php esc_html_e('4 hours', 'gravity-forms-booking-manager'); ?></option>
                <option value="480"><?php esc_html_e('8 hours', 'gravity-forms-booking-manager'); ?></option>
                <option value="1440"><?php esc_html_e('1 day', 'gravity-forms-booking-manager'); ?></option>
                <option value="2880"><?php esc_html_e('2 days', 'gravity-forms-booking-manager'); ?></option>
            </select>

            <label for="gfbm_max_days" class="gfbm-editor-sub-label"><?php esc_html_e('How far ahead visitors can book', 'gravity-forms-booking-manager'); ?></label>
            <select id="gfbm_max_days" onchange="SetFieldProperty('gfbmMaxDays', this.value);">
                <option value="0"><?php esc_html_e('No limit', 'gravity-forms-booking-manager'); ?></option>
                <option value="7"><?php esc_html_e('7 days', 'gravity-forms-booking-manager'); ?></option>
                <option value="14"><?php esc_html_e('14 days', 'gravity-forms-booking-manager'); ?></option>
                <option value="30"><?php esc_html_e('30 days', 'gravity-forms-booking-manager'); ?></option>
                <option value="60"><?php esc_html_e('60 days', 'gravity-forms-booking-manager'); ?></option>
                <option value="90"><?php esc_html_e('90 days', 'gravity-forms-booking-manager'); ?></option>
                <option value="180"><?php esc_html_e('180 days', 'gravity-forms-booking-manager'); ?></option>
                <option value="365"><?php esc_html_e('365 days', 'gravity-forms-booking-manager'); ?></option>
            </select>

            <p class="description"><?php esc_html_e('Business hours use the timezone in WordPress Settings → General. This field does not have its own timezone.', 'gravity-forms-booking-manager'); ?></p>
        </li>

        <li class="gfbm_blocked_times_setting field_setting">

            <details id="gfbm_blocked_panel" class="gfbm-editor-collapsible is-empty">

                <summary><?php esc_html_e('Blocked Times', 'gravity-forms-booking-manager'); ?></summary>



                <div class="gfbm-editor-collapsible-body">

                    <p class="description">

                        <?php esc_html_e('Block time ranges on specific days or all days.', 'gravity-forms-booking-manager'); ?>

                    </p>



                    <div id="gfbm_blocked_list" class="gfbm-editor-block-list"></div>



                    <button type="button" class="button" id="gfbm_add_block">

                        <?php esc_html_e('Add blocked time', 'gravity-forms-booking-manager'); ?>

                    </button>

                </div>

            </details>

        </li>



        <li class="gfbm_blackout_dates_setting field_setting">

            <details id="gfbm_blackout_panel" class="gfbm-editor-collapsible is-empty">

                <summary><?php esc_html_e('Blackout Dates', 'gravity-forms-booking-manager'); ?></summary>



                <div class="gfbm-editor-collapsible-body">

                    <p class="description">

                        <?php esc_html_e('Click dates on the calendar to block them from booking.', 'gravity-forms-booking-manager'); ?>

                    </p>



                    <div class="gfbm-editor-blackout-picker-wrap">

                        <input type="text" id="gfbm_blackout_picker" class="gfbm-blackout-input" readonly="readonly" />

                    </div>



                    <ul id="gfbm_blackout_list" class="gfbm-blackout-list"></ul>

                </div>

            </details>

        </li>



        <li class="gfbm_layout_setting field_setting">

            <label for="gfbm_layout"><?php esc_html_e('Layout', 'gravity-forms-booking-manager'); ?></label>



            <select id="gfbm_layout" onchange="SetFieldProperty('gfbmLayout', this.value);">

                <option value="side-by-side"><?php esc_html_e('Side by Side (50/50)', 'gravity-forms-booking-manager'); ?></option>

                <option value="auto"><?php esc_html_e('Auto Responsive', 'gravity-forms-booking-manager'); ?></option>

                <option value="stacked"><?php esc_html_e('Stacked', 'gravity-forms-booking-manager'); ?></option>

            </select>

        </li>



        <li class="gfbm_slots_display_setting field_setting">

            <label for="gfbm_slot_columns"><?php esc_html_e('Time Slot Columns', 'gravity-forms-booking-manager'); ?></label>



            <select id="gfbm_slot_columns" onchange="SetFieldProperty('gfbmSlotColumns', this.value);">

                <option value="1">1 column</option>

                <option value="2">2 columns</option>

                <option value="3">3 columns</option>

                <option value="4">4 columns</option>

            </select>



            <label for="gfbm_slot_max_height" class="gfbm-editor-sub-label">

                <?php esc_html_e('Time Slot Max Height', 'gravity-forms-booking-manager'); ?>

            </label>



            <input

                type="number"

                id="gfbm_slot_max_height"

                min="120"

                max="800"

                step="10"

                onchange="SetFieldProperty('gfbmSlotMaxHeight', this.value);"

            />



            <p class="description">

                <?php esc_html_e('Height in pixels before the time-slot list scrolls.', 'gravity-forms-booking-manager'); ?>

            </p>

        </li>



        <?php

    }

}


