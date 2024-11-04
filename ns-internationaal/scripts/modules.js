const intervalStates = [];

(function ($) {
  $(document).ready(function () {
    $(".js-date").each(function () {
      setDatePicker($(this));
    });

    $(".ns-international_searchform").each(function () {
      const $this = $(this);
      const $form = $this.find("form");
      $form.submit(function (event) {
        event.preventDefault();

        const $dateInput = $this.find('input[name="date"]');
        const $fromDisplay = $this.find('input[name="from_display"]');
        const $toDisplay = $this.find('input[name="to_display"]');

        $dateInput.attr("invalid", false);
        $fromDisplay.attr("invalid", false);
        $toDisplay.attr("invalid", false);

        let valid = true;
        const fromCode = $this.find('input[name="from"]').val();
        if (!fromCode) {
          $fromDisplay.attr("invalid", true);
          valid = false;
        }

        const toCode = $this.find('input[name="to"]').val();
        if (!toCode) {
          $toDisplay.attr("invalid", true);
          valid = false;
        }

        let date = $dateInput.val();
        if (!date) {
          $dateInput.attr("invalid", true);
          valid = false;
        }

        if (!valid) {
          return;
        }

        date = date.split("-").reverse().join("");
        const url = `https://www.nsinternational.com/traintracker/?tt=${php_vars.tracking_code}&r=%2Fnl%2Ftreintickets-v3%2F%23%2Fsearch%2F${fromCode}%2F${toCode}%2F${date}`;

        window.open(url, "_blank");
      });
    });

    $(".ns-international_calendar").each(async function () {
      const $this = $(this);
      const from = $(this).data("from");
      const to = $(this).data("to");
      const minDate = $(this).data("min-date");

      const $next = $(this).find(".next");
      const $prev = $(this).find(".prev");
      const $container = $(this).find(".ns-calendar-container");
      const $calendars = $container.find(".ns-calendar");
      let maxIndex = -1;

      const slideCalendar = function () {
        const current = $this.attr("data-current-index") ?? 0;
        const currentIndex = parseInt(current, 10);

        $container[0].style.transform = `translateX(calc(${
          currentIndex * -100
        }% - ${currentIndex * 10}px))`;
      };

      $next.click(async function () {
        const currentIndex = $this.data("current-index");
        const newIndex = currentIndex + 1;
        $this.attr("data-current-index", newIndex);
        $this.data("current-index", newIndex);

        const dateName = $this
          .find('[data-index="' + newIndex + '"]')
          .data("date-str");

        $this.find(".js-active-date").text(dateName);

        if (newIndex == maxIndex) {
          $this.attr("is-last", true);
        }

        slideCalendar();
      });

      $prev.click(async function () {
        const currentIndex = $this.data("current-index");
        const newIndex = currentIndex - 1;
        $this.attr("data-current-index", newIndex);
        $this.data("current-index", newIndex);

        const dateName = $this
          .find('[data-index="' + newIndex + '"]')
          .data("date-str");

        $this.find(".js-active-date").text(dateName);

        $this.removeAttr("is-last");

        slideCalendar();
      });

      const data = await loadCalendar(from, to, minDate);
      const allDates = data.calendarEntries.map(
        (x) => new Date(x.calendarDate)
      );
      const maxDate = new Date(Math.max.apply(null, allDates));
      const maxYear = maxDate.getFullYear();
      const maxMonth = maxDate.getMonth();

      $calendars.each(function () {
        const $calendar = $(this);
        const dt = new Date($calendar.data("date"));
        const year = dt.getFullYear();
        const month = dt.getMonth();

        if (year >= maxYear && month > maxMonth) {
          $calendar.remove();
          return;
        }

        maxIndex++;

        $calendar.find(".row:not(.header) > .cell").each(function (index) {
          const $cell = $(this);
          const date = $cell.data("date");
          const realDate = new Date(date);
          const currentMonth = realDate.getMonth();

          const dateDate = data.calendarEntries.find(
            (entry) => entry.calendarDate === date
          );

          if (!dateDate || month !== currentMonth) {
            $cell.addClass("disabled");
            $cell.find(".loader").remove();

            $cell.attr("href", "javascript:void(0)");
            $cell.css("cursur", "default");

            return;
          }

          const $price = $cell.find(".price");
          $price.find(".loader").remove();

          if (!dateDate.price?.lowest) {
            $cell.addClass("disabled");
            return;
          }

          $price.append(`<div class="current">${dateDate.price.lowest}</div>`);
          $price.addClass(dateDate.price.category.toLowerCase());
        });
      });
    });

    $(".ns-international-dayschedule").each(async function () {
      const $this = $(this);
      const $container = $this.find(".schedule");

      const $filterSelect = $this.find(".filter-input");
      const $filterHidden = $filterSelect.find("input[type='hidden']");
      const $fitlerInput = $filterSelect.find("input[type='text']");
      const code = $filterHidden.val();
      if (code) {
        const name = await getStationByCode(code);
        $fitlerInput.val(name);
      }

      $filterSelect.data("callback", async function (code) {
        $this.data("from", code);
        await renderDaySchedule($this, $container);
      });

      const $dateInput = $this.find(".js-date");
      $dateInput.find("input").data("callback", async function (date) {
        $this.data("date", date);

        await renderDaySchedule($this, $container);
      });

      await renderDaySchedule($this, $container);
    });

    $(".filter-input").each(async function () {
      const $select = $(this);
      const $input = $select.find("input[type='text']");
      const $codeInput = $select.find("input[type='hidden']");

      const code = $codeInput.val();
      if (code) {
        const name = await getStationByCode(code);
        $input.val(name);
      }

      $select.click(function (event) {
        if (event.target.tagName === "INPUT") {
          return;
        }

        $input.focus();
      });

      $input.focus(renderStationsEvent);
      $input.keyup(renderStationsEvent);

      $input.blur(function () {
        const name = $input.data("name");
        delete intervalStates[name];

        setTimeout(() => {
          $(this).parent().find(".options").remove();
        }, 200);
      });
    });
  });

  async function renderDaySchedule($this, $container) {
    const from = $this.data("from");
    const to = $this.data("to");
    const dateStr = $this.data("date");

    const date = dateStr.split("-").reverse().join("-") + "T00:00:00";

    $container.empty();

    $container.after($('<div class="center pt-10"></div>').append(loader()));
    await fetchAllSearchData(from, to, date, $container);
  }

  async function fetchAllSearchData(from, to, date, $container) {
    const search = await searchSchedule(from, to, date);
    appendSearchDateToView(search.data, $container);
    if (search.fromCache) {
      $container.parent().find(".center").find(".loader").remove();
      return;
    }

    if (search.scroll) {
      recursiveLoadScroll(
        search.scroll,
        $container,
        search.sessionId,
        from,
        to,
        date
      );
    }
  }

  function recursiveLoadScroll(scroll, $container, sessionId, from, to, date) {
    loadSearchScroll(scroll.id, scroll.token, sessionId, from, to, date).then(
      (search) => {
        appendSearchDateToView(search.data, $container);

        if (search.scroll) {
          recursiveLoadScroll(
            search.scroll,
            $container,
            sessionId,
            from,
            to,
            date
          );
        } else {
          $container.parent().find(".center").find(".loader").remove();
        }
      }
    );
  }

  function appendSearchDateToView(data, $container) {
    data.forEach((entry) => {
      if ($container.find(`.entry[data-id="${entry.id}"]`).length) {
        return;
      }

      const $element = createDayscheduleElement(entry);
      $container.append($element);
    });
  }

  function renderStationsEvent() {
    const $input = $(this);
    const name = $input.data("name");

    if (intervalStates[name]) {
      window.clearInterval(intervalStates[name]);
      delete intervalStates[name];
      return;
    }

    const value = $(this).val();
    intervalStates[name] = window.setTimeout(
      () => renderStationOptions(value, $(this).parent()),
      100
    );
  }

  async function renderStationOptions(value, $wrapper) {
    const $input = $wrapper.find("input[type='text']");
    if (!$input.is(":focus")) {
      return;
    }

    await new Promise((resolve) =>
      setTimeout(() => {
        $wrapper.find(".options").remove();
        resolve();
      }, 100)
    );

    if (value.length < 3) {
      return;
    }

    const $list = $('<div class="options"></div>');

    const stations = await searchStations(value);

    stations.forEach((station) => {
      const $option = $(`
        <div class="option" data-value="${station.beneCode}" data-display="${station.name}" title="${station.name}">
          <span class="name">${station.name}</span>
        </div>
      `);

      $option.click(function () {
        $wrapper = $(this).closest(".filter-input");
        $invalid = $wrapper.find("[invalid]");
        if ($invalid.length) {
          $invalid.removeAttr("invalid");
        }

        const $valueInput = $wrapper.find("input[type='text']");
        $valueInput.val(station.name);
        setTimeout(() => $valueInput.blur(), 0);

        $wrapper.find("input[type='hidden']").val(station.beneCode);
        const callback = $wrapper.data("callback");
        if (callback) {
          callback.call($wrapper, station.beneCode);
        }
      });

      $list.append($option);
    });

    $wrapper.append($list);
  }

  function setDatePicker($elem) {
    var $input = $elem.find("input");
    $input.datepicker({
      dateFormat: "dd-mm-yy",
      minDate: new Date(),
      onSelect: function (dateText, $this) {
        if (dateText) {
          $($this.input).removeAttr("invalid");
        }

        const callback = $this.input.data("callback");
        if (callback) {
          callback.call($this.input, dateText);
        }
      },
    });
  }

  function createDayscheduleElement(entry) {
    const $node = $(`<div class="entry"></div>`);
    $node.attr("data-id", entry.id);

    const $left = $(`<div class="left"></div>`);
    const $right = $(`<div class="right"></div>`);

    const origin = entry.itinerary.origin;
    const destination = entry.itinerary.destination;
    // const $date = $(`<div class="date">${}</div>`);
    const $time = $(
      `<div class="time">
        <div class="start">${getTime(
          origin.departure.plannedLocalDateTime
        )}</div>
        <div class="line"></div>
        <div class="duration">${niceDuration(entry.itinerary.duration)}</div>
        <div class="line"></div>
        <div class="end">${getTime(
          destination.arrival.plannedLocalDateTime
        )}</div>
      </div>`
    );
    $left.append($time);

    $node.append($left);

    const offer = getLowestOffer(entry);
    const $price = $(`<div class="price">${getOfferPrice(offer)}</div>`);
    $right.append($price);
    $node.append($right);

    return $node;
  }

  function niceDuration(duration) {
    return duration.replace("PT", "").replace("H", "u").replace("M", "m");
  }

  function getLowestOffer(entry) {
    if (!entry.offers?.length) {
      return null;
    }

    let lowest = null;
    for (const offer of entry.offers) {
      if (offer.totalPrice.amount > 0) {
        lowest = offer;
        break;
      }
    }

    return lowest;
  }

  function getOfferPrice(offer) {
    return offer?.totalPrice?.amount || "ntv";
  }

  function getTime(date) {
    const dt = new Date(date);
    return `${dt.getHours().toString().padStart(2, "0")}:${dt
      .getMinutes()
      .toString()
      .padStart(2, "0")}`;
  }

  function loader() {
    return $('<div class="loader"></div>');
  }
})(jQuery);
