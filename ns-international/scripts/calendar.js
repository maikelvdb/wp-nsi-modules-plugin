jQuery(document).ready(function ($) {
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
      const $slider = $container.find(".ns-calendar-slider");
      const current = $this.attr("data-current-index") ?? 0;
      const currentIndex = parseInt(current, 10);

      $slider[0].style.transform = `translateX(calc(${currentIndex * -100}% - ${
        currentIndex * 21
      }px))`;
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

    const currIndex = $this.attr("data-current-index");
    if (currIndex !== undefined && currIndex > 0) {
      slideCalendar();
    }

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

    try {
      const data = await loadCalendar(from, to, minDate);
      const allDates = data.calendarEntries.map(
        (x) => new Date(x.calendarDate)
      );

      const maxDate = new Date(Math.max.apply(null, allDates));
      const maxYear = maxDate.getFullYear();
      const maxMonth = maxDate.getMonth();

      let ldJsonCollection = [];
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

        $calendar.find(".row:not(.header) > .cell").each(function () {
          const $cell = $(this);
          const date = $cell.data("date");
          const realDate = new Date(date);
          const currentMonth = realDate.getMonth();

          const dateData = data.calendarEntries.find(
            (entry) => entry.calendarDate === date
          );

          if (!dateData || month !== currentMonth) {
            $cell.addClass("disabled");
            $cell.find(".loader").remove();

            $cell.attr("href", "javascript:void(0)");
            $cell.attr("target", "_self");
            $cell.css("cursur", "default");
            return;
          }

          const $price = $cell.find(".price");
          $price.find(".loader").remove();

          if (!dateData.price?.lowest) {
            $cell.addClass("search-icon");
            $cell
              .find(".price")
              .append(
                $(
                  `<svg width="20" height="20" class="search-icon" role="img" viewBox="2 9 20 5" focusable="false" aria-label="Search"><path class="search-icon-path" d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"></path></svg>`
                )
              );
            return;
          }

          let price = toPrice(dateData.price.lowest);
          price = price.replace("€", "").trim();
          $price.append(`<div class="current">${price}</div>`);
          $price.addClass(dateData.price.category.toLowerCase());

          $cell.find(".price").find("svg").remove();

          const departure = dateData.firstTravelConnection?.departureDate;
          const arrival = dateData.firstTravelConnection?.arrivalDate;
          const ldItem = createDlJsonItem(
            to,
            from,
            data.to.name,
            data.from.name,
            date,
            price,
            departure,
            arrival
          );

          ldJsonCollection.push(ldItem);
        });
      });

      if (ldJsonCollection.length > 0) {
        const id = `ldjson-calendar-${from}-${to}`;
        renderDlJson(ldJsonCollection, id);
      }
    } catch (error) {
      console.error("Error loading calendar data:", error);

      $calendars.each(function () {
        const $calendar = $(this);
        $calendar.find(".row:not(.header) > .cell").each(function () {
          const $cell = $(this);

          $cell.find(".loader").remove();

          if ($cell.hasClass("disabled")) {
            return;
          }

          $cell.addClass("search-icon");
          const $price = $cell.find(".price");
          if ($price.find(".current").length === 0) {
            $price.append(
              $(
                `<svg width="20" height="20" class="search-icon" role="img" viewBox="2 9 20 5" focusable="false" aria-label="Search"><path class="search-icon-path" d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"></path></svg>`
              )
            );
          }
        });
      });
    }
  });
});
