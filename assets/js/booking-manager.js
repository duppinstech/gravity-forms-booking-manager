(function ($) {
  'use strict';

  function parseJsonAttr(element, attr, fallback) {
    var raw = element.getAttribute(attr);

    if (!raw) {
      return fallback;
    }

    try {
      var parsed = JSON.parse(raw);
      return Array.isArray(parsed) ? parsed : fallback;
    } catch (e) {
      return fallback;
    }
  }

  function formatDateValue(date) {
    var month = String(date.getMonth() + 1).padStart(2, '0');
    var day = String(date.getDate()).padStart(2, '0');
    return date.getFullYear() + '-' + month + '-' + day;
  }

  function formatTimeLabel(value) {
    var parts = value.split(':');
    var hour = parseInt(parts[0], 10);
    var minute = parts[1];
    var suffix = hour >= 12 ? 'PM' : 'AM';
    var displayHour = hour % 12;

    if (displayHour === 0) {
      displayHour = 12;
    }

    return displayHour + ':' + minute + ' ' + suffix;
  }

  function t(key, fallback) {
    if (window.GFBM && GFBM.strings && GFBM.strings[key]) {
      return GFBM.strings[key];
    }

    return fallback;
  }

  function fill(template, values) {
    return String(template).replace(/\{(\w+)\}/g, function (match, key) {
      return Object.prototype.hasOwnProperty.call(values, key) ? values[key] : match;
    });
  }

  function formatLongDate(dateStr) {
    var parts = String(dateStr).split('-');
    var date = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));

    if (isNaN(date.getTime())) {
      return dateStr;
    }

    try {
      return new Intl.DateTimeFormat(undefined, {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
        year: 'numeric'
      }).format(date);
    } catch (e) {
      return dateStr;
    }
  }

  function updateSelection(field) {
    var summary = field.querySelector('.gfbm-selection');
    var dateInput = field.querySelector('.gfbm-date-input');
    var timeInput = field.querySelector('.gfbm-time-input');

    if (!summary || !dateInput || !timeInput) {
      return;
    }

    if (dateInput.value && timeInput.value) {
      summary.textContent = fill(t('dateTime', 'You are selecting a booking for {date} at {time}. Click Submit to request this time.'), {
        date: formatLongDate(dateInput.value),
        time: formatTimeLabel(timeInput.value)
      });
      return;
    }

    if (dateInput.value) {
      summary.textContent = fill(t('dateOnly', 'You are selecting {date}. Choose a time, then click Submit to request this booking.'), {
        date: formatLongDate(dateInput.value)
      });
      return;
    }

    summary.textContent = t('choose', 'Choose a date and time. Click Submit to request the booking.');
  }

  function mountYearSelect(instance) {
    var month = instance.calendarContainer.querySelector('.flatpickr-current-month');
    var wrapper = month && month.querySelector('.numInputWrapper');
    var input = wrapper && wrapper.querySelector('input.cur-year');

    if (!month || !wrapper || !input) {
      return;
    }

    var select = wrapper.querySelector('.gfbm-year-select');

    if (!select) {
      var minYear = instance.config.minDate ? instance.config.minDate.getFullYear() : instance.currentYear;
      var maxYear = instance.config.maxDate ? instance.config.maxDate.getFullYear() : minYear + 5;

      if (maxYear < minYear) {
        maxYear = minYear;
      }

      select = document.createElement('select');
      select.className = 'gfbm-year-select';
      select.setAttribute('aria-label', t('year', 'Year'));

      var year;
      for (year = minYear; year <= maxYear; year += 1) {
        var option = document.createElement('option');
        option.value = String(year);
        option.textContent = String(year);
        select.appendChild(option);
      }

      input.setAttribute('tabindex', '-1');
      input.setAttribute('aria-hidden', 'true');
      wrapper.insertBefore(select, input);

      select.addEventListener('change', function () {
        var nextYear = parseInt(select.value, 10);

        if (!isNaN(nextYear) && nextYear !== instance.currentYear) {
          instance.changeYear(nextYear);
        }
      });
    }

    if (select.value !== String(instance.currentYear)) {
      select.value = String(instance.currentYear);
    }
  }

  function setMessage(slotsWrap, message) {
    slotsWrap.innerHTML = '';
    var note = document.createElement('p');
    note.className = 'gfbm-message';
    note.setAttribute('role', 'status');
    note.textContent = message;
    slotsWrap.appendChild(note);
  }

  function isSideBySideLayout(field) {
    return !field.classList.contains('gfbm-layout-stacked');
  }

  function syncColumnHeights(field) {
    if (!isSideBySideLayout(field)) {
      return;
    }

    var calendarWrap = field.querySelector('.gfbm-calendar-wrap');
    var slots = field.querySelector('.gfbm-slots');
    var calendar = calendarWrap && calendarWrap.querySelector('.flatpickr-calendar');

    if (!calendarWrap || !slots || !calendar) {
      return;
    }

    var height = Math.max(calendar.offsetHeight, calendarWrap.offsetHeight);

    if (height > 0) {
      slots.style.height = height + 'px';
      slots.style.minHeight = height + 'px';
      slots.style.maxHeight = height + 'px';
    }
  }

  function scheduleHeightSync(field) {
    window.requestAnimationFrame(function () {
      syncColumnHeights(field);
      window.requestAnimationFrame(function () {
        syncColumnHeights(field);
      });
    });
  }

  function observeFieldResize(field) {
    if (field._gfbmResizeObserver) {
      field._gfbmResizeObserver.disconnect();
    }

    if (typeof ResizeObserver === 'undefined') {
      return;
    }

    var calendarWrap = field.querySelector('.gfbm-calendar-wrap');

    if (!calendarWrap) {
      return;
    }

    field._gfbmResizeObserver = new ResizeObserver(function () {
      syncColumnHeights(field);
    });

    field._gfbmResizeObserver.observe(calendarWrap);
  }

  function loadSlots(field, dateStr) {
    var formId = field.getAttribute('data-form-id');
    var fieldId = field.getAttribute('data-field-id');
    var slotsWrap = field.querySelector('.gfbm-slots');
    var dateInput = field.querySelector('.gfbm-date-input');
    var timeInput = field.querySelector('.gfbm-time-input');

    if (!slotsWrap || !dateInput || !timeInput || !formId || !fieldId || !dateStr) {
      return;
    }

    dateInput.value = dateStr;
    timeInput.value = '';
    updateSelection(field);

    setMessage(slotsWrap, t('loading', 'Loading available times...'));

    var formData = new FormData();
    formData.append('action', 'gfbm_get_slots');
    formData.append('nonce', GFBM.nonce);
    formData.append('formId', formId);
    formData.append('fieldId', fieldId);
    formData.append('date', dateStr);

    fetch(GFBM.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: formData
    })
      .then(function (response) {
        return response.json();
      })
      .then(function (response) {
        if (!response.success) {
          setMessage(
            slotsWrap,
            response.data && response.data.message
              ? response.data.message
              : t('error', 'Unable to load available times. Please try again.')
          );
          scheduleHeightSync(field);
          return;
        }

        var slots = response.data.slots || [];

        if (!slots.length) {
          setMessage(slotsWrap, t('none', 'No available times for this date.'));
          scheduleHeightSync(field);
          return;
        }

        slotsWrap.innerHTML = '';
        slotsWrap.setAttribute('role', 'group');
        slotsWrap.setAttribute('aria-label', t('times', 'Available times'));

        slots.forEach(function (slot) {
          var button = document.createElement('button');

          button.type = 'button';
          button.className = 'gfbm-slot';
          button.setAttribute('data-time', slot);
          button.setAttribute('aria-pressed', 'false');
          button.textContent = formatTimeLabel(slot);

          button.addEventListener('click', function () {
            slotsWrap.querySelectorAll('.gfbm-slot').forEach(function (selectedButton) {
              selectedButton.classList.remove('is-selected');
              selectedButton.setAttribute('aria-pressed', 'false');
            });

            button.classList.add('is-selected');
            button.setAttribute('aria-pressed', 'true');
            timeInput.value = slot;
            updateSelection(field);
          });

          slotsWrap.appendChild(button);
        });

        scheduleHeightSync(field);
      })
      .catch(function () {
        setMessage(slotsWrap, t('error', 'Unable to load available times. Please try again.'));
        scheduleHeightSync(field);
      });
  }

  function destroyCalendarInstance(field) {
    if (field._gfbmFlatpickr) {
      field._gfbmFlatpickr.destroy();
      field._gfbmFlatpickr = null;
    }

    if (field._gfbmResizeObserver) {
      field._gfbmResizeObserver.disconnect();
      field._gfbmResizeObserver = null;
    }
  }

  function isDayDisabled(date, disabledDays, blackoutDates) {
    var day = date.getDay();
    var i;

    for (i = 0; i < disabledDays.length; i += 1) {
      if (parseInt(disabledDays[i], 10) === day) {
        return true;
      }
    }

    return blackoutDates.indexOf(formatDateValue(date)) !== -1;
  }

  function initBookingManagerField(field) {
    if (typeof flatpickr === 'undefined') {
      return;
    }

    var calendarInput = field.querySelector('.gfbm-calendar-input');
    var dateInput = field.querySelector('.gfbm-date-input');
    var calendarWrap = field.querySelector('.gfbm-calendar-wrap');

    if (!calendarInput || !dateInput || !calendarWrap) {
      return;
    }

    destroyCalendarInstance(field);

    var blackoutDates = parseJsonAttr(field, 'data-blackout-dates', []);
    var disabledDays = parseJsonAttr(field, 'data-disabled-days', []);

    var minDate = field.getAttribute('data-min-date') || 'today';
    var maxDate = field.getAttribute('data-max-date') || '';

    field._gfbmFlatpickr = flatpickr(calendarInput, {
      inline: true,
      static: true,
      appendTo: calendarWrap,
      minDate: minDate,
      maxDate: maxDate || null,
      dateFormat: 'Y-m-d',
      defaultDate: dateInput.value || null,
      disable: [
        function (date) {
          return isDayDisabled(date, disabledDays, blackoutDates);
        }
      ],
      onChange: function (selectedDates) {
        if (!selectedDates.length) {
          return;
        }

        var dateStr = formatDateValue(selectedDates[0]);
        dateInput.value = dateStr;
        loadSlots(field, dateStr);
      },
      onReady: function (selectedDates, dateStr, instance) {
        mountYearSelect(instance);
        scheduleHeightSync(field);
        observeFieldResize(field);
      },
      onMonthChange: function (selectedDates, dateStr, instance) {
        mountYearSelect(instance);
        scheduleHeightSync(field);
      },
      onYearChange: function (selectedDates, dateStr, instance) {
        mountYearSelect(instance);
        scheduleHeightSync(field);
      }
    });

    scheduleHeightSync(field);
    observeFieldResize(field);

    updateSelection(field);

    if (dateInput.value) {
      loadSlots(field, dateInput.value);
    }
  }

  function initAllBookingManagerFields() {
    document.querySelectorAll('.gfbm-field').forEach(initBookingManagerField);
  }

  function scheduleInit() {
    initAllBookingManagerFields();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', scheduleInit);
  } else {
    scheduleInit();
  }

  window.addEventListener('load', scheduleInit);
  window.addEventListener('resize', function () {
    document.querySelectorAll('.gfbm-field').forEach(syncColumnHeights);
  });

  if ($) {
    $(document).on('gform_post_render', scheduleInit);
  }
})(window.jQuery);
