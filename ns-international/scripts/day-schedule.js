jQuery(document).ready(function ($) {
  $(".ns-international-dayschedule").each(async function () {
    const $this = $(this);
    const $container = $this.find(".schedule");
    $container.attr("data-has-more", "true");

    const $filterSelect = $this.find(".filter-input");

    $filterSelect.data("callback", async function (code) {
      const $input = $(this).find("input[type='hidden']");
      const name = $input.attr("name");

      const currentCode = $this.data(name);
      if (currentCode !== code) {
        $this.data(name, code);
        await renderDaySchedule($this, $container);
      }
    });

    const $dateInput = $this.find(".js-date");
    $dateInput.find("input").data("callback", async function (date) {
      const currentDate = $this.data("date");
      if (currentDate !== date) {
        $this.data("date", date);
        await renderDaySchedule($this, $container);
      }
    });

    await renderDaySchedule($this, $container);
  });

  async function renderDaySchedule($this, $container) {
    const from = $this.data("from");
    const to = $this.data("to");
    const dateStr = $this.data("date");

    const date = dateStr.split("-").reverse().join("-");

    $container.empty();

    // check if after $container is a 'div > .loader', if not add one
    const loaderCheck = $container.next().find(".loader");
    if (loaderCheck.length === 0) {
      $container.after($('<div class="center pt-10"></div>').append(loader()));
    }

    try {
      await streamSearchData(from, to, date, $container, renderDataCallback);
    } catch (err) {
      console.error(err);
      const $error = $('<div class="nsi-error"></div>');
      $error.text(php_vars.text_values["error-no-data"]);
      $container.parent().find(".center").append($error);
      $container.parent().find(".center").find(".loader").remove();
    }
  }

  function renderDataCallback(date, $container, data, isFinished, from, to) {
    try {
      const id = `nsi-dayschedule-${date}-${from}-${to}`;
      const items = data.map((i) => {
        let price = "0.00";
        if (i.offers?.length > 0) {
          const lowestOffer = getLowestOffer(i);
          if (lowestOffer) {
            price = lowestOffer.totalPrice.amount.toFixed(2);
          }
        }

        return createDlJsonItem(
          to,
          from,
          i.itinerary.destination.name,
          i.itinerary.origin.name,
          date,
          price,
          i.itinerary.origin.departure.plannedLocalDateTime,
          i.itinerary.destination.arrival.plannedLocalDateTime
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
        const $error = $('<div class="nsi-error"></div>');
        $error.text(php_vars.text_values["error-no-data"]);
        $container.parent().find(".center").append($error);
        $container.parent().find(".center").find(".loader").remove();
      }
    });

    if (isFinished) {
      checkShowMore($container);
      $container.parent().find(".center").find(".loader").remove();
    }
  }

  function checkShowMore($container) {
    if ($container.find(".entry").length < 6) {
      $container.removeAttr("data-has-more");
    } else {
      const $more = $(
        `<div class="more"><span>${php_vars.text_values["show_all"]}</span></div>`
      );
      $more.click(function () {
        $container.removeAttr("data-has-more");
        $container.find(".more").remove();
      });

      $container.append($more);
    }
  }

  function createDayscheduleElement(entry, date) {
    const origin = entry.itinerary.origin;
    const destination = entry.itinerary.destination;

    const departure = origin.departure.plannedLocalDateTime
      .split("T")[1]
      .replace(":", "")
      .substring(0, 4);
    const arival = destination.arrival.plannedLocalDateTime
      .split("T")[1]
      .replace(":", "")
      .substring(0, 4);

    const url = getTrackingUrl(
      origin.code,
      destination.code,
      date.substring(0, 10).replace(/-/g, ""),
      departure,
      arival
    );

    const $node = $(`<a href="${url}" target="_blank" class="entry"></a>`);
    $node.attr("data-id", entry.id);

    const $left = $(`<div class="left"></div>`);
    const $midd = $(`<div class="midd"></div>`);

    const $trains = $(
      `<div class="trains">${getTransferDetails(
        entry.itinerary.modalities
      )}</div>`
    );
    $left.append($trains);

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

  function getTransferDetails(modalities) {
    const trains = modalities.filter((x) => x.type.toUpperCase() === "TRAIN");

    const transferText = php_vars.text_values["transfer_amount"];
    return (
      `<span class="transfers">${transferText.replace(
        "#",
        trains.length - 1
      )}</span>` +
      trains
        .map((x) => `<span class="train">${x.name}</span>`)
        .join(" <span class='separator-gt'>&gt;</span> ")
    );
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
    return offer?.totalPrice?.amount
      ? `<span class="price">${toFullPrice(offer?.totalPrice?.amount)}</span>`
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
