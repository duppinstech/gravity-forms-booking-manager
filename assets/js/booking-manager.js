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
      onReady: function () {
        scheduleHeightSync(field);
        observeFieldResize(field);
      },
      onMonthChange: function () {
        scheduleHeightSync(field);
      },
      onYearChange: function () {
        scheduleHeightSync(field);
      }
    });

    scheduleHeightSync(field);
    observeFieldResize(field);

    if (dateInput.value) {
      loadSlots(field, dateInput.value);
    }
  }

  function initAllBookingManagerFields() {
    document.querySelectorAll('.gfbm-field').forEach(initBookingManagerField);
  }

  function scheduleInit() {
    window.requestAnimationFrame(initAllBookingManagerFields);
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
