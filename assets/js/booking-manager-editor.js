(function ($) {
  var GFBM_EDITOR = window.GFBM_EDITOR || {};
  var days = GFBM_EDITOR.days || {};
  var dayKeys = Object.keys(days);
  var CLOSED = 'closed';

  function getField() {
    return typeof GetSelectedField === 'function' ? GetSelectedField() : null;
  }

  function parseStoredJson(raw, fallback) {
    if (Array.isArray(raw)) {
      return raw;
    }

    if (raw && typeof raw === 'object') {
      return raw;
    }

    if (typeof raw === 'string' && raw.trim() !== '') {
      try {
        return JSON.parse(raw);
      } catch (e) {
        return fallback;
      }
    }

    return fallback;
  }

  function defaultBusinessHours() {
    return {
      monday: { enabled: true, open: '09:00', close: '17:00' },
      tuesday: { enabled: true, open: '09:00', close: '17:00' },
      wednesday: { enabled: true, open: '09:00', close: '17:00' },
      thursday: { enabled: true, open: '09:00', close: '17:00' },
      friday: { enabled: true, open: '09:00', close: '17:00' },
      saturday: { enabled: false, open: CLOSED, close: CLOSED },
      sunday: { enabled: false, open: CLOSED, close: CLOSED }
    };
  }

  function getBusinessHours(field) {
    var defaults = defaultBusinessHours();
    var hours = parseStoredJson(field.gfbmBusinessHours, {});

    if (!hours || typeof hours !== 'object' || Array.isArray(hours)) {
      hours = {};
    }

    dayKeys.forEach(function (day) {
      if (!hours[day]) {
        hours[day] = defaults[day];
      }

      if (hours[day].open === CLOSED || hours[day].close === CLOSED) {
        hours[day].enabled = false;
        hours[day].open = CLOSED;
        hours[day].close = CLOSED;
        return;
      }

      hours[day].enabled = hours[day].enabled !== false;
      hours[day].open = hours[day].open || defaults[day].open;
      hours[day].close = hours[day].close || defaults[day].close;
    });

    return hours;
  }

  function saveBusinessHours(hours) {
    var field = getField();

    if (!field) {
      return;
    }

    var encoded = JSON.stringify(hours);
    field.gfbmBusinessHours = encoded;
    SetFieldProperty('gfbmBusinessHours', encoded);
  }

  function readDayFromUi(day) {
    var open = daySelect('gfbm_' + day + '_open').val();
    var close = daySelect('gfbm_' + day + '_close').val();

    if (open === CLOSED || close === CLOSED) {
      return { enabled: false, open: CLOSED, close: CLOSED };
    }

    return { enabled: true, open: open, close: close };
  }

  function readAllBusinessHoursFromUi() {
    var hours = {};

    dayKeys.forEach(function (day) {
      hours[day] = readDayFromUi(day);
    });

    return hours;
  }

  function hoursPanel() {
    return $('#gfbm_hours_panel');
  }

  function daySelect(id) {
    var $select = hoursPanel().find('#' + id);

    return $select.length ? $select : $('#' + id);
  }

  function setSelectValue(id, value) {
    var $select = daySelect(id);

    if (!$select.length) {
      return;
    }

    $select.val(value);

    $select.find('option').each(function () {
      this.selected = this.value === value;
    });
  }

  function syncBusinessHoursUi(hours) {
    dayKeys.forEach(function (day) {
      var row = hours[day];
      var isClosed = !row.enabled || row.open === CLOSED || row.close === CLOSED;
      var openVal = isClosed ? CLOSED : row.open;
      var closeVal = isClosed ? CLOSED : row.close;

      setSelectValue('gfbm_' + day + '_open', openVal);
      setSelectValue('gfbm_' + day + '_close', closeVal);
      $('#gfbm_day_' + day).toggleClass('is-closed', isClosed);
    });
  }

  function updateDayHours(day, open, close) {
    var field = getField();
    var hours = getBusinessHours(field);

    if (open === CLOSED || close === CLOSED) {
      hours[day] = { enabled: false, open: CLOSED, close: CLOSED };
    } else {
      hours[day] = { enabled: true, open: open, close: close };

      if (open >= close) {
        hours[day].close = '17:00';

        if (open >= hours[day].close) {
          hours[day] = { enabled: false, open: CLOSED, close: CLOSED };
        }
      }
    }

    saveBusinessHours(hours);
    syncBusinessHoursUi(hours);
  }

  function applyHoursFromDay(sourceDay, targetDays) {
    var field = getField();

    if (!field) {
      return;
    }

    var hours = getBusinessHours(field);
    var source = readDayFromUi(sourceDay);

    targetDays.forEach(function (day) {
      hours[day] = {
        enabled: source.enabled,
        open: source.open,
        close: source.close
      };
    });

    saveBusinessHours(hours);
    syncBusinessHoursUi(hours);
  }

  function getBlockedTimes(field) {
    return parseStoredJson(field.gfbmBlockedTimes, []);
  }

  function saveBlockedTimes(items) {
    var field = getField();
    var encoded = JSON.stringify(items);

    if (field) {
      field.gfbmBlockedTimes = encoded;
    }

    SetFieldProperty('gfbmBlockedTimes', encoded);
    renderBlockedTimes(items);
    updateCollapsibleSections();
  }

  function blockedRowHtml(item, index) {
    var dayOptions = '<option value="all">' + GFBM_EDITOR.strings.allDays + '</option>';

    dayKeys.forEach(function (day) {
      dayOptions += '<option value="' + day + '"' + (item.day === day ? ' selected' : '') + '>' + days[day] + '</option>';
    });

    return (
      '<div class="gfbm-editor-block-row" data-index="' + index + '">' +
        '<select class="gfbm-block-day">' + dayOptions + '</select>' +
        '<select class="gfbm-block-start">' + GFBM_EDITOR.timeOptions + '</select>' +
        '<span class="gfbm-editor-sep">' + GFBM_EDITOR.strings.to + '</span>' +
        '<select class="gfbm-block-end">' + GFBM_EDITOR.timeOptions + '</select>' +
        '<button type="button" class="button gfbm-remove-block" aria-label="' + GFBM_EDITOR.strings.remove + '">&times;</button>' +
      '</div>'
    );
  }

  function renderBlockedTimes(items) {
    $('#gfbm_blocked_list').empty();

    items.forEach(function (item, index) {
      var $row = $(blockedRowHtml(item, index));
      $row.find('.gfbm-block-start').val(item.start || '12:00');
      $row.find('.gfbm-block-end').val(item.end || '13:00');
      $('#gfbm_blocked_list').append($row);
    });
  }

  function collectBlockedTimes() {
    var items = [];

    $('#gfbm_blocked_list .gfbm-editor-block-row').each(function () {
      var $row = $(this);
      var start = $row.find('.gfbm-block-start').val();
      var end = $row.find('.gfbm-block-end').val();

      if (!start || !end || start >= end) {
        return;
      }

      items.push({
        day: $row.find('.gfbm-block-day').val(),
        start: start,
        end: end
      });
    });

    return items;
  }

  function getBlackoutDates(field) {
    return parseStoredJson(field.gfbmBlackoutDates, []);
  }

  function saveBlackoutDates(dates, skipPickerSync) {
    var field = getField();
    var encoded = JSON.stringify(dates);

    if (field) {
      field.gfbmBlackoutDates = encoded;
    }

    SetFieldProperty('gfbmBlackoutDates', encoded);
    renderBlackoutDates(dates);
    updateCollapsibleSections();

    if (!skipPickerSync) {
      syncBlackoutPicker(dates);
    }
  }

  function renderBlackoutDates(dates) {
    var $list = $('#gfbm_blackout_list');
    $list.empty();

    dates.forEach(function (date) {
      $list.append(
        '<li class="gfbm-blackout-chip" data-date="' + date + '">' +
          '<span>' + date + '</span>' +
          '<button type="button" class="gfbm-remove-blackout" aria-label="' + GFBM_EDITOR.strings.remove + '">&times;</button>' +
        '</li>'
      );
    });
  }

  function updateCollapsibleSections() {
    var field = getField();

    if (!field) {
      return;
    }

    var blocked = getBlockedTimes(field);
    var blackouts = getBlackoutDates(field);

    $('#gfbm_blocked_panel').toggleClass('is-empty', blocked.length === 0);
    $('#gfbm_blackout_panel').toggleClass('is-empty', blackouts.length === 0);

    if (blocked.length === 0) {
      $('#gfbm_blocked_panel').removeAttr('open');
    }

    if (blackouts.length === 0) {
      $('#gfbm_blackout_panel').removeAttr('open');
    }
  }

  var blackoutPicker = null;

  function syncBlackoutPicker(dates) {
    if (!blackoutPicker) {
      return;
    }

    blackoutPicker.setDate(dates || [], false);
  }

  function initBlackoutPicker(dates) {
    if (typeof flatpickr === 'undefined') {
      return;
    }

    var input = document.getElementById('gfbm_blackout_picker');

    if (!input) {
      return;
    }

    if (blackoutPicker) {
      syncBlackoutPicker(dates);
      return;
    }

    blackoutPicker = flatpickr(input, {
      inline: true,
      static: true,
      appendTo: input.parentElement,
      mode: 'multiple',
      dateFormat: 'Y-m-d',
      defaultDate: dates || [],
      onChange: function (selectedDates, dateStr, instance) {
        var nextDates = instance.selectedDates.map(function (date) {
          return instance.formatDate(date, 'Y-m-d');
        });

        saveBlackoutDates(nextDates, true);
      }
    });
  }

  function ensureDefaults(field) {
    if (!field.gfbmBusinessHours) {
      var encoded = JSON.stringify(defaultBusinessHours());
      field.gfbmBusinessHours = encoded;
      SetFieldProperty('gfbmBusinessHours', encoded);
    }

    if (!field.label) {
      SetFieldProperty('label', GFBM_EDITOR.defaultLabel);
      field.label = GFBM_EDITOR.defaultLabel;
    }
  }

  function collectBlackoutDatesFromUi() {
    var dates = [];

    $('#gfbm_blackout_list .gfbm-blackout-chip').each(function () {
      var date = $(this).data('date');

      if (date) {
        dates.push(String(date));
      }
    });

    return dates;
  }

  function buildPayloadFromUi(field) {
    return {
      gfbmBusinessHours: JSON.stringify(readAllBusinessHoursFromUi()),
      gfbmDuration: $('#gfbm_duration').val() || '30',
      gfbmCapacity: $('#gfbm_capacity').val() || '1',
      gfbmBuffer: $('#gfbm_buffer').val() || '0',
      gfbmMinNotice: $('#gfbm_min_notice').val() || '0',
      gfbmMaxDays: $('#gfbm_max_days').val() || '0',
      gfbmLayout: $('#gfbm_layout').val() || 'side-by-side',
      gfbmSlotColumns: $('#gfbm_slot_columns').val() || '2',
      gfbmSlotMaxHeight: $('#gfbm_slot_max_height').val() || '320',
      gfbmBlockedTimes: JSON.stringify(collectBlockedTimes()),
      gfbmBlackoutDates: JSON.stringify(
        $('#gfbm_blackout_list .gfbm-blackout-chip').length
          ? collectBlackoutDatesFromUi()
          : getBlackoutDates(field)
      )
    };
  }

  function syncOpenFieldToForm(form) {
    var field = getField();

    if (!field || field.type !== 'booking_manager' || !form || !form.fields) {
      return;
    }

    var payload = buildPayloadFromUi(field);

    for (var i = 0; i < form.fields.length; i++) {
      if (parseInt(form.fields[i].id, 10) === parseInt(field.id, 10)) {
        Object.keys(payload).forEach(function (key) {
          form.fields[i][key] = payload[key];
          field[key] = payload[key];
        });
        break;
      }
    }
  }

  function registerFormSaveHooks() {
    if (window.gfbmSaveHooksRegistered || typeof gform === 'undefined' || typeof gform.addAction !== 'function') {
      return;
    }

    gform.addAction('gform_form_saving_action_save_began', function (form) {
      syncOpenFieldToForm(form);
    }, 10, 1);

    gform.addAction('gform_form_saving_action_save_in_progress', function (form) {
      syncOpenFieldToForm(form);
    }, 10, 1);

    window.gfbmSaveHooksRegistered = true;
  }

  window.gfbmSyncFormBeforeSave = syncOpenFieldToForm;
  window.gfbmRegisterFormSaveHooks = registerFormSaveHooks;

  function loadFieldSettings(field) {
    if (!field || field.type !== 'booking_manager') {
      return;
    }

    ensureDefaults(field);
    syncBusinessHoursUi(getBusinessHours(field));

    $('#gfbm_duration').val(field.gfbmDuration || '30');
    $('#gfbm_capacity').val(field.gfbmCapacity || '1');
    $('#gfbm_buffer').val(field.gfbmBuffer || '0');
    $('#gfbm_min_notice').val(field.gfbmMinNotice || '0');
    $('#gfbm_max_days').val(field.gfbmMaxDays || '0');
    $('#gfbm_layout').val(field.gfbmLayout || 'side-by-side');
    $('#gfbm_slot_columns').val(field.gfbmSlotColumns || '2');
    $('#gfbm_slot_max_height').val(field.gfbmSlotMaxHeight || '320');

    var blocked = getBlockedTimes(field);
    var blackouts = getBlackoutDates(field);

    renderBlockedTimes(blocked);
    renderBlackoutDates(blackouts);
    updateCollapsibleSections();

    if ($('#gfbm_blackout_panel').attr('open') !== undefined) {
      initBlackoutPicker(blackouts);
    }
  }

  window.gfbmApplyHours = function (sourceDay, scope) {
    var targets = [];

    if (scope === 'weekdays') {
      targets = ['tuesday', 'wednesday', 'thursday', 'friday'];
    } else if (scope === 'all') {
      targets = dayKeys.slice();
    }

    if (!targets.length) {
      return false;
    }

    applyHoursFromDay(sourceDay, targets);
    return false;
  };

  $(document).on('change', '.gfbm-day-open', function () {
    var day = $(this).data('day');
    var open = $(this).val();
    var close = daySelect('gfbm_' + day + '_close').val();

    if (open === CLOSED) {
      updateDayHours(day, CLOSED, CLOSED);
      return;
    }

    if (close === CLOSED) {
      close = '17:00';
    }

    updateDayHours(day, open, close);
  });

  $(document).on('change', '.gfbm-day-close', function () {
    var day = $(this).data('day');
    var close = $(this).val();
    var open = daySelect('gfbm_' + day + '_open').val();

    if (close === CLOSED) {
      updateDayHours(day, CLOSED, CLOSED);
      return;
    }

    if (open === CLOSED) {
      open = '09:00';
    }

    updateDayHours(day, open, close);
  });

  $(document).on('click', '.gfbm-apply-hours', function (event) {
    event.preventDefault();
    event.stopPropagation();
    gfbmApplyHours($(this).data('source'), $(this).data('scope'));
  });

  $(document).on('click', '#gfbm_add_block', function () {
    var items = collectBlockedTimes();
    items.push({ day: 'all', start: '12:00', end: '13:00' });
    saveBlockedTimes(items);
    $('#gfbm_blocked_panel').attr('open', 'open');
  });

  $(document).on('click', '.gfbm-remove-block', function () {
    $(this).closest('.gfbm-editor-block-row').remove();
    saveBlockedTimes(collectBlockedTimes());
  });

  $(document).on('change', '.gfbm-editor-block-row select', function () {
    saveBlockedTimes(collectBlockedTimes());
  });

  $(document).on('click', '.gfbm-remove-blackout', function () {
    var date = $(this).closest('.gfbm-blackout-chip').data('date');
    var field = getField();
    var dates = getBlackoutDates(field).filter(function (item) {
      return item !== date;
    });

    saveBlackoutDates(dates);
  });

  $(document).on('toggle', '#gfbm_blackout_panel', function () {
    if (this.open) {
      var field = getField();
      initBlackoutPicker(getBlackoutDates(field));
    }
  });

  $(document).on('gform_load_field_settings', function (event, field) {
    blackoutPicker = null;
    loadFieldSettings(field);
  });

  registerFormSaveHooks();
  setTimeout(registerFormSaveHooks, 0);
  setTimeout(registerFormSaveHooks, 500);

  $(document).on('click', '#gform_save_form, .update-form, button[data-js="save-form"]', function () {
    if (typeof window.form !== 'undefined') {
      syncOpenFieldToForm(window.form);
    }
  });

  $(document).on('gform_field_added', function (event, field) {
    if (field.type !== 'booking_manager') {
      return;
    }

    field.label = field.label || GFBM_EDITOR.defaultLabel;
    field.gfbmBusinessHours = field.gfbmBusinessHours || JSON.stringify(defaultBusinessHours());
    field.gfbmBlackoutDates = field.gfbmBlackoutDates || '[]';
    field.gfbmBlockedTimes = field.gfbmBlockedTimes || '[]';
    field.gfbmDuration = field.gfbmDuration || '30';
    field.gfbmCapacity = field.gfbmCapacity || '1';
    field.gfbmBuffer = field.gfbmBuffer || '0';
    field.gfbmMinNotice = field.gfbmMinNotice || '0';
    field.gfbmMaxDays = field.gfbmMaxDays || '0';
    field.gfbmLayout = field.gfbmLayout || 'side-by-side';
    field.gfbmSlotColumns = field.gfbmSlotColumns || '2';
    field.gfbmSlotMaxHeight = field.gfbmSlotMaxHeight || '320';
  });
})(jQuery);
