const API_URL = "https://nsi-api.goedkoop-treinkaartje.nl/api";
// const API_URL = "https://nsi.vandenbosch.dev/api";

async function searchStations(name) {
  if (name.length < 3) {
    return [];
  }

  const url = `${API_URL}/stations/${name}`;

  try {
    const response = await fetchData(url);
    const data = await response.json();

    return data;
  } catch {
    return null;
  }
}

async function getStationByCode(code) {
  const url = `${API_URL}/stations/bene/${code}`;

  const response = await fetchData(url);
  const data = await response.json();
  if (!data) {
    return "";
  }

  return data.name;
}

async function loadCalendar(from, to, date) {
  const url = `${API_URL}/calendar/${from}/${to}`;

  const response = await fetchData(url);
  const data = await response.json();

  // get from for response header 'x-station-origin'
  const fromHeader = response.headers.get("x-station-origin");
  const toHeader = response.headers.get("x-station-destination");
  const fromStation = JSON.parse(fromHeader);
  const toStation = JSON.parse(toHeader);

  return { ...data, from: fromStation, to: toStation };
}

async function searchSchedule(from, to, date) {
  const url = `${API_URL}/search/${from}/${to}/${date}`;

  try {
    const response = await fetchData(url);
    const fromHeader = response.headers.get("x-station-origin");
    const toHeader = response.headers.get("x-station-destination");
    const fromStation = JSON.parse(fromHeader);
    const toStation = JSON.parse(toHeader);

    const data = await response.json();
    return {
      journeys: data, from: fromStation, to: toStation
    };
  } catch {
    return null;
  }
}

const slash = "%2F";
function getBaseTrackingUrl() {
  return `https://www.nsinternational.com/traintracker/?tt=${php_vars.tracking_code}&r=%2Fnl%2Ftreintickets-v3%2F%23%2Fsearch%2F`;
}

function getTrackingUrl(from, to, date, departure, arival) {
  let path = `${from}${slash}${to}`;

  if (date) {
    path += `${slash}${date.toString().replaceAll("-", "")}`;
  }

  if (departure) {
    path += `${slash}${departure.toString().replaceAll(":", "")}`;
  }

  if (arival) {
    path += `${slash}${arival.toString().replaceAll(":", "")}`;
  }

  return getBaseTrackingUrl() + path;
}

function toPrice(price) {
  if (typeof price !== "number") {
    price = parseFloat(price);
  }

  return price.toLocaleString("nl-NL", {
    style: "currency",
    currency: "EUR",
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  });
}

function toFullPrice(price) {
  const [whole, fraction] = price
    .toLocaleString("nl-NL", {
      style: "currency",
      currency: "EUR",
      minimumFractionDigits: 0,
      maximumFractionDigits: 2,
    })
    .split(",");

  if (!fraction) {
    return whole;
  }

  return `${whole}<span class="fraction">,${fraction.padEnd(2, "0")}</span>`;
}

async function fetchData(url, options) {
  return await fetch(url, options ?? null)
    .then((response) => response)
    .catch(() => null);
}

function createDlJsonItem(
  to,
  from,
  toName,
  fromName,
  date,
  price = null,
  departureDate = null,
  arrivalDate = null
) {
  try {
    let departure = departureDate ? new Date(departureDate) : date;
    let arrival = arrivalDate ? new Date(arrivalDate) : null;

    return {
      toStation: to,
      fromStation: from,
      arrivalStation: toName,
      arrivalTime: arrival,
      departureStation: fromName,
      departureTime: departure,
      price: !price || price === "0.00" ? null : price,
      url: getTrackingUrl(
        from,
        to,
        date,
        getHourMinute(departure),
        getHourMinute(arrival)
      ),
    };
  } catch (error) {
    console.error("Error creating DL JSON item:", error);
    return null;
  }
}

function renderDlJson(collection, id = null) {
  const $ldElement = document.getElementById(id);
  if ($ldElement?.length > 0) {
    $ldElement.remove();
  }

  try {
    const newLdJson = document.createElement("script");
    newLdJson.id = id;
    newLdJson.type = "application/ld+json";

    const currentUrl = new URL(window.location.href);
    const baseUrl = currentUrl.origin;
    const image =
      baseUrl + "/wp-content/plugins/ns-international/styles/gtk-ns-logo.png";

    const allItems = collection.flatMap((trip) => {
      const trainTrip = {
        "@type": "TrainTrip",
        departureStation: {
          "@type": "TrainStation",
          name: trip.departureStation,
        },
        arrivalStation: {
          "@type": "TrainStation",
          name: trip.arrivalStation,
        },
        departureTime: trip.departureTime,
        arrivalTime: trip.arrivalTime,
        offers: {
          "@type": "Offer",
          price: trip.price,
          priceCurrency: "EUR",
          url: trip.url,
        },
        provider: {
          "@type": "Organization",
          name: "NS International",
          url: "https://www.nsinternational.com",
        },
      };

      if (trip.price === "0.00" || trip.price === null) {
        return [trainTrip];
      }

      let date;
      if (trip.departureTime) {
        const departureDate = new Date(trip.departureTime);
        const dateStr = departureDate.toISOString().split("T");
        date = `${dateStr[0]} ${dateStr[1].substring(0, 5)}`;
      }

      const nicePrice = trip.price ? toPrice(trip.price) : "onbekend";
      const productName = prepareDlText(
        php_vars.text_values["dl_product_name"],
        trip.departureStation,
        trip.arrivalStation,
        date,
        nicePrice
      );
      const productDescription = prepareDlText(
        php_vars.text_values["dl_product_description"],
        trip.departureStation,
        trip.arrivalStation,
        date,
        nicePrice
      );

      const product = {
        "@type": "Product",
        name: productName, //`Trein van ${trip.departureStation} naar ${trip.arrivalStation} op ${date}`,
        description: productDescription,
        /*`Goedkoopste treinkaartje van ${
          trip.departureStation
        } naar ${trip.arrivalStation} op ${date} voor ${
          trip.price ? `€${trip.price}` : "onbekend"
        }`,*/
        image: [image],
        offers: {
          "@type": "Offer",
          price: trip.price,
          priceCurrency: "EUR",
          url: trip.url,
        },
      };

      return [trainTrip, product];
    });

    const obj = JSON.stringify({
      "@context": "https://schema.org",
      "@graph": allItems,
    });

    newLdJson.textContent = obj;

    document.head.appendChild(newLdJson);
  } catch (error) {
    console.error("Error rendering DL JSON:", error);
  }
}

function prepareDlText(template, from, to, date, price) {
  return template
    .replace("{from}", from)
    .replace("{to}", to)
    .replace("{date}", date)
    .replace("{price}", price);
}

function getHourMinute(date) {
  if (!date) {
    return null;
  }

  if (!(date instanceof Date)) {
    date = new Date(date);
  }

  const digits = date
    .toLocaleTimeString("nl-NL", {
      hour: "2-digit",
      minute: "2-digit",
    })
    .replace(":", "");

  return digits.padStart(4, "0");
}
