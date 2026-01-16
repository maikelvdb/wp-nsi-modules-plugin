jQuery(document).ready(function ($) {
  $(".ns-international-dayschedule").each(async function () {
    const $this = $(this);
    const $container = $this.find(".schedule");
    $container.attr("data-has-more", "true");
    const $buttons = $this.find(".js-day-part");

    const $filterSelect = $this.find(".filter-input");

    $filterSelect.data("callback", async function (code) {
      const $input = $(this).find("input[type='hidden']");
      const name = $input.attr("name");

      const currentCode = $this.data(name);
      if (currentCode !== code) {
        $this.data(name, code);
        $container.data("data", undefined);

        $buttons.removeClass("active");
        const $firstButton = $buttons.first();
        $firstButton.addClass("active");

        await renderDaySchedule($this, $container);
      }
    });

    const $dateInput = $this.find(".js-date-self");
    const $dateInputSelector = $dateInput.find("input");
    $dateInputSelector.datepicker({
      dateFormat: "dd-mm-yy",
      minDate: new Date(),
      onSelect: async function (dateText, $datepicker) {
        if (dateText) {
          $($datepicker.input).removeAttr("invalid");
        }

        const currentDate = $this.data("date");
        if (currentDate !== dateText) {

          $this.data("date", dateText);

          $buttons.removeClass("active");
          const $firstButton = $buttons.first();
          $firstButton.addClass("active");

          await renderDaySchedule($this, $container);
        }
      },
    });

    $buttons.click(async function () {
      const data = $container.data("data");
      if (!data) {
        return;
      }

      const $button = $(this);
      if ($button.hasClass("active")) {
        return;
      }

      const start = $button.data("start")
        ? parseInt($button.data("start"), 10)
        : null;
      const end = $button.data("end")
        ? parseInt($button.data("end"), 10)
        : null;

      const filteredData = data.filter((i) => {
        const departureTime = new Date(
          i.departure.time ?? i.departure.plannedTime
        );
        const hour = departureTime.getHours();

        return (
          (start === null || hour >= start) && (end === null || hour < end)
        );
      });

      $buttons.removeClass("active");
      $button.addClass("active");
      $container.empty();
      if (filteredData.length === 0) {
        $(".nsi-error").remove();

        const $error = $('<div class="nsi-error"></div>');
        $error.text(php_vars.text_values["dayschedule-error-no-data"]);
        $container.append($error);
        return;
      }

      const from = $this.data("from");
      const to = $this.data("to");
      const dateStr = $this.data("date");
      const dateValue = dateStr.split("-").reverse().join("-");
      renderDataCallback(dateValue, $container, filteredData, from, to);
    });

    $container.data("data", undefined);
    await renderDaySchedule($this, $container);
  });

  async function renderDaySchedule($this, $container) {
    const from = $this.data("from");
    const to = $this.data("to");
    const dateStr = $this.data("date");

    console.log('Rendering day schedule for:', from, to, dateStr);

    $container.removeData("data");

    let date = dateStr.split("-").reverse().join("-");
    date += `T00:00:00`;

    $container.empty();

    // check if after $container is a 'div > .loader', if not add one
    const loaderCheck = $container.next().find(".loader");
    if (loaderCheck.length === 0) {
      $container.after($('<div class="center pt-10"></div>').append(loader()));
    }

    try {
      const result = await searchSchedule(from, to, date);
      collectDataCallback(date, $container, result?.journeys ?? [], from, to);
    } catch (err) {
      console.error(err);

      $(".nsi-error").remove();
      const $error = $('<div class="nsi-error"></div>');
      $error.text(php_vars.text_values["error-no-data"]);
      $container.parent().find(".center").append($error);
      $container.parent().find(".center").find(".loader").remove();
    }
  }

  function collectDataCallback(date, $container, data, from, to) {
    const containerData = $container.data("data");
    const isFirst = containerData === undefined;

    $container.removeData("data");
    $container.data("data", data);

    if (isFirst) {
      const filteredData = data.filter((i) => {
        const departureTime = new Date(
          i.departure.time ?? i.departure.plannedTime
        );
        return (
          departureTime.getHours() >= 0 &&
          departureTime.getHours() < 12 &&
          departureTime.getDate() === new Date(date).getDate()
        );
      });

      $container.parent().find(".center").find(".loader").remove();
      renderDataCallback(date, $container, filteredData, from, to);
    }
  }

  function renderDataCallback(date, $container, data, from, to) {
    try {
      const id = `nsi-dayschedule-${date}-${from}-${to}`;
      const items = data.map((i) => {
        let price = "0.00";
        if (i.price) {
          price = i.price.toFixed(2);
        }

        return createDlJsonItem(
          to,
          from,
          i.departure.name,
          i.arrival.name,
          date,
          price,
          i.departure.time ?? i.departure.plannedTime,
          i.arrival.time ?? i.arrival.plannedTime
        );
      });

      renderDlJson(items, id);
    } catch (err) {
      console.error("Error rendering data callback:", err);
    }

    data.forEach((entry) => {
      try {
        const $node = createDayscheduleElement(entry, date);
        $container.append($node);
      } catch (err) {
        console.error(err);
        $(".nsi-error").remove();

        const $error = $('<div class="nsi-error"></div>');
        $error.text(php_vars.text_values["error-no-data"]);
        $container.parent().find(".center").append($error);
        $container.parent().find(".center").find(".loader").remove();
      }
    });
  }

  function createDayscheduleElement(entry, date) {
    const origin = entry.departure;
    const destination = entry.arrival;

    const departure = (origin.time ?? origin.plannedTime)
      .split("T")[1]
      .replace(":", "")
      .substring(0, 4);
    const arival = (destination.time ?? destination.plannedTime)
      .split("T")[1]
      .replace(":", "")
      .substring(0, 4);

    const url = getTrackingUrl(
      origin.beneCode,
      destination.beneCode,
      date.substring(0, 10).replace(/-/g, ""),
      departure,
      arival
    );

    const $node = $(`<a href="${url}" target="_blank" class="entry"></a>`);
    $node.attr("data-id", entry.id);

    const $top = $(`<div class="top"></div>`);
    const $left = $(`<div class="left"></div>`);
    const $midd = $(`<div class="midd"></div>`);

    const $trains = $(
      `<div class="trains">${getTransferDetails(
        entry.stops
      )}</div>`
    );
    $top.append($trains);

    const $time = $(
      `<div class="time">
        <div class="start">${getTime(
          (origin.time ?? origin.plannedTime)
        )}</div>
        <div class="line"></div>
        <div class="duration">${entry.travelTime}</div>
        <div class="line"></div>
        <div class="end">${getTime(
          (destination.time ?? destination.plannedTime)
        )}</div>
      </div>`
    );
    $left.append($time);

    $node.append($top);
    $node.append($left);

    const $price = $(`<div class="price">${getOfferPrice(entry.price)}</div>`);
    $midd.append($price);
    $node.append($midd);

    const $footer = $(`<div class="right"></div>`);
    const $nsiButton = $(
      `<button class="nsi-button nsi-cta">` +
        php_vars.text_values["search_tickets"] +
        // `<svg width="20" height="20" class="search-icon" role="img" viewBox="2 9 20 5" focusable="false" aria-label="Search"><path class="search-icon-path" d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"></path></svg>` +
        `</button>`
    );
    const $dbButton = $(
      `<a href="https://www.awin1.com/awclick.php?gid=372445&mid=14964&awinaffid=374071&linkid=2476100&clickref=" class="db-button nsi-cta" target="_blank">` +
        php_vars.text_values["search_tickets"] +
        `<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="34" height="24"><defs><path id="a" d="M16.58 0h16.58v23.43H0V0h16.58z"/></defs><g fill="none" fill-rule="evenodd"><mask id="b" fill="#fff"><use xlink:href="#a"/></mask><path fill="#F01414" d="M29.83 0H3.33A3.32 3.32 0 000 3.34v16.72a3.35 3.35 0 003.33 3.37h26.5a3.35 3.35 0 003.33-3.37V3.34A3.32 3.32 0 0029.83 0" mask="url(#b)"/><path fill="#FFF" d="M30.74 20.06c0 .53-.39.96-.91.96H3.33c-.52 0-.9-.43-.9-.96V3.34c0-.54.38-.96.9-.96h26.5c.52 0 .91.42.91.96v16.72z"/><path fill="#F01414" d="M12.56 11.96c0-3.09-.31-5.5-3.61-5.5H8.2V16.9h1.3c1.93 0 3.05-1.56 3.05-4.94m-2.42 7.42H4.56V4.05h5.58c3.93 0 6.1 2.48 6.1 7.6 0 4.43-1.47 7.7-6.1 7.73m12.8-2.48h-1.49v-3.94h1.6c1.9 0 2.36 1.11 2.36 1.97 0 1.97-1.88 1.97-2.46 1.97zM21.46 6.42h1.15c1.64 0 2.29.57 2.29 1.84 0 1.01-.72 1.86-1.99 1.86h-1.45v-3.7zm4.43 4.94a3.77 3.77 0 002.66-3.57c0-.32-.07-3.74-4.34-3.74h-6.36v15.33h5.37c1.42 0 5.86 0 5.86-4.3 0-1.08-.44-3.11-3.2-3.72z"/></g></svg>` +
        `</a>`
    );
    $footer.append($nsiButton);
    $footer.append($dbButton);

    $node.append($footer);

    return $node;
  }

  function getTransferDetails(stops) {
    if (!stops) {
      return php_vars.text_values["no_transfer"];
    }
    
    const transfer_amount_txt = php_vars.text_values["transfer_amount"];
    return transfer_amount_txt.replace('#', stops);
  }

  function getOfferPrice(price) {
    return price
      ? `<span class="price">${toFullPrice(price)}</span>`
      : "";
    // : `<span class="view">${php_vars.text_values["view_prices"]}</span>`;
  }

  function getTime(date) {
    const dt = new Date(date);
    return `${dt
      .getHours()
      .toString()
      .padStart(2, "0")}:${dt.getMinutes().toString().padStart(2, "0")}`;
  }

  function loader() {
    return $('<div class="loader"></div>');
  }
});
