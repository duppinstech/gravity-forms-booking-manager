<?php

require_once __DIR__ . '/bootstrap.php';

$failures = 0;

function gfbm_assert($condition, $message) {
    global $failures;

    if ($condition) {
        echo "PASS {$message}\n";
        return;
    }

    $failures++;
    echo "FAIL {$message}\n";
}

function gfbm_field($overrides = []) {
    $hours = GFBM_Helpers::get_default_business_hours();
    $hours['monday'] = ['enabled' => true, 'open' => '09:00', 'close' => '12:00'];

    $field = (object) array_merge([
        'gfbmBusinessHours' => json_encode($hours),
        'gfbmDuration' => 30,
        'gfbmBlockedTimes' => json_encode([
            ['day' => 'all', 'start' => '10:00', 'end' => '11:00'],
        ]),
        'gfbmBlackoutDates' => '[]',
        'gfbmCapacity' => 1,
        'gfbmBuffer' => 0,
        'gfbmMinNotice' => 0,
        'gfbmMaxDays' => 0,
    ], $overrides);

    return $field;
}

gfbm_test_now('2026-01-01 08:00:00');
$monday = gfbm_field();
$slots = GF_Booking_Manager::build_slots($monday, '2026-10-19');
gfbm_assert($slots === ['09:00', '09:30', '11:00', '11:30'], 'Monday slots skip the 10:00-11:00 block');

gfbm_assert(GF_Booking_Manager::build_slots($monday, '2026-10-18') === [], 'Sunday is closed');

$blackout = gfbm_field([
    'gfbmBlackoutDates' => json_encode(['2026-10-19']),
]);
gfbm_assert(GF_Booking_Manager::is_date_available($blackout, '2026-10-19') === false, 'Blackout date is unavailable');

gfbm_test_now('2026-10-19 10:15:00');
$open = gfbm_field(['gfbmBlockedTimes' => '[]']);
$past = GF_Booking_Manager::build_slots($open, '2026-10-19');
gfbm_assert($past === ['10:30', '11:00', '11:30'], 'Times at or before 10:15 are omitted');

gfbm_test_now('2026-10-19 08:00:00');
$notice = gfbm_field(['gfbmMinNotice' => 120]);
$notice_slots = GF_Booking_Manager::build_slots($notice, '2026-10-19');
gfbm_assert($notice_slots === ['11:00', '11:30'], 'Minimum notice of 2 hours drops 09:00 and 09:30');

gfbm_test_now('2026-01-01 08:00:00');
$horizon = gfbm_field(['gfbmMaxDays' => 7, 'gfbmBlockedTimes' => '[]']);
gfbm_assert(GF_Booking_Manager::is_date_available($horizon, '2026-01-08') === true, 'Day 7 is inside a 7 day window');
gfbm_assert(GF_Booking_Manager::is_date_available($horizon, '2026-01-09') === false, 'Day 8 is outside a 7 day window');

$filtered = GF_Booking_Manager::filter_available_slots(
    ['09:00', '09:30', '10:00', '10:30', '11:00'],
    ['10:00' => 1],
    30,
    15,
    1
);
gfbm_assert($filtered === ['09:00', '11:00'], 'A 15 minute buffer blocks the neighboring slots');

$capacity = GF_Booking_Manager::filter_available_slots(
    ['10:00', '10:30'],
    ['10:00' => 1],
    30,
    0,
    2
);
gfbm_assert($capacity === ['10:00', '10:30'], 'Capacity 2 keeps a slot that already has one booking');

$GLOBALS['gfbm_options'] = [];
$first = GFBM_Slot_Lock::acquire(1, 3, '2026-10-19', '11:00', 1, 0);
$second = GFBM_Slot_Lock::acquire(1, 3, '2026-10-19', '11:00', 1, 0);
gfbm_assert($first === true && $second === false, 'Only one checkout can hold a capacity-1 slot');
GFBM_Slot_Lock::release_all();
$third = GFBM_Slot_Lock::acquire(1, 3, '2026-10-19', '11:00', 1, 0);
gfbm_assert($third === true, 'Releasing the lock lets the next checkout take the slot');

if ($failures > 0) {
    echo "{$failures} failed\n";
    exit(1);
}

echo "All slot tests passed\n";
exit(0);
